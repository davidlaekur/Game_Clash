<?php

/*
 * Rangos por méritos. Los méritos se ganan destacando en el juego (ganar
 * batallas, conquistar zonas, forjar inventos de élite). El rango desbloquea
 * cosas: construir minas (Soldado) y emprender aventuras (Veterano).
 * Lista ordenada de menor a mayor.
 */

return [
    ['name' => 'Recluta',  'merit' => 0,   'icon' => 'fa-user'],
    ['name' => 'Soldado',  'merit' => 30,  'icon' => 'fa-user-shield'],
    ['name' => 'Veterano', 'merit' => 100, 'icon' => 'fa-medal'],
    ['name' => 'Héroe',    'merit' => 250, 'icon' => 'fa-award'],
    ['name' => 'Leyenda',  'merit' => 600, 'icon' => 'fa-crown'],
];
