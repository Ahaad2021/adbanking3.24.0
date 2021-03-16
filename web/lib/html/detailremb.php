<?Php

/**
 * detailremb
 *
 * Renvoie le code html du suivi du crédit
 * INPUT: $Parametre et $echeancier
 * @package Credit
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/divers.php';

global $colb_tableau;
global $colb_tableau_altern;
global $tableau_border;
global $tableau_cellspacing;
global $tableau_cellpadding;
global $global_monnaie;
global $global_monnaie_prec;

require("lib/html/HtmlHeader.php");
if (isset($id_doss) && (isset($id_ech)))  $remb = getDetailRembCrdt($id_doss,$id_ech);

if (is_array($remb)) {
  $Myform = new HTML_GEN2(_("Suivi détaillé du crédit"));
  $retour="";

  //Tableau des remboursements
  $retour .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\"cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding  border=$tableau_border>\n";
  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=6 align=\"center\">"._("Remboursement de l'échéance")." $id_ech</TD>\n";
  $retour .= "</TR>\n";

  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD align=\"center\">"._("N°")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Date remboursement")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Capital remb.")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Intérêts remb.")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Pénalités remb.")."</TD>\n";
  $retour .= "</TR>\n";

  // Echéancier non stocké dans la base de données
  // D'où la nécessité de le générer entièrement
  reset($remb); // Réinitialise le pointeur de tableau des écheances
  $total_cap=0;
  $total_int=0;
  $total_pen=0;
  while (list($key,$echanc) = each($remb)) {
    $total_cap += $echanc["mnt_remb_cap"];
    $total_int += $echanc["mnt_remb_int"];
    $total_pen += $echanc["mnt_remb_pen"];

    // Affichage
    (($echanc["num_remb"] % 2==0) ? $retour .= "<TR bgcolor=\"$colb_tableau\">\n" : $retour .= "<TR bgcolor=\"$colb_tableau_altern\">\n");
    $retour .= "<TD align=\"center\">".$echanc["num_remb"]."</TD>\n";
    $retour .= "<TD align=\"center\">".pg2phpDate($echanc["date_remb"])."</TD>\n";
    $retour .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_remb_cap"].'',false)."</TD>\n";
    $retour .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_remb_int"].'',false)."</TD>\n";
    $retour .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_remb_pen"].'',false)."</TD>\n";
    $retour .= "</TR>\n";

  }
  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2>Total</TD>\n";
  $retour .= "<TD align=\"right\">".afficheMontant($total_cap.'',false)."</TD>\n";
  $retour .= "<TD align=\"right\">".afficheMontant($total_int.'',false)."</TD>\n";
  $retour .= "<TD align=\"right\">".afficheMontant($total_pen.'',false)."</TD>\n";
  $retour .= "</TABLE>\n";

  $Myform->addFormButton(1,1,"ok",_("Ok"),TYPB_SUBMIT);
  // Propriétés des boutons
  $Myform->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onClick"=>"window.close();"));

  $Myform->addHTMLExtraCode("rembourse",$retour);
  $Myform->setHTMLExtraCodeProperties("rembourse", HTMP_IN_TABLE, true);
  $Myform->buildHTML();
  echo $Myform->getHTML();
} else {

  $Myform = new HTML_GEN2(_("Aucun remboursement effectué pour cette échéance"));
  // les boutons ajoutés
  $Myform->addFormButton(1,1,"ok",_("Ok"),TYPB_SUBMIT);
  // Propriétés des boutons
  $Myform->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onClick"=>"window.close();"));
  $Myform->buildHTML();
  echo $Myform->getHTML();
}
require("lib/html/HtmlFooter.php");
?>