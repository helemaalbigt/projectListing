<?php
include_once './inc/db.inc.php';
include_once './inc/functions.inc.php';
require './inc/parameter_values.inc.php';

echo $_SERVER['DOCUMENT_ROOT'].APP_FOLDER;

echo ltrim("okok","k")."<br>";

echo ltrim("/projectListing/images/original/1410611672_1565.jpg", '/projectListing');
echo "<br>".ltrim("/projectListing/images/original/1408706757_7210.jpg", "/projectListing");
echo "<br>".str_replace("/projectListing", "", "/projectListing/images/original/1410611672_1565.jpg");

echo htmlspecialchars("<br> test $&", ENT_QUOTES);

echo "<br>";

$a = array('test' => "ok", 'test2' => "ok", 'test3' => "ok");
echo count($a);

$visible = false;

if($visible){
?>
<p>
	hahahahaha
	hahahad^$sq
	fqdlkfnqdlfmq
</p>
<p>
	hahahahaha
	hahahad^$sq
	fqdlkfnqdlfmq
</p>
<p>
	hahahahaha
	hahahad^$sq
	fqdlkfnqdlfmq
</p>

<?php } ?>