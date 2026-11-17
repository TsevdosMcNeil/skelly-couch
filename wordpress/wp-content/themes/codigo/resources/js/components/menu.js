/**
 * Menu bar: wipes down; its own menu icon collapses into one bar (and expands again on close). Esc closes.
 */
export function initMenu(root = document) {
  const navBar = root.getElementById ? root.getElementById('navBar') : root.querySelector('#navBar');
  if (!navBar) return null;
  const navBurger = navBar.querySelector('.burger');
  const closeBtn = navBar.querySelector('[data-menu-close]');
  const toggles = [...root.querySelectorAll('[data-menu-toggle]')];
  let isOpen = false;

  function set(open) {
    isOpen = open;
    document.body.classList.toggle('nav-open', open);
    navBurger.classList.toggle('is-collapsed', open);
    toggles.forEach((t) => t.setAttribute('aria-expanded', String(open)));
    if (open) closeBtn.focus({ preventScroll: true });
  }

  toggles.forEach((t) => t.addEventListener('click', () => set(true)));
  closeBtn.addEventListener('click', () => set(false));
  navBar.querySelectorAll('ul a').forEach((a) => a.addEventListener('click', () => set(false)));
  addEventListener('keydown', (e) => { if (e.key === 'Escape' && isOpen) set(false); });

  return { open: () => set(true), close: () => set(false) };
}
