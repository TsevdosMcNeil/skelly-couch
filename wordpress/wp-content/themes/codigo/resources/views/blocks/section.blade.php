{{--
  Section — a coloured band holding any blocks. Text blocks sit in the right half (see pcss/blocks/flow.pcss).
--}}
<section {{ $attributes->class(['section', 'flow', 'theme-'.$theme])->merge(['id' => $id]) }} data-surface="{{ $surface }}">
  <InnerBlocks template="{{ $block->template }}" />
</section>
