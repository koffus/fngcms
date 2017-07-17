// Share news
$('.share').on('click', function() {
    var nWin = window.open($(this).prop('href'), 'shareWindow', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');
    if (window.focus)
        nWin.focus();

    return false;
});