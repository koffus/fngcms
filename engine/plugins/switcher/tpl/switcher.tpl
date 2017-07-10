<div class="widget widget-switcher">
    <h3 class="widget-title">{{ lang['switcher:select'] }}</h3>
        <div class="widget-content">
        <select id="switcher_selector" onchange="sw_update();" class="form-control">{{ list }}</select>
        <script>
            function sw_update() {
                var x = document.getElementById('switcher_selector');
                document.cookie='sw_template='+x.value+'; expires=Mon,01-Jan-2017';
                document.location = document.location;
            }
        </script>
    </div>
</div>