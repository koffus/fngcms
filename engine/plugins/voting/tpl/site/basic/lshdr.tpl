<script type="text/javascript">
function make_voteL(mode, voteid) {
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

    if (choice < 0) {
        $.notify({message:'Сначала необходимо выбрать вариант!'},{type: 'info'});
        return false;
    }

    var params = {
        'mode': 'vote',
        'choice': choice,
        'list': 1,
        'style': 'ajax',
        'ajax': 1,
        'json': 1,
        };
    $.reqJSON('{admin_url}/rpc.php', 'plugin.voting.update', params, function(json) {
        $('#voting_'+voteid).html(json.content);
        $.notify({message:'Спасибо. Ваш голос принят!'},{type: 'success'});
    });
    
    return false;
}
</script>

<h2 class="section-title">Архив опросов сайта</h2>