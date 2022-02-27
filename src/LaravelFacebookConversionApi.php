<?php

namespace MokryPatrik\LaravelFacebookConversionApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Request;
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
     * @var array
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
     * @return array
     */
    public function getId(): array
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
            $eventId = uniqid($facebookPixel["name"], true);
            $pixelCode .= "fbq('track', '" . $facebookPixel["name"] . "', " . $params . ", {eventID: '" . $eventId . "'});";
             $this->conversionApiRequest($facebookPixel["name"], $eventId, $facebookPixel["parameters"]);
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

    /**
     * @param $eventName
     * @param $eventId
     * @param $params
     *
     * @throws GuzzleException
     */
    private function conversionApiRequest($eventName, $eventId, $params): void
    {
        if (isset($params['content_category']) && is_array($params['content_category'])) {
            $params['content_category'] = implode(',', $params['content_category']);
        }
        $data = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_id' => $eventId,
                    'event_time' => time(),
                    'event_source_url' => Request::url(),
                    'action_source' => 'website',
                    'user_data' => $this->getUserData(),
                    'custom_data' => $params,
                ]
            ]
        ];
        
        if (config('facebook-pixel.test_event_code')) {
            $data['test_event_code'] = config('facebook-pixel.test_event_code');
        }
        $data['access_token'] = config('facebook-pixel.facebook_conversion_api_access_token');

        foreach ($this->getId() as $id) {
            $endpoint = sprintf(
                'https://graph.facebook.com/v13.0/%s/events',
                $id
            );

            try {
                $client = new Client();
                 $client->request(
                    'POST',
                    $endpoint,
                    [
                        'headers' => ['Content-type' => 'application/json'],
                        'body' => json_encode($data,JSON_THROW_ON_ERROR)
                    ]
                );
            } catch (ClientException | JsonException $e) {}
        }
    }

    /**
     * @return array
     */
    private function getUserData(): array
    {
        $data =  [
            'client_ip_address' => Request::ip(),
            'client_user_agent' => Request::userAgent(),
        ];
        
        if (Request::cookie('_fbc')) {
            $data['fbc'] = Request::cookie('_fbc');
        }

        if (Request::cookie('_fbp')) {
            $data['fbp'] = Request::cookie('_fbp');
        }
        
        return $data;
    }
}
