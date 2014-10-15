<?php
$post = array("one"=>"1","array"=>array("two"=>"2","three"=>"3"),"one"=>"1","array"=>array("two"=>"2","three"=>"3"));
$serialize = serialize($post);
echo $serialize.'<br>';
$comp = gzcompress($serialize, 9);
echo $comp.'<br>';
$un = unserialize($serialize);
print_r($un);
?>