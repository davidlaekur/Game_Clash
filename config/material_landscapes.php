<?php

/*
 * Reparto lógico de materiales por bioma: en qué paisajes aparece cada familia
 * de material. Lo usan MaterialSeeder, ZoneController::materialsImportZones y el
 * patch de redistribución. La densidad sigue decidiendo la cantidad (menos
 * densidad => más abundante); esto solo decide DÓNDE aparece cada familia.
 */

// Los biomas de MÁS defensa (montaña, glaciar, volcán, cueva, polo) albergan las
// familias más valiosas (Metal, Mineral, Roca); los de menos defensa (pradera,
// playa, bosque...) las abundantes y ligeras (Fibra, Arena, Orgánico, Madera).
// Así "mejor defensa => mejores materiales" (riesgo/recompensa).
return [
    'Roca'     => ['montaña', 'cueva', 'volcán', 'meseta', 'glaciar', 'polo'],
    'Mineral'  => ['montaña', 'cueva', 'volcán', 'desierto', 'meseta', 'glaciar', 'polo'],
    'Arena'    => ['playa', 'isla', 'desierto'],
    'Metal'    => ['montaña', 'cueva', 'volcán', 'glaciar', 'polo'],
    'Madera'   => ['bosque', 'selva', 'jungla'],
    'Fibra'    => ['pradera', 'ciénaga', 'pantano', 'jungla', 'selva'],
    'Orgánico' => ['bosque', 'selva', 'jungla', 'pradera', 'ciénaga', 'pantano', 'isla'],
];
