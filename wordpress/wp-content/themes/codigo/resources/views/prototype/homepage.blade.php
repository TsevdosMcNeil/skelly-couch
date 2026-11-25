{{--
  Homepage prototype (client preview) — served at /homepage-prototype by app/prototype.php.
  Standalone on purpose: own header/footer/CSS, no wp_head(). Each <section> maps to a future ACF layout.
  Images live in wp-content/uploads/prototype/.
--}}
@php
  $img = fn ($file) => wp_upload_dir()['baseurl']."/prototype/{$file}";
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Skelly + Couch — Homepage prototype</title>
<!-- Chromatic Pro (design font) isn't web-licensed here yet; Inter Tight is a stand-in -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500&display=swap" rel="stylesheet">
<style>
@verbatim
  /* ---- Design tokens (Figma variables) ---- */
  :root{
    --sc-white:#fff;
    --sc-black:#000;
    --sc-blue:#253f7c;
    --sc-off-white:#f6f7f1;
    --sc-slate:#5f6d78;
    --sc-blue-soft:#7f91b5;

    --font: "Chromatic Pro","Inter Tight",system-ui,sans-serif;

    --sc-blue-bright:#1e4db2;   /* hover colour (Figma: R1 Ending CTA hover) */

    /* Everything is designed on the 1920px artboard and scales proportionally:
       --u is 1px at 1920 wide, so calc(N * var(--u)) matches the Figma at 1920. */
    --u: calc(100vw / 1920);

    /* Figma "Desktop Styles" */
    --fs-display: calc(119 * var(--u));  --lh-display: 1;       --ls-display: -.03em;  /* Big Titles 119/119 -3% */
    --fs-heading: calc(56 * var(--u));   --lh-heading: 1.0357;  --ls-heading: -.02em;  /* Headings 56/58 -2% */
    --fs-lead:    calc(34 * var(--u));   --lh-lead: 1.1176;     --ls-lead: -.02em;     /* Large Body 34/38 -2% */
    --fs-body:    calc(18 * var(--u));   --lh-body: 1.2222;                            /* Body 18/22 0% */

    /* Figma "Desktop Padding" */
    --sp-15: calc(15 * var(--u));  --sp-30: calc(30 * var(--u));  --sp-60: calc(60 * var(--u));
    --sp-80: calc(80 * var(--u));  --sp-120: calc(120 * var(--u)); --sp-200: calc(200 * var(--u));

    --gutter: calc(20 * var(--u));
    --col: 25vw;           /* 4-column grid */
    --indent: 9.4vw;       /* headline left edge */
    --title-top: calc(var(--gutter)*.6);
    --ease: cubic-bezier(.22, 1, .36, 1);
    --line: calc(var(--lh-display) * var(--fs-display));
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:smooth}
  body{font-family:var(--font);color:var(--sc-black);background:var(--sc-white);-webkit-font-smoothing:antialiased}
  img{display:block;max-width:100%}
  a{color:inherit;text-decoration:none}
  .sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}

  /* ---- Shared bits ---- */
  .display{font-size:var(--fs-display);line-height:var(--lh-display);letter-spacing:var(--ls-display);font-weight:400}
  .lead{font-size:var(--fs-lead);line-height:var(--lh-lead);letter-spacing:var(--ls-lead)}
  .heading{font-size:var(--fs-heading);line-height:var(--lh-heading);letter-spacing:var(--ls-heading)}
  /* "+ Find out more" (Figma: R1 Basic Button). Hover: the words lift and a line appears under them */
  .more{display:inline-flex;gap:.6em;align-items:baseline;font-size:var(--fs-body);line-height:var(--lh-body)}
  .more::before{content:"+";font-weight:500}
  .more > span{position:relative;display:inline-block;transition:transform .3s var(--ease)}
  .more > span::after{content:"";position:absolute;left:0;right:0;top:calc(100% + .3em);
    height:max(1px, calc(2 * var(--u)));background:currentColor;opacity:0;transform:translateY(.15em);
    transition:opacity .3s var(--ease),transform .3s var(--ease)}
  a.more:hover > span, a:hover .more > span{transform:translateY(-.28em)}
  a.more:hover > span::after, a:hover .more > span::after{opacity:1;transform:none}
  .caption{font-size:var(--fs-body);line-height:var(--lh-body)}

  /* Lists (menu, footer): hovering one link fades the others back, like hkrarchitects.com */
  .hover-dim a{transition:opacity .3s ease}
  .hover-dim:has(a:hover) a:not(:hover){opacity:.4}

  /* ---- Header ----
     Fixed S+C mark + menu icon. It sits BELOW the hero (z-index), so on the homepage the hero covers it
     and it is revealed by the blue section's top edge as you scroll (Figma: homeScroll).
     Sizes are the Figma's at 1920: the SVG is used at its native size (1 unit = 1px, 20px built-in padding);
     the menu bars are as wide as the S (30) and as thick as the plus stroke (6.24). */
  .site-header{position:fixed;inset:0 0 auto 0;z-index:5;color:var(--sc-white);pointer-events:none;transition:color .3s}
  .site-header > *{pointer-events:auto}
  /* Logo SVGs from George. Both are 188.04 units tall = 86px at 1920, 20px from the top-left corner. */
  .logo{display:block}
  .logo-mark{display:block;height:calc(86 * var(--u));width:auto;aspect-ratio:160.85/188.04;overflow:visible}
  .wordmark{display:block;height:calc(86 * var(--u));width:auto;aspect-ratio:474.58/188.04}
  .site-header .logo{position:absolute;left:var(--gutter);top:calc(20 * var(--u))}

  /* Menu logo animation (George: S+C-Menu-SVG.json, 42 frames at 60fps):
     on open the + and C slide into place so the S+C mark lines up with the full wordmark; reverses on close */
  .logo-mark .mk-plus,.logo-mark .mk-c{transition:transform .7s cubic-bezier(.701, .159, .232, 1)}
  .nav-open .site-header .mk-plus{transform:translate(3.221px, -6.358px)}
  .nav-open .site-header .mk-c{transform:translate(-10.541px, 0px)}
  /* While the menu is open (and closing) the mark sits above the blue bar, in white */
  .nav-layer .site-header{z-index:101;color:var(--sc-white)!important}
  .nav-layer .site-header .menu-btn{visibility:hidden}
  /* At the top of the homepage the mark is hidden under the hero, so it is wiped in with the bar */
  .nav-from-top .site-header .logo{clip-path:inset(0 0 100% 0);transition:clip-path .45s cubic-bezier(.65, 0, .35, 1)}
  .nav-from-top .nav-open .site-header .logo{clip-path:inset(0 0 0 0)}

  .menu-btn{position:absolute;right:calc(var(--gutter) - 8 * var(--u));top:calc(22.5 * var(--u) - 8 * var(--u));
    padding:calc(8 * var(--u));background:none;border:0;color:inherit;cursor:pointer}
  .burger{position:relative;display:block;width:calc(30 * var(--u));height:calc(33.24 * var(--u))}
  .burger i{position:absolute;left:0;width:100%;height:calc(6.24 * var(--u));background:currentColor;
    transition:transform .35s cubic-bezier(.65, 0, .35, 1)}
  .burger i:nth-child(1){top:0}
  .burger i:nth-child(2){top:calc(13.5 * var(--u))}
  .burger i:nth-child(3){top:calc(27 * var(--u))}
  /* collapsed: the lower bars slide up into the top bar */
  .burger.is-collapsed i:nth-child(2){transform:translateY(calc(-13.5 * var(--u)))}
  .burger.is-collapsed i:nth-child(3){transform:translateY(calc(-27 * var(--u)))}

  /* ---- Menu bar (Figma: OpenMenu) ----
     Wipes down from the top over everything; content stays in place and is revealed by the moving edge.
     Inside: white wordmark (it covers the S+C mark), links, the menu icon collapsing into one bar, search. */
  .nav-bar{position:fixed;inset:0 0 auto 0;z-index:100;padding:calc(20 * var(--u)) var(--gutter);background:var(--sc-blue);color:var(--sc-white);
    clip-path:inset(0 0 100% 0);pointer-events:none;transition:clip-path .45s cubic-bezier(.65, 0, .35, 1)}
  .nav-open .nav-bar{clip-path:inset(0 0 0 0);pointer-events:auto}
  /* the bar's height follows the wordmark: 20px above and below it */
  .nav-bar .logo{position:relative;width:max-content}
  .nav-bar ul{list-style:none;display:flex;justify-content:space-between;position:absolute;
    left:calc(var(--col)*2);width:calc(780 * var(--u));top:calc(20 * var(--u));font-size:var(--fs-body);line-height:var(--lh-body)}
  .nav-bar .burger{transition-delay:0s}
  .nav-open .nav-bar .burger i{transition-delay:.1s}
  .nav-search{position:absolute;right:var(--gutter);top:calc(60 * var(--u));width:calc(40 * var(--u));height:calc(40 * var(--u));
    padding:0;color:inherit;background:none;border:0;cursor:pointer}
  .nav-search svg{display:block;width:100%;height:100%}

  /* Homepage only: wordmark + menu icon printed on the hero, so they scroll away with it */
  .hero-head{position:absolute;inset:0 0 auto 0;z-index:2;color:var(--sc-blue)}
  .hero-head .logo{position:absolute;left:var(--gutter);top:calc(20 * var(--u))}

  /* ---- Hero ---- */
  .hero{position:relative;z-index:6;height:100vh;min-height:620px;background:#dfe4ee}
  .hero h1{position:absolute;left:calc(var(--col)*2);top:var(--gutter);right:var(--gutter);color:var(--sc-blue);max-width:9.5em}
  .hero .project-link{position:absolute;left:calc(var(--col)*3);bottom:calc(var(--gutter)*1.5);color:var(--sc-blue)}

  /* ==== Reveal — reusable "window grows to cover" component ====================
     Markup:  .reveal > .reveal__base (before) + .reveal__frame (optional S+C) + .reveal__layer (after)
     Config:  data-reveal-rect="x y w h"   window, as fractions of the base image (0–1)
              data-reveal-trigger           load | inview | manual   (default load)
              data-reveal-window / -mark / -open   ms after start (default 1100 / 3200 / 5800)
              data-reveal-duration          grow time in ms (default 1100)
              data-reveal-easing            CSS easing for the grow
              data-reveal-pop               ms for the window to grow from a point to its square (default 350)
              data-reveal-mark-in           ms for the S + C to slide out from inside the window (default 900)
              data-reveal-ghost="#sel"      fixed UI outside the block (e.g. the site header) that should be
                                            "printed" on the revealed image: a copy is placed in the layer for the
                                            duration, and the real element is shown instantly when it ends
              [data-reveal-lift="4"]        on any element inside the layer: it rises this many % of the reveal's
                                            height into place while the window grows
     JS:      Reveal.init(el) → { play(), skip(), finished }; fires reveal:open and reveal:done on el
     The grow uses the Web Animations API on clip-path + transforms only (no layout per frame),
     so it stays smooth on large / retina screens. */
  .reveal{position:relative;overflow:hidden}
  .reveal__base,.reveal__layer{position:absolute;inset:0}
  .reveal__base{z-index:0;display:none}
  .reveal__frame{z-index:1;display:none;position:absolute;pointer-events:none}  /* behind the revealed layer */
  .reveal__layer{z-index:2}
  .reveal__base img,.reveal__layer > img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  /* JS states (without JS, or with reduced motion, you just get the final layer) */
  .reveal[data-state]:not([data-state="done"]) .reveal__base,
  .reveal[data-state]:not([data-state="done"]) .reveal__frame{display:block}
  .reveal[data-state]:not([data-state="done"]) .reveal__layer{will-change:clip-path}
  .reveal[data-state="idle"] .reveal__layer{clip-path:inset(50% 50% 50% 50%)}
  /* The S, + and C are the pieces of the logo SVG; --rv-k = px per SVG unit, scaled to the window */
  .reveal__mark{position:absolute;display:block;color:var(--sc-white);opacity:0;transition:opacity .3s ease;will-change:transform;
    --rv-k:calc(var(--rv-m) * .01029)}
  .reveal__mark--s{width:calc(64.92 * var(--rv-k));height:calc(88.61 * var(--rv-k))}
  .reveal__mark--plus{width:calc(52.68 * var(--rv-k));height:calc(54.67 * var(--rv-k))}
  .reveal__mark--c{width:calc(77.1 * var(--rv-k));height:calc(88.55 * var(--rv-k))}
  .reveal[data-state="mark"] .reveal__mark,.reveal[data-state="open"] .reveal__mark{opacity:1}
  .reveal.is-closing .reveal__mark{opacity:0;transition-duration:.4s}
  .reveal__mark--s{left:calc(var(--rv-m) * -.34);top:calc(var(--rv-m) * -.5)}
  .reveal__mark--plus{left:calc(var(--rv-m) * -.3);bottom:calc(var(--rv-m) * -.34)}
  .reveal__mark--c{right:calc(var(--rv-m) * -.4);bottom:calc(var(--rv-m) * -.45)}

  /* Page intro: hide the header and lock scrolling while the hero reveal plays */
  .site-header{transition:color .3s}
  html.is-intro{overflow:hidden}
  html.is-intro .site-header{visibility:hidden}
  .reveal__ghost{position:absolute!important;pointer-events:none;visibility:visible!important}
  html.is-intro .proto-badge{opacity:0}

  /* ---- Intro ---- */
  .intro{background:var(--sc-blue);color:var(--sc-white);min-height:75vh;padding:calc(var(--gutter)*2) var(--gutter) 10vh calc(var(--col)*2)}
  .intro p{font-size:var(--fs-lead);line-height:var(--lh-lead);letter-spacing:var(--ls-lead);max-width:20.5em}
  .intro p + p{margin-top:1.15em}
  .intro .more{margin-top:2em}

  /* ---- Project panels (sticky stack) ---- */
  .projects{position:relative}
  /* overflow:clip (not hidden) so the sticky .panel-head still works; isolate so each
     later panel paints over the previous panel's pinned phrase */
  .panel{position:relative;height:100vh;min-height:620px;overflow:clip;isolation:isolate;color:var(--sc-white)}
  /* negative margin = never pushed up by the panel's bottom edge: the phrase stays pinned
     and is covered by the next panel (or clipped by this one) instead */
  /* The phrase rides at the top of its panel and pins one line down, under "Skelly + Couch are" */
  .panel-head{position:sticky;top:var(--line);z-index:1;opacity:var(--head-o,1);margin-bottom:-100vh}
  /* Shared first line: sticks to the top while the panels scroll under it */
  /* Track runs past the end of the projects so the prefix stays pinned until News covers it */
  .projects-track{position:absolute;top:0;left:0;right:0;bottom:-100vh;z-index:3;pointer-events:none}
  .projects-prefix{position:sticky;top:0;color:var(--sc-white);white-space:nowrap}
  .projects-prefix span{display:block;padding:var(--title-top) var(--gutter) 0 var(--indent)}
  .panel > img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .panel::after{content:"";position:absolute;inset:0;z-index:0;background:linear-gradient(180deg,rgba(0,0,0,.28),rgba(0,0,0,0) 45%)}
  .panel h2{padding:var(--title-top) var(--gutter) 0 var(--indent);max-width:calc(var(--col)*2 + var(--col)*.5)}
  /* Project link is fixed to the panel: it scrolls with the image and never fades */
  .panel .project-link{position:absolute;z-index:1;left:calc(var(--col)*3);top:calc(var(--title-top) + var(--gutter)*.6)}
  /* First panel arrives together with "Skelly + Couch are", so its phrase starts one line down */
  .panel:first-of-type h2{padding-top:calc(var(--title-top) + var(--line))}
  .panel:first-of-type .panel-head{top:0}
  .panel .project-link .caption{display:block}

  /* ---- News ---- */
  .news{position:relative;z-index:4;background:var(--sc-slate);color:var(--sc-white);padding:12vh 0 14vh var(--indent)}
  .news-head{display:flex;align-items:baseline;padding-right:var(--gutter);margin-bottom:3.2vw}
  .news-head h2{font-size:var(--fs-lead);line-height:var(--lh-lead);letter-spacing:var(--ls-lead);font-weight:400;flex:1}
  .news-head .more{position:absolute;left:calc(var(--col)*3)}
  /* Sideways scroll like krft.nl "Featured": trackpad / touch swipe or mouse drag, snaps to cards, no arrows */
  .news-track{display:flex;gap:var(--gutter);overflow-x:auto;overscroll-behavior-x:contain;scroll-snap-type:x mandatory;
    scrollbar-width:none;padding-right:var(--gutter);cursor:grab;-webkit-user-select:none;user-select:none}
  .news-track.is-dragging{cursor:grabbing;scroll-snap-type:none}
  .news-track.is-dragging a{pointer-events:none}
  .news-track img,.news-track a{-webkit-user-drag:none}
  .news-track::-webkit-scrollbar{display:none}
  .card{flex:0 0 32vw;scroll-snap-align:start}
  .card figure{aspect-ratio:1/1;overflow:hidden}
  .card img{width:100%;height:100%;object-fit:cover;transition:transform .6s ease}
  .card:hover img{transform:scale(1.03)}
  .card h3{font-size:var(--fs-lead);line-height:var(--lh-lead);letter-spacing:var(--ls-lead);font-weight:400;margin-top:var(--sp-30);max-width:15em}
  .card .meta{display:flex;justify-content:flex-start;gap:var(--gutter);font-size:var(--fs-body);line-height:var(--lh-body);margin-top:var(--sp-30);opacity:0;transition:opacity .25s}
  .card:hover .meta,.card:focus-within .meta{opacity:1}
  @media (hover: none){ .card .meta{opacity:1} }   /* touch screens have no hover */

  /* ---- Ending CTA ---- */
  .cta{position:relative;z-index:4;background:var(--sc-off-white);min-height:75vh;display:flex;align-items:center;padding:12vh var(--gutter) 12vh calc(var(--col)*2)}
  .cta p{font-size:var(--fs-heading);line-height:var(--lh-heading);letter-spacing:var(--ls-heading)}
  /* Figma: R1 Ending CTA. Hover: a plus slides in on the left and the line turns blue */
  .cta a{display:inline-flex;align-items:baseline;margin-top:var(--sp-60);transition:color .35s ease}
  .cta a::before{content:"+";font-weight:500;display:inline-block;overflow:hidden;max-width:0;margin-right:0;opacity:0;
    transition:max-width .4s var(--ease),margin-right .4s var(--ease),opacity .3s ease}
  .cta a:hover{color:var(--sc-blue-bright)}
  .cta a:hover::before{max-width:1em;margin-right:.35em;opacity:1}

  /* ---- Footer ---- */
  .site-footer{position:relative;z-index:4;background:var(--sc-blue);color:var(--sc-white);display:grid;grid-template-columns:repeat(4,1fr);
    padding:var(--gutter);min-height:42vh;font-size:var(--fs-body);line-height:var(--lh-body)}

  .site-footer ul{list-style:none;display:grid;gap:.85em;align-content:start}
  .site-footer .bottom{align-self:end;opacity:.55}
  .site-footer .muted{opacity:.55}

  /* ---- Prototype badge ---- */
  .proto-badge{position:fixed;right:var(--gutter);bottom:var(--gutter);z-index:60;font:500 11px/1 system-ui;letter-spacing:.04em;text-transform:uppercase;
    background:rgba(0,0,0,.65);color:#fff;padding:7px 9px;border-radius:3px}

  /* ---- Mobile ---- */
  @media (max-width: 820px){
    /* No mobile design yet: phones get their own sizes instead of scaling the 1920 artboard */
    :root{--col:50vw;--indent:var(--gutter);--u:.6px;--gutter:16px;--fs-body:15px;--fs-lead:22px;--fs-heading:30px}
    .hero h1{left:var(--gutter);top:auto;bottom:22vh}
    .hero .project-link{left:var(--gutter);top:auto;bottom:calc(var(--gutter)*2)}
    .panel .project-link{left:var(--gutter);top:auto;bottom:calc(var(--gutter)*2)}
    .intro,.cta{padding-left:var(--gutter)}
    .intro{padding-top:96px}
    :root{--title-top:80px;--fs-display:min(11vw, 48px)}
    .panel h2{max-width:none}
    .card{flex-basis:78vw}
    .news-head .more{position:static}
    .nav-bar{height:auto;padding:90px var(--gutter) 30px}
    .nav-bar .logo{position:absolute;left:var(--gutter);top:calc(20 * var(--u))}
    .nav-bar ul{position:static;flex-direction:column;gap:16px;margin:0;width:auto;font-size:22px}
    .nav-search{top:auto;bottom:30px}
    .site-footer{grid-template-columns:1fr 1fr;gap:30px var(--gutter)}
    .site-footer .bottom{grid-column:span 1}
  }
  @media (prefers-reduced-motion: reduce){
    *{transition:none!important;scroll-behavior:auto!important}
  }
@endverbatim
</style>
</head>
<body>

<!-- ===== Header (Blade: sections/header) ===== -->
<header class="site-header" id="siteHeader">
  <a class="logo" href="#top" aria-label="Skelly + Couch, home"><svg class="logo-mark" viewBox="0 0 160.85 188.04" fill="currentColor" aria-hidden="true"><path d="M 16.27,24.22 Q 16.27,29.61 20.74,32.85 Q 25.209999999999997,36.09 35.11,37.19 L 38.54,37.559999999999995 Q 51.879999999999995,38.91 59.099999999999994,44.72 C 60.94,46.21 62.44,48.01 63.58,50.11 L 33.16,50.11 C 31.839999999999996,49.95 30.239999999999995,49.68 29.089999999999996,49.47 C 20.779999999999994,47.949999999999996 13.699999999999996,45.74 9.049999999999997,41.78 Q 2.0799999999999974,35.84 2.0799999999999974,24.830000000000002 Q 2.0799999999999974,17.740000000000002 6.119999999999997,12.110000000000001 Q 10.159999999999997,6.490000000000001 17.25,3.240000000000002 Q 24.34,0.0 33.28,0.0 Q 47.71,0.0 55.790000000000006,7.22 Q 63.88000000000001,14.44 64.85000000000001,26.549999999999997 L 50.900000000000006,26.549999999999997 Q 50.410000000000004,20.189999999999998 45.82000000000001,16.509999999999998 Q 41.230000000000004,12.849999999999998 33.400000000000006,12.839999999999998 Q 25.950000000000006,12.839999999999998 21.110000000000007,15.959999999999997 Q 16.27,19.08 16.27,24.22M 16.33,84.91 Q 23.56,88.5 33.16,88.58 L 33.16,75.72 Q 24.939999999999998,75.6 20.189999999999998,71.27 Q 15.289999999999997,66.8 14.679999999999998,59.459999999999994 L 0.0,59.459999999999994 Q 0.62,68.13999999999999 4.77,74.69 Q 8.93,81.25 16.33,84.91"/><path class="mk-plus" d="M 33.16,122.66 L 19.529999999999994,122.66 L 19.529999999999994,142.19 L 0.0,142.19 L 0.0,155.82 L 19.53,155.82 L 19.53,177.37 L 33.160000000000004,177.37 L 33.160000000000004,155.82 L 52.690000000000005,155.82 L 52.690000000000005,142.19 L 33.160000000000004,142.19 L 33.160000000000004,122.66 L 33.16,122.66"/><path class="mk-c" d="M 145.8,130.54 L 160.73000000000002,130.54 Q 157.91000000000003,115.85 148.00000000000003,107.66 Q 138.89000000000004,100.13 125.23000000000003,99.52 L 125.23000000000003,112.64 Q 132.56000000000003,113.21 137.73000000000002,117.26 Q 143.71,121.97 145.8,130.54M 146.17,155.5 Q 144.32,164.92 138.28,169.94 Q 132.22,174.95 122.8,174.95 Q 111.17,174.95 104.75,166.75 Q 98.33,158.56 98.33,143.75 C 98.33,143.22 98.33,142.7 98.35,142.19 L 83.78999999999999,142.19 C 83.77999999999999,142.7 83.77,143.23 83.77,143.75 Q 83.77,157.34 88.53999999999999,167.31 Q 93.30999999999999,177.27 102.17999999999999,182.66 Q 111.05999999999999,188.04 122.8,188.04 Q 138.46,188.04 148.55,179.42 Q 158.64000000000001,170.79 160.85000000000002,155.5 L 146.17,155.5"/></svg></a>
  <button class="menu-btn" data-menu-toggle aria-expanded="false" aria-controls="navBar"><span class="burger" aria-hidden="true"><i></i><i></i><i></i></span><span class="sr-only">Open menu</span></button>
</header>

<nav class="nav-bar" id="navBar" aria-label="Primary">
  <a class="logo" href="#top" aria-label="Skelly + Couch, home"><svg class="wordmark" viewBox="0 0 474.58 188.04" fill="currentColor" aria-hidden="true"><defs><mask id="wordmarkCut" maskUnits="userSpaceOnUse" x="0" y="0" width="474.58" height="188.04"><rect width="474.58" height="188.04" fill="#fff"/><g fill="#000" stroke="#fff" stroke-width=".8"><path d="M 16.27,24.22 Q 16.27,29.61 20.74,32.85 Q 25.209999999999997,36.09 35.11,37.19 L 38.54,37.559999999999995 Q 51.879999999999995,38.91 59.099999999999994,44.72 C 60.94,46.21 62.44,48.01 63.58,50.11 L 33.16,50.11 C 31.839999999999996,49.95 30.239999999999995,49.68 29.089999999999996,49.47 C 20.779999999999994,47.949999999999996 13.699999999999996,45.74 9.049999999999997,41.78 Q 2.0799999999999974,35.84 2.0799999999999974,24.830000000000002 Q 2.0799999999999974,17.740000000000002 6.119999999999997,12.110000000000001 Q 10.159999999999997,6.490000000000001 17.25,3.240000000000002 Q 24.34,0.0 33.28,0.0 Q 47.71,0.0 55.790000000000006,7.22 Q 63.88000000000001,14.44 64.85000000000001,26.549999999999997 L 50.900000000000006,26.549999999999997 Q 50.410000000000004,20.189999999999998 45.82000000000001,16.509999999999998 Q 41.230000000000004,12.849999999999998 33.400000000000006,12.839999999999998 Q 25.950000000000006,12.839999999999998 21.110000000000007,15.959999999999997 Q 16.27,19.08 16.27,24.22M 16.33,84.91 Q 23.56,88.5 33.16,88.58 L 33.16,75.72 Q 24.939999999999998,75.6 20.189999999999998,71.27 Q 15.289999999999997,66.8 14.679999999999998,59.459999999999994 L 0.0,59.459999999999994 Q 0.62,68.13999999999999 4.77,74.69 Q 8.93,81.25 16.33,84.91"/><path d="M 33.16,122.66 L 19.529999999999994,122.66 L 19.529999999999994,142.19 L 0.0,142.19 L 0.0,155.82 L 19.53,155.82 L 19.53,177.37 L 33.160000000000004,177.37 L 33.160000000000004,155.82 L 52.690000000000005,155.82 L 52.690000000000005,142.19 L 33.160000000000004,142.19 L 33.160000000000004,122.66 L 33.16,122.66" transform="translate(3.221 -6.358)"/><path d="M 145.8,130.54 L 160.73000000000002,130.54 Q 157.91000000000003,115.85 148.00000000000003,107.66 Q 138.89000000000004,100.13 125.23000000000003,99.52 L 125.23000000000003,112.64 Q 132.56000000000003,113.21 137.73000000000002,117.26 Q 143.71,121.97 145.8,130.54M 146.17,155.5 Q 144.32,164.92 138.28,169.94 Q 132.22,174.95 122.8,174.95 Q 111.17,174.95 104.75,166.75 Q 98.33,158.56 98.33,143.75 C 98.33,143.22 98.33,142.7 98.35,142.19 L 83.78999999999999,142.19 C 83.77999999999999,142.7 83.77,143.23 83.77,143.75 Q 83.77,157.34 88.53999999999999,167.31 Q 93.30999999999999,177.27 102.17999999999999,182.66 Q 111.05999999999999,188.04 122.8,188.04 Q 138.46,188.04 148.55,179.42 Q 158.64000000000001,170.79 160.85000000000002,155.5 L 146.17,155.5" transform="translate(-10.541 0)"/></g></mask></defs><path d="M16.33,84.91q-7.4-3.67-11.56-10.21T0,59.46h14.68q.61,7.34,5.51,11.81,4.89,4.47,13.46,4.47,8.56,0,13.46-3.31,4.89-3.3,4.89-9.54,0-6.36-4.41-9.3-4.4-2.94-14.8-4.04l-4.16-.49q-12.6-1.34-19.58-7.28-6.97-5.93-6.97-16.94,0-7.1,4.04-12.73,4.04-5.62,11.13-8.87Q24.35,0,33.28,0q14.44,0,22.51,7.22,8.08,7.22,9.06,19.33h-13.95q-.49-6.36-5.08-10.03-4.59-3.67-12.42-3.67-7.46,0-12.29,3.12-4.84,3.12-4.84,8.26,0,5.38,4.47,8.62,4.46,3.24,14.37,4.34l3.43.37q13.33,1.35,20.56,7.16,7.21,5.81,7.21,17.92,0,7.84-4.03,13.71-4.04,5.87-11.44,9.05-7.41,3.18-17.19,3.18Q23.74,88.58,16.33,84.91ZM121.35,1.47h17.13l-33.77,42.82,33.77,42.82h-18.01l-29.71-35.93v35.93h-14.43V1.47h14.43v39.27L121.35,1.47ZM206.53,87.11h-58.73V1.47h57.38v13.09h-42.94v20.92h36.46v13.22h-36.46v25.32h44.29V87.11ZM229.92,1.47v72.55h43.55v13.09h-57.99V1.47H229.92ZM295.76,1.47v72.55h43.56v13.09h-57.99V1.47H295.76ZM370.42,87.11h-14.32v-31.2L325.51,1.47h15.91l21.9,40.5L385.22,1.47h15.79l-30.59,54.44V87.11ZM91.74,182.65q-8.87-5.38-13.64-15.35-4.77-9.97-4.77-23.55,0-13.58,4.77-23.56,4.77-9.97,13.64-15.35,8.87-5.38,20.62-5.39,15.29,0,25.2,8.2,9.91,8.2,12.72,22.88h-14.92q-2.08-8.56-8.08-13.27-5.99-4.71-14.92-4.71-11.63,0-18.05,8.19-6.42,8.2-6.42,23.01,0,14.8,6.42,23,6.42,8.2,18.05,8.2,9.42,0,15.47-5.02,6.06-5.02,7.89-14.44h14.69q-2.21,15.3-12.3,23.92-10.09,8.63-25.75,8.63Q100.61,188.04,91.74,182.65ZM175.94,182.65q-8.99-5.38-13.83-15.35-4.83-9.97-4.83-23.55,0-13.58,4.83-23.56,4.83-9.97,13.83-15.35,8.99-5.38,21.1-5.39,12.12,0,21.11,5.39,8.99,5.38,13.83,15.35,4.83,9.98,4.83,23.56,0,13.58-4.83,23.55-4.84,9.97-13.83,15.35-8.99,5.39-21.11,5.39Q184.93,188.04,175.94,182.65ZM215.64,166.75q6.61-8.2,6.61-23,0-14.81-6.61-23.01-6.61-8.19-18.6-8.19-11.99,0-18.59,8.19-6.61,8.2-6.61,23.01,0,14.8,6.61,23,6.6,8.2,18.59,8.2Q209.03,174.95,215.64,166.75ZM253.16,178.92q-8.38-9.11-8.38-26.98v-51.02h14.44v52.13q0,10.76,4.83,16.33,4.83,5.57,14.01,5.57,9.05,0,13.95-5.57,4.89-5.57,4.89-16.33v-52.13h14.32v51.02q0,17.87-8.38,26.98-8.39,9.12-24.78,9.12Q261.54,188.04,253.16,178.92ZM337.73,182.65q-8.88-5.38-13.65-15.35-4.77-9.97-4.77-23.55,0-13.58,4.77-23.56,4.78-9.97,13.65-15.35,8.87-5.38,20.61-5.39,15.3,0,25.21,8.2,9.91,8.2,12.72,22.88h-14.93q-2.08-8.56-8.07-13.27-6-4.71-14.93-4.71-11.62,0-18.04,8.19-6.43,8.2-6.43,23.01,0,14.8,6.43,23,6.42,8.2,18.04,8.2,9.42,0,15.48-5.02,6.06-5.02,7.89-14.44h14.68q-2.2,15.3-12.29,23.92-10.1,8.63-25.76,8.63Q346.6,188.04,337.73,182.65ZM460.26,135.55v-34.63h14.32v85.65h-14.32v-37.81h-40.13v37.81h-14.44v-85.65h14.44v34.63H460.26ZM56,135.92h-19.53v-19.53h-13.63v19.53H3.31v13.63h19.53v21.55h13.63v-21.55h19.53V135.92Z" mask="url(#wordmarkCut)"/></svg></a>
  <ul class="hover-dim">
    <li><a href="#">Work</a></li>
    <li><a href="#">Practice</a></li>
    <li><a href="#">Services</a></li>
    <li><a href="#news">News</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <button class="menu-btn" data-menu-close><span class="burger" aria-hidden="true"><i></i><i></i><i></i></span><span class="sr-only">Close menu</span></button>
  <button class="nav-search" aria-label="Search"><svg viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="4"><circle cx="18" cy="12" r="9"/><path d="M11.5 18.5 3.5 26.5"/></svg></button>
</nav>

<main id="top">

  <!-- ===== Layout: hero (ACF: hero_photo, hero_xray, headline, project) ===== -->
  <section class="hero reveal" id="hero" data-theme="blue"
           data-reveal data-reveal-trigger="load" data-reveal-pop="1100"  data-reveal-rect="0.47 0.458 0.085 0.126">
    <div class="reveal__base">
      <img src="{{ $img('alfriston.jpg') }}" alt="">
    </div>
    <div class="reveal__frame" aria-hidden="true">
      <svg class="reveal__mark reveal__mark--s" viewBox="0 0 64.92 88.61" fill="currentColor"><path d="M 16.27,24.22 Q 16.27,29.61 20.74,32.85 Q 25.209999999999997,36.09 35.11,37.19 L 38.54,37.559999999999995 Q 51.879999999999995,38.91 59.099999999999994,44.72 C 60.94,46.21 62.44,48.01 63.58,50.11 L 33.16,50.11 C 31.839999999999996,49.95 30.239999999999995,49.68 29.089999999999996,49.47 C 20.779999999999994,47.949999999999996 13.699999999999996,45.74 9.049999999999997,41.78 Q 2.0799999999999974,35.84 2.0799999999999974,24.830000000000002 Q 2.0799999999999974,17.740000000000002 6.119999999999997,12.110000000000001 Q 10.159999999999997,6.490000000000001 17.25,3.240000000000002 Q 24.34,0.0 33.28,0.0 Q 47.71,0.0 55.790000000000006,7.22 Q 63.88000000000001,14.44 64.85000000000001,26.549999999999997 L 50.900000000000006,26.549999999999997 Q 50.410000000000004,20.189999999999998 45.82000000000001,16.509999999999998 Q 41.230000000000004,12.849999999999998 33.400000000000006,12.839999999999998 Q 25.950000000000006,12.839999999999998 21.110000000000007,15.959999999999997 Q 16.27,19.08 16.27,24.22M 16.33,84.91 Q 23.56,88.5 33.16,88.58 L 33.16,75.72 Q 24.939999999999998,75.6 20.189999999999998,71.27 Q 15.289999999999997,66.8 14.679999999999998,59.459999999999994 L 0.0,59.459999999999994 Q 0.62,68.13999999999999 4.77,74.69 Q 8.93,81.25 16.33,84.91"/></svg>
      <svg class="reveal__mark reveal__mark--plus" viewBox="0 122.7 52.68 54.67" fill="currentColor"><path d="M 33.16,122.66 L 19.529999999999994,122.66 L 19.529999999999994,142.19 L 0.0,142.19 L 0.0,155.82 L 19.53,155.82 L 19.53,177.37 L 33.160000000000004,177.37 L 33.160000000000004,155.82 L 52.690000000000005,155.82 L 52.690000000000005,142.19 L 33.160000000000004,142.19 L 33.160000000000004,122.66 L 33.16,122.66"/></svg>
      <svg class="reveal__mark reveal__mark--c" viewBox="83.8 99.45 77.1 88.59" fill="currentColor"><path d="M 145.8,130.54 L 160.73000000000002,130.54 Q 157.91000000000003,115.85 148.00000000000003,107.66 Q 138.89000000000004,100.13 125.23000000000003,99.52 L 125.23000000000003,112.64 Q 132.56000000000003,113.21 137.73000000000002,117.26 Q 143.71,121.97 145.8,130.54M 146.17,155.5 Q 144.32,164.92 138.28,169.94 Q 132.22,174.95 122.8,174.95 Q 111.17,174.95 104.75,166.75 Q 98.33,158.56 98.33,143.75 C 98.33,143.22 98.33,142.7 98.35,142.19 L 83.78999999999999,142.19 C 83.77999999999999,142.7 83.77,143.23 83.77,143.75 Q 83.77,157.34 88.53999999999999,167.31 Q 93.30999999999999,177.27 102.17999999999999,182.66 Q 111.05999999999999,188.04 122.8,188.04 Q 138.46,188.04 148.55,179.42 Q 158.64000000000001,170.79 160.85000000000002,155.5 L 146.17,155.5"/></svg>
    </div>
    <div class="reveal__layer">
      <img src="{{ $img('hero-xray.jpg') }}" alt="Alfriston School Swimming Pool, building services x-ray">
      <div class="hero-head">
        <a class="logo" href="#top" aria-label="Skelly + Couch, home"><svg class="wordmark" viewBox="0 0 474.58 188.04" fill="currentColor" aria-hidden="true"><path d="M16.33,84.91q-7.4-3.67-11.56-10.21T0,59.46h14.68q.61,7.34,5.51,11.81,4.89,4.47,13.46,4.47,8.56,0,13.46-3.31,4.89-3.3,4.89-9.54,0-6.36-4.41-9.3-4.4-2.94-14.8-4.04l-4.16-.49q-12.6-1.34-19.58-7.28-6.97-5.93-6.97-16.94,0-7.1,4.04-12.73,4.04-5.62,11.13-8.87Q24.35,0,33.28,0q14.44,0,22.51,7.22,8.08,7.22,9.06,19.33h-13.95q-.49-6.36-5.08-10.03-4.59-3.67-12.42-3.67-7.46,0-12.29,3.12-4.84,3.12-4.84,8.26,0,5.38,4.47,8.62,4.46,3.24,14.37,4.34l3.43.37q13.33,1.35,20.56,7.16,7.21,5.81,7.21,17.92,0,7.84-4.03,13.71-4.04,5.87-11.44,9.05-7.41,3.18-17.19,3.18Q23.74,88.58,16.33,84.91ZM121.35,1.47h17.13l-33.77,42.82,33.77,42.82h-18.01l-29.71-35.93v35.93h-14.43V1.47h14.43v39.27L121.35,1.47ZM206.53,87.11h-58.73V1.47h57.38v13.09h-42.94v20.92h36.46v13.22h-36.46v25.32h44.29V87.11ZM229.92,1.47v72.55h43.55v13.09h-57.99V1.47H229.92ZM295.76,1.47v72.55h43.56v13.09h-57.99V1.47H295.76ZM370.42,87.11h-14.32v-31.2L325.51,1.47h15.91l21.9,40.5L385.22,1.47h15.79l-30.59,54.44V87.11ZM91.74,182.65q-8.87-5.38-13.64-15.35-4.77-9.97-4.77-23.55,0-13.58,4.77-23.56,4.77-9.97,13.64-15.35,8.87-5.38,20.62-5.39,15.29,0,25.2,8.2,9.91,8.2,12.72,22.88h-14.92q-2.08-8.56-8.08-13.27-5.99-4.71-14.92-4.71-11.63,0-18.05,8.19-6.42,8.2-6.42,23.01,0,14.8,6.42,23,6.42,8.2,18.05,8.2,9.42,0,15.47-5.02,6.06-5.02,7.89-14.44h14.69q-2.21,15.3-12.3,23.92-10.09,8.63-25.75,8.63Q100.61,188.04,91.74,182.65ZM175.94,182.65q-8.99-5.38-13.83-15.35-4.83-9.97-4.83-23.55,0-13.58,4.83-23.56,4.83-9.97,13.83-15.35,8.99-5.38,21.1-5.39,12.12,0,21.11,5.39,8.99,5.38,13.83,15.35,4.83,9.98,4.83,23.56,0,13.58-4.83,23.55-4.84,9.97-13.83,15.35-8.99,5.39-21.11,5.39Q184.93,188.04,175.94,182.65ZM215.64,166.75q6.61-8.2,6.61-23,0-14.81-6.61-23.01-6.61-8.19-18.6-8.19-11.99,0-18.59,8.19-6.61,8.2-6.61,23.01,0,14.8,6.61,23,6.6,8.2,18.59,8.2Q209.03,174.95,215.64,166.75ZM253.16,178.92q-8.38-9.11-8.38-26.98v-51.02h14.44v52.13q0,10.76,4.83,16.33,4.83,5.57,14.01,5.57,9.05,0,13.95-5.57,4.89-5.57,4.89-16.33v-52.13h14.32v51.02q0,17.87-8.38,26.98-8.39,9.12-24.78,9.12Q261.54,188.04,253.16,178.92ZM337.73,182.65q-8.88-5.38-13.65-15.35-4.77-9.97-4.77-23.55,0-13.58,4.77-23.56,4.78-9.97,13.65-15.35,8.87-5.38,20.61-5.39,15.3,0,25.21,8.2,9.91,8.2,12.72,22.88h-14.93q-2.08-8.56-8.07-13.27-6-4.71-14.93-4.71-11.62,0-18.04,8.19-6.43,8.2-6.43,23.01,0,14.8,6.43,23,6.42,8.2,18.04,8.2,9.42,0,15.48-5.02,6.06-5.02,7.89-14.44h14.68q-2.2,15.3-12.29,23.92-10.1,8.63-25.76,8.63Q346.6,188.04,337.73,182.65ZM460.26,135.55v-34.63h14.32v85.65h-14.32v-37.81h-40.13v37.81h-14.44v-85.65h14.44v34.63H460.26ZM56,135.92h-19.53v-19.53h-13.63v19.53H3.31v13.63h19.53v21.55h13.63v-21.55h19.53V135.92Z"/></svg></a>
        <button class="menu-btn" data-menu-toggle aria-expanded="false" aria-controls="navBar"><span class="burger" aria-hidden="true"><i></i><i></i><i></i></span><span class="sr-only">Open menu</span></button>
      </div>
      <h1 class="display" data-reveal-lift="4">Bringing life to buildings and buildings to life</h1>
      <a class="project-link" href="#">
        <span class="caption">Alfriston Swimming Pool</span><br>
        <span class="more"><span>Find out more</span></span>
      </a>
    </div>
  </section>

  <!-- ===== Layout: intro_text ===== -->
  <section class="intro" data-theme="light">
    <p>Skelly + Couch are multi-award-winning building environment and services engineers. Our engineering shapes interactions between people and the built environment in a way that allows them to coexist in harmony.</p>
    <p>While much of what we do is out of sight, it is our work that brings life to buildings, and buildings to life.</p>
    <a class="more" href="#"><span>Discover our Work</span></a>
  </section>

  <!-- ===== Layout: project_panels (repeater: image, statement, project title, link) ===== -->
  <div class="projects">
    <div class="projects-track" aria-hidden="true">
      <div class="projects-prefix display"><span>Skelly + Couch are</span></div>
    </div>
    <section class="panel" data-theme="light">
      <img src="{{ $img('appleby.jpg') }}" alt="Appleby Blue Almshouse" loading="lazy">
      <div class="panel-head">
        <h2 class="display"><span class="sr-only">Skelly + Couch are </span>harmonising people with nature</h2>
      </div>
      <a class="project-link" href="#"><span class="caption">Appleby Blue Almshouse</span><span class="more"><span>Find out more</span></span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('rhodes.jpg') }}" alt="Rhodes House" loading="lazy">
      <div class="panel-head">
        <h2 class="display"><span class="sr-only">Skelly + Couch are </span>thinking against the grain</h2>
      </div>
      <a class="project-link" href="#"><span class="caption">Rhodes House</span><span class="more"><span>Find out more</span></span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('farmiloe.jpg') }}" alt="Farmiloe Building" loading="lazy">
      <div class="panel-head">
        <h2 class="display"><span class="sr-only">Skelly + Couch are </span>united in curiosity</h2>
      </div>
      <a class="project-link" href="#"><span class="caption">Farmiloe Building</span><span class="more"><span>Find out more</span></span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('vitsoe.jpg') }}" alt="Vitsœ" loading="lazy">
      <div class="panel-head">
        <h2 class="display"><span class="sr-only">Skelly + Couch are </span>applying collective intelligence</h2>
      </div>
      <a class="project-link" href="#"><span class="caption">Vitsœ</span><span class="more"><span>Find out more</span></span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('notting-hill.jpg') }}" alt="Notting Hill and Ealing School" loading="lazy">
      <div class="panel-head">
        <h2 class="display"><span class="sr-only">Skelly + Couch are </span>collaborating with clarity</h2>
      </div>
      <a class="project-link" href="#"><span class="caption">Notting Hill and Ealing School</span><span class="more"><span>Find out more</span></span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('people.jpg') }}" alt="Skelly + Couch engineers reviewing a material board" loading="lazy">
      <div class="panel-head">
        <h2 class="display"><span class="sr-only">Skelly + Couch are </span>a hotbed for growth</h2>
      </div>
      <a class="project-link" href="#"><span class="caption">People</span><span class="more"><span>Find out more</span></span></a>
    </section>
  </div>

  <!-- ===== Layout: latest_news (query: 3 latest posts) ===== -->
  <section class="news" id="news" data-theme="light">
    <div class="news-head">
      <h2>News</h2>
      <a class="more" href="#"><span>Read more</span></a>
    </div>
    <div class="news-track" id="newsTrack">
      <article class="card">
        <a href="#">
          <figure><img src="{{ $img('news-1.jpg') }}" alt="" loading="lazy"></figure>
          <h3>Skelly + Couch’s RIBA National Awards high five</h3>
          <div class="meta"><span>24.06.26</span><span>Awards</span><span>News</span></div>
        </a>
      </article>
      <article class="card">
        <a href="#">
          <figure><img src="{{ $img('news-2.jpg') }}" alt="" loading="lazy"></figure>
          <h3>Appleby Blue Wins RIBA Stirling Prize 2025 in a Major Triple Victory</h3>
          <div class="meta"><span>12.06.26</span><span>Awards</span><span>News</span><span>Thought Piece</span></div>
        </a>
      </article>
      <article class="card">
        <a href="#">
          <figure><img src="{{ $img('news-3.jpg') }}" alt="" loading="lazy"></figure>
          <h3>S + C Focus on Theatre</h3>
          <div class="meta"><span>02.05.26</span><span>Thought Piece</span></div>
        </a>
      </article>
      <!-- prototype: same three articles repeated to show the sideways scroll -->
      <article class="card">
        <a href="#">
          <figure><img src="{{ $img('news-1.jpg') }}" alt="" loading="lazy"></figure>
          <h3>Skelly + Couch’s RIBA National Awards high five</h3>
          <div class="meta"><span>24.06.26</span><span>Awards</span><span>News</span></div>
        </a>
      </article>
      <article class="card">
        <a href="#">
          <figure><img src="{{ $img('news-2.jpg') }}" alt="" loading="lazy"></figure>
          <h3>Appleby Blue Wins RIBA Stirling Prize 2025 in a Major Triple Victory</h3>
          <div class="meta"><span>12.06.26</span><span>Awards</span><span>News</span><span>Thought Piece</span></div>
        </a>
      </article>
      <article class="card">
        <a href="#">
          <figure><img src="{{ $img('news-3.jpg') }}" alt="" loading="lazy"></figure>
          <h3>S + C Focus on Theatre</h3>
          <div class="meta"><span>02.05.26</span><span>Thought Piece</span></div>
        </a>
      </article>
    </div>
  </section>

  <!-- ===== Layout: ending_cta ===== -->
  <section class="cta" id="contact" data-theme="dark">
    <p>Our partnerships are enduring,<br>and constantly evolving.<br><a href="#">Contact us to get started.</a></p>
  </section>
</main>

<!-- ===== Footer (Blade: sections/footer) ===== -->
<footer class="site-footer" data-theme="light">
  <div><a class="logo" href="#top"><svg class="wordmark" viewBox="0 0 474.58 188.04" fill="currentColor" aria-hidden="true"><path d="M16.33,84.91q-7.4-3.67-11.56-10.21T0,59.46h14.68q.61,7.34,5.51,11.81,4.89,4.47,13.46,4.47,8.56,0,13.46-3.31,4.89-3.3,4.89-9.54,0-6.36-4.41-9.3-4.4-2.94-14.8-4.04l-4.16-.49q-12.6-1.34-19.58-7.28-6.97-5.93-6.97-16.94,0-7.1,4.04-12.73,4.04-5.62,11.13-8.87Q24.35,0,33.28,0q14.44,0,22.51,7.22,8.08,7.22,9.06,19.33h-13.95q-.49-6.36-5.08-10.03-4.59-3.67-12.42-3.67-7.46,0-12.29,3.12-4.84,3.12-4.84,8.26,0,5.38,4.47,8.62,4.46,3.24,14.37,4.34l3.43.37q13.33,1.35,20.56,7.16,7.21,5.81,7.21,17.92,0,7.84-4.03,13.71-4.04,5.87-11.44,9.05-7.41,3.18-17.19,3.18Q23.74,88.58,16.33,84.91ZM121.35,1.47h17.13l-33.77,42.82,33.77,42.82h-18.01l-29.71-35.93v35.93h-14.43V1.47h14.43v39.27L121.35,1.47ZM206.53,87.11h-58.73V1.47h57.38v13.09h-42.94v20.92h36.46v13.22h-36.46v25.32h44.29V87.11ZM229.92,1.47v72.55h43.55v13.09h-57.99V1.47H229.92ZM295.76,1.47v72.55h43.56v13.09h-57.99V1.47H295.76ZM370.42,87.11h-14.32v-31.2L325.51,1.47h15.91l21.9,40.5L385.22,1.47h15.79l-30.59,54.44V87.11ZM91.74,182.65q-8.87-5.38-13.64-15.35-4.77-9.97-4.77-23.55,0-13.58,4.77-23.56,4.77-9.97,13.64-15.35,8.87-5.38,20.62-5.39,15.29,0,25.2,8.2,9.91,8.2,12.72,22.88h-14.92q-2.08-8.56-8.08-13.27-5.99-4.71-14.92-4.71-11.63,0-18.05,8.19-6.42,8.2-6.42,23.01,0,14.8,6.42,23,6.42,8.2,18.05,8.2,9.42,0,15.47-5.02,6.06-5.02,7.89-14.44h14.69q-2.21,15.3-12.3,23.92-10.09,8.63-25.75,8.63Q100.61,188.04,91.74,182.65ZM175.94,182.65q-8.99-5.38-13.83-15.35-4.83-9.97-4.83-23.55,0-13.58,4.83-23.56,4.83-9.97,13.83-15.35,8.99-5.38,21.1-5.39,12.12,0,21.11,5.39,8.99,5.38,13.83,15.35,4.83,9.98,4.83,23.56,0,13.58-4.83,23.55-4.84,9.97-13.83,15.35-8.99,5.39-21.11,5.39Q184.93,188.04,175.94,182.65ZM215.64,166.75q6.61-8.2,6.61-23,0-14.81-6.61-23.01-6.61-8.19-18.6-8.19-11.99,0-18.59,8.19-6.61,8.2-6.61,23.01,0,14.8,6.61,23,6.6,8.2,18.59,8.2Q209.03,174.95,215.64,166.75ZM253.16,178.92q-8.38-9.11-8.38-26.98v-51.02h14.44v52.13q0,10.76,4.83,16.33,4.83,5.57,14.01,5.57,9.05,0,13.95-5.57,4.89-5.57,4.89-16.33v-52.13h14.32v51.02q0,17.87-8.38,26.98-8.39,9.12-24.78,9.12Q261.54,188.04,253.16,178.92ZM337.73,182.65q-8.88-5.38-13.65-15.35-4.77-9.97-4.77-23.55,0-13.58,4.77-23.56,4.78-9.97,13.65-15.35,8.87-5.38,20.61-5.39,15.3,0,25.21,8.2,9.91,8.2,12.72,22.88h-14.93q-2.08-8.56-8.07-13.27-6-4.71-14.93-4.71-11.62,0-18.04,8.19-6.43,8.2-6.43,23.01,0,14.8,6.43,23,6.42,8.2,18.04,8.2,9.42,0,15.48-5.02,6.06-5.02,7.89-14.44h14.68q-2.2,15.3-12.29,23.92-10.1,8.63-25.76,8.63Q346.6,188.04,337.73,182.65ZM460.26,135.55v-34.63h14.32v85.65h-14.32v-37.81h-40.13v37.81h-14.44v-85.65h14.44v34.63H460.26ZM56,135.92h-19.53v-19.53h-13.63v19.53H3.31v13.63h19.53v21.55h13.63v-21.55h19.53V135.92Z"/></svg></a></div>
  <div>Bringing life to buildings,<br>and buildings to life.</div>
  <ul class="hover-dim">
    <li><a href="#">Work</a></li>
    <li><a href="#">Practice</a></li>
    <li><a href="#">Services</a></li>
    <li><a href="#news">News</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <div class="muted">Design by TM</div>
  <div></div><div></div>
  <div class="bottom hover-dim"><a href="#">Instagram</a><br><a href="#">Linkedin</a></div>
  <div class="bottom hover-dim"><a href="#">Cookies &amp; Privacy Policy</a><br>©2026 Skelly + Couch</div>
</footer>

<div class="proto-badge">Prototype</div>

<script>
@verbatim
  // Vanilla JS for the prototype — each block maps 1:1 to a future Alpine component.
  const header = document.getElementById('siteHeader');
  const hero = document.getElementById('hero');

  /* ==== Reveal component (becomes resources/js/reveal.js / an Alpine component later) ==== */
  const Reveal = {
    init(el) {
      const d = el.dataset;
      const opt = {
        rect: (d.revealRect || '0.45 0.45 0.1 0.1').split(/\s+/).map(Number),
        trigger: d.revealTrigger || 'load',
        tWindow: +(d.revealWindow ?? 1100),
        tMark: +(d.revealMark ?? 3200),
        tOpen: +(d.revealOpen ?? 5800),
        duration: +(d.revealDuration ?? 1100),
        easing: d.revealEasing || 'cubic-bezier(.76, 0, .24, 1)',
        pop: +(d.revealPop ?? 350),
        markIn: +(d.revealMarkIn ?? 900),
      };
      const lifts = [...el.querySelectorAll('[data-reveal-lift]')];
      // Copies of outside UI, positioned exactly where the originals are, revealed with the image
      const ghosts = (d.revealGhost ? [...document.querySelectorAll(d.revealGhost)] : []).map(src => {
        const r = src.getBoundingClientRect(), c = src.cloneNode(true);
        c.removeAttribute('id'); c.querySelectorAll('[id]').forEach(n => n.removeAttribute('id'));
        c.setAttribute('aria-hidden', 'true'); c.inert = true;
        c.classList.add('reveal__ghost');
        Object.assign(c.style, { left: r.left + 'px', top: r.top + 'px', width: r.width + 'px', height: r.height + 'px' });
        el.querySelector('.reveal__layer').appendChild(c);
        return c;
      });
      const base = el.querySelector('.reveal__base img');
      const layer = el.querySelector('.reveal__layer');
      const frame = el.querySelector('.reveal__frame');
      const marks = {
        s: el.querySelector('.reveal__mark--s'),
        plus: el.querySelector('.reveal__mark--plus'),
        c: el.querySelector('.reveal__mark--c'),
      };
      const timers = [], anims = [];
      let g, finished, done = false;
      const finishedP = new Promise(r => finished = r);
      const set = state => { el.dataset.state = state; };

      // Window rect is given in image coordinates; convert to px in the element (object-fit: cover)
      function place() {
        const W = el.clientWidth, H = el.clientHeight;
        const iw = base?.naturalWidth || W, ih = base?.naturalHeight || H;
        const k = Math.max(W / iw, H / ih), dw = iw * k, dh = ih * k;
        const ox = (W - dw) / 2, oy = (H - dh) / 2;
        const [x, y, w, h] = opt.rect;
        g = { W, H, L: ox + x * dw, T: oy + y * dh, R: ox + (x + w) * dw, B: oy + (y + h) * dh };
        g.inset = `inset(${g.T}px ${W - g.R}px ${H - g.B}px ${g.L}px)`;
        const cx = (g.L + g.R) / 2, cy = (g.T + g.B) / 2;
        g.point = `inset(${cy}px ${W - cx}px ${H - cy}px ${cx}px)`;
        if (el.dataset.state !== 'idle') layer.style.clipPath = g.inset;
        if (frame) Object.assign(frame.style, { left: g.L + 'px', top: g.T + 'px', width: (g.R - g.L) + 'px', height: (g.B - g.T) + 'px' });
        el.style.setProperty('--rv-m', Math.min(g.R - g.L, g.B - g.T) + 'px'); // letters scale with the window
      }
      const onResize = () => { if (['idle','window','mark'].includes(el.dataset.state)) place(); };

      function finish() {
        if (done) return; done = true;
        timers.forEach(clearTimeout);
        anims.forEach(a => a.cancel());
        removeEventListener('resize', onResize);
        set('done');
        layer.style.clipPath = '';
        if (frame) frame.removeAttribute('style');
        el.style.removeProperty('--rv-m');
        el.classList.remove('is-closing');
        ghosts.forEach(c => c.remove());
        el.dispatchEvent(new CustomEvent('reveal:done'));
        finished();
      }
      function open() {
        if (done || el.dataset.state === 'open') return;
        set('open');
        anims.forEach(a => a.cancel()); anims.length = 0;
        const { W, H, L, T, R, B } = g;
        const o = { duration: opt.duration, easing: opt.easing, fill: 'forwards' };
        // Clip and letters share duration + easing, so the letters stay locked to the corners
        anims.push(layer.animate([{ clipPath: g.inset }, { clipPath: 'inset(0px 0px 0px 0px)' }], o));
        const move = (m, dx, dy) => m && anims.push(m.animate([{ transform: 'none' }, { transform: `translate(${dx}px, ${dy}px)` }], o));
        move(marks.s, -L, -T);
        move(marks.plus, -L, H - B);
        move(marks.c, W - R, H - B);
        lifts.forEach(n => anims.push(n.animate(
          [{ transform: `translateY(${H * (+n.dataset.revealLift || 4) / 100}px)` }, { transform: 'none' }], o)));
        el.dispatchEvent(new CustomEvent('reveal:open'));
        timers.push(setTimeout(() => el.classList.add('is-closing'), opt.duration * .8));
        anims[0].finished.then(() => timers.push(setTimeout(finish, 400))).catch(() => {});
      }
      async function play() {
        if (done || el.dataset.state !== 'idle') return finishedP;
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) { finish(); return finishedP; }
        // Wait for both images so the window never shows a half-loaded picture
        await Promise.all([...el.querySelectorAll('img')].map(i => i.decode().catch(() => {})));
        place();
        addEventListener('resize', onResize);
        timers.push(setTimeout(() => {
          set('window');
          layer.style.clipPath = g.inset;
          // grows from a point in the middle of the window to the full square
          anims.push(layer.animate([{ clipPath: g.point }, { clipPath: g.inset }],
            { duration: opt.pop, easing: 'cubic-bezier(.22, 1, .36, 1)' }));
        }, opt.tWindow));
        if (frame) timers.push(setTimeout(() => {
          set('mark');
          // Letters start at the middle of the window (hidden behind the x-ray layer)
          // and slide out to their corners
          const hw = (g.R - g.L) / 2, hh = (g.B - g.T) / 2;
          const from = { s: [hw, hh], plus: [hw, -hh], c: [-hw, -hh] };
          Object.entries(marks).forEach(([k, m]) => m && anims.push(m.animate(
            [{ transform: `translate(${from[k][0]}px, ${from[k][1]}px)`, opacity: 0 },
             { opacity: 1, offset: .3 },
             { transform: 'none', opacity: 1 }],
            { duration: opt.markIn, easing: 'cubic-bezier(.22, 1, .36, 1)' })));
        }, opt.tMark));
        timers.push(setTimeout(open, opt.tOpen));
        return finishedP;
      }
      function skip() {
        if (done) return;
        if (el.dataset.state === 'open') return finish();
        timers.forEach(clearTimeout); timers.length = 0;
        if (!g) place();
        layer.style.clipPath = g.inset;
        open();
      }

      set('idle');
      if (opt.trigger === 'load') play();
      if (opt.trigger === 'inview') {
        const io = new IntersectionObserver(([e]) => { if (e.isIntersecting) { io.disconnect(); play(); } }, { threshold: .6 });
        io.observe(el);
      }
      return { el, play, skip, finished: finishedP };
    },
  };

  // Home intro: hero reveal plays once on page load; header appears when it's done.
  // Any scroll / key / tap jumps straight to the "grow" step.
  document.documentElement.classList.add('is-intro');
  const heroReveal = Reveal.init(hero);
  const skipIntro = () => heroReveal.skip();
  const skipEvents = ['wheel', 'touchstart', 'keydown', 'pointerdown'];
  skipEvents.forEach(ev => addEventListener(ev, skipIntro, { passive: true, once: true }));
  heroReveal.finished.then(() => {
    skipEvents.forEach(ev => removeEventListener(ev, skipIntro));
    document.documentElement.classList.remove('is-intro');
    updateHeader();
  });
  // Other reveals on the page (e.g. inner-page images): <div class="reveal" data-reveal data-reveal-trigger="inview">
  document.querySelectorAll('[data-reveal]').forEach(el => { if (el !== hero) Reveal.init(el); });

  // Menu: the bar wipes down; its own menu icon collapses into one bar (and expands again on close)
  const navBar = document.getElementById('navBar');
  const navBurger = navBar.querySelector('.burger');
  const toggles = [...document.querySelectorAll('[data-menu-toggle]')];
  const root = document.documentElement;
  let navTimer;
  function setMenu(open) {
    if (open === document.body.classList.contains('nav-open')) return;
    clearTimeout(navTimer);
    if (open) {
      // Is the fixed S+C mark currently hidden under the hero? Then it gets wiped in with the bar
      const heroEl = document.getElementById('hero');
      root.classList.toggle('nav-from-top', !!heroEl && heroEl.getBoundingClientRect().bottom > header.querySelector('.logo').getBoundingClientRect().bottom);
      root.classList.add('nav-layer');
      root.offsetWidth;   // apply the start state before animating
    } else {
      navTimer = setTimeout(() => root.classList.remove('nav-layer', 'nav-from-top'), 700);
    }
    document.body.classList.toggle('nav-open', open);
    navBurger.classList.toggle('is-collapsed', open);
    toggles.forEach(t => t.setAttribute('aria-expanded', open));
    if (open) navBar.querySelector('[data-menu-close]').focus({ preventScroll: true });
  }
  toggles.forEach(t => t.addEventListener('click', () => setMenu(true)));
  navBar.querySelector('[data-menu-close]').addEventListener('click', () => setMenu(false));
  navBar.querySelectorAll('ul a').forEach(a => a.addEventListener('click', () => setMenu(false)));
  addEventListener('keydown', e => { if (e.key === 'Escape') setMenu(false); });

  // Header: colour follows the section underneath (the hero simply covers it)
  const themed = [...document.querySelectorAll('[data-theme]')];
  function updateHeader(){
    const probe = 30;
    let theme = 'light';
    for (const el of themed){
      const r = el.getBoundingClientRect();
      if (r.top <= probe && r.bottom > probe) theme = el.dataset.theme;
    }
    header.style.color = theme === 'dark' ? 'var(--sc-black)' : '';
  }
  addEventListener('scroll', updateHeader, {passive:true});
  updateHeader();

  // Project phrases: each fades in as its image comes up and fades out as the next one arrives
  const heads = [...document.querySelectorAll('.panel')].map((panel, i, all) => ({
    head: panel.querySelector('.panel-head'),
    panel,
    next: all[i + 1] || document.getElementById('news'),
  }));
  const clamp01 = v => Math.min(1, Math.max(0, v));
  let headsQueued = false;
  function updateHeads() {
    headsQueued = false;
    const H = innerHeight;
    for (const { head, panel, next } of heads) {
      const top = panel.getBoundingClientRect().top;
      const nextTop = next.getBoundingClientRect().top;
      const fadeIn = clamp01((H * .85 - top) / (H * .4));      // panel top 85% → 45% of the screen
      const fadeOut = clamp01((nextTop - H * .4) / (H * .35));  // next panel top 75% → 40%
      head.style.setProperty('--head-o', Math.min(fadeIn, fadeOut).toFixed(3));
    }
  }
  const queueHeads = () => { if (!headsQueued) { headsQueued = true; requestAnimationFrame(updateHeads); } };
  addEventListener('scroll', queueHeads, { passive: true });
  addEventListener('resize', queueHeads);
  updateHeads();

  // News: drag with the mouse to scroll sideways (touch and trackpads scroll natively), then snap to a card
  const track = document.getElementById('newsTrack');
  let drag = null;
  track.addEventListener('dragstart', e => e.preventDefault());   // stop the browser dragging the link/image
  track.addEventListener('pointerdown', e => {
    if (e.pointerType !== 'mouse' || e.button !== 0) return;
    drag = { x: e.clientX, left: track.scrollLeft, moved: false };
  });
  addEventListener('pointermove', e => {
    if (!drag) return;
    const dx = e.clientX - drag.x;
    if (!drag.moved && Math.abs(dx) > 4) { drag.moved = true; track.classList.add('is-dragging'); }
    if (drag.moved) track.scrollLeft = drag.left - dx;
  });
  addEventListener('pointerup', () => {
    if (!drag) return;
    const moved = drag.moved; drag = null;
    if (!moved) return;
    // Snap to the nearest card, then hand control back to native scroll snapping
    const cards = [...track.querySelectorAll('.card')];
    const x0 = cards[0].offsetLeft, pos = c => c.offsetLeft - x0;
    const target = cards.reduce((best, c) => Math.abs(pos(c) - track.scrollLeft) < Math.abs(pos(best) - track.scrollLeft) ? c : best);
    track.scrollTo({ left: pos(target), behavior: 'smooth' });
    setTimeout(() => track.classList.remove('is-dragging'), 450);
  });
@endverbatim
</script>
</body>
</html>
