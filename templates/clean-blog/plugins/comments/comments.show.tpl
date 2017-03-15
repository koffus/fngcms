<li id="comment_{id}" class="comment" itemscope="" itemtype="http://schema.org/Comment">
	<div class="comment-content post-content" itemprop="text">
		<figure class="gravatar">
			{avatar}
		</figure>
		<div class="comment-meta post-meta" role="complementary">
			<div class="comment-author h5 text-bold">
				<a class="comment-author-link" href="" itemprop="author">[profile]<a href="{profile_link}" target="_blank" title="{l_profile}">[/profile]{author}[profile]</a>[/profile]</a>
			</div>
			<time class="comment-meta-item" datetime="{date}" itemprop="datePublished"><span>{date}</span></time>
			<p>{comment-short}[comment_full]<span id="comment_full{comnum}" style="display: none;">{comment-full}</span><p style="text-align: right;"><a href="javascript:ShowOrHide('comment_full{comnum}');">{l_showhide}</a></p>[/comment_full]</p>
			<p>[answer]<br clear="all" />--------------------<br /><i>{l_answer}</i> <b>{name}</b><br />{answer}[/answer]</p>
			[quote]<a href="#" rel="nofollow" onmouseover="copy_quote('{author}');" onclick="quote();return false;" class="comment-reply-link"><span>Цитировать</span></a>[/quote]
			[if-have-perm][edit-com]Редактировать[/edit-com] | [del-com]Удалить[/del-com][/if-have-perm]
		</div>
	</div>
</li>