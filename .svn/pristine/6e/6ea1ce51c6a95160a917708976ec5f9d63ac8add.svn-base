<?php

/**
 * Gestion des jours fériés
 * @package Parametrage
 */

require_once 'lib/dbProcedures/ferie.php';
require_once 'lib/misc/VariablesGlobales.php';

if ($global_nom_ecran == "Vjf-1") {
  if (!isset($mois)) $mois = date("m");
  if (!isset($annee)) $annee = date("Y");
  $retour = "<H1 align=\"center\">"._("Visualisation jours ouvrables")."</H1><br><br>";
  $retour .= "<FORM NAME=\"ADForm\" METHOD=\"POST\" ACTION=\"$PHP_SELF\">";

  //Champs hidden
  $retour .= "<INPUT type=\"hidden\" name=\"prochain_ecran\" value=\"Vjf-1\">";
  $retour .= "<INPUT type=\"hidden\" name=\"m_agc\" value=\"".$_REQUEST['m_agc']."\">";
  $retour .= "<INPUT type=\"hidden\" name=\"mois\" value=\"$mois\">";
  $retour .= "<INPUT type=\"hidden\" name=\"annee\" value=\"$annee\">";

  //Affichage tableau sup
  $retour .= "<TABLE align=\"center\" valign=\"middle\" cellspacing=10>\n";
  $retour .= "<TR bgcolor=".$colb_tableau.">\n";

  //Mois
  $retour .= "<TD align=\"center\"><SELECT name=\"calend_mois\" onChange=\"document.ADForm.mois.value = document.ADForm.calend_mois.value;document.ADForm.submit();\">";
  for ($i=1; $i<=12; ++$i) {
    switch ($i) {
    case 1:
      $mois_str=_('Janvier');
      break;
    case 2:
      $mois_str=_('Février');
      break;
    case 3:
      $mois_str=_('Mars');
      break;
    case 4:
      $mois_str=_('Avril');
      break;
    case 5:
      $mois_str=_('Mai');
      break;
    case 6:
      $mois_str=_('Juin');
      break;
    case 7:
      $mois_str=_('Juillet');
      break;
    case 8:
      $mois_str=_('Août');
      break;
    case 9:
      $mois_str=_('Septembre');
      break;
    case 10:
      $mois_str=_('Octobre');
      break;
    case 11:
      $mois_str=_('Novembre');
      break;
    case 12:
      $mois_str=_('Décembre');
      break;
    }
    if ($i == $mois) $s = ' selected';
    else $s = '';
    $retour .= "<OPTION value=".$i.$s.">".$mois_str."</OPTION>";
  }
  $retour .= "</SELECT></TD>\n";
  //Années
  $retour .= "<TD align=\"center\"><SELECT name=\"calend_annee\" onchange=\"document.ADForm.annee.value = document.ADForm.calend_annee.value;document.ADForm.submit();\">";
  for ($i=1999; $i<=2025; ++$i) {
    if ($i == $annee) $s = ' selected';
    else $s = '';
    $retour .= "<OPTION value=".$i.$s.">".$i."</OPTION>";
  }
  $retour .= "</SELECT></TD>\n";
  $retour .= "</TR></TABLE>\n";

  //Deuxième tableau :
  $retour .= "<TABLE align=\"center\" valign=\"middle\" cellspacing=10>\n";
  //Jours de la semaine
  $retour .= "<TR bgcolor=$colb_tableau>\n";
  for ($i=1; $i<=7; ++$i) {
    switch ($i) {
    case 1 :
      $jour_str=_("lun[[lundi]]");
      break;
    case 2 :
      $jour_str=_("mar[[mardi]]");
      break;
    case 3 :
      $jour_str=_("mer[[mercredi]]");
      break;
    case 4 :
      $jour_str=_("jeu[[jeudi]]");
      break;
    case 5 :
      $jour_str=_("ven[[vendredi]]");
      break;
    case 6 :
      $jour_str=_("sam[[samedi]]");
      break;
    case 7 :
      $jour_str=_("dim[[dimanche]]");
      break;
    }

    $retour .= "<TD align=\"center\"><b>$jour_str</b></TD>";
  }
  $retour .= "</TR>\n";


  //On commence par positionner au bon endroit dans le tableau
  $retour .= "<TR bgcolor=$colb_tableau>\n";
  $jour_premier = date("w",mktime(0,0,0,$mois,1,$annee)); //Récupère le jour de la semaine du 1er (0=dimanche, 6=samedi)
  if ($jour_premier == 0) $jour_premier = 7; //On ramène à Lundi=1, Dimanche=7

  for ($j=1; $j<$jour_premier; ++$j) $retour .= "<td></td>";//On inscrit des cellules vides

  for ($i=1; checkdate($mois, $i, $annee) == true; ++$i, ++$j) { //Pour chaque jour de l'année

    if (is_ferie($i,$mois,$annee)) $color = 'bgcolor = '.$colb_ferie;
    else $color = "";
    $retour .= "<TD align=\"center\" $color>$i</TD>";

    if ($j==7) { //Si on arrive en fin de ligne
      $retour .= "</TR><TR bgcolor=$colb_tableau>";
      $j=0;
    }
  }

  //On termine le tableau
  for (; ($j<7) && ($j != 1); ++$j) $retour .= "<td></td>";//On inscrit des cellules vides
  if ($j != 1) $retour .= "</TR>";

  //Bouton Annuler
  $retour .= "<TR bgcolor=$colb_tableau><TD align=\"center\" colspan=7><INPUT type=\"submit\" value=\""._("Annuler")."\" onclick=\"assign('Gen-12');\"></TD>\n";
  $retour .= "</TR></TABLE>\n";
  //Fin
  $retour .= "</FORM>";
  //Legende
  $retour .= "<br><br><TABLE align = \"center\">";
  $retour .= "<TR><TD colspan=2><b>"._("Légende")."</b></TD></TR>";
  $retour .= "<TR><TD bgcolor=$colb_tableau width=10></TD><TD>"._("Jour ouvrable")."</TD></TR>";
  $retour .= "<TR><TD bgcolor=$colb_ferie></TD><TD>"._("Jour non-ouvrable")."</TD></TR>";
  $retour .= "</TABLE>";
  //Affiche
  echo $retour;
} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu"
?>