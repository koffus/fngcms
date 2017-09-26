<!DOCTYPE html>
<html lang="{l_langcode}">
<head>
    <title>BixBite CMS &copy; jChat plugin</title>
    <meta charset="{l_encoding}" />
    <meta http-equiv="content-language" content="{l_langcode}" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="generator" content="BixBite CMS jChat plugin" />
    <meta name="document-state" content="dynamic" />
    <!-- Bootstrap Core CSS -->
    <link href="{scriptLibrary}/bootstrap-4.0.0/bootstrap.css" rel="stylesheet">
    <!-- Additional fonts for this theme -->
    <!--link href="https://fonts.googleapis.com/css?family=Roboto:300" rel="stylesheet" /-->
    <link href="{scriptLibrary}/fontawesome-4.7.0/fontawesome.css" rel="stylesheet"/>
    <!-- Custom styles for this theme -->
    <link href="{jchat.self.css}" rel="stylesheet" type="text/css" media="screen" />
    <!--[if lt IE 9]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery first, then Tether, then Bootstrap JS. -->
    <script src="{scriptLibrary}/js/jquery-3.2.1.js"></script>
    <script src="{scriptLibrary}/tether-1.4.0/tether.js"></script>
    <script src="{scriptLibrary}/bootstrap-4.0.0/bootstrap.js"></script>
    <script src="{scriptLibrary}/js/notify-3.1.5.js"></script>
    <!-- Theme JavaScript -->
    <script src="{scriptLibrary}/engine.js"></script>
</head>
<body>
<section class="section">
    <h1>Чат-бокс</h1>
    <!-- SCRIPTS INTERNALS BEGIN ((( DO NOT CHANGE ))) -->
        [:include jchat.script.header.js]
    <!-- SCRIPTS INTERNALS END -->
    <div class="chat-table" onclick="jchatProcessAreaClick(event);">
        <table id="jChatTable" class="table"><tr><td><i class="fa fa-spinner fa-pulse"></i> {l_loading}</td></tr></table>
    </div>
    [post-enabled]
    <form method="post" name="jChatForm" id="jChatForm" onsubmit="chatSubmitForm(); return false;">
        [not-logged]
        <div class="form-group">
            <input type="text" name="name" value="{l_jchat:input.username}" onfocus="if(!jChatInputUsernameDefault){this.value='';jChatInputUsernameDefault=1;}" class="form-control" />
        </div>
        [/not-logged]
        <div class="form-group chat-textarea">
            <textarea id="jChatText" name="text" rows="8" onfocus="jchatCalculateMaxLen(this,'jchatWLen', {maxlen});" onkeyup="jchatCalculateMaxLen(this,'jchatWLen', {maxlen});" class="form-control"></textarea>
            <span id="jchatWLen">{maxlen}</span>
        </div>
        <div class="form-group">
            <input id="jChatSubmit" type="submit" value="{l_jchat:button.post}" class="btn btn-primary" />
        </div>
    </form>
    [/post-enabled]
    <!-- SCRIPTS INTERNALS BEGIN ((( DO NOT CHANGE ))) -->
        [:include jchat.script.footer.js]
    <!-- SCRIPTS INTERNALS END -->
</section>
    <div id="loading-layer" class="col-md-3"><i class="fa fa-spinner fa-pulse"></i> Loading, please wait ...</div>
</body>
</html>