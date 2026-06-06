<?php

namespace App\Services;

use GuzzleHttp\Client;

class ZoneApiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout'  => 5.0,
        ]);
    }

    /**
     * Obtener zonas desde la API mockAPI 
     */
    public function fetchZones()
    {
        $url ='https://67a91ec86e9548e44fc2ec42.mockapi.io/zones';

        try {
            $response = $this->client->get($url);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return ['error' => 'Error al obtener datos de la API externa: ' . $e->getMessage()];
        }
    }
}

