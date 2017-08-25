<!-- Page Header -->
<header class="intro-header" style="background-image: url('{tpl_url}/img/home-bg.jpg')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <div class="post-heading">
                    {% if havePermission %}<a href="{{ staticEditLink }}" class="pull-right "><i class="fa fa-pencil"></i></a>{% endif %}
                    <h1>{{ staticTitle }}</h1>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Page Content -->
<div class="container">
    <div class="row">
        <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
            <article>
                <small title="{{ staticDate }}"><i class="fa fa-calendar"></i>&nbsp;{{ staticDateStamp | cdate  }}</small>
                <a href="{{ staticPrintLink }}" rel="nofollow" class="pull-right btn btn-sm btn-outline-secondary"><i class="fa fa-print"></i></a>
            </article>
        </div>
    </div>
</div>