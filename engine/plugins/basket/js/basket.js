function rpcBasketRequest(url, method, params) {
    $.reqJSON(url, method, params, function(json) {
        $.notify({message: json.data},{type: 'info'});
        $('#basketTotalDisplay').html(json.update);
        $('#basket_'+params['id']).val('1');
    });
}
/*
$(function() {
    $('[data-basket]').on('click', function(){
        $.notify({message: json.data},{type: 'info'});
    });
    //onclick="rpcBasketRequest('{{ admin_url }}/rpc.php', 'plugin.basket.manage', {'action': 'add', 'ds':1,'id':{news-id},'count':1}); $(this).removeAttr('onclick'); $(this).attr('href','{{ home }}/plugin/basket/'); $(this).html('<i class=\'fa fa-check\'></i> В корзине'); return false;" 
});*/