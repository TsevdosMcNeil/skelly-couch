<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

/**
 * Project hero: blue band with the S+C mark, project name (the post title) and subtitle,
 * then the full-width hero image.
 */
class ProjectHero extends Block
{
    public $name = 'Project Hero';

    public $description = 'Project name, subtitle and full-width hero image. One per project, at the top.';

    public $category = 'skelly-couch';

    public $icon = 'cover-image';

    public $keywords = ['hero', 'header', 'project'];

    public $post_types = ['project'];

    // ACF Blocks V3 + WP Block API v3 (iframe-compatible)
    public $blockVersion = 3;

    public $apiVersion = 3;

    /**
     * Save the fields as post meta, so listings (Work grid, Related cards) can use the subtitle and image:
     * get_field('hero_image', $project_id).
     */
    public $usePostMeta = true;

    public $supports = [
        'align' => false,
        'multiple' => false,
        'reusable' => false,
        'jsx' => false,
    ];

    public function with(): array
    {
        return [
            'title' => get_the_title($this->post_id),
            'subtitle' => get_field('subtitle'),
            'image' => (int) get_field('hero_image'),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('project_hero');

        $fields
            ->addTextarea('subtitle', [
                'label' => 'Subtitle',
                'instructions' => 'Shown under the project name (the post title).',
                'rows' => 2,
                'new_lines' => 'br',
            ])
            ->addImage('hero_image', [
                'label' => 'Hero image',
                'instructions' => 'Full width, landscape. At least 2400px wide.',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'required' => 1,
            ]);

        return $fields->build();
    }
}
