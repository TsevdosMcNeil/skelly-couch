import { scrollToElement } from '../utils/motion.js';

/**
 * Chapter bar "+" menu: opens a drop-down with the project's sections; links scroll there with the
 * Figma "Scroll to" easing. Esc, a click outside or choosing a link closes it.
 */
export function initChapterNav(bar) {
  const toggle = bar.querySelector('.chapter-bar__toggle');
  const menu = bar.querySelector('.chapter-bar__menu');
  if (!toggle || !menu) return null;

  function set(open) {
    bar.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    menu.inert = !open;
  }
  const isOpen = () => bar.classList.contains('is-open');

  toggle.addEventListener('click', () => set(!isOpen()));
  addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen()) { set(false); toggle.focus(); }
  });
  document.addEventListener('pointerdown', (e) => { if (isOpen() && !bar.contains(e.target)) set(false); });

  menu.querySelectorAll('a[href^="#"]').forEach((a) => a.addEventListener('click', (e) => {
    const target = document.getElementById(a.hash.slice(1));
    if (!target) return;
    e.preventDefault();
    set(false);
    scrollToElement(target);
    history.replaceState(null, '', a.hash);
  }));

  return { open: () => set(true), close: () => set(false) };
}
