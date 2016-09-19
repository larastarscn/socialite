<?php

namespace Larastarscn\Socialite;

use Larastarscn\Socialite\Providers\QQProvider;
use Larastarscn\Socialite\Providers\WechatProvider;
use Larastarscn\Socialite\Providers\WeiboProvider;
use Laravel\Socialite\SocialiteServiceProvider as ServiceProvider;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Register extends.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerProviders();
    }

    /**
     * Register extends as a group.
     *
     * @return void
     */
    public function registerProviders()
    {
        $this->registerWechat();
        $this->registerQQ();
        $this->registerWeibo();
    }

    /*
     * Register a provider for wechat.
     *
     * @return void
     */
    public function registerWechat()
    {
        $this->app->make('Laravel\Socialite\Contracts\Factory')->extend('wechat', function ($app) {
            $config = $app['config']['services.wechat'];
            return new WechatProvider(
                $app['request'], $config['client_id'],
                $config['client_secret'], $config['redirect']
            );
        });
    }

    /*
     * Register a provider for QQ.
     *
     * @return void
     */
    public function registerQQ()
    {
        $this->app->make('Laravel\Socialite\Contracts\Factory')->extend('qq', function ($app) {
            $config = $app['config']['services.qq'];
            return new QQProvider(
               $app['request'], $config['client_id'],
               $config['client_secret'], $config['redirect']
            );
        });
    }

    /*
     * Register a provider for Weibo.
     *
     * @return void
     */
    public function registerWeibo()
    {
        $this->app->make('Laravel\Socialite\Contracts\Factory')->extend('weibo', function ($app) {
            $config = $app['config']['services.weibo'];
            return new WeiboProvider(
               $app['request'], $config['client_id'],
               $config['client_secret'], $config['redirect']
            );
        });
    }
}
