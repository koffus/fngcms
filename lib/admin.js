//
// JS Functions used for admin panel
//

function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return setStr;
}

function setCookie (name, value, expires, path, domain, secure) {
 document.cookie = name + "=" + escape(value) +
 ((expires) ? "; expires=" + expires : "") +
 ((path) ? "; path=" + path : "") +
 ((domain) ? "; domain=" + domain : "") +
 ((secure) ? "; secure" : "");
 return true;
}

function ngShowLoading() {

	var setX = ( $(window).width() - $("#loading-layer").width() ) / 2;
	var setY = ( $(window).height() - $("#loading-layer").height() ) / 2;

	$("#loading-layer").css( {
		left : setX + "px",
		top : setY + "px",
		position : 'fixed',
		zIndex : '99'
	});

	$("#loading-layer").fadeIn(0);
}

function ngHideLoading() {
	$("#loading-layer").fadeOut('slow');
}
