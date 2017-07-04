<!DOCTYPE html>
<html lang="{{ lang['langcode'] }}" dir="ltr">
<head>
    <title>{{ titles }}</title>
    <meta charset="{{ lang['encoding'] }}" />
    <meta http-equiv="content-language" content="{{ lang['langcode'] }}" />
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no"/>
    <meta http-equiv="Cache-Control" content="no-cache"/>
    <link href="{{ scriptLibrary }}/fontawesome-4.7.0/fontawesome.css" rel="stylesheet"/>
    <link href="{{ skin }}" rel="stylesheet"/>
    <link href="{{ skins_url }}/css/style.css" rel="stylesheet"/>

    <script src="{{ scriptLibrary }}/js/jquery-3.2.1.js"></script>
    <script src="{{ scriptLibrary }}/bootstrap-3.3.7/bootstrap.js"></script>
    <script src="{{ scriptLibrary }}/js/notify-3.1.5.js"></script>
    <script src="{{ scriptLibrary }}/functions.js"></script>
    <script src="{{ scriptLibrary }}/ajax.js"></script>
    <script src="{{ scriptLibrary }}/admin.js"></script>
    <script src="{{ skins_url }}/js/script.js"></script>
</head>
<body>

    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle sidebar-toggle">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand hidden-xs" href="admin.php" title="{{ lang['mainpage_t'] }}">Next Generation CMS</a>
            </div>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li><a href="admin.php?mod=statistics&action=clearCacheFiles" title="{{ lang['clearCacheFiles'] }}"><i class="fa fa-recycle" aria-hidden="true"></i></a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-plus"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a href="admin.php?mod=news&action=add">{{ lang['head_add_news'] }}</a></li>
                            <li><a href="admin.php?mod=categories&action=add">{{ lang['head_add_cat'] }}</a></li>
                            <li><a href="admin.php?mod=static&action=addForm">{{ lang['head_add_stat'] }}</a></li>
                            <li><a href="admin.php?mod=users" class="add_form">{{ lang['head_add_user'] }}</a></li>
                        </ul>
                    </li>
                    <li class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell-o"></i>{{ unnAppLabel }}
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">{{ unnAppText }}</li>
                            {{ unapproved1 }}
                            {{ unapproved2 }}
                            {{ unapproved3 }}
                            <li><a href="admin.php?mod=pm" title="{{ lang['pm_t'] }}"><i class="fa fa-envelope-o"></i> {{ newpmText }}</a></li>
                        </ul>
                    </li>
                    <li class="dropdown user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="{{ user.avatar }}" class="img-circle" alt="User Image" width="25" height="25"> <span class="hidden-xs">{{ user.name }}</span></a>
                        <ul class="dropdown-menu">
                            <li class="user-header">
                                <img src="http://lorempixel.com/output/nature-q-c-280-100-5.jpg">
                            </li>
                            <li class="user-avatar">
                                <img src="{{ user.avatar }}" class="img-circle" alt="User Image">
                            </li>
                            <li class="user-body">
                                <p><b>{{ user.name }}</b><br><small class="text-muted">{{ user.status }}</small></p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="?mod=users&action=editForm&id={{ user.id }}" class="btn btn-default btn-flat">{{ lang['my_profile'] }}</a>
                                </div>
                                <div class="pull-right">
                                    <a href="admin.php?action=logout" title="{{ lang['logout_t'] }}" class="btn btn-default btn-flat">{{ lang['logout'] }}</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="side-menu-container navbar navbar-inverse">
        <ul class="nav navbar-nav">
            <li><a href="{{ home }}" target="_blank"><i class="fa fa-external-link"></i> {{ lang['mainpage'] }}</a></li>
            <li>
                <a href="#active_content" data-toggle="collapse" {{ h_active.content }}>
                    <i class="fa fa-newspaper-o"></i> {{ lang['content'] }} <span class="caret"></span></a>
                <ul id="active_content" class="navbar-nav panel-collapse collapse">
                    <li><a href="admin.php?mod=news">{{ lang['content_news'] }}</a></li>
                    <li><a href="admin.php?mod=categories">{{ lang['content_categories'] }}</a></li>
                    <li><a href="admin.php?mod=static">{{ lang['content_static'] }}</a></li>
                    <li><a href="admin.php?mod=images">{{ lang['content_images'] }}</a></li>
                    <li><a href="admin.php?mod=files">{{ lang['content_files'] }}</a></li>
                </ul>
            </li>
            <li>
                <a href="#h_active_users" data-toggle="collapse" {{ h_active.users }}>
                    <i class="fa fa-users"></i> {{ lang['users'] }} <span class="caret"></span></a>
                <ul id="h_active_users" class="navbar-nav panel-collapse collapse">
                    <li><a href="admin.php?mod=users">{{ lang['users_management'] }}</a></li>
                    <li><a href="admin.php?mod=ipban">{{ lang['users_blocked'] }}</a></li>
                    <li><a href="admin.php?mod=ugroup">{{ lang['users_groups'] }}</a></li>
                    <li><a href="admin.php?mod=perm">{{ lang['users_perm'] }}</a></li>
                </ul>
            </li>
            <li><a href="admin.php?mod=extras" {{ h_active.extras }}><i class="fa fa-puzzle-piece"></i> {{ lang['extras'] }}</a></li>
            <li>
                <a href="#h_active_themes" data-toggle="collapse" {{ h_active.themes }}>
                    <i class="fa fa-paint-brush"></i> {{ lang['themes'] }} <span class="caret"></span></a>
                <ul id="h_active_themes" class="navbar-nav panel-collapse collapse">
                    <li><a href="admin.php?mod=themes">{{ lang['themes_management'] }}</a></li>
                    <li><a href="admin.php?mod=templates"> {{ lang['templates'] }}</a></li>
                </ul>
            </li>
            <li>
                <a href="#h_active_options" data-toggle="collapse" {{ h_active.options }}>
                    <i class="fa fa-cogs"></i> {{ lang['options'] }} <span class="caret"></span></a>
                <ul id="h_active_options" class="navbar-nav panel-collapse collapse">
                    <li><a href="admin.php?mod=options">{{ lang['options_all'] }}</a></li>
                    <li><a href="admin.php?mod=configuration">{{ lang['options_system'] }}</a></li>
                    <li><a href="admin.php?mod=dbo">{{ lang['options_database'] }}</a></li>
                    <li><a href="admin.php?mod=rewrite">{{ lang['options_rewrite'] }}</a></li>
                    <li><a href="admin.php?mod=cron">{{ lang['options_cron'] }}</a></li>
                    <li><a href="admin.php?mod=statistics">{{ lang['options_statistics'] }}</a></li>
                </ul>
            </li>
            <li>
                <a href="#active_help" data-toggle="collapse" {{ h_active.help }}>
                    <i class="fa fa-leanpub"></i> {{ lang['help'] }} <span class="caret"></span></a>
                <ul id="active_help" class="navbar-nav panel-collapse collapse">
                    <li><a href="http://ngcms.ru/forum/" target="_blank"> Форум поддержки</a></li>
                    <li><a href="http://wiki.ngcms.ru/" target="_blank"> Wiki NG CMS</a></li>
                    <li><a href="http://ngcms.ru/" target="_blank"> Официальный сайт</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <main id="adminDataBlock" class="side-body">

    <noscript><div class="alert alert-danger">Внимание! В вашем браузере отключен <b>JavaScript</b><br />Для полноценной работы с админ. панелью <b>включите его</b></div></noscript>
