{{--
  Project page: everything is blocks (Project Hero, Section, Chapter, Images…), in the post content.
--}}
@extends('layouts.app')

@section('content')
  @while (have_posts())
    @php(the_post())
    <article @php(post_class('project'))>
      @php(the_content())
    </article>
  @endwhile
@endsection
