
# Change Log

All notable changes to this project will be documented in this file.
 
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

---

## [0.0.4] - 2026-11-17

Homepage prototype: logo and menu, from George's recordings (homeScroll, OpenMenu, OpenMenutop).

### Changed
- **Logo:** the S+C mark is now the SVG exported from Figma, used at its native size at 1920
  (1 unit = 1px, with its own 20px padding). The S, + and C in the intro animation use the same SVG pieces.
- **Header on the homepage:** the "SKELLY +COUCH" wordmark and menu icon are printed on the hero and scroll away with it.
  The fixed S+C mark and menu icon sit behind the hero: they are revealed by the blue section's top edge as you scroll
  down, and covered again when you scroll back up.
- **Menu icon:** bars as wide as the S (30px) and as thick as the plus stroke (6.24px), 20px from the right.
- **Menu:** a 103px blue bar wipes down from the top. It holds the white wordmark (covering the S+C mark), the links
  in one row from the middle of the screen, and a search icon under the menu icon. As it opens, the menu icon's
  lower bars slide up into the top bar; closing reverses both. Escape also closes it.

### Removed
- The hero no longer copies the site header during the intro (`data-reveal-ghost`): the header is now part of the hero.
  The option is still available on the reveal component.

### Pending
- Wordmark as SVG (the "SKELLY +COUCH" at the top of the homepage and in the menu is still type).
- George's logo interaction (shapes shifting to line up with the wordmark).

## [0.0.3] - 2026-11-17

Homepage prototype: first round of design feedback (George).

### Changed
- **Proportional scaling from the 1920px artboard.** All sizes use a `--u` unit (1px at 1920 wide), so the page matches
  the Figma exactly at 1920 x 1080 and scales by the same ratio on other desktop screens. Phones keep their own sizes.
- **Type and spacing from the Figma "Desktop Styles":** Big Titles 119/119 −3%, Headings 56/58 −2%,
  Large Body 34/38 −2% (fixes the tracking of the blue intro text), Body 18/22, spacing scale 15/30/60/80/120/200.
  The ending CTA uses Headings; the intro, News label and article titles use Large Body.
- **Large titles transition** (per George's video): each project phrase fades in as its image comes up and fades out
  as the next one arrives, while "Skelly + Couch are" stays in place.
- **News:** arrows removed. Scrolls sideways like krft.nl "Featured": trackpad / touch swipe or mouse drag,
  snapping to each article. The three Figma articles are repeated to show the scroll (prototype only).
- **Article tags:** left-aligned with a 20px gap; always visible on touch screens.

### Added
- **"Find out more" hover** (Figma R1 Basic Button): the words lift and a thin line appears under them.
- **"Contact us to get started." hover** (Figma R1 Ending CTA): a plus slides in and the line turns blue (`#1e4db2`).
- **Menu and footer lists:** hovering a link fades the others to 40%, like hkrarchitects.com, instead of an underline.

### Pending (waiting on George)
- SVG wordmark and S+C mark, plus the logo interaction.
- Hamburger bars sized from the plus in the logo, and their collapse-into-the-top-bar interaction.
- Chromatic Pro web fonts, once the client has bought the licence.

## [0.0.2] - 2026-11-06

### Added
- **Homepage prototype** at `/homepage-prototype/` for client review, built from the Figma file
  *S+C Website — Final* (Desktop Design → Homepage).
  - Standalone Blade view: `resources/views/prototype/homepage.blade.php`, with its own header, footer, CSS and JS.
    It doesn't use the theme layout or `wp_head()`.
  - Sections: hero, intro text, six project panels, latest news carousel, ending CTA, footer.
    Each section is marked with the ACF layout it will become.
  - `noindex, nofollow` meta tag, so the page isn't indexed.
- **Prototype routing:** `app/prototype.php`, loaded from `functions.php`.
  - Rewrite rule that maps the URL slug to the view, so there's no page in the database.
  - Flushes the rewrite rules once, controlled by a version constant.
  - Extendable: add an entry to `prototypes()` for more prototype pages.
- **Reveal component:** a reusable "image grows to cover" animation, written to be reused on inner pages.
  - Layers: `.reveal__base` (before image), `.reveal__frame` (S + C letters, behind the layer), `.reveal__layer` (after image and content).
  - Sequence: the window grows from a point to a square, then the S + C slide out from inside the window,
    then the window grows to full screen with the letters following its corners.
  - Smooth on large and retina screens: it animates `clip-path` and transforms with the Web Animations API.
  - The window position is set in image coordinates, so it stays on the same spot at every screen size.
  - Configured with data attributes:
    | Attribute | Purpose | Default |
    | --- | --- | --- |
    | `data-reveal-rect` | window as `x y w h` fractions of the base image | `0.45 0.45 0.1 0.1` |
    | `data-reveal-trigger` | `load`, `inview` or `manual` | `load` |
    | `data-reveal-window` / `-mark` / `-open` | when each step starts, in ms | `1100` / `3200` / `5800` |
    | `data-reveal-pop` | point-to-square time, in ms | `350` (hero: `1100`) |
    | `data-reveal-mark-in` | S + C slide-out time, in ms | `900` |
    | `data-reveal-duration` | full-screen grow time, in ms | `1100` |
    | `data-reveal-easing` | grow easing | `cubic-bezier(.76, 0, .24, 1)` |
    | `data-reveal-ghost` | selector for outside UI (e.g. `#siteHeader`) revealed with the image | — |
    | `data-reveal-lift` | on a child element: rises into place during the grow, in % of the block's height | `4` |
  - JS API: `Reveal.init(el)` returns `{ play(), skip(), finished }` and fires `reveal:open` and `reveal:done` events.
  - Respects `prefers-reduced-motion`: the finished state is shown straight away.
- **Homepage intro:** the hero reveal plays once when the page loads.
  - The Alfriston photo opens into the building x-ray.
  - The headline rises into place while the image grows.
  - The logo, menu icon and project link are part of the revealed image, with no fade.
  - Scrolling is locked during the intro. Scrolling, a key press or a click skips ahead to the grow.
- **Project panels scroll effect:** "Skelly + Couch are" stays pinned while the project images scroll past.
  Each project's phrase pins under it and is cut off by the next panel's top edge. The last phrase stays
  until the News section covers it. Headline sizes are scaled on mobile so the first line fits on one line.
- **Header behaviour:** the full wordmark shows at the top of the page and switches to the S+C mark once you scroll.
  Its colour follows the section underneath. The menu button opens the blue navigation bar.

### Notes
- Prototype images go in `wp-content/uploads/prototype/`, which isn't tracked by git.
  Upload that folder to staging by hand.
- Staging needs pretty permalinks: run `wp rewrite structure '/%postname%/' --hard` if they aren't on.
- Placeholders still to replace: the Chromatic Pro font (Inter Tight is a stand-in), the logo (typed text,
  not the SVG) and the images (copied from Figma screen captures, not the originals).
- To remove the prototype later, delete `app/prototype.php`, its entry in `functions.php`,
  `resources/views/prototype/` and `uploads/prototype/`.
