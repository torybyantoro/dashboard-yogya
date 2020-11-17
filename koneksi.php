<?php
 
 $username="picktolight";
 $password="pick_to_light";
 $db="(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=192.168.156.5)(PORT=1521)) (CONNECT_DATA=(SERVER=dedicated)(SERVICE_NAME= epsgdg)))";
 $sambungan = ocilogon($username,$password,$db);


if ( !$sambungan)
{  
 echo "<h1> Sambungan gagal</h1>";
} 
?>