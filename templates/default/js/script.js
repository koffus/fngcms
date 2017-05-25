$(function($) {

	/* scrollTop */
	$('body').append('<div class="scrollTop fa fa-angle-up"></div>');
	$(window).scroll(function () {
		if ($(this).scrollTop() > 56) {
			$('.scrollTop').show();
			$('body').addClass('scrollActive').css('padding-top', '56px');
			$('#mainNav').css('top', 0);
		} else {
			$('.scrollTop').hide();
			$('body').removeClass('scrollActive').css('padding-top', 0);
			$('#mainNav').css('top', '-56px');
		}
	});
	$('.scrollTop').on('click', function(){$('html, body').animate({scrollTop:0}, 888);});

	// Share news
	$('.share').click(function() {
		var nWin = window.open($(this).prop('href'), 'shareWindow', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');
		if (window.focus) nWin.focus();
		return false;
	});

});