<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Access\Roles;
use Mini\Cms\Modules\PermanentStorage\Statement\interfaces\DeleteInterface;
use Mini\Cms\Routing\Route;
use Mini\Cms\Routing\RouteBuilder;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Pages implements ControllerInterface
{

    public function __construct(private Request &$request, private Response &$response)
    {
    }

    /**
     * @inheritDoc
     */
    public function isAccessAllowed(): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function writeBody(): void
    {
        $routes = new RouteBuilder();
        $roles = new Roles();

        if($this->request->isMethod('POST')) {

            $routes->setName($this->request->request->get('title'));
            $routes->setDescription($this->request->request->get('description'));
            $routes->setNewUrl($this->request->request->get('uri'));
            $routes->setUnAuthorizedAccess($this->request->request->get('accessible') === 'on');
            foreach ($this->request->request->all('methods') as $method) {
                $routes->setMethod(strtoupper($method));
            }
            foreach ($this->request->request->all('roles') as $role) {
                $routes->setAllowedRole($role);
            }
            $class = preg_replace('/[^A-Za-z0-9]/', '', ucwords($this->request->request->get('title')));
            $class = str_replace(' ', '', $class);
            $directory = 'controllers'.DIRECTORY_SEPARATOR. $class;
            @mkdir($directory, 0755, true);
            $controller = "Mini\\Cms\Web\\Controllers\\" . $class;
            $contents = "<?php".PHP_EOL.PHP_EOL."/**".PHP_EOL."@route $controller".PHP_EOL.PHP_EOL."*/".PHP_EOL;

            if($this->request->request->get('is_form') === 'on') {
                $routes->isFormRoute();
                $contents .= "namespace $controller;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Modules\\FormControllerBase\\FormControllerInterface;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\Request;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\Response;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\StatusCode;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\ContentType;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Modules\\FormControllerBase\\FormState;".PHP_EOL.PHP_EOL;
                $contents .= "class $class implements FormControllerInterface {".PHP_EOL;
                $contents .= '    public function __construct(private Request &$request, private Response &$response){}'.PHP_EOL.PHP_EOL;
                $contents .= $this->body2();
                $contents .= "}".PHP_EOL.PHP_EOL;
                file_put_contents($directory.DIRECTORY_SEPARATOR.$class.'.php', $contents);
            }
            else {
                $routes->isNormalRoute();
                $contents .= "namespace $controller;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\ControllerInterface;".PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\Request;".PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\Response;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\StatusCode;".PHP_EOL.PHP_EOL;
                $contents .= "use Mini\\Cms\\Controller\\ContentType;".PHP_EOL.PHP_EOL;
                $contents .= "class $class implements ControllerInterface {".PHP_EOL.PHP_EOL;
                $contents .= '    public function __construct(private Request &$request, private Response &$response){}'.PHP_EOL.PHP_EOL;
                $contents .= '    public function isAccessAllowed(): bool { return true; }'.PHP_EOL.PHP_EOL;
                $contents .= $this->body();
                $contents .= "}".PHP_EOL.PHP_EOL;
                file_put_contents($directory.DIRECTORY_SEPARATOR.$class.'.php', $contents);
            }
            if(file_exists($directory.DIRECTORY_SEPARATOR.$class.'.php')) {
                $routes->setHandler($controller."\\".$class, true);
                $routes->save();
                (new RedirectResponse($this->request->headers->get('referer')))->send();
                exit;
            }
        }

        $routes = $routes->getCustomRoutes();
        $this->response->write(Services::create('render')->render('default_pages.php',['pages' => $routes, 'roles' => $roles->getRoles()]));
    }

    private function body(): string
    {
        $content = ' public function writeBody(): void'.PHP_EOL.'{'.PHP_EOL;
        $content .= '        $this->response->setStatusCode(StatusCode::OK)->setContentType(ContentType::TEXT_HTML)->write("<h1>Hello World!</h1>");';
        $content .= '    }'.PHP_EOL;
        return $content;
    }

    private function body2(): string
    {
        $content = '    public function validateForm(array &$form, FormState &$formState): void{}'.PHP_EOL.PHP_EOL;
        $content .= '   public function getFormId(): string{ return "change_this_form_id"; }'.PHP_EOL.PHP_EOL;
        $content .= '   public function writeBody(): void{}'.PHP_EOL.PHP_EOL;
        $content .= '   public function isAccessAllowed(): bool{ return true; }'.PHP_EOL.PHP_EOL;
        $content .= '   public function submitForm(array &$form, FormState $formState): void{}'.PHP_EOL.PHP_EOL;
        $content .= '   public function buildForm(array $form, FormState $formState): array{'.PHP_EOL;
        $content .= '      $form["field_sample"] = [
            "#type" => "text",
            "#title" => "Sample field",
            "#required" => true,
            "#placeholder" => "enter sample data",
            "#attributes" => ["class" => "form-control", "id" => "sample-field"],
            "#description" => "Please enter your sample data.",
            "#default_value" => $formState->get("field_sample"),
        ];'.PHP_EOL;
        $content .= '       return $form;'.PHP_EOL;
        $content .= '    }'.PHP_EOL.PHP_EOL;
        return $content;
    }
}