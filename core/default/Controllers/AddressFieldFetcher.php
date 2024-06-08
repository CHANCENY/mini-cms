<?php

namespace Mini\Cms\default\Controllers;

use Mini\Cms\Controller\ContentType;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Controller\StatusCode;
use Mini\Cms\Modules\Respositories\Territory\AddressFormat;

class AddressFieldFetcher implements ControllerInterface
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
        $country_code = $this->request->get('country_code');
        $field_name = $this->request->get('field_name');
        if(!empty($country_code) && !empty($field_name)) {
            $this->response->setContentType(ContentType::APPLICATION_JSON)
                ->setStatusCode(StatusCode::OK)
                ->write((new AddressFormat())->getAddressFieldsMarkUp($country_code,$field_name));
        }
        else {
            $this->response->setStatusCode(StatusCode::BAD_REQUEST)
                ->setContentType(ContentType::APPLICATION_JSON)
                ->write(['error' => 'country_not_found']);
        }
    }
}