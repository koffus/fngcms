<script>
function rating(rating, post_id) {
    var params = {'rating': rating, 'post_id': post_id};
    $.reqJSON('{{ admin_url }}/rpc.php', 'plugin.rating.update', params, function(json) {
        $('#ratingdiv_'+post_id).html(json.content);
        $.notify({message: json.msg},{type: 'success'});
    });
}
$(document).on('click', '.post-rating li', function() {
    rating($(this).index(), '{{ post_id }}');
    return false;
});
</script>

<div id="ratingdiv_{{ post_id }}" class="post-rating">
    <ul class="uRating">
        <li class="r{{ rating }}">{{ rating }}</li>
        <li><a href="#" title="{{ lang['rating_1'] }}" class="r1u"></a></li>
        <li><a href="#" title="{{ lang['rating_2'] }}" class="r2u"></a></li>
        <li><a href="#" title="{{ lang['rating_3'] }}" class="r3u"></a></li>
        <li><a href="#" title="{{ lang['rating_4'] }}" class="r4u"></a></li>
        <li><a href="#" title="{{ lang['rating_5'] }}" class="r5u"></a></li>
    </ul>
    {{ lang['rating_votes'] }} {{ votes }}
</div>