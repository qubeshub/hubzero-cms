<?php

header("Content-type: application/json");

$db = App::get('db');
$query = '
    SELECT tag
    FROM #__tags';

$db->setQuery($query);

$tag = $db->loadAssoc();

$tagObj = json_encode($tag)

echo $tagObj;

?>