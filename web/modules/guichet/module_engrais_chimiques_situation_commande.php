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
require_once 'lib/dbProcedures/historique.php' ;



$id_commande = $_GET["id_comm"];
$id_benef = $_GET["id_benef"];

$myForm = new HTML_GEN2();
$myForm->setTitle(_("Details Commande"));

$condi = "id_benef=".$id_benef." and id_commande=".$id_commande;
$commande_actif= getCommande($condi);

while (list($key, $COM) = each($commande_actif)) {
  $id_comm = $COM['id_commande'];
  // select depuis la table année/saison
  $condi1 = "";
  $annee_agri_actuelle =getRangeDateAnneeAgri($condition_annee_agri);

  $condi2 = "id_saison = ".$COM['id_saison'];
  $saison_cultu_acutelle = getDetailSaisonCultu($condi2);

  $myForm->addField("commande".$COM['id_commande'],_("Commande numéro"), TYPC_TXT);
  $myForm->setFieldProperties("commande".$COM['id_commande'], FIELDP_DEFAULT,$COM['id_commande']);
  $myForm->setFieldProperties("commande".$COM['id_commande'], FIELDP_IS_LABEL, true);
  $myForm->addField("saison".$COM['id_commande'],_("Saison"), TYPC_TXT);
  $myForm->setFieldProperties("saison".$COM['id_commande'], FIELDP_DEFAULT, $saison_cultu_acutelle['nom_saison']);
  $myForm->setFieldProperties("saison".$COM['id_commande'], FIELDP_IS_LABEL, true);
  $myForm->addField("mnt_tot".$COM['id_commande'],_("Montant total"), TYPC_MNT);
  $myForm->setFieldProperties("mnt_tot".$COM['id_commande'], FIELDP_DEFAULT, $COM['montant_total']);
  $myForm->setFieldProperties("mnt_tot".$COM['id_commande'], FIELDP_IS_LABEL, true);
  $myForm->addField("mnt_depose".$COM['id_commande'],_("Montant Avance Payé"), TYPC_MNT);
  $myForm->setFieldProperties("mnt_depose".$COM['id_commande'], FIELDP_DEFAULT, $COM['montant_depose']);
  $myForm->setFieldProperties("mnt_depose".$COM['id_commande'], FIELDP_IS_LABEL, true);
  $myForm->addField("etat_comm" . $COM['id_commande'], _("Etat commande"), TYPC_TXT);
  $myForm->setFieldProperties("etat_comm" . $COM['id_commande'], FIELDP_DEFAULT, adb_gettext($adsys['adsys_etat_commande'][$COM['etat_commande']]));
  $myForm->setFieldProperties("etat_comm" . $COM['id_commande'], FIELDP_IS_LABEL, true);


  $xtHTML = "<br /><table align=\"center\" cellpadding=\"5\" width=\"80% \" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
    <tr align=\"center\" bgcolor=\"$colb_tableau\"><TD colspan=5 align=\"left\"><b>" . _("Details de la commande") . "</b></TD></TR><TR><th>"._("Produit")."</th><th>"._("Quantité")."</th><th>"._("Prix unitaire")."</th><th>"._("Montant avance paye")."</th><th>"._("Total")."</th></tr>";
  $condi3="id_commande=".$id_comm;
  $commande_detail_actif =getCommandeDetail($condi3);


  while (list($key1, $DET) = each($commande_detail_actif)) {
    $id_detail = $DET['id_detail'];
    $condi4="id_produit=".$DET['id_produit'];
    $id_prod=$DET['id_produit'];
    $libel_produit = getListeProduitPNSEB($condi4,true);
    if($libel_produit[$id_prod]['prix_unitaire']==null){
      $prix_unitaire="(non renseigné) ";
    }
    else{
      $prix_unitaire = afficheMontant($libel_produit[$id_prod]['prix_unitaire'],true);
    }
    if($DET['prix_total']==null){
      $prix_total = "(non renseigné)";
    }else{
      $prix_total = afficheMontant($DET['prix_total'],true);
    }
    $xtHTML .= "\n<tr bgcolor=\"$color\"><td>".$libel_produit[$id_prod]['libel']."</td><td>".$DET['quantite']."</td><td>".$prix_unitaire."</td><td>".afficheMontant($DET['montant_depose'],true)."</td><td>".$prix_total."</td></tr>";

  }
  $xtHTML .= "</table><br /><br/><br />";


  $myForm->addHTMLExtraCode("xtHTML".$id_comm, $xtHTML);
}

$condi8="id_commande =".$id_commande."order by id_remb asc";
$paiement_commande = getPaiementDetail($condi8);
$id_remb_max=0;
if ($paiement_commande != NULL){
  $remb_tab = "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=0  border=$tableau_border >\n";
  $remb_tab .= "<TR bgcolor=\"$colb_tableau\">\n";
  $remb_tab .= "<TD colspan=7 align=\"left\"><b>" . _("Recapitulatif des paiements") . "</b></TD>\n";
  $remb_tab .= "</TR>\n";
  $remb_tab .= "<TR bgcolor=\"$colb_tableau\">\n";
  $remb_tab .= "<TD align=\"center\">" . _("Numéro paiement") . "</TD>\n";
  $remb_tab .= "<TD align=\"center\">" . _("Date paiement") . "</TD>\n";
  $remb_tab .= "<TD align=\"center\">" . _("Produits") . "</TD>\n";
  $remb_tab .= "<TD align=\"center\">" . _("Montant payé") . "</TD>\n";
  $remb_tab .= "<TD align=\"center\">" . _("Quantité payé") . "</TD>\n";
  $remb_tab .= "<TD align=\"center\">" . _("Etat paiement") . "</TD>\n";
  $remb_tab .= "<TD align=\"center\">" . _("Bon achat distribué") . "</TD>\n";
  $remb_tab .= "</TR>\n";

  $row_counter = 0;

  $mnt_ = $total_attendu;
  while (list($key1, $DET1) = each($paiement_commande)) {
    $row_counter++;
    $id_remb = $DET1['id_remb'];
    $date_paiement = $DET1['date_creation'];
    $mnt_paye = $DET1['montant_paye'];
    //$mnt_restant = $total_attendu - $mnt_paye;
    $mnt_ -= $mnt_paye;
    $id_remb_max = $DET1['id_remb'];
    $detail_condi = "id_detail = ".$DET1['id_detail_commande'];
    $detail_commande = getCommandeDetail($detail_condi);
    foreach($detail_commande as $key_prod => $value_prod) {
      $condi_prod = "id_produit = " . $value_prod['id_produit'];
      $details_produit = getDetailsProduits($condi_prod);
      $libel_prod = $details_produit['libel'];
    }

    // Affichage
    $remb_tab .= "<TR bgcolor=\"$colb_tableau\">\n";
    $remb_tab .= "<TD align=\"center\">" . $id_remb . "</TD>\n";
    $remb_tab .= "<TD align=\"left\">" . pg2phpDate($date_paiement) . "</TD>\n";
    $remb_tab .= "<TD align=\"right\">" . $libel_prod . "</TD>\n";
    $remb_tab .= "<TD align=\"right\">" . afficheMontant($mnt_paye, true) . "</TD>\n";
    $remb_tab .= "<TD align=\"right\">" . $DET1['qtite_paye'] . "</TD>\n";
    $remb_tab .= "<TD align=\"right\">" .adb_gettext($adsys["adsys_etat_commande_detail"][$DET1['etat_paye']]). "</TD>\n";
    $remb_tab .= "<TD align=\"right\">" .$DET1['bon_achat']. "</TD>\n";
    $remb_tab .= "</TR>\n";

  }
}
else {
  $myForm->addHTMLExtraCode("espace" . $id_remb, "<br /><b><p align=center><b>" . sprintf(_("Aucun paiement associé à la commande %s"), $id_commande) . "</b></p>");
}
$remb_tab .= "</TABLE>\n";
$myForm->addHTMLExtraCode("remb".$row_counter, $remb_tab);
$myForm->setHTMLExtraCodeProperties("remb".$row_counter, HTMP_IN_TABLE, true);



$myForm->buildHTML();
echo $myForm->getHTML();

?>