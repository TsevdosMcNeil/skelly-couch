/*
 * Contents block colours as CSS variables (same rules as App\Blocks\Contents::colorVars() in PHP):
 * --contents-panel       list background (palette colour, Plaster by default)
 * --contents-text        text colour, when one is chosen (Black by default)
 * --contents-panel-text  default text on a dark list background (white)
 */
const DARK = ['blue', 'slate', 'black'];
const preset = (slug) => `var(--wp--preset--color--${slug})`;

export function contentsColorVars(attributes = {}) {
  const panel = attributes.panelColor || 'plaster';
  const text = attributes.textColor ? preset(attributes.textColor) : attributes.style?.color?.text;
  const vars = { '--contents-panel': preset(panel) };

  if (text) vars['--contents-text'] = text;
  if (DARK.includes(panel)) vars['--contents-panel-text'] = 'var(--color-white)';

  return vars;
}
