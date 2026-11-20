export const reducedMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Cubic-bezier easing function (same maths as CSS), so JS animations can use the Figma curves.
 */
export function bezier(x1, y1, x2, y2) {
  const ax = 3 * x1 - 3 * x2 + 1, bx = 3 * x2 - 6 * x1, cx = 3 * x1;
  const ay = 3 * y1 - 3 * y2 + 1, by = 3 * y2 - 6 * y1, cy = 3 * y1;
  const sx = (t) => ((ax * t + bx) * t + cx) * t;
  const sy = (t) => ((ay * t + by) * t + cy) * t;
  const dx = (t) => (3 * ax * t + 2 * bx) * t + cx;
  return (x) => {
    if (x <= 0) return 0;
    if (x >= 1) return 1;
    let t = x;
    for (let i = 0; i < 8; i++) {           // Newton–Raphson
      const err = sx(t) - x, d = dx(t);
      if (Math.abs(err) < 1e-5 || !d) break;
      t -= err / d;
    }
    return sy(Math.min(1, Math.max(0, t)));
  };
}

/** Figma: "Scroll to" — 1000ms, 0.8 0 0.2 1 */
const scrollEase = bezier(0.8, 0, 0.2, 1);

export function scrollToY(y, duration = 1000) {
  const start = scrollY, delta = y - start;
  if (reducedMotion() || Math.abs(delta) < 2) { scrollTo(0, y); return; }
  const t0 = performance.now();
  let cancelled = false;
  const cancel = () => { cancelled = true; };
  addEventListener('wheel', cancel, { once: true, passive: true });
  addEventListener('touchstart', cancel, { once: true, passive: true });
  const step = (now) => {
    if (cancelled) return;
    const p = Math.min(1, (now - t0) / duration);
    scrollTo(0, start + delta * scrollEase(p));
    if (p < 1) requestAnimationFrame(step);
    else { removeEventListener('wheel', cancel); removeEventListener('touchstart', cancel); }
  };
  requestAnimationFrame(step);
}

/** Scrolls so the element's top meets the top of the window (below the WordPress admin bar when logged in). */
export function scrollToElement(el, duration = 1000) {
  const adminBar = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--wp-admin--admin-bar--height')) || 0;
  scrollToY(el.getBoundingClientRect().top + scrollY - adminBar, duration);
}
