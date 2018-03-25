
function mostraFinestra(nome)
{
	obj = document.getElementById('piatti');
	obj1 = document.getElementById('pippo');
	
	if(nome=="piatti")
	{
		obj.style.visibility = 'visible';
		obj1.style.visibility = 'hidden';
	}		
	else if (nome=="pippo")
	{
		obj1.style.visibility = 'visible';
		obj.style.visibility = 'hidden';
	}		
}

function showBorder(nome)
{
	image = document.getElementById(nome);
	//retVal=window.confirm(nome);
	//obj.style.outline-style = 'outset';
	image.style.outlineStyle = 'outset';
}


function cancellaPiatto(id, name)
{
	messaggio = "Vuoi Cancellare il piatto ";
	testo = messaggio + name;
	retVal=window.confirm(testo);
	
	if(retVal)
	{
		refUri = "gest_piatti_proc.php?action=cancella&piattoId=" +id;
		finestra=window.open(refUri ,'Cancella Piatto','width=600, height=250');	
	}	
}


function MostraTutto()
{
	messaggio = "Vuoi Inserire tutti i piatti nel Menu ? ";
	retVal=window.confirm(messaggio);
	
	if(retVal)
	{
		refUri = "gest_piatti_proc.php?action=MostraTutto";
		finestra=window.open(refUri ,'Mostra Tutto','width=600, height=250');	
	}	
}
function NascondiTutto()
{
	messaggio = "Vuoi Togliere tutti i piatti dal Menu ? ";
	retVal=window.confirm(messaggio);
	
	if(retVal)
	{
		refUri = "gest_piatti_proc.php?action=NascondiTutto";
		finestra=window.open(refUri ,'Nascondi Tutto','width=600, height=250');	
	}	
}


function modificaPiatto(id, name)
{
	if(id=="nuovo")
		finestra=window.open('gest_piatti_InMask.php?action=nuovo&piattoId=0','Nuovo Piatto','width=600,height=250');
	else
	{
		refUri = "gest_piatti_InMask.php?action=modifica&piattoId=" +id;
		finestra=window.open(refUri ,'Modifica Piatto','width=600, height=250');
	}	
	finestra.focus();
}


function fineSubmitPiatto()
{
	top.opener.window.location.reload();
	window.close();
}


function checkIsNum(text_obj, tipo)
{ 
	var r={'numeri':/[^\d]/g,
        'soldi':/[^\d \.]/g};
	if(tipo=='soldi')
	{
		text_obj.value = text_obj.value.replace(',','.');
	}

  	text_obj.value = text_obj.value.replace(r[tipo],'');	
}




function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

