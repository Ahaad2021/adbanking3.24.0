<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Calendrier
 *
 * @todo lorsqu'on transmet $input dans l'URL on devrait faire un urlencode() d'abord et ensuite faire un urldecode()
 * @package Ifutilisateur
 */

require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/tableSys.php';


function get_calendrier($mois, $annee, $annee_start, $annee_end, $input) {
  /* Renvoie le code html pour le calendrier au mois/annee demandés */
  global $colb_tableau;
  global $adsys;

  $retour = "";
  //Corps
  $retour .= "<FORM NAME=\"ADForm\" METHOD=\"POST\" ACTION=\"$PHP_SELF\">\n";
  //Affichage tableau sup
  $retour .= "<TABLE align=\"center\" valign=\"middle\" cellspacing=10>\n";
  $retour .= "<TR bgcolor=".$colb_tableau.">\n";
  //link mois prec
  $dummy_mois = $mois-1;
  $dummy_annee = $annee;
  if ($dummy_mois == 0) {
    $dummy_mois = 12;
    $dummy_annee -= 1;
  }

  $retour .= "<TD align=\"left\"><A href=\"calendrier.php?m_agc=".$_REQUEST['m_agc']."&calend_mois=$dummy_mois&calend_annee=$dummy_annee&calend_annee_start=$annee_start&calend_annee_end=$annee_end&calend_input=$input\">"._("prec")."</A></TD>\n";
  //Mois
  $retour .= "<TD align=\"center\"><SELECT name=\"calend_mois\" onchange=\"document.ADForm.submit();\">";
  for ($i=1; $i<=12; ++$i) {
    if ($i == $mois)
      $s = ' selected';
    else
      $s = '';
    $retour .= "<OPTION value='$i' $s>".adb_gettext($adsys["adsys_mois"][$i])."</OPTION>";
  }

  $retour .= "</SELECT></TD>\n";
  //Années
  $retour .= "<TD align=\"center\"><SELECT name=\"calend_annee\" onchange=\"document.ADForm.submit();\">";
  for ($i=$annee_start; $i<=$annee_end; ++$i) {
    if ($i == $annee) $s = ' selected';
    else $s = '';
    $retour .= "<OPTION value=$i $s>$i</OPTION>";
  }
  $retour .= "</SELECT></TD>\n";
  //link mois suivant
  $dummy_mois = $mois+1;
  $dummy_annee = $annee;
  if ($dummy_mois == 13) {
    $dummy_mois = 1;
    $dummy_annee += 1;
  }
  $retour .= "<TD align=\"left\"><A href=\"calendrier.php?m_agc=".$_REQUEST['m_agc']."&calend_mois=$dummy_mois&calend_annee=$dummy_annee&calend_annee_start=$annee_start&calend_annee_end=$annee_end&calend_input=$input\">"._("suiv")."</A></TD>\n";
  $retour .= "</TR>\n";
  $retour .= "</TABLE>\n";

  //Deuxième tableau :
  $retour .= "<TABLE align=\"center\" valign=\"middle\" cellspacing=10>\n";
  //Jours de la semaine
  $retour .= "<TR bgcolor=$colb_tableau>\n";
  for ($i=1; $i<=7; ++$i) {
    switch ($i) {
    case 1 :
      $jour_str=_("lun");
      break;
    case 2 :
      $jour_str=_("mar");
      break;
    case 3 :
      $jour_str=_("mer");
      break;
    case 4 :
      $jour_str=_("jeu");
      break;
    case 5 :
      $jour_str=_("ven");
      break;
    case 6 :
      $jour_str=_("sam");
      break;
    case 7 :
      $jour_str=_("dim");
      break;
    }
    $retour .= "<TD align=\"center\"><b>$jour_str</b></TD>";
  }
  $retour .= "</TR>\n";


  //On commence par positionner au bon endroit dans le tableau
  $retour .= "<TR bgcolor=$colb_tableau>\n";
  $jour_premier = date("w",mktime(0,0,0,$mois,1,$annee)); //Récupère le jour de la semaine du 1er (0=dimanche, 6=samedi)
  if ($jour_premier == 0) $jour_premier=7; //On ramène à Lundi=1, Dimanche=7

  for ($j=1; $j<$jour_premier; ++$j) $retour .= "<td></td>";//On inscrit des cellules vides

  for ($i=1; checkdate($mois, $i, $annee) == true; ++$i, ++$j) { //Pour chaque jour de l'année
    $retour .= "<TD align=\"center\"><a href=\"#\" onclick=\"set_retour('".sprintf("%02d",$i)."/".sprintf("%02d",$mois)."/$annee');\">";
    if ($annee == date("Y") && $mois == date("m") && $i == date("d")) {
      $retour .= "<font color=red><B>$i</B></font>";
    } else {
      $retour .= "$i";
    }
    $retour .= "</a></TD>";

    if ($j==7) { //Si on arrive en fin de ligne
      $retour .= "</TR><TR bgcolor=$colb_tableau>";
      $j=0;
    }
  }

  //On termine le tableau
  for (; ($j<7) && ($j != 1); ++$j) $retour .= "<td></td>";//On inscrit des cellules vides
  if ($j != 1) $retour .= "</TR>";

  //Bouton Annuler
  $retour .= "</TR><TR bgcolor=$colb_tableau><TD align=\"center\" colspan=7><INPUT type=\"button\" value=\""._("Annuler")."\" onclick=\"window.close();\"></TD>\n";
  $retour .= "</TR></TABLE>\n";
  //Fin
  $retour .= "<INPUT TYPE=\"hidden\" VALUE=\"$annee_start\" NAME=\"calend_annee_start\">\n";
  $retour .= "<INPUT TYPE=\"hidden\" VALUE=\"$annee_end\" NAME=\"calend_annee_end\">\n";
  $retour .= "<INPUT TYPE=\"hidden\" VALUE=\"$input\" NAME=\"calend_input\">\n";
  $retour .= "</FORM>";
  //Javascript pour valeur retour
  $retour .= "
             <script type=\"text/javascript\">
             function set_retour(valeur){
             window.opener.document.ADForm.$input.value = convert_js_date(valeur); window.close();
           }
             </script>";
  return $retour;
}

if (isset($calend_mois) && isset($calend_annee) &&
    isset($calend_annee_start) && isset($calend_annee_end) && isset($calend_input)) { //Si l'appel est correct et que l'on doit (re)charger

  //En-tête du document
  require 'lib/html/HtmlHeader.php';
  require_once('lib/html/HTML_GEN2.php');
  //Corps
  $retour = get_calendrier($calend_mois, $calend_annee, $calend_annee_start, $calend_annee_end, $calend_input);
  $myForm = new HTML_GEN2(_("Calendrier"));
  $myForm->addHTMLExtraCode("cal", $retour);
  $myForm->buildHTML();
  echo $myForm->getHTML();
  //Fin
  require 'lib/html/HtmlFooter.php';
}
?>