/*
 * core/image + aperture "+" and comment "+" markers: the sidebar panels and the on-image preview.
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { createElement as h, Fragment } from '@wordpress/element';
import { ImageExtras } from '../editor/image-extras.js';

export const hook = 'editor.BlockEdit';
export const name = 'sc/image-extras-edit';

export const callback = createHigherOrderComponent(
  (BlockEdit) => (props) => {
    if (props.name !== 'core/image') return h(BlockEdit, props);

    // ImageExtras renders into the sidebar (slot) and over the image (portal): no extra DOM around the block
    return h(Fragment, null, h(BlockEdit, props), h(ImageExtras, props));
  },
  'withImageExtras'
);
