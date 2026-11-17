<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

/**
 * A coloured section holding any blocks (e.g. the blue introduction).
 * Text blocks inside sit in the right half; Images and "Grid split" Columns use the full grid.
 */
class Section extends Block
{
    public $name = 'Section';

    public $description = 'A coloured band of content. Use it for the introduction and for anything that isn’t a chapter.';

    public $category = 'skelly-couch';

    public $icon = 'align-wide';

    public $keywords = ['section', 'introduction', 'background', 'group'];

    public $blockVersion = 3;

    public $apiVersion = 3;

    public $supports = [
        'align' => false,
        'anchor' => true,
        'jsx' => true,
        'color' => ['background' => true, 'text' => false, 'gradients' => false, 'link' => false],
    ];

    /**
     * Starting content: the project facts (Table, "Facts" style) next to the lead paragraph.
     */
    public $template = [
        'core/columns' => [
            'className' => 'is-style-grid-split',
            'innerBlocks' => [
                ['core/column' => ['innerBlocks' => [
                    ['core/table' => ['className' => 'is-style-facts', 'hasFixedLayout' => false]],
                ]]],
                ['core/column' => ['innerBlocks' => [
                    ['core/paragraph' => ['fontSize' => 'lead', 'placeholder' => 'Lead paragraph…']],
                ]]],
            ],
        ],
    ];

    public function with(): array
    {
        $theme = \App\block_theme($this->block, 'white');

        return [
            'theme' => $theme,
            'surface' => \App\theme_surface($theme),
            'id' => ($this->block->anchor ?? '') ?: (get_field('nav_label') ? sanitize_title(get_field('nav_label')) : null),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('section');

        $fields
            ->addText('nav_label', [
                'label' => 'Name in the “+” menu',
                'instructions' => 'Optional. Fill it in to list this section in the chapter bars’ “+” menu (e.g. Introduction).',
            ]);

        return $fields->build();
    }
}
