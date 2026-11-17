/*
 * Editor UI for the Image block's aperture "+" and comment "+" markers (see filters/image.extras.*.js).
 *
 * - Sidebar: "Aperture" and "Comments" panels.
 * - On the image (while the block is selected): the aperture window showing its second image, and the
 *   numbered comment markers. "Place on image" lets the editor click on the picture to set a position.
 *
 * Written with createElement (no JSX): the Vite WordPress plugin maps @wordpress/* to the editor's globals,
 * but not React's JSX runtime.
 */
import { createElement as h, Fragment, useEffect, useState, createPortal } from '@wordpress/element';
import { InspectorControls, MediaUpload, MediaUploadCheck, useSettings } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl, RangeControl, TextareaControl, Button, Notice, BaseControl, ColorPalette } from '@wordpress/components';

const ANCHORS = [
  { label: 'Top left', value: 'top-left' },
  { label: 'Top right', value: 'top-right' },
  { label: 'Bottom left', value: 'bottom-left' },
  { label: 'Bottom right', value: 'bottom-right' },
];
const STYLES = [
  { label: 'Magnifying glass', value: 'magnify' },
  { label: 'Growing', value: 'grow' },
];
const round = (v) => Math.round(v * 10) / 10;
const clamp = (v, min, max) => Math.min(max, Math.max(min, v));

/** The <img> of this block in the editor canvas (inside the iframe when there is one). */
function findImage(clientId) {
  const iframe = document.querySelector('iframe[name="editor-canvas"]');
  const doc = iframe?.contentDocument || document;
  return doc.querySelector(`[data-block="${clientId}"] img`);
}

/** Tracks the image element and its size, so the preview can be drawn over it. */
function useImageBox(clientId, active, deps) {
  const [box, setBox] = useState(null);

  useEffect(() => {
    if (!active) { setBox(null); return undefined; }
    let img = null, ro = null, frame = 0;

    const attach = () => {
      const next = findImage(clientId);
      if (!next) { frame = requestAnimationFrame(attach); return; }
      img = next;
      const host = img.parentElement;
      if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
      const measure = () => setBox({ host, w: img.offsetWidth, h: img.offsetHeight, top: img.offsetTop, left: img.offsetLeft });
      ro = new (img.ownerDocument.defaultView.ResizeObserver)(measure);
      ro.observe(img);
      measure();
    };
    attach();

    return () => { cancelAnimationFrame(frame); ro?.disconnect(); };
  }, [clientId, active, ...deps]);

  return box;
}

const plusSvg = (color) => h('svg', { viewBox: '0 0 24 25', width: 14, height: 15, fill: color, 'aria-hidden': true },
  h('path', { d: 'M15.2 0H8.9v25h6.3z' }), h('path', { d: 'M24 8.9H0v6.3h24z' }));

/** The preview drawn over the image: aperture window + comment markers; click to place when placing. */
function Preview({ box, aperture, comments, placing, onPlace }) {
  const { w, h: H } = box;
  // `placing` is 'aperture' or a comment index — and the first comment is 0, so test for null, not truthiness
  const isPlacing = placing !== null;
  const children = [];

  if (aperture.enabled) {
    const S = (w * (aperture.size ?? 10)) / 100;
    const left = clamp((w * (aperture.x ?? 40)) / 100, 0, w - S);
    const top = clamp((H * (aperture.y ?? 40)) / 100, 0, H - S);
    children.push(h('div', {
      key: 'aperture',
      style: {
        position: 'absolute', left, top, width: S, height: S, overflow: 'hidden',
        outline: '2px dashed #fff', boxShadow: '0 0 0 1px rgba(0,0,0,.6)', background: 'rgba(255,255,255,.35)',
      },
    }, aperture.url && h('img', {
      src: aperture.url, alt: '',
      style: { position: 'absolute', left: -left, top: -top, width: w, height: H, maxWidth: 'none', objectFit: 'cover' },
    })));
    children.push(h('div', {
      key: 'aperture-plus',
      style: { position: 'absolute', left: left - 7, top: top + S - 7, lineHeight: 0 },
    }, plusSvg(aperture.plus === 'blue' ? '#233f7c' : '#fff')));
  }

  // 1 design px (at 1920) in the editor canvas
  const u = (box.host.ownerDocument.defaultView?.innerWidth || 1920) / 1920;
  const NOTE = 150 * u;   // note size (comment.pcss --box)
  const GLYPH = 14;       // preview "+" size

  comments.forEach((c, i) => {
    const cx = (w * (c.x ?? 50)) / 100;
    const cy = (H * (c.y ?? 50)) / 100;
    const anchor = c.anchor || 'top-right';
    const right = anchor.endsWith('right');
    const bottom = anchor.startsWith('bottom');
    const inset = (c.plusInset ?? 0) * u;

    // the note's area (dashed), opening away from its corner
    children.push(h('div', {
      key: `n${i}`,
      style: {
        position: 'absolute', width: NOTE, height: NOTE,
        left: right ? cx - NOTE : cx, top: bottom ? cy - NOTE : cy,
        outline: '1px dashed rgba(255,255,255,.8)', boxShadow: '0 0 0 1px rgba(0,0,0,.25)',
        background: c.noteColor ? `${c.noteColor}33` : 'transparent',
      },
    }));
    // the "+" inside that corner, `inset` in from both edges, with its number
    children.push(h('div', {
      key: `c${i}`,
      style: {
        position: 'absolute', lineHeight: 0, display: 'flex', alignItems: 'center', gap: 4,
        flexDirection: right ? 'row-reverse' : 'row',
        left: right ? undefined : cx + inset, right: right ? w - cx + inset : undefined,
        top: bottom ? cy - inset - GLYPH : cy + inset,
      },
    }, plusSvg(c.plusColor || '#fff'), h('span', {
      style: { font: '600 11px/16px system-ui', color: '#fff', background: '#233f7c', padding: '0 5px', borderRadius: 8 },
    }, String(i + 1))));
  });

  return createPortal(h('div', {
    style: {
      position: 'absolute', left: box.left, top: box.top, width: w, height: H, zIndex: 20,
      pointerEvents: isPlacing ? 'auto' : 'none', cursor: isPlacing ? 'crosshair' : 'default',
      boxShadow: isPlacing ? 'inset 0 0 0 3px #1e4db2' : 'none',
    },
    onClick: isPlacing ? (e) => {
      e.preventDefault(); e.stopPropagation();
      const r = e.currentTarget.getBoundingClientRect();
      onPlace(((e.clientX - r.left) / r.width) * 100, ((e.clientY - r.top) / r.height) * 100);
    } : undefined,
  }, children), box.host);
}

export function ImageExtras({ clientId, attributes, setAttributes, isSelected }) {
  const aperture = attributes.aperture || {};
  const comments = attributes.comments || [];
  const [placing, setPlacing] = useState(null); // 'aperture' | comment index | null
  const [palette] = useSettings('color.palette');

  const box = useImageBox(clientId, isSelected && (aperture.enabled || comments.length > 0),
    [attributes.url, attributes.width, attributes.height, attributes.aspectRatio, attributes.align]);

  useEffect(() => { if (!isSelected) setPlacing(null); }, [isSelected]);

  const setAperture = (patch) => {
    const next = { trigger: 'click', plus: 'white', x: 40, y: 40, size: 10, ...aperture, ...patch };
    const update = { aperture: next };
    // A click aperture and the "Expand on click" lightbox would both react to the same click
    if (next.enabled && next.trigger === 'click' && attributes.lightbox?.enabled) {
      update.lightbox = { ...attributes.lightbox, enabled: false };
    }
    setAttributes(update);
  };
  const setComment = (i, patch) => setAttributes({ comments: comments.map((c, j) => (j === i ? { ...c, ...patch } : c)) });

  const onPlace = (px, py) => {
    if (placing === 'aperture' && box) {
      const size = aperture.size ?? 10;
      const sizeY = (size * box.w) / box.h; // the window is square: its height in % of the image height
      setAperture({ x: round(clamp(px - size / 2, 0, 100 - size)), y: round(clamp(py - sizeY / 2, 0, 100 - sizeY)) });
    } else if (typeof placing === 'number') {
      setComment(placing, { x: round(px), y: round(py) });
    }
    setPlacing(null);
  };

  const placeButton = (key, label) => h(Button, {
    variant: placing === key ? 'primary' : 'secondary',
    onClick: () => setPlacing(placing === key ? null : key),
    disabled: !attributes.url,
    __next40pxDefaultSize: true,
  }, placing === key ? 'Click on the image…' : label);

  const aperturePanel = h(PanelBody, { title: 'Aperture “+”', initialOpen: !!aperture.enabled },
    h(ToggleControl, {
      label: 'Add an aperture',
      help: 'A square window in the image showing a second image (e.g. the drawing). It grows to cover the picture.',
      checked: !!aperture.enabled,
      onChange: (enabled) => setAperture({ enabled }),
      __nextHasNoMarginBottom: true,
    }),
    aperture.enabled && h(Fragment, null,
      h(BaseControl, { label: 'Image inside the window', help: 'Same framing as the main image, so it lines up when it grows.', __nextHasNoMarginBottom: true },
        h(MediaUploadCheck, null, h(MediaUpload, {
          onSelect: (media) => setAperture({ id: media.id, url: media.url }),
          allowedTypes: ['image'],
          value: aperture.id,
          render: ({ open }) => h('div', null,
            aperture.url && h('img', { src: aperture.url, alt: '', style: { display: 'block', width: '100%', marginBottom: 8 } }),
            h(Button, { variant: 'secondary', onClick: open, __next40pxDefaultSize: true }, aperture.id ? 'Replace image' : 'Choose image')),
        }))),
      h(SelectControl, {
        label: 'Opens on',
        value: aperture.trigger || 'click',
        options: [{ label: 'Click (the window follows the cursor)', value: 'click' }, { label: 'Hover', value: 'hover' }],
        onChange: (trigger) => setAperture({ trigger }),
        __nextHasNoMarginBottom: true, __next40pxDefaultSize: true,
      }),
      (aperture.trigger || 'click') === 'click' && h(Notice, { status: 'info', isDismissible: false },
        '“Expand on click” is switched off for this image: the click opens the aperture.'),
      h(SelectControl, {
        label: '“+” colour',
        value: aperture.plus || 'white',
        options: [{ label: 'White', value: 'white' }, { label: 'Blue', value: 'blue' }],
        onChange: (plus) => setAperture({ plus }),
        __nextHasNoMarginBottom: true, __next40pxDefaultSize: true,
      }),
      h(RangeControl, {
        label: 'Window size (% of the image width)', min: 4, max: 40, step: 0.5,
        value: aperture.size ?? 10, onChange: (size) => setAperture({ size }),
        __nextHasNoMarginBottom: true, __next40pxDefaultSize: true,
      }),
      h(BaseControl, { label: 'Position at rest', help: 'Click on the image to place the window.', __nextHasNoMarginBottom: true },
        placeButton('aperture', 'Place on image')),
      h(RangeControl, { label: 'Left (%)', min: 0, max: 100, step: 0.5, value: aperture.x ?? 40, onChange: (x) => setAperture({ x }), __nextHasNoMarginBottom: true, __next40pxDefaultSize: true }),
      h(RangeControl, { label: 'Top (%)', min: 0, max: 100, step: 0.5, value: aperture.y ?? 40, onChange: (y) => setAperture({ y }), __nextHasNoMarginBottom: true, __next40pxDefaultSize: true }),
    ));

  const commentsPanel = h(PanelBody, { title: 'Comments “+”', initialOpen: comments.length > 0 },
    h('p', { style: { marginTop: 0 } }, 'Fixed “+” markers with a short note. Magnifying glass: a magnified square, then a slate note. Growing: a plaster note grows out of the “+”.'),
    comments.map((c, i) => h('div', { key: i, style: { borderTop: '1px solid #ddd', padding: '12px 0' } },
      h('strong', { style: { display: 'block', marginBottom: 8 } }, `Comment ${i + 1}`),
      h(TextareaControl, { label: 'Note', rows: 2, value: c.text || '', onChange: (text) => setComment(i, { text }), __nextHasNoMarginBottom: true }),
      h(SelectControl, { label: 'Effect', value: c.style || 'magnify', options: STYLES, onChange: (style) => setComment(i, { style }), __nextHasNoMarginBottom: true, __next40pxDefaultSize: true }),
      h(SelectControl, { label: 'Note corner (the note opens away from it)', value: c.anchor || 'top-right', options: ANCHORS, onChange: (anchor) => setComment(i, { anchor }), __nextHasNoMarginBottom: true, __next40pxDefaultSize: true }),
      h(RangeControl, {
        label: '“+” padding (px at 1920)',
        help: 'Gap between the note’s corner and the “+”. 0 = flush in the corner.',
        min: 0, max: 60, step: 1, value: c.plusInset ?? 0,
        onChange: (plusInset) => setComment(i, { plusInset: plusInset ?? 0 }),
        __nextHasNoMarginBottom: true, __next40pxDefaultSize: true,
      }),
      h(BaseControl, { label: '“+” colour', help: 'Empty = white.', __nextHasNoMarginBottom: true },
        h(ColorPalette, { colors: palette || [], value: c.plusColor, onChange: (plusColor) => setComment(i, { plusColor }), enableAlpha: false, clearable: true })),
      h(BaseControl, { label: 'Note background', help: `Empty = ${(c.style || 'magnify') === 'grow' ? 'plaster' : 'slate'}. The text turns black or white to stay readable.`, __nextHasNoMarginBottom: true },
        h(ColorPalette, { colors: palette || [], value: c.noteColor, onChange: (noteColor) => setComment(i, { noteColor }), enableAlpha: false, clearable: true })),
      h('div', { style: { display: 'flex', gap: 8, marginTop: 8 } },
        placeButton(i, 'Place on image'),
        h(Button, { variant: 'tertiary', isDestructive: true, onClick: () => { setPlacing(null); setAttributes({ comments: comments.filter((_, j) => j !== i) }); } }, 'Remove')),
    )),
    h(Button, {
      variant: 'secondary',
      disabled: !attributes.url,
      onClick: () => {
        setAttributes({ comments: [...comments, { x: 50, y: 50, anchor: 'top-right', style: 'magnify', text: '' }] });
        setPlacing(comments.length);
      },
      __next40pxDefaultSize: true,
    }, 'Add comment'));

  return h(Fragment, null,
    h(InspectorControls, null, aperturePanel, commentsPanel),
    box && h(Preview, { box, aperture, comments, placing, onPlace }));
}
