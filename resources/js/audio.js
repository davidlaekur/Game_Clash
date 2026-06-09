const BASE = "/audio/";
const THEME = "music/theme.mp3";
const TRACKS = {
    move: "sfx/move.mp3",
    explore: "sfx/explore.mp3",
    collect: "sfx/collect.mp3",
    invent: "sfx/invent.mp3",
    attack: "sfx/attack.mp3",
};
const KEY = "col_audio_on";

let current = null;
let currentName = null;

const on = () => localStorage.getItem(KEY) === "1";

function fadeTo(target, vol = 0.4, ms = 600) {
    const prev = current;
    current = target;
    target.volume = 0;
    target.play().catch(() => {});
    const step = vol / (ms / 50);
    const t = setInterval(() => {
        if (target.volume < vol - step) target.volume += step;
        else { target.volume = vol; clearInterval(t); }
        if (prev && prev !== target) {
            prev.volume = Math.max(0, prev.volume - step);
            if (prev.volume <= 0.01) prev.pause();
        }
    }, 50);
}

const make = (src, loop) => {
    const a = new Audio(BASE + src);
    a.loop = loop;
    a.addEventListener("error", () => {});
    return a;
};

const themeAudio = () => make(THEME, true);
let theme = null;

function playTheme() {
    if (!on()) return;
    if (!theme) theme = themeAudio();
    currentName = "theme";
    fadeTo(theme);
}

export function playTrack(name) {
    if (!on() || !TRACKS[name]) return;
    const a = make(TRACKS[name], true);
    currentName = name;
    fadeTo(a);
}

function setAudio(state) {
    localStorage.setItem(KEY, state ? "1" : "0");
    const btn = document.getElementById("music-toggle");
    if (btn) btn.setAttribute("aria-pressed", state ? "true" : "false");
    if (state) playTheme();
    else if (current) { current.pause(); }
}

function init() {
    const btn = document.getElementById("music-toggle");
    if (btn) {
        btn.setAttribute("aria-pressed", on() ? "true" : "false");
        btn.addEventListener("click", () => setAudio(!on()));
    }
    if (on()) {
        const resume = () => { playTheme(); document.removeEventListener("pointerdown", resume); };
        document.addEventListener("pointerdown", resume, { once: true });
    }
    document.querySelectorAll("[data-sfx]").forEach((form) => {
        form.addEventListener("submit", () => playTrack(form.dataset.sfx));
    });
}

if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
else init();
