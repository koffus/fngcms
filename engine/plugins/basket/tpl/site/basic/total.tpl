<div id="basketTotalDisplay">
    {% if (count > 0) %}
    <a href="{{ home }}/plugin/basket/" class="btn btn-outline-primary">
        <i class="fa fa-shopping-cart "></i>&nbsp;{{ price }}&nbsp;<i class="fa fa-rub "></i>&nbsp;<span class="badge badge-primary">{{ count }}</span></a>
    {% else %}
    <a href="{{ home }}/plugin/basket/" class="btn btn-outline-primary">
        <i class="fa fa-shopping-cart "></i>&nbsp;Корзина</a>
    {% endif %}
</div>