<?php

/*
 * Valor (0..1) de cada familia de material para el reparto ponderado.
 * Más valor = familia más codiciada (densa, mejores stats). El reparto hace
 * que, con PROBABILIDAD, las familias valiosas caigan en zonas de más defensa
 * orográfica, y las abundantes en zonas de menos. Es probabilístico para que
 * cada partida sea distinta.
 */

return [
    'Metal'    => 1.00,
    'Estelar'  => 1.00,
    'Mineral'  => 0.60,
    'Roca'     => 0.50,
    'Arena'    => 0.35,
    'Orgánico' => 0.30,
    'Fibra'    => 0.25,
    'Madera'   => 0.22,
];
