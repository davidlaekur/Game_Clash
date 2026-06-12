<?php

/*
 * Eventos de mundo: suceden al azar en las zonas y las cambian durante un rato,
 * para que el mapa esté vivo entre tus acciones. Se generan de forma perezosa
 * (al cargar el mapa) y caducan solos. duration en segundos.
 */

return [
    'tormenta' => [
        'label' => 'Tormenta',
        'icon'  => 'fa-bolt',
        'desc'  => 'La defensa de la zona baja: es el momento de atacar',
        'duration' => 900, // 15 min
    ],
    'bonanza' => [
        'label' => 'Bonanza',
        'icon'  => 'fa-coins',
        'desc'  => 'Los recursos abundan: la zona regenera mucho más rápido',
        'duration' => 900,
    ],
    'plaga' => [
        'label' => 'Plaga',
        'icon'  => 'fa-skull-crossbones',
        'desc'  => 'Los recursos escasean: la zona apenas regenera',
        'duration' => 600, // 10 min
    ],
];
