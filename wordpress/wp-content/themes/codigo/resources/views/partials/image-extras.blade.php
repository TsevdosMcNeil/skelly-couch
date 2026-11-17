{{--
  Extra layers inside an Image block's .figure__media (see app/blocks.php, render_block_core/image):
  the aperture (second image, "ghost" copy of the picture, "+" button) and the comment "+" markers.
--}}
@if ($aperture)
  <div class="aperture__layer" aria-hidden="true">
    {!! wp_get_attachment_image((int) $aperture['id'], $size, false, ['class' => 'aperture__img', 'sizes' => $sizes, 'loading' => 'lazy', 'alt' => '']) !!}
  </div>
  @if ($image)
    <div class="aperture__ghost" aria-hidden="true">
      {!! wp_get_attachment_image($image, $size, false, ['class' => 'aperture__img', 'sizes' => $sizes, 'loading' => 'lazy', 'alt' => '']) !!}
    </div>
  @endif
  <button class="aperture__plus aperture__plus--{{ ($aperture['plus'] ?? 'white') === 'blue' ? 'blue' : 'white' }}" type="button" aria-expanded="false">
    @include('partials.plus')
    <span class="sr-only">{{ get_post_meta((int) $aperture['id'], '_wp_attachment_image_alt', true) ?: __('Show the other view', 'sage') }}</span>
  </button>
@endif

@foreach ($comments as $n => $comment)
  @php
    $style = ($comment['style'] ?? 'magnify') === 'grow' ? 'grow' : 'magnify';
    $anchor = in_array($comment['anchor'] ?? '', ['top-left', 'top-right', 'bottom-left', 'bottom-right'], true) ? $comment['anchor'] : 'top-right';
    $noteId = "{$uid}-note-{$n}";
  @endphp
  <span class="comment comment--{{ $style }} comment--{{ $anchor }}" style="{{ \App\comment_style($comment) }}">
    <span class="comment__box">
      @if ($style === 'magnify')
        <span class="comment__zoom" aria-hidden="true"></span>
      @endif
      <span class="comment__text" id="{{ $noteId }}" role="tooltip">{{ $comment['text'] }}</span>
    </span>
    <button class="comment__plus" type="button" aria-describedby="{{ $noteId }}">
      @include('partials.plus')
      <span class="sr-only">{{ __('Note', 'sage') }}</span>
    </button>
  </span>
@endforeach
