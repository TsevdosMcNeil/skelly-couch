/*
 * core/image + aperture "+" and comment "+" markers: the attributes.
 *
 * They have no `source`, so they're saved in the block comment
 * (<!-- wp:image {"aperture":{…},"comments":[…]} -->), not in the HTML. The Image block's own saved HTML
 * doesn't change: no "invalid block" warnings, and existing images keep working.
 * Front-end markup: app/blocks.php (render_block_core/image).
 */
export const hook = 'blocks.registerBlockType';
export const name = 'sc/image-extras-attributes';

export function callback(settings, blockName) {
  if (blockName !== 'core/image') return settings;

  return {
    ...settings,
    attributes: {
      ...settings.attributes,
      // { enabled, id, url, trigger: 'click'|'hover', plus: 'white'|'blue', x, y, size } — x/y/size in % of the image
      aperture: { type: 'object', default: {} },
      // [{ x, y, anchor: 'top-left'|…, style: 'magnify'|'grow', text, plusInset, plusColor, noteColor }]
      // x/y = the note's corner (%); plusInset = px at 1920; colours = hex
      comments: { type: 'array', default: [] },
    },
  };
}
