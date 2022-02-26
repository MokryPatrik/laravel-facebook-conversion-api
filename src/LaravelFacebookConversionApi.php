<?php

namespace MokryPatrik\LaravelFacebookConversionApi;

use Illuminate\Support\Traits\Macroable;
use JsonException;

/**
 * Class LaravelFacebookPixel
 * @package MokryPatrik\LaravelFacebookConversionApi
 */
class LaravelFacebookConversionApi
{
    use Macroable;

    /**
     * @var string
     */
    protected $id;
    
    /**
     * @var bool
     */
    private $enabled;

    /**
     * LaravelFacebookPixel constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->enabled = true;
    }

    /**
     * Return the Facebook Pixel id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id): LaravelFacebookConversionApi
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Enable Facebook Pixel scripts rendering.
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable Facebook Pixel scripts rendering.
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @throws JsonException
     * 
     * @return string
     */
    public function bodyContent(): string
    {
        $facebookPixelSession = session()->pull('facebookPixelSession', []);
        if (count($facebookPixelSession) < 1) {
            return '';
        }

        $pixelCode = '';
        foreach ($facebookPixelSession as $key => $facebookPixel) {
            $params = json_encode($facebookPixel["parameters"], JSON_THROW_ON_ERROR);
            $pixelCode .= "fbq('track', '" . $facebookPixel["name"] . "', " . $params . ");";
        }
        session()->forget('facebookPixelSession');
        return "<script>" . $pixelCode . "</script>";
    }

    /**
     * @param string $eventName
     * @param array $parameters
     */
    public function createEvent(string $eventName, array $parameters = []): void
    {
        $facebookPixelSession = session('facebookPixelSession');
        $facebookPixelSession = !$facebookPixelSession ? [] : $facebookPixelSession;
        $facebookPixel = [
            "name" => $eventName,
            "parameters" => $parameters,
        ];
        $facebookPixelSession[] = $facebookPixel;
        session(['facebookPixelSession' => $facebookPixelSession]);
    }
}
