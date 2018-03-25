<?php 
require 'config.php';

$g_ordine_id="";
$g_cassa_id="";
$g_data="";
$g_prezzoTot="";


$connect = mysql_connect("localhost:3306" ,$user, $pass) or 
die('Could not connect to MySQL database. ' . mysql_error());
mysql_select_db ("salce24ore");




function Ordini_Table_Create()
{
	$query = "SELECT * from testata_ordini WHERE prezzo !=0 ORDER BY Ordine_id DESC";
	$result = mysql_query($query) or die(mysql_error());
	$num_piatti = mysql_num_rows($result);

$tabella_ordini=<<<EOD
	<table  class="tabellaListaOrdini" >
EOD;
	$row_color1 = "DDDDFF";
	$row_color2 = "CCCCFF";
	$row_color = $row_color2;
	/* Leggo tutti gli ordini disponibili */
	while($row = mysql_fetch_array($result))
	{
		$ordine_id = $row["Ordine_id"];
		$cassa_id = $row['Cassa_id'];
		$data = date("d/m/Y H:i" ,strtotime($row["Data"]) );
		$prezzo =  $row["Prezzo"];	
 						

		if($row_color == $row_color1)
			$row_color = $row_color2;
		else
			$row_color = $row_color1;		
			
$tabella_ordini.=<<< EOD
		<tr bgcolor='$row_color' class="tabellaListaOrdini" onDblClick="mostraOrdine($ordine_id)" >
		<td  width="10%">$ordine_id</td>
		<td  width="10%">$cassa_id</td>
		<td  width="40%">$data</td>
		<td  width="40%">$prezzo €</td>
		</tr>
EOD;
	}
	$tabella_ordini.=<<< EOD
	</table>
EOD;

	print"$tabella_ordini";
}



function getGlobalData()
{
	global	$g_ordine_id;
	global	$g_cassa_id;
	global	$g_data;
	global	$g_prezzoTot;

	if (!isset($_POST['ordine_id']))
		return;
	
	if (!$_POST['ordine_id'])
		return;
		
	$g_ordine_id = $_POST['ordine_id'];
	if (!$g_ordine_id)
		return;

	/* Get data for ordine */
	$query = "SELECT * from testata_ordini WHERE Ordine_id = $g_ordine_id";
	$result = mysql_query($query) or die(mysql_error());
	$row = mysql_fetch_array($result);

	$g_cassa_id = $row['Cassa_id'];
	$g_data = date("d/m/Y H:i" ,strtotime($row["Data"]) );
	$g_prezzoTot  =  $row["Prezzo"];
}




function mostraOrdine()
{
	global	$g_ordine_id;
	global	$g_cassa_id;
	global	$g_data;
	global	$g_prezzoTot;

	$ordine_ht="";
	$totale_ht="";
	
	if (!$g_ordine_id)
		return;
		
	/* Get data for ordine */
	$query = "SELECT * from testata_ordini WHERE Ordine_id = $g_ordine_id";
	$result = mysql_query($query) or die(mysql_error());
	$row = mysql_fetch_array($result);

	$g_cassa_id = $row['Cassa_id'];
	$g_data = $row["Data"];
	$g_prezzoTot  =  $row["Prezzo"];
		
	/* get all the row of the order */	
	$query = "SELECT * from righe_ordine WHERE Ordine_id = $g_ordine_id";
	$result = mysql_query($query) or die(mysql_error());

	/* leggo tutti i piatti dell'ordine */
	while($row = mysql_fetch_array($result))
	{
		$piatto_id = $row['Piatto_id'];
		$descrizione= $row['Descrizione'];
		$quantita = $row['Quantita'];		
		$prezzoRow= $row['Prezzo'];
		
		/* get the name of piatto Id*/		
		$query = "SELECT * from piatti WHERE Piatto_id = $piatto_id";
		$nome_result = mysql_query($query) or die(mysql_error());
		$nome_row = mysql_fetch_array($nome_result);
		$nome_piatto = $nome_row['Nome'];
		$prezzo = $nome_row['Prezzo'];
		
		$ordine_ht.=<<< EOD
<div  class="piatto_div">
<div  class="row_div">
<div  class="span_nome"> $nome_piatto  $descrizione </div>
<div  class="span_prezzo"> $prezzo €</div>
</div>
<div  class="row_div">
<div  class="span_num"> x $quantita </div>
<div  class="span_prezRow"> $prezzoRow €</div>
</div>
</div>
<div class="gestOrdini_ordine_blank_div"> &nbsp </div>
EOD;
	}	

		$totale_ht.=<<< EOD
<div class="gestOrdini_ordine_blank_div"> &nbsp </div>	
<div class="tot_div"> 
<span class="span_prezTit"> TOTALE </span>
<span class="span_prezTot"> $g_prezzoTot € </span>
</div>
EOD;
	
	print"$ordine_ht";
	print"$totale_ht";
}

getGlobalData();

?>

<html>
<head>
<title>24OreSalce - Stampa vecchio ordine</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="styleSheet/stili.css" rel="stylesheet" type="text/css" media="screen">
<link href="styleSheet/print_style.css" rel="stylesheet" type="text/css"  media="print">
<script src="javascript/javaroutines.js" language="javascript" type="text/javascript"> </script>
<script type="text/javascript">


function toCurrency(num)
{
  var n = Math.round(num * 100) / 100;

  var m = n.toString();
  var loc = m.indexOf(".");
  if(loc < 0)
  {
    loc = m.length;
    m += ".";
  }

  while(loc + 3 > m.length)
  {
    m += '0';
  }
  return m;
}


function mostraOrdine(ordine_id)
{
	myId=document.getElementById("ordine_id");
	
	myId.value=ordine_id;
	document.prova.submit();
}


function StampaOrdine()
{	
			window.print();
}


</script>
</head>

<body class="gestOrdini">

<div class="gestOrdini" > 
  <div class="gestOrdini_toolBar" >  
		<a href="gest_ordini.php"><img src="gfx/menu.png" class="menu_img" title="Torna a gestione ordini"> </a> 
	</div>
  
	<div class="lista_ordini"  > 
    <div  class="testataListaOrdini"  align="center" > 
      <table class="testataListaOrdini" >
        <tr> 
          <th class="testataListaOrdini" align='left' width="10%">Id</th>
          <th class="testataListaOrdini" align='left' width="10%">Cassa</th>
          <th class="testataListaOrdini" align='left' width="40%">Data</th>
          <th class="testataListaOrdini" align='left' width="40%">Prezzo</th>
        </tr>
      </table>
    </div>
    <div  class="tabellaListaOrdini"> 
      <?php Ordini_Table_Create(); ?>
    </div>
  </div>	
  
	<div  id="ordine" class="gestOrdini_ordine" >
		
		<div id="logo"  class="gestOrdini_ordine_logo" > 
			<div id="plogoData"  class="plogoData" > 
				DATA: <?php global $g_data; echo " ".$g_data ?>
			</div>
	
			<div id="logo_2"  class="plogoCassa" > 		
				CASSA: N <?php global $g_cassa_id; echo " ".$g_cassa_id ?>
			</div>
	
			<div id="logo_tit"  class="plogoOrdine" > 
				ORDINE N <?php global $g_ordine_id; echo " ".$g_ordine_id; ?> 
			</div> 
			
			<div id="logo_tit"  class="plogoAppetito" > 
				Buon Appetito! 
			</div> 
				
			<div id="logo_img"  class="plogo" > <img src="gfx/logo24ore_75x75.jpg"  > 
      </div>
		</div>
			<?php mostraOrdine(); ?>
	</div>
	
	<div id="div_ordine_ctrl" class="gestOrdini_ordine_ctrl"  >
    <form id="prova" name="prova" action="lista_ordini.php"  method="post">
	    <INPUT type="hidden"   name="ordine_id" id="ordine_id">
		  <INPUT type="button"  name="stampa"  value ="Stampa"   onClick="StampaOrdine()">
	  </form>
	</div>

</div>
</body>
</html>