<?php

namespace Mini\Cms\Controller;

use Mini\Cms\bootstrap\Kernel;
use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Access\AccessMiddleRunner;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\FormControllerBase\FormBuilder;
use Mini\Cms\Modules\FormControllerBase\FormControllerInterface;
use Mini\Cms\Modules\FormControllerBase\FormState;
use Mini\Cms\Modules\MetaTag\MetaTag;
use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Routing\RouteBuilder;
use Mini\Cms\Routing\URIMatcher;
use Mini\Cms\Services\Services;
use Mini\Cms\Theme\Footer;
use Mini\Cms\Theme\MarkUp;
use Mini\Cms\Theme\Menus;
use Mini\Cms\Theme\Theme;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class Route
{

    /**
     * @var false|mixed
     */
    private mixed $loadedRoute;
    private Request $request;
    private Response $response;
    private mixed $controllerHandler;
    private string $currentUri;

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getControllerHandler(): mixed
    {
        return $this->controllerHandler;
    }

    public function getCurrentUri(): string
    {
        return $this->currentUri;
    }

    /**
     * @throws PageNotFoundException
     * @throws TemporaryUnAvailableException
     * @throws AccessDeniedRouteException
     * @throws ControllerMissingException
     * @throws ControllerHandlerNotFoundException
     * @throws BadGateWayException
     */
    public function match(string $method, string $path): void
    {
        // Prepare uri
        $path = "/". trim($path, '/');

        // Getting all routes.
        $builder = new RouteBuilder();

        $matcher = new URIMatcher($builder->getPatterns());
        if ($matcher->matchCurrentURI($path)) {
            $params = $matcher->getParams();
            $pattern = $matcher->getMatchedPattern();
            $this->currentUri = $path;

            // Global definitions.
            Extensions::runHooks('_global_definitions_alter',[]);
            define_global('current_route', $this);

            // Found matched route info
            $routeBuilder = new RouteBuilder();
            $this->loadedRoute = $routeBuilder->getRouteByPattern($pattern);

            $theme = Theme::loader();
            Extensions::runHooks('_theme_alter',[&$theme]);
            define_global('theme_loaded',$theme);

            $menus = new Menus($path);
            Extensions::runHooks('_menus_alter', [&$menus]);
            define_global('theme_navigation', $menus);

            $footer = new Footer();
            $footer->themeFile('footer.php');
            Extensions::runHooks('_footer_alter',[&$footer]);
            define_global('theme_footer', $footer);

            if(!empty($params)) {
                $_GET = array_merge($_GET, $params);
                Extensions::runHooks('_request_params_alter',[&$_GET]);
            }
            
            Extensions::runHooks('_loaded_route_alter',[&$this->loadedRoute]);

            // Let's load controller here.
            $controller = $this->loadedRoute->getRouteHandler();
            Extensions::runHooks('_route_controller_handler_alter',[&$controller]);

            // Do security check using hook
            Extensions::runHooks('_route_access_alter',[&$this->loadedRoute]);

            // Handling controller.
            if(empty($controller)) {
                Extensions::runHooks('_not_found_error');
                throw new ControllerHandlerNotFoundException("Controller not found to handle the request CID: ".$this->loadedRoute->getRouteId());
            }
            $this->request = Request::createFromGlobals();
            $this->response = new Response();

            if($this->request->isMethod('POST')) {
                Extensions::runHooks('_post_request_alter',[&$this->request]);
            }

            $metaTag = new MetaTag();
            Extensions::runHooks('_meta_data_initialize_alter',[&$metaTag, $this->request, $this->loadedRoute]);
            define_global('theme_meta_tags',$metaTag);

            // Middlewares calling.
            $access_middle_response = (new AccessMiddleRunner($this->loadedRoute, $this->response))->runMiddleWares();
            if($access_middle_response) {
                $access_middle_response->send();
                exit;
            }

            // Before calling handle lets check options
            if(!$this->loadedRoute->isAccessible()) {
                Extensions::runHooks('_access_denied_error');
                throw new TemporaryUnAvailableException("This controller can not be access at moment CD". $this->loadedRoute->getRouteId());
            }

            if(!$this->loadedRoute->isMethod($method)) {
                Extensions::runHooks('_method_not_allowed_error');
                throw new BadGateWayException('Method '.$method. ' is not allowed for route CD: '.$this->loadedRoute->getRouteId());
            }

            // Current user here.
            $currentUserRoles = Services::create('current.user')->getRoles();
            if((new CurrentUser())->isAdmin() === false && !$this->loadedRoute->isUserAllowed($currentUserRoles)) {
                Extensions::runHooks('_access_denied_error');
                throw new AccessDeniedRouteException("Route is not allowed to be access by user with ".implode(',', $currentUserRoles). ' roles RD: '.$this->loadedRoute->getRouteId());
            }

            try{
                $cacheable = (new $controller($this->request, $this->response))->cacheable();
                if($cacheable) {
                    $uid = (new CurrentUser())->id();
                    $data_cached = Caching::cache()->get($this->loadedRoute->getRouteId().'_'.$uid);
                    if(isset($data_cached['headers']) && isset($data_cached['content'])) {
                        header("Content-type: {$data_cached['headers']['Content-Type']}");
                        print($data_cached['content']);
                        return;
                    }
                }
            }catch (Throwable) {}

            if($this->loadedRoute->getRouteType() === '_controller') {
                $list = explode('::', $controller);
                $controller = $list[0];
                $method = $list[1] ?? null;
                // Making handler object.
                $this->controllerHandler = new $controller($this->request, $this->response);
                if($this->controllerHandler instanceof ControllerInterface) {

                    if($this->controllerHandler->isAccessAllowed()) {

                        // Will are calling writeBody method on controller class.
                        // so that we can have a response.
                        if($method) {
                            $this->controllerHandler->$method();
                        }
                        else {
                            $this->controllerHandler->writeBody();
                        }
                        Extensions::runHooks('_response_alter',[&$this->response]);
                        $this->response->send();
                        return;
                    }
                }
            }

            if ($this->loadedRoute->getRouteType() === '_form') {

                $this->controllerHandler = new $controller($this->request, $this->response);
                if($this->controllerHandler instanceof FormControllerInterface) {

                    if($this->controllerHandler->isAccessAllowed()) {

                        if($this->request->isMethod('GET')) {
                            $form_fields['form_id'] = [
                                "#type" => "hidden",
                                "#title" => "form id",
                                "#required" => true,
                                "#placeholder" => "form id",
                                "#attributes" => ["class" => "form-control", "id" => "form-id"],
                                "#description" => "form id is required.",
                                "#default_value" => $this->controllerHandler->getFormId(),
                            ];
                            $form_state = new FormState($form_fields, false);
                            $form = $this->controllerHandler->buildForm($form_fields, $form_state);
                            $form_base = new FormBuilder($form);
                            $_string = $form_base->buildForm();
                            $_form_setting = Tempstore::load('_form_setting');
                            $_form_setting[$this->controllerHandler->getFormId()] = [
                                '#form' => $form,
                            ];
                            Tempstore::save('_form_settings',$_form_setting);
                            $this->response->setStatusCode(StatusCode::OK)->setContentType(ContentType::TEXT_HTML);
                            if($this->controllerHandler->getTemplate()) {
                                $_string = Services::create('render')->render($this->controllerHandler->getTemplate(), ['_form' => $_string]);
                            }
                            $this->response->write($_string);
                            Extensions::runHooks('_response_alter',[&$this->response]);
                            $this->response->send();
                            return;
                        }

                        if($this->request->isMethod('POST')) {
                            $_form_setting = Tempstore::load('_form_settings');
                            $_form_setting = $_form_setting[$this->request->request->get('form_id')];
                            $form_fields = $_form_setting['#form'];
                            $form_state = new FormState($form_fields,true);
                            $this->controllerHandler->validateForm($form_fields, $form_state);
                            if(!$form_state->isValidated()) {
                                foreach ($form_state->getErrors() as $error) {
                                    if(is_string($error)) {
                                        Mini::messenger()->addErrorMessage($error);
                                    }
                                    if($error instanceof MarkUp) {
                                        Mini::messenger()->addErrorMessage($error->getMarkup());
                                    }
                                }
                            }
                            else {
                                $this->controllerHandler->submitForm($form_fields,$form_state);
                                (new RedirectResponse($form_state->getRedirectUrl()))->send();
                                return;
                            }
                        }
                    }
                }
            }

            // If reach at this point the dont have controller.
            Extensions::runHooks('_controller_not_found');
            throw new ControllerMissingException("Controller not found (".$controller. ")");
        }
        else {
            Extensions::runHooks('_not_found_alter',[&$path]);
            throw new PageNotFoundException("This uri is missing in system $path");
        }
    }

    public function getLoadedRoute(): mixed
    {
        return $this->loadedRoute;
    }

    public function __construct()
    {
        $config = Services::create('config.factory');
        if($config instanceof ConfigFactory) {
            $database = $config->get('database');
            if(empty($database)) {
                (new RedirectResponse('/new-install.php'))->send();
                exit;
            }
        }
        Extensions::runHooks('_wrapper_register_alter',[]);
    }
}