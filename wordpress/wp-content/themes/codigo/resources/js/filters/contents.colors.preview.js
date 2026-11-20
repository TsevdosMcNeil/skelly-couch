/*
 * Contents block: shows the colours in the editor straight away, without waiting for ACF to re-render the
 * preview. Sets the same CSS variables the front end gets (resources/views/blocks/contents.blade.php).
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { createElement as h } from '@wordpress/element';
import { contentsColorVars } from '../editor/contents-colors.js';

export const hook = 'editor.BlockListBlock';
export const name = 'sc/contents-colors-preview';

export const callback = createHigherOrderComponent(
  (BlockListBlock) => (props) => {
    if (props.name !== 'acf/contents') return h(BlockListBlock, props);

    const wrapperProps = props.wrapperProps || {};

    return h(BlockListBlock, {
      ...props,
      wrapperProps: { ...wrapperProps, style: { ...(wrapperProps.style || {}), ...contentsColorVars(props.attributes) } },
    });
  },
  'withContentsColorsPreview'
);
