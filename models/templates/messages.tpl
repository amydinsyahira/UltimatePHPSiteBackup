<span class="msg">
{section name=good loop=$good}
  <div class="solid-ok">{$good[good]}</div>
{/section}
{section name=msgs loop=$msgs}
  <div class="solid-yellow">{$msgs[msgs]}</div>
{/section}
{section name=errors loop=$errors}
  <div class="solid-error">{$errors[errors]}</div>
{/section}
</span>