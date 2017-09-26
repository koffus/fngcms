<div class="post-share">
    Поделиться ссылкой
    <a class="share-btn share facebook" title="Facebook" href="http://www.facebook.com/sharer/sharer.php?u={{ news.url }}" rel="nofollow"><i class="fa fa-facebook"></i></a>
    <a class="share-btn share twitter" title="Twitter" href="https://twitter.com/intent/tweet?text={{ news.title }}&url={{ news.url }}" rel="nofollow"><i class="fa fa-twitter"></i></a>
    <a class="share-btn share gplus" title="Google+" href="https://plus.google.com/share?url={{ news.url }}" rel="nofollow"><i class="fa fa-google-plus"></i></a>
    <a class="share-btn share vk" title="ВКонтакте" href="http://vkontakte.ru/share.php?url={{ news.url }}" rel="nofollow"><i class="fa fa-vk"></i></a>
    <a class="share-btn share ok" title="Одноклассники" href="http://ok.ru/dk?st.cmd=addShare&st._surl={{ news.url }}" rel="nofollow"><i class="fa fa-odnoklassniki"></i></a>
    <a class="share-btn share mm" title="Мой мир" href="http://connect.mail.ru/share?url={{ news.url }}&title={{ news.title }}&description={{ news.short|striptags }}&imageurl=" rel="nofollow"><i class="fa fa-at"></i></a>
    <a class="share-btn share whatsapp" title="Whatsapp" href="whatsapp://send?text={{ news.title }}%20{{ news.url }}" rel="nofollow"><i class="fa fa-whatsapp"></i></a>
    <a class="share-btn print" title="Версия для печати" href="javascript:window.print();" rel="nofollow"><i class="fa fa-print"></i></a>
</div>

<script>
// Share news
$('.share').on('click', function() {
    var nWin = window.open($(this).prop('href'), 'shareWindow', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');
    if (window.focus)
        nWin.focus();

    return false;
});
</script>