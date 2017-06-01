<html>
<head>
	<title>NGCMS Runtime exception: <?php echo get_class($exception); ?></title>
	<meta charset="UTF-8"/>
	<style>
		body {
			font: 1em Georgia,"Times New Roman",serif;
		}
		.dmsg {
			border: 1px #EEEEEE solid;
			padding: 10px;
			background-color: yellow;
		}
		.dtrace TBODY TD {
			padding: 3px;
			/*border: 1px #EEEEEE solid;*/
			background-color: #EEEEEE;
		}
		.dtrace THEAD TD {
			padding: 3px;
			background-color: #EEEEEE;
			font-weight: bold;
		}
	</style>
</head>
<body>
<?php
	print "<h1>NGCMS Runtime exception: ".get_class($exception)."</h1>\n";
	print "<div class='dmsg'>".$exception->getMessage()."</div><br/>";
	print "<h2>Stack trace</h2>";
	print "<table class='dtrace'><thead><tr><td>#</td><td>Line #</td><td><i>Class</i>/Function</td><td>File name</td></tr></thead><tbody>";
	foreach ($exception->getTrace() as $k => $v) {
		print "<tr><td>".$k."</td><td>".$v['line']."</td><td>".(isset($v['class'])?('<i>'.$v['class'].'</i>'):$v['function'])."</td><td>".$v['file']."</td></tr>\n";
	}
	print "</tbody></table>";
