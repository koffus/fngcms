<article>
    {% if havePermission %}<a href="{{ staticEditLink }}" class="pull-right "><i class="fa fa-pencil"></i></a>{% endif %}
    <h2 class="section-title">{{ staticTitle }}</h2>
    {{ staticContent }}
    <hr class="alert-info" />
    <small title="{{ staticDate }}"><i class="fa fa-calendar"></i>&nbsp;{{ staticDateStamp | cdate  }}</small>
    <a href="{{ staticPrintLink }}" rel="nofollow" class="pull-right btn btn-sm btn-outline-secondary"><i class="fa fa-print"></i></a>
</article>