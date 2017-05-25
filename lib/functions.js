//
// Basic JS functions for NGCMS core
//

$(function() {

	$('.scrollTop').click(function(){
		return $('html, body').animate({ scrollTop: 0 }, 888);
	});

	// for bootstrap element
	$('input[type=checkbox]').each(function() {
		if ( $(this).prop('checked') == true ) {
			$(this).parent().addClass('active');
		} else {
			$(this).parent().removeClass('active');
		}
	});

	// Select/unselect all
	$('table .select-all').click(function() {
		$(this).parents('table').find('input:checkbox:not([disabled])').prop('checked', $(this).prop('checked'));
	});

	// Process spoilers
	$('.sp-head').click(function() {
		$(this).next('.sp-body').toggle('fast');
	});

});

/* cookie style core */
function setCookie(name, value, days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function deleteCookie(name) {
	setCookie(name,"",-1);
}

function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

// ngShowLoading
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

// ngHideLoading
function ngHideLoading() {
	$("#loading-layer").fadeOut('slow');
}

// confirmIt
function confirmIt(url, text){
	var agree = confirm(text);
	if (agree) document.location=url;
}

// insertext
function insertext(open, close, field) {
	msgfield = document.getElementById((field != '') ? field : 'content');

	// IE support
	if (document.selection && document.selection.createRange){
		msgfield.focus();
		sel = document.selection.createRange();
		sel.text = open + sel.text + close;
		msgfield.focus();
	}
	// Moz support
	else if (msgfield.selectionStart || msgfield.selectionStart == "0"){
		var startPos = msgfield.selectionStart;
		var endPos = msgfield.selectionEnd;

		msgfield.value = msgfield.value.substring(0, startPos) + open + msgfield.value.substring(startPos, endPos) + close + msgfield.value.substring(endPos, msgfield.value.length);
		msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
		msgfield.focus();
	}
	// Fallback support for other browsers
	else {
		msgfield.value += open + close;
		msgfield.focus();
	}
	$('html, body').animate({ scrollTop: $(msgfield).offset().top-200 }, 888);
	return;
}

// insertimage
function insertimage(open) {
	insertext(open, ' ');
}

/* Quote user */
var q_txt = '';

function copy_quote(q_name) {

	if (window.getSelection) {
		q_txt = window.getSelection();
	} else if (document.getSelection) {
		q_txt = document.getSelection();
	} else if (document.selection) {
		q_txt = document.selection.createRange().text;
	}

	if (q_txt == '') {
		q_txt = '[b]'+q_name+'[/b],';
	} else {
		q_txt = '[quote='+q_name+']'+q_txt+'[/quote]';
	}

}

function quote(q_name) {
	insertext(q_txt, '', 'content');
}

// emailCheck
function emailCheck(emailStr) {
	var emailPat = /^(.+)@(.+)$/,
		specialChars = "\\(\\)<>@,;:\\\\\\\"\\.\\[\\]",
		validChars = "\[^\\s" + specialChars + "\]",
		quotedUser = "(\"[^\"]*\")",
		ipDomainPat = /^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/,
		atom = validChars + '+',
		word = "(" + atom + "|" + quotedUser + ")",
		userPat = new RegExp("^" + word + "(\\." + word + ")*$"),
		domainPat = new RegExp("^" + atom + "(\\." + atom +")*$");

	var matchArray = emailStr.match(emailPat);
	if (matchArray == null) return false;

	var user = matchArray[1],
		domain = matchArray[2];
	if (user.match(userPat) == null) return false;

	var IPArray = domain.match(ipDomainPat);
	if (IPArray != null) {
		for (var i=1;i<=4;i++) {
			if (IPArray[i]>255) return false;
		}
		return true;
	}

	var domainArray = domain.match(domainPat);
	if (domainArray == null) return false;

	var atomPat = new RegExp(atom,"g"),
		domArr = domain.match(atomPat),
		len = domArr.length;
	if (domArr[domArr.length-1].length<2 || domArr[domArr.length-1].length>3) return false;

	if (len<2) return false;

	return true;

}

// formatSize
function formatSize($file_size){
	if ($file_size >= 1073741824) {
		$file_size = Math.round( $file_size / 1073741824 * 100 ) / 100 + " Gb";
	} else if ($file_size >= 1048576) {
		$file_size = Math.round( $file_size / 1048576 * 100 ) / 100 + " Mb";
	} else if ($file_size >= 1024) {
		$file_size = Math.round( $file_size / 1024 * 100 ) / 100 + " Kb";
	} else {
		$file_size = $file_size + " b";
	}
	return $file_size;
}

// calculateMaxLen
function calculateMaxLen(oId, tId, maxLen) {
	var delta = maxLen - oId.val().length;

	if (tId) {
		tId.html(delta);
		tId.css('color', ((delta > 0) ? 'black' : 'red'));
	}
}

// printElem
function printElem(data) {
	
	var printing_css='<style>* {color:#888;} input{display:none;} a {text-decoration:none;}</style>';
	var html_to_print=printing_css + data;
	var iframe=$('<iframe id="print_frame">');
	$('body').append(iframe);
	var doc = $('#print_frame')[0].contentDocument || $('#print_frame')[0].contentWindow.document;
	var win = $('#print_frame')[0].contentWindow || $('#print_frame')[0];
	doc.getElementsByTagName('body')[0].innerHTML=html_to_print;
	win.print();
	$('iframe').remove();

	return true;
}

/* Main function to show Modal Bootsrtap */
function showModal(textOrID, header, footer, size) {
	var withID = document.getElementById(textOrID);
	if (withID && !header && !footer) { // Show modal with ID
		$(withID).modal('show');
		return;
	}
	var modalContent = '';
	if (header) {
		if (textOrID) {
			modalContent = '<div class="modal-header">\
								<h5 class="modal-title">' + header + '</h5>\
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">\
									<span aria-hidden="true">&times;</span>\
								</button>\
							</div>';
		} else {
			modalContent = '<div class="modal-header">\
								<h5 class="modal-title">Info</h5>\
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">\
									<span aria-hidden="true">&times;</span>\
								</button>\
							</div>';
		}
	} else {
		modalContent = '<div class="modal-header">\
							<h5 class="modal-title">Error</h5>\
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">\
								<span aria-hidden="true">&times;</span>\
							</button>\
						</div>';
	}
	if (textOrID)
		modalContent += '<div class="modal-body">' + textOrID + '</div>';
	else
		modalContent += '<div class="modal-body">Unable to load content . . .</div>';
	
	if (footer) {
		modalContent += '<div class="modal-footer">' + footer + '</div>';
	} else {
		modalContent += '<div class="modal-footer">\
							<button type="button" class="btn btn-default btn-secondary" data-dismiss="modal">\
							Close\
							</button>\
						</div>';
	}
	if (size == 'modal-lg')
		$('#modal-dialog .modal-dialog').addClass('modal-lg');
	else
		$('#modal-dialog .modal-dialog').removeClass('modal-lg');

	$('#modal-dialog .modal-content').html(modalContent); // #modal-dialog isset in html document'е
	$('#modal-dialog').modal('show');

	return;
}


/* ************ */
/* DINAMIC TIME */
/* ************ */
UsAgentLang = (navigator.language || navigator.systemLanguage || navigator.userLanguage).substr(0, 2).toLowerCase();

Lang = {}
// Выбираем нужную локализацию.
switch (UsAgentLang) {
	case 'ru' :
		Lang.Now = 'только что';
		Lang.Ago = 'назад';
		Lang.After = 'через';
		Lang.NameMonths = ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августa', 'Сентября', 'Октября', 'Ноября', 'Декабря'];
		Lang.NameMonthsMin = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
		Lang.NameWeekdays = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
		Lang.NameWeekdaysMin = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
		Lang.DimensionTime = {
				'n' : ['месяцев', 'месяц', 'месяца', 'месяц'],
				'j' : ['дней', 'день', 'дня'],
				'G' : ['часов', 'час', 'часа'],
				'i' : ['минут', 'минуту', 'минуты'],
				's' : ['секунд', 'секунду', 'секунды']
		}
		break;
	default:
		Lang.Now = 'now';
		Lang.Ago = 'ago';
		Lang.After = 'after';
		Lang.NameMonths = ['January', 'February', 'Marth', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		Lang.NameMonthsMin = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		Lang.NameWeekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		Lang.NameWeekdaysMin = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		Lang.DimensionTime = {
				'n' : ['months', 'month', 'months'],
				'j' : ['days', 'day', 'days'],
				'G' : ['hours', 'h', 'hours'],
				'i' : ['minutes', 'minute', 'minutes'],
				's' : ['seconds', 'second', 'seconds']
		}
		break;
}

// Выводит элемент даты с нужной размерностью, и в нужном склонении
function NiceDate(chislo, type) {
	var n;
	// Узнаем нужное склонение для временной единицы
	if (chislo >= 5 && chislo <= 20)
		n = 0;
	else if (chislo == 1 || chislo % 10 == 1)
		n = 1;
	else if ((chislo <= 4 && chislo >= 1) || (chislo % 10 <= 4 && chislo % 10 >= 1))
		n = 2;
	else
		n = 0;
		

	return chislo + ' ' + Lang.DimensionTime[type][n];

}

// Выводит двузначное число с ведущим нулем
function ZeroPlus(x) {
	if (x < 10)
		x = '0' + x;
	return x;
}
// Переводит в 12 часовой формат
function ToAM(x) {
	if (x > 12) 
		x -= 12;
	return x;
}

// Аналог функции date() из PHP
function ParseDateFormat(format, Time) {
	var DateInFormat = '';
	if (format.length === 0)
		return;
	for (var i = 0; i < format.length; i++) {
		switch (format[i]) {
			// Часы
			// 12 часовой
			case 'g' : DateInFormat += ToAM(Time.getUTCHours()); break; // без ведущего нуля
			case 'h' : DateInFormat += ZeroPlus(ToAM(Time.getUTCHours())); break; // C ведущим нулем
			// 24 часовой
			case 'G' : DateInFormat += Time.getUTCHours(); break; // без ведущего нуля
			case 'H' : DateInFormat += ZeroPlus(Time.getUTCHours()); break; // с ведущим нулём
			// Годы
			case 'Y' : DateInFormat += Time.getUTCFullYear(); break; // Четыре цифры
			case 'y' : DateInFormat += String(Time.getUTCFullYear()).substr(2); break; // Две цифры
			// Месяцы
			case 'm' : DateInFormat += ZeroPlus(Time.getUTCMonth() + 1); break; //Порядковый номер месяца с ведущим нулём
			case 'n' : DateInFormat += Time.getUTCMonth() + 1; break; // Порядковый номер месяца без ведущего нуля
			case 'F' : DateInFormat += Lang.NameMonths[Time.getUTCMonth()]; break; // Полное наименование месяца
			case 'M' : DateInFormat += Lang.NameMonthsMin[Time.getUTCMonth()]; break; // Сокращенное наименование месяца
			// Дни
			case 'd' : DateInFormat += ZeroPlus(Time.getUTCDate()); break;// День месяца
			case 'j' : DateInFormat += Time.getUTCDate(); break; // День месяца без в.н.
			// Дни недели
			case 'N' : DateInFormat += Time.getUTCDay() + 1; break; // Порядковый номер дня недели
			case 'D' : DateInFormat += Lang.NameWeekdaysMin[Time.getUTCDay()]; break; // Текстовое, сокращенное, представление дня недели
			case 'L' : DateInFormat += Lang.NameWeekdays[Time.getUTCDay()]; break; // Полное наименование дня недели
			// Минуты
			case 'i' : DateInFormat += ZeroPlus(Time.getUTCMinutes()); break; // с ведущим нулём
			// Секунды
			case 's' : DateInFormat += ZeroPlus(Time.getUTCSeconds()); break; // с ведущим нулём
			
			default : DateInFormat += format[i]; break;
		}
	}
	
	return DateInFormat;
}

// Выводит относительное время. А так же если check = true то просто делает проверку, относительную ли дату выводить
function OffsetDate(Time, Now, check) {
	
	if (check) {
		if (((new Date(Now - Time)) < (new Date(1970, 1))) || Time > Now)
			return true;
		else
			return false;
	}

	if (Time > Now)
		var OffsetTime = new Date(Time - Now);
	else
		var OffsetTime = new Date(Now - Time);
	
	var s = OffsetTime.getUTCSeconds(), // Секунды
		 i = OffsetTime.getUTCMinutes(), // Минуты
		 G = OffsetTime.getUTCHours(), // Часы
		 j = OffsetTime.getUTCDate()-1, // Дни
		 n = OffsetTime.getUTCMonth(), // Месяц
		 output = '';
	
	// Если время пошло на месяцы то выводим только месяцы и дни(если не ноль)
	if (n) {
		output += NiceDate(n, 'n') + ' ';
		if (j) output += NiceDate(j, 'j') + ' ';
	// Если время пошло на дни то выводим только дни
	} else if (j) {
		output += NiceDate(j, 'j') + ' ';
	// Если время пошло на часы то выводим только часы и минуты(если не ноль)
	} else if (G) {
		output += NiceDate(G, 'G') + ' ';
	// Если время пошло на минуты то выводим только минуты и секунды(если не ноль)
	} else if (i) {
		output += NiceDate(i, 'i') + ' ';
	// Если времени прошло менее минуты то выводим секунды
	} else {
		output += Lang.Now;
		return output;
	}

	if (Time > Now)
		return Lang.After + '  ' + output;
	else
		return output + '  ' + Lang.Ago;

}

// Выводит дату в нужном формате
function FormatTime(el) {
	
	var format = el.data('type'),
		stime = Date.parse(el.attr('datetime')),
		Now = new Date(), // Объект текущей даты
		Time = new Date(stime), // Обьект указанного времени
		f = OffsetDate(Time, Now, true); // Проверка на тип выводимого времени(относительный или дата)
		
	// Выводим относительное время
	if (f)
		el.html(OffsetDate(Time, Now, false));
	else {
		// Здесь просто выводим в нужном формате...
		// Если эту дату(не относительную) мы уже обработали, то не трогаем её.
		if (!el.data('compiled')) {
			el.html(ParseDateFormat(format, Time));
			el.attr('data-compiled', 'true');
		}
	}
}

// Ищем даты на странице и изменяем их под клиента
function UpdateTime() {
	var BlockTime = $('time');
	$.each(BlockTime, function () {
		if ($(this).attr('data-type'))
			FormatTime($(this));
	});
}
// Первоначальная обработка времени.
$(document).ready(function(){UpdateTime();});
// Динамическое обновление дат.
setInterval(UpdateTime, 10000);
