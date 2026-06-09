const BASE = "/audio/";
const TRACKS = {
    theme: "music/theme.mp3",
    aventura: "sfx/aventura.mp3",
    batalla: "sfx/batalla2.mp3",
};
const KEY_ON = "col_audio_on";
const KEY_TRACK = "col_audio_track";
const KEY_AT = "col_audio_at";
const VOL = 0.4;
const INTRO_SKIP = { aventura: 14 }; // saltar intro silenciosa de algunas pistas

let audio = null;
let currentTrack = null;

const on = () => localStorage.getItem(KEY_ON) === "1";

// Música de la pantalla actual: aventura/batalla si la página lo declara,
// si no, el tema de fondo general.
function pageTrack() {
    const m = document.body.dataset.music;
    return (m && TRACKS[m]) ? m : "theme";
}

function ensure(track, at) {
    if (audio) { audio.pause(); audio.ontimeupdate = null; }
    audio = new Audio(BASE + TRACKS[track]);
    currentTrack = track;
    audio.loop = true;
    audio.volume = VOL;
    audio.addEventListener("error", () => {});
    // el seek solo es fiable tras cargar los metadatos; antes se ignora y
    // arrancaría desde 0 (causa del "reinicio" al navegar).
    let ready = !(at > 0);
    if (at > 0) {
        const seek = () => { try { audio.currentTime = at; } catch (e) {} ready = true; };
        if (audio.readyState >= 1) seek();
        else audio.addEventListener("loadedmetadata", seek, { once: true });
    }
    // persistir posición para reanudar el fondo sin cortes al navegar.
    // No guardar hasta haber aplicado el seek, para no pisar la posición con 0.
    audio.ontimeupdate = () => {
        if (!ready) return;
        localStorage.setItem(KEY_TRACK, track);
        localStorage.setItem(KEY_AT, String(audio.currentTime));
    };
    return audio;
}

function start() {
    if (!on()) return;
    // ya sonando la pista correcta: no recrear (evita música doblada)
    if (audio && !audio.paused && currentTrack === pageTrack()) return;
    const want = pageTrack();
    const savedTrack = localStorage.getItem(KEY_TRACK);
    const savedAt = parseFloat(localStorage.getItem(KEY_AT) || "0") || 0;

    let at = 0;
    if (want === "theme" && savedTrack === "theme") {
        at = savedAt;            // reanudar el fondo donde iba (sin cortes)
    } else if (INTRO_SKIP[want]) {
        at = INTRO_SKIP[want];   // saltar intro silenciosa (p.ej. aventura)
    }
    ensure(want, at);
    audio.play().catch(() => {});
}

function setOn(state) {
    localStorage.setItem(KEY_ON, state ? "1" : "0");
    const btn = document.getElementById("music-toggle");
    if (btn) btn.setAttribute("aria-pressed", state ? "true" : "false");
    if (state) { localStorage.setItem(KEY_TRACK, "theme"); localStorage.setItem(KEY_AT, "0"); start(); }
    else if (audio) audio.pause();
}

function init() {
    const btn = document.getElementById("music-toggle");
    if (btn) {
        btn.setAttribute("aria-pressed", on() ? "true" : "false");
        btn.addEventListener("click", () => setOn(!on()));
    }
    if (on()) {
        // intentar reproducir ya (tras navegar por un clic suele permitirse);
        // si el navegador bloquea el autoplay, reintentar al primer puntero.
        start();
        const go = () => { start(); document.removeEventListener("pointerdown", go); };
        document.addEventListener("pointerdown", go, { once: true });
    }
}

if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
else init();
