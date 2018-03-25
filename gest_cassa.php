

<?php
require 'config.php';

$g_prezzoTotale=0;



function dBConnect()
{
	global $connect, $user, $pass;
	
	$connect = mysql_connect("localhost:3306" ,$user, $pass) or 
	die('Could not connect to MySQL database. ' . mysql_error());
	mysql_select_db ("salce24ore");
}



function dBDisconnect()
{
	global $connect;
	
	mysql_close($connect);
}


function cassaSetFromUser()
{
	

	if(!isset($_POST['Bar']))
		$consBar=0;
	else
		$consBar= $_POST['Bar'];
			
	if(!isset($_POST['Altro']))
		$consAltro=0;	
	else
		$consAltro=$_POST['Altro'];
		
	if(!isset($_POST['AltroNote']))
		$noteAltro="";			
	else
		$noteAltro=$_POST['AltroNote'];
		
	if ( ($consBar==0) && ($consAltro==0) && ($noteAltro==""))
		return;
			
	
	$sqlUpd="UPDATE gestione_cassa SET consuntivo=$consBar WHERE voce = 'Bar' ";
	$res = mysql_query($sqlUpd) or 
     	   die(mysql_error());
				 
	$sqlUpd="UPDATE gestione_cassa SET consuntivo=$consAltro WHERE voce = 'Altro' ";
	$res = mysql_query($sqlUpd) or 
     	   die(mysql_error());
	
	$sqlUpd="UPDATE gestione_cassa SET note='$noteAltro' WHERE voce = 'Altro' ";
	$res = mysql_query($sqlUpd) or 
     	   die(mysql_error());			 				 
		
}


function cassaSetTotale()
{
	$prezzoOrdini=0;
	$consuntivo=0;
	
	$query = "SELECT Prezzo from testata_ordini";
	$result = mysql_query($query) or die(mysql_error());
	
	while($row = mysql_fetch_array($result))
	{
		$prezzoOrdini+=$row[0];
	}
	
	$sqlUpd="UPDATE gestione_cassa SET consuntivo=$prezzoOrdini WHERE voce = 'Ordinativi' ";
	$res = mysql_query($sqlUpd) or 
     	die(mysql_error());
				
	/* aggiorna le altra casse */
	$query = "SELECT * from gestione_cassa";
	$result = mysql_query($query) or die(mysql_error());	
	
	while($row = mysql_fetch_array($result))
	{
		$nome= $row['voce'];
		
		if($nome!="Totale")
		{
			$consuntivo += $row['consuntivo'];
			$sqlUpd="UPDATE gestione_cassa SET consuntivo=$consuntivo WHERE voce = 'Totale' ";
			$res = mysql_query($sqlUpd) or 
     			die(mysql_error());
		}			
	}
}



function Cassa_Table_Create()
{
	$data_piatti="";
	
	
	$query = "SELECT * from gestione_cassa";
	$result = mysql_query($query) or die(mysql_error());
	$readOnly="";
	$td_htm="";

$pippo=<<<EOD
	<table  class="tabellaListaCassa" >
EOD;
	$row_color1 = "DDDDFF";
	$row_color2 = "CCCCFF";
	$row_color = $row_color2;
	/* Leggo tutti i piatti disponibili */
	while($row = mysql_fetch_array($result))
	{
		$voce = $row["voce"];
		$consuntivo = $row['consuntivo'];
		$note = $row["note"];
			
		if( ($voce=="Ordinativi") || ($voce=="Totale"))
			$readOnly="readonly" ;
		else	
		 	$readOnly="";
			
		if($voce=="Altro")
			$tdNote_htm="<INPUT name='AltroNote' type='text' $readOnly  size='50' maxlength='50' value='$note' >";
		else
			$tdNote_htm=$note;
		 						
		if($row_color == $row_color1)
			$row_color = $row_color2;
		else
			$row_color = $row_color1;		
			
  		$data_piatti.=<<< EOD
		<tr bgcolor='$row_color'  >
			
		<td  width="20%">$voce</td>
		<td  width="15%"> <INPUT name="$voce" type="text" $readOnly  size="9" maxlength="9" value=" $consuntivo " onKeyUp="checkIsNum(this,'soldi')" onChange='checkNull(this)'> €</td>
		<td  width="65%"> $tdNote_htm </td>
		</tr>
EOD;
	}
	$data_piatti.=<<< EOD
	</table>
EOD;

	print"$pippo";
	print"$data_piatti";
}


dBConnect();
cassaSetFromUser();
cassaSetTotale()

?>

<html>
<head>
<title>24 Ore Salce - Gestione Cassa</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="styleSheet/stili.css" rel="stylesheet" type="text/css">
<script src="javascript/javaroutines.js" language="javascript" type="text/javascript"></script>

<script language="javascript" type="text/javascript">
function checkNull(obj)
{
	if(obj.value=="")
		obj.value=0;
		
}
</script>

</head>
<body class="gestCassa">



<div  class="gestPiatti"> 
  <div  class="gestPiatti_toolBar"> <a href="index.htm" > <img src="gfx/home.png"  class="menu_img" title="Torna allo schermata principale"> 
    </a> <a href="gest_ordini.php" > <img src="gfx/menu.png" class="menu_img" title="Gestione ordini" > 
    </a> </div>
  <div class="lista_piatti"> 
    <div  class="testataListaPiatti"> 
      <form action="gest_cassa.php" method="post" class="insPiatti_form">
        <?php Cassa_Table_Create(); ?>
        <hr>
        <table border="0"  cellpadding="4" cellspacing="1">
          <tr> 
            <td width="90%"></td>
            <td> <INPUT type="submit"  name="submit"  value ="OK"> </td>
            <td> <INPUT type="button"  name="cancel"  value ="Annulla" onClick="fineSubmitPiatto()"> 
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>

</body>
</html>