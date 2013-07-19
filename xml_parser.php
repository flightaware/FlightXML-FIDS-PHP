<?php

require('airport_config.php');

require('table_class.php');



$tb = new table_class();

$tb->display();



$interval = $tb->interval;



   
echo "
<div  style='display:none'  id = 'refresh'>$interval</div>";
?>
