# Ayuda — Cómo se juega a *World of Laraveland*

Dossier para entender el juego. Es la base de la futura página de ayuda dentro del
juego. (El detalle técnico de balance de inventos vive en [inventos.md](inventos.md).)

---

## 1. Objetivo

Dos facciones se disputan el mapa: **Guardianes de Laraveland** y **Legión de Ítaca**.
Ganas la guerra cuando **expulsas al rival del mapa** (se queda con 0 territorios) y tu
equipo domina la **mayoría** de las zonas. → ver §9.

## 2. El bucle del juego

1. **Recolectar** materias primas en las zonas.
2. **Forjar** inventos con esas materias (armas, herramientas, curación…).
3. **Conquistar** zonas (explorando las neutrales, atacando las enemigas) y **defender** las tuyas.
4. Ganar **méritos** por pelear, conquistar y forjar.
5. Los méritos suben tu **rango** y desbloquean la **mina**, la **aventura**…
6. Las **aventuras** dan materia **estelar** → inventos de **élite**.

## 3. Materiales y densidad

- Cada material pertenece a una **familia** (Metal, Mineral, Roca, Fibra, Madera, Orgánico…).
- Cada invento solo acepta **ciertas familias** (no se hace una cuerda con diamantes).
- La **densidad** del material decide dos cosas:
  - **Probabilidad y cantidad**: lo denso/valioso es más **raro** (aparece menos y se agota antes).
  - **Potencia del invento**: a más densidad, **más ataque/defensa**. Elegir material es una decisión real.
- En **orgánicos**, la "densidad" representa el **valor nutritivo/medicinal** (raíz curativa, oso, bisonte → raros y curan más; bayas, setas, conejo → comunes y flojos).
- El **reparto** por el mapa es **probabilístico** y ligado a la dificultad: zonas de
  defensa orográfica alta (montaña, glaciar, volcán) tienden a dar familias densas y
  valiosas; las llanas (pradera, bosque, isla) dan abundantes y ligeras. Cada partida cambia.

## 4. Inventos

- Se forjan en una zona propia con el botón **Inventar**.
- Algunos necesitan **inventos previos** (p. ej. Espada ← Fuego + Piedra Afilada; Catalejo ← Vidrio + Cuerda; Cañón ← Pólvora + Carro).
- Algunos necesitan **materiales extra** por nombre (p. ej. Fuego = Madera + Fósforo).
- La **materia estelar** ("Aleación estelar") **no se recolecta**: solo se gana en aventuras y
  habilita el invento de élite **Núcleo Estelar**.
- Tras forjar puedes pulsar **"Forjar otro"** sin salir de la pantalla.

## 5. Estadísticas (stats)

Los inventos que llevas suman stats:

| Stat | Para qué sirve |
|------|----------------|
| **Ataque** | Fuerza al atacar una zona. |
| **Defensa** | Aguante al defender. |
| **Salud** | Refuerza la defensa. Viene del **sustento** (orgánicos y Vendaje/Ración/Poción), no de la armadura. |
| **Velocidad** | Iniciativa en combate. |
| **Suerte** | Mejora la tirada de ataque (hasta +30%). |
| **Ingenio** | Acorta el tiempo de explorar/recolectar/forjar (hasta −40%). |
| **Capacidad** | Espacio de inventario. |

## 6. Combate

- Atacas una zona **enemiga adyacente** desde una zona tuya, con el botón **Atacar**.
- **Ataque** (posicional) = (Σ ataque + Σ velocidad de **toda tu guarnición** en la zona de origen) × (azar + suerte).
- **Defensa** = defensa de la zona + (Σ defensa+salud de los defensores + refuerzo de equipo + bonus por atrincherarse) × azar.
- El **azar** (clima, enfermedades) varía en cada combate: a propósito, para que no todo sea calculado.
- El combate se resuelve **al instante** al atacar.

**Si ganas el ataque:** la zona pasa a tu equipo y la guarnición atacante gana **+20 méritos**.

**Si fallas el ataque:** pierdes ~**1/3 de tus inventos** (los de menor valor). Atacar en
campo enemigo es arriesgado.

**Si defiendes y ganas:** conservas la zona y los defensores ganan **+5 exp y +10 méritos**.

**Si defiendes y pierdes (te conquistan):** ver §7.

**Botín:** lo que sueltan los defensores derrotados (su ~1/3 de materiales) **lo saquea
la guarnición atacante** y se **reparte a partes iguales** entre los que lanzaron el
ataque. Conquistar recompensa.

## 7. Qué pasa con los defensores al perder una zona

El riesgo del juego está en el **territorio**, no en tu inventario. Por eso, al perder
una zona defendiendo:

- **Conservas tus inventos** (tu maquinaria de guerra se salva).
- **Pierdes ~1/3 de tus materias primas** (sueltas suministros al ser arrollado).
- Quedas **Herido**: −20% a los stats de combate durante un rato. **Se cura solo** con el tiempo.
- Te **repliegas** automáticamente a una zona propia adyacente (o a cualquiera de tu
  equipo; si no te queda ninguna, quedas fuera del mapa "en retirada").

El verdadero castigo es **perder la fortaleza**: como la defensa orográfica suele ser
alta, recuperarla luego es muy difícil.

## 8. Rendir (abandonar) una zona

A veces conviene **replegarse** antes que perderlo todo. Estando **dentro** de una zona
tuya puedes **Rendir zona**:

- La zona pasa a **NEUTRAL** (no se la regalas al enemigo: le niegas la fortaleza).
- Tu guarnición se retira **sana** (sin perder materiales ni quedar herida).
- **Pierdes la mina** de esa zona y queda **en revuelta** unos minutos (nadie puede reclamarla).
- **No puedes rendir tu última zona** (tu equipo quedaría eliminado).

Diferencia clave: si te **conquistan**, la fortaleza pasa al **enemigo**; si **rindes**,
queda **neutral** a repartir. Rendir es "tierra quemada": *la voy a perder igual, que no se la quede él*.

## 9. Cómo se gana la partida

Gana el equipo que cumple **las dos cosas a la vez**:

1. El **rival se queda con 0 territorios** (lo has expulsado del mapa).
2. Tú controlas **al menos la mitad** del mapa.

(La condición de la mitad es un seguro para que nadie "gane" al principio si la partida
arranca con casi todo neutral.)

**Al ganar:** la partida se **congela** (no se puede atacar ni explorar) y en la pantalla
principal aparece un **podio con los 3 mejores** jugadores por Gloria. El **admin** inicia
entonces una **nueva partida**: se archiva el podio en el **Salón de la Fama** (visible en
el Ranking) y se reinicia todo (zonas, inventarios, méritos y gloria a cero). Cada partida
es independiente, pero los campeones quedan para siempre en el Salón de la Fama.

## 10. Zonas vivas: recursos, minas y eventos

- Los recursos **se regeneran** con el tiempo (comunes rápido, raros lento).
- **Mina** (zona propia, rango Soldado, cuesta 10 metal + 15 madera): se construye en
  2º plano (10 min) y **triplica la regeneración**.
- **Eventos de mundo** que aparecen y caducan solos:
  - **Tormenta**: baja la defensa de la zona (buen momento para atacarla).
  - **Bonanza**: acelera mucho la regeneración.
  - **Plaga**: frena la regeneración.

## 11. Rangos y méritos

- **Méritos** (monedero): moneda **gastable** que ganas peleando, conquistando y forjando,
  y que **gastas** (p. ej. 100 para emprender una aventura).
- **Gloria** (carrera): los méritos **acumulados** de toda la partida. **Solo suben, nunca
  bajan** aunque gastes méritos. Definen tu **rango** y tu puesto en el **ranking**.
  - Recluta → Soldado (30) → Veterano (100) → Héroe (250) → Leyenda (600).
  - Así, gastar en aventuras te cuesta dinero pero **nunca tu prestigio**: un buen jugador
    sigue arriba en el ranking aunque vacíe el monedero.
- Puertas por rango: **Mina** = Soldado; **Aventura** = Veterano (y cuesta 100 méritos partir).

## 12. Aventuras

- Desde el mapa, si eres **Veterano** y tienes **100 méritos**.
- Preguntas de un escenario: **un intento** por escenario, con feedback (sin revelar la
  respuesta correcta); avances aciertes o no.
- Completarla da materia **estelar** para inventos de élite.

## 13. El mundo avanza solo

No hay que esperar turnos: las acciones de todos terminan en **2º plano** en cuanto
alguien carga una página. El mapa y el panel lateral se **refrescan en vivo** sin
recargar (la música no se corta).
