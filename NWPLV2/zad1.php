<?php
#naziv baze podataka
$databaseName="radovi";

#direktorij za backup baze podataka
$directory="backup/$databaseName";

#provjera postoji li directory, ako ne posotji stvori 
if (!is_dir($directory)) {
    if (!@mkdir($directory))
    { 
        die("<p>Ne možemo stvoriti direktorij: $directory</p>");
    }
}
$time=time();

$databaseconnect=mysqli_connect('localhost','root','',$databaseName) OR die("<p>Ne mozemo se povezati na bazu podataka .$databaseName.</p></body></html>");
#sql upit za dohvacanje svih tablica u bazi
$r=mysqli_query($databaseconnect,"SHOW TABLES");
#provjera postoji li neka tablica u bazi
if(mysqli_num_rows($r)>0)
{
    echo"<p> Radi se backup za bazu podataka .$databaseName.</p>";
    #Iteriranje kroz sve tablice u bazi podataka
    while(list($table)=mysqli_fetch_array($r,MYSQLI_NUM))
    {
        $query="SELECT * FROM $table";
        $r2=mysqli_query($databaseconnect,$query);
        #provjera postoje li pdoaci u tablici
        if(mysqli_num_rows($r2)>0)
        {
            #otvaranje datoteke 
            if($fileOpen=gzopen("$directory/{$table}_{$time}.sql.gz", 'w9'))
            {
                #dohvacanje informacija o stupcima 
                $columInfo=mysqli_fetch_fields($r2);
                $columns=array();
                foreach($columInfo as $columnsValue)
                {
                    $columns[]=$columnsValue->name;//spremanje imena stupca u columns
                }

                #iteriramo kroz svaki podatak u tablici
                while($row = mysqli_fetch_array($r2,MYSQLI_NUM))
                {
                    #zapis u datoteku zapocinjemo sa insert into
                    gzwrite($fileOpen, "INSERT INTO $table (");
                    
                    #koristimo implode funkciju za zapis imena stupaca u INSERT INTO 
                    $columnsString=implode(",",$columns);
                    gzwrite($fileOpen,"$columnsString)\n");
                    
                    #potom dodajemo VALUES
                    gzwrite($fileOpen,"VALUES (");

                    #Dodajemo vrijednost za svaki redak
                    $values=array_map('addslashes',$row);
                    $valueString="'".implode("', '",$values)."'";
                    gzwrite($fileOpen, "$valueString);\n");
                }
                #zatvaramo datoteku
                gzclose($fileOpen);


                echo"<p> Backup uspješno obavljen.</p>";
            }
            else 
            {
                echo"<p>Datoteka $directory/{$table}_{$time}.sql.gz se ne moze otvoriti </p>";
                break;
            }
        }
    }
}
else 
{
    echo"<p>Baza .$databaseName. ne sadrži tablice.</p>";
}