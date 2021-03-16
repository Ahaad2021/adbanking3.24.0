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

//error_reporting(E_ALL);
//ini_set("display_errors", "on");

//function generateRequestRefNum() {
//    return 'ATM' . md5(uniqid(mt_rand(0, 99999)));
//}

/* Crt-2 : Commande de cartes: choix du compte d'épargne */
if ($global_nom_ecran == "Crt-2") {

    global $dbHandler, $global_id_agence, $global_id_client;

    if (isset($SESSION_VARS["id_commande_carte"])) {
        // Clear session id_abonnement 
        unset($SESSION_VARS["id_commande_carte"]);
    }

//    $MyPage = new HTML_GEN2("Commande de cartes: choix du compte");

    // On construit la liste des comptes pour lesquels on peut commander une carte
    $liste_comptes = get_comptes_epargne($global_id_client);
    $choix = array();
    if (isset($liste_comptes)) {
        foreach($liste_comptes as $id_cpte => $infos_cpte) {
            $infos_prod = getProdEpargne($infos_cpte['id_prod']);
//            echo("id_cpte: $id_cpte");
            $commande_carte = getCommandeCarte($id_cpte);
//            echo("XX");echo($commande_carte!=null);echo("XX");
//            $etatChqs_param=isNotDemandeChequier($id_cpte);
//            $is_not_dmde=false;debug($etatChqs_param);
//            if($etatChqs_param->errCode == NO_ERR ) $is_not_dmde=true ;
//
//            if ($infos_prod["classe_comptable"] == '1' && $infos_cpte["etat_cpte"] != '3' && ($is_not_dmde)) {
            //TODO: Verify is if statut is ad_geste_carte.etat is null, then allow inside if statmement below
            if ($infos_prod["classe_comptable"] == '1' && $infos_cpte["etat_cpte"] != '3' && ($commande_carte === null || $commande_carte['date_envoi_impr'] !== null)) {
                // C'est un compte à vue, il n'est pas bloqué et aucune demande de chèquier n'est en cours
                $choix[$id_cpte] = $infos_cpte["num_complet_cpte"]." ".$infos_cpte["intitule_compte"];
            }
        }
    }

    // Création du formulaire
    $my_page = new HTML_GEN2(_("Commande de cartes: choix du compte d'épargne"));
    $my_page->addField("num_cpte", _("Numéro de compte"), TYPC_LSB);
    $my_page->setFieldProperties("num_cpte", FIELDP_ADD_CHOICES, $choix);
    $my_page->setFieldProperties("num_cpte",FIELDP_IS_REQUIRED, true);

    // Boutons
    $my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Crt-3");
    $my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Abn-1");
    $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
    $my_page->show();
    /*
    $liste_abonnements = getListAbonnement();

    //javascript
    if (count($liste_abonnements) > 0) {
        $js_vaid .= "document.ADForm.modif.disabled = true;\n";
        $js_vaid .= "document.ADForm.supr.disabled = true;\n";
    }
    $js_vaid .= "function activateButtons(){\n";
    $js_vaid .= "activate = (document.ADForm.HTML_GEN_LSB_id_abonnement.value != 0);";
    $js_vaid .= "document.ADForm.modif.disabled = !activate;";
    $js_vaid .= "document.ADForm.supr.disabled = !activate;";
    $js_vaid .= "}\n";
    $MyPage->addJS(JSP_FORM, "js_vaid", $js_vaid);

    $MyPage->addField("abonnement", "Abonnements", TYPC_LSB);
    $MyPage->setFieldProperties("abonnement", FIELDP_HAS_CHOICE_AUCUN, true);

    if (count($liste_abonnements) > 0) {
        $MyPage->setFieldProperties("abonnement", FIELDP_ADD_CHOICES, $liste_abonnements);
    }

    $MyPage->setFieldProperties("abonnement", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("abonnement", FIELDP_SHORT_NAME, "id_abonnement");
    $MyPage->setFieldProperties("id_abonnement", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();"));

    if (count($liste_abonnements) > 0) {
        
        // Bouton modifier
        $MyPage->addButton("id_abonnement", "modif", "Modifier", TYPB_SUBMIT);
        $MyPage->setButtonProperties("modif", BUTP_PROCHAIN_ECRAN, "Abn-4");
        
        // Bouton supprimer
        $MyPage->addButton("id_abonnement", "supr", "Supprimer", TYPB_SUBMIT);
        $MyPage->setButtonProperties("supr", BUTP_PROCHAIN_ECRAN, "Abn-6");
        
    }
    //$availablePrestataire = getAvailablePrestataire();
    $availableServices = getAvailableServices();

    if($availableServices == null) { //  && $availablePrestataire == null
        $MyPage->addHTMLExtraCode("htm1", "<span id='info'><br /><center style=\"font:12pt arial;\"><strong style=\"color:#FF0000;\">*</strong> Vous ne pouvez plus créer des nouveaux services</center></span>");
    } else {
        // Bouton créer
        $MyPage->addFormButton(1, 1, "cree", "Créer un abonnement", TYPB_SUBMIT);
        $MyPage->setFormButtonProperties("cree", BUTP_PROCHAIN_ECRAN, "Abn-3");
        $MyPage->setFormButtonProperties("cree", BUTP_CHECK_FORM, false);

        $MyPage->addHTMLExtraCode("htm1", "<span id='info'></span>");
    }

    //HTML
    $MyPage->buildHTML();
    echo $MyPage->getHTML();
    */
}
/*}}}*/

/*{{{ Crt-3 : Nouvelle commande de carte */
else if ($global_nom_ecran == "Crt-3") {
    // De quel écran vient-on et quelles variables doit-on sauvegarder ?
    if ($global_nom_ecran_prec == "Crt-2")
        $SESSION_VARS["id_cpte"] = $num_cpte;

    // Informations sur le compte et le produit
    $infos_cpte = get_compte_epargne_info($SESSION_VARS["id_cpte"]);
    $infos_prod = getProdEpargne($infos_cpte['id_prod']);
    $infosClient = getClientDatas($global_id_client);

    // Met la monnaie courante dans le devise
    setMonnaieCourante($infos_cpte['devise']);
//var_dump($infos_cpte);

    //var_dump($infosClient);

    // Création du formulaire
    $my_page = new HTML_GEN2(_("Nouvelle commande de carte"));

    $my_page->addField("num_complet_cpte", _("Numéro de compte"), TYPC_TXT);
    $my_page->setFieldProperties("num_complet_cpte", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("num_complet_cpte", FIELDP_DEFAULT,$infos_cpte["num_complet_cpte"]);

    $my_page->addField("id_client", _("Id client"), TYPC_INT);
    $my_page->setFieldProperties("id_client", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("id_client", FIELDP_DEFAULT, $infosClient['id_client']);

//    $my_page->addField("request_ref_num", _("Numéro de référence"), TYPC_TXT);
//    $my_page->setFieldProperties("request_ref_num", FIELDP_IS_LABEL, true);
//    $my_page->setFieldProperties("request_ref_num", FIELDP_DEFAULT, generateRequestRefNum());

    $pp_prenom_arr =  preg_split('/\s+/', $infosClient['pp_prenom']);

    $first_name = $pp_prenom_arr[0];
    $middle_name = "";
    if(count($pp_prenom_arr) > 1) {
        for($i=1; $i < count($pp_prenom_arr); $i++) {
            if($i > 1)
                $middle_name .= " ";
            $middle_name .= $pp_prenom_arr[$i];
        }
    }

    $my_page->addField("first_name_label", _("Prénom"), TYPC_TXT);
    $my_page->setFieldProperties("first_name_label", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("first_name_label", FIELDP_DEFAULT, $first_name);
    $my_page->addHiddenType("first_name", $first_name);

    $my_page->addField("middle_name_label", _("Deuxième prénom"), TYPC_TXT);
    $my_page->setFieldProperties("middle_name_label", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("middle_name_label", FIELDP_DEFAULT, $middle_name);
    $my_page->addHiddenType("middle_name", $middle_name);

    $my_page->addField("last_name_label", _("Nom"), TYPC_TXT);
    $my_page->setFieldProperties("last_name_label", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("last_name_label", FIELDP_DEFAULT, $infosClient['pp_nom']);
    $my_page->addHiddenType("last_name", $infosClient['pp_nom']);

    $my_page->addField("nom_carte", _("Nom sur la carte"), TYPC_TXT);
    $my_page->setFieldProperties("nom_carte", FIELDP_DEFAULT, $first_name . ($middle_name ? " " . $middle_name : "") . " " . $infosClient['pp_nom']);
    $my_page->setFieldProperties("nom_carte", FIELDP_IS_REQUIRED, TRUE);

    $titres = array("0"=>"MR","1"=>"MRS","2"=>"MISS","3"=>"MS","4"=>"PROF","5"=>"DR","6"=>"REV","7"=>"OTHER");
    $my_page->addField("titre", "Titre", TYPC_LSB);
    $my_page->setFieldProperties("titre", FIELDP_ADD_CHOICES, $titres);
//    $my_page->setFieldProperties("titre", FIELDP_IS_REQUIRED, true);
    $my_page->setFieldProperties("titre", FIELDP_HAS_CHOICE_AUCUN, false);

    $my_page->addField("num_identite_passeport", _("ID / Numéro de passeport"), TYPC_TXT);
    $my_page->setFieldProperties("num_identite_passeport", FIELDP_DEFAULT, ($infosClient['pp_type_piece_id'] == 1 || $infosClient['pp_type_piece_id'] == 3) ? $infosClient['pp_nm_piece_id'] : "");
    $my_page->setFieldProperties("num_identite_passeport", FIELDP_IS_REQUIRED, TRUE);

    $resident = array("0"=>"Résident","1"=>"Non résident");
    $my_page->addField("resident", "Résident / Non résident", TYPC_LSB);
    $my_page->setFieldProperties("resident", FIELDP_ADD_CHOICES, $resident);
//    $my_page->setFieldProperties("resident", FIELDP_IS_REQUIRED, true);
    $my_page->setFieldProperties("resident", FIELDP_HAS_CHOICE_AUCUN, false);

    $my_page->addField("type_client_label", _("Type client"), TYPC_TXT);
    $my_page->setFieldProperties("type_client_label", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("type_client_label", FIELDP_DEFAULT, $infosClient['statut_juridique'] == 1 ? "Privé" : "Entreprise");
    $my_page->addHiddenType("type_client", $infosClient['statut_juridique'] == 1 ? 0 : 1); //0=> prive, 1=> entreprise

    $motifs_delivrances = array("0"=>"Nouveau compte - nouveau client","1"=>"Nouveau compte - client existant","2"=>"Renouvellement de la carte","3"=>"Remplacement de la carte","4"=>"Carte supplémentaire");
    $my_page->addField("reason_for_issue", "Motif de la délivrance", TYPC_LSB);
    $my_page->setFieldProperties("reason_for_issue", FIELDP_ADD_CHOICES, $motifs_delivrances);
//    $my_page->setFieldProperties("reason_for_issue", FIELDP_IS_REQUIRED, true);
    $my_page->setFieldProperties("reason_for_issue", FIELDP_HAS_CHOICE_AUCUN, false);

    $my_page->addField("type_compte_label", _("Type compte"), TYPC_TXT);
    $my_page->setFieldProperties("type_compte_label", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("type_compte_label", FIELDP_DEFAULT, "Courant"); //TODO: le num_compte_cpte doit avoir tout le temps la classe comptable = 1
    $my_page->addHiddenType("type_compte", "Courant");

    $my_page->addField("devise_label", _("Devise du compte"), TYPC_TXT);
    $my_page->setFieldProperties("devise_label", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("devise_label", FIELDP_DEFAULT, $infos_cpte['devise']);
    $my_page->addHiddenType("devise", $infos_cpte['devise']);

    $priorites = array("1"=>"Haute","2"=>"Normale","3"=>"Faible");
    $my_page->addField("priorite", "Priorité", TYPC_LSB);
    $my_page->setFieldProperties("priorite", FIELDP_ADD_CHOICES, $priorites);
//    $my_page->setFieldProperties("priorite", FIELDP_IS_REQUIRED, true);
    $my_page->setFieldProperties("priorite", FIELDP_HAS_CHOICE_AUCUN, false);

    $my_page->addField("guichet", _("Guichet"), TYPC_TXT);
    $my_page->setFieldProperties("guichet", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("guichet", FIELDP_DEFAULT, $global_nom_utilisateur);

    $tarif = getTarificationDatas('ATM_REG');
    var_dump($tarif);
    $my_page->addField("frais", _("Frais de commande"), TYPC_MNT);
    $my_page->setFieldProperties("frais", FIELDP_IS_LABEL, true);
    $my_page->setFieldProperties("frais", FIELDP_DEFAULT, $tarif['valeur']);


//    $my_page->addField("nbre_carnets", _("Nombre de carnets"), TYPC_INT);
//    $my_page->setFieldProperties("nbre_carnets", FIELDP_DEFAULT, 1);
//    $my_page->setFieldProperties("nbre_carnets", FIELDP_IS_REQUIRED, TRUE);
//
//    $my_page->addField("frais_chequier", _("Frais de commande de chèquier"), TYPC_MNT);
//    $my_page->setFieldProperties("frais_chequier", FIELDP_IS_LABEL, TRUE);
//    $my_page->setFieldProperties("frais_chequier", FIELDP_DEFAULT, $infos_prod["frais_chequier"]);
//    $codejs = "function setFraisChequier() {
//  		      document.ADForm.frais_chequier.value =  eval(document.ADForm.nbre_carnets.value )  * '". $infos_prod["frais_chequier"]."';
//          }";
//    $my_page->setFieldProperties("nbre_carnets", FIELDP_JS_EVENT, array("onChange"=>"setFraisChequier();"));
//    $my_page->addJS(JSP_FORM, "JS1", $codejs);
//    $my_page->addHiddenType('frais_chequier_unitaire',$infos_prod["frais_chequier"]);
//
//    if (check_access(299)) {
//        $my_page->setFieldProperties("frais_chequier", FIELDP_CAN_MODIFY, true);
//    }
//
//    // enable the field so that it is posted
//    $js_mnt_frais = " if(ADFormValid == true) \n\t\t document.ADForm.frais_chequier.removeAttribute('disabled'); \n";
//    $my_page->addJS(JSP_END_CHECK,"js_mnt_frais", $js_mnt_frais);

    //Boutons
    $my_page->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Crt-4");
    $my_page->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
    $my_page->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Crt-2");
    $my_page->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);
    $my_page->show();
} /*}}}*/

/*{{{ Crt-4 : Confirmation commande carte */
else if ($global_nom_ecran == "Crt-4") {
//    if($nbre_carnets > 0 ) {
        $frais_chequier = $_POST['frais_chequier'];

        if(empty($frais_chequier)) {
            $frais_chequier = recupMontant($frais_chequier_unitaire) * $nbre_carnets;
        }

        $type_compte = 0;
        $branch_code = 'RW0010529'; //TODO CHANGE THIS TO RETRIEVE FROM TABLE
        $tarif = getTarificationDatas('ATM_REG');
        echo("firstname: $first_name, middlename: $middle_name, lastname: $last_name, guichet: $guichet");
//        $result = doCommandeChequier($SESSION_VARS["id_cpte"], $nbre_carnets, recupMontant($frais_chequier));
        $result = doCommandeCarte($branch_code, $first_name, $middle_name, $last_name, $SESSION_VARS["id_cpte"], $global_id_client, $nom_carte, $titre == null ? 0 : $titre, $num_identite_passeport, $type_client, $resident == null ? 0 : $resident, $reason_for_issue == null ? 0 : $reason_for_issue, $type_compte, $devise, $priorite == null ? 0 : $priorite, $global_nom_utilisateur, $tarif['valeur']);

        if ($result->errCode == NO_ERR) {
            $my_page =new HTML_message(_("Confirmation commande"));
            $my_page->setMessage(sprintf(_("La commande d'une nouvelle carte a été introduite. Numéro de référence: "),$nbr_cheques),true);
            $my_page->addButton("BUTTON_OK", 'Gen-4');
        } else {
            $my_page = new HTML_erreur(_("Echec lors de la commande d'un nouveau chèquier "));
            $my_page->setMessage(_("Erreur")." : ".$error[$result->errCode]."<br />"._("Paramètre")." : ".$result->param);
            $my_page->addButton("BUTTON_OK", 'Chq-1');
        }
        $my_page->show();
//    }
//    else {
//        $my_page = new HTML_erreur(_("Echec lors de la commande d'un nouveau chèquier "));
//        $my_page->setMessage(_("Le champs Nombre de carnets doit être supérieur à zero"));
//        $my_page->addButton("BUTTON_OK", 'Chq-1');
//        $my_page->show();
//        exit();
//
//    }
} /*}}}*/







//
//
///* Abn-2 : Réinitialisation mot de passe */
//elseif ($global_nom_ecran == "XXX") {
//
//    global $dbHandler, $global_id_agence, $global_id_client;
//
//    if (isset($_POST["hdd_reset_pwd"]) && isset($SESSION_VARS["id_abonnement"]) && trim($_POST["hdd_reset_pwd"])=='oui' && trim($SESSION_VARS["id_abonnement"]) > 0) {
//
//        $id_abonnement = trim($SESSION_VARS["id_abonnement"]);
//
//        $erreur = resetMotDePasse($id_abonnement);
//
//        if ($erreur->errCode == NO_ERR) {
//
//            // Affichage de la confirmation
//            $html_msg = new HTML_message("Confirmation Réinitialisation mot de passe");
//
//            $html_msg->setMessage("<br />Votre mot de passe a été réinitialisé.<br />");
//
//            $html_msg->addButton("BUTTON_OK", 'Abn-7');
//            $html_msg->buildHTML();
//
//            echo $html_msg->HTML_code;
//        } else {
//            $html_err = new HTML_erreur("Echec lors de la réinitialisation mot de passe.");
//
//            $html_err->setMessage("Erreur : " . $error[$erreur->errCode] . "<br /> Paramètre : " . $erreur->param);
//
//            $html_err->addButton("BUTTON_OK", 'Abn-7');
//
//            $html_err->buildHTML();
//            echo $html_err->HTML_code;
//        }
//    } else {
//
//        // Affichage de la confirmation
//        $html_msg = new HTML_message("Réinitialisation mot de passe");
//
//        $html_msg->setMessage("<br />Êtes-vous sûr de vouloir réinitialiser votre mot de passe ?<br /><input type=\"hidden\" id=\"hdd_reset_pwd\" name=\"hdd_reset_pwd\" value=\"oui\">");
//
//        $html_msg->addButton("BUTTON_OUI", 'Abn-2');
//        $html_msg->addButton("BUTTON_NON", 'Abn-7');
//        $html_msg->buildHTML();
//
//        echo $html_msg->HTML_code;
//    }
//}
//
///* Abn-3 : Inscription & Modification abonnement */
//elseif ($global_nom_ecran == "Abn-3" || $global_nom_ecran == "Abn-4") {
//
//    global $dbHandler, $global_id_agence, $global_id_client;
//
//    if ($global_nom_ecran == "Abn-3") {
//        $pageTitle = "Inscription abonnement";
//    } elseif ($global_nom_ecran == "Abn-4") {
//        $pageTitle = "Modification abonnement";
//    }
//
//    $MyPage = new HTML_GEN2($pageTitle);
//
//    $InfosClient = getClientDatas($global_id_client);
//
//    $editMode = false;
//    $shortName = "ajouter";
//    $pageMode = $shortName;
//    $longName = "Ajouter un abonnement";
//    if (isset($modif) && isset($id_abonnement) && $modif = 'Modifier' && $id_abonnement > 0) {
//
//        $InfosAbonnements = getAbonnementData($id_abonnement);
//
//        if (is_array($InfosAbonnements) && count($InfosAbonnements) > 0) {
//            $identifiant = trim($InfosAbonnements['identifiant']);
//            $num_sms = trim($InfosAbonnements['num_sms']);
//            $langue = trim($InfosAbonnements['langue']);
//            $ewallet = trim($InfosAbonnements['ewallet']);
//            $id_prestataire = trim($InfosAbonnements['id_prestataire']);
//
//            $email = trim($InfosAbonnements['estatement_email']);
//            $journalier = trim($InfosAbonnements['estatement_journalier']);
//            $hebdomadaire = trim($InfosAbonnements['estatement_hebdo']);
//            $mensuel = trim($InfosAbonnements['estatement_mensuel']);
//            $idService = trim($InfosAbonnements['id_service']);
//            $idPrestataire = trim($InfosAbonnements['id_prestataire']);
//            $nomPrestataire = trim($InfosAbonnements['nom_prestataire']);
//
//            $SESSION_VARS["id_abonnement"] = $id_abonnement;
//
//            $editMode = true;
//
//            $shortName = "modifier";
//            $pageMode = $shortName;
//            $longName = "Modifier";
//        }
//
//    } elseif (is_array($InfosClient) && count($InfosClient) > 0) {
//        $identifiant = generateIdentifiant();
//    }
//
//    if ($editMode == false && (($InfosClient['num_tel']) == '' || trim($InfosClient['email']) == '')) {
//        $html_err = new HTML_erreur("Echec lors de la création d'un abonnement.");
//
//        $html_err->setMessage("Erreur : Veuillez saisir un numéro de téléphone valide et un email dans la fiche client !");
//        $html_err->addButton("BUTTON_OK", 'Abn-7');
//
//        $html_err->buildHTML();
//        echo $html_err->HTML_code;
//    }
//    else
//    {
//        // Page mode
//        $MyPage->addHiddenType("page_mode", $pageMode);
//
//        $codejs = "
//                      function refreshFields(status) {
//                        var selection;
//
//                        if(document.getElementsByName('id_service')[0]) {
//                          //page edition
//                          selection = document.getElementsByName('id_service')[0].value;
//                          if(selection == 'eStatement') {
//                            document.getElementsByName('btn_reset_pwd')[0].style.display = 'none';
//                          }
//                        } else {
//                          //page creation
//                          selection = document.ADForm.HTML_GEN_LSB_id_service.value;
//                          if(selection == 1) {
//                            //SMSBANKING
//                            document.getElementById('info2').style.display = 'inline';
//                            document.getElementById('info3').style.display = 'none';
//                            if (document.getElementsByName('estatement_email')[0].value==''){
//                              document.getElementsByName('estatement_email')[0].value='email@invalid.com';
//                            }
//                            if (status == 1 && document.getElementsByName('num_sms')[0].value=='25000000000') {
//                              document.getElementsByName('num_sms')[0].value='';
//                            }
//                            ";
//
//        $is_data_err = false;
//        if ($editMode == false) {
//
//            $num_tel_test = preg_replace('/[\t\n\r\s\+\-]+/i', '', trim($InfosClient['num_tel']));
//
//            $param_abonnement = getParamAbonnement('NB_CARACTERES_TELEPHONE');
//
//            if ($num_tel_test == '') {
//                $MyPage->addHTMLExtraCode("htm1", "<span id='info'><br /><center style=\"font:12pt arial;color:#FF0000;\"><strong>Erreur : </strong>Veuillez saisir au préalable un numéro de téléphone dans la fiche client</center><br /></span>");
//                $is_data_err = true;
//            }
//            elseif (strlen($num_tel_test) != $param_abonnement[1]) {
//                $MyPage->addHTMLExtraCode("htm1", "<span id='info'><br /><center style=\"font:12pt arial;color:#FF0000;\"><strong>Erreur : </strong>" . $param_abonnement[2] . "</center><br /></span>");
//                $is_data_err = true;
//            }
//
//            if ($is_data_err) {
//                $codejs .= "document.getElementsByName('ajouter')[0].parentNode.style.display = 'none';\n";
//                $codejs .= "document.getElementById('info').style.display = 'inline';";
//            } else {
//                $codejs .= "document.getElementsByName('ajouter')[0].parentNode.style.display = 'table-row';\n";
//                $codejs .= "document.getElementById('info').style.display = 'none';";
//            }
//        } else {
//            $codejs .= "document.getElementsByName('ajouter')[0].parentNode.style.display = 'table-row';\n";
//            $codejs .= "document.getElementById('info').style.display = 'none';";
//        }
//
//        $codejs .= "
//                          } else {
//                            //E-STATEMENT
//                            if (status == 1 && document.getElementsByName('estatement_email')[0].value=='email@invalid.com') {
//                              document.getElementsByName('estatement_email')[0].value='';
//                            }
//                            if (document.getElementsByName('num_sms')[0].value==''){
//                              document.getElementsByName('num_sms')[0].value='25000000000';
//                            }
//                            document.getElementById('info2').style.display = 'none';
//                            document.getElementById('info3').style.display = 'inline';
//                            ";
//        if ($editMode == false && trim($InfosClient['email']) == '') {
//            $MyPage->addHTMLExtraCode("htm1", "<span id='info'><br /><center style=\"font:12pt arial;color:#FF0000;\"><strong>Erreur : </strong>Veuillez saisir au préalable un email dans la fiche client</center><br /></span>");
//            $codejs .= "document.getElementsByName('ajouter')[0].parentNode.style.display = 'none';\n";
//            $codejs .= "document.getElementById('info').style.display = 'inline';";
//            $is_data_err = true;
//        } else {
//            $codejs .= "document.getElementsByName('ajouter')[0].parentNode.style.display = 'table-row';\n";
//            $codejs .= "document.getElementById('info').style.display = 'none';";
//        }
//
//        $codejs .= "
//                          }
//                        }
//                        if(selection == '1' || (document.getElementsByName('id_service_hidden') && document.getElementsByName('id_service_hidden')[0].value == '1')) {
//                          document.getElementsByName('num_sms')[0].parentNode.parentNode.style.display = 'table-row';
//                          document.getElementsByName('HTML_GEN_BOL_ewallet')[0].parentNode.parentNode.style.display = 'table-row';
//                          if(selection == '1') {
//                            document.getElementsByName('HTML_GEN_LSB_id_prestataire')[0].parentNode.parentNode.style.display = 'table-row';
//                          } else {
//                            document.getElementsByName('HTML_GEN_LSB_id_prestataire')[0].parentNode.parentNode.style.display = 'table-row';
//                          }
//                          document.getElementsByName('motdepasse')[0].parentNode.parentNode.style.display = 'table-row';
//                          document.getElementsByName('confirm_motdepasse')[0].parentNode.parentNode.style.display = 'table-row';
//
//                          document.getElementsByName('estatement_email_label')[0].parentNode.parentNode.style.display = 'none';
//                          document.getElementsByName('HTML_GEN_BOL_estatement_journalier')[0].parentNode.parentNode.style.display = 'none';
//                          document.getElementsByName('HTML_GEN_BOL_estatement_hebdo')[0].parentNode.parentNode.style.display = 'none';
//                          document.getElementsByName('HTML_GEN_BOL_estatement_mensuel')[0].parentNode.parentNode.style.display = 'none';
//                        } else {
//                          document.getElementsByName('num_sms')[0].parentNode.parentNode.style.display = 'none';
//                          document.getElementsByName('HTML_GEN_BOL_ewallet')[0].parentNode.parentNode.style.display = 'none';
//                          if(selection == '2') {
//                            document.getElementsByName('HTML_GEN_LSB_id_prestataire')[0].parentNode.parentNode.style.display = 'none';
//                          } else {
//                            document.getElementsByName('HTML_GEN_LSB_id_prestataire')[0].parentNode.parentNode.style.display = 'none';
//                          }
//                          document.getElementsByName('motdepasse')[0].parentNode.parentNode.style.display = 'none';
//                          document.getElementsByName('confirm_motdepasse')[0].parentNode.parentNode.style.display = 'none';
//
//                          document.getElementsByName('estatement_email_label')[0].parentNode.parentNode.style.display = 'table-row';
//                          document.getElementsByName('HTML_GEN_BOL_estatement_journalier')[0].parentNode.parentNode.style.display = 'table-row';
//                          document.getElementsByName('HTML_GEN_BOL_estatement_hebdo')[0].parentNode.parentNode.style.display = 'table-row';
//                          document.getElementsByName('HTML_GEN_BOL_estatement_mensuel')[0].parentNode.parentNode.style.display = 'table-row';
//                        }
//                      }
//                      refreshFields(0);
//        ";
//
//        $MyPage->addJS(JSP_FORM, "JS_ESTATEMENT", $codejs);
//
//        $code_bol_js = "
//                      function checkBolFields() {
//                        var service_id = 0;
//                        if (document.ADForm.HTML_GEN_LSB_id_service) {
//                            var service_id = document.ADForm.HTML_GEN_LSB_id_service.value;
//                        }
//                        else if (document.ADForm.id_service_hidden) {
//                            var service_id = document.ADForm.id_service_hidden.value;
//                        }
//
//                        var bol_estat_checked = false;
//                        if(service_id == 2) {
//                            var bol_estat_journalier = document.ADForm.HTML_GEN_BOL_estatement_journalier.checked;
//                            var bol_estat_hebdo = document.ADForm.HTML_GEN_BOL_estatement_hebdo.checked;
//                            var bol_estat_mensuel = document.ADForm.HTML_GEN_BOL_estatement_mensuel.checked;
//
//                            if (bol_estat_journalier || bol_estat_hebdo || bol_estat_mensuel)
//                            {
//                                bol_estat_checked=true;
//                            }
//
//                            if (!bol_estat_checked) {
//                                msg += '- Veuillez cocher une case de périodicité \\n';
//                                ADFormValid=false;
//                            }
//                        }
//                      }
//                      checkBolFields();
//        ";
//
//        $MyPage->addJS(JSP_BEGIN_CHECK, "JS_BOL", $code_bol_js);
//
//        $choix_service = getListMobileService();
//
//        if ($editMode) {
//            $MyPage->addField("id_service", "Service", TYPC_TXT);
//            $MyPage->setFieldProperties("id_service", FIELDP_DEFAULT, $choix_service[$idService]);
//            $MyPage->setFieldProperties("id_service", FIELDP_IS_LABEL, true);
//            $MyPage->addHiddenType("id_service_hidden", $idService);
//        } else {
//            $availableServices = getAvailableServices();
//            $availablePrestataires = getAvailablePrestataire();
//
//            $choix_service_label = array();
//
//            if($availableServices == NULL) {
//                //param false => On enleve ESTATEMENT de la liste
//                $choix_service_label = getListMobileService(false);
//            } else {
//                foreach ($availableServices as $key=>$val) {
//                    $choix_service_label[$key] = $choix_service[$key];
//                }
//            }
//
//            $MyPage->addField("id_service", "Liste des services", TYPC_LSB);
//            $MyPage->setFieldProperties("id_service", FIELDP_ADD_CHOICES, $choix_service_label);
//            $MyPage->setFieldProperties("id_service", FIELDP_IS_REQUIRED, false);
//            $MyPage->setFieldProperties("id_service", FIELDP_HAS_CHOICE_AUCUN, false);
//            $MyPage->addHiddenType("id_service_hidden", 0);
//        }
//
//        $MyPage->setFieldProperties("id_service", FIELDP_JS_EVENT, array("onchange"=>"refreshFields(1);"));
//
//        $MyPage->addField("num_sms", "Numéro SMS", TYPC_TXT);
//        if ($editMode) {
//            $MyPage->setFieldProperties("num_sms", FIELDP_DEFAULT, $num_sms);
//            $MyPage->setFieldProperties("num_sms", FIELDP_IS_LABEL, true);
//            $MyPage->addHiddenType("num_sms_hdd", $num_sms);
//
//            if ($idService==1) {
//                //$MyPage->setFieldProperties("num_sms", FIELDP_IS_REQUIRED, true);
//            }
//        } else {
//            //$MyPage->setFieldProperties("num_sms", FIELDP_IS_REQUIRED, true);
//
//            $num_tel = preg_replace('/[\t\n\r\s\+\-]+/i', '', trim($InfosClient['num_tel'])); // [ \t\n\r\+\-]*/g
//
//            // Get num sms from table ad_cli
//            $MyPage->setFieldProperties("num_sms", FIELDP_DEFAULT, $num_tel);
//            $MyPage->setFieldProperties("num_sms", FIELDP_IS_LABEL, true);
//            $MyPage->addHiddenType("num_sms_hdd", $num_tel);
//        }
//
//        $MyPage->addField("ewallet", "eWallet", TYPC_BOL);
//        if ($editMode && isset($ewallet)) {
//            $MyPage->setFieldProperties("ewallet", FIELDP_DEFAULT, ($ewallet=='t') ? 1 : 0);
//        }
//
//        $choix_prestataire = getAvailablePrestataire(false);
//
//        $MyPage->addField("id_prestataire", "Liste des prestataires", TYPC_LSB);
//        $MyPage->setFieldProperties("id_prestataire", FIELDP_ADD_CHOICES, $choix_prestataire);
//        $MyPage->setFieldProperties("id_prestataire", FIELDP_IS_REQUIRED, false);
//        $MyPage->setFieldProperties("id_prestataire", FIELDP_HAS_CHOICE_AUCUN, false);
//
//        if ($editMode) {
//            $MyPage->setFieldProperties("id_prestataire", FIELDP_DEFAULT, $idPrestataire);
//        }
//
//        $MyPage->addField("identifiant", "Identifiant", TYPC_TXT);
//        $MyPage->setFieldProperties("identifiant", FIELDP_DEFAULT, $identifiant);
//        $MyPage->setFieldProperties("identifiant", FIELDP_IS_LABEL, true);
//
//        $MyPage->addField("langue", "Langue", TYPC_LSB);
//        $liste_langues = array(3 => "Kinyarwanda", 1 => "Français", 2 => "Anglais");
//
//        if (count($liste_langues) > 0) {
//            $MyPage->setFieldProperties("langue", FIELDP_ADD_CHOICES, $liste_langues);
//
//            if ($editMode && isset($langue)) {
//                $MyPage->setFieldProperties("langue", FIELDP_DEFAULT, $langue);
//            }
//        }
//
//        $MyPage->setFieldProperties("langue", FIELDP_HAS_CHOICE_AUCUN, false);
//
//        $MyPage->addField("motdepasse", "Mot de passe", TYPC_PWD);
//
//        $MyPage->addField("confirm_motdepasse", "Confirmation Mot de Passe", TYPC_PWD);
//
//        $MyPage->addField("estatement_email_label", "Email", TYPC_EMA);
//        if ($editMode && isset($email)) {
//            $MyPage->setFieldProperties("estatement_email_label", FIELDP_DEFAULT, $email);
//            $MyPage->setFieldProperties("estatement_email_label", FIELDP_IS_LABEL, true);
//            $MyPage->addHiddenType("estatement_email", $email);
//        } else {
//            //$MyPage->setFieldProperties("estatement_email", FIELDP_IS_REQUIRED, true);
//
//            // Get email from table ad_cli
//            $MyPage->setFieldProperties("estatement_email_label", FIELDP_DEFAULT, trim($InfosClient['email']));
//            $MyPage->setFieldProperties("estatement_email_label", FIELDP_IS_LABEL, true);
//            $MyPage->addHiddenType("estatement_email", trim($InfosClient['email']));
//        }
//
//        $MyPage->addField("estatement_journalier", "Journalier", TYPC_BOL);
//        if ($editMode && isset($journalier)) {
//            $MyPage->setFieldProperties("estatement_journalier", FIELDP_DEFAULT, ($journalier=='t') ? 1 : 0);
//        }
//
//        $MyPage->addField("estatement_hebdo", "Hebdomadaire", TYPC_BOL);
//        if ($editMode && isset($hebdomadaire)) {
//            $MyPage->setFieldProperties("estatement_hebdo", FIELDP_DEFAULT, ($hebdomadaire=='t') ? 1 : 0);
//        }
//
//        $MyPage->addField("estatement_mensuel", "Mensuel", TYPC_BOL);
//        if ($editMode && isset($mensuel)) {
//            $MyPage->setFieldProperties("estatement_mensuel", FIELDP_DEFAULT, ($mensuel=='t') ? 1 : 0);
//        }
//
//        if (!$editMode) {
//
//            $tarifSMS = getTarificationDatas("SMS_REG");
//            $tarifESTAT = getTarificationDatas("ESTAT_REG");
//
//            if (is_array($tarifSMS) || is_array($tarifESTAT)) {
//
//                $span_info2 = "<span id='info2'></span>";
//                $span_info3 = "</span><span id='info3'></span>";
//
//                if (count($tarifSMS) > 0 && trim($tarifSMS['valeur']) > 0) {
//                    $span_info2 = "<span id='info2'>NOTE : un frais d'activation de <strong>".afficheMontant(trim($tarifSMS['valeur']), TRUE)."</strong> sera prélevé de votre compte de base</span>";
//                }
//
//                if (count($tarifESTAT) > 0 && trim($tarifESTAT['valeur']) > 0) {
//                    $span_info3 = "<span id='info3'>NOTE : un frais d'activation de <strong>".afficheMontant(trim($tarifESTAT['valeur']), TRUE)."</strong> sera prélevé de votre compte de base</span>";
//                }
//
//                $MyPage->addHTMLExtraCode("htm2", "<br /><center style=\"font:12pt arial;color:#FF0000;\">".$span_info2.$span_info3."</center><br />");
//            } else {
//                $MyPage->addHTMLExtraCode("htm2", "<span id='info2'></span><span id='info3'></span>");
//            }
//        } else {
//            $MyPage->addHTMLExtraCode("htm2", "<span id='info2'></span><span id='info3'></span>");
//        }
//
//        $order_arr = array("id_service", "num_sms", "ewallet", "id_prestataire", "identifiant", "langue", "motdepasse", "confirm_motdepasse", "estatement_email_label", "estatement_journalier", "estatement_hebdo", "estatement_mensuel", "htm1", "htm2");
//
//        if (!$is_data_err) {
//            $MyPage->addHTMLExtraCode("htm1", "<span id='info'></span>");
//        }
//
//        $MyPage->setOrder(NULL, $order_arr); // , "statut"
//
//        $js_pwd = "";
//        if (!$editMode)
//        {
//            //$js_pwd .= "\nif (document.ADForm.num_sms.value != '' && !(/^25/).test(document.ADForm.num_sms.value)) \n\t{ \n\t\t msg+='- le champ Numéro SMS doit commencer par le code pays, ex. 250XXXXXXXXX\\n';ADFormValid=false;\n }\n";
//            //$js_pwd .= "\nif (document.ADForm.num_sms.value != '' && (document.ADForm.num_sms.value.length > 12 || isNaN(document.ADForm.num_sms.value))) \n\t{ \n\t\t msg+='- le nombre de chiffres du champ Numéro SMS doit être antérieure ou égale à 12 chiffres\\n';ADFormValid=false;\n }\n";
//        }
//        $js_pwd .= "if (document.ADForm.motdepasse.value != '' && !(/^((?=.*\d)(?=.*[a-z])(?=.*[!?@#$:,;%&]).{6,})$/).test(document.ADForm.motdepasse.value)) \n\t{ \n\t\t msg+='- Le champ Mot de passe doit contenir au minimum 6 caractères dont 1 chiffre, 1 lettre et 1 caractère spécial\\n';ADFormValid=false;\n }\nelse if (document.ADForm.motdepasse.value != document.ADForm.confirm_motdepasse.value) \n\t{ \n\t\t msg+='- Les valeurs des champs mot de passe ne sont pas identique\\n';ADFormValid=false;\n }\n";
//        $MyPage->addJS(JSP_BEGIN_CHECK, "JS", $js_pwd );
//
//        $MyPage->addFormButton(1, 1, $shortName, $longName, TYPB_SUBMIT);
//
//        if ($editMode) {
//            $MyPage->addFormButton(1, 2, "btn_reset_pwd", "Réinitialisation mot de passe", TYPB_SUBMIT);
//        }
//        $MyPage->addFormButton(1, 3, "cancel", "Annuler", TYPB_SUBMIT);
//
//        $MyPage->setFormButtonProperties($shortName, BUTP_PROCHAIN_ECRAN, 'Abn-5');
//
//        if ($editMode) {
//            $MyPage->setFormButtonProperties("btn_reset_pwd", BUTP_PROCHAIN_ECRAN, 'Abn-2');
//            $MyPage->setFormButtonProperties("btn_reset_pwd", BUTP_CHECK_FORM, false);
//        }
//
//        $MyPage->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Abn-7');
//        $MyPage->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
//
//        $MyPage->buildHTML();
//        echo $MyPage->getHTML();
//    }
//}
//
///* Abn-5 : Confirmation abonnement */
//elseif ($global_nom_ecran == "Abn-5") {
//
//    global $dbHandler, $global_id_agence, $global_id_client;
//
//    $id_abonnement = null;
//    if (isset($SESSION_VARS["id_abonnement"]) && trim($SESSION_VARS["id_abonnement"]) > 0) {
//        $id_abonnement = trim($SESSION_VARS["id_abonnement"]);
//
//        //$InfosAbonnements = getAbonnementData($id_abonnement);
//
//        // Clear session id_abonnement
//        unset($SESSION_VARS["id_abonnement"]);
//    }
//
//    if (isset($page_mode)) {
//
//        if ($page_mode == 'ajouter') {
//            if($id_service === "1") {
//                if (!isNumSmsExist($num_sms_hdd)) {
//                    $erreur = handleAbonnement($page_mode, $num_sms_hdd, $langue, $ewallet, $id_prestataire, $motdepasse, null, $id_service, null, null, null, null);
//
//                    if ($erreur->errCode == NO_ERR) {
//                        // Prélève frais abonnement
//                        $erreur = preleveFraisAbonnement('SMS_REG', $global_id_client, 180);
//                    }
//
//                    $confirmationMessage = "Un abonnement a été créé !";
//                } else {
//                    $erreur = new ErrorObj(ERR_CPT_EXIST);
//                }
//            } else {
//                if (!isEmailExist($estatement_email)) {
//                    $erreur = handleAbonnement($page_mode, null, $langue, $ewallet, $id_prestataire, $motdepasse, null, $id_service, $estatement_email, $estatement_journalier, $estatement_hebdo, $estatement_mensuel);
//
//                    if ($erreur->errCode == NO_ERR) {
//                        // Prélève frais abonnement estatement
//                        $erreur = preleveFraisAbonnement('ESTAT_REG', $global_id_client, 185);
//                    }
//
//                    $confirmationMessage = "Un abonnement a été créé !";
//                } else {
//                    $erreur = new ErrorObj(ERR_EMAIL_EXIST);
//                }
//            }
//        }
//        elseif ($page_mode == 'modifier') {
//            if($id_service_hidden == "2") {
//                //si eStatement
//                $emailByClientId = getEmailByClientId($global_id_client);
//                if ((isset($estatement_email)) && ( strval($estatement_email) === strval($emailByClientId))) {
//                    $erreur = handleAbonnement($page_mode, null, $langue, $ewallet, $id_prestataire, $motdepasse, $id_abonnement, $id_service_hidden, null, $estatement_journalier, $estatement_hebdo, $estatement_mensuel); // $estatement_email
//
//                    $confirmationMessage = "L'abonnement a été mis à jour !";
//                } else {
//                    $erreur = new ErrorObj(ERR_EMAIL_EXIST);
//                }
//            } else {
//                //$numSmsByClientId = getNumSmsByClientId($global_id_client);
//                if (isset($num_sms_hdd) && !isNumSmsExist($num_sms_hdd, $global_id_client)) { //  && ( strval($num_sms_hdd) === strval($numSmsByClientId))
//                    $erreur = handleAbonnement($page_mode, null, $langue, $ewallet, $id_prestataire, $motdepasse, $id_abonnement, $id_service_hidden, null, null, null, null); // $num_sms_hdd
//
//                    $confirmationMessage = "L'abonnement a été mis à jour !";
//                } else {
//                    $erreur = new ErrorObj(ERR_CPT_EXIST);
//                }
//            }
//        }
//
//        if ($erreur->errCode == NO_ERR) {
//
//            $dbHandler->closeConnection(true);
//
//            // Affichage de la confirmation
//            $html_msg = new HTML_message("Confirmation abonnement");
//
//            $html_msg->setMessage("<br />{$confirmationMessage}<br />");
//
//            $html_msg->addButton("BUTTON_OK", 'Abn-7');
//            $html_msg->buildHTML();
//
//            echo $html_msg->HTML_code;
//        }
//        else {
//            $html_err = new HTML_erreur("Echec lors de la création d'un abonnement.");
//
//            if ($erreur->errCode == ERR_CPT_EXIST) {
//                $html_err->setMessage("Erreur : Le numéro SMS existe déjà !");
//            } elseif($erreur->errCode == ERR_EMAIL_EXIST) {
//                $html_err->setMessage("Erreur : L'email existe déjà !");
//            } else {
//                $html_err->setMessage("Erreur : " . $error[$erreur->errCode] . "<br /> Paramètre : " . $erreur->param);
//            }
//            $html_err->addButton("BUTTON_OK", 'Abn-7');
//
//            $html_err->buildHTML();
//            echo $html_err->HTML_code;
//        }
//    } else {
//        signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
//    }
//}
//
///* Abn-6 : Supprimer un abonnement */
//elseif ($global_nom_ecran == "Abn-6") {
//
//    global $dbHandler, $global_id_agence, $global_id_client;
//
//    if (isset($_POST["hdd_delete_abn"]) && isset($SESSION_VARS["id_abonnement"]) && trim($_POST["hdd_delete_abn"])=='oui' && trim($SESSION_VARS["id_abonnement"]) > 0) {
//
//        $id_abonnement = trim($SESSION_VARS["id_abonnement"]);
//
//        $erreur = deleteAbonnement($id_abonnement);
//
//        if ($erreur->errCode == NO_ERR) {
//
//            // Affichage de la confirmation
//            $html_msg = new HTML_message("Confirmation Suppression abonnement");
//
//            $html_msg->setMessage("<br />L'abonnement a été supprimé.<br />");
//
//            $html_msg->addButton("BUTTON_OK", 'Abn-7');
//            $html_msg->buildHTML();
//
//            echo $html_msg->HTML_code;
//        } else {
//            $html_err = new HTML_erreur("Echec lors de la suppression d'un abonnement.");
//
//            $html_err->setMessage("Erreur : " . $error[$erreur->errCode] . "<br /> Paramètre : " . $erreur->param);
//
//            $html_err->addButton("BUTTON_OK", 'Abn-7');
//
//            $html_err->buildHTML();
//            echo $html_err->HTML_code;
//        }
//    } else {
//
//        if (isset($id_abonnement) && $id_abonnement > 0) {
//
//            $SESSION_VARS["id_abonnement"] = $id_abonnement;
//
//            // Affichage de la confirmation
//            $html_msg = new HTML_message("Suppression abonnement");
//
//            $html_msg->setMessage("<br />Êtes-vous sûr de vouloir supprimer cet abonnement ?<br /><input type=\"hidden\" id=\"hdd_delete_abn\" name=\"hdd_delete_abn\" value=\"oui\">");
//
//            $html_msg->addButton("BUTTON_OUI", 'Abn-6');
//            $html_msg->addButton("BUTTON_NON", 'Abn-7');
//            $html_msg->buildHTML();
//
//            echo $html_msg->HTML_code;
//
//        } else {
//
//            $html_err = new HTML_erreur("Echec lors de la suppression d'un abonnement.");
//
//            $html_err->setMessage("Erreur : L'abonnement n'existe pas.");
//
//            $html_err->addButton("BUTTON_OK", 'Abn-7');
//
//            $html_err->buildHTML();
//            echo $html_err->HTML_code;
//        }
//    }
//}
