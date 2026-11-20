import { scrollToElement } from '../utils/motion.js';

/**
 * Contents block: the chapter list next to an image.
 *
 * - Hover, keyboard focus or a tap on a chapter makes it active (black, ↓ arrow) and fades its highlight
 *   image in over the picture. The first chapter is active to start with; the last one hovered stays active.
 * - Click: scrolls to the chapter with the Figma "Scroll to" easing.
 *
 * The highlights wait in a <template data-contents-highlights> (not downloaded until used). They're placed
 * right after the Image block's <img>, inside .figure__media, so an aperture or comments stay on top of them.
 */
export function initContents(root) {
  const links = [...root.querySelectorAll('.contents__nav a[data-chapter]')];
  if (!links.length) return null;

  const highlights = new Map();
  const media = placeHighlights(root);
  media?.querySelectorAll('.contents__highlight').forEach((img) => highlights.set(img.dataset.chapter, img));

  let active = null;
  function activate(id) {
    if (id === active) return;
    active = id;
    links.forEach((a) => a.parentElement.classList.toggle('is-active', a.dataset.chapter === id));
    highlights.forEach((img, key) => img.classList.toggle('is-visible', key === id));
  }

  links.forEach((a) => {
    const id = a.dataset.chapter;
    a.addEventListener('pointerenter', () => activate(id));
    a.addEventListener('focus', () => activate(id));
    a.addEventListener('click', (e) => {
      const target = document.getElementById(id);
      if (!target) return;
      e.preventDefault();
      activate(id);
      scrollToElement(target);
      history.replaceState(null, '', `#${id}`);
    });
  });

  activate(links[0].dataset.chapter);

  return { activate };
}

/** Moves the highlight images from their <template> over the first image on the right. */
function placeHighlights(root) {
  const template = root.querySelector('template[data-contents-highlights]');
  const img = root.querySelector('.contents__media img');
  if (!template || !img) return null;

  // .figure__media is the positioning box; PHP adds it when the image has an aperture or comments
  let box = img.parentElement;
  if (!box.classList.contains('figure__media')) {
    box = document.createElement('div');
    box.className = 'figure__media';
    img.replaceWith(box);
    box.append(img);
  }

  const sizes = img.getAttribute('sizes');
  const layers = template.content.cloneNode(true);
  layers.querySelectorAll('img').forEach((layer) => { if (sizes) layer.setAttribute('sizes', sizes); });
  img.after(layers);
  template.remove();
  box.classList.add('has-highlights');

  return box;
}
