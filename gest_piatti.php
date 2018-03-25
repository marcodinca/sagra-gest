

<?php
require 'config.php';





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


function Piatti_Table_Create()
{
	$data_piatti="";

	$query = "SELECT * from piatti ORDER BY Tipo_id, Nome";
	$result = mysql_query($query) or die(mysql_error());
	$num_piatti = mysql_num_rows($result);

$pippo=<<<EOD
	<table  class="tabellaListaPiatti" >
EOD;
	$row_color1 = "DDDDFF";
	$row_color2 = "CCCCFF";
	$row_color = $row_color2;
	/* Leggo tutti i piatti disponibili */
	while($row = mysql_fetch_array($result))
	{
		$Piatto_id = $row["Piatto_id"];
		$Nome = $row['Nome'];
		$Tipo_id = $row["Tipo_id"];	
 						
		/* Ottieni il nome Del tipo di piatto */
  		$query_type= "SELECT nome FROM tipo_piatti WHERE Tipo_id = $Tipo_id ";
  		$result_type = mysql_query($query_type) or die(mysql_error());
  
  		$row_tipo = mysql_fetch_array($result_type);
  		$Tipo_id = $row_tipo['nome'];
  		$Prezzo = $row['Prezzo'];
  		$Quantita = $row['Quantita'];
  		$Qta_min = $row['Qta_min'];
			$Mostra = $row['Mostra'];
			$checked='';
			
			if($Mostra)
				$checked='checked="checked"';

		if($row_color == $row_color1)
			$row_color = $row_color2;
		else
			$row_color = $row_color1;		
			
  		$data_piatti.=<<< EOD
		<tr bgcolor='$row_color' class="tabellaListaPiatti" name="$Piatto_id" 
		    onDblClick="modificaPiatto($Piatto_id, '$Nome')" >
			
		<td  width="5%">$Piatto_id</td>
		<td  width="20%">$Nome</td>
		<td  width="10%">$Tipo_id</td>
		<td  width="12%">$Prezzo €</td>
		<td  width="10%">$Quantita</td>
		<td  width="10%">$Qta_min</td>
		<td  width="5%" <INPUT type="checkbox" name="Mostra"  value= "Mostra" $checked disabled="disabled"></td>
		<td  width="10%"> 
			<img src="gfx/db_update.png"  title="Modifica Piatto" onClick="modificaPiatto($Piatto_id, '$Nome')">
			&nbsp; &nbsp; 
			<img src="gfx/db_remove.png"  title="Cancella Piatto" onClick="cancellaPiatto($Piatto_id, '$Nome')">
		</td>
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

?>

<html>
<head>
<title>24 Ore Salce - Gestione Piatti</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="styleSheet/stili.css" rel="stylesheet" type="text/css">
<script src="javascript/javaroutines.js" language="javascript" type="text/javascript"></script>
</head>
<body class="gestPiatti">



<div  class="gestPiatti"> 
  <div  class="gestPiatti_toolBar"> <a href="index.htm" > <img src="gfx/home.png"  class="menu_img" title="Torna allo schermata principale"> 
    </a> <a href="gest_ordini.php" > <img src="gfx/menu.png" class="menu_img" title="Gestione ordini" > 
    </a> <a  href="javascript:modificaPiatto('nuovo', '')" > <img  id="piattoNew" src="gfx/piattoNew48x48.png"  class="menu_img" title="Aggiungi nuovo piatto"> 
    </a> <a  href="javascript:MostraTutto()" > <img src="gfx/menu_all_in.png" width="48" height="48" class="menu_img"  id="MostraTutto" title="Aggiungi tutti i piatti nel menu"> 
    </a> <a  href="javascript:NascondiTutto()" > <img src="gfx/menu_all_out.png" width="48" height="48" class="menu_img"  id="NascondiTutto" title="Togli tutti i piatti dal menu"> 
    </a> </div>
  <div class="lista_piatti"> 
    <div  class="testataListaPiatti"> 
      <table class="testataListaPiatti" >
        <tr> 
          <th class="testataListaPiatti" align='left' width="5%">Id</th>
          <th class="testataListaPiatti" align='left' width="20%">Nome</th>
          <th class="testataListaPiatti" align='left' width="10%">Tipo</th>
          <th class="testataListaPiatti" align='left' width="12%">Prezzo</th>
          <th class="testataListaPiatti" align='left' width="10%">Quantità</th>
          <th class="testataListaPiatti" align='left' width="10%">Qta min</th>
          <th class="testataListaPiatti" align='left' width="5%">Mostra</th>
          <th class="testataListaPiatti" align='left' width="10%">Operazioni</th>
        </tr>
      </table>
    </div>
    <div  class="tabellaListaPiatti"> 
      <?php Piatti_Table_Create(); ?>
    </div>
  </div>
</div>

</body>
</html>
