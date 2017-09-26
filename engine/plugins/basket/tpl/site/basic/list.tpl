<h2 class="section-title">Моя корзина</h2>

<section class="section">
    <div class="card card-body">
        {% if (recs > 0) %}
        <form method="post" action="{{ home }}/plugin/basket/update/">
            <table class="table">
            <thead>
                <tr><th>#</th><th>Наименование</th><th class="text-center" width="80">Кол-во</th><th class="text-right">Цена</th><th class="text-right">Стоимость</th></tr>
            </thead>
            <tbody>
                {% for entry in entries %}
                <tr>
                    <td>{{ loop.index }}</td>
                    <td>{{ entry.title }}</td>
                    <td class="text-center" width="80">
                        <input name="count_{{ entry.id }}" type="number" maxlength="5" class="form-control input-sm form-control-sm text-right" value="{{ entry.count }}"/>
                    </td>
                    <td class="text-right">{{ entry.price }}</td>
                    <td class="text-right">{{ entry.sum }} <!--span class="delete" onclick="$(this).closest('tr').remove();">Удалить</span--></td>
                </tr>
                {% endfor %}
            </tbody>
            <tfoot>
                <tr><td colspan="4">Итого:</td><td class="text-right">{{ total }}</td></tr>
            </tfoot>
            </table>
            <hr class="alert-info">
            <a href="{{ form_url }}" class="btn btn-primary">Оформить заказ</a>
            <input type="submit" class="btn btn-secondary pull-right" value="Пересчитать"/>
        </form>
        {% else %}
        <div class="text-center">
        <p><i class="fa fa-shopping-cart fa-5x text-muted"></i></p>
        <p>Ваша корзина пуста!</p>
        </div>
        {% endif %}
    </div>
</section>
