/**
 * Small page effects: scroll reveal, card spotlight, and the Alpine
 * components the hero uses (typer, terminal, count-up).
 */

/** Elements with [data-reveal] fade up the first time they scroll into view. */
export function initReveal() {
    const root = document.documentElement;
    const items = document.querySelectorAll('[data-reveal]');

    if (!('IntersectionObserver' in window) || items.length === 0) {
        root.classList.remove('reveal-ready');
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        }
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    items.forEach((item) => observer.observe(item));
    window.__revealStarted = true;
}

/** Feeds the pointer position into .card-hover so its spotlight follows it. */
export function initSpotlight() {
    document.addEventListener('pointermove', (e) => {
        const card = e.target instanceof Element ? e.target.closest('.card-hover') : null;
        if (!card) return;
        const rect = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${e.clientX - rect.left}px`);
        card.style.setProperty('--my', `${e.clientY - rect.top}px`);
    }, { passive: true });
}

const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

export function registerComponents(Alpine) {
    /** Types each word, holds it, deletes it, moves to the next — for ever. */
    Alpine.data('typer', (words = []) => ({
        words: words.filter(Boolean),
        text: '',

        async init() {
            if (this.words.length === 0) return;
            if (reduceMotion() || this.words.length === 1) {
                this.text = this.words[0];
                return;
            }

            for (let i = 0; ; i = (i + 1) % this.words.length) {
                const word = this.words[i];
                for (let c = 1; c <= word.length; c++) {
                    this.text = word.slice(0, c);
                    await wait(70);
                }
                await wait(1700);
                for (let c = word.length; c >= 0; c--) {
                    this.text = word.slice(0, c);
                    await wait(35);
                }
                await wait(300);
            }
        },
    }));

    /**
     * A terminal that types its lines out once it is on screen, then starts over.
     * Lines starting with "$ " are typed as commands; the rest print at once.
     */
    Alpine.data('terminal', (lines = []) => ({
        lines,
        shown: [],
        typing: '',
        started: false,

        async run() {
            if (this.started) return;
            this.started = true;

            if (reduceMotion()) {
                this.shown = [...this.lines];
                return;
            }

            for (;;) {
                this.shown = [];
                for (const line of this.lines) {
                    if (line.startsWith('$ ')) {
                        for (let c = 1; c <= line.length; c++) {
                            this.typing = line.slice(0, c);
                            await wait(38);
                        }
                        this.typing = '';
                        this.shown.push(line);
                        await wait(350);
                    } else {
                        await wait(420);
                        this.shown.push(line);
                    }
                }
                await wait(4500);
            }
        },

        tone(line) {
            if (line.startsWith('$ ')) return 'text-white';
            if (line.startsWith('✓')) return 'text-emerald-400';
            if (line.startsWith('→')) return 'text-brand-300';
            if (line.startsWith('!')) return 'text-accent-400';
            return 'text-slate-400';
        },
    }));

    /** "150+", "99.9%", "24/7": counts the number up, keeps what surrounds it. */
    Alpine.data('countUp', (value = '') => ({
        display: value,

        start() {
            const match = String(value).match(/^(\D*)(\d+(?:\.\d+)?)(.*)$/);
            // "24/7" is a phrase, not a quantity: leave it as written.
            if (!match || match[3].startsWith('/') || reduceMotion()) return;

            const [, prefix, number, suffix] = match;
            const target = parseFloat(number);
            const decimals = number.includes('.') ? number.split('.')[1].length : 0;
            const startedAt = performance.now();
            const duration = 1600;

            const tick = (now) => {
                const t = Math.min(1, (now - startedAt) / duration);
                const eased = 1 - Math.pow(1 - t, 3);
                this.display = prefix + (target * eased).toFixed(decimals) + suffix;
                if (t < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        },
    }));
}
