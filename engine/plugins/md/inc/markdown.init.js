$(function() {
    $("[id~='ng_news_content']").after(function() {
        $("#flag_HTML").prop('checked', 'checked');
        $("#flag_RAW").prop('checked', 'checked');
        var converter = new showdown.Converter();
        return $('<a href="#" id="md_href" title="Парсить текст Markdown" style="float:right;"><b>MD</b></a>').click(
            function() {
                ngShowLoading();
                $("[id~='ng_news_content']").val((converter.makeHtml($("[id~='ng_news_content']").val())));
                ngHideLoading();
            return false;
        });
    });
});
