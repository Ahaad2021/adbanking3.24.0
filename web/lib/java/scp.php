<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Fonctions javascript
 * @package Ifutilisateur
 */

require_once 'lib/multilingue/convertdate.php';
require_once 'lib/misc/VariablesSession.php';

if (locale_mmjjaaaa())
{
	echo ' 
	function convert_js_date(date) {
		if (date == "")	return "";
		strarr = new Array();
		own_split(strarr, date, "/");
		if ((strarr[2] == "") || (! isIntPos(strarr[1]))) 
			return "";
		return strarr[1]+"/"+strarr[0]+"/"+strarr[2];
	}	
	';
}
else
{
	echo '
        function convert_js_date(date) {
		return date;
	}
	';
}

?>

var mnt_sep_mil = ' '; //Séparateur pour les milliers (montants)
var mnt_sep_dec = new Array();
mnt_sep_dec[0] = ','; //Séparateur pour la décimale (montants), celui-ci sera utilisé pour le reformattage
mnt_sep_dec[1] = '.'; //Séparateur pour la décimale (montants), celui-ci est reconnu mais pas conservé

function OpenBrw(FileName, Title) {
  WinFils = window.open(FileName, Title, "width=400,height=600,screenX=200,screenY=75,status=no,toolbar=no,menubar=no,scrollbars=yes,resizable=yes");
  return true;
}

function OpenBrwXY(FileName, Title, X, Y) {
WinFils = window.open(FileName, Title, "width="+X+",height="+Y+",screenX=200,screenY=75,status=no,toolbar=no,menubar=no,scrollbars=yes,resizable=yes");
return true;
}

function OpenBrwCustom(FileName, Title, X, Y) {
myWindow = window.open(FileName, Title, "width=400,height=600,screenX="+X+",screenY="+Y+",status=no,toolbar=no,menubar=no,scrollbars=yes,resizable=yes");
return myWindow;
}

// Used to add slash and triggered by an OnChange event
function addslashes(objet) {
  var len = objet.value.length;
  if( len == 2 || len == 5) {
    objet.value += '/';
    return;
  }
}

function getMonth(p){
  p = convert_js_date(p);
  if (p.length == 0){
    d = new Date();
    return (d.getMonth()+1);
  }
  else{
    strarr = new Array();
    own_split(strarr, p, "/");
    if ((strarr[1] == "") || (! isIntPos(strarr[1]))) alert("<?= _("Erreur interne JS : impossible de récupérer le mois !") ?>");
    return strarr[1];
  }
}

function getYear(p){
  p = convert_js_date(p);
  if (p.length == 0){
    d = new Date();
    return d.getFullYear();
  }
  else{
    strarr = new Array();
    own_split(strarr, p, "/");
    if ((strarr[2] == "") || (! isIntPos(strarr[1]))) alert("<?= _("Erreur interne JS : impossible de récupérer l'année !") ?>");
    return strarr[2];
  }
}


function isDate(str){
    str = convert_js_date(str);
    strarr = new Array();

    if (str.length == 0) return true;    
    
    own_split(strarr, str, "/");
    
    if (! isIntPos(strarr[0])) return false;
    if (! isIntPos(strarr[1])) return false;
    if (! isIntPos(strarr[2])) return false;

    //Check année (sur 4 chiffres?)
    if ((strarr[2] < 1900) || (strarr[2] > 2100)) return false;

    //Check mois
    if ((strarr[1] < 1) || (strarr[1]>12)) return false;

    //Check jours
    if (strarr[0] < 1) return false;
    if ((strarr[0] > 30) && ((strarr[1] == 4) || (strarr[1] == 6) || (strarr[1] == 9) || (strarr[1] == 11))) return false;
    if ((strarr[0] > 31) && ((strarr[1] == 1) || (strarr[1] == 3) || (strarr[1] == 5) || (strarr[1] == 7) || (strarr[1] == 8) || (strarr[1] == 10) || (strarr[1] == 12))) return false;
    if ((strarr[1] == 2) && ((strarr[2] % 4) == 0) && (strarr[0]>29)) return false;
    if ((strarr[1] == 2) && ((strarr[2] % 4) != 0) && (strarr[0]>28)) return false;

    return true;
}

function isBefore(date1, date2)
    // Fonction qui renvoie true si date1 est strictement antérieur à date2 et false sinon
{
    date1 = convert_js_date(date1);
    date2 = convert_js_date(date2);
    strarr1 = new Array();
    own_split(strarr1, date1, "/");	
    dateObj1 = new Date(parseInt(strarr1[2],10), parseInt(strarr1[1],10)-1, parseInt(strarr1[0],10));
    strarr2 = new Array();
    own_split(strarr2, date2, "/");
    dateObj2 = new Date(parseInt(strarr2[2],10), parseInt(strarr2[1],10)-1, parseInt(strarr2[0],10));
    return (dateObj1.getTime() < dateObj2.getTime());
}

function isBeforeOrEqualTo(date1, date2)
// Fonction qui renvoie true si date1 est strictement antérieur ou egale à date2 et false sinon
{
date1 = convert_js_date(date1);
date2 = convert_js_date(date2);
strarr1 = new Array();
own_split(strarr1, date1, "/");
dateObj1 = new Date(parseInt(strarr1[2],10), parseInt(strarr1[1],10)-1, parseInt(strarr1[0],10));
strarr2 = new Array();
own_split(strarr2, date2, "/");
dateObj2 = new Date(parseInt(strarr2[2],10), parseInt(strarr2[1],10)-1, parseInt(strarr2[0],10));
return (dateObj1.getTime() <= dateObj2.getTime());
}

function isAfter(date1, date2)
// Fonction qui renvoie true si date1 est strictement postérieure à date2 et false sinon
{
    date1 = convert_js_date(date1);
    date2 = convert_js_date(date2);
    strarr1 = new Array();
    own_split(strarr1, date1, "/");
    dateObj1 = new Date(parseInt(strarr1[2],10), parseInt(strarr1[1],10)-1, parseInt(strarr1[0],10));
    strarr2 = new Array();
    own_split(strarr2, date2, "/");
    dateObj2 = new Date(parseInt(strarr2[2],10), parseInt(strarr2[1],10)-1, parseInt(strarr2[0],10));
    return (dateObj1.getTime() > dateObj2.getTime());
}

function isAfterToday(date)
    // Fonction qui renvoie true si la date passée en paramètre est postérieure à la date du jour.
    // La date est attendue au format 'jj/mm/aaaa'
{
    date = convert_js_date(date);
    today = new Date();
    today_str = today.getDate()+'/'+(parseInt(today.getMonth())+1)+'/'+today.getFullYear();
    //    alert(today_str);
    return (isBefore(today_str, date));
}

function own_split(arr, str, delim)
{
  //Split le string "arr" en fonction des délimiteurs "delim" et renvoie dans le tableau "str[]"

  //Initialise local variables
  var pos = 0;
  var num = 0;
  var start = 0;
	
  //Loop while there are characters in the string
  while (pos < str.length)
    {
      //Loop while there are delimiters in the string
      while((str.substring (pos, pos+1) != delim) && (pos < str.length))
	{
	  pos++;
	}
      //Add the new characters to the output array
      arr[num] = str.substring(start,pos);
      num++;
      start = pos+1;
      pos++;
    }
}

function isIntPos(str){ //Entier positif ?
  var pattern = /^[0-9]+$/;
  return ((str == "") || (pattern.test(str)));
}

function isFloat(str){
  var floatPattern = /^[0-9]+([\.\,][0-9]+)*$/;
  return ((str == "") || (floatPattern.test(str)));
}

function isEmail(str)
{
	if (str == "") return true;
	var emailFilter=/^.+@.+\..{2,3}$/;
	var illegalChars= /[\(\)\<\>\,\;\:\\\/\"\[\]]/;
	if (!(emailFilter.test(str)) || (str.match(illegalChars))) return false;
	return true;
}

function isInRange(objet, minVal, maxVal)
{
	return ((objet.value > minVal) && (objet.value < maxVal) ? true : false);
}

function isEmpty(objet)
{
	return (objet.value == "" ? true : false);
}

function isInt(objet)
{
	return ( ( parseInt(objet.value) == objet.value) ? true : false )
}

function isFlt(objet)
{
	return ( ( parseFloat(objet.value) == objet.value) ? true : false)
}

function isLegalChar(objet)
{
	var illegalChars = /\W/;
	// allow only letters, numbers, and underscores
	if (illegalChars.test(objet.value)) return false;
	return true;
}

function isPhone(str)
{
  //On accepte les chiffres, point, espace, parenthèse, plus
  return (str.search(/[^0-9\. \(\)\+]/) == -1);
}

function checkDropdown(objet) {
	if (objet.selectedIndex == 0) return false;
	return true;
}

function open_calendrier(mois, annee, annee_depart, annee_fin, nom_champs){
  url = '<?php echo $http_prefix; ?>/lib/html/calendrier.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&calend_mois='+mois+'&calend_annee='+annee+'&calend_annee_start='+
    annee_depart+'&calend_annee_end='+annee_fin+'&calend_input='+nom_champs;
  CalendWindow = window.open(url,"<?php echo _("Calendrier");?>","alwaysRaised=1,dependent=1,scrollbars,resizable=0");
}

function open_change(montant,devise,montantCV,deviseCV,nomChampDevise,nomChampCV,comm_nette,taux,reste,dest_reste,achat_vente, type_change) {
  url = '<?php echo $http_prefix; ?>/lib/html/change_montant_devise.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&montant='+montant+'&contre_valeur='+montantCV+'&devise='+
	devise+'&devise_contre_valeur='+deviseCV+'&nomChampDevise='+nomChampDevise+'&nomChampCV='+nomChampCV+
    '&comm_nette='+comm_nette+'&taux='+taux+'&reste='+reste+'&dest_reste='+dest_reste+"&achat_vente="+achat_vente+"&type_change="+type_change+"&etape=1";
  ChangedWindow = window.open(url,"<?php echo _("Change");?>","alwaysRaised=1,dependent=1,scrollbars,resizable=0");
};

function open_change_multi_agences(montant,devise,montantCV,deviseCV,nomChampDevise,nomChampCV,comm_nette,taux,reste,dest_reste,achat_vente, type_change) {
  url = '<?php echo $http_prefix; ?>/lib/html/change_montant_devise_multi_agences.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&montant='+montant+'&contre_valeur='+montantCV+'&devise='+
	devise+'&devise_contre_valeur='+deviseCV+'&nomChampDevise='+nomChampDevise+'&nomChampCV='+nomChampCV+
    '&comm_nette='+comm_nette+'&taux='+taux+'&reste='+reste+'&dest_reste='+dest_reste+"&achat_vente="+achat_vente+"&type_change="+type_change+"&etape=1";
  ChangedWindow = window.open(url,"<?php echo _("Change");?>","alwaysRaised=1,dependent=1,scrollbars,resizable=0");
};

function open_billetage(shortName, direction,devise)
{ 
    // Construction de l'URL : de type ./lib/html/billettage.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&shortName=somme&direction=in
    url = '<?php echo $http_prefix; ?>/lib/html/billetage.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&shortName='+shortName+'&direction='+direction+'&devise='+devise;
   BillettageWindow = window.open(url, "<?php echo _("Billettage");?>", 'alwaysRaised=1,dependent=1,scrollbars,resizable=0');
}

// Ouvre le gestionnaire d'image
function open_image_manager(imgshortname, imglongname, imgurl, canmodif)
{ 
    // Construction de l'URL : de type ./lib/html/image_mgr.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&name=mame&url=url&canmodif=0
    url = '<?php echo $http_prefix; ?>/lib/html/image_mgr.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&shortname='+imgshortname+'&longname='+imglongname+'&url='+imgurl+'&canmodif='+canmodif;
   ImageWindow = window.open(url, "<?php echo _("Gestionnaire d\'image");?>", 'alwaysRaised=1,dependent=1,scrollbars,resizable=0');
}

//Gestion du bouton "enter"
enterButtonExist = false;

//Gestion des boutons 0->9
link0Value = "";
link1Value = "";
link2Value = "";
link3Value = "";
link4Value = "";
link5Value = "";
link6Value = "";
link7Value = "";
link8Value = "";
link9Value = "";

function handleKeys(evnt){
  if ((evnt.which == 13) && (enterButtonExist == true)){ //Touche ENTER
	 enterButton.click();
  }
  else if ((evnt.which == 48) && (link0Value != "")) location.replace(link0Value); //Touche 0
  else if ((evnt.which == 49) && (link1Value != "")) location.replace(link1Value); //Touche 1
  else if ((evnt.which == 50) && (link2Value != "")) location.replace(link2Value); //Touche 2
  else if ((evnt.which == 51) && (link3Value != "")) location.replace(link3Value); //Touche 3
  else if ((evnt.which == 52) && (link4Value != "")) location.replace(link4Value); //Touche 4
  else if ((evnt.which == 53) && (link5Value != "")) location.replace(link5Value); //Touche 5
  else if ((evnt.which == 54) && (link6Value != "")) location.replace(link6Value); //Touche 6
  else if ((evnt.which == 55) && (link7Value != "")) location.replace(link7Value); //Touche 7
  else if ((evnt.which == 56) && (link8Value != "")) location.replace(link8Value); //Touche 8
  else if ((evnt.which == 57) && (link9Value != "")) location.replace(link9Value); //Touche 9
}

//Gestion de l'assignation d'un valeur pour le prochain écran (un champ hidden "prochain_ecran" doit exister dans le formulaire "ADForm")
function assign(value){
  document.ADForm.prochain_ecran.value = value;
  if(document.ADForm.m_agc) {
    document.ADForm.m_agc.value = '<?php echo $_REQUEST['m_agc']; ?>';
  }
  return true;
}

function splitMontant(str){ //Renvoie un array avec [0] : partie entière et [1] partie décimale
    var retour = new Array();

    entier_begin = false;          // Passe à true lorsqu'on détecte le 1er entier
    entier_has_zero = false;       // Passe à true ... ??? 
    part = 0;                      // 0 si avant le point de fractionnement, 1 après
    retour[0] = "";                // Partie entière
    retour[1] = "";                // Partie décimale
    for (i = 0; i < str.length; ++i)
      {
	if ((str.charAt(i) != mnt_sep_dec[0]) && (str.charAt(i) != mnt_sep_dec[1]))
	  { //Si ce n'est pas un séparateur, on reste dans la mme partie
	    if ((! entier_begin) && (part == 0))
	      {
		if ((str.charCodeAt(i) == 45)) // Si on a pas encore trouvé de chifre et qu'on trouve '-', on a affaire à un nombre négatif
		  retour[part] = '-';
		else if ((str.charCodeAt(i) >= 49) && (str.charCodeAt(i) <= 57)) // chiffre [1-9]
		  entier_begin = true; // Système pour éviter de récupérer les zéros précédent la partie entière
		if (str.charCodeAt(i) == 48) // chiffre [0]
		  entier_has_zero = true; //Si entier = "0000" alors on renvoie "0"
	    }
	    if ((part == 0) && (entier_begin) || (part == 1)) //Si on a commencé à récupérer la partie entière
	      if ((str.charCodeAt(i) >= 48) && (str.charCodeAt(i) <= 57)) // Chiffre [0-9] 
		retour[part] += str.charAt(i); //Si c'est un chiffre
	  } // Sinon on passe à la partie 1
	else part = 1;
    }
    if ((retour[0] == "") && (entier_has_zero)) 
      retour[0] = "0";

    //Renvoie "" si la partie décimale ne contient que des zéros
    var pattern = /^[0]+$/;
    if ((retour[1] != "") && (pattern.test(retour[1]))) 
      retour[1] = "";

    return retour;
}

function isMontant(str){ //Montant positif ou négatif avec séparateur de milliers/décimale
    if (str == "") 
      return true;
    str = splitMontant(str);
    pattern = "^-{0,1}[0-9 ]+$";
    pattern2 = "^[0-9]+$";
    if ((str[0] != "") && (str[0].search(pattern) != 0)) return false;
    if ((str[1] != "") && (str[1].search(pattern2) != 0)) return false;
    return true;
}

function formateMontant(str){
  if (!isNaN(str))        // Si le montant n'est pas un string mais un nombre
    str = str.toString(); // On le convertit
  if (str == "") return "";
  str = splitMontant(str);
  entier = str[0];
  decimal = str[1];
  
  //Place les séparateurs de milliers
  entier2 = "";
  for (i = entier.length-1; i >= 0; --i){
    if ((((entier.length-i)%3) == 0) && (i != 0)){
      entier2 = mnt_sep_mil + entier.charAt(i) + entier2;
    }
    else entier2 = entier.charAt(i) + entier2;
    }
  
  //Merge entier + decimal
  if (decimal != ""){
    if (entier2 == "") entier2 = "0";
    entier2 = entier2 + mnt_sep_dec[0] + decimal;
  }
  
  return entier2;
}

function recupMontant(str){
    str = splitMontant(str);
    montant = str[0];
    if (str[1] != "") 
      montant += '.' + str[1];
    if(montant == "")
      montant = 0;
    return parseFloat(montant);
}

// checkSumLessThan : Calcule le total des cellules d'une colonne, place le résultat dans une cellule de somme et ensuite vérifie que ce résultat ne dépasse pas une certaine valeur, si oui, l'élement somme est coloré de rouge.
//
// @param input_cells Un tableau contenant les éléments à additioner
// @param sum_cell L'élément contenant le total calculé
// @param ceiling Le plafond à ne pas dépasser
// @access public
// @return void Un message d'erreur si le plafond est dépassé, une chaîne vide sinon.<br><strong>Effet de bord</strong> L'élément total est coloré de rouge si le plafond est dépassé.

function checkSumLessThan(input_cells, sum_cell, ceiling, cell_description) {
  var total = 0;
  var return_msg = '';
  for (var i = 0; i < input_cells.length; ++i) {
    if (input_cells[i].value != "") total += recupMontant(input_cells[i].value);
  }
  sum_cell.value = formateMontant(total);
  if (total > ceiling && ceiling > 0) {
    sum_cell.style.backgroundColor = 'red';
    return_msg += '<?= sprintf(_("Le total '%s' doit être plus petit que '%s' et est pour le moment égal à '%s"),"+cell_description+","+formateMontant(ceiling)+","+formateMontant(sum_cell.value)+") ?>'\n';
  } else {
    sum_cell.style.backgroundColor = 'lightgrey';
  }
  return (return_msg);
}

// open_produit : Ouverture d'une fenêtre popup avec les détails d'un produit
//
// @param id_produit L'identifiant du produit
// @access public
// @return bool
function open_produit(id_produit, id_doss)
{
  if(id_produit != 0)
    {
      OpenBrwXY('../lib/html/produit.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&id='+id_produit+'&id_doss='+id_doss, '<?=_("Produit sélectionné")?>', 550, 650);
    }
  return false;
}

function open_produit_recup (id_produit)
{
  if(id_produit != 0)
    {
      OpenBrwXY('../../lib/html/produit.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&id='+id_produit, '<?=_("Produit sélectionné")?>', 550, 650);
    }
  return false;
}
function open_compte(cpte_cli,id_compte)
{
  url =  '<?php echo $http_prefix; ?>/modules/clients/rech_client.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&choixCompte=1 & cpt_dest='+cpte_cli+'&id_cpt_dest='+id_compte;
  garant = OpenBrwXY(url,'<?=_("Compte de prélèvement")?>', 400, 500);
}

// Convert date string in object date time
function parseDate(str)
{
    var mdy = str.split('/');
    return new Date(mdy[2], mdy[1]-1, mdy[0]);
};

// Difference entre 2 dates
function daydiff(deb, fin)
{
    return Math.round((fin-deb)/(1000*60*60*24));
};

// Intervale entre 2 dates.
function checkDateRange(range,datedeb,datefin)
{
    if(range == 0)
    {
        return true;
    }

    var deb = parseDate(datedeb);
    var fin = parseDate(datefin);

    var diff= daydiff(deb, fin);

    if( diff <= range)
    {
        return true;
    }
    else
    {
        return false;
    }
}

// ordre alphabétique d'un element select (HTML) case insensitive
function sortSelect(selElem)
{
    var tmpAry = new Array();
    for (var i=0;i<selElem.options.length;i++)
    {
        tmpAry[i] = new Array();
        tmpAry[i][0] = selElem.options[i].text;
        tmpAry[i][1] = selElem.options[i].value;
    }

    tmpAry.sort(function(a,b)
    {
        var x = a[0].toLowerCase(), y = b[0].toLowerCase();
        return x < y ? -1 : x > y ? 1 : 0;
    });

    while (selElem.options.length > 0)
    {
        selElem.options[0] = null;
    }

    for (var i=0;i<tmpAry.length;i++)
    {
        var op = new Option(tmpAry[i][0], tmpAry[i][1]);
        selElem.options[i] = op;
    }

    return;
}

// open_compte_mvts : Ouverture d'une fenêtre popup avec un rapport PDF pour 100 derniers mouvements d'un compte epargne
//
// @param id_cpte L'identifiant du compte, is_ord_per : 1 = ordre permanents
// @access public
// @return bool
function open_compte_mvts(id_cpte, is_ord_per)
{
  if(id_cpte != 0)
  {
  OpenBrwXY('../lib/html/compte_mvts.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&id_cpte='+id_cpte+'&id_ord_per='+is_ord_per, '<?=_("Rapport 100 derniers mouvements")?>', 550, 650);
  }
  return false;
}

// suiviCredit : Ouverture d'une fenêtre popup avec un rapport PDF pour le suivi d'un credit
//
// @param id_doss L'identifiant du dossier credit
// @access public
// @return bool
function suiviCredit(id_doss)
{
  if(id_doss != 0)
  {
  OpenBrwXY('../lib/html/rapportSuiviCredit.php?m_agc=<?php echo $_REQUEST['m_agc']; ?>&id_doss='+id_doss, '<?=_("Rapport Suivi Credit")?>', 550, 650);
  }
  return false;
}