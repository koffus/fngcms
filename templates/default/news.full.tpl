[TWIG]
<article class="full-post">
	<div class="btn-group pull-right">
		<button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></button>
		<div class="dropdown-menu dropdown-menu-right">
			<a href="{{ news.url.print }}" class="dropdown-item"><i class="fa fa-print"></i> {{ lang.print }}</a>
			{% if (news.flags.canEdit) %}<a href="{{ news.url.edit }}" class="dropdown-item"><i class="fa fa-pencil"></i> {{ lang.editnews }}</a>{% endif %}
			{% if pluginIsActive('bookmarks') %}{{ plugin_bookmarks_news }}{% endif %}
			{% if (news.flags.canDelete) %}
				<div class="dropdown-divider"></div>
				<a href="#" onclick="confirmIt('{{ news.url.delete }}', '{{ lang['sure_del'] }}'); return false;" target="_blank" class="dropdown-item"><i class="fa fa-trash"></i> {{ lang.delnews }}</a>
			{% endif %}
		</div>
	</div>
	<h1 class="section-title">{{ news.title }}</h1>
	<p>{{ news.short }}</p>
	{% for file in _files %}
		<p class="text-center "><a href="{{ file.url }}" title="{{ file.origName }}" class="btn btn-success"><i class="fa fa-download"></i> Скачать ({{ file.size }})</a></p>
	{% endfor %}
	<p class="post-meta clearfix">
		<span class="pull-left">
			{% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %}&nbsp;&nbsp;•&nbsp;&nbsp;{{ news.categories.text }}
		</span>
		<span class="pull-right">
			<i class="fa fa-calendar"></i> {{ news.dateStamp | cdate  }}&nbsp;&nbsp;•&nbsp;&nbsp;
			{% if (news.flags.isUpdated) %}<i class="fa fa-refresh"></i> {{ news.updateStamp | cdate }}&nbsp;&nbsp;•&nbsp;&nbsp;{% endif %}
			<i class="fa fa-eye"></i> {{ news.views }}
			{% if pluginIsActive('comments') %}&nbsp;&nbsp;•&nbsp;&nbsp;<i class="fa fa-comments"></i> {comments-num}{% endif %}
		</span>
	</p>
	<hr class="alert-info">
	<p>{{ news.full }}</p>
	{% if (news.flags.hasPagination) %}
	<!-- Pager -->
	<nav><ul class="pagination justify-content-center">{{ news.pagination }}</ul></nav>
	{% endif %}
	
	<div class="post-full-footer">
		{% if pluginIsActive('tags') %}{% if (p.tags.flags.haveTags) %}<div class="post-full-tags">{{ lang.tags }}: {{ tags }}</div>{% endif %}{% endif %}
		{% if pluginIsActive('rating') %}<div class="post-rating">{{ lang.rating }}: <span class="post-rating-inner">{{ rating }}</span></div>{% endif %}
	</div>
</article>

<!-- Aditional Content -->
<div class="section">
	<div class="post-share">
		<hr class="alert-info">
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
	{% if pluginIsActive('similar') %}<hr class="alert-info">{{ plugin_similar_tags }}{% endif %}
	{% if pluginIsActive('comments') %}<hr class="alert-info">{{ plugin_comments }}{% endif %}
</div>
[/TWIG]