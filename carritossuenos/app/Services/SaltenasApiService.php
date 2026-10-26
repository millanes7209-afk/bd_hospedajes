<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SaltenasApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('SAL_API_BASE_URL', ''), '/');
    }

    /**
     * Perform a GET request to the central API.
     */
    public function get(string $uri, array $params = [])
    {
        $url = $this->baseUrl . $uri;
        $response = Http::withOptions(['verify' => false])->get($url, $params);
        return $response->json();
    }

    /**
     * Perform a POST request to the central API.
     */
    public function post(string $uri, array $data = [])
    {
        $url = $this->baseUrl . $uri;
        $response = Http::withOptions(['verify' => false])->post($url, $data);
        return $response->json();
    }
}
