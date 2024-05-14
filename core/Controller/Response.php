<?php

namespace Mini\Cms\Controller;

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

    public function __construct()
    {
        $this->contentType = ContentType::TEXT_HTML;
        $this->responseType = 'normal';
        $this->generator = "Mini CMS";
        $this->statusCode = StatusCode::OK;
        $this->cacheHeader = 'max-age=' . (60 * 60 * 24 * 365);
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
        // Setting headers
        header("Content-Type: ".$this->contentType->value);
        header("min-cms-type: ".$this->responseType);
        header("Generator: $this->generator");
        header("Cache-Control: $this->cacheHeader");

        // Setting response code.
        http_response_code($this->statusCode->value);

        // Bring in theme loaded.
        $theme = Tempstore::load('theme_loaded');

        if($theme instanceof Theme) {

            $route = Tempstore::load('current_route');
            $route_id = null;

            if($route instanceof Route) {
                $route_id = $route->getLoadedRoute()->getRouteId();
            }

            // Making sure that on txt/html we are sending all required html content.
            if($this->contentType === ContentType::TEXT_HTML) {

                $title = $route->getLoadedRoute()->getRouteTitle();
                $in_response_data = "<!DOCTYPE html><html {{ATTRIBUTES}}>
                                     <head>
                                       {{META_TAGS}}
                                       {{HEAD_ASSETS}}
                                       <title>$title</title>
                                     </head>
                                     <body class='body-content full-content-$route_id'>
                                       <header class='header-content'>{{NAVIGATION}}</header>
                                       <section class='main-content'>{{CONTENT_BODY}}</section> 
                                       <footer class='footer-content'>{{FOOTER}}</footer>
                                       {{APPEND_ASSETS}}
                                       {{DEFAULTS_ASSETS}}
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
                echo $theme->processBuildContentHtml($in_response_data);
                exit;
            }
            else {
                // Lets response request with other content type.
                // Checking if we are responding with json data.
                if($this->contentType === ContentType::APPLICATION_JSON) {
                    if(gettype($this->body) === 'array') {
                        $this->body = json_encode($this->body);
                    }
                }
                // Writing to content.
                print_r($this->body);
                exit;
            }
        }


    }
}