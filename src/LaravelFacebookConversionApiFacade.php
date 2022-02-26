<?php

namespace MokryPatrik\LaravelFacebookConversionApi;

use Illuminate\Support\Facades\Facade;

/**
 * Class LaravelFacebookPixelFacade
 * @package MokryPatrik\LaravelFacebookConversionApi
 */
class LaravelFacebookConversionApiFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'facebook-pixel';
    }
}