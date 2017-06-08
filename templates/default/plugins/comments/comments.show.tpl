<li class="comment clearfix" itemscope="" itemtype="http://schema.org/Comment">
<a id="comment_{id}"></a>
	<div class="comment-content post-content" itemprop="text">
		<figure class="gravatar">
			{avatar}
		</figure>
		<div class="comment-meta" role="complementary">
			[quote]
				<a href="#" rel="nofollow" onmouseover="copy_quote('{author}');" onclick="quote();return false;" class="comment-reply-link"><span>Цитировать</span></a>
			[/quote]
			[if-have-perm]
				[edit-com]<a href="{edit_link}" target="_blank" title="{l_addanswer}" class="comment-reply-link"><span>{l_addanswer}</span></a>[/edit-com]
				[del-com]<a href="{delete_link}" title="{l_comdelete}" class="comment-reply-link"><span>{l_comdelete}</span></a>[/del-com]
			[/if-have-perm]
			
			<div class="comment-author">
				[profile]<a class="comment-author-link" href="{profile_link}" target="_blank" title="{l_profile}">[/profile]<span itemprop="author">{author}</span>[profile]</a>[/profile]
			</div>
			<time class="comment-meta-item" datetime="{date}" itemprop="datePublished"><span>{date}</span></time>
			<p>{comment-short}[comment_full]<span id="comment_full{comnum}" style="display: none;">{comment-full}</span><p style="text-align: right;"><a href="javascript:ShowOrHide('comment_full{comnum}');">{l_showhide}</a></p>[/comment_full]</p>
			[answer]<p class="well well-sm text-muted">{l_answer} <b>{name}</b>:<br />{answer}</p>[/answer]
			
		</div>
	</div>
</li>