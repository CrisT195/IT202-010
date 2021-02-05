<?php
echo "PHP Strings Exercise 1 <br>\n";
echo strlen("Hello World!");
echo "<br><br>PHP Strings Exercise 2 <br>\n";
echo strrev("Hello World!");
echo "<br><br>PHP Strings Exercise 3 <br>\n";
$oldtxt = "Hello World!";
$newtxt = str_replace("World", "Dolly", $oldtxt);
echo $newtxt;
?>
