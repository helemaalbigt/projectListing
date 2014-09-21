<?php
include_once './inc/db.inc.php';
include_once './inc/functions.inc.php';
require './inc/parameter_values.inc.php';

echo $_SERVER['DOCUMENT_ROOT'].APP_FOLDER;

echo ltrim("okok","k")."<br>";

echo ltrim("/projectListing/images/original/1410611672_1565.jpg", '/projectListing');
echo "<br>".ltrim("/projectListing/images/original/1408706757_7210.jpg", "/projectListing");
echo "<br>".str_replace("/projectListing", "", "/projectListing/images/original/1410611672_1565.jpg");


?>
