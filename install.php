<?php

require 'config.php';


$instOK=0;

$connect = mysql_connect("localhost:3306" ,$user, $pass) or 
die('Could not connect to MySQL database. ' . mysql_error());

echo "Connection to dB server localhost  OK <br>";

$sql = "CREATE DATABASE IF NOT EXISTS " . "salce24ore" . ";";
$res = mysql_query($sql) or 
     die(mysql_error());



mysql_select_db ("salce24ore");


$sql1= "CREATE TABLE IF NOT EXISTS " . "tipo_piatti (
  Tipo_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  nome    VARCHAR(45) NOT NULL,
  PRIMARY KEY(Tipo_id)
)
TYPE = InnoDB
";

$res = mysql_query($sql1) or 
     die(mysql_error());


/* fill the data*/
	$query = "SELECT * from tipo_piatti";
	$result = mysql_query($query) or die(mysql_error());
	$num = mysql_num_rows($result);
	if ($num==0)
	{
		$sqlIns="INSERT INTO tipo_piatti (nome) 
	         VALUES('Primo')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO tipo_piatti (nome) 
	         VALUES('Secondo')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO tipo_piatti (nome) 
	         VALUES('Contorno')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO tipo_piatti (nome)  
	         VALUES('Bibita')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());	
			
		$sqlIns="INSERT INTO tipo_piatti (nome) 
	         VALUES('Dessert')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());										
	}



$sql2= "CREATE TABLE  IF NOT EXISTS " . "piatti (
  Piatto_id int(10) unsigned NOT NULL auto_increment,
  Tipo_id   int(10) unsigned NOT NULL default 0,
  Nome      text NOT NULL,
  Prezzo    double(8,2) default 0.00,
  Quantita  int(10) unsigned NOT NULL default 0,
  Qta_min   int(10) unsigned NOT NULL default 0,
	Mostra    varchar(1) NOT NULL default '1',
  PRIMARY KEY  (Piatto_id),
  KEY FK_piatti_1 (Tipo_id),
  CONSTRAINT FK_piatti_1 FOREIGN KEY  (Tipo_id) REFERENCES tipo_piatti (Tipo_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Piatti disponibili'
";
$res = mysql_query($sql2) or 
     die(mysql_error());



$sql3= "CREATE TABLE IF NOT EXISTS " . "testata_ordini (
  `Ordine_id` int(10) unsigned NOT NULL auto_increment,
  `Cassa_id` int(10) unsigned NOT NULL default '0',
  `Data` datetime NOT NULL default '0000-00-00 00:00:00',
  `Prezzo` double(8,2) NOT NULL default '0.00',
  `Coperti` INT NOT NULL DEFAULT '1',
  PRIMARY KEY  (Ordine_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
";



$res = mysql_query($sql3) or 
     die(mysql_error());



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



$sql5= "CREATE TABLE IF NOT EXISTS " . " cassa (
  Cassa_id int(10) unsigned NOT NULL auto_increment,
  ipaddr VARCHAR(45) NOT NULL,
  PRIMARY KEY(Cassa_id)
	)ENGINE=InnoDB DEFAULT CHARSET=latin1
";
$res = mysql_query($sql5) or 
     die(mysql_error());
	
/* fill the data*/
	$query = "SELECT * from cassa";
	$result = mysql_query($query) or die(mysql_error());
	$num = mysql_num_rows($result);
	if ($num==0)
	{
		$sqlIns="INSERT INTO cassa (ipaddr) 
	         VALUES('127.0.0.1')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO cassa (ipaddr) 
	         VALUES('192.168.1.102')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO cassa (ipaddr) 
	         VALUES('192.168.1.103')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO cassa (ipaddr) 
	         VALUES('192.168.1.104')";
		$res = mysql_query($sqlIns) or 
     	die(mysql_error());							
	}	
	
	
		 
		 
$sql6= "CREATE TABLE IF NOT EXISTS " . " gestione_cassa (
  id int(10) unsigned NOT NULL auto_increment,
	voce VARCHAR(45) NOT NULL,
  consuntivo DOUBLE(8,2) NOT NULL,
  note VARCHAR(256) NOT NULL,
  PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=latin1
";		 
$res = mysql_query($sql6) or 
     die(mysql_error());
		 
		 
/* fill the data*/
	$query = "SELECT * from gestione_cassa";
	$result = mysql_query($query) or die(mysql_error());
	$num = mysql_num_rows($result);
	if ($num==0)
	{
		$sqlIns="INSERT INTO gestione_cassa (voce, consuntivo, note) 
	         VALUES('Ordinativi',0,'Consuntivo consumazioni cucina')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO gestione_cassa (voce, consuntivo, note) 
	         VALUES('Bar',0,'Consuntivo consumazioni bar')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO gestione_cassa (voce, consuntivo, note) 
	         VALUES('Altro',0,'')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
		$sqlIns="INSERT INTO gestione_cassa (voce, consuntivo, note) 
	         VALUES('Totale',0,'Consuntivo totale')";

		$res = mysql_query($sqlIns) or 
     	die(mysql_error());							
	}

echo "<br> <br>";
echo "Installazione completata con successo <br>";
$instOK=1;

?>


<html>
<head>
<title>Documento senza titolo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

<div>
Torna alla Schermata 
<a href="index.htm" title="index" target="_self"> principale</a> 

</div>

</body>
</html>
