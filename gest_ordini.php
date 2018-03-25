<?php 
require 'config.php';

$g_piatto="";
$g_lenPiatto="";
$g_ordine_id=0;
$g_coperti=1;


$g_ordPiatto="";
$g_ordPiattoIndex="";


$connect = mysql_connect("localhost:3306" ,$user, $pass) or 
die('Could not connect to MySQL database. ' . mysql_error());
mysql_select_db ("salce24ore");


function getPiatti_select($piatto_tipo)
{

	switch($piatto_tipo)
	{
		case "Primo":
			$piatto_tipo=1;
			break;
			
		case "Secondo":
			$piatto_tipo=2;
			break;
			
		case "Contorno":
			$piatto_tipo=3;
			break;	

		case "Bibita":
			$piatto_tipo=4;
			break;
			
		case "Dessert":
			$piatto_tipo=5;
			break;
			
		default:
			return;									
	}


	mysql_select_db ("salce24ore");
	$query = " SELECT * FROM piatti WHERE Tipo_id = $piatto_tipo AND Mostra like 1 ORDER BY 'Nome' ";
	$result = mysql_query($query)or die(mysql_error());
	
	while($row = mysql_fetch_array($result))
	{
		$nome= $row['Nome'];
		echo "<option value='$nome'> $nome </option>";
	}
}




function getPiatti_FastButt($piatto_tipo,$fastButtId)
{

	switch($piatto_tipo)
	{
		case "Primo":
			$piatto_tipo=1;
			break;
			
		case "Secondo":
			$piatto_tipo=2;
			break;
			
		case "Contorno":
			$piatto_tipo=3;
			break;	

		case "Bibita":
			$piatto_tipo=4;
			break;
			
		case "Dessert":
			$piatto_tipo=5;
			break;
			
		default:
			return;									
	}


	mysql_select_db ("salce24ore");
	$query = " SELECT * FROM piatti WHERE Tipo_id = $piatto_tipo AND Mostra like 1 AND Fast_but_id=$fastButtId";
	$result = mysql_query($query)or die(mysql_error());
	
	while($row = mysql_fetch_array($result))
	{
		$nome= $row['Nome'];
		echo "<b>$nome</b>";
	}
}




function getPiatti_fromDb()
{
	global $g_piatto;
	global $g_lenPiatto;
	$piatto_tipo=1;
	$i=1;	

	for ($piatto_tipo=1; $piatto_tipo<=5; $piatto_tipo++)
	{
		$query = " SELECT * FROM piatti WHERE Tipo_id = $piatto_tipo AND Mostra like 1 ORDER BY 'Nome' ";
		$result = mysql_query($query)or die(mysql_error());
		$g_lenPiatto[$piatto_tipo] = mysql_num_rows($result);
		$i=1;
		while($g_piatto[$piatto_tipo][$i] = mysql_fetch_array($result))
		{
			$i++;
		}
	}	
}



function updateQtainDb( $piatto_id, $quantita)
{	
	/* Now get theNumber of piatto_id in DB in order to subtract the number of that piatto in the ordine */			
 		$query = "SELECT Quantita  from piatti WHERE Piatto_id = $piatto_id ";
		$result = mysql_query($query) or die(mysql_error());
		$qta = mysql_fetch_array($result);
		$qta_orig = $qta[0];
		$qta_upd = $qta_orig - $quantita;
		if ($qta_upd<0)
			$qta_upd = 0;
	
	/* now Update the quanta in the piatti table */
	$sqlIns ="UPDATE piatti SET Quantita= $qta_upd WHERE Piatto_id = $piatto_id";
	$res = mysql_query($sqlIns) or 
     	die(mysql_error());	
		
}



function checkForDuplicate()
{
	global $g_ordine_id;
	
	if(!isset($_POST['old_ordine_id']))
		return 0;
	
	$post_id= $_POST['old_ordine_id'];
		
		if(!$post_id)
			return 0;
		
		//echo " controllo l'id ". $_POST['old_ordine_id'] . "visto";
		if($_POST["old_ordine_id"])
		{
			$query = "SELECT Prezzo  from testata_ordini WHERE Ordine_id = $post_id ";
			$result = mysql_query($query) or die(mysql_error());
			$prezzo = mysql_fetch_array($result);
			//echo " il prezzo � ". $prezzo[0];
			
			if($prezzo[0]!=0)
			{
				//echo "� un duplicato";
				getMyOrdineId();
				return 1;
			}
		}
		//echo "NON � un duplicato";
		return 0;
}



function getCassaId()
{
	$remote_ip=getenv('REMOTE_ADDR'); 

	
	$query = "SELECT Cassa_id  from cassa WHERE ipaddr = '$remote_ip' ";
	$result = mysql_query($query) or die(mysql_error());
	$cassa = mysql_fetch_array($result);
	
	$CassaId = $cassa[0];
	
	if(!$CassaId)
		return 	0;
	else
		return $CassaId;
}



function getMyOrdineId()
{
	global $g_ordine_id;
	$cassa_id      =getCassaId();
	$data_ordine   =date("Y-m-d :H:i:s");

	

		$query = "LOCK TABLES testata_ordini  WRITE ";
		$result = mysql_query($query) or die(mysql_error());		
		
	 	$query = "SELECT MAX(Ordine_id)  from testata_ordini WHERE Cassa_id = $cassa_id ";
		$result = mysql_query($query) or die(mysql_error());
		$id = mysql_fetch_array($result);
		
		/* craete element if not exist (the first access) */
		if($id[0])
		{
	 		$query = "SELECT Prezzo  from testata_ordini WHERE Ordine_id = $id[0] ";
	 
			$result = mysql_query($query) or die(mysql_error());
			$prezzo = mysql_fetch_array($result);
			
			/* Tnis order is not used, use it now */
			if ($prezzo[0]==0)
			{
				$g_ordine_id=$id[0];
			}
			else
			{
				$sqlIns ="INSERT INTO testata_ordini (Cassa_id, Data, Prezzo ) 
	                VALUES('$cassa_id','$data_ordine',0)";

				$res = mysql_query($sqlIns) or die(mysql_error());

	 			$query = "SELECT MAX(Ordine_id)  from testata_ordini WHERE Cassa_id = $cassa_id ";
				$result = mysql_query($query) or die(mysql_error());
				$id = mysql_fetch_array($result);				
				
				$g_ordine_id=$id[0];			
			}
		}	
		else
		{		
				/* now create  a new ordine in testata ordini table */
				$sqlIns ="INSERT INTO testata_ordini (Cassa_id, Data, Prezzo ) 
	                VALUES('$cassa_id','$data_ordine',0)";

				$res = mysql_query($sqlIns) or 
     		die(mysql_error());
				
	 			$query = "SELECT MAX(Ordine_id)  from testata_ordini WHERE Cassa_id = $cassa_id ";
				$result = mysql_query($query) or die(mysql_error());
				$id = mysql_fetch_array($result);				
				
				$g_ordine_id=$id[0];				
		}

		$query = "UNLOCK TABLES";
		$result = mysql_query($query) or die(mysql_error());
							
}


function insertOrdineInDb()
{
	global $g_ordine_id;
	$cassa_id      =getCassaId();
	$data_ordine   =date("Y-m-d :H:i:s");
	$prezzo_ordine =$_POST['ordine_prezTot'];
	$g_coperti=$_POST['coperti'];

	$sqlIns ="UPDATE testata_ordini SET Cassa_id=$cassa_id, Data='$data_ordine', Prezzo=$prezzo_ordine, Coperti='$g_coperti' WHERE Ordine_id = $g_ordine_id";						

	$res = mysql_query($sqlIns) or 
     	die(mysql_error());
			
	/* Now get the Order Id from Testata Ordini
	   in order to use it in righe ordini */			
 		$query = "SELECT MAX(Ordine_id)  from testata_ordini WHERE Cassa_id = $cassa_id ";
		$result = mysql_query($query) or die(mysql_error());
		$ordine = mysql_fetch_array($result);
		$ordine_id = $ordine[0];
		
		/*now fill the Table Riga_ordini*/
		$numPiatti= $_POST['ordine_len'];	

		for($i=0; $i<$numPiatti; $i++)
		{
			$piatto_id  = 'piatto_id'.$i;
			$descrizione ="piatto_desc".$i;
			$quantita ="piatto_qta".$i;
			$prezzo ="piatto_prezRig".$i;

			$piatto_id= $_POST["$piatto_id"];
			$descrizione= $_POST["$descrizione"];
			$quantita= $_POST["$quantita"];
			$prezzo= $_POST["$prezzo"];
				
			$sqlIns ="INSERT INTO righe_ordine (Ordine_id, Piatto_id, Descrizione, Quantita, Prezzo ) 
	                      VALUES('$ordine_id','$piatto_id','$descrizione','$quantita', '$prezzo')";

			$res = mysql_query($sqlIns) or 
     		die(mysql_error());
				
			/* Update the Num of piatto */		
			updateQtainDb( $piatto_id, $quantita);		
		}
		
		
		
}

function getDataFromPost()
{
global $g_ordPiatto;
global $g_ordPiattoIndex;

	if(!isset($_POST['ordine_len']))
		$ordine_len = 0;
	else
		$ordine_len =$_POST['ordine_len'];
	
	if( $ordine_len>0)
	{
		insertOrdineInDb();
		getMyOrdineId();		
	}
}

	if(!checkForDuplicate())
	{
		getMyOrdineId();
		getDataFromPost();
	}
  getPiatti_fromDb();


?>
<html>
<head>
<title>24 Ore Salce - Gestione ordini</title>
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


function FillDataInMask()
{
	primi = new Array();
	secondi = new Array();
	contorni = new Array();
	bibite = new Array();
	dessert = new Array();
	piatti = new Array(0,primi,secondi,contorni,bibite,dessert);
	
	//retVal=window.confirm(piatti.length);
	/* creo vettore per i dati dei primi */
<?php	
	global $g_lenPiatto;
	
	echo 'for(i=1; i<= ' . $g_lenPiatto[1] . ';i++)';
?>	
	{
		primi[i]=new Array();
	}	
		/* creo vettore per i dati dei secondi */
<?php	
	global $g_lenPiatto;
	
	echo 'for(i=1; i<= ' . $g_lenPiatto[2] . ';i++)';
?>	
	{
		secondi[i]=new Array();
	}
	
	/* creo vettore per i dati dei contorni */
<?php	
	global $g_lenPiatto;
	
	echo 'for(i=1; i<= ' . $g_lenPiatto[3] . ';i++)';
?>	
	{
		contorni[i]=new Array();
	}
	
		/* creo vettore per i dati dei contorni */
<?php	
	global $g_lenPiatto;
	
	echo 'for(i=1; i<= ' . $g_lenPiatto[4] . ';i++)';
?>	
	{
		bibite[i]=new Array();
	}
	
	/* creo vettore per i dati dei contorni */
<?php	
	global $g_lenPiatto;
	
	echo 'for(i=1; i<= ' . $g_lenPiatto[5] . ';i++)';
?>	
	{
		dessert[i]=new Array();
	}


<?php

	$i=1;
	$j=1;
	global $g_piatto;
	
	/* get DB data in jscript */
	for($j=1; $j<=5; $j++)
	{
		$i=1;
		while($g_piatto[$j][$i])
		{
			$piatto_id 	    = $g_piatto[$j][$i]['Piatto_id'];
			$nome			= $g_piatto[$j][$i]['Nome'];
			$tipo			= $g_piatto[$j][$i]['Tipo_id'];
			$prezzo			= $g_piatto[$j][$i]['Prezzo'];
			$qta			= $g_piatto[$j][$i]['Quantita'];
			$qta_min		= $g_piatto[$j][$i]['Qta_min'];
			$fastButId	    = $g_piatto[$j][$i]['Fast_but_id'];
			echo 'piatti[' . $j .'][' .$i . '][1]=' . $piatto_id . ';';
			print("\n");
			echo 'piatti[' . $j .'][' .$i . '][2]=' .  '"' .$nome . '"' .';';
			print("\n");
			echo 'piatti[' . $j .'][' .$i . '][3]=' . $tipo . ';';
			print("\n");
			echo 'piatti[' . $j .'][' .$i . '][4]=' . 'toCurrency(' .$prezzo . ');';
			print("\n");
			echo 'piatti[' . $j .'][' .$i . '][5]=' . $qta . ';';
			print("\n");
			echo 'piatti[' . $j .'][' .$i . '][6]=' . $qta_min . ';';
			print("\n");
			echo 'piatti[' . $j .'][' .$i . '][7]=' . $fastButId . ';';
			print("\n");
			$i++;
		}
	}
?>	

	var restPrezTot_obj=document.getElementById("prezzo_tot");
	var restPrezTot="";
	
	restPrezTot=readCookie("resto");
    restPrezTot_obj.value=restPrezTot;
	eraseCookie("resto");
		
		
		
	updateonSelect("primo");
	updateonSelect("secondo");
	updateonSelect("contorno");
	updateonSelect("bibite");
	updateonSelect("dessert");
	
	updateData()	
	
}


function checkForQuantity(piatto)
{
	switch(piatto)
	{
		case "primo":
		{
			var select_obj=document.getElementById("sel_pri");
			var allarm_obj=document.getElementById("piattiAllarm_pri");
			
			if(piatti[1][select_obj.selectedIndex+1][5] < piatti[1][select_obj.selectedIndex+1][6])
				allarm_obj.className = "gestOrdini_piatti_allarm_show";
			else
				allarm_obj.className = "gestOrdini_piatti_allarm_hide";
		}
		break;
		
		case "secondo":
		{
			var select_obj=document.getElementById("sel_sec");
			var allarm_obj=document.getElementById("piattiAllarm_sec");
			
			if(piatti[2][select_obj.selectedIndex+1][5] < piatti[2][select_obj.selectedIndex+1][6])
				allarm_obj.className = "gestOrdini_piatti_allarm_show";
			else
				allarm_obj.className = "gestOrdini_piatti_allarm_hide";
		}
		break;		
		case "contorno":
		{
			var select_obj=document.getElementById("sel_cont");
			var allarm_obj=document.getElementById("piattiAllarm_cont");
			
			if(piatti[3][select_obj.selectedIndex+1][5] < piatti[3][select_obj.selectedIndex+1][6])
				allarm_obj.className = "gestOrdini_piatti_allarm_show";
			else
				allarm_obj.className = "gestOrdini_piatti_allarm_hide";
		}
		break;
		case "bibite":
		{
			var select_obj=document.getElementById("sel_bib");
			var allarm_obj=document.getElementById("piattiAllarm_bib");
			
			if(piatti[4][select_obj.selectedIndex+1][5] < piatti[4][select_obj.selectedIndex+1][6])
				allarm_obj.className = "gestOrdini_piatti_allarm_show";
			else
				allarm_obj.className = "gestOrdini_piatti_allarm_hide";
		}	
		break;
		case "dessert":
		{
			var select_obj=document.getElementById("sel_des");
			var allarm_obj=document.getElementById("piattiAllarm_des");
			
			if(piatti[5][select_obj.selectedIndex+1][5] < piatti[5][select_obj.selectedIndex+1][6])
				allarm_obj.className = "gestOrdini_piatti_allarm_show";
			else
				allarm_obj.className = "gestOrdini_piatti_allarm_hide";
		}	
		break;		
		
		default:
			return;
	}	
}



function updateonSelect(piatto)
{
	
	switch (piatto)
	{
		case "primo":
		{
			var select_obj=document.getElementById("sel_pri");
			//retVal=window.confirm(select_obj.selectedIndex);
			var prez_obj=document.getElementById("tex_priPrez");
			prez_obj.value = piatti[1][select_obj.selectedIndex+1][4];
			var rim_obj=document.getElementById("tex_priRim");
			rim_obj.value = piatti[1][select_obj.selectedIndex+1][5];
			var id_obj=document.getElementById("hid_priId");
			id_obj.value = piatti[1][select_obj.selectedIndex+1][1];
			//retVal=window.confirm(id_obj.value);
			
		}
		break;
		case "secondo":
		{
			var select_obj=document.getElementById("sel_sec");
			//retVal=window.confirm(select_obj.selectedIndex);
			var prez_obj=document.getElementById("tex_secPrez");
			prez_obj.value = piatti[2][select_obj.selectedIndex+1][4];
			var rim_obj=document.getElementById("tex_secRim");
			rim_obj.value = piatti[2][select_obj.selectedIndex+1][5];
		}
		break;		
		case "contorno":
		{
			var select_obj=document.getElementById("sel_cont");
			//retVal=window.confirm(select_obj.selectedIndex);
			var prez_obj=document.getElementById("tex_contPrez");
			prez_obj.value = piatti[3][select_obj.selectedIndex+1][4];
			var rim_obj=document.getElementById("tex_contRim");
			rim_obj.value = piatti[3][select_obj.selectedIndex+1][5];
		}		
		break;
		case "bibite":
		{
			var select_obj=document.getElementById("sel_bib");
			//retVal=window.confirm(select_obj.selectedIndex);
			var prez_obj=document.getElementById("tex_bibPrez");
			prez_obj.value = piatti[4][select_obj.selectedIndex+1][4];
			var rim_obj=document.getElementById("tex_bibRim");
			rim_obj.value = piatti[4][select_obj.selectedIndex+1][5];
		}		
		break;
		case "dessert":
		{
			var select_obj=document.getElementById("sel_des");
			//retVal=window.confirm(select_obj.selectedIndex);
			var prez_obj=document.getElementById("tex_desPrez");
			prez_obj.value = piatti[5][select_obj.selectedIndex+1][4];
			var rim_obj=document.getElementById("tex_desRim");
			rim_obj.value = piatti[5][select_obj.selectedIndex+1][5];
		}		
		break;		
		
		default:
			return;
	}
	calcolaPrezzo(piatto);
	checkForQuantity(piatto);
}



function checkNum(num_obj)
{
	if(num_obj.value==0)
	{
		num_obj.value=1;
	}
}

function calcolaPrezzo(piatto)
{
	switch (piatto)
	{
		case "primo":
		{
			var select_obj=document.getElementById("sel_pri");
			var num_obj =document.getElementById("tex_priNum");
			checkNum(num_obj);
			
			var prezzo=num_obj.value;
			var prez_obj=document.getElementById("tex_priPrez");
			
			var prez_tot= prezzo * piatti[1][select_obj.selectedIndex+1][4];
			prez_tot = toCurrency(prez_tot);
			prez_obj.value = prez_tot;
		}
		break;
		case "secondo":
		{
			//retVal=window.confirm(obj.value);
			var select_obj=document.getElementById("sel_sec");
			var num_obj =document.getElementById("tex_secNum");
			checkNum(num_obj);
			var prezzo=num_obj.value;
			var prez_obj=document.getElementById("tex_secPrez");
			var prez_tot= prezzo * piatti[2][select_obj.selectedIndex+1][4];
			prez_tot = toCurrency(prez_tot);
			prez_obj.value = prez_tot;
		}
		break;		
		case "contorno":
		{
			//retVal=window.confirm(obj.value);
			var select_obj=document.getElementById("sel_cont");
			var num_obj =document.getElementById("tex_contNum");
			checkNum(num_obj);
			var prezzo=num_obj.value;
			var prez_obj=document.getElementById("tex_contPrez");
			var prez_tot= prezzo * piatti[3][select_obj.selectedIndex+1][4];
			prez_tot = toCurrency(prez_tot);
			prez_obj.value = prez_tot;
		}		
		break;
		case "bibite":
		{
			//retVal=window.confirm(obj.value);
			var select_obj=document.getElementById("sel_bib");
			var num_obj =document.getElementById("tex_bibNum");
			checkNum(num_obj);
			var prezzo=num_obj.value;
			var prez_obj=document.getElementById("tex_bibPrez");
			var prez_tot= prezzo * piatti[4][select_obj.selectedIndex+1][4];
			prez_tot = toCurrency(prez_tot);
			prez_obj.value = prez_tot;
		}		
		break;
		case "dessert":
		{
			//retVal=window.confirm(obj.value);
			var select_obj=document.getElementById("sel_des");
			var num_obj =document.getElementById("tex_desNum");
			checkNum(num_obj);
			var prezzo=num_obj.value;
			var prez_obj=document.getElementById("tex_desPrez");
			var prez_tot= prezzo * piatti[5][select_obj.selectedIndex+1][4];
			prez_tot = toCurrency(prez_tot);
			prez_obj.value = prez_tot;
		}		
		break;		
		
		default:
			return;
	}

	  
}



function selectedPiatto(obj)
{
	alert(obj.id);
}

Righe_Ordini = Array();


function showAlert()
 {
 
 var i=0;
 
 i= this.id
 this.className ="piatto_div_canc";
 num  = Righe_Ordini[i][6]
 name = Righe_Ordini[i][1];
 messaggio = "Vuoi Cancellare  " + num  + " " + name + " dall'ordine ?";


 
 retVal=window.confirm(messaggio);
	
	if(retVal)
	{
	this.parentNode.removeChild(this);
	Righe_Ordini.splice(i,1);
	
	insertPiattoInOrdine("salta");
	}	
 }





function insertCoperti(nCoperti)
{
	/* update data*/
	mydiv =document.getElementById("logo_coperti");
	string =  "COPERTI: N. " + nCoperti;
		
	mydiv.innerHTML=string; 	
}


function insertFastButtonInOrdine(piatto,fastButId)
{
	var ordini= new Array();
    var prezzo_ordine = 0;
	var prez_tot_obj= document.getElementById("prezzo_tot");
	var prez_cont_obj= document.getElementById("contanti");
	var prez_rest_obj= document.getElementById("resto");
	var piattoFast=-1;
	
	
	if(piatto!="salta")
	{
	switch (piatto)
	{
		case "primo":
		{	
			var select_obj=document.getElementById("sel_pri");
			var note_obj=document.getElementById("tex_priNote");
  		    var num_obj= document.getElementById("tex_priNum");
			var prez_obj=document.getElementById("tex_priPrez")
			
			
			for(i=1;i<piatti[1].length;i++)
			{
				if(fastButId==piatti[1][i][7])
				{
					piattoFast=i;
					break;
				}
			}
			
			if(piattoFast==-1)
				return;
			
			if(piatti[1][piattoFast][5]<=0)
			{
				alert_string = piatti[1][piattoFast][2] + " esauriti";
				alert(alert_string);
				return;
			}
			
			
			ordini[0]= piatti[1][piattoFast][1];
			ordini[1]= piatti[1][piattoFast][2];
			ordini[2]= piatti[1][piattoFast][3];
			ordini[4]= piatti[1][piattoFast][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[1][piattoFast][4] * num_obj.value;			
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[1][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		case "secondo":
		{	
			var select_obj=document.getElementById("sel_sec");
			var note_obj=document.getElementById("tex_secNote");
			var num_obj= document.getElementById("tex_secNum");
			var prez_obj=document.getElementById("tex_secPrez")
			
			for(i=1;i<piatti[2].length;i++)
			{
			//alert_string = "Fast Butt associato: " + piatti[1][i][7] + " " + piattoFast;
			//alert(alert_string);
				if(fastButId==piatti[2][i][7])
				{
					piattoFast=i;
					break;
				}
			}
			
			//alert_string = "Fast Butt associato: " + piatti[1][piattoFast][7] + " " + piattoFast;
			//alert(alert_string);
			
			if(piattoFast==-1)
				return;
			
			if(piatti[2][piattoFast][5]<=0)
			{
				alert_string = piatti[2][piattoFast][2] + " esauriti";
				alert(alert_string);
				return;
			}
			
			
			ordini[0]= piatti[2][piattoFast][1];
			ordini[1]= piatti[2][piattoFast][2];
			ordini[2]= piatti[2][piattoFast][3];
			ordini[4]= piatti[2][piattoFast][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[2][piattoFast][4] * num_obj.value;
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[2][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		case "contorno":
		{	
			var select_obj=document.getElementById("sel_cont");
			var note_obj=document.getElementById("tex_contNote");
			var num_obj= document.getElementById("tex_contNum");
			var prez_obj=document.getElementById("tex_contPrez");
			
			for(i=1;i<piatti[3].length;i++)
			{
				if(fastButId==piatti[3][i][7])
				{
					piattoFast=i;
					break;
				}
			}
			
			if(piattoFast==-1)
				return;
			
			if(piatti[3][piattoFast][5]<=0)
			{
				alert_string = piatti[3][piattoFast][2] + " esauriti";
				alert(alert_string);
				return;
			}
			
			
			ordini[0]= piatti[3][piattoFast][1];
			ordini[1]= piatti[3][piattoFast][2];
			ordini[2]= piatti[3][piattoFast][3];
			ordini[4]= piatti[3][piattoFast][4];
			ordini[5] = "";
			ordini[6] = 1;
			ordini[7] = piatti[3][piattoFast][4] * 1;			
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[3][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		case "bibite":
		{	
			var select_obj=document.getElementById("sel_bib");
			var note_obj=document.getElementById("tex_bibNote");
  		    var num_obj= document.getElementById("tex_bibNum");
			var prez_obj=document.getElementById("tex_bibPrez");
			
			for(i=1;i<piatti[4].length;i++)
			{
				if(fastButId==piatti[4][i][7])
				{
					piattoFast=i;
					break;
				}
			}
			
			if(piattoFast==-1)
				return;
			
			if(piatti[4][piattoFast][5]<=0)
			{
				alert_string = piatti[4][piattoFast][2] + " esauriti";
				alert(alert_string);
				return;
			}
			
			ordini[0]= piatti[4][piattoFast][1];
			ordini[1]= piatti[4][piattoFast][2];
			ordini[2]= piatti[4][piattoFast][3];
			ordini[4]= piatti[4][piattoFast][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[4][piattoFast][4] * num_obj.value;			
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[4][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		case "dessert":
		{	
			var select_obj=document.getElementById("sel_des");
			var note_obj=document.getElementById("tex_desNote");
			var num_obj= document.getElementById("tex_desNum");
			var prez_obj=document.getElementById("tex_desPrez");
			
			for(i=1;i<piatti[5].length;i++)
			{
				if(fastButId==piatti[5][i][7])
				{
					piattoFast=i;
					break;
				}
			}
			
			if(piattoFast==-1)
				return;
			
			if(piatti[5][piattoFast][5]<=0)
			{
				alert_string = piatti[5][piattoFast][2] + " esauriti";
				alert(alert_string);
				return;
			}
			
			ordini[0]= piatti[5][piattoFast][1];
			ordini[1]= piatti[5][piattoFast][2];
			ordini[2]= piatti[5][piattoFast][3];
			ordini[4]= piatti[5][piattoFast][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[5][piattoFast][4] * num_obj.value;			
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[5][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		default:
			return;											
	}
	
	var i;
	
	/* Check for Same Order */
	for(i=0; i<Righe_Ordini.length; i++)
	{
		if((Righe_Ordini[i][1]==ordini[1]) && (Righe_Ordini[i][5]==ordini[5]) )
		{	
			Righe_Ordini[i][6]=ordini[6]*1+Righe_Ordini[i][6]*1;
			Righe_Ordini[i][7]=Righe_Ordini[i][6] * ordini[4];
			break;
		}
	}
	
	if(i >= Righe_Ordini.length )
		Righe_Ordini.push(ordini);
	
	}	
	myparent=document.getElementById("ordine");
	/* Reset all the order and create from scratch appending the new one */	
	while (myparent.firstChild) 
	{
			if(myparent.lastChild)
			{
				if(myparent.lastChild.id== "logo")
					break;
				myparent.removeChild(myparent.lastChild);
			}
	}		
	/* salva i dati nei campi nascosti */
	/*Reset all the order and create from scatch appendind the new one*/
	myform =document.getElementById("form_ordine_ctrl");
		/*Reset all the hidden data and create from scatch appendind the new one */
		while (myform.firstChild) 
		{
			if(myform.lastChild)
			{
				if(myform.lastChild.name== "coperti")
					break;
					
				myform.removeChild(myform.lastChild);
			}
	}	
	
	for(i=0; i<Righe_Ordini.length; i++)
	{
		/* visualizza l'ordine nei div*/
		piatto_div= document.createElement("div");
		
		piatto_div.id=i;
		myparent.appendChild(piatto_div);
		
		row1_div= document.createElement("div");
		piatto_div.appendChild(row1_div);
		
		row1_span1= document.createElement("div");
		row1_span2= document.createElement("div");
		row1_div.appendChild(row1_span1);
		row1_div.appendChild(row1_span2);		
		
		row2_div= document.createElement("div");
		piatto_div.appendChild(row2_div);
		
		row2_span1= document.createElement("div");
		row2_span2= document.createElement("div");
		row2_div.appendChild(row2_span1);
		row2_div.appendChild(row2_span2);
		
		/* add a style class to each span*/
		row1_div.className="row_div";
		row2_div.className="row_div";
		piatto_div.className="piatto_div";
		
		piatto_div.ondblclick=showAlert;
		
		row1_span1.className="span_nome";
		row1_span2.className="span_prezzo";
		row2_span1.className="span_num";
		row2_span2.className="span_prezRow";
		
		row1_span1.innerHTML = Righe_Ordini[i][1]+ " " + Righe_Ordini[i][5] + " ";
		row1_span2.innerHTML = Righe_Ordini[i][4] + " �";
		row2_span1.innerHTML = "x"  +Righe_Ordini[i][6];
		row2_span2.innerHTML = toCurrency(Righe_Ordini[i][7]) + " �";
		
	    /* make some space from row to row */
		/* Visualizza il totale e salva il totale */
		blank_div=  document.createElement("div");
		myparent.appendChild(blank_div);
		blank_div.className = "gestOrdini_ordine_blank_div";
		blank_div.innerHTML = "&nbsp";		
		
		
		/* salva i dati nei campi nascosti */
		
		/* salva i piatti Id */
		hid_piatto_id= document.createElement("input");
		nome = "piatto_id" + (i);
		hid_piatto_id.name= nome;
		hid_piatto_id.type= "hidden";
		hid_piatto_id.value =  Righe_Ordini[i][0];
		myform.appendChild(hid_piatto_id);				
		
		/* salva la descrizione */
		hid_piatto_desc= document.createElement("input");
		nome = "piatto_desc" + (i);
		hid_piatto_desc.name= nome;
		hid_piatto_desc.type= "hidden";
		hid_piatto_desc.value =  Righe_Ordini[i][5];
		myform.appendChild(hid_piatto_desc);
		
		/* salva la quantit� */
		hid_piatto_qta= document.createElement("input");
		nome = "piatto_qta" + (i);
		hid_piatto_qta.name= nome;
		hid_piatto_qta.type= "hidden";
		hid_piatto_qta.value =  Righe_Ordini[i][6];
		myform.appendChild(hid_piatto_qta);
		
		/* salva i prezzo riga */
		hid_piatto_prezRig= document.createElement("input");
		nome = "piatto_prezRig" + (i);
		hid_piatto_prezRig.name= nome;
		hid_piatto_prezRig.type= "hidden";
		hid_piatto_prezRig.value =  Righe_Ordini[i][7];
		myform.appendChild(hid_piatto_prezRig);								
		
		
	/* calcolo il prezzo totale */
	prezzo_ordine += Righe_Ordini[i][7];
	}

	/* Visualizza il totale e salva il totale */
	blank_tab=  document.createElement("table");
	blank_tab.className = "gestOrdini_ordine_blank_tab";
	tblBody = document.createElement("tbody");

	blank_tr=document.createElement("tr");
	
	tot_td_tit=  document.createElement("td");
	//tot_td_tit.className="tot_div";
	
	tot_td_tit_text = document.createTextNode("TOTALE");
    tot_td_tit.appendChild(tot_td_tit_text);
    blank_tr.appendChild(tot_td_tit);

	
	tot_td_prez = document.createElement("td");
	tot_td_prez.className=("gestOrdini_ordine_td_prez");
	//tot_td_prez.className="gestOrdini_ordine_td_prez";
	
	tot_td_prez_text = document.createTextNode(toCurrency(prezzo_ordine) + " �");
    tot_td_prez.appendChild(tot_td_prez_text);
    blank_tr.appendChild(tot_td_prez);

	tblBody.appendChild(blank_tr);
	blank_tab.appendChild(tblBody);
	myparent.appendChild(blank_tab);
	
	
	hid_OrdinePrezTot= document.createElement("input");
	nome = "ordine_prezTot";
	hid_OrdinePrezTot.name= nome;
	hid_OrdinePrezTot.type= "hidden";
	hid_OrdinePrezTot.value =  prezzo_ordine;
	myform.appendChild(hid_OrdinePrezTot);	
	
	/* save the len of the array */
	hid_OrdineLen= document.createElement("input");
	nome = "ordine_len";
	hid_OrdineLen.name= nome;
	hid_OrdineLen.type= "hidden";
	hid_OrdineLen.value =  Righe_Ordini.length;
	myform.appendChild(hid_OrdineLen);
	
	/* update data*/
	mydiv =document.getElementById("plogoData");
	string =  "DATA: " + getData_string();
		
	mydiv.innerHTML=string;
	prez_tot_obj.value=toCurrency(prezzo_ordine);
	prez_rest_obj.value="";
	
	prez_rest_obj.value="";
	prez_cont_obj.value="";
	
	createCookie("resto",prez_tot_obj.value,500);
}






function insertPiattoInOrdine(piatto)
{
	var ordini= new Array();
    var prezzo_ordine = 0;
	var prez_tot_obj= document.getElementById("prezzo_tot");
	var prez_cont_obj= document.getElementById("contanti");
	var prez_rest_obj= document.getElementById("resto");
	
	
	if(piatto!="salta")
	{
	switch (piatto)
	{
		case "primo":
		{	
			var select_obj=document.getElementById("sel_pri");
			var note_obj=document.getElementById("tex_priNote");
  		    var num_obj= document.getElementById("tex_priNum");
			var prez_obj=document.getElementById("tex_priPrez")
			
			if(num_obj.value > piatti[1][select_obj.selectedIndex+1][5])
			{

				if(piatti[1][select_obj.selectedIndex+1][5] == 0)
					alert_string = piatti[1][select_obj.selectedIndex+1][2] + " esauriti";
				else
					alert_string = piatti[1][select_obj.selectedIndex+1][2] + " rimasti non sufficienti \n" + "controlla il numero selezionato";
				
				alert(alert_string);
				return;
			}
			
			
			ordini[0]= piatti[1][select_obj.selectedIndex+1][1];
			ordini[1]= piatti[1][select_obj.selectedIndex+1][2];
			ordini[2]= piatti[1][select_obj.selectedIndex+1][3];
			ordini[4]= piatti[1][select_obj.selectedIndex+1][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[1][select_obj.selectedIndex+1][4] * num_obj.value;
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[1][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		case "secondo":
		{	
			var select_obj=document.getElementById("sel_sec");
			var note_obj=document.getElementById("tex_secNote");
  		    var num_obj= document.getElementById("tex_secNum");
			var prez_obj=document.getElementById("tex_secPrez");
			
			if(num_obj.value > piatti[2][select_obj.selectedIndex+1][5])
			{
				
				if(piatti[2][select_obj.selectedIndex+1][5] == 0)
					alert_string = piatti[2][select_obj.selectedIndex+1][2] + " esauriti";
				else
					alert_string = piatti[2][select_obj.selectedIndex+1][2] + " rimasti non sufficienti \n" + "controlla il numero selezionato";
							
				alert(alert_string);
				return;
			}
			ordini[0]= piatti[2][select_obj.selectedIndex+1][1];
			ordini[1]= piatti[2][select_obj.selectedIndex+1][2];
			ordini[2]= piatti[2][select_obj.selectedIndex+1][3];
			ordini[4]= piatti[2][select_obj.selectedIndex+1][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[2][select_obj.selectedIndex+1][4] * num_obj.value;	
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[2][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		case "contorno":
		{	
			var select_obj=document.getElementById("sel_cont");
			var note_obj=document.getElementById("tex_contNote");
  		    var num_obj= document.getElementById("tex_contNum");
			var prez_obj=document.getElementById("tex_contPrez");
			
			if(num_obj.value > piatti[3][select_obj.selectedIndex+1][5])
			{
				
				if(piatti[3][select_obj.selectedIndex+1][5] == 0)
					alert_string = piatti[3][select_obj.selectedIndex+1][2] + " esauriti";
				else
					alert_string = piatti[3][select_obj.selectedIndex+1][2] + " rimasti non sufficienti \n" + "controlla il numero selezionato";
				
				alert(alert_string);
				return;
			}
			ordini[0]= piatti[3][select_obj.selectedIndex+1][1];
			ordini[1]= piatti[3][select_obj.selectedIndex+1][2];
			ordini[2]= piatti[3][select_obj.selectedIndex+1][3];
			ordini[4]= piatti[3][select_obj.selectedIndex+1][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[3][select_obj.selectedIndex+1][4] * num_obj.value;			
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[3][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		case "bibite":
		{	
			var select_obj=document.getElementById("sel_bib");
			var note_obj=document.getElementById("tex_bibNote");
  		    var num_obj= document.getElementById("tex_bibNum");
			var prez_obj=document.getElementById("tex_bibPrez");
			
			if(num_obj.value > piatti[4][select_obj.selectedIndex+1][5])
			{
				if(piatti[4][select_obj.selectedIndex+1][5] == 0)
					alert_string = piatti[4][select_obj.selectedIndex+1][2] + " esauriti";
				else
					alert_string = piatti[4][select_obj.selectedIndex+1][2] + " rimasti non sufficienti \n" + "controlla il numero selezionato";
				
				alert(alert_string);
				return;
			}
			ordini[0]= piatti[4][select_obj.selectedIndex+1][1];
			ordini[1]= piatti[4][select_obj.selectedIndex+1][2];
			ordini[2]= piatti[4][select_obj.selectedIndex+1][3];
			ordini[4]= piatti[4][select_obj.selectedIndex+1][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[4][select_obj.selectedIndex+1][4] * num_obj.value;			
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[4][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		case "dessert":
		{	
			var select_obj=document.getElementById("sel_des");
			var note_obj=document.getElementById("tex_desNote");
  		    var num_obj= document.getElementById("tex_desNum");
			var prez_obj=document.getElementById("tex_desPrez");
			
			if(num_obj.value > piatti[5][select_obj.selectedIndex+1][5])
			{
				if(piatti[5][select_obj.selectedIndex+1][5] == 0)
					alert_string = piatti[5][select_obj.selectedIndex+1][2] + " esauriti";
				else
					alert_string = piatti[5][select_obj.selectedIndex+1][2] + " rimasti non sufficienti \n" + "controlla il numero selezionato";
				
				alert(alert_string);
				return;
			}
			ordini[0]= piatti[5][select_obj.selectedIndex+1][1];
			ordini[1]= piatti[5][select_obj.selectedIndex+1][2];
			ordini[2]= piatti[5][select_obj.selectedIndex+1][3];
			ordini[4]= piatti[5][select_obj.selectedIndex+1][4];
			ordini[5] = note_obj.value;
			ordini[6] = num_obj.value;
			ordini[7] = piatti[5][select_obj.selectedIndex+1][4] * num_obj.value;			
			
			num_obj.value=1;
			note_obj.value="";
			prez_obj.value=piatti[5][select_obj.selectedIndex+1][4] * 1;
		}
		break;
		
		default:
			return;											
	}
	
	var i;
	
	/* Check for Same Order */
	for(i=0; i<Righe_Ordini.length; i++)
	{
		if((Righe_Ordini[i][1]==ordini[1]) && (Righe_Ordini[i][5]==ordini[5]) )
		{	
			Righe_Ordini[i][6]=ordini[6]*1+Righe_Ordini[i][6]*1;
			Righe_Ordini[i][7]=Righe_Ordini[i][6] * ordini[4];
			break;
		}
	}
	
	if(i >= Righe_Ordini.length )
		Righe_Ordini.push(ordini);
	
	}	
	myparent=document.getElementById("ordine");
	/* Reset all the order and create from scratch appending the new one */	
	while (myparent.firstChild) 
	{
			if(myparent.lastChild)
			{
				if(myparent.lastChild.id== "logo")
					break;
				myparent.removeChild(myparent.lastChild);
			}
	}		
	/* salva i dati nei campi nascosti */
	/*Reset all the order and create from scatch appendind the new one*/
	myform =document.getElementById("form_ordine_ctrl");
		/*Reset all the hidden data and create from scatch appendind the new one */
		while (myform.firstChild) 
		{
			if(myform.lastChild)
			{
				if(myform.lastChild.name== "coperti")
					break;
					
				myform.removeChild(myform.lastChild);
			}
	}	
	
	for(i=0; i<Righe_Ordini.length; i++)
	{
		/* visualizza l'ordine nei div*/
		piatto_div= document.createElement("div");
		
		piatto_div.id=i;
		myparent.appendChild(piatto_div);
		
		row1_div= document.createElement("div");
		piatto_div.appendChild(row1_div);
		
		row1_span1= document.createElement("div");
		row1_span2= document.createElement("div");
		row1_div.appendChild(row1_span1);
		row1_div.appendChild(row1_span2);		
		
		row2_div= document.createElement("div");
		piatto_div.appendChild(row2_div);
		
		row2_span1= document.createElement("div");
		row2_span2= document.createElement("div");
		row2_div.appendChild(row2_span1);
		row2_div.appendChild(row2_span2);
		
		/* add a style class to each span*/
		row1_div.className="row_div";
		row2_div.className="row_div";
		piatto_div.className="piatto_div";
		
		piatto_div.ondblclick=showAlert;
		
		row1_span1.className="span_nome";
		row1_span2.className="span_prezzo";
		row2_span1.className="span_num";
		row2_span2.className="span_prezRow";
		
		row1_span1.innerHTML = Righe_Ordini[i][1]+ " " + Righe_Ordini[i][5] + " ";
		row1_span2.innerHTML = Righe_Ordini[i][4] + " �";
		row2_span1.innerHTML = "x"  +Righe_Ordini[i][6];
		row2_span2.innerHTML = toCurrency(Righe_Ordini[i][7]) + " �";
		
	    /* make some space from row to row */
		/* Visualizza il totale e salva il totale */
		blank_div=  document.createElement("div");
		myparent.appendChild(blank_div);
		blank_div.className = "gestOrdini_ordine_blank_div";
		blank_div.innerHTML = "&nbsp";		
		
		
		/* salva i dati nei campi nascosti */
		
		/* salva i piatti Id */
		hid_piatto_id= document.createElement("input");
		nome = "piatto_id" + (i);
		hid_piatto_id.name= nome;
		hid_piatto_id.type= "hidden";
		hid_piatto_id.value =  Righe_Ordini[i][0];
		myform.appendChild(hid_piatto_id);				
		
		/* salva la descrizione */
		hid_piatto_desc= document.createElement("input");
		nome = "piatto_desc" + (i);
		hid_piatto_desc.name= nome;
		hid_piatto_desc.type= "hidden";
		hid_piatto_desc.value =  Righe_Ordini[i][5];
		myform.appendChild(hid_piatto_desc);
		
		/* salva la quantit� */
		hid_piatto_qta= document.createElement("input");
		nome = "piatto_qta" + (i);
		hid_piatto_qta.name= nome;
		hid_piatto_qta.type= "hidden";
		hid_piatto_qta.value =  Righe_Ordini[i][6];
		myform.appendChild(hid_piatto_qta);
		
		/* salva i prezzo riga */
		hid_piatto_prezRig= document.createElement("input");
		nome = "piatto_prezRig" + (i);
		hid_piatto_prezRig.name= nome;
		hid_piatto_prezRig.type= "hidden";
		hid_piatto_prezRig.value =  Righe_Ordini[i][7];
		myform.appendChild(hid_piatto_prezRig);								
		
		
	/* calcolo il prezzo totale */
	prezzo_ordine += Righe_Ordini[i][7];		
	}

	/* Visualizza il totale e salva il totale */
	blank_tab=  document.createElement("table");
	blank_tab.className = "gestOrdini_ordine_blank_tab";
	tblBody = document.createElement("tbody");

	blank_tr=document.createElement("tr");
	
	tot_td_tit=  document.createElement("td");
	//tot_td_tit.className="tot_div";
	
	tot_td_tit_text = document.createTextNode("TOTALE");
    tot_td_tit.appendChild(tot_td_tit_text);
    blank_tr.appendChild(tot_td_tit);

	
	tot_td_prez = document.createElement("td");
	tot_td_prez.className=("gestOrdini_ordine_td_prez");
	//tot_td_prez.className="gestOrdini_ordine_td_prez";
	
	tot_td_prez_text = document.createTextNode(toCurrency(prezzo_ordine) + " �");
    tot_td_prez.appendChild(tot_td_prez_text);
    blank_tr.appendChild(tot_td_prez);

	tblBody.appendChild(blank_tr);
	blank_tab.appendChild(tblBody);
	myparent.appendChild(blank_tab);
	
	
	hid_OrdinePrezTot= document.createElement("input");
	nome = "ordine_prezTot";
	hid_OrdinePrezTot.name= nome;
	hid_OrdinePrezTot.type= "hidden";
	hid_OrdinePrezTot.value =  prezzo_ordine;
	myform.appendChild(hid_OrdinePrezTot);	
	
	/* save the len of the array */
	hid_OrdineLen= document.createElement("input");
	nome = "ordine_len";
	hid_OrdineLen.name= nome;
	hid_OrdineLen.type= "hidden";
	hid_OrdineLen.value =  Righe_Ordini.length;
	myform.appendChild(hid_OrdineLen);
	
	/* update data*/
	mydiv =document.getElementById("plogoData");
	string =  "DATA: " + getData_string();
		
	mydiv.innerHTML=string;
	prez_tot_obj.value=toCurrency(prezzo_ordine);
	prez_rest_obj.value="";
	prez_cont_obj.value="";
	
	createCookie("resto",prez_tot_obj.value,500);
}






function updateData()
{
	/* update data*/
	mydiv =document.getElementById("plogoData");
	string =  "DATA: " + getData_string();
		
	mydiv.innerHTML=string;

	return true;
}


function CancellaOrdine()
{
var len=Righe_Ordini.length;

	myparent=document.getElementById("ordine");
	/* Reset all the order */	
	while (myparent.firstChild) 
		{
			if(myparent.lastChild)
			{
				if(myparent.lastChild.id== "logo")
					break;
				myparent.removeChild(myparent.lastChild);
			}
	}		
	
	/*Reset all the order */
	myform =document.getElementById("form_ordine_ctrl");
		/*Reset all the hidden data and create from scatch appendind the new one */
		while (myform.firstChild) 
		{
			if(myform.lastChild)
			{
				if(myform.lastChild.name== "coperti")
					break;
				myform.removeChild(myform.lastChild);
			}
	}	
	/* now erase the array*/
		for(i=0; i<len; i++)
		{
			Righe_Ordini.shift();
		}		
}


function getData_string()
{
	var data = new Date();
  var giorno, mese, anno, Hh, Mm;
	
	giorno = data.getDate();
	mese   = data.getMonth()+1;
	anno   = data.getYear()-100 +2000;
	Hh     = data.getHours();
	Mm     = data.getMinutes();
	
	data_String=giorno + "/" + mese + "/" + anno + " " + Hh + ":" + Mm;
	
	return data_String;
}

function StampaOrdine()
{
	var prez_tot_obj= document.getElementById("prezzo_tot");
	createCookie("resto",prez_tot_obj.value,500);
	
	
	updateData();
	
	if(Righe_Ordini.length > 0)
	{
		window.print();
	}
		
	return true;
}


function calcResto()
{
	var prez_tot_obj= document.getElementById("prezzo_tot");
	var prez_cont_obj= document.getElementById("contanti");
	var prez_rest_obj= document.getElementById("resto");
	var fnum=0.0;
	
	fnum=prez_rest_obj.value=prez_cont_obj.value-prez_tot_obj.value;
	
	prez_rest_obj.value=fnum.toFixed(2);
	
	createCookie("resto",prez_tot_obj.value,500);
}

</script>
</head>

<body class="gestOrdini"  onLoad="FillDataInMask()">

<div class="gestOrdini" > 
  <div class="gestOrdini_toolBar"  > 
		<a href="index.htm"> <img src="gfx/home.png"  class="menu_img" title="Torna allo schermata principale"> 
    </a> 
		<a href="lista_ordini.php"> <img src="gfx/printer.png"  class="menu_img" title="Stampa vecchio ordine"> 
    </a> </div>
  
	<div class="gestOrdini_piatti"  >
  	<form action="gest_ordini.php" method="post" id="primo" class="gestOrdini_piatti" >
			<INPUT type="hidden"  name="hid_pri" value="primo"> 
			<INPUT type="hidden"  name="hid_priId" id="hid_priId" > 
    	<table >
      	<tr> 
        	<td width="4%">Pri.</td>
          <td width="30%"> 
						<select  name="sel_pri" id="sel_pri" onchange='updateonSelect("primo")'>
          		<?php getPiatti_select("Primo"); ?>
            </select> </td>
          <td width="5%"> Num.</td>
          <td width="8%">
						<INPUT  type="text" size="3" name="tex_priNum" id="tex_priNum" maxlength="3"  value="1"   onKeyUp="checkIsNum(this,'numeri')" onChange='calcolaPrezzo("primo")'> 
          </td>
          <td width="5%"> Prez.</td>
          <td width="15%"> <INPUT  type="text" name="tex_priPrez" id="tex_priPrez" size="7" readonly="1" > �
          </td>
          <td width="5%"> Note.</td>
          <td> <INPUT type="text" name="tex_priNote" id="tex_priNote" size="20" maxlength="30"> 
          </td>
       	</tr>
		</table>
		<table >
		<tr> 
		<td width="30%"> <img  src="gfx/spaghetti-48x48.png"  class="iconaPiatto"> </td>
     	<td width="4%"> </td>			
     	<td width="5%"> Rim.</td>
     	<td width="8%"> <INPUT id="tex_priRim" type="text" size="3" readonly="1" > </td>	
        <td width="18%"> 
      	<div class="gestOrdini_piatti_allarm_hide" id="piattiAllarm_pri" > 
				<img src="gfx/led_rosso_16x16.png"  align="middle" /> 
              in esaurimento 
				</div>
			</td>   
      <td  align="right"> 
				<INPUT type="button"  id="canc_des"  value ="Cancella" onclick='insertPiattoInOrdine("cancella")'> 
				<INPUT type="button"  id="sub_pri"  value ="Inserisci" onClick='insertPiattoInOrdine("primo")'> 
			</td> 
            <tr>
            <td colspan="6">
            	<button name="primo1" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("primo",1)'>
   					<img  src="gfx/spaghetti-48x48.png"  class="iconaPiattoButt" \>
   					<?php getPiatti_FastButt("Primo",1); ?>
 				</button> 
            	
                <button name="primo2" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("primo",2)'>
   					<img  src="gfx/spaghetti-48x48.png"  class="iconaPiattoButt" \>
   					<?php getPiatti_FastButt("Primo",2); ?>
 				</button> 
                
                <button name="primo3" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("primo",3)'>
   					<img  src="gfx/spaghetti-48x48.png"  class="iconaPiattoButt" \>
   					<?php getPiatti_FastButt("Primo",3); ?>
 				</button> 
            	
                <button name="primo4" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("primo",4)'>
   					<img  src="gfx/spaghetti-48x48.png"  class="iconaPiattoButt" \>
   					<?php getPiatti_FastButt("Primo",4); ?>
 				</button> 
            </td >
            </tr>
		</tr>
	</table>
		</form>
    
		<form action="gest_ordini.php" method="post" id="secondo" class="gestOrdini_piatti">
    	<table >
      	<tr> 
        	<td width="4%">Sec.</td>
          <td width="30%"> 
						<select id="sel_sec"  onchange='updateonSelect("secondo")' >
          		<?php getPiatti_select("Secondo"); ?>
            </select> </td>
          <td width="5%"> Num.</td>
          <td width="8%">
						<INPUT id="tex_secNum" type="text" size="3" maxlength="3"  value="1" onKeyUp="checkIsNum(this,'numeri')" onChange='calcolaPrezzo("secondo")'> 
          </td>
          <td width="5%"> Prez.</td>
          <td width="15%"> <INPUT id="tex_secPrez" type="text" size="7"  readonly="1" > �
          </td>
          <td width="5%"> Note.</td>
          <td> <INPUT id="tex_secNote" type="text" size="20" maxlength="30"> 
          </td>
       	</tr>
		</table>
		<table >
		<tr> 
			<td width="30%"> <img  src="gfx/cutlet-48x48.png"  class="iconaPiatto"> </td>
     	<td width="4%"> </td>			
     	<td width="5%"> Rim.</td>
     	<td width="8%"> <INPUT id="tex_secRim" type="text" size="3" readonly="1" > 
      </td>	
      <td width="18%"> 
      	<div class="gestOrdini_piatti_allarm_hide" id="piattiAllarm_sec"> 
					<img src="gfx/led_rosso_16x16.png" align="middle" class="gestOrdini_piatti_allarm"> 
					in esaurimento 
				</div>
			</td>   
      <td  align="right"> 
				<INPUT type="button"  id="canc_des"  value ="Cancella" onclick='insertPiattoInOrdine("cancella")'> 
				<INPUT type="button"  id="sub_sec"  value ="Inserisci" onclick='insertPiattoInOrdine("secondo")'> 
			</td> 
		</tr>
        <tr>
            <td colspan="6">
            	<button name="secondo1" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("secondo",1)'>
   					<img  src="gfx/cutlet-48x48.png"  class="iconaPiattoButt" \>
   					<?php getPiatti_FastButt("Secondo",1); ?>
 				</button> 
            	
                <button name="secondo2" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("secondo",2)'>
   					<img  src="gfx/cutlet-48x48.png"  class="iconaPiattoButt" \>
   					<?php getPiatti_FastButt("Secondo",2); ?>
 				</button> 
                
                <button name="secondo3" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("secondo",3)'>
   					<img  src="gfx/cutlet-48x48.png"  class="iconaPiattoButt" \>
   					<?php getPiatti_FastButt("Secondo",3); ?>
 				</button> 
            	
                <button name="secondo4" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("secondo",4)'>
   					<img  src="gfx/cutlet-48x48.png"  class="iconaPiattoButt" \>
   					<?php getPiatti_FastButt("Secondo",4); ?>
 				</button> 
            </td >
            </tr>
	</table>
    </form>
		
		<form action="gest_ordini.php" method="post" id="contorno" class="gestOrdini_piatti">
    	<table >
      	<tr> 
        	<td width="4%">Con.</td>
          <td width="30%"> 
						<select id="sel_cont" onchange='updateonSelect("contorno")'>
          		<?php getPiatti_select("Contorno"); ?>
            </select> </td>
          <td width="5%"> Num.</td>
          <td width="8%">
						<INPUT id="tex_contNum" type="text" size="3" maxlength="3"  value="1" onKeyUp="checkIsNum(this,'numeri')" onChange='calcolaPrezzo("contorno")'> 
          </td>
          <td width="5%"> Prez.</td>
          <td width="15%"> <INPUT id="tex_contPrez" type="text" size="7"  readonly="1" > �
          </td>
          <td width="5%"> Note.</td>
          <td > <INPUT id="tex_contNote" type="text" size="20" maxlength="30"> 
          </td>
       	</tr>
		</table>
		<table >
		<tr> 
			<td width="30%"> <img src="gfx/sliced-bread-48x48.png"  class="iconaPiatto"> </td>
     	<td width="4%"> </td>			
     	<td width="5%"> Rim.</td>
     	<td width="8%"> <INPUT id="tex_contRim" type="text" size="3" readonly="1" > 
      </td>	
      <td width="18%"> 
      	<div class="gestOrdini_piatti_allarm_hide" id="piattiAllarm_cont"> 
					<img src="gfx/led_rosso_16x16.png" align="middle" class="gestOrdini_piatti_allarm"> 
					in esaurimento 
				</div>
			</td>   
     <td  align="right"> 
				<INPUT type="button"  id="canc_des"  value ="Cancella"  onclick='insertPiattoInOrdine("cancella")'> 
				<INPUT type="button"  id="sub_cont"  value ="Inserisci" onclick='insertPiattoInOrdine("contorno")'> 
			</td>
		</tr>
        <tr>
            <td colspan="6">
            	<button name="contorno1" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("contorno",1)'>
   					<img src="gfx/sliced-bread-48x48.png"  class="iconaPiattoButt">
   					<?php getPiatti_FastButt("Contorno",1); ?>
 				</button> 
            	
                <button name="contorno2" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("contorno",2)'>
   					<img src="gfx/sliced-bread-48x48.png"  class="iconaPiattoButt">
   					<?php getPiatti_FastButt("Contorno",2); ?>
 				</button> 
                
                <button name="contorno3" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("contorno",3)'>
   					<img src="gfx/sliced-bread-48x48.png"  class="iconaPiattoButt">
   					<?php getPiatti_FastButt("Contorno",3); ?>
 				</button> 
            	
                <button name="contorno4" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("contorno",4)'>
   					<img src="gfx/sliced-bread-48x48.png"  class="iconaPiattoButt">
   					<?php getPiatti_FastButt("Contorno",4); ?>
 				</button> 
            </td >
            </tr>
	</table>
 </form>
		
		<form action="gest_ordini.php" method="post" id="Bibite" class="gestOrdini_piatti">
    	<table >
      	<tr> 
        	<td width="4%">Bib.</td>
          <td width="30%"> 
			<select id="sel_bib" onchange='updateonSelect("bibite")'>
          	<?php getPiatti_select("Bibita"); ?>
            </select> </td>
          <td width="5%"> Num.</td>
          <td width="8%">
						<INPUT id="tex_bibNum" type="text" size="3" maxlength="3"  value="1" onKeyUp="checkIsNum(this,'numeri')" onChange='calcolaPrezzo("bibite")'> 
          </td >
          <td width="5%"> Prez.</td>
          <td width="15%"> <INPUT id="tex_bibPrez" type="text" size="7"  readonly="1" > �
          </td>
          <td width="5%"> Note.</td>
          <td> <INPUT id="tex_bibNote" type="text" size="20" maxlength="30" > 
          </td>
       	</tr>
		</table>
		<table >
		<tr> 
			<td width="30%"> <img src="gfx/beer-48x48.png"  class="iconaPiatto"> </td>
     	<td width="4%"> </td>			
     	<td width="5%"> Rim.</td>
     	<td width="8%"> <INPUT id="tex_bibRim" type="text" size="3" readonly="1" > 
      </td>	
      <td width="18%"> 
      	<div class="gestOrdini_piatti_allarm_hide" id="piattiAllarm_bib"> 
					<img src="gfx/led_rosso_16x16.png" align="middle" class="gestOrdini_piatti_allarm"> 
					in esaurimento 
				</div>
			</td>   
      <td  align="right"> 
				<INPUT type="button"  id="canc_des"  value ="Cancella" onclick='insertPiattoInOrdine("cancella")'> 
				<INPUT type="button"  id="sub_bib"  value ="Inserisci" onclick='insertPiattoInOrdine("bibite")'> 
			</td>
		</tr>
         <tr>
            <td colspan="6" valign="middle">
            	<button name="bibite1" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("bibite",1)'>
   					<img src="gfx/beer-48x48.png"  class="iconaPiattoButt">
   					<?php getPiatti_FastButt("Bibita",1); ?>
 				</button> 
            	
                <button name="bibite2" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("bibite",2)'>
   					<img src="gfx/beer-48x48.png"  class="iconaPiattoButt">
   					<?php getPiatti_FastButt("Bibita",2); ?>
 				</button> 
                
                <button name="bibite3" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("bibite",3)'>
   					<img src="gfx/beer-48x48.png"  class="iconaPiattoButt">
   					<?php getPiatti_FastButt("Bibita",3); ?>
 				</button> 
            	
                <button name="bibite4" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("bibite",4)'>
   					<img src="gfx/beer-48x48.png"  class="iconaPiattoButt">
   					<?php getPiatti_FastButt("Bibita",4); ?>
 				</button> 
            </td >
            </tr>
	</table>
    </form>				
    
		<form action="gest_ordini.php" method="post" id="dessert" class="gestOrdini_piatti" >
    	<table style="table-layout:fixed" >
      	<tr> 
        	<td width="4%">Bar</td>
          <td width="30%"> 
						<select id="sel_des" onchange='updateonSelect("dessert")'>
          		<?php getPiatti_select("Dessert"); ?>
            </select> </td>
					<td width="5%"> Num.</td>
        	<td width="8%">
						<INPUT id="tex_desNum" type="text" size="3" maxlength="3"  value="1" onKeyUp="checkIsNum(this,'numeri')" onChange='calcolaPrezzo("dessert")'> 
          </td>
     			<td width="5%"> Prez.</td>
          <td width="15%"> <INPUT id="tex_desPrez" type="text" size="7"  readonly="1" > � 
          </td>
          <td width="5%"> Note.</td>
          <td > <INPUT id="tex_desNote" type="text" size="20" maxlength="30"> 
          </td> 
       	</tr>
		</table>
		<table >
		<tr> 
			<td width="30%"> <img src="gfx/coffee-cup-48x48.png"  class="iconaPiatto" > </td>
     	<td width="4%"> </td>			
     	<td width="5%"> Rim.</td>
     	<td width="8%">  <INPUT id="tex_desRim" type="text" size="3" readonly="1" > 
      </td>	
     <td width="18%" > 
      	<div class="gestOrdini_piatti_allarm_hide" id="piattiAllarm_des"> 
					<img src="gfx/led_rosso_16x16.png" align="middle" class="gestOrdini_piatti_allarm"> 
              in esaurimento </div>
			</td>   
			<td  align="right"> 
				<INPUT type="button"  id="canc_des"  value ="Cancella" onclick='insertPiattoInOrdine("cancella")'> 
				<INPUT type="button"  id="sub_des"  value ="Inserisci" onclick='insertPiattoInOrdine("dessert")'> 
			</td>
		</tr>
        <tr>
            <td colspan="6" valign="middle">
            	<button name="dessert1" type="button" style="width:150;height:40;font-size:10;text-align:center;" onClick='insertFastButtonInOrdine("dessert",1)'>
   				<img src="gfx/coffee-cup-48x48.png"  class="iconaPiattoButt" border="0">
   					<?php getPiatti_FastButt("Dessert",1); ?>
 				</button> 
            	
                <button name="dessert2" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("dessert",2)'>
   					<img src="gfx/coffee-cup-48x48.png"  class="iconaPiattoButt" >
   					<?php getPiatti_FastButt("Dessert",2); ?>
 				</button> 
                
                <button name="dessert3" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("dessert",3)'>
   					<img src="gfx/coffee-cup-48x48.png"  class="iconaPiattoButt" >
   					<?php getPiatti_FastButt("Dessert",3); ?>
 				</button> 
            	
                <button name="dessert4" type="button" style="width:150;height:40;font-size:10;" onClick='insertFastButtonInOrdine("dessert",4)'>
   					<img src="gfx/coffee-cup-48x48.png"  class="iconaPiattoButt" >
   					<?php getPiatti_FastButt("Dessert",4); ?>
 				</button> 
            </td >
            </tr>
	  </table>
    </form>
  </div>
  
	<div  id="ordine" class="gestOrdini_ordine" >
		<div id="logo"  class="gestOrdini_ordine_logo" > 
	
			<div id="plogoData"  class="plogoData" > 
				DATA:			</div>
	
	  <div id="logo_2"  class="plogoCassa" > 		
				CASSA: N. <?php $cassa_id=getCassaId(); echo " ".$cassa_id; ?>
			</div>
	
			<div id="logo_tit"  class="plogoOrdine" > 
				ORDINE: N. <?php global $g_ordine_id; echo " ".$g_ordine_id; ?> 
			</div> 
            
<div id="logo_coperti" class="plogoCoperti"  > 
				COPERTI: N. <?php global $g_coperti; echo " ".$g_coperti; ?> 
		  </div> 
            
		  <div id="logo_tit"  class="plogoAppetito" > 
				Buon Appetito!          </div> 
				
		  <div id="logo_img"  class="plogo" > 
          	<img src="gfx/logo24ore_75x75.jpg">      	  
		  </div>
		
      </div>
	</div>
	
	<div id="div_ordine_ctrl" class="gestOrdini_ordine_ctrl"  >
	<form id="form_ordine_ctrl" action="gest_ordini.php"  method="post" onSubmit="StampaOrdine()">
	    <INPUT type="hidden"  value='<?php global $g_ordine_id; echo $g_ordine_id ?>' name="old_ordine_id">
		<INPUT type="submit"  name="submit"  value ="OK"  >
		<INPUT type="button"  name="cancel"  value ="Annulla" onClick="CancellaOrdine()"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        coperti <INPUT id="coperti" name="coperti" type="text" size="5" style="font-size:10" value="1" onChange="insertCoperti(this.value)" onBlur="insertCoperti(this.value)">
	</form>
    
    <form id="form_resto" style="font-size:10">
		tot.<INPUT name="resto_prez_tot" id="prezzo_tot" type="text" size="5" style="font-size:10">
		cont.<INPUT name="contanti" id="contanti" type="text" size="5" style="font-size:10">
        resto<INPUT id="resto" type="text" size="5" readonly="1" style="font-size:10">
		<INPUT type="button"  name="calcola" value ="calcola" onClick="calcResto()" style="font-size:10">
	</form>
	</div>

</body>
</html>
