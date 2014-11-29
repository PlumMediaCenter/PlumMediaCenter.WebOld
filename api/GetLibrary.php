<?php

//for now, just read out the library.json file. Having the api use GetLibrary.php to retrieve the libary allows us to change the web code without having
//to also push a fix to the roku app should anything need to change with the library location or filename
readfile("library.json");
?>
