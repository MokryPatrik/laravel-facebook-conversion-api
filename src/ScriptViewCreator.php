<?php

namespace MokryPatrik\LaravelFacebookConversionApi;

use Illuminate\View\View;

/**
 * Class ScriptViewCreator
 * @package MokryPatrik\LaravelFacebookConversionApi
 */
class ScriptViewCreator
{
    /**
     * @var LaravelFacebookConversionApi
     */
    protected $laravelFacebookPixel;
    
    /**
     * ScriptViewCreator constructor.
     *
     * @param LaravelFacebookConversionApi $laravelFacebookPixel
     */
    public function __construct(LaravelFacebookConversionApi $laravelFacebookPixel)
    {
        $this->laravelFacebookPixel = $laravelFacebookPixel;
    }
    
    /**
     * @param View $view
     */
    public function create(View $view): void
    {
        $view
            ->with('enabled', $this->laravelFacebookPixel->isEnabled())
            ->with('id', $this->laravelFacebookPixel->getId())
        ;
    }
}