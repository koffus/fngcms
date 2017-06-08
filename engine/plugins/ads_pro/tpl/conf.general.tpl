<form method="post" action="admin.php?mod=extra-config&plugin=ads_pro">
	<input type="hidden" name="action" value="main_submit" />
	
	<fieldset>
	<legend>{{ lang['ads_pro:general_config'] }}</legend>
		<div class="form-group">
			<div class="row">
				<div class="col-sm-8">
					{{ lang['ads_pro:general_news'] }}
					<span class="help-block">{{ lang['ads_pro:general_news#desc'] }}</span>
				</div>
				<div class="col-sm-4">
					<select name="support_news" class="form-control">
						<option value="0" {{ s_news_0 }}>{{ lang['noa'] }}</option>
						<option value="1" {{ s_news_1 }}>{{ lang['yesa'] }}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-sm-8">
					{{ lang['ads_pro:news_cfg_sort'] }}
					<span class="help-block">{{ lang['ads_pro:news_cfg_sort#desc'] }}</span>
				</div>
				<div class="col-sm-4">
					<select name="news_cfg_sort" class="form-control">
						<option value="0" {{ s_news_sort_0 }}>{{ lang['ads_pro:news_cfg_sort_id'] }}</option>
						<option value="1" {{ s_news_sort_1 }}>{{ lang['ads_pro:news_cfg_sort_title'] }}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-sm-8">
					{{ lang['ads_pro:multidisplay_mode'] }}
					<span class="help-block">{{ lang['ads_pro:multidisplay_mode#desc'] }}</span>
				</div>
				<div class="col-sm-4">
					<select name="multidisplay_mode" class="form-control">
						<option value="0" {{ multidisplay_mode_0 }}>{{ lang['ads_pro:multidisplay_mode0'] }}</option>
						<option value="1" {{ multidisplay_mode_1 }}>{{ lang['ads_pro:multidisplay_mode1'] }}</option>
						<option value="2" {{ multidisplay_mode_2 }}>{{ lang['ads_pro:multidisplay_mode2'] }}</option>
					</select>
				</div>
			</div>
		</div>
	</fieldset>

	<div class="well text-center">
		<input type="submit" value="{{ lang['ads_pro:general_submit'] }}" class="btn btn-success">
		<a href="admin.php?mod=extra-config&plugin=ads_pro&action=clear_cash" class="btn btn-primary">{{ lang['ads_pro:button_clear_cash'] }}</a>
	</div>
</form>
