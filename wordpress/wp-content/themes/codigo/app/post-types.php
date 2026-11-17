<?php

/**
 * Custom post types.
 *
 * Fields for these live in app/Fields (ACF Composer).
 */

namespace App;

add_action('init', function () {
    register_post_type('project', [
        'labels' => [
            'name' => __('Projects', 'sage'),
            'singular_name' => __('Project', 'sage'),
            'add_new_item' => __('Add New Project', 'sage'),
            'edit_item' => __('Edit Project', 'sage'),
            'all_items' => __('All Projects', 'sage'),
        ],
        'public' => true,
        'has_archive' => false,          // the Work page will list them
        'rewrite' => ['slug' => 'work', 'with_front' => false],
        'menu_icon' => 'dashicons-building',
        'menu_position' => 20,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields'],
        'show_in_rest' => true,          // block editor
        // A new project starts with: hero, blue introduction (facts + lead), first chapter
        'template' => [
            ['acf/project-hero'],
            ['acf/section', ['backgroundColor' => 'blue']],
            ['acf/chapter'],
        ],
    ]);

    register_post_type('member', [
        'labels' => [
            'name' => __('Team', 'sage'),
            'singular_name' => __('Team member', 'sage'),
            'add_new_item' => __('Add New Team member', 'sage'),
            'edit_item' => __('Edit Team member', 'sage'),
            'all_items' => __('All Team members', 'sage'),
        ],
        'public' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'people', 'with_front' => false],
        'menu_icon' => 'dashicons-groups',
        'menu_position' => 21,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'], // photo = featured image; menu order = order on the People page
        'show_in_rest' => true,
    ]);
});

/**
 * "Full name" placeholder for team members.
 */
add_filter('enter_title_here', fn ($text, $post) => $post->post_type === 'member' ? __('Full name', 'sage') : $text, 10, 2);

/**
 * Flush rewrite rules once when the post types (or their slugs) change.
 */
const POST_TYPES_VERSION = '2';

add_action('init', function () {
    if (get_option('sc_post_types') !== POST_TYPES_VERSION) {
        flush_rewrite_rules(false);
        update_option('sc_post_types', POST_TYPES_VERSION);
    }
}, 99);
