import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
import { initCursor } from './cursor';
import { initReveal, initSpotlight, registerComponents } from './effects';
import { initNetworks } from './network';

Alpine.plugin(collapse);
Alpine.plugin(intersect);

/**
 * Theme store: the <head> inline script already applied the right class before
 * first paint (no flash); this keeps Alpine in sync and persists the choice.
 */
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),

    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try {
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        } catch (e) {
            /* storage blocked — theme still switches for this page view */
        }
    },
});

registerComponents(Alpine);

window.Alpine = Alpine;
Alpine.start();

// Module scripts run after the document is parsed, so the DOM is ready here.
initReveal();
initSpotlight();
initNetworks();
initCursor();
