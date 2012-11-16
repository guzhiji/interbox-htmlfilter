<?php

$htmlinput="";
$exetime=0;
if(isset($_POST["htmlinput"])){
	include "HTMLFilter.php";
	$filter=new HTMLFilter();

	$mtime = explode(" ", microtime());
	$starttime = $mtime[1] + $mtime[0];
	
	$htmlinput= $filter->filter($_POST["htmlinput"]);

	$mtime = explode(" ", microtime());
	$exetime=($mtime[1] + $mtime[0] - $starttime)*1000;
}
function htmlentities2($value){
	$value = str_replace("&", "&amp;", $value);
	$value = str_replace("\"", "&quot;", $value);
	$value = str_replace("<", "&lt;", $value);
	$value = str_replace(">", "&gt;", $value);
	return $value;
}
?>

<html>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<head><title>HTML Filter Test</title></head>
<body>
<form method="post">
<div>
<textarea name="htmlinput" style="width: 40%" rows="20"><?php
if(isset($_POST["htmlinput"])){
	echo htmlentities2($_POST["htmlinput"]);
}else{

	echo htmlentities2(<<<EOT
<img src=foo.jpg>
<img src='foo.jpg'>
<img src="foo.jpg">

<img src=foo.jpg >
<img src='foo.jpg' >
<img src="foo.jpg" >

<img src='foo.jpg>
<img src="foo.jpg>
<img src='foo.jpg >
<img src="foo.jpg >

<img src=foo.jpg'>
<img src=foo.jpg">
<img src=foo.jpg' >
<img src=foo.jpg" >

<img src=foo.jpg onclick=alert('hi')>
<img src='foo.jpg' onclick=alert('hi')>
<img src="foo.jpg" onclick=alert('hi')>

<img src='foo.jpg'onclick=alert('hi')>
<img src="foo.jpg"onclick=alert('hi')>

<img src='foo.jpg'\tonclick='alert('hi')'>
<img src="foo.jpg"\x00onclick="alert('hi')">

<script>a()</script>
<<script>a()</script>
<<script>>a()</script>
<<script>a()</script>>
<!-- < -->script>a()</script>
<script><</script>script>a()</script>

<<script>script>>
<<script<script>>

<a href="http://foo">bar</a>
<a href="https://foo">bar</a>
<a href="ftp://foo">bar</a>
<a href="mailto:foo">bar</a>
<a href="javascript:foo">bar</a>
<a href="java script:foo">bar</a>
<a href="java\tscript:foo">bar</a>
<a href="java\nscript:foo">bar</a>
<a href="java\rscript:foo">bar</a>
<a href="java\x00script:foo">bar</a>
<a href="jscript:foo">bar</a>
<a href="vbscript:foo">bar</a>
<a href="jAvAsCrIpT:foo">bar</a>
<a href=<script>ja</script>vascript:foo>bar</a>

<a href="&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#57;foo">bar</a>
<a href="&#0000106;&#0000097;&#0000118;&#0000097;&#0000115;&#0000099;&#0000114;&#0000105;&#0000112;&#0000116;&#0000057;foo">bar</a>
<a href="&#x6A;&#x61;&#x76;&#x61;&#x73;&#x63;&#x72;&#x69;&#x70;&#x74;&#x30;foo">bar</a>
<a href="&#X6A;&#X61;&#X76;&#X61;&#X73;&#X63;&#X72;&#X69;&#X70;&#X74;&#X30;foo">bar</a>

<a href="&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;foo">bar</a>
<a href="&#0000106;&#0000097;&#0000118;&#0000097;&#0000115;&#0000099;&#0000114;&#0000105;&#0000112;&#0000116;&#0000058;foo">bar</a>
<a href="&#x6A;&#x61;&#x76;&#x61;&#x73;&#x63;&#x72;&#x69;&#x70;&#x74;&#x3A;foo">bar</a>
<a href="&#X6A;&#X61;&#X76;&#X61;&#X73;&#X63;&#X72;&#X69;&#X70;&#X74;&#X3A;foo">bar</a>

<a href="&#106&#97&#118&#97&#115&#99&#114&#105&#112&#116&#58;foo">bar</a>
<a href="&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058foo">bar</a>
<a href="&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A;foo">bar</a>
<a href="&#X6A&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3Afoo">bar</a>

EOT
);

}
?></textarea>
<textarea name="htmloutput" style="width: 40%" rows="20"><?php echo htmlentities2($htmlinput);?></textarea>
</div>
<div>Execution Time: <?php echo $exetime; ?> ms</div>
<div>
<input type="submit" />
</div>
</form>
</body>
</html>
