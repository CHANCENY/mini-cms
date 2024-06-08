<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Entity;
use Mini\Cms\Field;
use Mini\Cms\Fields\FieldInterface;
use Mini\Cms\Vocabulary;

class AutoComplete implements ControllerInterface
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
        $this->response->setStatusCode(StatusCode::OK)
            ->setContentType(ContentType::APPLICATION_JSON);

        $action = $this->request->get('action');
        if(!empty($action)) {
            $entities = Entity::entities();
            $vocabularies = Vocabulary::vocabularies();

            $list = [];
            $list[] = [
                'name' => 'users',
                'type' => 'users',
                'label' => 'User',
            ];

            if(!empty($entities)) {
                foreach ($entities as $entity) {
                    if($entity instanceof Entity) {
                        $list[] = [
                            'name' => $entity->getEntityTypeName(),
                            'type' => 'entity',
                            'label' => $entity->getEntityLabel(),
                        ];
                    }
                }
            }

            if(!empty($vocabularies)) {
                foreach ($vocabularies as $vocabulary) {
                    if($vocabulary instanceof Vocabulary) {
                        $list[] = [
                            'type' => 'vocabulary',
                            'label' => $vocabulary->getLabelName(),
                            'name' => $vocabulary->getVocabulary(),
                        ];
                    }
                }
            }


            $this->response->write($list);
            return;
        }

        $field_name = $this->request->get('field');
        $value = $this->request->get('value');
        if(!empty($field_name) && !empty($value)) {
            $field = Field::load($field_name);
            if($field instanceof FieldInterface) {
                $results = $field->referenceResults($value);
                $this->response->setStatusCode(StatusCode::OK)
                    ->write($results);
                return;
            }
        }
        $this->response->setStatusCode(StatusCode::EXPECTATION_FAILED)
            ->write(['error'=>'missing parameter expecting field and value']);
    }
}