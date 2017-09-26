<div class="widget widget-switcher">
    <div class="widget-header">
        <h4 class="widget-title">{{ lang['favorites:select'] }}</h4>
    </div>
    <div class="widget-body">
        <select onchange="sw_update($(this).val());" class="form-control">{{ list }}</select>
    </div>
</div>
<script>function sw_update(val) {setCookie('sw_template', val);location.reload();}</script>