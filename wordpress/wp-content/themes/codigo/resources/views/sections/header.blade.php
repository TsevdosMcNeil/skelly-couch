{{--
  Fixed header: S+C mark + menu icon, and the menu bar.
  $mark = false on pages that print the mark in their own hero (it scrolls away with it), e.g. projects.
--}}
@php($mark = $mark ?? true)

<header class="site-header" data-site-header>
  @if ($mark)
    <a class="logo" href="{{ home_url('/') }}" aria-label="{{ $siteName }}, home">
      @include('partials.logo-mark')
    </a>
  @endif
  <button class="menu-btn" data-menu-toggle aria-expanded="false" aria-controls="navBar">
    <span class="burger" aria-hidden="true"><i></i><i></i><i></i></span>
    <span class="sr-only">{{ __('Open menu', 'sage') }}</span>
  </button>
</header>

<nav class="nav-bar" id="navBar" aria-label="{{ __('Primary', 'sage') }}">
  <a class="logo" href="{{ home_url('/') }}" aria-label="{{ $siteName }}, home">
    <span class="logo-word"><span>Skelly</span><span>+Couch</span></span>
  </a>
  <ul class="hover-dim">
    @foreach ($navigation as $item)
      <li><a href="{{ $item['url'] }}" @if ($item['current']) aria-current="page" @endif>{{ $item['title'] }}</a></li>
    @endforeach
  </ul>
  <button class="menu-btn" data-menu-close>
    <span class="burger" aria-hidden="true"><i></i><i></i><i></i></span>
    <span class="sr-only">{{ __('Close menu', 'sage') }}</span>
  </button>
  <a class="nav-search" href="{{ home_url('/?s=') }}" aria-label="{{ __('Search', 'sage') }}">
    <svg viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="4" aria-hidden="true"><circle cx="18" cy="12" r="9"/><path d="M11.5 18.5 3.5 26.5"/></svg>
  </a>
</nav>
