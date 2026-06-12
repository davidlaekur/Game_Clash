<?php

/*
 * Stats base de cada invento (por nombre). Se aplican al forjarlo (MonitorAction)
 * escalados por la densidad del material (statFactor). Fuente única para forja y
 * para reparar inventos antiguos sin stats.
 */

return [
    'Piedra Afilada' => ['ataque' => 5],
    'Cuerda'         => ['ingenio' => 3, 'velocidad' => 2],
    'Fuego'          => ['ingenio' => 4, 'defensa' => 3],
    'Lanza'          => ['ataque' => 6, 'defensa' => 2],
    'Arco y Flecha'  => ['ataque' => 7, 'defensa' => 3],
    'Cesta'          => ['capacidad' => 5, 'suerte' => 2],
    'Rueda'          => ['velocidad' => 5],
    'Trampa'         => ['defensa' => 6],
    'Hacha'          => ['ataque' => 5, 'suerte' => 4],
    'Carro'          => ['capacidad' => 7, 'velocidad' => 3],
    'Traje de Malla' => ['defensa' => 6],
    'Espada'         => ['ataque' => 8],
    'Escudo'         => ['defensa' => 8],
    'Vendaje'        => ['salud' => 4],
    'Poción'         => ['salud' => 6, 'ingenio' => 2],
    'Ración'         => ['salud' => 5, 'capacidad' => 2],
    'Vidrio'         => ['ingenio' => 2],
    'Catalejo'       => ['ingenio' => 4, 'suerte' => 3],
    'Núcleo Estelar' => ['ataque' => 8, 'defensa' => 8, 'salud' => 8],
    'Pólvora'        => ['ataque' => 4],
    'Cañón'          => ['ataque' => 9],
];
