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
  .logo-mark{display:block;width:calc(92 * var(--u));height:calc(106 * var(--u))}
  .logo{display:block;font-weight:500;letter-spacing:-.02em;line-height:.88}
  .logo-word{font-size:calc(51 * var(--u));text-transform:uppercase}
  .logo-word span{display:block}
  .site-header .logo{position:absolute;left:0;top:0}

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
    --rv-k:calc(var(--rv-m) * .0225)}
  .reveal__mark--s{width:calc(30 * var(--rv-k));height:calc(40 * var(--rv-k))}
  .reveal__mark--plus{width:calc(24.11 * var(--rv-k));height:calc(25.04 * var(--rv-k))}
  .reveal__mark--c{width:calc(36 * var(--rv-k));height:calc(40 * var(--rv-k))}
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
  .site-footer .logo-word{font-size:calc(56 * var(--u))}
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
  <a class="logo" href="#top" aria-label="Skelly + Couch, home"><svg class="logo-mark" viewBox="0 0 92 106" fill="currentColor" aria-hidden="true"><path d="M47.3414 40.1956C45.1133 38.4461 41.943 37.3679 37.8305 36.9635L36.2467 36.797C33.1901 36.4641 30.9701 35.8113 29.5948 34.8362C28.2168 33.861 27.5291 32.5608 27.5291 30.9408C27.5291 29.3208 28.2736 28.1501 29.7654 27.2093C31.2544 26.2711 33.1495 25.8007 35.4535 25.8007C37.7574 25.8007 39.7825 26.3531 41.1984 27.4577C42.6144 28.565 43.3968 30.074 43.5484 31.99H50C49.6995 28.343 48.3025 25.4334 45.8117 23.2585C43.321 21.0862 39.8502 20 35.3966 20C32.6406 20 30.1688 20.4889 27.9812 21.4641C25.791 22.4392 24.0745 23.7764 22.8292 25.4704C21.5838 27.1644 20.9611 29.0803 20.9611 31.2156C20.9611 34.5322 22.0386 37.0825 24.1883 38.8689C26.3406 40.6554 29.3593 41.7521 33.2443 42.1564L35.1692 42.3784C35.865 42.4498 36.5175 42.5344 37.1266 42.6321H49.4125C48.8873 41.6808 48.1969 40.8668 47.3414 40.1956ZM26.7927 46.8525H20C20.1868 49.4688 20.9232 51.7627 22.2065 53.7315C23.4898 55.703 25.2739 57.241 27.5562 58.3457C29.7816 59.4239 32.3779 59.9736 35.3398 60V54.1993C32.8057 54.1623 30.805 53.491 29.3403 52.1855C27.8296 50.8404 26.9795 49.0645 26.7927 46.8525Z"/><path d="M85.1432 91.2945C84.5721 94.134 83.3423 96.3074 81.4566 97.8197C79.5709 99.3294 77.1606 100.086 74.2282 100.086C70.6099 100.086 67.8005 98.8508 65.8 96.3814C63.7996 93.912 62.8021 90.4484 62.8021 85.9881C62.8021 85.7502 62.8048 85.5148 62.8103 85.2822H56.0082C56.0027 85.5148 56 85.7502 56 85.9881C56 90.0783 56.7433 93.6264 58.23 96.6299C59.714 99.6334 61.8374 101.947 64.6003 103.568C67.3632 105.188 70.5716 106 74.2282 106C79.1036 106 83.1127 104.702 86.2555 102.103C89.3983 99.5039 91.3141 95.9028 92 91.2945H85.1432ZM84.9711 80.0181H91.9426C91.0654 75.5948 89.084 72.1498 85.9986 69.6804C83.1619 67.4119 79.6174 66.1851 75.3651 66V71.9383C77.647 72.1048 79.5928 72.8002 81.1997 74.0217C83.0663 75.4388 84.3234 77.4403 84.9711 80.0181Z"/><path d="M35.1931 76.1609H28.9553V101.2H35.1931V76.1609Z"/><path d="M44.1307 85.0984H20.0178V91.3362H44.1307V85.0984Z"/></svg></a>
  <button class="menu-btn" data-menu-toggle aria-expanded="false" aria-controls="navBar"><span class="burger" aria-hidden="true"><i></i><i></i><i></i></span><span class="sr-only">Open menu</span></button>
</header>

<nav class="nav-bar" id="navBar" aria-label="Primary">
  <a class="logo" href="#top" aria-label="Skelly + Couch, home"><span class="logo-word"><span>Skelly</span><span>+Couch</span></span></a>
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
      <svg class="reveal__mark reveal__mark--s" viewBox="20 20 30 40" fill="currentColor"><path d="M47.3414 40.1956C45.1133 38.4461 41.943 37.3679 37.8305 36.9635L36.2467 36.797C33.1901 36.4641 30.9701 35.8113 29.5948 34.8362C28.2168 33.861 27.5291 32.5608 27.5291 30.9408C27.5291 29.3208 28.2736 28.1501 29.7654 27.2093C31.2544 26.2711 33.1495 25.8007 35.4535 25.8007C37.7574 25.8007 39.7825 26.3531 41.1984 27.4577C42.6144 28.565 43.3968 30.074 43.5484 31.99H50C49.6995 28.343 48.3025 25.4334 45.8117 23.2585C43.321 21.0862 39.8502 20 35.3966 20C32.6406 20 30.1688 20.4889 27.9812 21.4641C25.791 22.4392 24.0745 23.7764 22.8292 25.4704C21.5838 27.1644 20.9611 29.0803 20.9611 31.2156C20.9611 34.5322 22.0386 37.0825 24.1883 38.8689C26.3406 40.6554 29.3593 41.7521 33.2443 42.1564L35.1692 42.3784C35.865 42.4498 36.5175 42.5344 37.1266 42.6321H49.4125C48.8873 41.6808 48.1969 40.8668 47.3414 40.1956ZM26.7927 46.8525H20C20.1868 49.4688 20.9232 51.7627 22.2065 53.7315C23.4898 55.703 25.2739 57.241 27.5562 58.3457C29.7816 59.4239 32.3779 59.9736 35.3398 60V54.1993C32.8057 54.1623 30.805 53.491 29.3403 52.1855C27.8296 50.8404 26.9795 49.0645 26.7927 46.8525Z"/></svg>
      <svg class="reveal__mark reveal__mark--plus" viewBox="20.0178 76.1609 24.1129 25.0391" fill="currentColor"><path d="M35.1931 76.1609H28.9553V101.2H35.1931V76.1609Z"/><path d="M44.1307 85.0984H20.0178V91.3362H44.1307V85.0984Z"/></svg>
      <svg class="reveal__mark reveal__mark--c" viewBox="56 66 36 40" fill="currentColor"><path d="M85.1432 91.2945C84.5721 94.134 83.3423 96.3074 81.4566 97.8197C79.5709 99.3294 77.1606 100.086 74.2282 100.086C70.6099 100.086 67.8005 98.8508 65.8 96.3814C63.7996 93.912 62.8021 90.4484 62.8021 85.9881C62.8021 85.7502 62.8048 85.5148 62.8103 85.2822H56.0082C56.0027 85.5148 56 85.7502 56 85.9881C56 90.0783 56.7433 93.6264 58.23 96.6299C59.714 99.6334 61.8374 101.947 64.6003 103.568C67.3632 105.188 70.5716 106 74.2282 106C79.1036 106 83.1127 104.702 86.2555 102.103C89.3983 99.5039 91.3141 95.9028 92 91.2945H85.1432ZM84.9711 80.0181H91.9426C91.0654 75.5948 89.084 72.1498 85.9986 69.6804C83.1619 67.4119 79.6174 66.1851 75.3651 66V71.9383C77.647 72.1048 79.5928 72.8002 81.1997 74.0217C83.0663 75.4388 84.3234 77.4403 84.9711 80.0181Z"/></svg>
    </div>
    <div class="reveal__layer">
      <img src="{{ $img('hero-xray.jpg') }}" alt="Alfriston School Swimming Pool, building services x-ray">
      <div class="hero-head">
        <a class="logo" href="#top" aria-label="Skelly + Couch, home"><span class="logo-word"><span>Skelly</span><span>+Couch</span></span></a>
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
  <div><a class="logo" href="#top"><span class="logo-word"><span>Skelly</span><span>+Couch</span></span></a></div>
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
  function setMenu(open) {
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
