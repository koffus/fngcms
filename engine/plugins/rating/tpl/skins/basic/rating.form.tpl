<script type="text/javascript">
function rating(rating, post_id) {
    var params = {'rating': rating, 'post_id': post_id};
    $.reqJSON('{{ admin_url }}/rpc.php', 'plugin.rating.update', params, function(json) {
        $('#ratingdiv_'+post_id).html(json.content);
        $.notify({message: json.msg},{type: 'success'});
    });
}
</script>

<div id="ratingdiv_{{ post_id }}">
	<div class="rating" style="float:left;">
		<ul class="uRating">
		<li class="r{{ rating }}">{{ rating }}</li>
			<li><a href="#" title="{{ lang['rating_1'] }}" class="r1u" onclick="rating('1', '{{ post_id }}'); return false;"></a></li>
			<li><a href="#" title="{{ lang['rating_2'] }}" class="r2u" onclick="rating('2', '{{ post_id }}'); return false;"></a></li>
			<li><a href="#" title="{{ lang['rating_3'] }}" class="r3u" onclick="rating('3', '{{ post_id }}'); return false;"></a></li>
			<li><a href="#" title="{{ lang['rating_4'] }}" class="r4u" onclick="rating('4', '{{ post_id }}'); return false;"></a></li>
			<li><a href="#" title="{{ lang['rating_5'] }}" class="r5u" onclick="rating('5', '{{ post_id }}'); return false;"></a></li>
		</ul>
	</div>
	<div class="rating" style="float:left; padding-top:2px;">&nbsp;({{ lang['rating_votes'] }} {{ votes }})</div>
</div>