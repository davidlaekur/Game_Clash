<?php

/*
|--------------------------------------------------------------------------
| Adyacencia de zonas (fronteras del mapa)
|--------------------------------------------------------------------------
|
| Define qué territorios limitan entre sí. Es la ÚNICA fuente de verdad:
|   - el backend la usa para validar ataques (ZoneService::zonesAdjacent)
|   - el mapa la usa para dibujar las líneas de conexión (WarMap)
|
| La clave es la coordenada de cada zona "latitude,longitude". El grafo debe
| ser SIMÉTRICO: si A lista a B, B debe listar a A.
|
| Generado a partir de las posiciones reales de las zonas en el mapa, así que
| las fronteras coinciden con lo que se ve. Edítalo a mano cuando quieras
| (p.ej. abrir una ruta marítima a una isla, o cerrar un paso de montaña).
|
*/

return [
    '0,0' => ['1,0', '1,1', '1,4', '2,4'],
    '1,0' => ['0,0', '0,4', '1,1', '1,4'],
    '2,0' => ['0,1', '1,1', '2,2'],
    '0,1' => ['1,3', '2,0', '2,3'],
    '1,1' => ['0,0', '0,3', '0,4', '1,0', '2,0', '2,2'],
    '2,1' => ['0,2', '1,2', '1,5', '2,4'],
    '0,2' => ['2,1', '2,2', '2,3', '2,4'],
    '1,2' => ['0,5', '1,5', '2,1', '2,5'],
    '2,2' => ['0,2', '1,1', '2,0', '2,4'],
    '0,3' => ['0,4', '1,1', '1,3'],
    '1,3' => ['0,1', '0,3', '0,4'],
    '2,3' => ['0,1', '0,2', '1,5'],
    '0,4' => ['0,3', '1,0', '1,1', '1,3', '1,4'],
    '1,4' => ['0,0', '0,4', '1,0'],
    '2,4' => ['0,0', '0,2', '2,1', '2,2'],
    '0,5' => ['1,2', '1,5', '2,5'],
    '1,5' => ['0,5', '1,2', '2,1', '2,3', '2,5'],
    '2,5' => ['0,5', '1,2', '1,5'],
];
