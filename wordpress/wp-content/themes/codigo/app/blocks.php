<?php

/**
 * Helpers for the theme's blocks (app/Blocks) and the block editor.
 */

namespace App;

/** Background colours that take white text and a white menu icon. */
const DARK_THEMES = ['blue', 'slate'];

/**
 * The block's background colour slug (Gutenberg colour support), e.g. "blue".
 */
function block_theme(object $block, string $default = 'white'): string
{
    $slug = $block->backgroundColor ?? $block->background_color ?? '';

    return $slug !== '' ? sanitize_key($slug) : $default;
}

/**
 * "dark" or "light": tells the fixed menu icon which colour to use over a section.
 */
function theme_surface(string $theme): string
{
    return in_array($theme, DARK_THEMES, true) ? 'dark' : 'light';
}

/**
 * A chapter's id: its HTML anchor if set, otherwise the slug of its (short) title.
 */
function chapter_id(?string $anchor, string $title): string
{
    return $anchor ?: (sanitize_title($title) ?: 'chapter');
}

/**
 * The page's Chapter blocks, in order: id, label (short title or title) and "Highlight in Contents" image.
 *
 * @return array<int, array{id: string, label: string, highlight: int}>
 */
function chapters(int $post_id): array
{
    return array_values(array_filter(page_nav($post_id), fn ($item) => $item['type'] === 'chapter'));
}

/**
 * Entries for the chapter bars' "+" menu, in page order: Sections that have a menu name, and every Chapter.
 *
 * @return array<int, array{id: string, label: string}>
 */
function section_nav(int $post_id): array
{
    return page_nav($post_id);
}

/**
 * Sections with a menu name and Chapters of a post, in page order (read from the saved content).
 *
 * @return array<int, array{type: string, id: string, label: string, highlight: int}>
 */
function page_nav(int $post_id): array
{
    static $cache = [];

    if (isset($cache[$post_id])) {
        return $cache[$post_id];
    }

    $nav = [];

    foreach (parse_blocks((string) get_post_field('post_content', $post_id)) as $block) {
        $name = $block['blockName'] ?? '';
        $attrs = $block['attrs'] ?? [];
        $data = $attrs['data'] ?? [];

        if ($name === 'acf/section' && ! empty($data['nav_label'])) {
            $nav[] = [
                'type' => 'section',
                'id' => ($attrs['anchor'] ?? '') ?: sanitize_title($data['nav_label']),
                'label' => $data['nav_label'],
                'highlight' => 0,
            ];
        }

        if ($name === 'acf/chapter') {
            $title = ($data['short_title'] ?? '') ?: ($data['title'] ?? '');

            if ($title !== '') {
                $nav[] = [
                    'type' => 'chapter',
                    'id' => chapter_id($attrs['anchor'] ?? '', $title),
                    'label' => $title,
                    'highlight' => (int) ($data['highlight'] ?? 0),
                ];
            }
        }
    }

    return $cache[$post_id] = $nav;
}

/**
 * The post being edited, also inside ACF's block requests (sidebar fields, previews).
 */
function editing_post_id(): int
{
    $candidates = [
        function_exists('acf_get_form_data') ? acf_get_form_data('post_id') : null,
        $_REQUEST['post_id'] ?? null,
        $_GET['post'] ?? null,
        get_the_ID(),
    ];

    foreach ($candidates as $id) {
        if (is_numeric($id) && (int) $id > 0) {
            return (int) $id;
        }
    }

    return 0;
}

/**
 * Contents block, "Replace a highlight" → Chapter: a drop-down of this page's chapters.
 * A replacement whose chapter has gone (renamed or deleted) is kept, marked as missing.
 */
add_filter('acf/prepare_field/name=contents_chapter', function ($field) {
    $choices = [];

    foreach (chapters(editing_post_id()) as $chapter) {
        $choices[$chapter['id']] = $chapter['label'];
    }

    $value = is_string($field['value'] ?? null) ? $field['value'] : '';
    if ($value !== '' && ! isset($choices[$value])) {
        $choices[$value] = sprintf(__('%s (chapter not found)', 'sage'), $value);
    }

    $field['choices'] = $choices;

    if (! $choices) {
        $field['instructions'] = __('Save the page after adding chapters to see them here.', 'sage');
    }

    return $field;
});

/**
 * "Skelly + Couch" block category, first in the inserter.
 */
add_filter('block_categories_all', function (array $categories) {
    return array_merge(
        [['slug' => 'skelly-couch', 'title' => 'Skelly + Couch', 'icon' => null]],
        $categories
    );
});

/*
|--------------------------------------------------------------------------
| core/image + aperture "+" and comment "+" markers
|--------------------------------------------------------------------------
|
| Attributes and editor panels: resources/js/filters/image.extras.*.js.
| Here: the front-end markup. When an image has an aperture or comments, its <img> is wrapped in
| .figure__media with the extra layers (resources/views/partials/image-extras.blade.php), and
| resources/js/components/aperture.js / comments.js bring them to life.
|
*/

/**
 * A click aperture and the "Expand on click" lightbox would react to the same click: no lightbox then.
 */
add_filter('render_block_data', function (array $block) {
    if (($block['blockName'] ?? '') !== 'core/image') {
        return $block;
    }

    $aperture = $block['attrs']['aperture'] ?? [];

    if (! empty($aperture['enabled']) && ($aperture['trigger'] ?? 'click') === 'click') {
        $block['attrs']['lightbox'] = ['enabled' => false];
    }

    return $block;
});

add_filter('render_block_core/image', function (string $html, array $block) {
    $attrs = $block['attrs'] ?? [];
    $aperture = $attrs['aperture'] ?? [];
    $hasAperture = ! empty($aperture['enabled']) && ! empty($aperture['id']);
    $comments = array_values(array_filter(
        (array) ($attrs['comments'] ?? []),
        fn ($c) => is_array($c) && trim($c['text'] ?? '') !== ''
    ));

    if (! $hasAperture && ! $comments) {
        return $html;
    }

    // The block's own <img>: mark it, and read its sizes/size for the extra copies
    $tags = new \WP_HTML_Tag_Processor($html);
    if (! $tags->next_tag('img')) {
        return $html;
    }
    $tags->add_class('figure__img');
    $sizes = $tags->get_attribute('sizes') ?: '100vw';
    $html = $tags->get_updated_html();

    $extras = view('partials.image-extras', [
        'image' => (int) ($attrs['id'] ?? 0),
        'size' => $attrs['sizeSlug'] ?? 'full',
        'sizes' => is_string($sizes) ? $sizes : '100vw',
        'aperture' => $hasAperture ? $aperture : null,
        'comments' => $comments,
        'uid' => wp_unique_id('img-'),
    ])->render();

    $config = $hasAperture ? esc_attr(wp_json_encode([
        'x' => (float) ($aperture['x'] ?? 40),
        'y' => (float) ($aperture['y'] ?? 40),
        'size' => (float) ($aperture['size'] ?? 10),
        'trigger' => ($aperture['trigger'] ?? 'click') === 'hover' ? 'hover' : 'click',
    ])) : '';

    // "Darken the image" (%), comments only; empty = the CSS default (18%)
    $darken = $comments && isset($attrs['commentsDarken']) && is_numeric($attrs['commentsDarken'])
        ? ' style="--darken: '.(max(0, min(100, (float) $attrs['commentsDarken'])) / 100).'"'
        : '';

    $open = sprintf(
        '<div class="figure__media%s%s"%s%s%s>',
        $hasAperture ? ' has-aperture' : '',
        $comments ? ' has-comments' : '',
        $hasAperture ? " data-aperture=\"{$config}\"" : '',
        $comments ? ' data-comments' : '',
        $darken
    );

    // Wrap the <img> (only the first one: the block's image)
    return preg_replace('#<img\b[^>]*>#', $open.'$0'.str_replace(['\\', '$'], ['\\\\', '\\$'], $extras).'</div>', $html, 1);
}, 10, 2);

/**
 * Black or white text for a background colour (hex), whichever reads better.
 */
function contrast_text(string $hex): string
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
        return '';
    }

    // WCAG relative luminance
    $channel = function (string $c) {
        $v = hexdec($c) / 255;

        return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    };
    $l = 0.2126 * $channel(substr($hex, 0, 2)) + 0.7152 * $channel(substr($hex, 2, 2)) + 0.0722 * $channel(substr($hex, 4, 2));

    // contrast with black (l + .05) / .05 vs with white 1.05 / (l + .05)
    return ($l + 0.05) / 0.05 >= 1.05 / ($l + 0.05) ? '#000000' : '#ffffff';
}

/**
 * A colour saved by the editor's colour pickers: a hex value, or null.
 */
function hex_color(mixed $value): ?string
{
    return is_string($value) && preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/i', $value) ? $value : null;
}

/**
 * Inline CSS for the aperture "+": its colour (palette hex; 'white' / 'blue' were saved before the colour picker).
 */
function aperture_plus_style(array $aperture): string
{
    $plus = $aperture['plus'] ?? null;
    $plus = ['white' => '#ffffff', 'blue' => '#233f7c'][$plus] ?? hex_color($plus);

    return $plus ? "--plus-color: {$plus}" : '';
}

/**
 * Inline CSS variables for one comment "+": position, "+" padding and colours.
 */
function comment_style(array $comment): string
{
    $pct = fn ($v) => max(0, min(100, (float) $v)).'%';
    $color = fn ($v) => hex_color($v);

    $vars = [
        '--x' => $pct($comment['x'] ?? 50),
        '--y' => $pct($comment['y'] ?? 50),
        '--plus-inset' => (string) max(0, min(200, (float) ($comment['plusInset'] ?? 0))),
    ];
    if ($plus = $color($comment['plusColor'] ?? null)) {
        $vars['--plus-color'] = $plus;
    }
    if ($bg = $color($comment['noteColor'] ?? null)) {
        $vars['--note-bg'] = $bg;
        $vars['--note-fg'] = contrast_text($bg);
    }

    return implode('; ', array_map(fn ($k, $v) => "{$k}: {$v}", array_keys($vars), $vars));
}

/**
 * Contents block: the "List background" colour (`panelColor`, a palette slug) is a block attribute set from
 * the Color panel (resources/js/filters/contents.colors.*.js). Registered here so WordPress keeps it.
 */
add_filter('register_block_type_args', function (array $args, string $name) {
    if ($name === 'acf/contents') {
        $args['attributes'] = ($args['attributes'] ?? []) + ['panelColor' => ['type' => 'string']];
    }

    return $args;
}, 10, 2);
