<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class App extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '*',
    ];

    /**
     * Retrieve the site name.
     */
    public function siteName(): string
    {
        return get_bloginfo('name', 'display');
    }

    /**
     * Top-level items of the Primary Navigation menu (menu bar and footer).
     * Falls back to the Figma's five links until the menu is set up in Appearance → Menus.
     *
     * @return array<int, array{title: string, url: string, current: bool}>
     */
    public function navigation(): array
    {
        static $items;

        if ($items !== null) {
            return $items;
        }

        $locations = get_nav_menu_locations();
        $menu = $locations['primary_navigation'] ?? null;
        $entries = $menu ? (wp_get_nav_menu_items($menu) ?: []) : [];

        if ($entries) {
            _wp_menu_item_classes_by_context($entries);

            return $items = collect($entries)
                ->where('menu_item_parent', 0)
                ->map(fn ($item) => [
                    'title' => $item->title,
                    'url' => $item->url,
                    'current' => (bool) $item->current,
                ])
                ->values()
                ->all();
        }

        return $items = collect(['Work', 'Practice', 'Services', 'News', 'Contact'])
            ->map(fn ($title) => ['title' => $title, 'url' => home_url('/'.strtolower($title).'/'), 'current' => false])
            ->all();
    }
}
