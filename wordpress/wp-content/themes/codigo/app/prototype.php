<?php

/**
 * Client-preview prototypes.
 *
 * Serves standalone Blade views at fixed URLs without needing a page in the
 * database, e.g. /homepage-prototype → resources/views/prototype/homepage.blade.php
 *
 * Remove this file (and its entry in functions.php) once the real templates ship.
 */

namespace App;

/**
 * URL slug => view name (relative to resources/views).
 */
function prototypes(): array
{
    return [
        'homepage-prototype' => 'prototype/homepage',
    ];
}

/**
 * Bump when the routes above change so the rewrite rules are flushed once.
 */
const PROTOTYPE_ROUTES_VERSION = '1';

add_action('init', function () {
    foreach (array_keys(prototypes()) as $slug) {
        add_rewrite_rule('^'.preg_quote($slug, '#').'/?$', 'index.php?sc_prototype='.$slug, 'top');
    }

    if (get_option('sc_prototype_routes') !== PROTOTYPE_ROUTES_VERSION) {
        flush_rewrite_rules(false);
        update_option('sc_prototype_routes', PROTOTYPE_ROUTES_VERSION);
    }
});

add_filter('query_vars', function (array $vars) {
    $vars[] = 'sc_prototype';

    return $vars;
});

/**
 * Hand the matching Blade file to Sage, which renders it through Acorn.
 */
add_filter('template_include', function ($template) {
    $slug = get_query_var('sc_prototype');

    if (! $slug || ! isset(prototypes()[$slug])) {
        return $template;
    }

    status_header(200);
    nocache_headers();

    return get_theme_file_path('resources/views/'.prototypes()[$slug].'.blade.php');
}, 99);
