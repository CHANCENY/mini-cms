<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Modules\Cache\CacheStorage;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CacheSettings implements ControllerInterface
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
        if($this->request->getMethod() === 'POST') {
            $data['max_age'] = (int) $this->request->request->get('ma-age', 0);
            $data['enabled'] = $this->request->request->get('cache-setting', 0) === 'on' ? 1 : 0;
            $config = new ConfigFactory();
            $config->set('caching_setting',$data);
            $config->save();
            $cache = new CacheStorage();
            $cache->destroy();
            (new RedirectResponse($this->request->headers->get('referer')))->send();
        }
       $this->response->write(Services::create('render')->render('default_cache_settings.php'));
    }
}