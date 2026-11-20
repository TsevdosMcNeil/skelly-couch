<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

/**
 * Contents: the list of the page's chapters (left) next to any blocks, normally an Image (right).
 * Hovering a chapter shows its highlight image over the Image; clicking scrolls to the chapter.
 * Highlights come from each Chapter's "Highlight in Contents" field; this block can replace them.
 */
class Contents extends Block
{
    public $name = 'Contents';

    public $description = 'The list of the page’s chapters next to an image. Hovering a chapter highlights its part of the image; clicking scrolls to it.';

    public $category = 'skelly-couch';

    public $icon = 'list-view';

    public $keywords = ['contents', 'chapters', 'index', 'navigation', 'exploded view'];

    public $blockVersion = 3;

    public $apiVersion = 3;

    public $multiple = false;

    public $supports = [
        'align' => false,
        'anchor' => true,
        'jsx' => true,
        'color' => ['background' => true, 'text' => true, 'gradients' => false, 'link' => false],
    ];

    /**
     * The right side starts with an Image (the plain exploded view, aperture optional).
     */
    public $template = [
        'core/image' => ['sizeSlug' => 'full'],
    ];

    /** List backgrounds that get white text by default. */
    private const DARK = ['blue', 'slate', 'black'];

    public function with(): array
    {
        $post_id = (int) ($this->post_id ?: \App\editing_post_id());
        $theme = \App\block_theme($this->block, 'off-white');
        // "List background" (Color panel); `panel_color` was the ACF select it replaced
        $panel = sanitize_key($this->block->panelColor ?? ($this->block->data['panel_color'] ?? '') ?: 'plaster');

        // Replacements set in this block, by chapter id
        $replace = [];
        foreach ((array) get_field('highlights') as $row) {
            if (! empty($row['contents_chapter']) && ! empty($row['image'])) {
                $replace[$row['contents_chapter']] = (int) $row['image'];
            }
        }

        $items = array_map(function ($chapter) use ($replace) {
            $chapter['highlight'] = $replace[$chapter['id']] ?? $chapter['highlight'];

            return $chapter;
        }, \App\chapters($post_id));

        return [
            'heading' => get_field('heading') ?: __('Contents', 'sage'),
            'items' => $items,
            'theme' => $theme,
            'surface' => \App\theme_surface($theme),
            'panelSurface' => in_array($panel, self::DARK, true) ? 'dark' : 'light',
            // In the editor the colours come from resources/js/filters/contents.colors.preview.js
            'colorVars' => $this->preview ? '' : $this->colorVars($panel),
        ];
    }

    /**
     * The block's colours as CSS variables (same rules as resources/js/editor/contents-colors.js).
     */
    private function colorVars(string $panel): string
    {
        $preset = fn ($slug) => 'var(--wp--preset--color--'.sanitize_key($slug).')';
        $vars = ['--contents-panel' => $preset($panel)];

        $text = $this->block->textColor ?? null;
        $custom = $this->block->style['color']['text'] ?? null;
        if ($text) {
            $vars['--contents-text'] = $preset($text);
        } elseif (is_string($custom) && preg_match('/^#[0-9a-f]{3,8}$/i', $custom)) {
            $vars['--contents-text'] = $custom;
        }

        if (in_array($panel, self::DARK, true)) {
            $vars['--contents-panel-text'] = 'var(--color-white)';
        }

        return implode('; ', array_map(fn ($k, $v) => "$k: $v", array_keys($vars), $vars));
    }

    public function fields(): array
    {
        $fields = Builder::make('contents');

        $fields
            ->addText('heading', [
                'label' => 'Heading',
                'placeholder' => 'Contents',
                'instructions' => 'The list below it is built from the page’s Chapter blocks (their short title, or title). Colours: Styles → Color (Text, Background for the image side, List background).',
            ])
            ->addRepeater('highlights', [
                'label' => 'Replace a highlight',
                'instructions' => 'Optional. Each chapter shows its own “Highlight in Contents” image; add a row to use a different one here.',
                'button_label' => 'Replace a highlight',
                'layout' => 'block',
            ])
                ->addSelect('contents_chapter', [
                    'label' => 'Chapter',
                    'choices' => [],
                    'allow_null' => 1,
                    'ui' => 0,
                ])
                ->addImage('image', [
                    'label' => 'Highlight',
                    'return_format' => 'id',
                    'preview_size' => 'medium',
                ])
            ->endRepeater();

        return $fields->build();
    }
}
