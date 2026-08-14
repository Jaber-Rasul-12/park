<?php namespace Jaber\Player;

use System\Classes\PluginBase;
use Illuminate\Support\Facades\Route;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

public function registerNodes()
{
    return [
        // قائمة الفواتير مع ترقيم الصفحات
        '/invoice' => [
            'controller' => 'Jaber\Player\Http\Invoice',
            'only'       => ['show', 'update', 'destroy'] 
        ],
        

       
    ];
}
    /**
     * Register middleware - الطريقة الصحيحة لـ WinterCMS
     */
    public function registerMiddleware()
    {
        return [
            'api.auth' => \Jaber\Player\Http\Middleware\ApiAuthMiddleware::class
        ];
    }

    /**
     * Boot method
     */
    public function boot()
    {
        // تسجيل middleware في الـ router
        $this->app['router']->aliasMiddleware('api.auth', \Jaber\Player\Http\Middleware\ApiAuthMiddleware::class);
    }
}
