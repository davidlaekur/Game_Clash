# Memoria de Inventos — World of Laraveland

Fuente de verdad para el balance de inventos. De aquí salen las fórmulas y los
seeders. Objetivo: que TODO tenga sentido común (un invento superior siempre es
mejor que sus partes; el material y los inventos previos importan; nada gratis).

---

## 1. Problemas actuales que resuelve

- 🔴 **Espada de oro (efi 100 / pts 10) supera al Núcleo Estelar (80 / 8)**: hoy
  el material manda por encima del nivel/élite. Debe ser al revés.
- 🔴 **Un invento "superior" puede ser más débil que su prerequisito** (Arco
  necesita Lanza, pero una Lanza de metal pega más que un Arco de madera).
- 🔴 **Un invento hecho de N previos NO es más potente que la suma** de esos previos.
- 🔴 **Se gasta 1 unidad de cada material** sea oro o cáñamo (sin economía).
- 🔴 **Recetas sin cantidades** (el Carro debería pedir ≥2 ruedas; la Malla, cuerda + metal).

---

## 2. Fórmula coherente de poder

El poder de un invento se compone de TRES factores que se multiplican:

| Factor | Fórmula | Rango |
|---|---|---|
| **Nivel** | `0.8 + nivel × 0.2` | L1=1.0 · L2=1.2 · L3=1.4 · **L4=1.6** |
| **Material** | `0.7 + √(densidad)/5` (acotado) | 0.85 (ligero) → 1.6 (oro) → **1.7 (estelar)** |
| **Previos** | `1 + (media eficiencia de inventos previos / 100) × 0.4` | 1.0 (sin previos) → 1.4 (previos de élite) |

```
statFactor = factor_nivel × factor_material × factor_previos      (máx 3.0)
stat_final = round(stat_base × statFactor)                        (mín 1)
eficiencia = clamp(round(20 × factor_nivel × factor_material × factor_previos), 10, 100)
puntos     = clamp(round(nivel × factor_material × factor_previos), nivel, 20)
```

**Por qué funciona:**
- **Más nivel** → siempre más fuerte (factor_nivel).
- **Mejor material** → más fuerte (factor_material). El **Estelar es el tope**.
- **Mejores inventos previos** → el final hereda su calidad (factor_previos). Así
  un Arco hecho de una Lanza buena ES mejor que la Lanza; y un invento de 4 previos
  supera a la suma porque añade su base + el bonus de previos.

> Ajuste clave: **Aleación estelar densidad 22** (por encima del oro 19.3) → el
> Núcleo Estelar y el arma de nivel 4 son la cúspide, no una espada de oro.

---

## 3. Cantidades de material (los baratos cuestan más)

```
cantidad = max(1, round(coste_nivel × (3.0 / densidad_material)))
coste_nivel: L1=2 · L2=3 · L3=4 · L4=5
```

Ejemplos (densidad ref = 3 ≈ roca):
- **Cuerda** (L1, Fibra cáñamo d=1.5): 2 × (3/1.5) = **4 de fibra**.
- **Espada** (L3, Metal hierro d=7.9): 4 × (3/7.9) ≈ **2 de metal**.
- **Espada** (L3, Oro d=19.3): 4 × (3/19.3) ≈ **1 de oro** (pero da más stats).

Así gastas mucho material ligero/barato y poco material denso/valioso. Coherente.

---

## 4. Tabla de inventos (recetas propuestas)

`mat` = familia(s) admitidas · `prev` = inventos previos (con cantidad) · stats base.

### Nivel 1 — básicos
| Invento | mat | prev | stats base | para qué |
|---|---|---|---|---|
| Piedra Afilada | Roca/Mineral | — | ataque 5 | base de armas/herramientas |
| Cuerda | Fibra | — | ingenio 3, velocidad 2 | base de muchas recetas |
| Fuego | Madera (+Fósforo) | — | ingenio 4, defensa 3 | forja, cocina |
| Cesta | Fibra/Madera | — | capacidad 5, suerte 2 | capacidad de inventario |
| Rueda | Madera/Metal | — | velocidad 5 | transporte |
| Vidrio | Arena | — | ingenio 2 | óptica |
| Vendaje | Fibra/Orgánico | — | salud 4 | sustento |
| Ración | Orgánico | — | salud 5, capacidad 2 | sustento |

### Nivel 2 — intermedios
| Invento | mat | prev | stats base | para qué |
|---|---|---|---|---|
| Lanza | Madera/Roca/Metal | Piedra Afilada + Cuerda | ataque 6, defensa 2 | arma |
| Arco y Flecha | Madera/Fibra | Lanza + Cuerda×2 | ataque 7, defensa 3 | arma (supera a la lanza) |
| Hacha | Roca/Metal | Piedra Afilada | ataque 5, suerte 4 | arma + herramienta (talar→carro) |
| Escudo | Madera/Metal | Cuerda | defensa 8 | armadura |
| Traje de Malla | Metal | Cuerda + (metal×2) | defensa 6, salud 2 | armadura |
| Trampa | — | Cuerda + Cesta + Arco y Flecha | defensa 6 | defensa de zona |
| Poción | Orgánico | Fuego | salud 6, ingenio 2 | sustento (consumible) |
| Catalejo | Madera/Metal | Vidrio×2 + Cuerda | ingenio 4, suerte 3 | exploración |
| Pólvora | Madera (+Fósforo×2) | Fuego | ataque 4 | explosivo (base del cañón) |

### Nivel 3 — avanzados
| Invento | mat | prev | stats base | para qué |
|---|---|---|---|---|
| Carro | Madera/Metal | Rueda×2 + Cuerda + Cesta + Hacha | capacidad 7, velocidad 3 | logística |
| Espada | Metal | Fuego + Piedra Afilada | ataque 8 | mejor arma craftable |
| Cañón | Metal | Pólvora×2 + Carro | ataque 9 | arma de asedio |
| Núcleo Estelar | Estelar | — (material de aventura) | ataque 8, defensa 8, salud 8 | élite (todo terreno) |

### Nivel 4 — demoledor (NUEVO)
| Invento | mat | prev | stats base | para qué |
|---|---|---|---|---|
| **Aniquilador** | Estelar/Metal | Núcleo Estelar + Cañón | ataque 14, defensa 6 | arma definitiva |

---

## 5. Nichos de material (que todos sirvan)

| Familia | Nicho |
|---|---|
| **Metal** (hierro, acero, cobre, estaño, plata, oro, plomo) | armas y armaduras; los más densos (oro, plata) = más stats pero raros |
| **Roca / Mineral** | herramientas (piedra afilada, hacha), Fósforo→pólvora |
| **Madera** | estructuras: rueda, carro, arco, escudo, fuego |
| **Fibra** | cuerda, cesta, vendaje |
| **Orgánico** (plantas, fauna) | sustento → salud (vendaje, ración, poción) |
| **Arena** | vidrio → catalejo/óptica |
| **Estelar** | élite (Núcleo, Aniquilador) — solo de aventuras |

> Pendiente de afinar: dar a cada metal/mineral concreto un matiz (acero ideal
> para espada, cobre para…); de momento el nicho es por familia + densidad.

---

## 6. Combate: qué stat sirve para qué

- **ataque** → atacar zonas.  **defensa + salud** → defender.
- **velocidad** → iniciativa (suma al ataque).  **suerte** → mejora la tirada.
- **capacidad** → inventario.  **ingenio** → acelera acciones (forjar/explorar/recolectar).

Conclusión: los inventos de combate son los de ataque/defensa/salud; el resto
(cesta, rueda, catalejo, carro…) dan ventajas de utilidad, no de pelea directa.

---

## 7. Cambios de código para implementar esto

1. `InventionPointsService`: nueva fórmula (factor_nivel × factor_material × factor_previos)
   → devolver points/efficiency/statFactor. Necesita el nivel y la calidad de los previos.
2. `config/material_value` o densidades: **Aleación estelar densidad 22**.
3. Recetas con **cantidad de material** (campo `material_qty` por nivel) y **cantidad de prerequisitos** (ya existe `Need.quantity`, hay que exigirla y consumirla).
4. `InventionController::store`: consumir la cantidad correcta de material y de cada prerequisito; pasar la calidad de previos al cálculo.
5. Nuevo invento **Aniquilador** (L4) + su `Need` (Núcleo + Cañón) + stats en `config/invention_stats`.
6. `config/invention_stats`: añadir Aniquilador; revisar Malla (+salud 2).

---

_Este documento es editable: ajusta recetas/stats aquí y luego lo trasladamos al código._
