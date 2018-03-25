<?php 
require 'config.php';


$connect = mysql_connect("localhost:3306" ,$user, $pass) or 
die('Could not connect to MySQL database. ' . mysql_error());
mysql_select_db ("salce24ore");



$sql4= "CREATE TABLE IF NOT EXISTS " . "righe_ordine (
  Ordine_id int(10) unsigned NOT NULL default 0,
  Piatto_id int(10) unsigned NOT NULL default 0,
  Descrizione text NOT NULL COMMENT 'Imìnserito per ingredienti piadina',
  Quantita int(10) unsigned NOT NULL default 0,
  Prezzo double(8,2) NOT NULL default 0.00,
  KEY FK_righe_ordine_1 (Piatto_id),
  CONSTRAINT righe_ordine_ibfk_3 FOREIGN KEY (Piatto_id) REFERENCES piatti (Piatto_Id),
  CONSTRAINT righe_ordine_ibfk_4 FOREIGN KEY (Ordine_id) REFERENCES testata_ordini (Ordine_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='InnoDB free: 3072 kB'
";
$res = mysql_query($sql4) or 
     die(mysql_error());
?>