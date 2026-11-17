/**
 * Comment "+" markers (Figma: CommentPlus_MagnifyingGlassEffect / CommentPlus_GrowingEffect).
 * Opens on hover (mouse), focus (keyboard) or tap (touch). Magnify squares get the picture's own
 * image, enlarged around the square's centre.
 */
const ZOOM = 1.6;

export function initComments(media) {
  const img = media.querySelector('.figure__img');
  const comments = [...media.querySelectorAll('.comment')];
  if (!img || !comments.length) return null;

  function placeZooms() {
    const W = media.clientWidth, H = media.clientHeight;
    const src = img.currentSrc || img.src;
    comments.forEach((c) => {
      const zoom = c.querySelector('.comment__zoom');
      if (!zoom) return;
      const box = c.querySelector('.comment__box');
      // centre of the square, in image px
      const mr = media.getBoundingClientRect(), br = box.getBoundingClientRect();
      const cx = br.left - mr.left + br.width / 2, cy = br.top - mr.top + br.height / 2;
      zoom.style.backgroundImage = `url("${src}")`;
      zoom.style.setProperty('--zoom-size', `${W * ZOOM}px ${H * ZOOM}px`);
      zoom.style.setProperty('--zoom-pos', `${br.width / 2 - cx * ZOOM}px ${br.height / 2 - cy * ZOOM}px`);
    });
  }

  comments.forEach((c) => {
    const plus = c.querySelector('.comment__plus');
    let timer = 0;
    const set = (open) => { clearTimeout(timer); c.classList.toggle('is-open', open); };
    const later = () => { clearTimeout(timer); timer = setTimeout(() => set(false), 120); };

    plus.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') set(true); });
    c.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') later(); });
    c.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse' && c.classList.contains('is-open')) set(true); });
    plus.addEventListener('focus', () => set(true));
    plus.addEventListener('blur', () => set(false));
    plus.addEventListener('click', (e) => {
      e.stopPropagation();                         // don't trigger an aperture underneath
      if (e.pointerType === 'touch' || e.pointerType === 'pen') set(!c.classList.contains('is-open'));
    });
  });
  // a tap anywhere else closes open notes
  document.addEventListener('pointerdown', (e) => {
    if (!media.contains(e.target)) comments.forEach((c) => c.classList.remove('is-open'));
  });

  new ResizeObserver(placeZooms).observe(media);
  img.addEventListener('load', placeZooms);
  return { update: placeZooms };
}
