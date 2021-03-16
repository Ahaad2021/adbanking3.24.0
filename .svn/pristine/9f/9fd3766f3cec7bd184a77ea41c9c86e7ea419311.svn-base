<?php

/**
 * [63] Effectuer annulation retrait et dépôt
 *
 * Cette opération comprends les écrans :
 * - Eae-1 : Liste des demandes d''annulation retraits / dépôts autorisé
 * - Eae-2 : Confirmation annulation retraits / dépôts
 *
 * @package Annulation Retrait et Dépôt
 *
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/annulation_retrait_depot.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';


if ($global_nom_ecran == "Eae-1") {

    global $global_id_client;

    // Affichage de la liste des annulations autorisées
    $table = new HTML_TABLE_table(6, TABLE_STYLE_ALTERN);
    $table->set_property("title", "Liste des demandes d'annulations autorisées");
    $table->add_cell(new TABLE_cell("N°"));
    $table->add_cell(new TABLE_cell("Opération"));
    $table->add_cell(new TABLE_cell("Type"));
    $table->add_cell(new TABLE_cell("Montant"));
    $table->add_cell(new TABLE_cell("Frais"));
    $table->add_cell(new TABLE_cell(""));

    // Get liste des demandes d'annulations autorisées
    $listeDemandeAnnulationAutorise = AnnulationRetraitDepot::getListeDemandeAnnulation($global_id_client, AnnulationRetraitDepot::ETAT_ANNUL_AUTORISE);

    foreach ($listeDemandeAnnulationAutorise as $id => $annulationAutorise) {

        $id_demande = trim($annulationAutorise["id"]);
        $libel_fonc = AnnulationRetraitDepot::getLibelFonc($annulationAutorise["fonc_sys"]);
        $libel_ope = AnnulationRetraitDepot::getLibelOpe($annulationAutorise["type_ope"]);
        $devise = $annulationAutorise["devise"];
        $montant = afficheMontant($annulationAutorise["montant"])." ".$devise;
        $frais = afficheMontant($annulationAutorise["frais"])." ".$devise;

        $table->add_cell(new TABLE_cell($id_demande));
        $table->add_cell(new TABLE_cell($libel_fonc));
        $table->add_cell(new TABLE_cell($libel_ope));
        $table->add_cell(new TABLE_cell($montant));
        $table->add_cell(new TABLE_cell($frais));
        $table->add_cell(new TABLE_cell("<a href=\"javascript:void(0);\" onclick=\"return submitFormData($id_demande);\">Effectuer l'annulation</a>"));
        $table->set_row_property("height","35px");
    }

    // Génération du tableau des demandes d'annulations autorisées
    echo $table->gen_HTML();

    $myPage = new HTML_GEN2("");

    $myPage->addHiddenType("hdd_id_demande");

    $code_js = "
                  function submitFormData(id) {

                      if (confirm(\"Annuler l'opération ?\")) {
                            document.ADForm.hdd_id_demande.value = id;
                            document.ADForm.prochain_ecran.value = 'Eae-2';
                            if(document.ADForm.m_agc) {
                                document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';
                            }

                            document.ADForm.submit();
                      }

                      return false;
                  }
        ";

    $myPage->addJS(JSP_FORM, "JS_CODE", $code_js);

    $myPage->show();

} elseif ($global_nom_ecran == "Eae-2") {

    global $global_id_client;

    $erreur = AnnulationRetraitDepot::processApprobationAnnulation($_POST['hdd_id_demande'], $global_id_client);

    if ($erreur->errCode == NO_ERR) {

        // Affichage de la confirmation
        $html_msg = new HTML_message("Confirmation annulation");

        $demande_msg = "L'annulation a été effectuée !";

        $html_msg->setMessage($demande_msg);

        $html_msg->addButton("BUTTON_OK", 'Gen-10');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
    } else {
        $html_err = new HTML_erreur("Echec lors de l'annulation de retrait / dépôt.");

        $err_msg = $error[$erreur->errCode];

        $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Gen-10');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }

} else {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    // _("L'écran $global_nom_ecran n'existe pas")
}