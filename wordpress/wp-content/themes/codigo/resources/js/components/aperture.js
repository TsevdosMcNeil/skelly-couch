import { reducedMotion } from '../utils/motion.js';

/**
 * Aperture "+" (Figma: AperturePlus).
 *
 * Markup (resources/views/components/figure.blade.php):
 *   .figure__media[data-aperture='{"x":40,"y":40,"size":10,"trigger":"click"}']
 *     img.figure__img              the picture
 *     .aperture__layer > img       the second image, clipped to the window
 *     .aperture__ghost > img       the picture again, shown in the window once the second image has grown
 *     button.aperture__plus        the "+" on the window's bottom-left corner
 *
 * x / y / size are % of the image (size = window width as % of the image width; the window is square).
 * trigger "click": the window follows the cursor over the image; a click grows the second image from
 *                  wherever the window is; another click (or leaving the image) shrinks it back.
 * trigger "hover": entering the image grows it from the window's resting place; leaving shrinks it back.
 */
const GROW = { duration: 1000, easing: 'cubic-bezier(.8, 0, .2, 1)' };   // Figma "smart animate" curve
const FOLLOW = 0.16;                                                       // how quickly the window chases the cursor

export function initAperture(media) {
  let cfg;
  try { cfg = JSON.parse(media.dataset.aperture); } catch { return null; }

  const layer = media.querySelector('.aperture__layer');
  const ghost = media.querySelector('.aperture__ghost');
  const plus = media.querySelector('.aperture__plus');
  if (!layer || !ghost || !plus) return null;

  const clickMode = cfg.trigger !== 'hover';
  let W = 0, H = 0, S = 0;                 // image size and window size, px
  let rest = { x: 0, y: 0 };               // window top-left at rest
  const pos = { x: 0, y: 0 };              // window top-left now
  const target = { x: 0, y: 0 };           // where it's heading
  let open = false, anim = null, raf = 0, opened = null;

  const clamp = (v, max) => Math.min(Math.max(v, 0), Math.max(max, 0));
  const inset = (r) => `inset(${r.y}px ${W - r.x - S}px ${H - r.y - S}px ${r.x}px)`;
  const full = 'inset(0px 0px 0px 0px)';

  function measure() {
    W = media.clientWidth; H = media.clientHeight;
    S = Math.round(W * cfg.size / 100);
    rest = { x: clamp(W * cfg.x / 100, W - S), y: clamp(H * cfg.y / 100, H - S) };
    if (!open) { Object.assign(pos, rest); Object.assign(target, rest); }
    paint();
  }

  function paint() {
    if (!open && !anim) layer.style.clipPath = inset(pos);
    media.style.setProperty('--ax', `${pos.x}px`);
    media.style.setProperty('--ay', `${pos.y + S}px`);
  }

  // Window chases the cursor (click mode only), eased per frame
  function tick() {
    raf = 0;
    pos.x += (target.x - pos.x) * FOLLOW;
    pos.y += (target.y - pos.y) * FOLLOW;
    const settled = Math.abs(target.x - pos.x) < 0.3 && Math.abs(target.y - pos.y) < 0.3;
    if (settled) Object.assign(pos, target);
    paint();
    if (!settled) raf = requestAnimationFrame(tick);
  }
  const chase = () => { if (!raf) raf = requestAnimationFrame(tick); };

  function grow() {
    if (open) return;
    open = true;
    cancelAnimationFrame(raf); raf = 0;
    opened = { ...pos };
    media.classList.add('is-aperture-open');
    plus.setAttribute('aria-expanded', 'true');
    ghost.style.clipPath = inset(opened);
    anim?.cancel();
    const o = { ...GROW, duration: reducedMotion() ? 0 : GROW.duration, fill: 'forwards' };
    anim = layer.animate([{ clipPath: inset(opened) }, { clipPath: full }], o);
    // the original shows through the window once the new image has mostly grown past it
    ghost.animate([{ opacity: 0 }, { opacity: 0, offset: 0.35 }, { opacity: 1 }], o);
  }

  function shrink() {
    if (!open) return;
    open = false;
    media.classList.remove('is-aperture-open');
    plus.setAttribute('aria-expanded', 'false');
    const back = opened || rest;
    const o = { ...GROW, duration: reducedMotion() ? 0 : GROW.duration * 0.8, fill: 'forwards' };
    anim?.cancel();
    ghost.animate([{ opacity: 1 }, { opacity: 0, offset: 0.4 }, { opacity: 0 }], o);
    const a = anim = layer.animate([{ clipPath: full }, { clipPath: inset(back) }], o);
    a.finished.then(() => {
      if (anim !== a) return;
      anim = null;
      a.cancel();
      Object.assign(pos, back);
      if (clickMode) chase();   // drift back to the cursor / resting place
      paint();
    }).catch(() => {});
  }

  const toggle = () => (open ? shrink() : grow());

  if (clickMode) {
    media.classList.add('is-aperture-click');
    media.addEventListener('pointermove', (e) => {
      if (e.pointerType !== 'mouse' || open) return;
      const r = media.getBoundingClientRect();
      target.x = clamp(e.clientX - r.left - S / 2, W - S);
      target.y = clamp(e.clientY - r.top - S / 2, H - S);
      chase();
    });
    media.addEventListener('pointerleave', () => {
      Object.assign(target, rest);
      if (open) shrink(); else chase();
    });
    media.addEventListener('click', (e) => {
      if (e.target.closest('.comment')) return;   // comment "+"s have their own behaviour
      toggle();
    });
  } else {
    media.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') grow(); });
    media.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') shrink(); });
    // touch / keyboard: the "+" toggles
    plus.addEventListener('click', (e) => { e.stopPropagation(); toggle(); });
  }

  new ResizeObserver(measure).observe(media);
  const imgs = [...media.querySelectorAll('img')];
  Promise.all(imgs.map((i) => i.decode().catch(() => {}))).then(() => {
    measure();
    media.classList.add('is-aperture-ready');
  });

  return { open: grow, close: shrink, toggle };
}
