<?php
$titolo="";

require 'config.php';



$connect = mysql_connect("localhost:3306" ,$user, $pass) or 
die('Could not connect to MySQL database. ' . mysql_error());
mysql_select_db ("salce24ore");

function procAction()
{
    global $titolo; 
	
	if(!isset($_GET['piattoId']))
	{
		$piattoId=0;
	}
	else
	{
		$piattoId=$_GET['piattoId'];
	}
	$action =$_GET['action'];
	
	if( ($action=="modifica") && ($piattoId!=0))
	{
		$titolo="Modifica Piatto";
		piatto_getAllFromId($piattoId);
	}
	else
	{
		$titolo="Nuovo Piatto";
		
		global $piattiRow;
		$piattiRow['Quantita']=9999999;
		$piattiRow['Qta_min']=10;
		$piattiRow['Mostra']='1';
		$piattiRow['Fast_but_id']='0';
	}	
}

function piatto_getAllFromId($piatti_id)
{
	global $piattiRow;
	
	$query = "SELECT * FROM piatti WHERE Piatto_id = $piatti_id ";
  	$result = mysql_query($query) or die(mysql_error());
	$piattiRow = mysql_fetch_array($result);
	
}



function piatti_getType_select()
{
	global $piattiRow;
    $tipo_id="";
	
	mysql_select_db ("salce24ore");
	$query = "SELECT * from tipo_piatti";
	$result = mysql_query($query) or die(mysql_error());
	$num_tipi = mysql_num_rows($result);	

	while($row = mysql_fetch_array($result))
	{
  		$tipo_id   = $row['Tipo_id'];
		$tipo_nome = $row['nome'];
		
		if ($tipo_id == $piattiRow['Tipo_id'])
			echo "<option value='$tipo_id' selected> $tipo_nome </option>";
		else
			echo "<option value='$tipo_id'> $tipo_nome </option>";
	}		
}


function piatti_getFAstButtId_select()
{
global $piattiRow;

	for($i=0;$i<=4;$i++)
	{
		if ($piattiRow['Fast_but_id']==$i)
			echo "<option value='$i' selected> $i </option>";
		else
			echo "<option value='$i'> $i </option>";
	}		
}



function piatti_getId()
{
	global $piattiRow;

	
	if($_GET['action']=="nuovo")
	{
		mysql_select_db ("salce24ore");
		$query = "SELECT MAX(Piatto_id)  from piatti";
		$result = mysql_query($query) or die(mysql_error());
		$piatto = mysql_fetch_array($result);
		$piatti_id = $piatto[0] +1;
	}
	else
	{
		$piatti_id = $piattiRow['Piatto_id'];
	}
	echo "$piatti_id" ;
}
?>


<html>
<head>
<title>24 Ore Salce - Gestione Piatti</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="styleSheet/stili.css" rel="stylesheet" type="text/css">
<script src="javascript/javaroutines.js" language="javascript" type="text/javascript"></script>
</head>

<body class="bodyPiatti">
<?php procAction(); ?>

<div id="piattiMod" class="finestraGestionePiatti"> 

  <div class="finestraGestionePiatti_titolo"><?php  global $titolo; echo $titolo; ?></div>
  
  <form action="gest_piatti_proc.php" method="post" class="insPiatti_form">
    <table  border="0"  cellpadding="14" cellspacing="3" >
      <tr>
        <td  align="center" valign="middle"> Id
          <INPUT name="piatto_id" type="text" readonly="1"  size="7" maxlength="7" value="<?php  piatti_getId(); ?>" >
	    </td>
        <td  align="center" valign="middle"> Nome 
	      <input type="text" name="nome_piatto" value="<?php  global $piattiRow; echo $piattiRow['Nome']; ?>">
	    </td>                                   
	    <td  align="center" valign="middle"> Tipo
          <select name="tipo_piatto">
            <?php piatti_getType_select(); ?>		
		  </select>
        </td>
      </tr>
    </table>
	
    <table border="0"  cellpadding="14" cellspacing="3">
    <tr>
      <td  align="center" valign="middle"> Prezzo
        <INPUT type="text" name="prezzo_piatto" size="7" maxlength="7" 
		       value="<?php  global $piattiRow; echo $piattiRow['Prezzo']; ?>" onKeyUp="checkIsNum(this,'soldi')">
      </td>
      <td  align="center" valign="middle"> Quantità
        <INPUT type="text" name="quantita_piatto" size="7" maxlength="7" 
		       value="<?php  global $piattiRow; echo $piattiRow['Quantita']; ?> " onKeyUp="checkIsNum(this,'numeri')">
      </td>
      <td  align="center" valign="middle"> Qta Min.
        <INPUT type="text" name="qtaMin_piatto" size="7" maxlength="7" 
		       value="<?php  global $piattiRow; echo $piattiRow['Qta_min']; ?>" onKeyUp="checkIsNum(this,'numeri')">
      </td>
			<td  align="center" valign="middle"> Mostra
        <INPUT type="checkbox" name="Mostra"  
		       value= "Mostra" <?php  global $Mostra; if($piattiRow['Mostra']) echo 'checked="checked"'; ?> >
          Fast But.     
         <select name="fast_button_id">
            <?php piatti_getFAstButtId_select(); ?>		
		  </select>
      </td>								
    </tr>
  </table>	
    

	<br>
	<br>
	<hr>
	<table border="0"  cellpadding="4" cellspacing="1">
    <tr>
	<td width="90%">
	</td>
	<td >
		<INPUT type="hidden"  name="action"  value ="<?php echo $_GET['action'] ?>">
	</td>
	<td>	
        <INPUT type="submit"  name="submit"  value ="OK">
	</td>	
	<td>	
		<INPUT type="button"  name="cancel"  value ="Annulla" onClick="fineSubmitPiatto()">
	</td>
	</tr>
	</table>		
  </form>
  
</div>
</body>
</html>
