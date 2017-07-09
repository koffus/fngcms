<div class="widget widget-tags">
    <h3 class="widget-title">Облако тегов</h3>
    <div id="insertTagCloud"><ul class="list-unstyled">{entries}</ul></div>
    <br>
    <a href="{home}/plugin/tags/">Показать все теги</a>
</div>

<script src="{home}/engine/plugins/tags/tpl/skins/3d/swfobject.js"></script>
<script>
var insertCloudElementID = 'insertTagCloud';
var insertCloudClientWidth = document.getElementById(insertCloudElementID).clientWidth;
var insertCloudClientHeight = insertCloudClientWidth; //140;
var tagLine = '{cloud3d}';
var rnumber = Math.floor(Math.random()*9999999);
var widget_so = new SWFObject("{home}/engine/plugins/tags/tpl/skins/3d/tagcloud.swf?r="+rnumber, "tagcloudflash", insertCloudClientWidth, insertCloudClientHeight, "9", "#ffffff");
widget_so.addParam("allowScriptAccess", "always");
widget_so.addParam("wmode", "transparent");
widget_so.addVariable("tcolor", "0x333333");
widget_so.addVariable("tspeed", "115");
widget_so.addVariable("distr", "true");
widget_so.addVariable("mode", "tags");
widget_so.addVariable("tagcloud", tagLine);
widget_so.write(insertCloudElementID);
</script>