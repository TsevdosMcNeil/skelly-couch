/*
 * Block editor: block styles, variations and filters (auto-registered, see SAGE.md).
 * core/image is extended with the aperture and comment "+" markers: filters/image.extras.*.js.
 */
import domReady from '@wordpress/dom-ready';
import { addFilter } from '@wordpress/hooks';
import { registerBlockVariation, registerBlockStyle, unregisterBlockStyle } from '@wordpress/blocks';

// Auto-register variations
const variationModules = import.meta.glob('./variations/*.js', { eager: true });
Object.values(variationModules).forEach((module) => {
  const variation = module.default;
  registerBlockVariation(variation.block, variation);
});

// Auto-register styles
const styleModules = import.meta.glob('./styles/*.js', { eager: true });
Object.values(styleModules).forEach((module) => {
  const style = module.default;
  registerBlockStyle(style.block, style);
});

// Auto-register filters
const filterModules = import.meta.glob('./filters/*.js', { eager: true });
Object.values(filterModules).forEach((module) => {
  if (module.hook && module.name && typeof module.callback === 'function') {
    addFilter(module.hook, module.name, module.callback);
  }
});

domReady(() => {
  // Core styles that don't fit the design
  unregisterBlockStyle('core/table', 'stripes');
  unregisterBlockStyle('core/button', 'outline');
});
