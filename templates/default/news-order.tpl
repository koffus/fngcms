<style>
.news-order-block {
	padding: .75rem 1rem;
	margin-bottom: 1rem;
	background-color: #eceeef;
	border-radius: .25rem;
	font-size: 0.8rem;
}
.news-order-block::after {
	display: block;
	content: "";
	clear: both;
}
.news-order-title,
.news-order-list,
.news-order-link {
	float: left;
	padding: 0;
	margin: 0;
}
.news-order-list {
	list-style: none;
	padding-left: 1rem;
}
.news-order-link {
	cursor: pointer;
}
.news-order-link:focus,
.news-order-link:hover {
	color: #0275d8;
}
.news-order-link+.news-order-link::before {
	display: inline-block;
	padding-right: .5rem;
	padding-left: .5rem;
	color: #636c72;
	content: "·";
}
.news-order-link.active {
	font-weight: bold;
	color: #0275d8;
}
.news-order-link.active.asc:after,
.news-order-link.active.desc:after {
    padding-left: 4px;
    color: #444;
}
.news-order-link.active.asc:after {
    content: '\2193';
}
.news-order-link.active.desc:after {
    content: '\2191';
}
</style>
<div class="news-order-block">
	<p class="news-order-title">{{ lang['news.order'] }}</p>
	<ul class="news-order-list">
		{{ newsOrder }}
	</ul>
</div>

<script>
	function newsorder(a){setCookie('newsOrder', a); document.location.href=''; return false;}
</script>
