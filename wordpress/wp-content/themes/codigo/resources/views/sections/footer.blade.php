<footer class="site-footer theme-blue" data-surface="dark">
  <div>
    <a class="logo" href="{{ home_url('/') }}" aria-label="{{ $siteName }}, home">
      <span class="logo-word"><span>Skelly</span><span>+Couch</span></span>
    </a>
  </div>
  <div>Bringing life to buildings,<br>and buildings to life.</div>
  <ul class="hover-dim">
    @foreach ($navigation as $item)
      <li><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></li>
    @endforeach
  </ul>
  <div class="muted">Design by TM</div>
  <div></div><div></div>
  <div class="bottom hover-dim"><a href="https://www.instagram.com/">Instagram</a><br><a href="https://www.linkedin.com/">Linkedin</a></div>
  <div class="bottom hover-dim">
    <a href="{{ get_privacy_policy_url() ?: '#' }}">Cookies &amp; Privacy Policy</a><br>&copy;{{ wp_date('Y') }} Skelly + Couch
  </div>
</footer>
