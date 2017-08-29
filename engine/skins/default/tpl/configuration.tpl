<!-- Navigation bar -->
<ul class="breadcrumb">
    <li><a href="admin.php">{{ lang['home'] }}</a></li>
    <li><a href="admin.php?mod=options">{{ lang['options'] }}</a></li>
    <li class="active">{{ lang['configuration_title'] }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
    <form action="admin.php?mod=configuration" method="POST" class="form-horizontal">
        <input type="hidden" name="mod" value="configuration" />
        <input type="hidden" name="token" value="{{ token }}" />
        <input type="hidden" name="selectedOption" id="selectedOption" />
        <input type="hidden" name="subaction" value="save" />
        <input type="hidden" name="save" value="" />

        <ul class="nav nav-tabs">
            <li class="active"><a href="#userTabs-db" data-toggle="tab">{{ lang['db'] }}</a></li>
            <li><a href="#userTabs-security" data-toggle="tab">{{ lang['security'] }}</a></li>
            <li><a href="#userTabs-system" data-toggle="tab">{{ lang['syst'] }}</a></li>
            <li><a href="#userTabs-news" data-toggle="tab">{{ lang['sn'] }}</a></li>
            <li><a href="#userTabs-users" data-toggle="tab">{{ lang['users'] }}</a></li>
            <li><a href="#userTabs-imgfiles" data-toggle="tab">{{ lang['files'] }}/{{ lang['img'] }}</a></li>
            <li><a href="#userTabs-cache" data-toggle="tab">{{ lang['cache'] }}</a></li>
        </ul>

        <br>

        <div id="userTabs" class="tab-content">
            <!-- ########################## DB TAB ########################## -->
            <div id="userTabs-db" class="tab-pane active">
                <fieldset>
                    <legend>{{ lang['db_connect'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['dbengine'] }} <span class="help-block">{{ lang['dbengine#desc'] }}</span></div>
                        <div class="col-md-7">
                            <input type="text" name="save_con[dbengine]" value="{{ config['dbengine'] }}" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['dbhost'] }} <span class="help-block">{{ lang['example'] }} localhost</span></div>
                        <div class="col-md-7">
                            <input type="text" name="save_con[dbhost]" value="{{ config['dbhost'] }}" id="db_dbhost" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['dbname'] }} <span class="help-block">{{ lang['example'] }} ng</span></div>
                        <div class="col-md-7">
                            <input type="text" name="save_con[dbname]" value="{{ config['dbname'] }}" id="db_dbname" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['dbuser'] }} <span class="help-block">{{ lang['example'] }} root</span></div>
                        <div class="col-md-7">
                            <input type="text" name="save_con[dbuser]" value="{{ config['dbuser'] }}" id="db_dbuser" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['dbpass'] }} <span class="help-block">{{ lang['example'] }} password</span></div>
                        <div class="col-md-7">
                            <input type="password" name="save_con[dbpasswd]" value="{{ config['dbpasswd'] }}" id="db_dbpasswd" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['dbprefix'] }} <span class="help-block">{{ lang['example'] }} ng</span></div>
                        <div class="col-md-7">
                            <input type="text" name="save_con[prefix]" value="{{ config['prefix'] }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">&nbsp;</div>
                        <div class="col-md-7">
                            <input type="button" value="{{ lang['btn_checkDB'] }}" onclick="ngCheckDB(); return false;" class="btn btn-default" />
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>{{ lang['db_backup'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['auto_backup'] }} <span class="help-block">{{ lang['auto_backup_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[auto_backup]', 'value' : config['auto_backup'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['auto_backup_time'] }} <span class="help-block">{{ lang['auto_backup_time_desc'] }}</span></div>
                        <div class="col-md-7">
                            <input type="number" name="save_con[auto_backup_time]" value="{{ config['auto_backup_time'] }}" class="form-control" />
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ########################## SECURITY TAB ########################## -->
            <div id="userTabs-security" class="tab-pane">
                <fieldset>
                    <legend>{{ lang['logging'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['x_ng_headers'] }} <span class="help-block">{{ lang['x_ng_headers#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectNY({'name' : 'save_con[x_ng_headers]', 'value' : config['x_ng_headers'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['syslog'] }} <span class="help-block">{{ lang['syslog_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[syslog]', 'value' : config['syslog'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['load'] }} <span class="help-block">{{ lang['load_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[load_analytics]', 'value' : config['load_analytics'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['load_profiler'] }} <span class="help-block">{{ lang['load_profiler_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" name="save_con[load_profiler]" value="{{ config['load_profiler'] }}" class="form-control" /></div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>{{ lang['security'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['flood_time'] }} <span class="help-block">{{ lang['flood_time_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" name="save_con[flood_time]" value="{{ config['flood_time'] }}" class="form-control" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_captcha'] }} <span class="help-block">{{ lang['use_captcha_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_captcha]', 'value' : config['use_captcha'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['captcha_font'] }} <span class="help-block">{{ lang['captcha_font_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[captcha_font]', 'value' : config['captcha_font'], 'values' : list['captcha_font'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_crypto_salt'] }} <span class="help-block">{{ lang['use_crypto_salt_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" name="save_con[crypto_salt]" value="{{ config['crypto_salt'] }}" class="form-control" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_cookies'] }} <span class="help-block">{{ lang['use_cookies_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_cookies]', 'value' : config['use_cookies'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_sessions'] }} <span class="help-block">{{ lang['use_sessions_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_sessions]', 'value' : config['use_sessions'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['sql_error'] }} <span class="help-block">{{ lang['sql_error_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[sql_error_show]', 'value' : config['sql_error_show'], 'values' : { 0 : lang['sql_error_0'], 1 : lang['sql_error_1'], 2 : lang['sql_error_2'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['multiext_files'] }} <span class="help-block">{{ lang['multiext_files_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectNY({'name' : 'save_con[allow_multiext]', 'value' : config['allow_multiext'] }) }}</div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>{{ lang['debug_generate'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['debug'] }} <span class="help-block">{{ lang['debug_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[debug]', 'value' : config['debug'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['debug_queries'] }} <span class="help-block">{{ lang['debug_queries_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[debug_queries]', 'value' : config['debug_queries'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['debug_profiler'] }} <span class="help-block">{{ lang['debug_profiler_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[debug_profiler]', 'value' : config['debug_profiler'] }) }}</div>
                    </div>
                </fieldset>
            </div>
            
            <!-- ########################## SYSTEM TAB ########################## -->
            <div id="userTabs-system" class="tab-pane">
                <fieldset>
                    <legend>{{ lang['syst'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['home_url'] }} <span class="help-block">{{ lang['example'] }} http://server.com</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[home_url]" value="{{ config['home_url'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['admin_url'] }} <span class="help-block">{{ lang['example'] }} http://server.com/engine</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[admin_url]" value="{{ config['admin_url'] }}" readonly/></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['home_title'] }} <span class="help-block">{{ lang['example'] }} NGcms</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[home_title]" value="{{ config['home_title']|escape }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['meta'] }} <span class="help-block">{{ lang['meta_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[meta]', 'value' : config['meta'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['meta_title'] }} <span class="help-block">{{ lang['meta_title_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[meta_title]" value="{{ config['meta_title'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['description'] }} <span class="help-block">{{ lang['description_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[description]" value="{{ config['description'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['keywords'] }} <span class="help-block">{{ lang['keywords_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[keywords]" value="{{ config['keywords'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['admin_mail'] }} <span class="help-block">{{ lang['example'] }} admin@server.com</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[admin_mail]" value="{{ config['admin_mail'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['lock'] }} <span class="help-block">{{ lang['lock_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectNY({'name' : 'save_con[lock]', 'value' : config['lock'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['lock_reason'] }} <span class="help-block">{{ lang['lock_reason_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[lock_reason]" value="{{ config['lock_reason'] }}" maxlength="200" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['theme'] }} <span class="help-block">{{ lang['theme_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[theme]', 'value' : config['theme'], 'values' : list['theme'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['skin'] }} <span class="help-block">{{ lang['skin_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[skin]', 'value' : config['skin'], 'values' : list['skin'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['lang'] }} <span class="help-block">{{ lang['lang_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[default_lang]', 'value' : config['default_lang'], 'values' : list['default_lang'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_gzip'] }} <span class="help-block">{{ lang['use_gzip_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_gzip]', 'value' : config['use_gzip'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['404_mode'] }} <span class="help-block">{{ lang['404_mode_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[404_mode]', 'value' : config['404_mode'], 'values' : { 0 : lang['404.int'], 1 : lang['404.ext'], 2 : lang['404.http'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['url_external_nofollow'] }} <span class="help-block">{{ lang['url_external_nofollow_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectNY({'name' : 'save_con[url_external_nofollow]', 'value' : config['url_external_nofollow'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['url_external_target_blank'] }} <span class="help-block">{{ lang['url_external_target_blank_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectNY({'name' : 'save_con[url_external_target_blank]', 'value' : config['url_external_target_blank'] }) }}</div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>{{ lang['email_configuration'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['mailfrom_name'] }} <span class="help-block">{{ lang['example'] }} Administrator</span></div>
                        <div class="col-md-7"><input class="mailfrom_name form-control" type="text" id="mail_fromname" name="save_con[mailfrom_name]" value="{{ config['mailfrom_name'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['mailfrom'] }} <span class="help-block">{{ lang['example'] }} mailbot@server.com</span></div>
                        <div class="col-md-7"><input class="mailfrom form-control" type="text" id="mail_frommail" name="save_con[mailfrom]" value="{{ config['mailfrom'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['mail_mode'] }} <span class="help-block">{{ lang['mail_mode#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[mail_mode]', 'id' : 'mail_mode', 'value' : config['mail_mode'], 'values' : { 'mail' : 'mail', 'sendmail' : 'sendmail', 'smtp' : 'smtp' } }) }}</div>
                    </div>
                </fieldset>
                <fieldset class="useSMTP">
                    <legend>{{ lang['smtp_config'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['smtp_host'] }} <span class="help-block">{{ lang['example'] }} smtp.mail.ru</span></div>
                        <div class="col-md-7"><input class="mailfrom form-control" type="text" name="save_con[mail][smtp][host]" id="mail_smtp_host" value="{{ config['mail']['smtp']['host'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['smtp_port'] }} <span class="help-block">{{ lang['example'] }} 25</span></div>
                        <div class="col-md-7"><input class="mailfrom form-control" type="text" name="save_con[mail][smtp][port]" id="mail_smtp_port" value="{{ config['mail']['smtp']['port'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['smtp_auth'] }} <span class="help-block">{{ lang['smtp_auth#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectNY({'name' : 'save_con[mail][smtp][auth]', 'id' : 'mail_smtp_auth', 'value' : config['mail']['smtp']['auth'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['smtp_secure'] }} <span class="help-block">{{ lang['smtp_secure#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[mail][smtp][secure]', 'id' : 'mail_smtp_secure', 'value' : config['mail']['smtp']['secure'], 'values' : { '' : 'None', 'tls' : 'TLS', 'ssl' : 'SSL' } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['smtp_auth_login'] }}: <span class="help-block">{{ lang['example'] }} email@mail.ru</span></div>
                        <div class="col-md-7"><input class="mailfrom form-control" type="text" id="mail_smtp_login" name="save_con[mail][smtp][login]" value="{{ config['mail']['smtp']['login'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['smtp_auth_pass'] }} <span class="help-block">{{ lang['example'] }} mySuperPassword</span></div>
                        <div class="col-md-7"><input class="mailfrom form-control" type="text" name="save_con[mail][smtp][pass]" id="mail_smtp_pass" value="{{ config['mail']['smtp']['pass'] }}" /></div>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['btn_checkSMTP'] }}</div>
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-addon">@</span>
                                <input type="email" name="" id="mail_tomail" value="" placeholder="EMail" class="form-control">
                                <span class="input-group-btn">
                                    <input type="button" class="btn btn-primary" value="{{ lang['submit'] }}" onclick="ngCheckEmail(); return false;">
                                </span>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ########################## NEWS TAB ########################## -->
            <div id="userTabs-news" class="tab-pane">
                <fieldset>
                    <legend>{{ lang['sn'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['number'] }}</div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[number]" value="{{ config['number'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['news_multicat_url'] }} <span class="help-block">{{ lang['news_multicat_url#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[news_multicat_url]', 'value' : config['news_multicat_url'], 'values' : { 0 : lang['news_multicat:0'], 1 : lang['news_multicat:1'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['news_translit'] }} <span class="help-block">{{ lang['news_translit#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[news_translit]','value' : config['news_translit'],'values' : { 1 : lang['yesa'], 0 : lang['noa'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['nnavigations'] }}<br/><span class="help-block">{{ lang['nnavigations_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[newsNavigationsCount]" value="{{ config['newsNavigationsCount'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['nnavigations_admin'] }}<br/><span class="help-block">{{ lang['nnavigations_admin_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[newsNavigationsAdminCount]" value="{{ config['newsNavigationsAdminCount'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['category_counters'] }} <span class="help-block">{{ lang['category_counters_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[category_counters]', 'value' : config['category_counters'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['news_view_counters'] }} <span class="help-block">{{ lang['news_view_counters#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[news_view_counters]', 'value' : config['news_view_counters'], 'values' : { 1 : lang['yesa'], 0 : lang['noa'], 2 : lang['news_view_counters#2'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['news_without_content'] }} <span class="help-block">{{ lang['news_without_content_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[news_without_content]', 'value' : config['news_without_content'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['date_adjust'] }} <span class="help-block">{{ lang['date_adjust_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[date_adjust]" value="{{ config['date_adjust'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['timestamp_active'] }} <span class="help-block">{{ lang['date_help'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[timestamp_active]" value="{{ config['timestamp_active'] }}" /> <span class="help-block">{{ lang['date_now'] }} {{ timestamp_active_now }}</span></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['smilies'] }} <span class="help-block">{{ lang['smilies_desc'] }} (<b>,</b>)</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[smilies]" value="{{ config['smilies'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['blocks_for_reg'] }} <span class="help-block">{{ lang['blocks_for_reg_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[blocks_for_reg]', 'value' : config['blocks_for_reg'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_smilies'] }} <span class="help-block">{{ lang['use_smilies_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_smilies]', 'value' : config['use_smilies'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_bbcodes'] }} <span class="help-block">{{ lang['use_bbcodes_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_bbcodes]', 'value' : config['use_bbcodes'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_htmlformatter'] }} <span class="help-block">{{ lang['use_htmlformatter_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_htmlformatter]', 'value' : config['use_htmlformatter'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['default_newsorder'] }} <span class="help-block">{{ lang['default_newsorder_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[default_newsorder]', 'value' : config['default_newsorder'], 'values' : { 'id desc' : lang['order_id_desc'], 'id asc' : lang['order_id_asc'], 'postdate desc' : lang['order_postdate_desc'], 'postdate asc' : lang['order_postdate_asc'], 'title desc' : lang['order_title_desc'], 'title asc' : lang['order_title_asc'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['template_mode'] }} <span class="help-block">{{ lang['template_mode#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[template_mode]', 'value' : config['template_mode'], 'values' : { 1 : lang['template_mode.1'], 2 : lang['template_mode.2'] } }) }}</div>
                    </div>
                </fieldset>
            </div>
            
            <!-- ########################## USERS TAB ########################## -->
            <div id="userTabs-users" class="tab-pane">
                <fieldset>
                    <legend>{{ lang['auth'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['remember'] }} <span class="help-block">{{ lang['remember_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[remember]', 'value' : config['remember'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['auth_module'] }} <span class="help-block">{{ lang['auth_module_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[auth_module]', 'value' : config['auth_module'], 'values' : list['auth_module'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['auth_db'] }} <span class="help-block">{{ lang['auth_db_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[auth_db]', 'value' : config['auth_db'], 'values' : list['auth_db'] }) }}</div>
                    </div>
                </fieldset>
                <fieldset>
                        <legend>{{ lang['users'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['users_selfregister'] }} <span class="help-block">{{ lang['users_selfregister_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[users_selfregister]', 'value' : config['users_selfregister'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['register_type'] }} <span class="help-block">{{ lang['register_type_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[register_type]', 'value' : config['register_type'], 'values' : { 0 : lang['register_extremly'], 1 : lang['register_simple'], 2 : lang['register_activation'], 3 : lang['register_manual'], 4 : lang['register_manual_confirm'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['user_aboutsize'] }} <span class="help-block">{{ lang['user_aboutsize_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[user_aboutsize]" value="{{ config['user_aboutsize'] }}" /></div>
                    </div>
                <fieldset>
                    <legend>{{ lang['users.avatars'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_avatars'] }} <span class="help-block">{{ lang['use_avatars_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_avatars]', 'value' : config['use_avatars'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['avatars_gravatar'] }} <span class="help-block">{{ lang['avatars_gravatar_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[avatars_gravatar]', 'value' : config['avatars_gravatar'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['avatars_url'] }} <span class="help-block">{{ lang['example'] }} http://server.com/uploads/avatars</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[avatars_url]" value="{{ config['avatars_url'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['avatars_dir'] }} <span class="help-block">{{ lang['example'] }} /home/servercom/public_html/uploads/avatars/</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[avatars_dir]" value="{{ config['avatars_dir'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['avatar_wh'] }} <span class="help-block">{{ lang['avatar_wh_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[avatar_wh]" value="{{ config['avatar_wh'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['avatar_max_size'] }} <span class="help-block">{{ lang['avatar_max_size_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[avatar_max_size]" value="{{ config['avatar_max_size'] }}" /></div>
                    </div>
                <fieldset>
                    <legend>{{ lang['users.photos'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['use_photos'] }} <span class="help-block">{{ lang['use_photos_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectYN({'name' : 'save_con[use_photos]', 'value' : config['use_photos'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['photos_url'] }} <span class="help-block">{{ lang['example'] }} http://server.com/uploads/photos</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[photos_url]" value="{{ config['photos_url'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['photos_dir'] }} <span class="help-block">{{ lang['example'] }} /home/servercom/public_html/uploads/photos/</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[photos_dir]" value="{{ config['photos_dir'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['photos_max_size'] }} <span class="help-block">{{ lang['photos_max_size_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[photos_max_size]" value="{{ config['photos_max_size'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['photos_thumb_size'] }} <span class="help-block">{{ lang['photos_thumb_size_desc'] }}</span></div>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="save_con[photos_thumb_size_x]" value="{{ config['photos_thumb_size_x'] }}" class="form-control" />
                                <span class="input-group-addon"> x </span>
                                <input type="text" name="save_con[photos_thumb_size_y]" value="{{ config['photos_thumb_size_y'] }}" class="form-control" />
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
            
            <!-- ########################## IMAGES TAB ########################## -->
            <div id="userTabs-imgfiles" class="tab-pane">
                <fieldset>
                    <legend>{{ lang['files'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['files_url'] }} <span class="help-block">{{ lang['example'] }} http://server.com/uploads/files</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[files_url]" value="{{ config['files_url'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['files_dir'] }} <span class="help-block">{{ lang['example'] }} /home/servercom/public_html/uploads/files/</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[files_dir]" value="{{ config['files_dir'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['attach_url'] }} <span class="help-block">{{ lang['example'] }} http://server.com/uploads/dsn</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[attach_url]" value="{{ config['attach_url'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['attach_dir'] }} <span class="help-block">{{ lang['example'] }} /home/servercom/public_html/uploads/dsn/</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[attach_dir]" value="{{ config['attach_dir'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['files_ext'] }} <span class="help-block">{{ lang['files_ext_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[files_ext]" value="{{ config['files_ext'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['files_max_size'] }} <span class="help-block">{{ lang['files_max_size_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[files_max_size]" value="{{ config['files_max_size'] }}" /></div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>{{ lang['img'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['images_url'] }} <span class="help-block">{{ lang['example'] }} http://server.com/uploads/images</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[images_url]" value="{{ config['images_url'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['images_dir'] }} <span class="help-block">{{ lang['example'] }} /home/servercom/public_html/uploads/images/</span></div>
                        <div class="col-md-7"><input class="form-control" type="text" name="save_con[images_dir]" value="{{ config['images_dir'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['images_ext'] }} <span class="help-block">{{ lang['images_ext_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[images_ext]" value="{{ config['images_ext'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['images_max_size'] }} <span class="help-block">{{ lang['images_max_size_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[images_max_size]" value="{{ config['images_max_size'] }}" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['images_dim_action'] }} <span class="help-block">{{ lang['images_dim_action#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[images_dim_action]', 'value' : config['images_dim_action'], 'values' : { 0 : lang['images_dim_action#0'], 1 : lang['images_dim_action#1'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['images_max_dim'] }} <span class="help-block">{{ lang['images_max_dim#desc'] }}</span></div>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="save_con[images_max_x]" value="{{ config['images_max_x'] }}" class="form-control" />
                                <span class="input-group-addon"> x </span>
                                <input type="text" name="save_con[images_max_y]" value="{{ config['images_max_y'] }}" class="form-control" />
                            </div>
                        </div>
                    </div>

                <!-- IMAGE transform control -->
                <fieldset>
                    <legend>{{ lang['img.thumb'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['thumb_mode'] }} <span class="help-block">{{ lang['thumb_mode_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[thumb_mode]', 'value' : config['thumb_mode'], 'values' : { 0 : lang['mode_demand'], 1 : lang['mode_forbid'], 2 : lang['mode_always'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['thumb_size'] }} <span class="help-block">{{ lang['thumb_size_desc'] }}</span></div>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" name="save_con[thumb_size_x]" value="{{ config['thumb_size_x'] }}" class="form-control" />
                                <span class="input-group-addon"> x </span>
                                <input type="text" name="save_con[thumb_size_y]" value="{{ config['thumb_size_y'] }}" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['thumb_quality'] }} <span class="help-block">{{ lang['thumb_quality_desc'] }}</span></div>
                        <div class="col-md-7">
                            <div class="input-group">
                                <input type="text" class="form-control" name="save_con[thumb_quality]" value="{{ config['thumb_quality'] }}" />
                                <span class="input-group-addon"> % </span>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>{{ lang['img.shadow'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['shadow_mode'] }} <span class="help-block">{{ lang['shadow_mode_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[shadow_mode]', 'value' : config['shadow_mode'], 'values' : { 0 : lang['mode_demand'], 1 : lang['mode_forbid'], 2 : lang['mode_always'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['shadow_place'] }} <span class="help-block">{{ lang['shadow_place_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[shadow_place]', 'value' : config['shadow_place'], 'values' : { 0 : lang['mode_orig'], 1 : lang['mode_copy'], 2 : lang['mode_origcopy'] } }) }}</div>
                    </div>
                <fieldset>
                    <legend>{{ lang['img.stamp'] }}</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['stamp_mode'] }} <span class="help-block">{{ lang['stamp_mode_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[stamp_mode]', 'value' : config['stamp_mode'], 'values' : { 0 : lang['mode_demand'], 1 : lang['mode_forbid'], 2 : lang['mode_always'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['stamp_place'] }} <span class="help-block">{{ lang['stamp_place_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[stamp_place]', 'value' : config['stamp_place'], 'values' : { 0 : lang['mode_orig'], 1 : lang['mode_copy'], 2 : lang['mode_origcopy'] } }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['wm_image'] }} <span class="help-block">{{ lang['wm_image_desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelect({'name' : 'save_con[wm_image]', 'value' : config['wm_image'], 'values' : list['wm_image'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['wm_image_transition'] }} <span class="help-block">{{ lang['wm_image_transition_desc'] }}</span></div>
                        <div class="col-md-7"><input type="text" name="save_con[wm_image_transition]" value="{{ config['wm_image_transition'] }}" class="form-control" /></div>
                    </div>
                <!-- END: IMAGE transform control -->
                </fieldset>
            </div>

            <!-- ########################## CACHE TAB ########################## -->
            <div id="userTabs-cache" class="tab-pane">
                <fieldset>
                    <legend>Memcached</legend>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['memcached_enabled'] }} <span class="help-block">{{ lang['memcached_enabled#desc'] }}</span></div>
                        <div class="col-md-7">{{ mkSelectNY({'name' : 'save_con[use_memcached]', 'value' : config['use_memcached'] }) }}</div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['memcached_ip'] }} <span class="help-block">{{ lang['example'] }} localhost</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[memcached_ip]" value="{{ config['memcached_ip'] }}" id="memcached_ip" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['memcached_port'] }} <span class="help-block">{{ lang['example'] }} 11211</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[memcached_port]" value="{{ config['memcached_port'] }}" id="memcached_port" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">{{ lang['memcached_prefix'] }} <span class="help-block">{{ lang['example'] }} ng</span></div>
                        <div class="col-md-7"><input type="text" class="form-control" name="save_con[memcached_prefix]" value="{{ config['memcached_prefix'] }}" id="memcached_prefix" /></div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5">&nbsp;</div>
                        <div class="col-md-7"><input type="button" class="btn btn-default" value="{{ lang['btn_checkMemcached'] }}" onclick="ngCheckMemcached(); return false;"/></div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="well text-center">
            <input type="submit" class="btn btn-success" value="{{ lang['save'] }}">
        </div>
    </form>
</div>

<script>
// Check DB connection
function ngCheckDB() {
    var url = '{{ admin_url }}/rpc.php';
    var method = 'admin.configuration.dbCheck';
    var params = {'token': '{{ token }}','dbhost': $("#db_dbhost").val(),'dbname': $("#db_dbname").val(),'dbuser': $("#db_dbuser").val(),'dbpasswd': $("#db_dbpasswd").val(),};
    $.reqJSON(url, method, params, function(json) {$.notify({message: json.errorText},{type: 'success'});});
}

// Check MEMCached connection
function ngCheckMemcached() {
    var url = '{{ admin_url }}/rpc.php';
    var method = 'admin.configuration.memcachedCheck';
    var params = {'token' : '{{ token }}','ip' : $("#memcached_ip").val(),'port' : $("#memcached_port").val(),'prefix' : $("#memcached_prefix").val(),};
    $.reqJSON(url, method, params, function(json) {$.notify({message:json.errorText},{type: 'success'});});
}

// Send test e-mail message
function ngCheckEmail() {
    var url = '{{ admin_url }}/rpc.php';
    var method = 'admin.configuration.emailCheck';
    var params = {
        'token': '{{ token }}',
        'mode': $("#mail_mode").val(),
        'from': {
            'name': $("#mail_fromname").val(),
            'email': $("#mail_frommail").val(),
        },
        'to': {
            'email': $("#mail_tomail").val(),
        },
        'smtp': {
            'host': $("#mail_smtp_host").val(),
            'port' : $("#mail_smtp_port").val(),
            'auth' : $("#mail_smtp_auth").val(),
            'login': $("#mail_smtp_login").val(),
            'pass': $("#mail_smtp_pass").val(),
            'secure': $("#mail_smtp_secure").val(),
        },
    };
    $.reqJSON(url, method, params, function(json) {$.notify({message:json.errorText},{type: 'success'});});
}
</script>

<script>
if ($("#mail_mode option:selected").val() != "smtp") {
    $(".useSMTP").hide();
}

$("#mail_mode").on('change', function() {
    if ($("#mail_mode option:selected").val() == "smtp") {
        $(".useSMTP").show();
    } else {
        $(".useSMTP").hide();
    }
});
</script>
