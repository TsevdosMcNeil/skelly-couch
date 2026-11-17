<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

/**
 * Chapter: a sticky bar (project name · chapter title · "+") over any blocks.
 * Each bar is sticky inside its own chapter, so the next chapter's bar pushes it off the top.
 */
class Chapter extends Block
{
    public $name = 'Chapter';

    public $description = 'A chapter of the page, with a bar that sticks to the top while you read it. Add any blocks inside.';

    public $category = 'skelly-couch';

    public $icon = 'excerpt-view';

    public $keywords = ['chapter', 'section', 'sticky'];

    public $blockVersion = 3;

    public $apiVersion = 3;

    public $supports = [
        'align' => false,
        'anchor' => true,
        'jsx' => true,
        'color' => ['background' => true, 'text' => false, 'gradients' => false, 'link' => false],
    ];

    public $template = [
        'core/paragraph' => ['placeholder' => 'Chapter text…'],
    ];

    public function with(): array
    {
        $title = get_field('title') ?: __('Chapter title', 'sage');
        $theme = \App\block_theme($this->block, 'white');

        return [
            'title' => $title,
            'id' => \App\chapter_id($this->block->anchor ?? '', get_field('short_title') ?: $title),
            'theme' => $theme,
            'surface' => \App\theme_surface($theme),
            'project' => get_the_title($this->post_id),
            'nav' => \App\section_nav($this->post_id),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('chapter');

        $fields
            ->addText('title', [
                'label' => 'Title',
                'required' => 1,
            ])
            ->addText('short_title', [
                'label' => 'Short title',
                'instructions' => 'Optional. Used in the “+” menu and the Contents list.',
            ]);

        return $fields->build();
    }
}
