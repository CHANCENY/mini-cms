<?php

namespace Mini\Cms\default\modules\default\core\src\Controllers;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Controller\ControllerInterface;
use Mini\Cms\Controller\Request;
use Mini\Cms\Controller\Response;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Services\Services;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CachingSettings implements ControllerInterface
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
            $config = new ConfigFactory();
            $config->set('caching', ['cache_global'=> $this->request->request->get('is_global') === 'on']);
            $config->save();
            if(in_array('all', $this->request->request->all('cache_key'))) {
                Caching::cache()->clear();
                Mini::messenger()->addSuccessMessage("Cached data cleared");
            }
            if(!empty($this->request->request->all('cache_key'))) {
                foreach($this->request->request->all('cache_key') as $key => $value) {
                    Caching::cache()->delete($value);
                }
                Mini::messenger()->addSuccessMessage("Cached data cleared");
            }
            (new RedirectResponse($this->request->headers->get('referer'),308))->send();
            exit;
        }
        $cached = Caching::cache()->getAll();
        $this->response->write(
            Services::create('render')
            ->render('caching-settings-form.php',['cache_global'=> getConfigValue('caching.cache_global'), 'cached'=>$cached])
        );
    }

}