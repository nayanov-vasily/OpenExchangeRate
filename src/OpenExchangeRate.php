<?php

namespace OpenExchangeRate;

use OpenExchangeRate\Cache\SimpleCache;

/**
 *
 */
class OpenExchangeRate
{
    private string $appId;
    private string $uri = 'https://openexchangerates.org/api';
    private SimpleCache $cache;
    private bool $useCache;

    /**
     * 
     */
    public function __construct(string $appId, bool $useCache = false, SimpleCache $cache = null)
    {
        $this->appId = $appId;
        $this->useCache = $useCache;
        $this->cache = new SimpleCache();
    }

    /**
     * Актуальный курс обмена 
     */
    public function latest(string $base = 'USD'): ExchangeRate
    {
        $cacheKey = "latest_$base";

        if ($this->useCache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $params = [
            'app_id' => $this->appId,
            'base' => $base,
        ];

        $params = http_build_query($params);
        $uri = "$this->uri/latest.json?$params";
        $latest = $this->sendRequest('GET', $uri);
        $rate = new ExchangeRate($latest->timestamp, $latest->base, $latest->rates);

        $this->cache->set($cacheKey, $rate);

        return $rate;
    }

    /**
     * Список валют
     */
    public function currencies(bool $prettyprint = false, bool $show_alternative = false, bool $show_inactive = false): object
    {
        $cacheKey = "currencies_{$prettyprint}_{$show_alternative}_{$show_inactive}";

        if ($this->useCache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $params = [
            'app_id' => $this->appId,
            'prettyprint' => $prettyprint,
            'show_alternative' => $show_alternative,
            'show_inactive' => $show_inactive,
        ];

        $params = http_build_query($params);
        $uri = "$this->uri/currencies.json?$params";
        $currencies = $this->sendRequest('GET', $uri);

        $this->cache->set($cacheKey, $currencies);

        return $currencies;
    }

    private function sendRequest(string $method = 'GET', string $uri): object
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request($method, $uri);
        
        $statusCode = $response->getStatusCode();

        $contentType = $response->getHeader('content-type');

        echo $contentType[0] . PHP_EOL;

        // $object;
        
        $body = json_decode($response->getBody());

        if ($statusCode !== 200) {
            throw new \Exception($body->description, $statusCode);
        }

        return $body;
    }

    /**
     * @deprecated
     */
    private function executeQuery(string $uri): object
    {
        // Open CURL session:
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Get the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $response = json_decode($json);

        if (property_exists($response, 'error') && $response->error) {
            throw new \Exception($response->message, $response->status);
        }

        return $response;
    }
}