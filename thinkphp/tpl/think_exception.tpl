<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<title>系统发生错误</title>
<style type="text/css">
*{ padding: 0; margin: 0; }
html{ overflow-y: scroll; }
body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px; }
img{ border: 0; }
.error{ padding: 24px 48px; }
.face{ font-size: 10.25em; color:red;font-weight: 400; line-height: 1.0; margin-bottom: .15em; }
.error .content{ padding-top: 10px;}
.error .info{ margin-bottom: 12px; }
.error .info .title{ margin-bottom: 3px; }
h1{ font-size:2.75em;line-height:1.2;font-weight:200}
h2{ padding-bottom:.3em;line-height:1.225;border-bottom:1px solid #eee;color: #000; font-weight: 300; font-size: 1.75em; }
.error .info .text{ line-height: 24px; }
.copyright{ font-weight:200;padding: 12px 48px; color: #999; }
.copyright a{ color: #000; text-decoration: none;font-weight:300;font-size:1.1em }
</style>
</head>
<body>
<div class="error">
<p class="face">:(</p>
<h1><?php echo '<span style="color:#999">[ '.$e['code'].' ]</span> '.strip_tags($e['message']);?></h1>
<div class="content">
<?php if(isset($e['file'])) {?>
<div class="info">
	<div class="title">
		<h2>错误位置</h2>
	</div>
	<div class="text">
		<p>FILE: <?php echo $e['file'] ;?> &#12288;LINE: <?php echo $e['line'];?></p>
	</div>
</div>
<?php }?>
<?php if(isset($e['trace'])) {?>
<div class="info">
	<div class="title">
		<h2>TRACE</h2>
	</div>
	<div class="text">
		<p><?php echo nl2br($e['trace']);?></p>
	</div>
</div>
<?php }?>
</div>
</div>
<div class="copyright">
<p><a title="官方网站" href="http://www.thinkphp.cn">ThinkPHP</a> <sup><?php echo THINK_VERSION ?></sup> { Fast & Simple OOP PHP Framework } -- [ WE CAN DO IT JUST THINK ]</p>
</div>
</body>
</html>