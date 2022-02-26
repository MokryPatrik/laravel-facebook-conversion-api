<?php

namespace MokryPatrik\LaravelFacebookConversionApi;

use Illuminate\Support\ServiceProvider;
use MokryPatrik\LaravelFacebookConversionApi\LaravelFacebookConversionApi;

/**
 * Class LaravelFacebookPixelServiceProvider
 * @package MokryPatrik\LaravelFacebookConversionApi
 */
class LaravelFacebookConversionApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'facebook-pixel');
    
        $this->publishes([
            __DIR__.'/../resources/config/facebook-pixel.php' => config_path('facebook-pixel.php'),
        ], 'config');
        
        $this->app['view']->creator(
            ['facebook-pixel::head', 'facebook-pixel::body'],
            ScriptViewCreator::class
        );
    }
    
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../resources/config/facebook-pixel.php', 'facebook-pixel');
        
        $id = config('facebook-pixel.facebook_pixel_ids');
        
        $laravelFacebookPixel = new LaravelFacebookConversionApi($id);
        
        if (config('facebook-pixel.enabled') === false) {
            $laravelFacebookPixel->disable();
        }
        
        $this->app->instance(LaravelFacebookConversionApi::class, $laravelFacebookPixel);
        $this->app->alias(LaravelFacebookConversionApi::class, 'facebook-pixel');
    }
}