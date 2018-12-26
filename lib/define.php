<?php

// Folder with route6 objects.
$files6 = scandir ("../registry/data/route6/");

// Folder with route objects.
$files4 = scandir ("../registry/data/route/");

// Define array() we are gonna store data inside.
$roas = array();

define ("MAX_LEN_IPV4", 28);
define ("MAX_LEN_IPV6", 64);
?>
