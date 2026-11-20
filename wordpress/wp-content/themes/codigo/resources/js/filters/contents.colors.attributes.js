/*
 * Contents block (acf/contents): "List background" colour, saved in the block comment as `panelColor`
 * (a palette slug: plaster, off-white…). Registered in PHP too (app/blocks.php, register_block_type_args).
 * The text colour is Gutenberg's own (supports.color.text): `textColor` / `style.color.text`.
 */
export const hook = 'blocks.registerBlockType';
export const name = 'sc/contents-colors-attributes';

export function callback(settings, blockName) {
  if (blockName !== 'acf/contents') return settings;

  return {
    ...settings,
    attributes: {
      ...settings.attributes,
      panelColor: { type: 'string' },
    },
  };
}
