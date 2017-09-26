
<div id="voting_{voteid}" class="card mb-3">
  <div class="card-body">
    <h4 class="card-title">{votename}</h4>
    [votedescr]<p>Описание: {votedescr}</p>[/votedescr]
    <form action="{post_url}" method="get" id="voteForm_{voteid}">
    <input type=hidden name=action value=vote />
    <input type=hidden name=voteid value="{voteid}" />
    <input type=hidden name=referer value="{REFERER}" />
    {votelines}
    
            <a href="#" onclick="make_voteL(1, {voteid}); return false;" class="btn btn-outline-primary btn-sm mt-3">Голосовать</a>
            <a href="{post_url}?mode=show&voteid={voteid}" onclick="make_voteL(0, {voteid}); return false;" class="pull-right mt-3"><small>Результаты</small></a>

    </form>
  </div>
</div>