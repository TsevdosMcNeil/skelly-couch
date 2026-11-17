{{--
  Chapter — sticky bar (project name · chapter title · "+") over any blocks.
  The bar is sticky inside its own chapter, so the next chapter's bar pushes it off the top.
  The "+" opens a drop-down with the page's sections (App\section_nav).
--}}
<section {{ $attributes->class(['chapter', 'theme-'.$theme])->merge(['id' => $id]) }} data-surface="{{ $surface }}" aria-labelledby="{{ $id }}-title">
  <div class="chapter-bar" @unless ($block->preview) data-chapter-nav @endunless>
    <div class="chapter-bar__row lead">
      <p class="chapter-bar__project">{{ $project }}</p>
      <h2 class="chapter-bar__title" id="{{ $id }}-title">{{ $title }}</h2>
      <button class="chapter-bar__toggle" type="button" aria-expanded="false" aria-controls="{{ $id }}-nav">
        @include('partials.plus')
        <span class="sr-only">{{ __('Jump to a section', 'sage') }}</span>
      </button>
    </div>
    @unless ($block->preview)
      <nav class="chapter-bar__menu" id="{{ $id }}-nav" aria-label="{{ __('Project sections', 'sage') }}" inert>
        <ul class="hover-dim">
          @foreach ($nav as $item)
            <li><a href="#{{ $item['id'] }}" @if ($item['id'] === $id) aria-current="location" @endif>{{ $item['label'] }}</a></li>
          @endforeach
        </ul>
      </nav>
    @endunless
  </div>

  <div class="chapter__body flow">
    <InnerBlocks template="{{ $block->template }}" />
  </div>
</section>
