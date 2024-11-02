<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Access\Roles;
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