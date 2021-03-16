<?php

/**
 * [162] Gestion des chèques certifiés
 *
 * Cette opération comprends les écrans :
 * - Gcc-1 : Liste des chèques certifiés
 * - Gcc-2 : Ajout chèque certifié
 * - Gcc-3 : Confirmation chèque certifié
 * - Gcc-4 : Modification chèque certifié
 *
 * @package Chèques certifiés
 *
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/cheque_interne.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';

if ($global_nom_ecran == "Gcc-1") {

    $MyPage = new HTML_GEN2("Synthèse des chèques certifiés");

    $nb_cheques_certifies_non_traite = ChequeCertifie::getNbChequeCertifie(ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE);
    $nb_cheques_certifies_traite = ChequeCertifie::getNbChequeCertifie(ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TRAITE);
    $nb_cheques_certifies_restitue = ChequeCertifie::getNbChequeCertifie(ChequeCertifie::ETAT_CHEQUE_CERTIFIE_RESTITUEE);

    $MyPage->addField("nb_cheques_certifies_non_traite", "Nombre de chèques certifiés non-traité", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_certifies_non_traite", FIELDP_DEFAULT, $nb_cheques_certifies_non_traite);
    $MyPage->setFieldProperties("nb_cheques_certifies_non_traite", FIELDP_IS_LABEL, true);

    $MyPage->addField("nb_cheques_certifies_traite", "Nombre de chèques certifiés traité", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_certifies_traite", FIELDP_DEFAULT, $nb_cheques_certifies_traite);
    $MyPage->setFieldProperties("nb_cheques_certifies_traite", FIELDP_IS_LABEL, true);

    $MyPage->addField("nb_cheques_certifies_restitue", "Nombre de chèques certifiés restitué", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_certifies_restitue", FIELDP_DEFAULT, $nb_cheques_certifies_restitue);
    $MyPage->setFieldProperties("nb_cheques_certifies_restitue", FIELDP_IS_LABEL, true);

    $MyPage->addHTMLExtraCode("htm1", "<br />");

    //Boutons
    $MyPage->addFormButton(1, 1, "ajout", _("Ajouter un chèque certifié"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, "Gcc-2");
    $MyPage->setFormButtonProperties("ajout", BUTP_CHECK_FORM, false);
    $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gen-6");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    //HTML
    $MyPage->buildHTML();
    echo $MyPage->getHTML();

} elseif ($global_nom_ecran == "Gcc-2") {

    $MyPage = new HTML_GEN2("Ajout d'une chèque certifié");

    $MyPage->addField("num_cheque", _("Numéro du chèque"), TYPC_INT);
    $MyPage->setFieldProperties("num_cheque", FIELDP_IS_REQUIRED, true);

    $MyPage->addField("date_cheque", _("Date du chèque"), TYPC_DTE);
    $MyPage->setFieldProperties("date_cheque", FIELDP_DEFAULT, date("d/m/Y"));
    $MyPage->setFieldProperties("date_cheque", FIELDP_IS_REQUIRED, true);

    $MyPage->addField("montant_cheque", _("Montant du chèque"), TYPC_MNT);
    $MyPage->setFieldProperties("montant_cheque", FIELDP_IS_REQUIRED, true);

    $MyPage->addField("nom_benef",_("Nom du bénéficiaire"), TYPC_TXT);
    $MyPage->setFieldProperties("nom_benef", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("nom_benef", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("nom_benef", FIELDP_WIDTH, 40);
    $MyPage->addLink("nom_benef", "rechercher_nom_benef", _("Rechercher"), "#");
    $MyPage->setLinkProperties("rechercher_nom_benef", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_benef&field_id=id_benef&type=b', '"._("Recherche")."');return false;"));
    $MyPage->addHiddenType("id_benef", "");
    $checkJS = "if (document.ADForm.id_benef.value == '')
             {
               msg += '- "._("Le champ \'Nom du bénéficiaire\' doit être renseigné")."\\n';
               ADFormValid=false;
             }";

    $MyPage->addField("cpt_cli", _("Compte client associé"), TYPC_TXT);
    $MyPage->setFieldProperties("cpt_cli", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("cpt_cli", FIELDP_IS_LABEL, true);
    $MyPage->addLink("cpt_cli", "rechercher_cpt_cli", _("Rechercher"), "#");
    $MyPage->setLinkProperties("rechercher_cpt_cli", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=" . $_REQUEST['m_agc'] . "&choixCompte=1&cpt_dest=cpt_cli&id_cpt_dest=num_cpte_cli','" . _("Recherche") . "');return false;"));
    $MyPage->addHiddenType("num_cpte_cli", "");
    $checkJS .= "if (document.ADForm.num_cpte_cli.value == '')
             {
               msg += '- "._("Le champ \'Compte client associé\' doit être renseigné")."\\n';
               ADFormValid=false;
             }";

    $list_etat_cheque = array(ChequeCertifie::ETAT_CHEQUE_CERTIFIE_NON_TRAITE => _("Non traité"), ChequeCertifie::ETAT_CHEQUE_CERTIFIE_TRAITE => _("Traité"));
    $MyPage->addField("etat_cheque", _("Etat du chèque"), TYPC_LSB);
    $MyPage->setFieldProperties("etat_cheque", FIELDP_ADD_CHOICES, $list_etat_cheque);
    $MyPage->setFieldProperties("etat_cheque", FIELDP_HAS_CHOICE_AUCUN, false);
    $MyPage->setFieldProperties("etat_cheque", FIELDP_HAS_CHOICE_TOUS, false);
    $MyPage->setFieldProperties("etat_cheque", FIELDP_IS_LABEL, true);

    //$MyPage->addField("date_traitement", _("Date de traitement"), TYPC_DTE);

    $chqValidite = getValidityChequeDate();
    $chqCertVal = $chqValidite['validite_chq_cert'];

    // Set field "Compte client associé" as readOnly
    /*
    $jsFormChq = "document.ADForm.cpt_cli.readOnly = true;";

    $MyPage->addJS(JSP_FORM, "JS_FORM_CHQ", $jsFormChq);
    */

    // Validation date validité chèques
    $checkJS .= "
                  function validChqDate(){
                    var date_chq = document.ADForm.HTML_GEN_date_date_cheque;

                      if(date_chq.value != '')
                      {
                          var now = \"" . date("d/m/Y") . "\";
                          var isValid = checkDateRange($chqCertVal,date_chq.value,now);

                          if(!isValid){
                            alert ('La validité du chèque dépasse le nombre de jours autorisé!');
                            ADFormValid=false;
                          }
                      }
                  }
                  validChqDate();
    ";

    $MyPage->addJS(JSP_BEGIN_CHECK, "JS_CHQ", $checkJS);

    //Boutons
    $MyPage->addFormButton(1, 1, "valider", _("Enregistrer"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Gcc-3");
    $MyPage->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Gcc-1");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    //HTML
    $MyPage->buildHTML();
    echo $MyPage->getHTML();

} elseif ($global_nom_ecran == "Gcc-3") {

    $erreur = ChequeCertifie::processChequeCertifie($num_cheque, $montant_cheque, $id_benef, $num_cpte_cli, $date_cheque);

    if ($erreur->errCode == NO_ERR) {

        $dbHandler->closeConnection(true);

        // Affichage de la confirmation
        $html_msg = new HTML_message("Confirmation chèque certifié");

        $html_msg->setMessage("<br />Un nouveau chèque certifié a été créé avec succès !<br />");

        $html_msg->addButton("BUTTON_OK", 'Gcc-1');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;
    } else {
        $html_err = new HTML_erreur("Echec lors de la création d'un chèque certifié.");

        if ($erreur->errCode == ERR_SOLDE_INSUFFISANT) {
            $err_msg = "Le solde du compte client est insuffisant";
        } else {
            $err_msg = $error[$erreur->errCode];
            //$err_msg = "L'opération ne s'est pas correctement terminée";
        }

        $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Gcc-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }

} elseif ($global_nom_ecran == "Gcc-4") {

    $MyPage = new HTML_GEN2("Modification chèque certifié");

    $MyPage->buildHTML();
    echo $MyPage->getHTML();

} else {
    signalErreur(__FILE__, __LINE__, __FUNCTION__);
    // _("L'écran $global_nom_ecran n'existe pas")
}
