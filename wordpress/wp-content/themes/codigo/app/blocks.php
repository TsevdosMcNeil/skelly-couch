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
 * Entries for the chapter bars' "+" menu, in page order: Sections that have a menu name, and every Chapter.
 *
 * @return array<int, array{id: string, label: string}>
 */
function section_nav(int $post_id): array
{
    static $cache = [];

    if (isset($cache[$post_id])) {
        return $cache[$post_id];
    }

    $nav = [];

    foreach (parse_blocks(get_post_field('post_content', $post_id)) as $block) {
        $name = $block['blockName'] ?? '';
        $attrs = $block['attrs'] ?? [];
        $data = $attrs['data'] ?? [];

        if ($name === 'acf/section' && ! empty($data['nav_label'])) {
            $nav[] = [
                'id' => ($attrs['anchor'] ?? '') ?: sanitize_title($data['nav_label']),
                'label' => $data['nav_label'],
            ];
        }

        if ($name === 'acf/chapter') {
            $title = ($data['short_title'] ?? '') ?: ($data['title'] ?? '');

            if ($title !== '') {
                $nav[] = ['id' => chapter_id($attrs['anchor'] ?? '', $title), 'label' => $title];
            }
        }
    }

    return $cache[$post_id] = $nav;
}

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

    $open = sprintf(
        '<div class="figure__media%s%s"%s%s>',
        $hasAperture ? ' has-aperture' : '',
        $comments ? ' has-comments' : '',
        $hasAperture ? " data-aperture=\"{$config}\"" : '',
        $comments ? ' data-comments' : ''
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
 * Inline CSS variables for one comment "+": position, "+" padding and colours.
 */
function comment_style(array $comment): string
{
    $pct = fn ($v) => max(0, min(100, (float) $v)).'%';
    $color = fn ($v) => is_string($v) && preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/i', $v) ? $v : null;

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
