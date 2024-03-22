<?php
$xmlParser=simplexml_load_file("LV2.xml");

foreach($xmlParser as $record)
{
    echo"<h2> Ime: </h2>";
    echo "<span>".$record->ime. "</span>";
    echo"<h2> Prezime: </h2>";
    echo "<span>".$record->prezime. "</span>";
    echo"<h2> Email: </h2>";
    echo "<span>".$record->email. "</span>";
    echo"<h2> Zivotopis: </h2>";
    echo "<span>".$record->zivotopis. "</span>";
    echo"<h2> Slika: </h2>";
    echo "<img src=".$record->slika;
    echo"<br>";
}

