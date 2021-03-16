<?php


/*
  Retrait d'un compte d'épargne client

  Description :
  Ce module crée 3 écrans :
 * Ope-11 : Choix d'une agence
 * Ope-12 : Opération en déplacé
 * Ope-13 : Menu Opération en déplacé

 */

// On charge les variables globales
//require_once 'lib/dbProcedures/login_func.php';
//require_once 'lib/misc/VariablesGlobales.php';
//require_once 'lib/misc/VariablesSession.php';
// Multi agence includes
require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/Client.php';
require_once 'ad_ma/app/models/Compta.php';
require_once 'ad_ma/app/models/Compte.php';
require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/Epargne.php';
require_once 'ad_ma/app/models/Parametrage.php';

require_once "lib/html/HTML_menu_gen.php";


//-----------------------------------------------------------------
//--------------- Ecran Ope-11 : Choix d'une agence ---------------
//-----------------------------------------------------------------
if ($global_nom_ecran == "Ope-11") {
    global $global_nom_login, $tableau_border;

//-------------------------------- ECRAN Ope-11 : Opération en déplacé ------------------------------------------
    // Clear all remote info
    resetVariablesGlobalesRemoteClient();

    // Création du formulaire
    $html = new HTML_GEN2();
    $html->setTitle(_("Opération en déplacé"));


    // Récupère la liste des agences distantes
    $ListeAgences = AgenceRemote::getListRemoteAgence();

    //tableau des écritures diverses
    $ExtraHtml = "<link rel=\"stylesheet\" href=\"/lib/misc/js/chosen/css/chosen.css\">";
    $ExtraHtml .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $ExtraHtml .= "<script src=\"/lib/misc/js/chosen/chosen.jquery.js\" type=\"text/javascript\"></script>";

    $ExtraHtml .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

    //En-tête du tableau
    $ExtraHtml .= "<TR bgcolor=$colb_tableau>";
    $ExtraHtml.="<TD align=\"center\" colspan= 2><b>"._("Choix de l'agence")."</b> </TD>";
    $ExtraHtml.="</TR>\n";

    $ExtraHtml .= "<TR bgcolor=$color>\n";

    // Comptes comptables qui peuvent être mouvementés dans le brouillard

    $ExtraHtml .= "<TD>\n";
    $ExtraHtml .= "<label>Agence : </label>";
    $ExtraHtml .= "</TD>\n";
    $ExtraHtml .= "<TD>\n";
    $ExtraHtml .= "<select required class=\"chosen-select\" NAME=\"agence_cliente\"  style=\"width:150px\" "  ;
    $ExtraHtml .= "\">\n";
    $ExtraHtml .= "<option value=\"0\">["._("Aucun")."]</option>\n";

    if (isset($ListeAgences))
        foreach($ListeAgences as $key=>$value)
                $ExtraHtml .= "<option value=$key>".$key." ".$value["app_db_description"]."</option>\n";



    $ExtraHtml .= "</select>\n";
    $ExtraHtml .= "</TD>";


    $ExtraHtml .= "</TR>";
    $ExtraHtml .= "</TABLE>\n";


    $ExtraHtml .= "<script type=\"text/javascript\">\n";
    $ExtraHtml .= "var config = { '.chosen-select' : {} }\n";
    $ExtraHtml .= "for (var selector in config) {\n";
    $ExtraHtml .= "$(selector).chosen(config[selector]); }\n";

    $ExtraHtml .= "</script>\n";

    $html->addHTMLExtraCode("html2",$ExtraHtml);
    $html->setHTMLExtraCodeProperties("html2", HTMP_IN_TABLE, true);

    //$html->setFieldProperties("IdAgence", FIELDP_JS_EVENT, array("onchange" => "alert(this.value);"));
    // Ajout des champs ornementaux
   /*$xtra1 = "<b>" . _("Choix d'une agence") . "</b>";
    $html->addHTMLExtraCode("htm1", $xtra1);
    $html->setHTMLExtraCodeProperties("htm1", HTMP_IN_TABLE, true);*/

    //ordonner les champs
    $html->setOrder(NULL, array( "html2"));

    //Boutons
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    //$html->addFormButton(1, 2, "back", _("Retour"), TYPB_SUBMIT);
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ope-12');
    //$html->setFormButtonProperties("back", BUTP_CHECK_FORM, false);
    //$html->setFormButtonProperties("back", BUTP_PROCHAIN_ECRAN, 'Gen-6');
    // Petite astuces pour permettre à l'utilisateur d'utiliser la touche 'Entrée' depuis le champs
//    $JS = "hasChecked = false;";
//    $html->addJS(JSP_FORM, "JS", $JS);
//    $html->addJS(JSP_BEGIN_CHECK, "changeChecked", "hasChecked = true;");
//    $html->setFormProperties(FORMP_JS_EVENT, array("onsubmit" => "if (hasChecked == false) {assign('Gen-6');checkForm();} hasChecked = false;"));
    //JavaScript

    $html->buildHTML();
    echo $html->getHTML();

}
//-----------------------------------------------------------------
//--------------- Ecran Ope-12 : Opération en déplacé ------------
//-----------------------------------------------------------------
elseif ($global_nom_ecran == "Ope-12") {
    global $global_remote_id_agence, $global_remote_agence_obj, $global_multidevise;
    resetVariablesGlobalesRemoteClient();
    if ((strstr($global_nom_ecran_prec, "Ope-11"))) {
        if (isset($_POST['agence_cliente'])) {
            $agenceCo = AgenceRemote::getRemoteAgenceConnection($_POST['agence_cliente']);
            if ($agenceCo != null){
                $global_remote_id_agence = $_POST['agence_cliente'];
            }
            else {
                $msg = _("L'agence selectionnée n'est pas connecté. Veuillez contacter votre administrateur");
                $html_err = new HTML_erreur("Échec de connection");
                $html_err->setMessage("Echec : " . $msg);
                $html_err->addButton("BUTTON_OK", 'Ope-11');
                $html_err->buildHTML();
                echo $html_err->HTML_code;
                die();
            }
        }
    }

//-------------------------------- ECRAN Ope-12 : Opération en déplacé ------------------------------------------
    // Récupère les infos de l'agence distante choisie
    $global_remote_agence_obj = AgenceRemote::getRemoteAgenceInfo($global_remote_id_agence);

    //Control sur le flag des prelevement de commissions si multidevises
    if($global_multidevise) {       
        $isAllowedTransaction = Divers::checkFlagPrelevementCommissionsForOperationDeplacer();  
                
        if(! $isAllowedTransaction) {
            $msg = _("Le paramétrage 'Appliquer la commission dans l'agence locale en mode multi-agences?' ne sont pas identiques dans les deux agences . Revoir paramétrage des deux agences.");
            $html_err = new HTML_erreur("Échec opération en déplacé");
            $html_err->setMessage("Erreur : " . $msg);
            $html_err->addButton("BUTTON_OK", 'Ope-11');
            $html_err->buildHTML();
            echo $html_err->HTML_code;
            die();
        }
    }    
    
    // Création du formulaire
    $html = new HTML_GEN2();
    $html->setTitle(_("Opération en déplacé"));

    $ListeAgences = AgenceRemote::getListRemoteAgence();

    $choix_agence = array();
    if (is_array($ListeAgences) && count($ListeAgences) > 0) {
        foreach ($ListeAgences as $key => $value) {
            $choix_agence[$key] = sprintf("%s (%s)", $value["app_db_description"], $value["id_agc"]);
        }
    }
    $html->addField("IdAgence", _("Agence"), TYPC_LSB, $global_remote_id_agence);
    $html->setFieldProperties("IdAgence", FIELDP_ADD_CHOICES, $choix_agence);
    $html->setFieldProperties("IdAgence", FIELDP_IS_LABEL, true);

    // Ajout des champs ornementaux
    $xtra1 = "<b>" . _("Choix d'un client") . "</b>";
    $html->addHTMLExtraCode("htm1", $xtra1);
    $html->setHTMLExtraCodeProperties("htm1", HTMP_IN_TABLE, true);

    //Partie sélection client
    $html->addField("num_client", _("N° de client"), TYPC_INT);
    $html->addLink("num_client", "rechercher", _("Rechercher"), "#");
    $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/ad_ma/app/views/client/rech_client_distant.php?m_agc=" . $_REQUEST['m_agc'] . "&field_name=num_client&remote_id_agence=$global_remote_id_agence', '" . _("Recherche") . "');return false;"));
    $html->setFieldProperties("num_client", FIELDP_JS_EVENT, array("onkeypress" => "return true;"));

    $html->setFieldProperties("num_client", FIELDP_IS_REQUIRED, true);

    //ordonner les champs
    $html->setOrder(NULL, array("htm1", "IdAgence", "num_client"));

    //Boutons
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "back", _("Retour"), TYPB_SUBMIT);
    $html->setFormButtonProperties("back", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Ope-13');
    $html->setFormButtonProperties("back", BUTP_PROCHAIN_ECRAN, 'Ope-11');

    // Petite astuces pour permettre à l'utilisateur d'utiliser la touche 'Entrée' depuis le champs
    $ControlNumClient = " function control(){\n";
    $ControlNumClient .=" var chaine;\n";
    $ControlNumClient .=" chaine=document.ADForm.num_client.value;\n";
    $ControlNumClient .=" var tab=chaine.split('-');\n";
    $ControlNumClient .=" if( tab.length >1 )\n";
    $ControlNumClient .=" document.ADForm.num_client.value=tab[1];\n";
    $ControlNumClient .=" else \n";
    $ControlNumClient .=" document.ADForm.num_client.value=tab[0];\n";
    $ControlNumClient .=" } \n";
    //$JS = "hasChecked = false;";
    $html->addJS(JSP_FORM, "JS", $JS);
    $html->addJS(JSP_FORM, "JS_NUM", $ControlNumClient);
    $html->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onClick" => "control();"));

    $html->buildHTML();
    echo $html->getHTML();
}
//-----------------------------------------------------------------
//------------ Ecran Ope-13 : Menu Opération en déplacé ----------
//-----------------------------------------------------------------
elseif ($global_nom_ecran == "Ope-13") {
    global $global_remote_id_agence, $global_remote_agence, $global_remote_agence_obj, $global_remote_client, $global_remote_id_client, $global_nom_login, $global_etat_client, $global_langue_rapport, $global_id_client, $global_client, $global_remote_id_exo, $global_remote_monnaie, $global_remote_monnaie_courante, $global_monnaie_courante, $global_id_profil, $global_multidevise, $global_photo_client, $global_signature_client;
//-------------------------------- ECRAN Ope-13 : Menu Opération en déplacé --------------------------------
    // Begin remote transaction
    $pdo_conn->beginTransaction();

    try {

        // Récupère l'ID client distant choisi
        if (isset($num_client) && $num_client > 0) {
            $global_remote_id_client = $num_client;

            // Init class
            $ComptaObj = new Compta($pdo_conn, $global_remote_id_agence);

            $global_remote_id_exo = $ComptaObj->getCurrentExercicesComptables();

            // Destroy object
            unset($ComptaObj);
        }

        // Init class
        $ClientObj = new Client($pdo_conn, $global_remote_id_agence);

        //Recupere le photo et signature pour client en operation dans agence externe
        $IMGS = $ClientObj->getBandeauImagesClient($global_remote_id_client);

        $global_photo_client = $IMGS["photo"];
        $global_signature_client = $IMGS["signature"];

        $clientDetailsArr = $ClientObj->getClientDatas($global_remote_id_client);

        if ($clientDetailsArr == NULL) { //Si le client n'existe pas
            $erreur = new HTML_erreur(_("Client inexistant"));
            $erreur->setMessage(_("Le numéro de client entré ne correspond à aucun client valide"));
            $erreur->addButton(BUTTON_OK, "Ope-12");
            $erreur->buildHTML();
            echo $erreur->HTML_code;
        } else { //Si le client existe
            // Enregistrement l'accès au menu
            ajout_historique(193, NULL, 'Opération en déplacé : ' . $global_remote_id_agence . '-' . $global_remote_id_client, $global_nom_login, date("r"), NULL);

            // Set global remote info
            $global_id_client = $clientDetailsArr['id_client'];
            $global_client = sprintf("%s ~ %s", $ClientObj->getClientName($global_remote_id_client), $global_remote_agence);
            $global_remote_client = $ClientObj->getClientName($global_remote_id_client);

            // Init class
            $AgenceObj = new Agence($pdo_conn);

            $id_agc = $AgenceObj->getNumAgc();
            $agenceArr = $AgenceObj->getAgenceDatas($id_agc);
            $type_num_cpte = $agenceArr['type_numerotation_compte'];
            $agence_statut = $agenceArr['statut'];

            switch ($type_num_cpte) {
                case 1:
                    $global_id_client_formate = sprintf("%06d", $clientDetailsArr['id_client']);
                    break;
                case 2:
                    $global_id_client_formate = sprintf("%05d", $clientDetailsArr['id_client']);
                    break;
                case 3:
                    $global_id_client_formate = sprintf("%07d", $clientDetailsArr['id_client']);
                    break;
                case 4:
                    $global_id_client_formate = $AgenceObj->makeNumClient($clientDetailsArr['id_client']);
                    break;
            }

            // Destroy object
            unset($AgenceObj);

            $global_etat_client = $ClientObj->getEtatClient($global_remote_id_client);
            $global_langue_rapport = $clientDetailsArr['langue_correspondance'];

            if ($ClientObj->isClientDebiteur($global_remote_id_client)) {
                $global_client_debiteur = TRUE;
            } else {
                $global_client_debiteur = FALSE;
            }

            // Destroy object
            unset($ClientObj);

            $html = new HTML_GEN2();
            $html->setTitle(_("Menu Opération en déplacé"));

            // Init class
            $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);

            $id_cpte_base = $CompteObj->getBaseAccountID($global_remote_id_client);

            $InfoCpteBase = $CompteObj->getAccountDatas($id_cpte_base);

            // Destroy object
            unset($CompteObj);

            // Set monnaie
            $global_remote_monnaie = $InfoCpteBase["devise"];
            $global_remote_monnaie_courante = $InfoCpteBase["devise"];

            // Store local monnaie courante
            $global_monnaie_courante_tmp = $global_monnaie_courante;
            $global_monnaie_courante = $global_remote_monnaie_courante;

            // Init class
            $ListeAgences = AgenceRemote::getListRemoteAgence();

            $choix_agence = array();
            if (is_array($ListeAgences) && count($ListeAgences) > 0) {
                foreach ($ListeAgences as $key => $value) {
                    $choix_agence[$key] = sprintf("%s (%s)", $value["app_db_description"], $value["id_agc"]);
                }
            }
            $html->addField("IdAgence", _("Agence"), TYPC_LSB, $global_remote_id_agence);
            $html->setFieldProperties("IdAgence", FIELDP_ADD_CHOICES, $choix_agence);
            $html->setFieldProperties("IdAgence", FIELDP_IS_LABEL, true);

            // Init class
            $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);

            //affichage de tous les comptes du client et s'il n'y a rien...ne rien faire
            //retirer de la liste les comptes à retrait unique
            $TempListeComptes = $EpargneObj->getComptesEpargne($global_remote_id_client);

            $choix = $ListeComptes = array();
            if (isset($TempListeComptes)) {
                $ListeComptes = $EpargneObj->getComptesPossible($TempListeComptes);
                if (isset($ListeComptes)) {
                    //index par id_cpte pour la listbox
                    foreach ($ListeComptes as $key => $value)
                        $choix[$key] = $value["num_complet_cpte"] . " " . $value["intitule_compte"];
                }
            }

            // Sort accounts alphabetically
            asort($choix, SORT_REGULAR);

            $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
            $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);

            //en fonction du choix du compte, afficher les infos avec le onChange javascript
            $solde_disponible = 0;
            $display_retrait_link = FALSE;

            $codejs = "
                    function getInfoCompte()
                  {
                    if (document.ADForm.HTML_GEN_LSB_NumCpte.value == 0){
                        document.ADForm.solde.value = formateMontant(0);
                    }
                    ";
            if (isset($ListeComptes) && count($ListeComptes) > 0) {
                $html->setFieldProperties("NumCpte", FIELDP_HAS_CHOICE_AUCUN, false);
                foreach ($ListeComptes as $key => $value) {
                    $solde_disponible = $EpargneObj->getSoldeDisponible($value["id_cpte"]);
                    if ($solde_disponible > 0) {
                        $display_retrait_link = TRUE;
                    }
                    $codejs .= "
                         if (document.ADForm.HTML_GEN_LSB_NumCpte.value == $key)
                         {
                            document.ADForm.solde.value = formateMontant(" . $solde_disponible . ");";
                    $codejs .= "
                       };";
                }
            } else {
                $html->setFieldProperties("NumCpte", FIELDP_HAS_CHOICE_AUCUN, true);
            }

            $codejs .= "
                   }
                     getInfoCompte();";

            $html->setFieldProperties("NumCpte", FIELDP_JS_EVENT, array("onChange" => "getInfoCompte();"));
            $html->addJS(JSP_FORM, "JS1", $codejs);

            $order_arr = array("IdAgence", "NumCpte");

            $access_solde = get_profil_acces_solde($global_id_profil);

            if ($access_solde) {
                // Contrôle sur l'accès au solde
                $html->addField("solde", _("Solde disponible"), TYPC_MNT);
                $html->setFieldProperties("solde", FIELDP_IS_LABEL, true);

                $order_arr[] = "solde";
            }
            $html->setOrder(NULL, $order_arr);

            // Destroy object
            unset($EpargneObj);

            $html->buildHTML();
            echo $html->getHTML();

            $MyMenu = new HTML_menu_gen(""); // Menu clientèle , Opération en déplacé
            // Vérifié si l'agence est ouverte
            if ($agence_statut == 1) {
                if (isset($ListeComptes) && count($ListeComptes) > 0) {

                    $fonction_retrait = 92;
                    $fonction_retrait_autorise = 64;
                    $fonction_depot = 93;
                    
                    if ($global_multidevise) {
                        $ecran_depot = 'Dpm-1';
                        $ecran_retrait = 'Rtm-1';
                        
                    } else {
                        $ecran_depot = 'Dcp-11';
                        $ecran_retrait = 'Rcp-11';
                    }

                    if ($display_retrait_link) {
                        $MyMenu->addItem(_("Retrait en déplacé"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=$ecran_retrait", $fonction_retrait, "$http_prefix/images/retrait.gif", "1");
                        $MyMenu->addItem(_("Paiement retrait en déplacé autorisé"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Prd-11", $fonction_retrait_autorise, "$http_prefix/images/retrait.gif", "1");
                    }

                    $MyMenu->addItem(_("Dépôt en déplacé"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=$ecran_depot", $fonction_depot, "$http_prefix/images/depot.gif", "2");
                }
            } else {
                $MyMenu->addItem(_("<b style='color:#FF0000;'>L'Agence est actuellement fermée.</b>"), "#", 0, "$http_prefix/images/travaux.gif");
            }
            
            $MyMenu->addItem(_("Consultation du client"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Mac-1", 0, "$http_prefix/images/consultation_client.gif", "3");
            
            $MyMenu->addItem(_("Infos financières"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Mac-2", 0, "$http_prefix/images/consult_cpt.gif", "4");

            $MyMenu->addItem(_("Liste des mandataires"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Mac-3", 0, "$http_prefix/images/gestion_mandats.gif", "5");

            $MyMenu->addItem(_("Consultation de compte"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Vcp-11", 0, "$http_prefix/images/consult_cpt.gif", "6");

            $MyMenu->addItem(_("Retour menu principal"), "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Gen-3", 0, "$http_prefix/images/back.gif", "0");
            $MyMenu->buildHTML();

            echo $MyMenu->HTMLCode;
        }

        // Commit transaction
        $pdo_conn->commit();
    } catch (PDOException $e) {

        $pdo_conn->rollBack(); // Roll back remote transaction

        $erreur = new HTML_erreur(_("Client inexistant"));
        $erreur->setMessage(_("Le numéro de client entré ne correspond à aucun client valide"));
        $erreur->addButton(BUTTON_OK, "Ope-12");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
    }

    // Restore local monnaie courante
    $global_monnaie_courante = $global_monnaie_courante_tmp;
}

// Fermer la connexion BDD
unset($pdo_conn);
