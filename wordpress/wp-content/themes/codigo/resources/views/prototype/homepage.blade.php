{{--
  Homepage prototype (client preview) — served at /homepage-prototype by app/prototype.php.
  Standalone on purpose: own header/footer/CSS, no wp_head(). Each <section> maps to a future ACF layout.
--}}
@php
  $img = fn ($file) => \Illuminate\Support\Facades\Vite::asset("resources/images/prototype/{$file}");
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

    /* 1920px artboard → fluid */
    --fs-display: clamp(48px, 6.2vw, 119px);   /* 119/118, -3.57px */
    --fs-lead:    clamp(22px, 1.75vw, 34px);
    --fs-cta:     clamp(28px, 2.6vw, 50px);
    --fs-h3:      clamp(20px, 1.6vw, 30px);
    --fs-body:    clamp(14px, .94vw, 18px);     /* 18/22 */
    --fs-small:   clamp(12px, .8vw, 15px);

    --gutter: clamp(16px, 1vw, 20px);
    --col: 25vw;           /* 4-column grid */
    --indent: 9.4vw;       /* headline left edge */
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:smooth}
  body{font-family:var(--font);color:var(--sc-black);background:var(--sc-white);-webkit-font-smoothing:antialiased}
  img{display:block;max-width:100%}
  a{color:inherit;text-decoration:none}
  .sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}

  /* ---- Shared bits ---- */
  .display{font-size:var(--fs-display);line-height:.99;letter-spacing:-.03em;font-weight:400}
  .more{display:inline-flex;gap:.6em;align-items:center;font-size:var(--fs-body);line-height:1.22}
  .more::before{content:"+";font-weight:500}
  .more:hover{text-decoration:underline;text-underline-offset:3px}
  .caption{font-size:var(--fs-body);line-height:1.22}

  /* ---- Header ---- */
  .site-header{position:fixed;inset:0 0 auto 0;z-index:50;display:flex;justify-content:space-between;align-items:flex-start;
    padding:var(--gutter);color:var(--sc-white);pointer-events:none;transition:color .3s}
  .site-header > *{pointer-events:auto}
  .site-header.is-blue{color:var(--sc-blue)}
  .logo{display:block;font-weight:500;letter-spacing:-.02em;line-height:.88}
  .logo-word{font-size:clamp(26px,2.3vw,44px);text-transform:uppercase}
  .logo-word span{display:block}
  .logo-mark{display:none;font-size:clamp(28px,2.4vw,46px);line-height:.85;width:1.6em}
  .logo-mark span{display:block}
  .logo-mark span:last-child{margin-top:.08em}
  .site-header.is-compact .logo-word{display:none}
  .site-header.is-compact .logo-mark{display:block}

  .menu-btn{width:34px;height:30px;background:none;border:0;color:inherit;cursor:pointer;display:grid;place-items:center}
  .menu-btn i{display:block;width:30px;height:4px;background:currentColor;box-shadow:0 -10px 0 currentColor,0 10px 0 currentColor;transition:box-shadow .2s}
  .nav-open .menu-btn i{box-shadow:none}

  .nav-bar{position:fixed;inset:0 0 auto 0;z-index:40;background:var(--sc-blue);color:var(--sc-white);
    height:clamp(90px,7.5vw,140px);transform:translateY(-100%);transition:transform .35s ease}
  .nav-open .nav-bar{transform:none}
  .nav-open .site-header{color:var(--sc-white)}
  .nav-open .site-header .logo-word{display:block}
  .nav-open .site-header .logo-mark{display:none}
  .nav-bar ul{list-style:none;display:flex;justify-content:space-between;width:calc(var(--col)*2 - 110px);
    margin-left:calc(var(--col)*2);padding-top:var(--gutter);font-size:var(--fs-body)}
  .nav-bar a:hover{text-decoration:underline;text-underline-offset:3px}
  .nav-search{position:absolute;right:var(--gutter);top:calc(var(--gutter) + 40px);width:30px;height:30px;color:inherit;background:none;border:0;cursor:pointer}

  /* ---- Hero ---- */
  .hero{position:relative;height:100vh;min-height:620px;overflow:hidden;background:#dfe4ee}
  .hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:opacity 1.4s ease}
  .hero .hero-photo{opacity:1}
  .hero.is-xray .hero-photo{opacity:0}
  .hero h1{position:absolute;left:calc(var(--col)*2);top:var(--gutter);right:var(--gutter);color:var(--sc-blue);max-width:9.5em}
  .hero .hero-photo ~ h1{transition:color 1.4s}
  .hero:not(.is-xray) h1{color:var(--sc-white)}
  .hero .project-link{position:absolute;left:calc(var(--col)*3);bottom:calc(var(--gutter)*1.5);color:var(--sc-blue)}
  .hero:not(.is-xray) .project-link{color:var(--sc-white)}

  /* ---- Intro ---- */
  .intro{background:var(--sc-blue);color:var(--sc-white);min-height:75vh;padding:calc(var(--gutter)*2) var(--gutter) 10vh calc(var(--col)*2)}
  .intro p{font-size:var(--fs-lead);line-height:1.18;max-width:20.5em}
  .intro p + p{margin-top:1.15em}
  .intro .more{margin-top:2em}

  /* ---- Project panels (sticky stack) ---- */
  .projects{position:relative}
  .panel{position:sticky;top:0;height:100vh;min-height:620px;overflow:hidden;color:var(--sc-white)}
  .panel img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .panel::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.28),rgba(0,0,0,0) 45%)}
  .panel h2{position:relative;z-index:1;padding:calc(var(--gutter)*.6) var(--gutter) 0 var(--indent);max-width:calc(var(--col)*2 + var(--col)*.5)}
  .panel .project-link{position:absolute;z-index:1;left:calc(var(--col)*3);top:calc(var(--gutter)*1.8)}
  .panel .project-link .caption{display:block}

  /* ---- News ---- */
  .news{position:relative;z-index:2;background:var(--sc-slate);color:var(--sc-white);padding:12vh 0 14vh var(--indent)}
  .news-head{display:flex;align-items:baseline;padding-right:var(--gutter);margin-bottom:3.2vw}
  .news-head h2{font-size:var(--fs-h3);font-weight:400;flex:1}
  .news-head .more{position:absolute;left:calc(var(--col)*3)}
  .news-track{display:flex;gap:1vw;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;padding-right:var(--gutter)}
  .news-track::-webkit-scrollbar{display:none}
  .card{flex:0 0 32vw;scroll-snap-align:start}
  .card figure{aspect-ratio:1/1;overflow:hidden}
  .card img{width:100%;height:100%;object-fit:cover;transition:transform .6s ease}
  .card:hover img{transform:scale(1.03)}
  .card h3{font-size:var(--fs-h3);font-weight:400;line-height:1.2;margin-top:1.1vw;max-width:15em}
  .card .meta{display:flex;gap:2.2em;font-size:var(--fs-small);margin-top:1.4em;opacity:0;transition:opacity .25s}
  .card:hover .meta,.card:focus-within .meta{opacity:1}
  .card .meta span + span{margin-left:auto}
  .news-nav{display:flex;gap:10px;margin-top:2.4vw}
  .news-nav button{width:34px;height:34px;border:1px solid currentColor;border-radius:50%;background:none;color:inherit;cursor:pointer;font:inherit}

  /* ---- Ending CTA ---- */
  .cta{position:relative;z-index:2;background:var(--sc-off-white);min-height:75vh;display:flex;align-items:center;padding:12vh var(--gutter) 12vh calc(var(--col)*2)}
  .cta p{font-size:var(--fs-cta);line-height:1.15;letter-spacing:-.01em}
  .cta a{display:inline-block;margin-top:1.1em;border-bottom:2px solid transparent;transition:border-color .2s}
  .cta a:hover{border-color:currentColor}

  /* ---- Footer ---- */
  .site-footer{position:relative;z-index:2;background:var(--sc-blue);color:var(--sc-white);display:grid;grid-template-columns:repeat(4,1fr);
    padding:var(--gutter);min-height:42vh;font-size:var(--fs-body);line-height:1.22}
  .site-footer .logo-word{font-size:clamp(32px,2.9vw,56px)}
  .site-footer ul{list-style:none;display:grid;gap:.85em;align-content:start}
  .site-footer .bottom{align-self:end;opacity:.55}
  .site-footer .muted{opacity:.55}
  .site-footer a:hover{text-decoration:underline;text-underline-offset:3px}

  /* ---- Prototype badge ---- */
  .proto-badge{position:fixed;right:var(--gutter);bottom:var(--gutter);z-index:60;font:500 11px/1 system-ui;letter-spacing:.04em;text-transform:uppercase;
    background:rgba(0,0,0,.65);color:#fff;padding:7px 9px;border-radius:3px}

  /* ---- Mobile ---- */
  @media (max-width: 820px){
    :root{--col:50vw;--indent:var(--gutter)}
    .hero h1{left:var(--gutter);top:auto;bottom:22vh}
    .hero .project-link,.panel .project-link{left:var(--gutter);top:auto;bottom:calc(var(--gutter)*2)}
    .intro,.cta{padding-left:var(--gutter)}
    .intro{padding-top:96px}
    .panel h2{padding-top:90px;max-width:none}
    .card{flex-basis:78vw}
    .news-head .more{position:static}
    .nav-bar{height:auto;padding:90px var(--gutter) 30px}
    .nav-bar ul{flex-direction:column;gap:16px;margin:0;width:auto;font-size:22px}
    .nav-search{top:auto;bottom:30px}
    .site-footer{grid-template-columns:1fr 1fr;gap:30px var(--gutter)}
    .site-footer .bottom{grid-column:span 1}
  }
  @media (prefers-reduced-motion: reduce){
    *{transition:none!important;scroll-behavior:auto!important}
    .panel{position:relative}
  }
@endverbatim
</style>
</head>
<body>

<!-- ===== Header (Blade: sections/header) ===== -->
<header class="site-header is-blue" id="siteHeader">
  <a class="logo" href="#top" aria-label="Skelly + Couch, home">
    <span class="logo-word"><span>Skelly</span><span>+Couch</span></span>
    <span class="logo-mark" aria-hidden="true"><span>S</span><span>+C</span></span>
  </a>
  <button class="menu-btn" id="menuBtn" aria-expanded="false" aria-controls="navBar"><i></i><span class="sr-only">Menu</span></button>
</header>

<nav class="nav-bar" id="navBar" aria-label="Primary">
  <ul>
    <li><a href="#">Work</a></li>
    <li><a href="#">Practice</a></li>
    <li><a href="#">Services</a></li>
    <li><a href="#news">News</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <button class="nav-search" aria-label="Search">
    <svg viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="3.2"><circle cx="17" cy="12" r="9"/><path d="M10.5 18.5 3 26"/></svg>
  </button>
</nav>

<main id="top">

  <!-- ===== Layout: hero (ACF: hero_photo, hero_xray, headline, project) ===== -->
  <section class="hero" id="hero" data-theme="blue">
    <img class="hero-xray" src="{{ $img('hero-xray.jpg') }}" alt="">
    <img class="hero-photo" src="{{ $img('alfriston.jpg') }}" alt="Alfriston School Swimming Pool">
    <h1 class="display">Bringing life to buildings and buildings to life</h1>
    <a class="project-link" href="#">
      <span class="caption">Alfriston Swimming Pool</span><br>
      <span class="more">Find out more</span>
    </a>
  </section>

  <!-- ===== Layout: intro_text ===== -->
  <section class="intro" data-theme="light">
    <p>Skelly + Couch are multi-award-winning building environment and services engineers. Our engineering shapes interactions between people and the built environment in a way that allows them to coexist in harmony.</p>
    <p>While much of what we do is out of sight, it is our work that brings life to buildings, and buildings to life.</p>
    <a class="more" href="#">Discover our Work</a>
  </section>

  <!-- ===== Layout: project_panels (repeater: image, statement, project title, link) ===== -->
  <div class="projects">
    <section class="panel" data-theme="light">
      <img src="{{ $img('appleby.jpg') }}" alt="Appleby Blue Almshouse" loading="lazy">
      <h2 class="display">Skelly + Couch are harmonising people with nature</h2>
      <a class="project-link" href="#"><span class="caption">Appleby Blue Almshouse</span><span class="more">Find out more</span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('rhodes.jpg') }}" alt="Rhodes House" loading="lazy">
      <h2 class="display">Skelly + Couch are thinking against the grain</h2>
      <a class="project-link" href="#"><span class="caption">Rhodes House</span><span class="more">Find out more</span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('farmiloe.jpg') }}" alt="Farmiloe Building" loading="lazy">
      <h2 class="display">Skelly + Couch are united in curiosity</h2>
      <a class="project-link" href="#"><span class="caption">Farmiloe Building</span><span class="more">Find out more</span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('vitsoe.jpg') }}" alt="Vitsœ" loading="lazy">
      <h2 class="display">Skelly + Couch are applying collective intelligence</h2>
      <a class="project-link" href="#"><span class="caption">Vitsœ</span><span class="more">Find out more</span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('notting-hill.jpg') }}" alt="Notting Hill and Ealing School" loading="lazy">
      <h2 class="display">Skelly + Couch are collaborating with clarity</h2>
      <a class="project-link" href="#"><span class="caption">Notting Hill and Ealing School</span><span class="more">Find out more</span></a>
    </section>
    <section class="panel" data-theme="light">
      <img src="{{ $img('people.jpg') }}" alt="Skelly + Couch engineers reviewing a material board" loading="lazy">
      <h2 class="display">Skelly + Couch are a hotbed for growth</h2>
      <a class="project-link" href="#"><span class="caption">People</span><span class="more">Find out more</span></a>
    </section>
  </div>

  <!-- ===== Layout: latest_news (query: 3 latest posts) ===== -->
  <section class="news" id="news" data-theme="light">
    <div class="news-head">
      <h2>News</h2>
      <a class="more" href="#">Read more</a>
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
    </div>
    <div class="news-nav">
      <button data-dir="-1" aria-label="Previous">←</button>
      <button data-dir="1" aria-label="Next">→</button>
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
  <ul>
    <li><a href="#">Work</a></li>
    <li><a href="#">Practice</a></li>
    <li><a href="#">Services</a></li>
    <li><a href="#news">News</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <div class="muted">Design by TM</div>
  <div></div><div></div>
  <div class="bottom"><a href="#">Instagram</a><br><a href="#">Linkedin</a></div>
  <div class="bottom"><a href="#">Cookies &amp; Privacy Policy</a><br>©2026 Skelly + Couch</div>
</footer>

<div class="proto-badge">Prototype</div>

<script>
@verbatim
  // Vanilla JS for the prototype — each block maps 1:1 to a future Alpine component.
  const header = document.getElementById('siteHeader');
  const hero = document.getElementById('hero');

  // Hero: photo → building x-ray after a delay (Figma "Delay → Change to")
  setTimeout(() => hero.classList.add('is-xray'), 1800);

  // Menu toggle
  const menuBtn = document.getElementById('menuBtn');
  menuBtn.addEventListener('click', () => {
    const open = document.body.classList.toggle('nav-open');
    menuBtn.setAttribute('aria-expanded', open);
  });
  document.querySelectorAll('.nav-bar a').forEach(a => a.addEventListener('click', () => {
    document.body.classList.remove('nav-open'); menuBtn.setAttribute('aria-expanded', false);
  }));

  // Header: wordmark at top, S+C mark once scrolled; colour follows the section underneath
  const themed = [...document.querySelectorAll('[data-theme]')];
  function updateHeader(){
    header.classList.toggle('is-compact', scrollY > 40);
    const probe = 30;
    let theme = 'light';
    for (const el of themed){
      const r = el.getBoundingClientRect();
      if (r.top <= probe && r.bottom > probe) theme = el.dataset.theme;
    }
    if (theme === 'blue' && !hero.classList.contains('is-xray')) theme = 'light';
    header.classList.toggle('is-blue', theme === 'blue');
    header.style.color = theme === 'dark' ? 'var(--sc-black)' : '';
  }
  addEventListener('scroll', updateHeader, {passive:true});
  hero.addEventListener('transitionend', updateHeader);
  updateHeader();

  // News carousel arrows
  const track = document.getElementById('newsTrack');
  document.querySelectorAll('.news-nav button').forEach(b => b.addEventListener('click', () => {
    track.scrollBy({left: track.querySelector('.card').offsetWidth * +b.dataset.dir, behavior:'smooth'});
  }));
@endverbatim
</script>
</body>
</html>
