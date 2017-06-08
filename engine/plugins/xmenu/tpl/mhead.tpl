
<script>
function xmenu_click(id) {
	var i;
	for (i=0; i<=9; i++) {
		document.getElementById('go_'+i).className = (i==id)?'active':'passive';
		document.getElementById('menu_'+i).style.display = (i==id)?'block':'none';
	}
}
$('nav-tabs a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
</script>
<tr><td width="100%">
<ul class="nav nav-tabs nav-justified">
<li id="go_0" class="active"><a href="#" onclick="xmenu_click(0);">Категории</a></li>
<li id="go_1"><a href="#" onclick="xmenu_click(1);">1</a></li>
<li id="go_2"><a href="#" onclick="xmenu_click(2);">2</a></li>
<li id="go_3"><a href="#" onclick="xmenu_click(3);">3</a></li>
<li id="go_4"><a href="#" onclick="xmenu_click(4);">4</a></li>
<li id="go_5"><a href="#" onclick="xmenu_click(5);">5</a></li>
<li id="go_6"><a href="#" onclick="xmenu_click(6);">6</a></li>
<li id="go_7"><a href="#" onclick="xmenu_click(7);">7</a></li>
<li id="go_8"><a href="#" onclick="xmenu_click(8);">8</a></li>
<li id="go_9"><a href="#" onclick="xmenu_click(9);">9</a></li>
</ul>
</td></tr>