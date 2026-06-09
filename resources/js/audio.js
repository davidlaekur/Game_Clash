const BASE = "/audio/";
const TRACKS = {
    theme: "music/theme.mp3",
    move: "sfx/move.mp3",
    explore: "sfx/explore.mp3",
    collect: "sfx/collect.mp3",
    invent: "sfx/invent.mp3",
    attack: "sfx/attack.mp3",
};
const KEY_ON = "col_audio_on";
const KEY_TRACK = "col_audio_track";
const KEY_AT = "col_audio_at";
const VOL = 0.4;

let current = null;

const on = () => localStorage.getItem(KEY_ON) === "1";

function fadeOut(audio, ms = 500) {
    if (!audio) return;
    const step = audio.volume / (ms / 50);
    const t = setInterval(() => {
        audio.volume = Math.max(0, audio.volume - step);
        if (audio.volume <= 0.01) { audio.pause(); clearInterval(t); }
    }, 50);
}

function fadeIn(audio, ms = 500) {
    audio.volume = 0;
    audio.play().catch(() => {});
    const step = VOL / (ms / 50);
    const t = setInterval(() => {
        audio.volume = Math.min(VOL, audio.volume + step);
        if (audio.volume >= VOL) clearInterval(t);
    }, 50);
}

function play(name, { at = 0, fade = true } = {}) {
    if (!on() || !TRACKS[name]) return;
    const a = new Audio(BASE + TRACKS[name]);
    a.loop = true;
    a.addEventListener("error", () => {});
    if (at > 0) a.currentTime = at;
    localStorage.setItem(KEY_TRACK, name);
    if (fade) { fadeOut(current); fadeIn(a); }
    else { a.volume = VOL; a.play().catch(() => {}); }
    current = a;
    a.ontimeupdate = () => localStorage.setItem(KEY_AT, String(a.currentTime));
}

export function playTrack(name) {
    if (!on()) return;
    localStorage.setItem(KEY_AT, "0");
    play(name, { at: 0 });
}

function resumeOrTheme() {
    if (!on()) return;
    const name = localStorage.getItem(KEY_TRACK) || "theme";
    const at = parseFloat(localStorage.getItem(KEY_AT) || "0") || 0;
    play(TRACKS[name] ? name : "theme", { at, fade: false });
}

function setAudio(state) {
    localStorage.setItem(KEY_ON, state ? "1" : "0");
    const btn = document.getElementById("music-toggle");
    if (btn) btn.setAttribute("aria-pressed", state ? "true" : "false");
    if (state) { localStorage.setItem(KEY_TRACK, "theme"); play("theme", { at: 0 }); }
    else { fadeOut(current); }
}

function init() {
    const btn = document.getElementById("music-toggle");
    if (btn) {
        btn.setAttribute("aria-pressed", on() ? "true" : "false");
        btn.addEventListener("click", () => setAudio(!on()));
    }
    if (on()) {
        const start = () => { resumeOrTheme(); document.removeEventListener("pointerdown", start); };
        document.addEventListener("pointerdown", start, { once: true });
    }
    document.querySelectorAll("[data-sfx]").forEach((form) => {
        form.addEventListener("submit", () => {
            if (on()) localStorage.setItem(KEY_TRACK, form.dataset.sfx);
        });
    });
}

if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
else init();
