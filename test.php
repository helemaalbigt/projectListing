<?php
include_once '/inc/functions.inc.php';
require '/inc/parameter_values.inc.php';

//helps interptre french accented characters. They have special needs
header('Content-type: text/html; charset=utf-8');

$s = "FRjoiner5joiner10joinerWHERE projecttype='projets-_--eprojects-_--eproject' OR projecttype='concourss-_--ewedstrijds-_--ecompetition' OR projecttype='étude de faisibilités-_--ehaalbaarheidsstudies-_--efeasibility study' OR projecttype='autres-_--eanderes-_--eother'";
$a = splitData($s, 0, "joiner");
print_r($a)

?>
