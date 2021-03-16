<?php


require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/algo/ech_theorique.php';
require_once 'lib/html/echeancier.php';
require_once 'lib/html/suiviCredit.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/mobile_lending.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/excel.php';
require_once 'lib/misc/csv.php';
require_once 'lib/html/HTML_message.php';
require_once "lib/html/HTML_menu_gen.php";

if ($global_nom_ecran == "Mlx-1") {
    global $global_id_agence;
    $data_exist = getDataMlIMF();

    $myForm = new HTML_GEN2(_("Modification des données de l' IMF"));



    if (sizeof($data_exist)>0){
//        $Order = array ("param_normal","interet_salarie","interet_normal","duree_max_salarie","duree_max_normal","remboursement_anticipe_salarie","remboursement_anticipe_normal","lien_contrat","seuil_score_deb","seuil_score_fin","premier_credit_gratuit","palier_montant_max_normal","palier_montant_max_salarie","coef_montant_max","capital_imf","localisation");
        $myForm->addHTMLExtraCode("param_normal", "<table align=\"center\" valign=\"left\" bgcolor=\"" . $colb_tableau . "\"><tr><td><b>"._("Paramétrage du produit de crédit « Mobile Lending Normal » :")."</b></td></tr></table>\n");

        $myForm->addHTMLExtraCode("espace1", "<BR>");
        $myForm->addHTMLExtraCode("param_sal", "<table align=\"center\" valign=\"left\" bgcolor=\"" . $colb_tableau . "\"><tr><td><b>"._("Paramétrage du produit de crédit « Mobile Lending Salarié » :")."</b></td></tr></table>\n");

        $myForm->addHTMLExtraCode("espace2", "<BR>");
        $myForm->addHTMLExtraCode("param_general", "<table align=\"center\" valign=\"left\" bgcolor=\"" . $colb_tableau . "\"><tr><td><b>"._("Paramétrage général de l’IMF :")."</b></td></tr></table>\n");


        $Order = array ("param_normal","interet_normal","duree_max_normal","remboursement_anticipe_normal","premier_credit_gratuit_normal","palier_montant_max_normal","espace1","param_sal","interet_salarie","duree_max_salarie","remboursement_anticipe_salarie","premier_credit_gratuit_salarie","palier_montant_max_salarie","espace2","param_general","coef_montant_max","seuil_score_deb","seuil_score_fin","lien_contrat","capital_imf");

        $exclude = array(
            "localisation");
        $myForm->addTable("ml_imf", OPER_EXCLUDE, $exclude);

        $fill = new FILL_HTML_GEN2();
        $fill->addFillClause ("data_imf", "ml_imf");
        $fill->addCondition("data_imf", "id_ag", $global_id_agence);
        $fill->addManyFillFields("data_imf", OPER_EXCLUDE, $exclude);
        $fill->fill($myForm);

        $myForm->addField("localisation", _("Localisation IMF"), TYPC_LSB);
        $myForm->setFieldProperties("localisation", FIELDP_HAS_CHOICE_AUCUN, false);
        $myForm->setFieldProperties("localisation", FIELDP_DEFAULT,  $data_exist['localisation']);
        $myForm->setFieldProperties("localisation", FIELDP_ADD_CHOICES, $adsys["adsys_localisation_ml"]);
        $myForm->setFieldProperties("localisation", FIELDP_IS_REQUIRED, true);


        // Ajout des boutons
        $myForm->addFormButton(1, 1, "ok", _("Modifier"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Mlx-2');
        $myForm->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-17');
        $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
        $myForm->addFormButton(1, 3, "cancel", _("Retour"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-17');
        $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
        $myForm->setOrder(NULL, $Order);
        $myForm->buildHTML();
        echo $myForm->getHTML();

    }else {
        // Création du formulaire
        $myForm = new HTML_GEN2(_("Renseignement des données de l' IMF"));
        $myForm->addHTMLExtraCode("param_normal", "<table align=\"center\" valign=\"left\" bgcolor=\"" . $colb_tableau . "\"><tr><td><b>"._("Paramétrage du produit de crédit « Mobile Lending Normal » :")."</b></td></tr></table>\n");

        $myForm->addHTMLExtraCode("espace1", "<BR>");
        $myForm->addHTMLExtraCode("param_sal", "<table align=\"center\" valign=\"left\" bgcolor=\"" . $colb_tableau . "\"><tr><td><b>"._("Paramétrage du produit de crédit « Mobile Lending Salarié » :")."</b></td></tr></table>\n");

        $myForm->addHTMLExtraCode("espace2", "<BR>");
        $myForm->addHTMLExtraCode("param_general", "<table align=\"center\" valign=\"left\" bgcolor=\"" . $colb_tableau . "\"><tr><td><b>"._("Paramétrage général de l’IMF :")."</b></td></tr></table>\n");


        $Order = array ("param_normal","interet_normal","duree_max_normal","remboursement_anticipe_normal","premier_credit_gratuit_normal","palier_montant_max_normal","espace1","param_sal","interet_salarie","duree_max_salarie","remboursement_anticipe_salarie","premier_credit_gratuit_salarie","palier_montant_max_salarie","espace2","param_general","coef_montant_max","seuil_score_deb","seuil_score_fin","lien_contrat","capital_imf");

        $exclude = array(
            "localisation");
        $myForm->addTable("ml_imf", OPER_EXCLUDE, $exclude);

        $myForm->addField("localisation", _("Localisation IMF"), TYPC_LSB);
        $myForm->setFieldProperties("localisation", FIELDP_HAS_CHOICE_AUCUN, true);
        $myForm->setFieldProperties("localisation", FIELDP_ADD_CHOICES, $adsys["adsys_localisation_ml"]);
        $myForm->setFieldProperties("localisation", FIELDP_IS_REQUIRED, true);
        $myForm->setOrder(NULL, $Order);
        // Ajout des boutons
        $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Mlx-2');
        $myForm->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-17');
        $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
        $myForm->addFormButton(1, 3, "cancel", _("Précédent"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-17');
        $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
        $myForm->buildHTML();
        echo $myForm->getHTML();
    }
}

else if ($global_nom_ecran == "Mlx-2") {

    global $dbHandler,$global_id_agence;

    //mise a jour interet nn salarie pour les produit de credit Mobile lending
    $data_produit_non_sal = getProdInfo(" WHERE is_mobile_lending_credit = 't' AND crdt_salairie = 'f'");
    $db = $dbHandler->openConnection();
    $interet_normal = $interet_normal/100;
    $array_update = array("tx_interet" => $interet_normal,"duree_max_mois" => $duree_max_normal,"mnt_max" => recupMontant($palier_montant_max_normal));
    $array_condi = array("id" => $data_produit_non_sal[0]['id']);
    $sql = buildUpdateQuery("adsys_produit_credit", $array_update, $array_condi);
    $result = executeDirectQuery($sql);
    if ($result->errCode != NO_ERR){
        $dbHandler->closeConnection(false);
        return new ErrorObj($result->errCode);
    }
    $dbHandler->closeConnection(true);

    //mise a jour interet salarie pour les produit de credit Mobile lending
    $data_produit_non_sal = getProdInfo(" WHERE is_mobile_lending_credit = 't' AND crdt_salairie = 't'");
    $db = $dbHandler->openConnection();
    $interet_salarie =$interet_salarie/100;
    $array_update = array("tx_interet" => $interet_salarie,"duree_max_mois" => $duree_max_salarie,"mnt_max" => recupMontant($palier_montant_max_salarie));
    $array_condi = array("id" => $data_produit_non_sal[0]['id']);
    $sql = buildUpdateQuery("adsys_produit_credit", $array_update, $array_condi);
    $result = executeDirectQuery($sql);
    if ($result->errCode != NO_ERR){
        $dbHandler->closeConnection(false);
        return new ErrorObj($result->errCode);
    }
    $dbHandler->closeConnection(true);

    //Insertion des données dans la table ml_imf
    //Verification des boolean remboursmenet anticipe salarie
    if(isset($remboursement_anticipe_salarie)){
        $remboursement_anticipe_salarie = 't';
    }else{
        $remboursement_anticipe_salarie = 'f';
    }
    //Verification des boolean remboursmenet anticipe non-salarie
    if(isset($remboursement_anticipe_normal)){
        $remboursement_anticipe_normal = 't';
    }else{
        $remboursement_anticipe_normal = 'f';
    }
    //Verification des boolean premier credit gratuit normal
    if(isset($premier_credit_gratuit_normal)){
        $premier_credit_gratuit_normal = 't';
    }else{
        $premier_credit_gratuit_normal = 'f';
    }
    //Verification des boolean premier credit gratuit salarie
    if(isset($premier_credit_gratuit_salarie)){
        $premier_credit_gratuit_salarie = 't';
    }else{
        $premier_credit_gratuit_salarie = 'f';
    }
    //Verification des boolean capital
    if(isset($capital_imf)){
        $capital_imf = 't';
    }else{
        $capital_imf = 'f';
    }


    $data_exist = getDataMlIMF();
    if (sizeof($data_exist)> 0){
        $data_imf = array(
            "interet_salarie" => $interet_salarie,
            "interet_normal" => $interet_normal,
            "duree_max_salarie" => $duree_max_salarie,
            "duree_max_normal" => $duree_max_normal,
            "remboursement_anticipe_salarie" => $remboursement_anticipe_salarie,
            "remboursement_anticipe_normal" => $remboursement_anticipe_normal,
            "lien_contrat" => $lien_contrat,
            "seuil_score_deb" => $seuil_score_deb/100,
            "seuil_score_fin" => $seuil_score_fin/100,
            "premier_credit_gratuit_normal" => $premier_credit_gratuit_normal,
            "premier_credit_gratuit_salarie" => $premier_credit_gratuit_salarie,
            "palier_montant_max_normal" => recupMontant($palier_montant_max_normal),
            "palier_montant_max_salarie" => recupMontant($palier_montant_max_salarie),
            "coef_montant_max" => $coef_montant_max/100,
            "capital_imf" => $capital_imf,
            "localisation" => $localisation,
            "id_ag" => $global_id_agence
        );
        //fonction insert data dans ml_imf
        $insert_data = modifParamIMF($data_imf);
        if ($insert_data->errCode == NO_ERR) {

            $html_msg = new HTML_message("Confirmation de modification des paramètres de l' IMF");

            $html_msg->setMessage(sprintf(" <br /> Les données de l' IMF ont été modifié! <br /> "));

            $html_msg->addButton("BUTTON_OK", 'Gen-17');

            $html_msg->buildHTML();
            echo $html_msg->HTML_code;
        }
    }else {
        $data_imf = array(
            "interet_salarie" => $interet_salarie,
            "interet_normal" => $interet_normal,
            "duree_max_salarie" => $duree_max_salarie,
            "duree_max_normal" => $duree_max_normal,
            "remboursement_anticipe_salarie" => $remboursement_anticipe_salarie,
            "remboursement_anticipe_normal" => $remboursement_anticipe_normal,
            "lien_contrat" => $lien_contrat,
            "seuil_score_deb" => $seuil_score_deb/100,
            "seuil_score_fin" => $seuil_score_fin/100,
            "premier_credit_gratuit_normal" => $premier_credit_gratuit_normal,
            "premier_credit_gratuit_salarie" => $premier_credit_gratuit_salarie,
            "palier_montant_max_normal" => recupMontant($palier_montant_max_normal),
            "palier_montant_max_salarie" => recupMontant($palier_montant_max_salarie),
            "coef_montant_max" => $coef_montant_max/100,
            "capital_imf" => $capital_imf,
            "localisation" => $localisation,
            "id_ag" => $global_id_agence,
            "date_creation" => date('r')
        );
        //fonction insert data dans ml_imf
        $insert_data = ajoutParamIMF($data_imf);
        if ($insert_data->errCode == NO_ERR) {

            $html_msg = new HTML_message("Confirmation d'enregistrement des paramètres de l' IMF");

            $html_msg->setMessage(sprintf(" <br /> Les données de l' IMF ont été enregistré! <br /> "));

            $html_msg->addButton("BUTTON_OK", 'Gen-17');

            $html_msg->buildHTML();
            echo $html_msg->HTML_code;
        }
    }

}
?>