import { useMemo, useRef, useState, useCallback, useEffect } from "react";

/**
 * Mapa de campaña: imagen del mundo de fondo con las zonas como marcadores
 * clicables posicionados según sus coordenadas. Pan y zoom contenidos en el
 * lienzo. Props: zones, showUrlBase, worldMap, worldW, worldH.
 */

// Reparto por defecto para zonas sin ancla definida (fracción 0..1).
const AREA = { x0: 0.12, y0: 0.18, x1: 0.88, y1: 0.82 };

// Posición de cada zona sobre la tierra del mapa, indexada por "lat,lon".
const ANCHORS = {
    "0,0": { x: 0.30, y: 0.34 },
    "1,0": { x: 0.31, y: 0.46 },
    "2,0": { x: 0.46, y: 0.55 },
    "0,1": { x: 0.52, y: 0.74 },
    "1,1": { x: 0.40, y: 0.55 },
    "2,1": { x: 0.64, y: 0.15 },
    "0,2": { x: 0.56, y: 0.33 },
    "1,2": { x: 0.79, y: 0.39 },
    "2,2": { x: 0.50, y: 0.41 },
    "0,3": { x: 0.37, y: 0.79 },
    "1,3": { x: 0.43, y: 0.87 },
    "2,3": { x: 0.61, y: 0.56 },
    "0,4": { x: 0.30, y: 0.67 },
    "1,4": { x: 0.15, y: 0.46 },
    "2,4": { x: 0.45, y: 0.13 },
    "0,5": { x: 0.81, y: 0.73 },
    "1,5": { x: 0.72, y: 0.48 },
    "2,5": { x: 0.83, y: 0.82 },
};

function ownClass(z) {
    if (z.ownership === "mine") return "mine";
    if (z.ownership === "enemy") return "enemy";
    return "neutral";
}
function landIcon(landscape) {
    const l = (landscape || "").toLowerCase();
    if (l.includes("bosque")) return "🌲";
    if (l.includes("selva") || l.includes("jungla")) return "🌴";
    if (l.includes("desierto")) return "🏜️";
    if (l.includes("montaña") || l.includes("montana")) return "⛰️";
    if (l.includes("polo") || l.includes("glaciar")) return "❄️";
    if (l.includes("volcán") || l.includes("volcan")) return "🌋";
    if (l.includes("pradera") || l.includes("meseta")) return "🌾";
    if (l.includes("playa") || l.includes("isla")) return "🏝️";
    if (l.includes("pantano") || l.includes("ciénaga") || l.includes("cienaga")) return "🪵";
    if (l.includes("cueva")) return "🕳️";
    return "🏰";
}

export default function WarMap({
    zones = [],
    showUrlBase = "/zones",
    worldMap = "",
    worldW = 1408,
    worldH = 768,
}) {
    const wrapRef = useRef(null);
    const [view, setView] = useState({ x: 0, y: 0, k: 1 });
    const fit = useRef({ x: 0, y: 0, k: 1 });
    const drag = useRef({ active: false, sx: 0, sy: 0, ox: 0, oy: 0, moved: 0 });
    const [hover, setHover] = useState(null);

    // posiciones de cada zona sobre la imagen (px en coords del mundo).
    // Prioridad: ancla en tierra (ANCHORS) por coordenada; si no, rejilla.
    const points = useMemo(() => {
        if (!zones.length) return [];
        const lons = zones.map((z) => z.lon), lats = zones.map((z) => z.lat);
        const minLon = Math.min(...lons), maxLon = Math.max(...lons);
        const minLat = Math.min(...lats), maxLat = Math.max(...lats);
        const spanLon = Math.max(1, maxLon - minLon);
        const spanLat = Math.max(1, maxLat - minLat);
        const ax0 = AREA.x0 * worldW, ax1 = AREA.x1 * worldW;
        const ay0 = AREA.y0 * worldH, ay1 = AREA.y1 * worldH;
        return zones.map((z) => {
            const a = ANCHORS[`${z.lat},${z.lon}`];
            if (a) return { zone: z, x: a.x * worldW, y: a.y * worldH };
            // fallback: rejilla dentro del área de tierra
            return {
                zone: z,
                x: ax0 + ((z.lon - minLon) / spanLon) * (ax1 - ax0),
                y: ay0 + ((z.lat - minLat) / spanLat) * (ay1 - ay0),
            };
        });
    }, [zones, worldW, worldH]);

    const computeFit = useCallback(() => {
        const el = wrapRef.current;
        if (!el) return null;
        const k = Math.min(el.clientWidth / worldW, el.clientHeight / worldH);
        return {
            k,
            x: (el.clientWidth - worldW * k) / 2,
            y: (el.clientHeight - worldH * k) / 2,
        };
    }, [worldW, worldH]);

    // Limita el desplazamiento para que el mapa nunca deje hueco en el lienzo.
    const clampView = useCallback((v) => {
        const el = wrapRef.current;
        if (!el) return v;
        const vw = el.clientWidth, vh = el.clientHeight;
        const mw = worldW * v.k, mh = worldH * v.k;
        const minX = Math.min(0, vw - mw), maxX = Math.max(0, vw - mw);
        const minY = Math.min(0, vh - mh), maxY = Math.max(0, vh - mh);
        return {
            ...v,
            x: Math.min(maxX, Math.max(minX, v.x)),
            y: Math.min(maxY, Math.max(minY, v.y)),
        };
    }, [worldW, worldH]);

    useEffect(() => {
        const f = computeFit();
        if (f) { fit.current = f; setView(f); }
        const onResize = () => { const f2 = computeFit(); if (f2) { fit.current = f2; setView(f2); } };
        window.addEventListener("resize", onResize);
        return () => window.removeEventListener("resize", onResize);
    }, [computeFit]);

    // El puntero se captura solo al arrastrar (>6px), no en pointerdown,
    // para no interceptar el click en los marcadores.
    const onPointerDown = (e) => {
        drag.current = { active: true, sx: e.clientX, sy: e.clientY, ox: view.x, oy: view.y, moved: 0, captured: false, pid: e.pointerId, el: e.currentTarget };
    };
    const onPointerMove = (e) => {
        if (!drag.current.active) return;
        const dx = e.clientX - drag.current.sx, dy = e.clientY - drag.current.sy;
        drag.current.moved = Math.abs(dx) + Math.abs(dy);
        if (drag.current.moved > 6) {
            if (!drag.current.captured) {
                drag.current.captured = true;
                drag.current.el?.setPointerCapture?.(drag.current.pid);
            }
            setView((v) => clampView({ ...v, x: drag.current.ox + dx, y: drag.current.oy + dy }));
        }
    };
    const onPointerUp = () => { drag.current.active = false; };

    const onWheel = useCallback((e) => {
        e.preventDefault();
        setView((v) => clampView({ ...v, k: Math.min(3, Math.max(fit.current.k, v.k * (1 - e.deltaY * 0.0012))) }));
    }, [clampView]);
    const zoomIn = () => setView((v) => clampView({ ...v, k: Math.min(3, v.k * 1.25) }));
    const resetView = () => setView(fit.current);

    const openZone = (z) => { if (drag.current.moved <= 6) window.location.href = `${showUrlBase}/${z.id}`; };

    return (
        <div
            className="warmap-stage"
            ref={wrapRef}
            onPointerDown={onPointerDown}
            onPointerMove={onPointerMove}
            onPointerUp={onPointerUp}
            onPointerLeave={onPointerUp}
            onWheel={onWheel}
        >
            <div
                className="warmap-canvas"
                style={{ transform: `translate(${view.x}px, ${view.y}px) scale(${view.k})`, width: worldW, height: worldH }}
            >
                {/* fondo: el mapa-mundo único */}
                <img className="warmap-world" src={worldMap} width={worldW} height={worldH} alt="Mapa del mundo" draggable="false" />

                {/* marcadores de zona */}
                {points.map((p) => {
                    const z = p.zone;
                    const cls = `warmap-marker warmap-marker--${ownClass(z)} ${z.current ? "is-current" : ""} ${hover === z.id ? "is-hover" : ""}`;
                    return (
                        <button
                            key={z.id}
                            type="button"
                            className={cls}
                            style={{ left: p.x, top: p.y }}
                            onClick={() => openZone(z)}
                            onMouseEnter={() => setHover(z.id)}
                            onMouseLeave={() => setHover((h) => (h === z.id ? null : h))}
                            title={`${z.name} — ${z.landscape}`}
                        >
                            {z.current && <span className="warmap-marker__here">★</span>}
                            <span className="warmap-marker__pin">
                                <span className="warmap-marker__icon">{landIcon(z.landscape)}</span>
                                <span className="warmap-marker__def">{z.defense}</span>
                            </span>
                            <span className="warmap-marker__label">
                                <span className="warmap-marker__name">{z.name}</span>
                                <span className={`warmap-marker__team warmap-marker__team--${ownClass(z)}`}>
                                    {z.teamName || "Neutral"}
                                </span>
                            </span>
                        </button>
                    );
                })}
            </div>

            <div className="warmap-controls">
                <button onClick={zoomIn} title="Ampliar" aria-label="Ampliar">＋</button>
                <button onClick={resetView} title="Vista completa" aria-label="Vista completa">⤢</button>
            </div>
            <p className="warmap-hint">Arrastra para recorrer · rueda o ＋ para ampliar · clic en un territorio para entrar</p>
        </div>
    );
}
