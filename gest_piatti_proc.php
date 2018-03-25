<?php 
require 'config.php';

$g_get_close=""; 

$connect = mysql_connect("localhost:3306" ,$user, $pass) or 
die('Could not connect to MySQL database. ' . mysql_error());
mysql_select_db ("salce24ore");


function proccessAction()
{
	global $g_get_close;

	if(!isset($_GET['action']))
		$action =$_POST['action'];
	else
		$action =$_GET['action'];
	

	if ($action=="nuovo")
		Piatti_TableInsert();
	else if ($action=="modifica")
		Piatti_TableUpdate();
	else if ($action=="cancella")
		Piatti_TableDelete();
	else if ($action=="MostraTutto")
		Piatti_MostraTutto();
	else if ($action=="NascondiTutto")
		Piatti_NascondiTutto();		
		
 	if($action!="nuovo")
 		$g_get_close='onLoad="fineSubmitPiatto()"';
	else					
		$g_get_close='onLoad="ContiunuaSubmitPiatto()"';
}


function Piatti_TableInsert()
{
	$nome_piatto     =$_POST['nome_piatto'];
	$tipo_piatto     =$_POST['tipo_piatto'];
	$prezzo_piatto   =$_POST['prezzo_piatto'];
	$quantita_piatto =$_POST['quantita_piatto'];
	$qtaMin_piatto   =$_POST['qtaMin_piatto'];
	$fastButId_piatto=$_POST['fast_button_id'];
	
	if(!isset($_POST['Mostra']))
		$Mostra          =0;
	else
		$Mostra          =1;
	
	$sqlIns="INSERT INTO piatti (Nome, Tipo_id, Prezzo, Quantita, Qta_min, Mostra,Fast_but_id ) 
	         VALUES('$nome_piatto','$tipo_piatto','$prezzo_piatto','$quantita_piatto','$qtaMin_piatto','$Mostra','$fastButId_piatto')";

	$res = mysql_query($sqlIns) or 
     	die(mysql_error());
}




function Piatti_TableUpdate()
{
	$id_piatto       =$_POST['piatto_id'];
	$nome_piatto     =$_POST['nome_piatto'];
	$tipo_piatto     =$_POST['tipo_piatto'];
	$prezzo_piatto   =$_POST['prezzo_piatto'];
	$quantita_piatto =$_POST['quantita_piatto'];
	$qtaMin_piatto   =$_POST['qtaMin_piatto'];
	$fastButId_piatto=$_POST['fast_button_id'];
	
		if(!isset($_POST['Mostra']))
		$Mostra          =0;
	else
		$Mostra          =1;
	

	$sqlUpd="UPDATE piatti SET Nome='$nome_piatto' , Tipo_id=$tipo_piatto, Prezzo=$prezzo_piatto, 
	                           Quantita=$quantita_piatto, Qta_min=$qtaMin_piatto, Mostra=$Mostra, Fast_but_id=$fastButId_piatto
             WHERE Piatto_id = $id_piatto";

	$res = mysql_query($sqlUpd) or 
     	die(mysql_error());
}

function Piatti_TableDelete()
{
	$id_piatto=$_GET['piattoId'];
	$sqlUpd="DELETE FROM piatti WHERE Piatto_id = $id_piatto";

	$res = mysql_query($sqlUpd) or 
     	die(mysql_error());
}


function Piatti_MostraTutto()
{
	$sqlUpd="UPDATE piatti SET Mostra=1";

	$res = mysql_query($sqlUpd) or 
     	die(mysql_error());
}


function Piatti_NascondiTutto()
{
	$sqlUpd="UPDATE piatti SET Mostra=0";

	$res = mysql_query($sqlUpd) or 
     	die(mysql_error());
}


proccessAction();
?>

<html>
<head>
<title>Documento senza titolo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script type="text/javascript">
function fineSubmitPiatto()
{
top.opener.window.location.reload();
window.close();
}

function ContiunuaSubmitPiatto()
{
top.opener.window.location.reload();
location.href = "gest_piatti_InMask.php?action=nuovo";
}





</script>
</head>

<body <?php global $g_get_close; echo $g_get_close ?>  >
</body>
</html>
