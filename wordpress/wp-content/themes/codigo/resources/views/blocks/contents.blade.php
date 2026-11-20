{{--
  Contents — the page's chapters (left) next to any blocks, normally an Image (right).
  Hover / focus / tap on a chapter: its highlight image fades in over the Image (resources/js/components/contents.js).
  Click: scrolls to the chapter. The highlights wait in a <template> until JS places them over the image.
--}}
<section {{ $attributes->class(['contents', 'theme-'.$theme])->merge(array_filter(['style' => $colorVars])) }} data-surface="{{ $surface }}" @unless ($block->preview) data-contents @endunless>
  <div class="contents__panel" data-surface="{{ $panelSurface }}">
    <h2 class="contents__heading lead">{{ $heading }}</h2>

    @if ($items)
      <nav class="contents__nav" aria-label="{{ $heading }}">
        <ol class="contents__list">
          @foreach ($items as $i => $item)
            <li @class(['contents__item', 'is-active' => $i === 0])>
              <a href="#{{ $item['id'] }}" data-chapter="{{ $item['id'] }}">
                <svg class="contents__arrow" viewBox="0 0 14 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 0v14M1 8l6 6 6-6"/></svg>
                @if ($block->preview && $item['highlight'])
                  {!! wp_get_attachment_image($item['highlight'], 'thumbnail', false, ['class' => 'contents__thumb', 'alt' => '']) !!}
                @endif
                {{ $item['label'] }}
              </a>
            </li>
          @endforeach
        </ol>
      </nav>
    @elseif ($block->preview)
      <p class="contents__empty">{{ __('Add Chapter blocks to the page and save it: they’ll be listed here.', 'sage') }}</p>
    @endif
  </div>

  <div class="contents__media">
    <InnerBlocks template="{{ $block->template }}" />
  </div>

  @unless ($block->preview)
    <template data-contents-highlights>
      @foreach ($items as $item)
        @if ($item['highlight'])
          {!! wp_get_attachment_image($item['highlight'], 'full', false, [
            'class' => 'contents__highlight',
            'data-chapter' => $item['id'],
            'alt' => '',
            'aria-hidden' => 'true',
            'loading' => 'lazy',
            'decoding' => 'async',
          ]) !!}
        @endif
      @endforeach
    </template>
  @endunless
</section>
