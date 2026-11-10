/**
 * Custom cursor: a glowing dot, a lagging ring, and a short "wire" hanging
 * from the pointer. The wire is a verlet rope — its first point is pinned to
 * the pointer and the rest follow with momentum and a little gravity, so it
 * swings behind fast moves and droops when the pointer rests.
 *
 * Mouse/trackpad only (pointer: fine). With reduced motion the dot and ring
 * stay, without the rope or any lag.
 */

const SEGMENTS = 18;
const SEGMENT_LENGTH = 7;
const GRAVITY = 0.16;
const DAMPING = 0.9;
const INTERACTIVE = 'a, button, [role="button"], label, summary, select, [data-cursor="hover"]';
const TEXT = 'input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="submit"]):not([type="button"]), textarea, [contenteditable="true"]';

export function initCursor() {
    if (!window.matchMedia('(pointer: fine)').matches) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const root = document.documentElement;

    const dot = el('cursor-dot');
    const ring = el('cursor-ring');
    const canvas = reduceMotion ? null : document.createElement('canvas');
    if (canvas) {
        canvas.className = 'cursor-trail';
        canvas.setAttribute('aria-hidden', 'true');
        document.body.append(canvas);
    }
    document.body.append(ring, dot);
    root.classList.add('has-custom-cursor', 'cursor-hidden');

    const ctx = canvas?.getContext('2d');
    let dpr = 1;
    const resize = () => {
        if (!canvas) return;
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = innerWidth * dpr;
        canvas.height = innerHeight * dpr;
        canvas.style.width = innerWidth + 'px';
        canvas.style.height = innerHeight + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    };
    resize();
    addEventListener('resize', resize);

    const mouse = { x: innerWidth / 2, y: innerHeight / 2 };
    const ringPos = { ...mouse };
    // Each rope point keeps its previous position: velocity is implied (verlet).
    const rope = Array.from({ length: SEGMENTS }, (_, i) => ({
        x: mouse.x, y: mouse.y + i * SEGMENT_LENGTH,
        px: mouse.x, py: mouse.y + i * SEGMENT_LENGTH,
    }));
    let seen = false;

    addEventListener('pointermove', (e) => {
        if (e.pointerType && e.pointerType !== 'mouse' && e.pointerType !== 'pen') return;
        mouse.x = e.clientX;
        mouse.y = e.clientY;

        if (!seen) {
            // First sighting: start everything at the pointer, not mid-screen.
            seen = true;
            Object.assign(ringPos, mouse);
            rope.forEach((p, i) => Object.assign(p, { x: mouse.x, y: mouse.y + i, px: mouse.x, py: mouse.y + i }));
        }
        root.classList.remove('cursor-hidden');

        const target = e.target instanceof Element ? e.target : null;
        root.classList.toggle('cursor-text', Boolean(target?.closest(TEXT)));
        root.classList.toggle('cursor-hover', Boolean(target?.closest(INTERACTIVE)));
    }, { passive: true });

    // Leaving the window: mouseout with no element to go to.
    document.addEventListener('mouseout', (e) => {
        if (!e.relatedTarget) root.classList.add('cursor-hidden');
    });
    addEventListener('blur', () => root.classList.add('cursor-hidden'));
    addEventListener('pointerdown', () => root.classList.add('cursor-down'));
    addEventListener('pointerup', () => root.classList.remove('cursor-down'));

    const place = (node, x, y) => {
        node.style.transform = `translate3d(${x}px, ${y}px, 0)`;
    };

    const stepRope = () => {
        const head = rope[0];
        head.x = mouse.x;
        head.y = mouse.y;

        for (let i = 1; i < rope.length; i++) {
            const p = rope[i];
            const vx = (p.x - p.px) * DAMPING;
            const vy = (p.y - p.py) * DAMPING;
            p.px = p.x;
            p.py = p.y;
            p.x += vx;
            p.y += vy + GRAVITY;
        }

        // Keep every link at its length; a few passes make the rope stiff enough.
        for (let pass = 0; pass < 4; pass++) {
            for (let i = 1; i < rope.length; i++) {
                const a = rope[i - 1];
                const b = rope[i];
                const dx = b.x - a.x;
                const dy = b.y - a.y;
                const dist = Math.hypot(dx, dy) || 0.0001;
                const diff = (dist - SEGMENT_LENGTH) / dist;
                if (i === 1) {
                    b.x -= dx * diff;
                    b.y -= dy * diff;
                } else {
                    a.x += dx * diff * 0.5;
                    a.y += dy * diff * 0.5;
                    b.x -= dx * diff * 0.5;
                    b.y -= dy * diff * 0.5;
                }
            }
            rope[0].x = mouse.x;
            rope[0].y = mouse.y;
        }

        // A fast flick moves the head further than the passes above can
        // follow; pull each link back so the wire never stretches like elastic.
        for (let i = 1; i < rope.length; i++) {
            const a = rope[i - 1];
            const b = rope[i];
            const dx = b.x - a.x;
            const dy = b.y - a.y;
            const dist = Math.hypot(dx, dy);
            if (dist > SEGMENT_LENGTH) {
                b.x = a.x + (dx / dist) * SEGMENT_LENGTH;
                b.y = a.y + (dy / dist) * SEGMENT_LENGTH;
            }
        }
    };

    const drawRope = () => {
        ctx.clearRect(0, 0, innerWidth, innerHeight);
        if (!seen || root.classList.contains('cursor-hidden') || root.classList.contains('cursor-text')) return;

        const tail = rope[rope.length - 1];
        const gradient = ctx.createLinearGradient(rope[0].x, rope[0].y, tail.x, tail.y);
        gradient.addColorStop(0, 'rgba(19, 181, 203, 0.95)');
        gradient.addColorStop(0.55, 'rgba(168, 85, 247, 0.7)');
        gradient.addColorStop(1, 'rgba(229, 35, 60, 0.85)');

        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = gradient;
        ctx.shadowColor = 'rgba(19, 181, 203, 0.6)';
        ctx.shadowBlur = 8;

        // Taper: thick at the pointer, thin at the end, as short curved pieces.
        for (let i = 1; i < rope.length - 1; i++) {
            const a = rope[i - 1];
            const b = rope[i];
            const c = rope[i + 1];
            ctx.lineWidth = 3.2 * (1 - i / rope.length) + 0.6;
            ctx.beginPath();
            ctx.moveTo((a.x + b.x) / 2, (a.y + b.y) / 2);
            ctx.quadraticCurveTo(b.x, b.y, (b.x + c.x) / 2, (b.y + c.y) / 2);
            ctx.stroke();
        }

        // A small glowing bead on the end of the wire.
        ctx.shadowColor = 'rgba(229, 35, 60, 0.9)';
        ctx.shadowBlur = 12;
        ctx.fillStyle = '#e5233c';
        ctx.beginPath();
        ctx.arc(tail.x, tail.y, 3, 0, Math.PI * 2);
        ctx.fill();
        ctx.shadowBlur = 0;
    };

    const frame = () => {
        place(dot, mouse.x, mouse.y);

        if (reduceMotion) {
            place(ring, mouse.x, mouse.y);
        } else {
            ringPos.x += (mouse.x - ringPos.x) * 0.18;
            ringPos.y += (mouse.y - ringPos.y) * 0.18;
            place(ring, ringPos.x, ringPos.y);
            stepRope();
            drawRope();
        }

        requestAnimationFrame(frame);
    };
    requestAnimationFrame(frame);
}

function el(className) {
    const node = document.createElement('div');
    node.className = className;
    node.setAttribute('aria-hidden', 'true');
    return node;
}
