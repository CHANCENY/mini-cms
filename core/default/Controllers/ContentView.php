<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entities\Node;
use Mini\Cms\Services\Services;
use Throwable;

class ContentView implements ControllerInterface
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

    public function writeBody(): void
    {
        $node = Node::load((int) $this->request->get('node_id'));
        $markup_line = null;
        if($node instanceof Node) {
            $fields = $node->getFields();
            foreach($fields as $key=>$field) {
                try {
                   $field_value = $node->get($field->getName());

                   // Displaying markup
                    $markup_line .= $field->markUp($field_value);
                }catch (Throwable $exception) {

                }
            }
        }
        $this->response->setContentType(ContentType::TEXT_HTML)
            ->setStatusCode(StatusCode::OK)
            ->write(Services::create('render')->render('node_node_view_content.php',['markup_line'=>$markup_line, 'node'=>$node]));
    }
}