<script type="text/javascript">
function make_vote(mode, voteid) {
    var form = document.getElementById('voteForm_'+voteid);
    var choice = -1;
    for (i=0;i<form.elements.length;i++) {
        var elem = form.elements[i];
        if (elem.type == 'radio') {
            if (elem.checked == true) {
                choice = elem.value;
            }
        }
    }

    var voteid = form.voteid.value;
    if (mode && (choice < 0)) {
        $.notify({message:'Сначала необходимо выбрать вариант!'},{type: 'info'});
        return false;
    }

    if (mode) { 
        mode = "vote";
    } else {
        mode = "show";
    }

    var params = {
        "mode": mode,
        "choice": choice,
        "voteid": voteid,
        "list": 0,
        "style": 'ajax',
        "ajax": "1",
        "json": "1",
        };
    $.reqJSON('{admin_url}/rpc.php', 'plugin.voting.update', params, function(json) {
        $('#voting_'+{voteid}).html(json.content);
        $.notify({message:'Спасибо. Ваш голос принят!'},{type: 'success'});
    });
}
</script>

<div id="voting_{voteid}" class="widget widget-voting">
    <h3 class="widget-title">{l_voting:voting}</h3>
    <h5>{votename}</h5>
    [votedescr]<small>Описание: {votedescr}</small><br/>[/votedescr]
    <form action="{post_url}" method="post" id="voteForm_{voteid}">
        <input type="hidden" name="mode" value="vote" />
        <input type="hidden" name="voteid" value="{voteid}" />
        <input type="hidden" name="referer" value="{referer}" />
        {votelines}
        <a href="#" onclick="make_vote(1, {voteid}); return false;" class="btn btn-outline-primary btn-sm mt-3">Голосовать</a>
        <!--a href="{home}/plugin/voting/" class="pull-right mt-3 ml-3"><small>Архив</small></a>
        <a href="#" onclick="make_vote(0, {voteid}); return false;" class="pull-right mt-3"><small>Результаты</small></a-->
    </form>
</div>