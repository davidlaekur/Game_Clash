<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{


    use CreatesApplication;


    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mongodbTest',
            'database.connections.mongodbTest.database' => 'game_test',
        ]);
    }

    protected function tearDown(): void
    {
  
        $mongoClient = DB::connection('mongodb')->getMongoClient();

        //  eliminar la base de datos de prueba
        $mongoClient->dropDatabase(config('database.connections.mongodbTest.database'));


        parent::tearDown();
    }
}
