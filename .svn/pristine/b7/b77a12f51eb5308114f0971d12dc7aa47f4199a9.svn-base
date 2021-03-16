<?php
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/parametrage.php');
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/engrais_chimiques.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';
require_once "lib/html/HTML_menu_gen.php";
require_once 'modules/guichet/recu_modules_specifiques.php';
require_once 'lib/dbProcedures/historique.php';

$id_commande = $_GET["id_comm"];

$myForm = new HTML_GEN2();
$myForm->setTitle(_("Details derogation"));

$commande_actif= getCommande("id_commande=".$id_commande);

$xtHTML = "<br /><table align=\"center\" cellpadding=\"5\" width=\"65% \" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
    <tr align=\"center\" bgcolor=\"$colb_tableau\"><th>"._("Produit")."</th><th>"._("Quantite")."</th><th>"._("Montant avance paye")."</th></tr>";
$condi3="id_commande=".$id_commande;
$commande_detail_actif =getCommandeDetail($condi3);


while (list($key1, $DET) = each($commande_detail_actif)) {
  $id_detail = $DET['id_detail'];
  $condi4="id_produit=".$DET['id_produit'];
  $id_prod=$DET['id_produit'];
  $libel_produit = getListeProduitPNSEB($condi4,true);

  $xtHTML .= "\n<tr bgcolor=\"$color\"><td>".$libel_produit[$id_prod]['libel']."</td><td>".$DET['quantite']."</td><td>".afficheMontant($DET['montant_depose'])."</td></tr>";

}
$xtHTML .= "</table><br /><br/><br />";


$myForm->addHTMLExtraCode("xtHTML".$id_comm, $xtHTML);


$myForm->buildHTML();
echo $myForm->getHTML();
?>