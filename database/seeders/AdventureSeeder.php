<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Adventure;
use App\Models\Item;
use App\Models\Scenario;
use App\Models\Option;

class AdventureSeeder extends Seeder
{
    /**
     * Run the adventurebase seeds.
     */
    public function run(): void
    {


        $adventures = [
            [
                "name" => "Tatooine",
                "image" => "tatooine.jpeg",
                "description" => "Explora el árido planeta desértico y enfréntate a los peligros del desierto.",
                "items" => [
                    [
                        "name" => "Mapa del Desierto",
                        "description" => "Es un antiguo mapa que muestra rutas seguras en Tatooine.",
                        "image" => "mapa-desierto.jpeg"
                    ],
                    [
                        "name" => "Espada láser",
                        "description" => "Es un arma elegante para tiempos más civilizados.",
                        "image" => "espada-laser.jpeg"
                    ]
                ],
                "scenarios" => [
                    [
                        "question" => "¿Quién es el gánster más temido de Tatooine?",
                        "options" => [
                            ["text" => "Jabba el Hutt", "is_correct" => true],
                            ["text" => "Darth Vader", "is_correct" => false],
                            ["text" => "Luke Skywalker", "is_correct" => false],
                            ["text" => "Bib Fortuna", "is_correct" => false]
                        ],
                        "items" => []
                    ],
                    [
                        "question" => "¿Qué especie comercia con chatarra en Tatooine?",
                        "options" => [
                            ["text" => "Jawas", "is_correct" => true],
                            ["text" => "Tusken Raiders", "is_correct" => false],
                            ["text" => "Ewoks", "is_correct" => false]
                        ],
                        "items" => []
                    ],
                    [
                        "question" => "¿Cómo se llama el cazarrecompensas mandaloriano que capturó a Han Solo?",
                        "options" => [
                            ["text" => "Boba Fett", "is_correct" => true],
                            ["text" => "Cad Bane", "is_correct" => false],
                            ["text" => "Bossk", "is_correct" => false]
                        ],
                        "items" => []
                    ],
                    [
                        "question" => "¿Cuál es el medio de transporte más común en Tatooine?",
                        "options" => [
                            ["text" => "Banthas", "is_correct" => false],
                            ["text" => "Speeder", "is_correct" => true],
                            ["text" => "AT-AT", "is_correct" => false]
                        ],
                        "items" => [
                            [
                                "name" => "Cristal Kyber",
                                "description" => "Es un raro cristal utilizado en sables de luz.",
                                "image" => "cristal-kyber.jpeg"
                            ]
                        ]
                    ],
                    [
                        "question" => "¿Qué clima predomina en Tatooine?",
                        "options" => [
                            ["text" => "Desértico", "is_correct" => true],
                            ["text" => "Boscoso", "is_correct" => false],
                            ["text" => "Polar", "is_correct" => false]
                        ],
                        "items" => []
                    ]
                ]
            ],
            [
                "name" => "Endor",
                "image" => "endor.jpeg",
                "description" => "Explora el bosque y enfréntate a los peligros de la naturaleza.",
                "items" => [
                    [
                        "name" => "Mapa del Bosque",
                        "description" => "Es un mapa detallado de los senderos en Endor.",
                        "image" => "mapa-bosque.jpeg"
                    ],
                    [
                        "name" => "Bláster",
                        "description" => "Es un arma de fuego de alta tecnología.",
                        "image" => "blaster.jpeg"
                    ]
                ],
                "scenarios" => [
                    [
                        "question" => "¿Qué especie nativa habita en Endor?",
                        "options" => [
                            ["text" => "Ewoks", "is_correct" => true],
                            ["text" => "Jawas", "is_correct" => false],
                            ["text" => "Wookiees", "is_correct" => false]
                        ],
                        "items" => []
                    ],
                    [
                        "question" => "¿Cómo se llama el vehículo bípedo del Imperio en Endor?",
                        "options" => [
                            ["text" => "AT-ST", "is_correct" => true],
                            ["text" => "AT-AT", "is_correct" => false],
                            ["text" => "Speeder", "is_correct" => false]
                        ],
                        "items" => [
                            [
                                "name" => "Lanza Ewok",
                                "description" => "Es una lanza rudimentaria fabricada por los Ewoks.",
                                "image" => "lanza-ewok.jpeg"
                            ]
                        ]
                    ],
                    [
                        "question" => "¿Quién dirigió el ataque rebelde en Endor?",
                        "options" => [
                            ["text" => "Han Solo", "is_correct" => true],
                            ["text" => "Luke Skywalker", "is_correct" => false],
                            ["text" => "Leia Organa", "is_correct" => false]
                        ],
                        "items" => []
                    ],
                    [
                        "question" => "¿Qué recurso usan los Ewoks para derrotar a los soldados imperiales?",
                        "options" => [
                            ["text" => "Trampas", "is_correct" => true],
                            ["text" => "Blásters", "is_correct" => false],
                            ["text" => "Sables de luz", "is_correct" => false]
                        ],
                        "items" => [
                            [
                                "name" => "Holocrón Jedi",
                                "description" => "Es un dispositivo de conocimiento de los Jedi.",
                                "image" => "holocron-jedi.jpeg"
                            ]
                        ]
                    ],
                    [
                        "question" => "¿Dónde se encuentra el generador de escudo del Imperio en Endor?",
                        "options" => [
                            ["text" => "En una base terrestre", "is_correct" => true],
                            ["text" => "En el espacio", "is_correct" => false],
                            ["text" => "En un destructor estelar", "is_correct" => false]
                        ],
                        "items" => []
                    ]
                ]
            ]
        ];

        // primera iteración  creamos la  aventura 
        foreach ($adventures as $adventureData) {

            $adventure = Adventure::create([
                'name' => $adventureData['name'],
                'image' => $adventureData['image'],
                'description' => $adventureData['description']
            ]);

            // segunda iteración creamos los items 1:N podemos tener varios items. 
            foreach ($adventureData['items'] as $itemData) {

                Item::create([
                    'name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'image' => $itemData['image'],
                    'itemable_id' => $adventure->id,
                    'itemable_type' => 'App\Models\Adventure'
                ]);
            }

            // tercera iteración creamos los escenarios 1:N podemos tener varios escenarios 
            foreach ($adventureData['scenarios'] as $scenarioData) {

                $scenario = Scenario::create([
                    'question' => $scenarioData['question'],
                    'adventure_id' => $adventure->id
                ]);

                // cuarta iteración creamos las opciones 1:N podemos tener varias opciones
                foreach ($scenarioData['options'] as $optionData) {


                    Option::create([
                        'text' => $optionData['text'],
                        'is_correct' => $optionData['is_correct'],
                        'scenario_id' => $scenario->id
                    ]);
                }

                // chequeamos si hay items en el escenario, aqui temos una relación 1:1 solo un item por escenario
                if (!empty($scenarioData['items'])) {

                    $itemData = $scenarioData['items'][0];
                    Item::create([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'],
                        'image' => $itemData['image'],
                        'itemable_id' => $scenario->id,
                        'itemable_type' => 'App\Models\Scenario'
                    ]);
                }
            }
        }
    }
}
