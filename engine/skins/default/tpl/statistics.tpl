<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=options">{{ lang['options'] }}</a></li>
	<li class="active">{{ lang['statistics'] }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
	{% if(flags.confError) %}
	<!-- Configuration errors -->
	<div class=" alert alert-danger" role="alert">
		<h3>{{ lang['pconfig.error'] }}</h3>
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>{{ lang['perror.parameter'] }}</th>
					<th>{{ lang['perror.shouldbe'] }}</th>
					<th>{{ lang['perror.set'] }}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Register Globals</td>
					<td>{{ lang['statistics.disabled'] }}</td>
					<td>{{ flags.register_globals }}</td>
				</tr>
				<tr>
					<td>Magic Quotes GPC</td>
					<td>{{ lang['statistics.disabled'] }}</td>
					<td>{{ flags.magic_quotes_gpc }}</td>
				</tr>
				<tr>
					<td>Magic Quotes Runtime</td>
					<td>{{ lang['statistics.disabled'] }}</td>
					<td>{{ flags.magic_quotes_runtime }}</td>
				</tr>
				<tr>
					<td>Magic Quotes Sybase</td>
					<td>{{ lang['statistics.disabled'] }}</td>
					<td>{{ flags.magic_quotes_sybase }}</td>
				</tr>
			</tbody>
		</table>
		
		<p><a href="#" data-toggle="modal" data-target="#perror">{{ lang['perror.howto'] }}?</a></p>
	</div>
	<div id="perror" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4>{{ lang['perror.howto'] }}</h4>
				</div>
				<div class="modal-body">
					{{ lang['perror.descr'] }}
				</div>
				<div id="modalmsgWindowButton" class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{ lang['close'] }}</button>
				</div>
			</div>
		</div>
	</div>
	{% endif %}
	
	<div class="row">
		<div class="col-lg-3 col-xs-6">
			<a href="admin.php?mod=news" class="small-box bg-info">
				<div class="inner">
					<h3>{{ news_draft + news_unapp + news }}</h3>
					<p>{{ lang['news'] }}</p>
				</div>
				<i class="fa fa-newspaper-o"></i>
			</a>
		</div>
		<div class="col-lg-3 col-xs-6">
			<a href="admin.php?mod=images" class="small-box bg-success">
				<div class="inner">
					<h3>{{ images }}</h3>
					<p>{{ lang['images'] }}</p>
				</div>
				<i class="fa fa-picture-o"></i>
			</a>
		</div>
		<div class="col-lg-3 col-xs-6">
			<a href="admin.php?mod=files" class="small-box bg-warning">
				<div class="inner">
					<h3>{{ files }}</h3>
					<p>{{ lang['files'] }}</p>
				</div>
				<i class="fa fa-file-text-o"></i>
			</a>
		</div>
		<div class="col-lg-3 col-xs-6">
			<a href="admin.php?mod=users" class="small-box bg-danger">
				<div class="inner">
					<h3>{{ users }}</h3>
					<p>{{ lang['users'] }}</p>
				</div>
				<i class="fa fa-user"></i>
			</a>
		</div>
	</div>
		 
	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ lang['server'] }}</h4>
				</div>
				<table class="table table-statistics">
					<tr>
						<td>{{ lang['os'] }}</td>
						<td>{{ php_os }}</td>
					</tr>
					<tr>
						<td>{{ lang['php_version'] }} / {{ lang['mysql_version'] }}</td>
						<td>{{ php_version }} / {{ mysql_version }}</td>
					</tr>
					<tr>
						<td>{{ lang['gd_version'] }}</td>
						<td>{{ gd_version }}</td>
					</tr>
					<tr>
						<td>{{ lang['pdo_support'] }}</td>
						<td>{{ pdo_support }}</td>
					</tr>
					<tr>
						<td>{{ lang['opcache_support'] }}</td>
						<td>{{ opcache_support }}</td>
					</tr>
				</table>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>Next Generation CMS <span id="needUpdate" class="badge bg-success pull-right">Обновите CMS</span></h4>
				</div>
				<table class="table table-statistics">
					<tr>
						<td>{{ lang['current_version'] }}</td>
						<td>{{ currentVersion }} [ {{engineVersionBuild}} ]</td>
					</tr>
					<tr>
						<td>{{ lang['lastRelease'] }}</td>
						<td><span id="lastRelease">loading..</span></td>
					</tr>
					<tr>
						<td>{{ lang['git_version'] }}</td>
						<td>
                            <span><a href="https://github.com/russsiq/fngcms/archive/master.zip">Download Zip</a></span> 
                            [ <span><a href="#" id="compare">Изменения</a> ]</span>
                        </td>
					</tr>
					<tr>
						<td>{{ lang.lastCommit }}</td>
						<td><span id="lastCommit">loading..</span></td>
					</tr>
					<!--tr>
						<td></td>
						<td><span id="lastCommitInfo">loading..</span></td>
					</tr-->
				</table>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ lang['size'] }}</h4>
				</div>
				<table class="table table-statistics">
					<thead>
						<tr>
							<th>{{ lang['group'] }}</th>
							<th>{{ lang['amount'] }}</th>
							<th>{{ lang['volume'] }}</th>
							<th>{{ lang['permissions'] }}</th>
						</tr>
					</thead>
					<tbody>
						<tr><td>{{ lang['group_images'] }}</td><td>{{ image_amount }}</td><td>{{ image_size }}</td><td>{{ image_perm }}</td></tr>
						<tr><td>{{ lang['group_files'] }}</td><td>{{ file_amount }}</td><td>{{ file_size }}</td><td> {{ file_perm }}</td></tr>
						<tr><td>{{ lang['group_photos'] }}</td><td>{{ photo_amount }}</td><td>{{ photo_size }}</td><td> {{ photo_perm }}</td></tr>
						<tr><td>{{ lang['group_avatars'] }}</td><td>{{ avatar_amount }}</td><td>{{ avatar_size }}</td><td> {{ avatar_perm }}</td></tr>
						<tr><td>{{ lang['group_backup'] }}</td><td>{{ backup_amount }}</td><td>{{ backup_size }}</td><td> {{ backup_perm }}</td></tr>
						<tr>
						<td colspan="2">{{ lang['mysql_size'] }}</td>
						<td>{{ mysql_size }}</td>
						<td></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ lang['system'] }}</h4>
				</div>
				<table class="table table-statistics">
					<tr>
						<td>{{ lang['all_cats'] }}</td>
						<td>{{ categories }}</td>
					</tr>
					<tr>
						<td>{{ lang['all_news'] }}</td>
						<td><a href="admin.php?mod=news&status=1">{{ news_draft }}</a> / <a href="admin.php?mod=news&status=2">{{ news_unapp }}</a> / <a href="?mod=news&status=3">{{ news }}</a></td>
					</tr>
					<tr>
						<td>{{ lang['all_comments'] }}</td>
						<td>{{ comments }}</td>
					</tr>
					<tr>
						<td>{{ lang['all_users'] }}</td>
						<td>{{ users }}</td>
					</tr>
					<tr>
						<td>{{ lang['all_users_unact'] }}</td>
						<td>{{ users_unact }}</td>
					</tr>
					<tr>
						<td>{{ lang['all_images'] }}</td>
						<td>{{ images }}</td>
					</tr>
					<tr>
						<td>{{ lang['all_files'] }}</td>
						<td>{{ files }}</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="admin_note panel panel-default">
				<div class="panel-heading">
					<h4>{{ lang['note'] }}</h4>
				</div>
				<form method="post" action="admin.php?mod=statistics">
					<input type="hidden" name="action" value="save" >
					<div class="panel-body">
						<textarea name="note" class="form-control" rows="8"{% if (not admin_note) %}placeholder="{{ lang['no_notes'] }}"{% endif %}>{{ admin_note }}</textarea>
					</div>
					<div class="panel-footer">
						<button type="submit" class="btn btn-success">{{ lang['save_note'] }}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
$(function(){
    var reqCompare = "https://api.github.com/repos/russsiq/fngcms/compare/{{ currentVersion }}...master";
    var reqReleas = "https://api.github.com/repos/russsiq/fngcms/releases/latest";
    var reqCommit = "https://api.github.com/repos/russsiq/fngcms/commits";
    requestJSON(reqReleas, function(json) {
        if(json.message == "Not Found") {
            $('#lastRelease').html("No Info Found");
        } else {
            var currentVersion = '{{ currentVersion }}';
            var engineVersionBuild = '{{ engineVersionBuild }}';
            var publish = json.published_at;
            if (currentVersion === json.tag_name && engineVersionBuild == publish.split('T')[0]) {
                $('#needUpdate').html('Обновление не требуется');
            }
            $('#lastRelease').html('<a href="'+ json.zipball_url +'">' + json.tag_name + '</a> [ ' + json.published_at.slice(0, 10) + ' ]');
        }
    });
    requestJSON(reqCommit, function(json) {
        if(json.message == "Not Found") {
            $('#lastCommit').html("No Info Found");
        } else {
            $('#lastCommit').html('<a href="'+json[0].html_url+'" target="_blank">'+json[0].sha.slice(0, 7)+'</a> \
                <b>@</b> <a href="'+json[0].committer.html_url+'" target="_blank">'+json[0].committer.login+'</a> [ '+
                json[0].commit.author.date.slice(0, 10) + ' ]');
        }
    });
    function requestJSON(url, callback) {
        $.ajax({
            url: url,
            beforeSend: function(jqXHR) {
                $('#list-files').html('Загрузка списка');
                jqXHR.overrideMimeType("application/json; charset=UTF-8");
                // Repeat send header ajax
                jqXHR.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            },
        })
        .done(function(data, textStatus, jqXHR) {
            if (typeof(data) == 'object') {
                callback.call(null, data);
            } else {
                $.notify({message: '<i><b>Bad reply from server</b></i>'},{type: 'danger'});
            }
        });
    }
});
</script>