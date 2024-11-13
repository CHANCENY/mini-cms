<?php

namespace Mini\Cms\Controller;

use Mini\Cms\Mini;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Theme\Theme;

class Response
{
    private StatusCode $statusCode;

    private ContentType $contentType;
    private mixed $body;
    private $headers;

    public function __construct()
    {
        $this->contentType = ContentType::TEXT_HTML;
        $this->statusCode = StatusCode::OK;
        $this->headers = [
            'Content-Encoding' => 'gzip',
            'Cache-Control' => 'public, max-age=604800, immutable',
            'Expires' => gmdate("D, d M Y H:i:s", time() + 604800) . " GMT",
            'Pragma' => 'public',
            'Vary' => 'Accept-Encoding',
        ];

        // Setting Etag and Last Modified.
        $current_route = Mini::currentRoute()->getRouteId();
        $etag_register = Caching::cache()->get('etag-register');
        if($etag_register){
            $this_route = $etag_register[$current_route] ?? null;
            if(is_array($this_route)){
                $this->headers['ETag'] = $this_route['id'] ?? uniqid();
                $this->headers['Last-Modified'] = gmdate("D, d M Y H:i:s", $this_route['last_modified']) . " GMT";
            }
        }
        Extensions::runHooks('_response_headers_alter', [&$this->headers]);
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

    public function write(mixed $body_data): Response
    {
        $this->body = $body_data;
        return $this;
    }

    public function send(): void
    {
        // Setting headers
        foreach ($this->headers as $name => $value){
            header($name . ': ' . $value);
        }

        // Setting response code.
        http_response_code($this->statusCode->value);

        // Bring in theme loaded.
        $theme = get_global('theme_loaded');

        if($theme instanceof Theme) {

            $route = get_global('current_route');
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
                $in_response_data = str_replace('{{CONTENT_BODY}}',$this->body ?? '',$in_response_data);
                
                // Footer content.
                $footer = $theme->writeFooter();
                $in_response_data = str_replace('{{FOOTER}}',$footer ?? '', $in_response_data);

                // Assets appending
                $belowAssets = $theme->writeAssets('footer');
                $in_response_data = str_replace('{{APPEND_ASSETS}}',$belowAssets ?? '', $in_response_data);

                // Finishing
                $content = $theme->processBuildContentHtml($in_response_data);
                header("Content-Type: ".(ContentType::TEXT_HTML)->value);
                if($flag_cacheable) {
                    $uid = $current_user->id();
                    Caching::cache()->set($route_id.'_'.$uid,['headers'=> ['Content-Type' => ContentType::TEXT_HTML->value], 'content' => $content]);
                }
                //print_r($content);
                // Compress the content and print it
                $compressedContent = gzencode($content, 6);
                print_r($compressedContent);
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
                $compressedContent = gzencode($this->body, 6);
                print_r($compressedContent);
                return;
            }
        }

    }

}