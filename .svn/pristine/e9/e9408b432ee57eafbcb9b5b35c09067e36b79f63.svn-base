<?php

require_once ('lib/dbProcedures/client.php');
require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/agence.php');
require_once ('lib/dbProcedures/tarification.php');
require_once ('lib/dbProcedures/compte.php');
require_once ('lib/dbProcedures/epargne.php');
require_once ('lib/dbProcedures/abonnement.php');
require_once ('lib/dbProcedures/parametrage.php');
require_once ('lib/html/HTML_GEN2.php');
require_once ('lib/html/FILL_HTML_GEN2.php');
require_once 'lib/html/HTML_champs_extras.php';
require_once "modules/rapports/csv_cartes.php";
require_once 'lib/misc/csv.php';


/* Crt-2 : Commande de cartes: choix du compte d'épargne */
if ($global_nom_ecran == "Rgc-1") {

    global $dbHandler, $global_id_agence, $global_id_client;

//    if (isset($SESSION_VARS["id_commande_carte"])) {
//        // Clear session id_abonnement
//        unset($SESSION_VARS["id_commande_carte"]);
//    }
//
//    // On construit la liste des comptes pour lesquels on peut commander une carte
//    $liste_comptes = get_comptes_epargne($global_id_client);
//    $choix = array();
//    if (isset($liste_comptes)) {
//        foreach($liste_comptes as $id_cpte => $infos_cpte) {
//            $infos_prod = getProdEpargne($infos_cpte['id_prod']);
//            $commande_carte = getCommandeCarte($id_cpte);
//
//            if ($infos_prod["classe_comptable"] == '1' && $infos_cpte["etat_cpte"] != '3' && ($commande_carte === null || $commande_carte['date_envoi_impr'] !== null)) {
//                // C'est un compte à vue, il n'est pas bloqué et aucune demande de chèquier n'est en cours
//                $choix[$id_cpte] = $infos_cpte["num_complet_cpte"]." ".$infos_cpte["intitule_compte"];
//            }
//        }
//    }

    $myForm = new HTML_GEN2(_("Rapport gestion cartes"));

    $myForm -> addHTMLExtraCode("ExtraCode", "<br>");
    $table =& $myForm -> addHTMLTable("tb_info", 18,TABLE_STYLE_ALTERN);

//    $table->add_cell(new TABLE_cell(" <h3 id='tb_his'>Historique</h3>", 7, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Request reference number</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Branch code</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Customer first name</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Customer middle name</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Customer last name</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Customer id</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Name on card</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Title</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>ID\passport number</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Resident\Nationality</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Customer type</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Reason for issue</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Account number</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Account type</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Account currency</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Priority</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>Order date</b>"), 1, 1));
    $table -> add_cell(new TABLE_cell(_("<b>User name</b>"), 1, 1));

    $infos = getCommandesCartes();

    if ($infos != NULL) {
        foreach($infos as $key=>$value) {
            $table->add_cell(new TABLE_cell($value['request_ref_num']), 1, 1);
            $table->add_cell(new TABLE_cell($value['branch_code']), 1, 1);
            $table->add_cell(new TABLE_cell($value['first_name']), 1, 1);
            $table->add_cell(new TABLE_cell($value['middle_name']), 1, 1);
            $table->add_cell(new TABLE_cell($value['last_name']), 1, 1);
            $table->add_cell(new TABLE_cell($value['id_client']), 1, 1);
            $table->add_cell(new TABLE_cell($value['nom_carte']), 1, 1);
            $table->add_cell(new TABLE_cell($value['titre']), 1, 1);
            $table->add_cell(new TABLE_cell($value['num_identite_passeport']), 1, 1);
            $table->add_cell(new TABLE_cell($value['resident']), 1, 1);
            $table->add_cell(new TABLE_cell($value['type_client']), 1, 1);
            $table->add_cell(new TABLE_cell($value['reason_for_issue']), 1, 1);
            $table->add_cell(new TABLE_cell($value['id_cpte']), 1, 1);
            $table->add_cell(new TABLE_cell($value['type_compte']), 1, 1);
            $table->add_cell(new TABLE_cell($value['devise']), 1, 1);
            $table->add_cell(new TABLE_cell($value['priorite']), 1, 1);
            $table->add_cell(new TABLE_cell(pg2phpDate($value['date_cmde']), 1, 1));
            $table->add_cell(new TABLE_cell($value['guichet']), 1, 1);
        }
    }

//    $myForm->addFormButton(1, 1, "ok", _("Afficher Historique"), TYPB_BUTTON);
//    $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
//    $myForm->setFormButtonProperties("ok", BUTP_CHECK_FORM, false);
//    $myForm->buildHTML();
//    echo $myForm->getHTML();

    $myForm->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Rgc-2");
    $myForm->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
    $myForm->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
    $myForm->show();

//    // Création du formulaire
//    $my_page = new HTML_GEN2(_("Commande de cartes: choix du compte d'épargne"));
//    $my_page->addField("num_cpte", _("Numéro de compte"), TYPC_LSB);
//    $my_page->setFieldProperties("num_cpte", FIELDP_ADD_CHOICES, $choix);
//    $my_page->setFieldProperties("num_cpte",FIELDP_IS_REQUIRED, true);
//
//    // Boutons
//    $my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
//    $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Crt-3");
//    $my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
//    $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Abn-1");
//    $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
//    $my_page->show();

}
/*}}}*/

/*{{{ Rgc-2 : export csv */
else if ($global_nom_ecran == "Rgc-2") {

//        $result = doCommandeChequier($SESSION_VARS["id_cpte"], $nbre_carnets, recupMontant($frais_chequier));
    $cartes = getCommandesCartes();

    if (count($cartes) > 0) {
        // Nous avons des chèquiers à imprimer
        $result = csvCartesIndigo($cartes);
        $datacsv = $result->param;
//        $result = setAttenteImpressionChequier(array_keys($cartes));
//        if ($result->errCode == NO_ERR) {
            $result = doWriteCSV($datacsv);
            if ($result->errCode == NO_ERR) {
                echo getShowCSVHTML("Gen-6", $result->param);
                ajout_historique(430, NULL, NULL, $global_nom_login, date("r"), NULL);
            }
//        }
        if ($result->errCode != NO_ERR) {
            $my_page = new HTML_erreur(_("Echec lors de l'export des demandes de cartes"));
            $my_page->setMessage(_("Erreur : ") . $error[$result->errCode] . "<br />"._("Paramètre : ") . $result->param);
            $my_page->addButton("BUTTON_OK", 'Gen-6');
            $my_page->show();
        }
    } else {
        // Aucun chèquier ne doit être imprimé
        $my_page = new HTML_message(_("Aucune demande de carte"));
        $my_page->setMessage(_("Aucun demande de cartes n'a été trouvée l'impression."), true);
        $my_page->addButton("BUTTON_OK", 'Gen-6');
        $my_page->show();
    }
//    if ($result->errCode == NO_ERR) {
//        $my_page =new HTML_message(_("Confirmation commande"));
//        $my_page->setMessage(sprintf(_("La commande d'une nouvelle carte a été introduite. Numéro de référence: "),$nbr_cheques),true);
//        $my_page->addButton("BUTTON_OK", 'Gen-4');
//    } else {
//        $my_page = new HTML_erreur(_("Echec lors de la commande d'un nouveau chèquier "));
//        $my_page->setMessage(_("Erreur")." : ".$error[$result->errCode]."<br />"._("Paramètre")." : ".$result->param);
//        $my_page->addButton("BUTTON_OK", 'Chq-1');
//    }
//    $my_page->show();
} /*}}}*/