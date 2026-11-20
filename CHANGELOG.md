
# Change Log

All notable changes to this project will be documented in this file.
 
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

---

## [0.0.6] - 2026-11-20

Project page, second part: the Contents block (Figma "Contents", between the introduction and the first chapter),
from George's recordings (Contents hover and scroll, Contents anchored while the chapters scroll over it).

### Added
- **Contents block** (`acf/contents`, ACF Composer): the page's chapters listed on a panel (650 wide at 1920) next to
  any blocks, normally an Image filling the rest (the exploded view, with its aperture "+").
  - The list is built from the page's Chapter blocks (short title, or title), so it follows chapters being added,
    renamed or reordered.
  - Hovering or focusing a chapter turns it black with the ↓ arrow and fades its highlight image in over the picture
    (300ms). The first chapter starts active; the last one hovered stays active.
  - Clicking a chapter scrolls to it (Figma "Scroll to": 1000ms).
  - The block fills the window and, once it reaches the top, stays put while the chapters scroll up over it.
  - On phones: list on top, image below, a tap scrolls. In the editor the list shows a thumbnail of each highlight.
  - Colours in the block's Styles → Color panel, with the palette swatches: **Text** (black by default; white on a
    dark list background), **Background** (image side, Off-white by default) and **List background** (Plaster by
    default; added to the core panel by `resources/js/filters/contents.colors.*.js`, saved as `panelColor`).
- **Highlight in Contents** image field on the Chapter block. The Contents block can replace any of them
  ("Replace a highlight": chapter + image, matched by the chapter's id). Highlights must have the same size and
  framing as the Contents image.
- A new project now starts with Project Hero, a blue Section, **Contents** and a Chapter.
- **Seed:** the Contents block, highlights for each chapter (`contents-*.jpg`, used when the files exist) and the
  "Listed Building Phasing / Fabric Upgrades" chapter (text only until the Building Phases block exists).

### Changed
- The chapter list for the "+" menu and the Contents block share one reader of the page's blocks
  (`App\page_nav()`, `App\chapters()`), and scrolling to a section is one helper (`scrollToElement()` in
  `resources/js/utils/motion.js`).

### Pending
- Work in progress on `feature/project-blocks`, not merged into `main` until the whole Project page is reviewed.
- Remaining parts of the Project page: image carousel, Impact stats, Building Phases with note tags
  (interactive list), accordion (core Details), video, quote, awards, team and related projects.
- Real Rhodes House images exported from the Figma for the seed (it uses placeholders for now), including the
  Contents exploded view, its aerial photo and one highlight per chapter (same size and framing).
- Check the Figma colours against the prototype's (Blue #233F7C vs #253F7C, Slate #626C77 vs #5F6D78,
  Off-white #F7F7F2 vs #F6F7F1).

## [0.0.5] - 2026-11-17

Project page, first part, built with Gutenberg blocks: ACF Composer blocks for the custom parts, core blocks
with theme styles for everything else. From the Figma "Project Page — Type A" (Rhodes House) and George's recordings.

### Added
- **Post types:** `project` (URL `/work/{slug}`, block editor) and `member` (Team, URL `/people/{slug}`).
  A new project starts with Project Hero, a blue Section (facts + lead) and a Chapter.
- **ACF Composer blocks** (`app/Blocks`, views in `resources/views/blocks`, "Skelly + Couch" category):
  - **Project Hero:** project name (post title), subtitle, full-width image. Fields saved as post meta.
  - **Section:** coloured band holding any blocks (the introduction). Optional name for the "+" menu.
  - **Chapter:** sticky bar (project name · title · "+") over any blocks; the next chapter's bar pushes it off.
    The "+" turns the bar plaster and opens a drop-down listing the page's sections (Figma "Scroll to": 1000ms).
- **Image block + aperture and comments:** the core Image block gets an **aperture "+"** (click or hover,
  set per image) and **comment "+"** markers (magnifying glass or growing note), in two sidebar panels.
  Positions are set by clicking on the image in the editor, which also shows the window and the markers.
  Each comment has a "+" padding (gap from the note's corner, px at 1920, default 0), a "+" colour and a note
  background colour; the note text turns black or white for contrast.
  Editors keep everything Image already has (alignment, size, crop, link, caption, replace…). A click aperture
  switches off "Expand on click" for that image. Front-end markup: `render_block_core/image` in `app/blocks.php`.
- **Images on the page grid:** an Image on its own uses the alignment toolbar: none = right half,
  Wide = between the page margins, Full width = edge to edge. Rows of images are core **Columns** or **Grid**
  (any number of images, any widths), on the page margins with the 20px gutter.
- **Core block styles** (`resources/js/styles`, auto-registered as in SAGE.md): Columns "Grid split",
  Table "Facts", Button "Plus link". Text blocks inside a Section or Chapter sit in the right half.
- The block CSS (`pcss/blocks`) is imported without a cascade layer, so it wins over WordPress's core block CSS.
- The fixed menu icon, menu bar and sticky chapter bars sit below the WordPress admin bar when logged in.
- **Editor:** palette (White, Off-white, Plaster, Blue, Slate, Black) and font sizes (Body, Large Body, Heading,
  Big Title) from the Figma; the page CSS is loaded into the editor so blocks look like the front end.
- **Team member fields** (ACF, sidebar): role, key projects. Photo = featured image, About = the editor.
- **Design system in the theme:** tokens in `resources/css/base.css`, custom CSS in `resources/css/pcss/`
  (header, menu bar, footer ported from the homepage prototype).
- **Seed:** `scripts/seed-rhodes-house.php` builds Rhodes House from blocks.

## [0.0.4] - 2026-11-17

Homepage prototype: logo and menu, from George's recordings (homeScroll, OpenMenu, OpenMenutop).

### Changed
- **Logo:** the S+C mark is now the SVG exported from Figma, used at its native size at 1920
  (1 unit = 1px, with its own 20px padding). The S, + and C in the intro animation use the same SVG pieces.
- **Header on the homepage:** the "SKELLY +COUCH" wordmark and menu icon are printed on the hero and scroll away with it.
  The fixed S+C mark and menu icon sit behind the hero: they are revealed by the blue section's top edge as you scroll
  down, and covered again when you scroll back up.
- **Menu icon:** bars as wide as the S (30px) and as thick as the plus stroke (6.24px), 20px from the right.
- **Project links ("Appleby Blue Almshouse / + Find out more")** are fixed to their panel: they scroll with the image
  and no longer fade with the large titles.
- **Large titles:** each phrase now arrives at the top of its image (no empty line above it) and pins one line down,
  under "Skelly + Couch are". The project links sit at the top right of every panel, the first one included.
- **Menu:** a blue bar wipes down from the top. Its height follows the wordmark, with 20px above and below it
  (130px at 1920 with the fallback font). It holds the white wordmark (covering the S+C mark), the links
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
