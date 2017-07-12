var $ = jQuery.noConflict();
var attachAbsoluteRowID = 0;

$(function() {
    // Прокрутка вверх
    $(window).scroll(function () {
        ( $(this).scrollTop() == 0 ) ? $('.scrollTop').fadeOut() : $('.scrollTop').fadeIn();
    });

    // Боковое меню
    $('.sidebar-toggle, #sidenav-overlay').click(function () {
        $('.side-menu-container').toggleClass('slide-in');
        $('.side-body').toggleClass('body-slide-in');
        if($('.side-body').hasClass('body-slide-in'))
            $('#sidenav-overlay').fadeIn('slow');
        else
            $('#sidenav-overlay').fadeOut('slow');
    });

    /* admGroup hide/show */
    $('.adm-group-toggle').click(function() {
        if ($(this).hasClass('expanded')) {
            $(this).removeClass('expanded');
            $(this).parents().next('.adm-group-content').slideUp('slow');
        } else {
            $(this).addClass('expanded');
            $(this).parents().next('.adm-group-content').slideDown('slow');
        }
        return false;
    });

    $('code').click(function() {
        select(this);
    });

    /*****************************
     * ACTION
    ******************************/

    // Добавление элементов (пользователь, группы) в modal
    $(document).on('click', '.add_form', function(){
        $('#modal-dialog .modal-dialog').load($(this).attr('href') + ' #add_edit_form .modal-content');
        $('#modal-dialog').modal('show');
        return false;
    });
    // Редактирование элементов (пользователь, группы) в modal
    $(document).on('click', '.edit_form', function(){
        $('#modal-dialog .modal-dialog').load($(this).attr('href') + ' #add_edit_form .modal-content');
        $('#modal-dialog').modal('show');
        return false;
    });

    /*
     * Images
    */
    var $lightbox = $('#lightbox');

    $('.thumbnail').on('click', function(event) {
        var $img = $(this).find('img'),
            src = $img.attr('src'),
            alt = $img.attr('alt'),
            css = {
                'maxWidth': $(window).width() - 100,
                'maxHeight': $(window).height() - 100
            };

        $lightbox.find('img').attr('src', src);
        $lightbox.find('img').attr('alt', alt);
        $lightbox.find('img').css(css);

    });

    $lightbox.on('shown.bs.modal', function (event) {
        var $img = $lightbox.find('img');
        $lightbox.find('.modal-dialog').css({'width': $img.width() + 30});
    });

});

function select(elem) {
    var rng, sel;
    if (document.createRange) {
        rng = document.createRange();
        rng.selectNode(elem);
        sel = window.getSelection();
        var strSel = '' + sel;
        if (!strSel.length) {
            sel.removeAllRanges();
            sel.addRange(rng);
        }
    } else {
        var rng = document.body.createTextRange();
        rng.moveToElementText(elem);
        rng.select();
    }
}

/* **************** Insert image ****************** */

$(document).on('click', '.preview-img a', function(){
    $(this).html('<span class=text-success>&#10004;</span>');
});

function insert_image(text, area) {
    var form = document.forms['form'];
    try {
        var xarea = document.forms['DATA_tmp_storage'].area.value;
        if (xarea != '') area = xarea;
    } catch(err) {;}
        var control = document.getElementById(area);
    // IE
    if (document.selection && document.selection.createRange){
        sel = document.selection.createRange();
        sel.text = text = sel.text;
    } else
    // Mozilla
    if (control.selectionStart || control.selectionStart == "0"){
        var startPos = control.selectionStart;
        var endPos = control.selectionEnd;
        control.value = control.value.substring(0, startPos) + text + control.value.substring(startPos, control.value.length);
    } else {
        control.value += text;
    }
    //control.focus();
}

/* Получение списка изображений */
function getImageList(id, npp, page) {
    
    ngShowLoading();
    
    $.post('admin.php?mod=images&npp=' + npp + '&page=' + page, function (r) {
        var qw = $('.img-src a', r).attr('href');
        if (!qw) {
            showModal('Нет загруженных изображений!',
                        'Выберите изображение для вставки',
                        '<button type="button" class="btn btn-default pull-left" data-dismiss="modal">\
                            <i class="fa fa-times"></i>\
                        </button>\
                        <button type="button" class="btn btn-primary pull-left" onclick="getImageList(\'img_popup\', 8, 1);return false;">\
                            <i class="fa fa-refresh"></i>\
                        </button>\
                        <a href="#" class="btn btn-primary pull-left" onclick="$(\'#modal-dialog .modal-body\').load(\'admin.php?mod=images #upload-files\');return false;">\
                            <i class="fa fa-upload"></i>\
                        </a>');
            ngHideLoading();
            return false;
            
        } else {
            setTimeout(function () {
                var modalContent = '';
                var snum = '0';
                $(".img-src a", r).each(function () {
                    snum++;
                    var hrf = $(this).attr('href');
                    var tr = $(this).closest('tr');
                    var title = $('.img-title', tr).text();
                    var width = $('.img-width', tr).text();
                    var height = $('.img-height', tr).text();
                    var size = $('.img-size', tr).text();
                    if (size=='-') {
                        modalContent += '<div class="preview-img" title="' + title + '"><a href="' + hrf + '" target="_blank">' + title + '</a><div class="img-descr"><span>Wrong Image source!</span></div></div>';
                    } else {
                        modalContent += '<div class="col-md-3 text-center" ><div class="preview-img" style="background-image: url(\'' + hrf + '\');" title="' + title + '">\
                        <span class="img-descr"><span class="img-title">' + title + '</span></span>' +
                            $('.insert-file', tr).html().replace('insertimage','insert_image') + 
                            $('.insert-thumb', tr).html().replace('insertimage','insert_image') + 
                            $('.insert-preview', tr).html().replace('insertimage','insert_image') + 
                    '</div></div>';
                    }
                });
                
                $("#modal-dialog .modal-dialog").addClass('modal-lg');
                
                $("#modal-dialog").animate({ scrollTop: 0 }, 888);
                
                showModal('<div class="row">' + modalContent + '</div>',
                        'Выберите изображение для вставки',
                        '<button type="button" class="btn btn-default pull-left" data-dismiss="modal">\
                            <i class="fa fa-times"></i>\
                        </button>\
                        <a href="#" class="btn btn-primary pull-left" onclick="getImageList(\'img_popup\', 8, 1);return false;">\
                            <i class="fa fa-refresh"></i>\
                        </a>\
                        <a href="#" class="btn btn-primary pull-left" onclick="$(\'#modal-dialog .modal-body\').load(\'admin.php?mod=images #upload-files\');return false;">\
                            <i class="fa fa-upload"></i>\
                        </a>\
                        <button type="button" class="btn btn-success img-back">\
                            <i class="fa fa-backward"></i>\
                        </button>\
                        <button type="button" class="btn btn-success img-next">\
                            <i class="fa fa-forward"></i>\
                        </button>',
                        'modal-lg');
                
                if (page<2) {$('.img-back').attr('disabled','disabled');} else {var page_back = page - 1; $('.img-back').attr('onclick','ngShowLoading();getImageList(\'' + id + '\', ' + npp + ', ' + page_back + ');ngHideLoading(); return false;');}

                if (snum<npp || page==0) {$('.img-next').attr('disabled','disabled');} else {page++; $('.img-next').attr('onclick','ngShowLoading();getImageList(\'' + id + '\', ' + npp + ', ' + page + ');ngHideLoading(); return false;');}
                
            }, 101);
        }
        ngHideLoading();
    });
}

/*
Для input type="file"
HTML
<div class="btn btn-default btn-fileinput">
    <span><i class="fa fa-plus"></i> Add files ...</span>
    <input type="file" name="image" id="image-con" onchange="validateFile(this);">
</div>
*/
function checkImage(where, idnumber) {
    var preview = document.getElementById('preview' + idnumber);
    preview.innerHTML = '';
    [].forEach.call(where.files, function(file) {
        if (file.type.match(/image.*/)) {
            var reader = new FileReader();
            reader.onload = function(event) {
                var img = document.createElement('img');
                img.src = event.target.result;
                img.style.cssText = 'vertical-align: top; width: 88px;';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
}

function attachAddRow(id) {
    
    ++attachAbsoluteRowID;
    var tbl = document.getElementById(id);
    var lastRow = tbl.rows.length;
    var row = tbl.insertRow(lastRow - 1);
    
    // Add cells, Add file input
    if ( id == 'imageup2' || id == 'fileup2' ) {
        row.insertCell(0).innerHTML = '<input type="text" name="userurl[' + attachAbsoluteRowID + ']" class="form-control">'
    } else if ( id == 'imageup' || id == 'fileup' ) {
        row.insertCell(0).innerHTML = '<div class="btn btn-default btn-fileinput">\
                            <span><i class="fa fa-plus"></i> Add files ...</span>\
                            <input type="file" name="userfile[' + attachAbsoluteRowID + ']" onchange="validateFile(this, multiple);" multiple="multiple" / >\
                        </div>';
    } else if ( id == 'attachFilelist' ) {
        row.insertCell(0).innerHTML = '<div class="btn btn-default btn-fileinput">\
                                <span><i class="fa fa-plus"></i> Add files ...</span>\
                                <input type="file" name="userfile[]" onchange="validateFile(this, multiple);" multiple="multiple" / >\
                            </div>';
    } else if ( id == 'attachFilelist_edit' ) {
        var xCell = row.insertCell(0);
        xCell.setAttribute('colspan', '5');
        xCell.innerHTML = '<div class="btn btn-default btn-fileinput">\
                            <span><i class="fa fa-plus"></i> Add files ...</span>\
                            <input type="file" name="userfile[]" onchange="validateFile(this, multiple);" multiple="multiple" />\
                        </div>';
    } else {
        row.insertCell(0).innerHTML = '<div class="btn btn-default btn-fileinput">\
                            <span><i class="fa fa-plus"></i> Add files ...</span>\
                            <input type="file" name="userfile[' + attachAbsoluteRowID + ']" onchange="validateFile(this);">\
                        </div>';
    }
    
    var xCell = row.insertCell(1);
    xCell.setAttribute('class', 'text-center');
    
    el = document.createElement('button');
    el.setAttribute('type', 'button');
    el.setAttribute('onclick', '$(this).closest("tr").remove();');
    el.setAttribute('class', 'btn btn-danger');
    el.innerHTML = '<i class="fa fa-trash"></i>';
    xCell.appendChild(el);

}

$(document).on('submit', '#upload-files', function(e){
    
    e.preventDefault();
    $("#modal-dialog").animate({ scrollTop: 0 }, 888);
    var progressBar = $('#progressbar');
    
    var $formData = new FormData($(this)[0]);
    $formData.append('ngAuthCookie', '{authcookie}');
    $formData.append('category', document.getElementById('categorySelect').value);
    $formData.append('rand', document.getElementById('flagRand').checked?1:0);
    $formData.append('replace', document.getElementById('flagReplace').checked?1:0);
    
    if ( $("input[name='uploadType']").val() == 'image' ) {
        $formData.append('uploadType', 'image');
        $formData.append('thumb', document.getElementById('flagThumb').checked?1:0);
        $formData.append('stamp', document.getElementById('flagStamp').checked?1:0);
        $formData.append('shadow', document.getElementById('flagShadow').checked?1:0);
    } else {
        $formData.append('uploadType', 'file');
    }
    $formData.append('methodName', 'admin.files.upload');
    $formData.append('json', '1');
    
    $.each($(this).find("input[type='file']"), function(i, tag) {
        var input, filter, table, tr, td, i;
        table = $(this).parent().find('table');

        $.each($(tag)[0].files, function(i, file) {
            tr = table.find('tr');
            $formData.append('Filedata', file);
            $.ajax({
                url: 'rpc.php',
                data: $formData,
                processData: false,
                contentType: false,
                type: 'POST',
                //dataType: 'JSON',
                xhr: function(){
                    var xhr = $.ajaxSettings.xhr();
                    xhr.upload.addEventListener('progress', function(evt){
                     if(evt.lengthComputable) {
                        var percentComplete = Math.floor(evt.loaded / evt.total * 100);
                        progressBar.css('width', percentComplete + '%').text(percentComplete + '%');
                     }
                    }, false);
                    return xhr;
                },
                success: function (res) {
                    // Response should be in JSON format
                    var resData;
                    var resStatus = 0;
                    td = tr[i].getElementsByTagName('td')[0];
                    tr[i].style.background = 'white';
                    tr[i].style.color = 'black';
                    
                    try {
                        resData = res;
                        if (typeof(resData.status))
                            resStatus = 1;
                    } catch (err) {
                        alert('Error parsing JSON output. Result: '+res);
                    }
                    if (!resStatus) {
                        alert('Upload resp: '+res);
                        tr[i].style.color = 'red';
                        return false;
                    }
                    
                    flagRequireReload = 1;
                    
                    // If upload fails
                    if (resData.status < 1) {
                        el = document.createElement('div');
                        el.setAttribute('class', 'text-danger');
                        el.innerHTML = '('+resData.errorCode+') '+resData.errorText;
                        td.appendChild(el);
                        if (typeof(resData.errorDescription) !== 'undefined') {
                            el = document.createElement('div');
                            el.setAttribute('class', 'text-info');
                            el.innerHTML = resData.errorDescription;
                            td.appendChild(el);
                        }
                        tr[i].style.color = 'red';
                        return false;
                    } else {
                        el = document.createElement('div');
                        el.setAttribute('class', 'text-success');
                        el.innerHTML = resData.errorText;
                        td.appendChild(el);
                        //$(tr[i]).fadeOut(3000);
                    }
                    return true;
                },
                error : function(res) {
                    console.log(res);
                    $.notify({message:'Error parsing JSON output.'},{type:'danger'});
                    tr[i].style.color = 'red';
                    return false;
                }
                
            });
        });
        
    });
});
