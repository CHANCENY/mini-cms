<?php

namespace Mini\Cms\Controller;

use Mini\Cms\Modules\Cache\CacheStorage;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\ErrorSystem;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\FormControllerBase\FormControllerInterface;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Theme\Theme;

class Response
{
    private StatusCode $statusCode;

    private ContentType $contentType;
    private string $redirectUrl;

    private mixed $body;
    private string $cacheHeader;
    private string $responseType;
    private string $generator;
    private int $maxAge;

    public function __construct()
    {
        $this->contentType = ContentType::TEXT_HTML;
        $this->responseType = 'normal';
        $this->generator = "Mini CMS";
        $this->statusCode = StatusCode::OK;
        $this->cacheHeader = 'public, max-age=' . (60 * 60 * 24 * 365);
        $this->body = '';
    }

    public function setStatusCode(StatusCode $status_code): Response
    {
        $this->statusCode = $status_code;
        return $this;
    }

    public function setContentType(ContentType $content_type): Response
    {
        $this->contentType = $content_type;
        return $this;
    }

    public function setRedirect(string $redirect_url): Response
    {
        $this->redirectUrl = $redirect_url;
        return $this;
    }

    public function write(mixed $body_data): Response
    {
        $this->body = $body_data;
        return $this;
    }

    public function send(): void
    {
        $max_age = $this->maxAge ?? getConfigValue('caching_setting.max_age') ?? 12;
        // Setting headers
        header("min-cms-type: ".$this->responseType);
        header("Generator: $this->generator");

        if($max_age) {
            header("Cache-Control: ".$this->cacheHeader);
            header("Pragma: cache");
            header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600 * 60 * $max_age) . " GMT");
        }

        // Setting response code.
        http_response_code($this->statusCode->value);

        // Bring in theme loaded.
        $theme = Tempstore::load('theme_loaded');

        if($theme instanceof Theme) {

            $route = Tempstore::load('current_route');
            $route_id = null;
            $flag_cacheable = false;
            $current_user = new CurrentUser();

            if($route instanceof Route) {
                $route_id = $route->getLoadedRoute()->getRouteId();
                $controller = $route->getControllerHandler();
                if($controller instanceof ControllerInterface) {
                    try{ $flag_cacheable = $controller->cacheable(); }catch (\Throwable){}
                }
            }

            // Making sure that on txt/html we are sending all required html content.
            if($this->contentType === ContentType::TEXT_HTML) {

                $inline_head = [];
                $inline_footer = [];

                Extensions::runHooks('_inline_head_script_alter', [&$inline_head, &$inline_footer]);

                $inline_head = implode("\n", $inline_head);
                $inline_footer = implode("\n", $inline_footer);

                header("Content-Type: ".$this->contentType->value);
                $title = $route->getLoadedRoute()->getRouteTitle();
                $in_response_data = "<!DOCTYPE html><html {{ATTRIBUTES}}>
                                     <head>
                                       {{META_TAGS}}
                                       {{HEAD_ASSETS}}
                                       <title>$title</title>
                                        {{INLINE_SCRIPT_HEAD}}
                                     </head>
                                     <body class='body-content full-content-$route_id'>
                                       {{NAVIGATION}}
                                       {{CONTENT_BODY}}
                                       {{FOOTER}}
                                       {{APPEND_ASSETS}}
                                       {{DEFAULTS_ASSETS}}
                                       {{INLINE_SCRIPT_FOOTER}}
                                     </body>
                                     </html>";

                // Loading head section assets.
                $header = $theme->writeAssets('head');
                $in_response_data = str_replace('{{HEAD_ASSETS}}',$header, $in_response_data);

                // Load meta tags.
                $meta_tags = $theme->writeMetaTag();
                $in_response_data = str_replace('{{META_TAGS}}',$meta_tags, $in_response_data);

                // Html attributes
                $htmlAttribute = $theme->writeHtmlAttribute();
                $in_response_data = str_replace('{{ATTRIBUTES}}',$htmlAttribute, $in_response_data);

                $in_response_data = str_replace('{{INLINE_SCRIPT_HEAD}}',$inline_head, $in_response_data);
                $in_response_data = str_replace('{{INLINE_SCRIPT_FOOTER}}', $inline_head, $in_response_data);

                // Adding navigation content.
                $navigation = $theme->writeNavigation();
                $in_response_data = str_replace('{{NAVIGATION}}',$navigation,$in_response_data);

                // Main content.
                $in_response_data = str_replace('{{CONTENT_BODY}}',$this->body,$in_response_data);
                
                // Footer content.
                $footer = $theme->writeFooter();
                $in_response_data = str_replace('{{FOOTER}}',$footer, $in_response_data);

                // Assets appending
                $belowAssets = $theme->writeAssets('footer');
                $in_response_data = str_replace('{{APPEND_ASSETS}}',$belowAssets, $in_response_data);

                // Finishing
                $content = $theme->processBuildContentHtml($in_response_data);
                header("Content-Type: ".(ContentType::TEXT_HTML)->value);
                if($flag_cacheable) {
                    $uid = $current_user->id();
                    Caching::cache()->set($route_id.'_'.$uid,['headers'=> ['Content-Type' => ContentType::TEXT_HTML->value], 'content' => $content]);
                }
                print_r($content);
                return;
            }

            else {
                header("Content-Type: ".$this->contentType->value);
                // Lets response request with other content type.
                // Checking if we are responding with json data.
                if($this->contentType === ContentType::APPLICATION_JSON) {
                    if(gettype($this->body) === 'array') {
                        $this->body = json_encode($this->body);
                    }
                }
                // Writing to content.
                if($flag_cacheable) {
                    $uid = $current_user->id();
                    Caching::cache()->set($route_id.'_'.$uid,['headers'=> ['Content-Type'=> $this->contentType->value], 'content' => $this->body]);
                }
                print_r($this->body);
                exit;
            }
        }

    }

    public function setMaxAge(int $days): Response
    {
        $this->maxAge = $days;
        return $this;
    }
}