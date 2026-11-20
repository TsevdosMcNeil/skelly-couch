/*
 * Contents block: adds "List background" to the block's Color panel (next to Text and Background),
 * with the theme palette, like the core colour settings.
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { createElement as h, Fragment } from '@wordpress/element';
import * as blockEditor from '@wordpress/block-editor';
import { PanelBody, ColorPalette } from '@wordpress/components';

const { InspectorControls } = blockEditor;
const ColorDropdown = blockEditor.ColorGradientSettingsDropdown || blockEditor.__experimentalColorGradientSettingsDropdown;
const useColors = blockEditor.useMultipleOriginColorsAndGradients || blockEditor.__experimentalUseMultipleOriginColorsAndGradients;

export const hook = 'editor.BlockEdit';
export const name = 'sc/contents-colors-edit';

function ListBackground({ attributes, setAttributes, clientId }) {
  const colors = useColors ? useColors() : { colors: [] };
  const palette = (colors.colors || []).flatMap((origin) => origin.colors || []);
  const value = palette.find((c) => c.slug === attributes.panelColor)?.color;
  const onChange = (hex) => setAttributes({ panelColor: palette.find((c) => c.color === hex)?.slug });

  // Core's colour panel (Styles → Color), when this WordPress has it
  if (ColorDropdown && useColors) {
    return h(InspectorControls, { group: 'color' },
      h(ColorDropdown, {
        __experimentalIsRenderedInSidebar: true,
        panelId: clientId,
        ...colors,
        disableCustomColors: true,
        gradients: [],
        disableCustomGradients: true,
        settings: [{
          label: 'List background',
          colorValue: value,
          onColorChange: onChange,
          isShownByDefault: true,
          resetAllFilter: () => ({ panelColor: undefined }),
        }],
      }));
  }

  // Fallback: a panel of its own
  return h(InspectorControls, null,
    h(PanelBody, { title: 'List background', initialOpen: true },
      h(ColorPalette, { colors: palette, value, onChange, disableCustomColors: true, clearable: true })));
}

export const callback = createHigherOrderComponent(
  (BlockEdit) => (props) => {
    if (props.name !== 'acf/contents') return h(BlockEdit, props);

    return h(Fragment, null, h(BlockEdit, props), h(ListBackground, props));
  },
  'withContentsColors'
);
