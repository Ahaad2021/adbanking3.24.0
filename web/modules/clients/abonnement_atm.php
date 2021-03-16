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
require_once ('lib/dbProcedures/carte_atm.php');



if ($global_nom_ecran == "Abt-1") {

    global $dbHandler, $global_id_agence, $global_id_client;

    if (isset($SESSION_VARS["id"])) {
        // Clear session id_abonnement
        unset($SESSION_VARS["id"]);
    }

    $MyPage = new HTML_GEN2("Gestion des abonnements ATM");

    $liste_abonnements = getListAbonnementATM();

    $num_complet_cpte_arr = array();
    foreach($liste_abonnements as $abonnement){
        $num_complet_cpte_arr[$abonnement['id_cpte']] = $abonnement['num_complet_cpte']." ".$abonnement['intitule_compte'];
    }

    //javascript
    if (count($num_complet_cpte_arr) > 0) {
        $js_vaid .= "document.ADForm.modif.disabled = true;\n";
//        $js_vaid .= "document.ADForm.supr.disabled = true;\n";
    }
    $js_vaid .= "function activateButtons(){\n";
    $js_vaid .= "activate = (document.ADForm.HTML_GEN_LSB_id_abonnement_atm.value != 0);";
    $js_vaid .= "document.ADForm.modif.disabled = !activate;";
//    $js_vaid .= "document.ADForm.supr.disabled = !activate;";
    $js_vaid .= "}\n";
    $MyPage->addJS(JSP_FORM, "js_vaid", $js_vaid);

    $MyPage->addField("abonnement_atm", "Abonnements ATM", TYPC_LSB);
    $MyPage->setFieldProperties("abonnement_atm", FIELDP_HAS_CHOICE_AUCUN, true);

    if (count($num_complet_cpte_arr) > 0) {
        $MyPage->setFieldProperties("abonnement_atm", FIELDP_ADD_CHOICES, $num_complet_cpte_arr);
    }

    $MyPage->setFieldProperties("abonnement_atm", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("abonnement_atm", FIELDP_SHORT_NAME, "id_abonnement_atm");
    $MyPage->setFieldProperties("id_abonnement_atm", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();"));

    if (count($num_complet_cpte_arr) > 0) {
        // Bouton modifier
        $MyPage->addButton("id_abonnement_atm", "modif", "Modifier", TYPB_SUBMIT);
        $MyPage->setButtonProperties("modif", BUTP_PROCHAIN_ECRAN, "Abt-3");
    }

    //$availablePrestataire = getAvailablePrestataire();
    $availableServices = getAvailableServices();

    if($availableServices == null) { //  && $availablePrestataire == null
        $MyPage->addHTMLExtraCode("htm1", "<span id='info'><br /><center style=\"font:12pt arial;\"><strong style=\"color:#FF0000;\">*</strong> Vous ne pouvez plus créer des nouveaux services</center></span>");
    } else {
        // Bouton créer
        $MyPage->addFormButton(1, 1, "cree", "Créer un abonnement ATM", TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("cree", BUTP_PROCHAIN_ECRAN, "Abt-2");
        $MyPage->setFormButtonProperties("cree", BUTP_CHECK_FORM, false);

        $MyPage->addHTMLExtraCode("htm1", "<span id='info'></span>");
    }

    //HTML
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Abt-2") {
    global $dbHandler, $global_id_agence, $global_id_client;

    $MyPage = new HTML_GEN2("Inscription abonnements ATM");
    $html_jquery = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $MyPage->addHTMLExtraCode("jquery_link", $html_jquery);

    $id_cpte = getAbonnementATM();
    $id_cpte = implode(", ", $id_cpte[$global_id_client]);
    if(!empty($id_cpte)){
        $accounts = getAccountClient($global_id_client, "c.id_cpte NOT IN ($id_cpte)");
    }else{
        $accounts = getAccountClient($global_id_client);
    }

    $cpt_client = get_comptes_epargne($global_id_client);
    $libel_cpt = array();

    foreach($cpt_client as $cpt){
        $libel_cpt[$cpt['id_cpte']] = $cpt['libel'];
    }

    $MyPage->addField("compte", "Compte", TYPC_LSB);
    $MyPage->setFieldProperties("compte", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("compte", FIELDP_ADD_CHOICES, $accounts);

    $MyPage->addField("libel", "Libellé du produit d'épargne", TYPC_TXT);
    $MyPage->setFieldProperties("libel", FIELDP_DEFAULT, null);
    $MyPage->setFieldProperties("libel", FIELDP_IS_LABEL,true) ;


    $MyPage->addField("id_carte", "Id Carte", TYPC_TXT);
    $MyPage->setFieldProperties("id_carte", FIELDP_DEFAULT, null);
    $MyPage->setFieldProperties("id_carte", FIELDP_IS_LABEL,true) ;

    $MyPage->addField("num_carte", "Numéro carte", TYPC_TXT);
    $MyPage->setFieldProperties("num_carte", FIELDP_DEFAULT, null);
    $MyPage->setFieldProperties("num_carte", FIELDP_IS_LABEL,true );

    $etat = 1;
    $MyPage->addField("statut", "Statut", TYPC_TXT);
    $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, adb_gettext($adsys["adsys_etat_dossier_credit"][$etat]));
    $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);
    $MyPage->addHiddenType("statut_num", $etat);

    $MyPage->addFormButton(1, 1, "valider", "Valider", TYPB_BUTTON);
//    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Abt-4");
//    $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array('onclick' => 'checkfield();') );

    $MyPage->addFormButton(1, 2, "annul", "Annuler", TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Abt-1");

    $js = "
        $('[name=HTML_GEN_LSB_compte]').change(function(){
            var dataset = JSON.parse('".addslashes(json_encode($libel_cpt))."');
            $('[name=libel]').val(dataset[$(this).val()]);
        });
        
        function checkfield(){
            if ($('[name=HTML_GEN_LSB_compte]').val()== 0){
                alert('Le numero de compte doit être renseigné')
            }
            else{
                assign('Abt-4');
                document.ADForm.submit();
            }
        }
        
    ";

    //HTML
    $MyPage->addJS(JSP_FORM, "js_change_val", $js);
    $MyPage->buildHTML();
    echo $MyPage->getHTML();

}
else if ($global_nom_ecran == "Abt-3") {
    global $dbHandler, $global_id_agence, $global_id_client;

    $MyPage = new HTML_GEN2("Modification abonnements ATM");
    $html_jquery = "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $MyPage->addHTMLExtraCode("jquery_link", $html_jquery);

    $liste_abonnements = getListAbonnementATM();

    $cpt_client = get_comptes_epargne($global_id_client);
    $libel_cpt = array();

    foreach($cpt_client as $cpt){
        $libel_cpt[$cpt['id_cpte']] = $cpt['libel'];
    }

    $ref = $liste_abonnements[$id_abonnement_atm];
    $num_cpt = $ref['num_complet_cpte']." ".$ref['intitule_compte'];
    $MyPage->addField("compte", "Compte", TYPC_TXT);
    $MyPage->setFieldProperties("compte", FIELDP_DEFAULT, $num_cpt);
    $MyPage->setFieldProperties("compte", FIELDP_IS_REQUIRED, true);
    $MyPage->addHiddenType("compte_id", $id_abonnement_atm);

    $MyPage->addField("libel", "Libellé du produit d'épargne", TYPC_TXT);
    $MyPage->setFieldProperties("libel", FIELDP_DEFAULT, null);
    $MyPage->setFieldProperties("libel", FIELDP_IS_LABEL,true) ;

    $MyPage->addField("id_carte", "Id Carte", TYPC_TXT);
    $MyPage->setFieldProperties("id_carte", FIELDP_DEFAULT, $ref['id_carte']);
    $MyPage->setFieldProperties("id_carte", FIELDP_IS_LABEL,true) ;
    $MyPage->addHiddenType("id_carte_hidden", $ref['id_carte']);

    $array_split = str_split($ref['num_carte_atm'],4);
    $num_atm = implode(" ",$array_split);
    $MyPage->addField("num_carte", "Numéro carte", TYPC_TXT);
    $MyPage->setFieldProperties("num_carte", FIELDP_DEFAULT, $num_atm);
    $MyPage->setFieldProperties("num_carte", FIELDP_IS_LABEL,true );

    $MyPage->addField("statut", "Statut", TYPC_LSB);
    $MyPage->setFieldProperties("statut", FIELDP_HAS_CHOICE_AUCUN,  FALSE);
    $MyPage->setFieldProperties("statut", FIELDP_ADD_CHOICES, $adsys["statut_abonnement_atm"]);

    $MyPage->addField("motif", "Motif", TYPC_LSB);
    $MyPage->setFieldProperties("motif", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("motif", FIELDP_DEFAULT, $liste_abonnements[$id_abonnement_atm]['motif_suspension']);
    $MyPage->setFieldProperties("motif", FIELDP_ADD_CHOICES, $adsys["motif_inactif"]);

    $MyPage->addFormButton(1, 1, "valider", "Valider", TYPB_BUTTON);
    $MyPage->setFormButtonProperties("valider", BUTP_JS_EVENT, array('onclick' => 'check_motif()'));
//    $MyPage->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Abt-4");
//    $MyPage->setFormButtonProperties("valider", BUTP_CHECK_FORM, false);

    $MyPage->addFormButton(1, 2, "annul", "Annuler", TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Abt-1");

    $js_disable = "
        document.ADForm.compte.readOnly = true;
        document.ADForm.HTML_GEN_LSB_statut.selectedIndex  = ".--$liste_abonnements[$id_abonnement_atm]['statut'].";
        
        var dataset = JSON.parse('".addslashes(json_encode($libel_cpt))."');
        $('[name=libel]').val(dataset[".$id_abonnement_atm."]);
        
        if($('[name=id_carte]').val() == ''){
            $('[name=HTML_GEN_LSB_statut]').prop('disabled', true);
            $('[name=HTML_GEN_LSB_motif]').prop('disabled', true);
        }
        
        $('[name=HTML_GEN_LSB_statut]').find('option:nth-child(1)').css('display', 'none');
        $('[name=HTML_GEN_LSB_motif]').prop('disabled', !($('[name=HTML_GEN_LSB_statut]').val() == 3))
        $('[name=HTML_GEN_LSB_statut]').change(function(){
             $('[name=HTML_GEN_LSB_motif]').prop('disabled', !($(this).val() == 3));
        });
        
        function check_motif(){
            assign('Abt-4');
            if($('[name=HTML_GEN_LSB_statut]').val() == 3){
                if($('[name=HTML_GEN_LSB_motif]').val() == 0){
                    alert('- Le champ Motif ne doit pas être vide');
                }else{
                    $(document.ADForm).submit();
                }
            }else{
                $(document.ADForm).submit();
            }
        }
    ";

    $MyPage->addJS(JSP_FORM, "diable_input", $js_disable);
    //HTML
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
}
else if ($global_nom_ecran == "Abt-4") {
    global $global_id_client, $global_id_agence, $global_nom_ecran_prec, $error;

    if($global_nom_ecran_prec == "Abt-2") {
        $DATA['id_cpte'] = $compte;
        $DATA['id_client'] = $global_id_client;
        $DATA['id_ag'] = $global_id_agence;
        $DATA['date_creation'] = date('r');
        $DATA['statut'] = $statut_num;
        $DATA['identifiant_client'] = generateIdentifiant();

        $err = insertAbonnementAtm($DATA);
        if ($err->errCode == NO_ERR) {
            // Prélève frais abonnement
            $erreur = preleveFraisAbonnementATM('ATM_REG', $global_id_client, 180);
            if($erreur->errCode == NO_ERR){

                $myForm = new HTML_message(_("Confirmation de l'ajout d'un nouveaux abonnement"));
                $msg = _("L'ajout de l'abonnement s'est déroulée avec succès");
                $myForm->setMessage($msg);
                $myForm->addButton(BUTTON_OK, "Abt-1");
                $myForm->buildHTML();
                echo $myForm->HTML_code;
            }
        } else {
            $html_err = new HTML_erreur("Echec lors de la demande d'abonnement d'un carte ATM.");
            $err_msg = $error[$err->errCode];
            $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));
            $html_err->addButton("BUTTON_OK", 'Abt-1');
            $html_err->buildHTML();
            echo $html_err->HTML_code;
        }
    }else{
        $DATA['statut'] = $statut;
        $WHERE['id_client'] = $global_id_client;
        $WHERE['id_ag'] = $global_id_agence;
        $WHERE['id_cpte'] = $compte_id;

        $err = updateAbonnementATM($DATA, $WHERE);

        if ($err->errCode == NO_ERR) {
            if(isset($statut) && $statut == 3){
                $DATA_CARTE['etat_carte'] = 6;
                $DATA_CARTE['motif_suspension'] = $motif;
                $DATA_CARTE['date_suspension'] = date('r');
                $WHERE_CARTE['id_client'] = $global_id_client;
                $WHERE_CARTE['id_carte'] = $id_carte_hidden;
                $WHERE_CARTE['id_ag'] = $global_id_agence;
                $WHERE_CARTE['id_cpte'] = $compte_id;
                $err = updateCarteATM($DATA_CARTE, $WHERE_CARTE);
            }

            $myForm = new HTML_message(_("Confirmation du modification d'un abonnement"));
            $msg = _("La modification de l'abonnement s'est déroulée avec succès");
            $myForm->setMessage($msg);
            $myForm->addButton(BUTTON_OK, "Abt-1");
            $myForm->buildHTML();
            echo $myForm->HTML_code;

        } else {
            $html_err = new HTML_erreur("Echec lors de la modification d'abonnement d'un carte ATM.");
            $err_msg = $error[$err->errCode];
            $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));
            $html_err->addButton("BUTTON_OK", 'Abt-1');
            $html_err->buildHTML();
            echo $html_err->HTML_code;
        }
    }
}