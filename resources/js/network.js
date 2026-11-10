/**
 * Particle network for <canvas data-network>: drifting nodes joined by lines
 * when close, reaching out to the pointer. Pauses while off screen or in a
 * background tab; with reduced motion it draws a single still frame.
 */

const LINK = 135;
const MOUSE_LINK = 180;

export function initNetworks() {
    document.querySelectorAll('canvas[data-network]').forEach(start);
}

function start(canvas) {
    const ctx = canvas.getContext('2d');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const host = canvas.parentElement;
    const mouse = { x: -9999, y: -9999 };
    let nodes = [];
    let width = 0;
    let height = 0;
    let running = false;
    let visible = true;

    const resize = () => {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = host.clientWidth;
        height = host.clientHeight;
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const count = Math.min(95, Math.round((width * height) / 13000));
        nodes = Array.from({ length: count }, () => ({
            x: Math.random() * width,
            y: Math.random() * height,
            vx: (Math.random() - 0.5) * 0.45,
            vy: (Math.random() - 0.5) * 0.45,
            r: Math.random() * 1.6 + 0.8,
            // One in six is the logo's red, the rest its cyan.
            red: Math.random() < 0.16,
        }));
    };

    const draw = () => {
        ctx.clearRect(0, 0, width, height);

        for (const n of nodes) {
            if (!reduceMotion) {
                n.x += n.vx;
                n.y += n.vy;
                if (n.x < 0 || n.x > width) n.vx *= -1;
                if (n.y < 0 || n.y > height) n.vy *= -1;

                // Gently drawn towards the pointer when it is near.
                const dx = mouse.x - n.x;
                const dy = mouse.y - n.y;
                const d = Math.hypot(dx, dy);
                if (d < MOUSE_LINK && d > 1) {
                    n.x += (dx / d) * 0.25;
                    n.y += (dy / d) * 0.25;
                }
            }
        }

        for (let i = 0; i < nodes.length; i++) {
            const a = nodes[i];
            for (let j = i + 1; j < nodes.length; j++) {
                const b = nodes[j];
                const d = Math.hypot(a.x - b.x, a.y - b.y);
                if (d < LINK) {
                    ctx.strokeStyle = `rgba(19, 181, 203, ${(1 - d / LINK) * 0.35})`;
                    ctx.lineWidth = 0.7;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }

            const dm = Math.hypot(a.x - mouse.x, a.y - mouse.y);
            if (dm < MOUSE_LINK) {
                ctx.strokeStyle = `rgba(229, 35, 60, ${(1 - dm / MOUSE_LINK) * 0.55})`;
                ctx.lineWidth = 0.9;
                ctx.beginPath();
                ctx.moveTo(a.x, a.y);
                ctx.lineTo(mouse.x, mouse.y);
                ctx.stroke();
            }
        }

        for (const n of nodes) {
            ctx.fillStyle = n.red ? 'rgba(229, 35, 60, 0.9)' : 'rgba(19, 181, 203, 0.9)';
            ctx.beginPath();
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fill();
        }
    };

    const loop = () => {
        if (!running) return;
        draw();
        requestAnimationFrame(loop);
    };

    const update = () => {
        const shouldRun = visible && !document.hidden && !reduceMotion;
        if (shouldRun && !running) {
            running = true;
            requestAnimationFrame(loop);
        } else if (!shouldRun) {
            running = false;
        }
    };

    resize();
    draw();

    new ResizeObserver(() => {
        resize();
        draw();
    }).observe(host);

    new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        update();
    }).observe(canvas);

    document.addEventListener('visibilitychange', update);

    host.addEventListener('pointermove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    }, { passive: true });
    host.addEventListener('pointerleave', () => {
        mouse.x = -9999;
        mouse.y = -9999;
    });

    update();
}
