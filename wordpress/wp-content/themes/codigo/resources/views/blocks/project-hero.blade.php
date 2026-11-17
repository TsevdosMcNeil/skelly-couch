{{--
  Project Hero — blue band (S+C mark, project name, subtitle) + full-width hero image.
  The S+C mark scrolls away with the page; the menu icon stays fixed (sections/header).
--}}
<header {{ $attributes->class(['project-hero', 'theme-blue']) }} data-surface="dark">
  <div class="project-hero__band">
    <a class="logo project-hero__mark" href="{{ home_url('/') }}" aria-label="{{ get_bloginfo('name', 'display') }}, home">
      @include('partials.logo-mark')
    </a>
    <div class="project-hero__titles lead">
      <h1 class="project-hero__title">{{ $title ?: __('Project name', 'sage') }}</h1>
      @if ($subtitle)
        <p class="project-hero__subtitle">{!! wp_kses($subtitle, ['br' => []]) !!}</p>
      @endif
    </div>
  </div>

  @if ($image)
    {!! wp_get_attachment_image($image, 'full', false, [
      'class' => 'project-hero__image',
      'sizes' => '100vw',
      'loading' => 'eager',
      'fetchpriority' => 'high',
    ]) !!}
  @elseif ($block->preview)
    <div class="block-placeholder">{{ __('Choose the hero image in the block settings', 'sage') }}</div>
  @endif
</header>
