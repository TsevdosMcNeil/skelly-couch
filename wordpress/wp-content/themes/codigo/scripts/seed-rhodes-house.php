<?php

/**
 * Seeds the Rhodes House project (Figma "Project Page — Type A") as blocks, plus its team members.
 *
 *   docker compose run --rm wpcli eval-file wp-content/themes/codigo/scripts/seed-rhodes-house.php
 *
 * Safe to run again: it rewrites the same project, team members and images instead of duplicating them.
 *
 * Images: put the files listed in IMAGES below in wp-content/uploads/seed/rhodes-house/.
 * Missing ones fall back to wp-content/uploads/prototype/rhodes.jpg, so the page still renders.
 * Apertures are only switched on when their second image exists.
 * Images are core Image blocks; the aperture and comments are the theme's extra Image attributes.
 */

if (! defined('WP_CLI') || ! function_exists('acf_get_block_fields')) {
    echo "Run with: wp eval-file (ACF Pro active)\n";

    return;
}

require_once ABSPATH.'wp-admin/includes/image.php';
require_once ABSPATH.'wp-admin/includes/file.php';
require_once ABSPATH.'wp-admin/includes/media.php';

const SEED_DIR = WP_CONTENT_DIR.'/uploads/seed/rhodes-house';
const FALLBACK = WP_CONTENT_DIR.'/uploads/prototype/rhodes.jpg';

/** file in SEED_DIR => alt text */
const IMAGES = [
    'hero.jpg' => 'Rhodes House entrance portico',
    'intro-rotunda.jpg' => 'Glazed roof over the stone rotunda',
    'intro-garden.jpg' => 'New lightwell beside the garden terrace',
    'intro-garden-drawing.jpg' => 'Drawing of the lightwell',
    'offices-1.jpg' => 'Below ground offices',
    'offices-2.jpg' => 'Below ground offices, desks',
    'stair.jpg' => 'Spiral stair from above',
    'stair-drawing.jpg' => 'Drawing of the spiral stair',
    'lift.jpg' => 'New lift lobby',
    'bench.jpg' => 'Basement seating with timber screen',
    'bench-drawing.jpg' => 'Drawing of the basement seating',
    'lightwell.jpg' => 'Enclosed courtyard with glazed roof',
    'pavilion.jpg' => 'Glass Pavilion at dusk',
    'pavilion-drawing.jpg' => 'Drawing of the Glass Pavilion',
    'pavilion-interior.jpg' => 'Meeting inside the Glass Pavilion',
    'exploded-view.jpg' => 'Exploded view of the East Wing',
    'bedroom.jpg' => 'East Wing bedroom',
    'bedroom-drawing.jpg' => 'Drawing of the East Wing bedroom',
    'courtyard.jpg' => 'Residential Sunken Courtyard',
    'courtyard-garden-narrow.jpg' => 'East Garden terrace',
    'courtyard-garden.jpg' => 'Wildflower garden above the courtyard',
    'courtyard-garden-drawing.jpg' => 'Drawing of the garden',
    'courtyard-rooms.jpg' => 'Guest bedrooms opening onto the courtyard',
    'hall.jpg' => 'Convening Hall',
    'aerial.jpg' => 'Aerial view of Rhodes House',
    'aerial-before.jpg' => 'Aerial view during construction',
    // Contents: the exploded view, its aperture (aerial photo) and one highlight per chapter (same size and framing)
    'contents.jpg' => 'Exploded view of Rhodes House',
    'contents-aerial.jpg' => 'Aerial view of Rhodes House',
    'contents-offices.jpg' => 'Exploded view: Below Ground Offices',
    'contents-stairwells.jpg' => 'Exploded view: Stairwells, Basements, and Lightwells',
    'contents-phasing.jpg' => 'Exploded view: Listed Building Phasing',
    'contents-pavilion.jpg' => 'Exploded view: Glass Pavilion',
    'contents-east-wing.jpg' => 'Exploded view: East Wing',
    'contents-courtyard.jpg' => 'Exploded view: Residential Sunken Courtyard',
    'contents-hall.jpg' => 'Exploded view: Convening Hall',
];

// ---------------------------------------------------------------------------
// Images
// ---------------------------------------------------------------------------

function seed_image(string $file): int
{
    static $cache = [];
    if (isset($cache[$file])) {
        return $cache[$file];
    }

    $path = SEED_DIR.'/'.$file;
    $source = is_file($path) ? $path : FALLBACK;
    $key = is_file($path) ? $file : 'fallback:'.basename(FALLBACK);

    if (! is_file($source)) {
        WP_CLI::warning("Missing image and fallback: $file");

        return $cache[$file] = 0;
    }

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'meta_key' => '_sc_seed',
        'meta_value' => $key,
        'fields' => 'ids',
        'numberposts' => 1,
    ]);
    if ($existing) {
        return $cache[$file] = (int) $existing[0];
    }

    $tmp = wp_tempnam($source);
    copy($source, $tmp);
    $id = media_handle_sideload(['name' => basename($source), 'tmp_name' => $tmp], 0);
    if (is_wp_error($id)) {
        WP_CLI::warning("$file: ".$id->get_error_message());

        return $cache[$file] = 0;
    }
    update_post_meta($id, '_sc_seed', $key);
    update_post_meta($id, '_wp_attachment_image_alt', IMAGES[$file] ?? '');
    WP_CLI::log("  image: $key");

    return $cache[$file] = (int) $id;
}

function has_image(string $file): bool
{
    return is_file(SEED_DIR.'/'.$file);
}

// ---------------------------------------------------------------------------
// ACF block data: values by field name → ACF's stored format (name/value + _name/key, groups and
// repeaters flattened), using the field definitions registered by app/Blocks.
// ---------------------------------------------------------------------------

function acf_flatten(array $fields, array $values, string $prefix = ''): array
{
    $data = [];

    foreach ($fields as $field) {
        $name = $field['name'];
        if (! array_key_exists($name, $values)) {
            continue;
        }
        $value = $values[$name];
        $meta = $prefix.$name;

        if ($field['type'] === 'group') {
            $data[$meta] = '';
            $data['_'.$meta] = $field['key'];
            $data += acf_flatten($field['sub_fields'], (array) $value, $meta.'_');

            continue;
        }

        if ($field['type'] === 'repeater') {
            $rows = array_values((array) $value);
            $data[$meta] = count($rows);
            $data['_'.$meta] = $field['key'];
            foreach ($rows as $i => $row) {
                $data += acf_flatten($field['sub_fields'], $row, "{$meta}_{$i}_");
            }

            continue;
        }

        $data[$meta] = $value;
        $data['_'.$meta] = $field['key'];
    }

    return $data;
}

function acf_block(string $name, array $values = [], array $attrs = [], array $inner = []): array
{
    $fields = acf_get_block_fields(['name' => $name]);

    return [
        'blockName' => $name,
        'attrs' => array_merge(['name' => $name, 'data' => acf_flatten($fields, $values), 'mode' => 'preview'], $attrs),
        'innerBlocks' => $inner,
        'innerHTML' => '',
        'innerContent' => array_fill(0, count($inner), null),
    ];
}

// ---------------------------------------------------------------------------
// Core blocks, with the markup their save() produces (so the editor validates them)
// ---------------------------------------------------------------------------

function core(string $name, array $attrs, string $html, array $inner = [], string $close = ''): array
{
    return [
        'blockName' => $name,
        'attrs' => $attrs,
        'innerBlocks' => $inner,
        'innerHTML' => $html.$close,
        'innerContent' => $inner ? array_merge([$html], array_fill(0, count($inner), null), [$close]) : [$html],
    ];
}

function p(string $text, ?string $size = null): array
{
    return $size
        ? core('core/paragraph', ['fontSize' => $size], "<p class=\"has-{$size}-font-size\">{$text}</p>")
        : core('core/paragraph', [], "<p>{$text}</p>");
}

function h3(string $text): array
{
    return core('core/heading', ['level' => 3], "<h3 class=\"wp-block-heading\">{$text}</h3>");
}

function facts(array $rows): array
{
    $tr = implode('', array_map(fn ($r) => '<tr><td>'.esc_html($r[0]).'</td><td>'.esc_html($r[1]).'</td></tr>', $rows));

    return core('core/table', ['hasFixedLayout' => false, 'className' => 'is-style-facts'],
        "<figure class=\"wp-block-table is-style-facts\"><table><tbody>{$tr}</tbody></table></figure>");
}

function grid_split(array $left, array $right): array
{
    return core('core/columns', ['className' => 'is-style-grid-split'], '<div class="wp-block-columns is-style-grid-split">', [
        core('core/column', [], '<div class="wp-block-column">', $left, '</div>'),
        core('core/column', [], '<div class="wp-block-column">', $right, '</div>'),
    ], '</div>');
}

function plus_link(string $text, string $url): array
{
    $button = core('core/button', ['className' => 'is-style-plus'],
        '<div class="wp-block-button is-style-plus"><a class="wp-block-button__link wp-element-button" href="'.esc_url($url).'">'.esc_html($text).'</a></div>');

    return core('core/buttons', [], '<div class="wp-block-buttons">', [$button], '</div>');
}

// ---------------------------------------------------------------------------
// Theme blocks
// ---------------------------------------------------------------------------

function section(array $inner, string $color = 'blue', string $navLabel = ''): array
{
    return acf_block('acf/section', ['nav_label' => $navLabel], ['backgroundColor' => $color], $inner);
}

/** $highlight: file in SEED_DIR for "Highlight in Contents" (skipped if it isn't there). */
function chapter(string $title, array $inner, string $color = 'white', string $short = '', string $highlight = ''): array
{
    $values = ['title' => $title, 'short_title' => $short];
    if ($highlight !== '' && has_image($highlight)) {
        $values['highlight'] = seed_image($highlight);
    }

    return acf_block('acf/chapter', $values, ['backgroundColor' => $color], $inner);
}

/** Contents: the chapter list next to the exploded view (aperture: aerial photo). */
function contents(): array
{
    return acf_block('acf/contents', ['heading' => 'Contents'], ['backgroundColor' => 'off-white', 'panelColor' => 'plaster'], [
        figure('contents.jpg', '', ['image' => 'contents-aerial.jpg', 'plus' => 'blue', 'x' => 25.6, 'y' => 40.7, 'size' => 15.7]),
    ]);
}

/** A row of images in core Columns; widths like '29.2%' (pair 455 + 1085 at 1920) or '50%'. */
function row(array $widths, array $figures): array
{
    $columns = [];
    foreach ($figures as $i => $figure) {
        $w = $widths[$i];
        $columns[] = core('core/column', ['width' => $w], "<div class=\"wp-block-column\" style=\"flex-basis:{$w}\">", [$figure], '</div>');
    }

    return core('core/columns', [], '<div class="wp-block-columns">', $columns, '</div>');
}

/**
 * One core Image block, with the theme's aperture / comments attributes.
 * $align: '' (right half), 'wide' (between the page margins), 'full' (edge to edge).
 */
function figure(string $file, string $caption = '', ?array $aperture = null, array $comments = [], string $align = ''): array
{
    $id = seed_image($file);
    $attrs = ['id' => $id, 'sizeSlug' => 'full', 'linkDestination' => 'none'];

    if ($align) {
        $attrs['align'] = $align;
    }
    if ($aperture && has_image($aperture['image'])) {
        $apertureId = seed_image($aperture['image']);
        $attrs['aperture'] = [
            'enabled' => true,
            'id' => $apertureId,
            'url' => wp_get_attachment_image_url($apertureId, 'full'),
            'trigger' => $aperture['trigger'] ?? 'click',
            'plus' => $aperture['plus'] ?? 'white',
            'x' => $aperture['x'], 'y' => $aperture['y'], 'size' => $aperture['size'] ?? 10,
        ];
    }
    if ($comments) {
        $attrs['comments'] = $comments;
    }

    // Same markup as the Image block's save(), so the editor accepts it
    $classes = 'wp-block-image'.($align ? " align{$align}" : '').' size-full';
    $img = sprintf('<img src="%s" alt="%s" class="wp-image-%d"/>', esc_url(wp_get_attachment_image_url($id, 'full')), esc_attr(IMAGES[$file] ?? ''), $id);
    $figcaption = $caption !== '' ? '<figcaption class="wp-element-caption">'.esc_html($caption).'</figcaption>' : '';

    return core('core/image', $attrs, "<figure class=\"{$classes}\">{$img}{$figcaption}</figure>");
}

$lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';

// ---------------------------------------------------------------------------
// Team members (Figma: Team list)
// ---------------------------------------------------------------------------
WP_CLI::log('Team members');
foreach ([
    ['Mark Skelly', 'Founding Director'],
    ['David Hawkins', 'Director'],
    ['Julian Cottrill', 'Associate Director: Bristol Office'],
    ['James A. Nortey-Glover', 'Senior Engineer'],
    ['Mark Holden', 'Senior Engineer'],
    ['Luke Walters', 'Senior Engineer'],
] as $order => [$name, $role]) {
    $found = get_page_by_path(sanitize_title($name), OBJECT, 'member');
    $id = $found ? $found->ID : wp_insert_post([
        'post_type' => 'member',
        'post_status' => 'publish',
        'post_title' => $name,
        'post_name' => sanitize_title($name),
        'menu_order' => $order,
    ]);
    update_field('role', $role, $id);
    WP_CLI::log("  $name");
}

// ---------------------------------------------------------------------------
// Rhodes House
// ---------------------------------------------------------------------------
WP_CLI::log('Project: Rhodes House');

$blocks = [
    acf_block('acf/project-hero'),   // its fields are stored as post meta (below)

    section([
        grid_split(
            [facts([
                ['Architect', 'Stanton Williams'],
                ['Client', 'The Rhodes Trust'],
                ['Completion Date', '2023'],
                ['Value', '£38m'],
                ['Area', '5,300 m2 GIA'],
            ])],
            [p('The project involved the partial refurbishment and extension of the Grade II*-listed Rhodes House in Oxford, home to the Rhodes Scholars, transforming it into a world-class hub for global idea exchange.', 'lead')]
        ),
        row(['29.2%', '70.8%'], [
            figure('intro-rotunda.jpg'),
            figure('intro-garden.jpg', '', ['image' => 'intro-garden-drawing.jpg', 'x' => 2, 'y' => 3, 'size' => 9]),
        ]),
        p('Originally designed by Sir Herbert Baker and completed in 1929, Rhodes House is situated within landscaped gardens. The aim was to conserve and enhance the building’s historic elements through an ambitious environmental and energy strategy. Refurbishments included new foyer spaces in the entrance rotunda, a large social and study area in the basement with new lightwells, a refurbished archive space for the convening hall, and new ensuite bedrooms within the main listed building.'),
        p('Additionally, two subterranean elements were added beneath the gardens: a sunken residential courtyard to the east and a roof-lit administration office to the west. A new above-ground sculptural glazed pavilion in the West Garden provides a tranquil meeting place for scholars.'),
        p('Non-intrusive surveys enhanced understanding of the existing early 20th-century services, defining the scope and phasing of upgrades that allowed continued building use during the works.'),
    ], 'blue', 'Introduction'),

    contents(),

    chapter('Below Ground Offices', [
        p($lorem), p($lorem),
        row(['29.2%', '70.8%'], [
            figure('offices-2.jpg', 'Lorem ipsum caption'),
            figure('offices-1.jpg', 'Dolar sit amet caption', null, [
                ['x' => 75, 'y' => 8, 'anchor' => 'top-right', 'style' => 'magnify', 'text' => 'Lorem ipsum dolor sit amet'],
                ['x' => 5, 'y' => 93, 'anchor' => 'bottom-left', 'style' => 'magnify', 'text' => 'Services run under the floor'],
            ]),
        ]),
    ], 'white', '', 'contents-offices.jpg'),

    chapter('Stairwells, Basements, and Lightwells', [
        h3('Stairwells'), p($lorem),
        figure('stair.jpg', '', ['image' => 'stair-drawing.jpg', 'plus' => 'blue', 'x' => 60, 'y' => 42, 'size' => 11], [], 'wide'),
        h3('Basement'), p($lorem), p($lorem),
        row(['50%', '50%'], [
            figure('lift.jpg', '', null, [
                ['x' => 4, 'y' => 4, 'anchor' => 'top-left', 'style' => 'grow', 'text' => 'Lifts were added to this listed building'],
            ]),
            figure('bench.jpg', '', ['image' => 'bench-drawing.jpg', 'plus' => 'blue', 'x' => 75, 'y' => 66, 'size' => 18]),
        ]),
        h3('Lightwell / Courtyard'),
        p('Two open courtyards have been enclosed, improving usability, providing daylight, and natural ventilation, to the reconfigured lower floors and reducing heat loss. The glass has the same thermal performance as the old walls.'),
        p($lorem),
    ], 'off-white', '', 'contents-stairwells.jpg'),

    // Listed Building Phasing: text only until the Building Phases block exists
    chapter('Listed Building Phasing / Fabric Upgrades', [
        p($lorem),
    ], 'white', '', 'contents-phasing.jpg'),

    chapter('Glass Pavilion', [
        figure('pavilion.jpg', '', ['image' => 'pavilion-drawing.jpg', 'x' => 33, 'y' => 7, 'size' => 7], [], 'full'),
        p('In the West Garden, a new 50-seat glazed Pavilion provides a tranquil place for Scholars and the public to meet and exchange ideas in a magnificent garden setting. In contrast to the introverted character of Rhodes House, the Pavilion was conceived as a ‘room in the garden’.'),
        p('Turning a perceived weakness of highly glazed buildings into a strength, the Garden Pavilion is designed to act as a solar collector. Its glazed façades passively collect solar heat while fan coil units keep the pavilion at a comfortable temperature by extracting excess heat that is then used elsewhere in the main building for heating and hot water.'),
        figure('pavilion-interior.jpg', '', null, [
            ['x' => 49.5, 'y' => 32, 'anchor' => 'bottom-right', 'style' => 'grow', 'text' => 'Lorem ipsum dolor sit amet'],
            ['x' => 41.5, 'y' => 60, 'anchor' => 'top-right', 'style' => 'grow', 'text' => 'Lorem ipsum dolor sit amet'],
            ['x' => 85, 'y' => 68, 'anchor' => 'bottom-left', 'style' => 'grow', 'text' => 'Lorem ipsum dolor sit amet'],
        ], 'wide'),
    ], 'off-white', 'Glass Pavillion', 'contents-pavilion.jpg'),

    chapter('East Wing Bedrooms / Ensuites', [
        p($lorem), p($lorem),
        figure('exploded-view.jpg', '', null, [], 'wide'),
        p('The East Wing of Rhodes House has been returned to its original residential use, providing 21 ensuite bedrooms for conference delegates, with new lifts ensuring that most rooms have flat and level access.'),
        figure('bedroom.jpg', '', ['image' => 'bedroom-drawing.jpg', 'x' => 28, 'y' => 26, 'size' => 11]),
    ], 'white', '', 'contents-east-wing.jpg'),

    chapter('Residential Sunken Courtyard', [
        figure('courtyard.jpg', '', null, [], 'full'),
        p('Adjacent to the East Wing, a new lower ground Residential Courtyard, discreetly set within the East Garden, provides 16 new guest bedrooms, all opening onto a leafy communal patio echoing the materiality of the historic façade above. The rooms have glazed front openings to maximise natural light and are highly insulated and earth sheltered, with the gardens reinstated above. A new Garden Room opens out onto the courtyard below and a newly landscaped part of the garden, providing additional amenities for staff.'),
        row(['29.2%', '70.8%'], [
            figure('courtyard-garden-narrow.jpg'),
            figure('courtyard-garden.jpg', '', ['image' => 'courtyard-garden-drawing.jpg', 'x' => 2, 'y' => 3, 'size' => 4]),
        ]),
        p('There’s a rampart from the English Civil Wars of the seventeenth century to the side, so there was a gap limitation, between this monument and the building. The first architectural proposal of this area obstructed views of the Rampart, which led to the idea of making it underground. Excavating is expensive so the project tried to keep that space as shallow as possible. All the services run laterally, along the side bathroom walls.'),
        p('The spaces are naturally ventilated, and as the structure is earth buried, it is passively cooled.'),
        figure('courtyard-rooms.jpg', '', null, [
            ['x' => 19, 'y' => 60, 'anchor' => 'bottom-left', 'style' => 'magnify', 'text' => 'Glazed front openings'],
            ['x' => 44, 'y' => 28.5, 'anchor' => 'bottom-left', 'style' => 'magnify', 'text' => 'Textured brick façade'],
            ['x' => 70.5, 'y' => 72.5, 'anchor' => 'top-left', 'style' => 'magnify', 'text' => 'Planted communal patio'],
        ], 'wide'),
        p($lorem),
    ], 'off-white', '', 'contents-courtyard.jpg'),

    chapter('Convening Hall: Main Conference Space', [
        p('A new 300-seat Conference Hall and adjacent Foyer have been created by converting existing cavernous archive spaces beneath the House’s main rooms and gardens.'),
        figure('hall.jpg', '', null, [
            ['x' => 9.5, 'y' => 73, 'anchor' => 'bottom-left', 'style' => 'magnify', 'text' => 'Lorem ipsum dolor sit amet'],
            ['x' => 47, 'y' => 79, 'anchor' => 'bottom-left', 'style' => 'magnify', 'text' => 'Lorem ipsum dolor sit amet'],
            ['x' => 72.5, 'y' => 63, 'anchor' => 'bottom-left', 'style' => 'magnify', 'text' => 'Lorem ipsum dolor sit amet'],
            ['x' => 88, 'y' => 55, 'anchor' => 'bottom-left', 'style' => 'magnify', 'text' => 'Lorem ipsum dolor sit amet'],
        ], 'full'),
        p($lorem), p($lorem),
        figure('aerial.jpg', '', ['image' => 'aerial-before.jpg', 'x' => 24, 'y' => 59, 'size' => 15.5], [], 'full'),
        p($lorem),
        plus_link('Back to all projects', home_url('/work/')),
    ], 'white', '', 'contents-hall.jpg'),
];

$found = get_page_by_path('rhodes-house', OBJECT, 'project');
$postarr = [
    'post_type' => 'project',
    'post_status' => 'publish',
    'post_title' => 'Rhodes House',
    'post_name' => 'rhodes-house',
    'post_excerpt' => 'A sustainable transformation of Oxford’s iconic landmark.',
    'post_content' => wp_slash(serialize_blocks($blocks)),
];
if ($found) {
    $postarr['ID'] = $found->ID;
}
$projectId = wp_insert_post($postarr, true);
if (is_wp_error($projectId)) {
    WP_CLI::error($projectId->get_error_message());
}

// Project Hero stores its fields as post meta (usePostMeta)
$heroFields = array_column(acf_get_block_fields(['name' => 'acf/project-hero']), 'key', 'name');
update_field($heroFields['subtitle'], "A Sustainable Transformation of\nOxford’s Iconic Landmark", $projectId);
update_field($heroFields['hero_image'], seed_image('hero.jpg'), $projectId);
set_post_thumbnail($projectId, seed_image('hero.jpg'));

$missing = array_filter(array_keys(IMAGES), fn ($f) => ! has_image($f));
if ($missing) {
    WP_CLI::warning(count($missing).' image(s) not in '.SEED_DIR.' (fallback used, apertures off): '.implode(', ', $missing));
}

WP_CLI::success('Rhodes House: '.get_permalink($projectId));
