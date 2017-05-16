[TWIG]
<!-- Page Header -->
<header class="intro-header" style="background-image: url('{% if (news.embed.imgCount > 0) %}{{ news.embed.images[0] }}{% else %}{{ tpl_url }}/img/home-bg.jpg{% endif %}')">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<div class="post-heading">
					<h1>{{ news.title }}</h1>
					<hr class="small">
					<div class="subheading">{{ category }}</div>
				</div>
			</div>
		</div>
	</div>
</header>

<!-- Post Content -->
<article class="post-full">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<p>{{ news.short|striptags }}</p>
				<p class="post-meta clearfix">
					<span class="pull-left">
						{% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %}&nbsp;&nbsp;•&nbsp;&nbsp;{{ news.date }}
					</span>
					<span class="pull-right">
						<i class="fa fa-eye" title="{{ lang.views }}"></i> {{ news.views }} &nbsp;&nbsp;•&nbsp;&nbsp; {% if pluginIsActive('comments') %} <i class="fa fa-comments" title="{{ lang.com }}"></i> {comments-num}{% endif %}
					</span>
				</p>
				<hr class="alert-info">
				<p>{{ news.full }}</p>
				{% if (news.flags.hasPagination) %}
				<!-- Pager -->
				<nav><ul class="pagination justify-content-center">{{ news.pagination }}</ul></nav>
				{% endif %}
			</div>
		</div>
	</div>
</article>

<!-- Aditional Content -->
<div class="container">
	<div class="row">
		<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
			
			<div class="post-info post-share">
				Поделиться ссылкой
				<a class="share-btn share facebook" title="Facebook" href="http://www.facebook.com/sharer/sharer.php?u={{ home }}{{ news.url.full }}" rel="nofollow"><i class="fa fa-facebook"></i></a>
				<a class="share-btn share twitter" title="Twitter" href="https://twitter.com/intent/tweet?text={{ news.title }}&url={{ home }}{{ news.url.full }}" rel="nofollow"><i class="fa fa-twitter"></i></a>
				<a class="share-btn share gplus" title="Google+" href="https://plus.google.com/share?url={{ home }}{{ news.url.full }}" rel="nofollow"><i class="fa fa-google-plus"></i></a>
				<a class="share-btn share vk" title="ВКонтакте" href="http://vkontakte.ru/share.php?url={{ home }}{{ news.url.full }}" rel="nofollow"><i class="fa fa-vk"></i></a>
				<a class="share-btn share ok" title="Одноклассники" href="http://ok.ru/dk?st.cmd=addShare&st._surl={{ home }}{{ news.url.full }}" rel="nofollow"><i class="fa fa-odnoklassniki"></i></a>
				<a class="share-btn share mm" title="Мой мир" href="http://connect.mail.ru/share?url={{ home }}{{ news.url.full }}&title={{ news.title }}&description={{ news.short|striptags }}&imageurl=" rel="nofollow"><i class="fa fa-at"></i></a>
				<a class="share-btn share whatsapp" title="Whatsapp" href="whatsapp://send?text={{ news.title }}%20{{ home }}{{ news.url.full }}" rel="nofollow"><i class="fa fa-whatsapp"></i></a>
				<a class="share-btn print" title="Версия для печати" href="javascript:window.print();" rel="nofollow"><i class="fa fa-print"></i></a>
			</div>
			<hr class="alert-info">
			{% if pluginIsActive('comments') %}{{ plugin_comments }}{% endif %}
		</div>
	</div>
</div>
[/TWIG]