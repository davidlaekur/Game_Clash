/**
 * Sistema de "islas" React.
 *
 * Permite montar componentes React dentro de cualquier vista Blade sin
 * convertir el proyecto en una SPA. En Blade escribes:
 *
 *   <div data-react-island="WarMap" data-props='@json($payload)'></div>
 *
 * y este módulo monta <WarMap {...payload} /> ahí. El resto del sitio
 * sigue siendo Laravel + Blade. Backend intacto.
 */
import { createRoot } from "react-dom/client";
import WarMap from "./components/WarMap.jsx";

const REGISTRY = {
    WarMap,
};

function mountIslands() {
    document.querySelectorAll("[data-react-island]").forEach((el) => {
        if (el.dataset.mounted === "1") return;
        const name = el.dataset.reactIsland;
        const Component = REGISTRY[name];
        if (!Component) {
            console.warn(`[islands] Componente desconocido: ${name}`);
            return;
        }
        let props = {};
        if (el.dataset.props) {
            try {
                props = JSON.parse(el.dataset.props);
            } catch (e) {
                console.error(`[islands] data-props inválido en ${name}`, e);
            }
        }
        createRoot(el).render(<Component {...props} />);
        el.dataset.mounted = "1";
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mountIslands);
} else {
    mountIslands();
}
