/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.jsx",
    ],
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            colors: {
                brass: { DEFAULT: "#c9a24b", light: "#f1d27a", dark: "#7a5e1f" },
                wood: { DEFAULT: "#5a3a1e", dark: "#3d2713" },
                parchment: { DEFAULT: "#e8d6a8", dark: "#c9b27e" },
                ink: "#2a1d0e",
                laraveland: "#d8362e",
                mordor: "#1f1f24",
                panel: { DEFAULT: "#1f1810", elevated: "#2a2014" },
            },
            fontFamily: {
                display: ["Cinzel", "serif"],
                script: ["MedievalSharp", "cursive"],
                body: ["Inter", "system-ui", "sans-serif"],
            },
        },
    },
    plugins: [],
};
