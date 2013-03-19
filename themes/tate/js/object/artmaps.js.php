<?php
header('Content-type: application/javascript', true);
foreach(array('../base', '../util', 'ui', 'map') as $f)
    require_once("$f.js");
?>

