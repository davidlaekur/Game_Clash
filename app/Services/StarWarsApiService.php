<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;


class StarWarsApiService
{

    public function getRandomCharacter()
    {


        $response = Http::get('https://www.swapi.tech/api/people/'.rand(1,83));


        if (!$response->successful() || empty($response->json()['result'])) {
            return null;
        }

        return $response->json()['result']['properties']['name'];
    }
}
