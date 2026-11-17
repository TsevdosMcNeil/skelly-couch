/*
 * Vanilla components, one file each. Every component is a function (root element) → instance,
 * so each can become an Alpine component later without changing its behaviour.
 */

import { initMenu } from './components/menu.js';
import { initHeaderSurface } from './components/header-surface.js';
import { initChapterNav } from './components/chapter-nav.js';
import { initAperture } from './components/aperture.js';
import { initComments } from './components/comments.js';

const ready = (fn) => (document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn());

ready(() => {
  initMenu(document);
  initHeaderSurface(document.querySelector('[data-site-header]'));
  document.querySelectorAll('[data-chapter-nav]').forEach(initChapterNav);
  document.querySelectorAll('[data-aperture]').forEach(initAperture);
  document.querySelectorAll('[data-comments]').forEach(initComments);
});
