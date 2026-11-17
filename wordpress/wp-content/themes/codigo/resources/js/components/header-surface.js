/**
 * The fixed menu icon is white over dark sections and black over light ones.
 * Sections say what they are with data-surface="dark|light"; the innermost one under the icon wins.
 */
export function initHeaderSurface(header) {
  if (!header) return null;
  let queued = false;

  function update() {
    queued = false;
    const btn = header.querySelector('.menu-btn');
    const r = btn.getBoundingClientRect();
    const x = r.left + r.width / 2, y = r.top + r.height / 2;
    // elementsFromPoint ignores the header itself (pointer-events: none on the header, but not on the button)
    const under = document.elementsFromPoint(x, y).find((el) => !header.contains(el) && el.closest('[data-surface]'));
    const surface = under ? under.closest('[data-surface]').dataset.surface : 'light';
    header.dataset.over = surface;
  }

  const queue = () => { if (!queued) { queued = true; requestAnimationFrame(update); } };
  addEventListener('scroll', queue, { passive: true });
  addEventListener('resize', queue);
  update();
  return { update };
}
