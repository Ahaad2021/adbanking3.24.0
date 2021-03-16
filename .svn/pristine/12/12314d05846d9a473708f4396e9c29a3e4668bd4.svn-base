<?php


/*

  Retrait Généraliste en déplacé d'un compte d'épargne client

  Description :
  Ce module crée 4 écrans :
 * Rcp-11 : Choix du compte et du type de retrait
 * Rcp-21 : Introduction du montant
 * Rcp-31 : Confirmation du montant
 * Rcp-41 : Confirmation du retrait

 */

// Multi agence includes
require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/Audit.php';
require_once 'ad_ma/app/models/Client.php';
require_once 'ad_ma/app/models/Compta.php';
require_once 'ad_ma/app/models/Compte.php';
require_once 'ad_ma/app/models/Credit.php';
require_once 'ad_ma/app/models/Devise.php';
require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/Epargne.php';
require_once 'ad_ma/app/models/Guichet.php';
require_once 'ad_ma/app/models/Historique.php';
require_once 'ad_ma/app/models/Parametrage.php';
require_once 'ad_ma/app/models/TireurBenef.php';

require_once 'ad_ma/app/controllers/epargne/Retrait.php';
require_once 'ad_ma/app/controllers/epargne/Recu.php';

require_once 'lib/dbProcedures/billetage.php';
require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'modules/rapports/xml_devise.php';
require_once 'modules/rapports/xslt.php';
require_once 'modules/epargne/recu.php';

//-----------------------------------------------------------------
//------- Ecran Rcp-11 Choix du compte et du type de retrait -------
//-----------------------------------------------------------------
if ($global_nom_ecran == "Rcp-11") {
    global $global_remote_id_agence, $global_remote_id_client, $global_monnaie_courante, $global_remote_monnaie_courante;

    // Clear data session variables
    unset($SESSION_VARS['NumCpte'], $SESSION_VARS['type_retrait'], $SESSION_VARS['mandat'], $SESSION_VARS['frais_retrait'], $SESSION_VARS['type_recherche'], $SESSION_VARS['field_name'], $SESSION_VARS['nom_ben'], $SESSION_VARS['field_id'], $SESSION_VARS['id_ben'], $SESSION_VARS['tib'], $SESSION_VARS['beneficiaire'], $SESSION_VARS['tireur'], $SESSION_VARS['denomination'], $SESSION_VARS['adresse'], $SESSION_VARS['code_postal'], $SESSION_VARS['ville'], $SESSION_VARS['num_tel'], $SESSION_VARS['num_piece'], $SESSION_VARS['lieu_delivrance'], $SESSION_VARS['id_mandat'], $SESSION_VARS['remarque'], $SESSION_VARS['communication'], $SESSION_VARS['mnt'], $SESSION_VARS['num_chq'], $SESSION_VARS['date_chq'], $SESSION_VARS['envoi'], $SESSION_VARS['gpe'], $SESSION_VARS['gpe']['denom'], $SESSION_VARS['gpe']['pers_ext'], $SESSION_VARS['gpe']['denomination'], $SESSION_VARS['gpe']['id_pers_ext'], $SESSION_VARS['id_pers_ext']);

    // Store local monnaie courante
    $global_monnaie_courante_tmp = $global_monnaie_courante;
    $global_monnaie_courante = $global_remote_monnaie_courante;

    // Begin remote transaction
    $pdo_conn->beginTransaction();

    // Création du formulaire
    $html = new HTML_GEN2();
    $html->setTitle(_("Retrait en déplacé sur un compte : choix du compte"));

    // Init class
    $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);

    //affichage de tous les comptes du client et s'il n'y a rien...ne rien faire
    //retirer de la liste les comptes à retrait unique
    $TempListeComptes = $EpargneObj->getComptesEpargne($global_remote_id_client);

    $choix = array();
    if (isset($TempListeComptes)) {
        $ListeComptes = $EpargneObj->getComptesRetraitPossible($TempListeComptes);
        if (isset($ListeComptes)) {
            //index par id_cpte pour la listbox
            foreach ($ListeComptes as $key => $value)
                $choix[$key] = $value["num_complet_cpte"] . " " . $value["intitule_compte"];
        }
    }

    // Destroy object
    unset($EpargneObj);

    $html->addField("NumCpte", _("Numéro de compte"), TYPC_LSB);
    $html->setFieldProperties("NumCpte", FIELDP_ADD_CHOICES, $choix);
    $html->setFieldProperties("NumCpte", FIELDP_IS_REQUIRED, true);

    // Ajout des champs ornementaux
    $xtra1 = "<b>" . _("Choix du compte") . "</b>";
    $html->addHTMLExtraCode("htm1", $xtra1);
    $html->setHTMLExtraCodeProperties("htm1", HTMP_IN_TABLE, true);
    $xtra2 = "<b>" . _("Choix du type de retrait") . "</b>";
    $html->addHTMLExtraCode("htm2", $xtra2);
    $html->setHTMLExtraCodeProperties("htm2", HTMP_IN_TABLE, true);

    // Recupere la liste des devises
    $ListeDevises = Divers::getListDevises($pdo_conn, $global_remote_id_agence);

    $choix_devises = array();
    if (is_array($ListeDevises) && count($ListeDevises) > 0) {
        foreach ($ListeDevises as $key => $value) {
            $choix_devises[$key] = trim($value["code_devise"]);
        }
    }
    $html->addField("devise", _("Devise du compte"), TYPC_LSB);
    $html->setFieldProperties("devise", FIELDP_ADD_CHOICES, $choix_devises);
    $html->setFieldProperties("devise", FIELDP_HAS_CHOICE_AUCUN, true);

    // Gestion des champs liés au type de produit choisi
    $html->addTable("ad_cpt", OPER_INCLUDE, array("etat_cpte"));
    $html->addTable("adsys_produit_epargne", OPER_INCLUDE, array("libel", "duree_min_retrait_jour")); // , "devise"
    $html->setFieldProperties("libel", FIELDP_WIDTH, 40);

    //mettre les champs en label
    $fieldslabel = array("libel", "etat_cpte", "duree_min_retrait_jour", "devise");
    foreach ($fieldslabel as $value) {
        $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
        $html->setFieldProperties($value, FIELDP_IS_REQUIRED, false);
    };

    //en fonction du choix du compte, afficher les infos avec le onChange javascript
    $codejs = "
            function getInfoCompte()
          {
            ";
    if (isset($ListeComptes)) {
        foreach ($ListeComptes as $key => $value) {
            $codejs .= "
                 if (document.ADForm.HTML_GEN_LSB_NumCpte.value == $key)
                 {
                 document.ADForm.HTML_GEN_LSB_etat_cpte.value = " . $value["etat_cpte"] . ";
                 document.ADForm.libel.value = \"" . $value["libel"] . "\";
                 document.ADForm.HTML_GEN_LSB_devise.value = '" . $value["devise"] . "';";
            if ($value["duree_min_retrait_jour"] > 0)
                $codejs .= "
            document.ADForm.duree_min_retrait_jour.value = " . $value["duree_min_retrait_jour"] . ";";
            else
                $codejs .= "
                        document.ADForm.duree_min_retrait_jour.value = '0';";
            $codejs .= "
               };";
        }
    }
    $codejs .= "
           }
             getInfoCompte();";

    $html->setFieldProperties("NumCpte", FIELDP_JS_EVENT, array("onChange" => "getInfoCompte();"));
    $html->addJS(JSP_FORM, "JS1", $codejs);

    // Gestion du type de retrait
    $html->addField("type_retrait", _("Type de retrait"), TYPC_LSB);
    $html->setFieldProperties("type_retrait", FIELDP_IS_REQUIRED, true);
    $choix2 = array();
    //les clés des choix correspondent à la table $adsys['adsys_type_piece_payement']
    $choix2[1] = _('Retrait Cash avec impression reçu');
    $choix2[15] = _('Retrait Cash sur présentation d\'un chèque guichet');
    $choix2[4] = _('Retrait Cash sur présentation d\'une autorisation de retrait sans livret/chèque');

    $html->setFieldProperties("type_retrait", FIELDP_ADD_CHOICES, $choix2);

    //ordonner les champs
    $html->setOrder(NULL, array("htm1", "NumCpte", "libel", "devise", "etat_cpte", "duree_min_retrait_jour", "htm2", "type_retrait"));

    //Boutons
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rcp-21');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Ope-13');

    $html->buildHTML();
    echo $html->getHTML();

    // Commit transaction
    $pdo_conn->commit();

    // Restore local monnaie courante
    $global_monnaie_courante = $global_monnaie_courante_tmp;
}
//-----------------------------------------------------------------
//------------ Ecran Rcp-21 Introduction du montant ----------------
//-----------------------------------------------------------------
else if ($global_nom_ecran == "Rcp-21") {
    global $global_remote_id_agence, $global_remote_id_client, $global_monnaie_courante, $global_remote_monnaie_courante, $global_id_profil;

    // Store local monnaie courante
    $global_monnaie_courante_tmp = $global_monnaie_courante;
    $global_monnaie_courante = $global_remote_monnaie_courante;


    /*************************Ticket Jira AT-44 *******************************************************/
    unset($SESSION_VARS['id_dem']);
    $communication = $remarque = "";
    if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0) {

        $SESSION_VARS['id_dem'] = $_GET['id_dem'];

        $infoRetraitDeplaceAttente = getRetraitDeplaceAttenteAutorise($SESSION_VARS['id_dem'], $global_remote_id_client);

        $type_retrait = trim($infoRetraitDeplaceAttente['type_retrait']);
        $mnt = recupMontant($infoRetraitDeplaceAttente['montant_retrait']);
        $frais_retrait_cpt = recupMontant($infoRetraitDeplaceAttente['frais_retrait_cpte']);
        $mandat = isset($infoRetraitDeplaceAttente['mandat']) ? trim($infoRetraitDeplaceAttente['mandat']) : 0;
        $beneficiaire = trim($infoRetraitDeplaceAttente['beneficiaire']);
        $nom_ben = trim($infoRetraitDeplaceAttente['nom_ben']);
        $num_chq = trim($infoRetraitDeplaceAttente['num_chq']);
        $date_chq = pg2phpDate($infoRetraitDeplaceAttente['date_chq']);
        $id_pers_ext = trim($infoRetraitDeplaceAttente['id_pers_ext']);
        $id_ben = trim($infoRetraitDeplaceAttente['id_ben']);
        $denomination = trim($infoRetraitDeplaceAttente['denomination']);
        $communication = trim($infoRetraitDeplaceAttente['communication']);
        $remarque = trim($infoRetraitDeplaceAttente['remarque']);
        $cpte = trim($infoRetraitDeplaceAttente['id_cpte_distant']);

        if ($cpte != '') {
            $NumCpte = trim($infoRetraitDeplaceAttente['id_cpte_distant']);
        }

        $SESSION_VARS['tib'] = json_decode($infoRetraitDeplaceAttente['tib'],true);
    }
    /*********************************************************************************/


    // Begin remote transaction
    $pdo_conn->beginTransaction();

    //Enregistrement des informations postées en Rcp-11
    if (isset($NumCpte)) {
        $SESSION_VARS["NumCpte"] = $NumCpte;
    }

    if (isset($type_retrait) || isset($_GET['id_dem']) && $_GET['id_dem'] > 0) {
        $SESSION_VARS["type_retrait"] = $type_retrait;
    }

    if (isset($frais_retrait_cpt) || isset($_GET['id_dem']) && $_GET['id_dem'] > 0) {
        $SESSION_VARS["frais_retrait"] = $frais_retrait_cpt;
    }

    switch ($SESSION_VARS['type_retrait']) {
        case 1:
            $charTitre = _("Retrait compte en déplacé : montant");
            $charMnt = _("du compte");
            $charCv = _("à remettre au guichet");
            break;
        case 15:
            $charTitre = _("Retrait en déplacé par chèque : montant");
            $charMnt = _("du compte");
            $charCv = _("à remettre au guichet");
            $charCheque = _("Informations chèque guichet");
            break;
        case 4:
            $charTitre = _("Retrait en déplacé par chèque-guichet : montant");
            $charMnt = _("Montant du chèque");
            $charCv = _("à remettre au guichet");
            $charCheque = _("Informations Autorisation de retrait");
            break;
        default:
            $charTitre = _("Erreur ecran Rcp-2");
            $charMnt = _("montant dans la devise du compte");
            $charCv = _("contrevaleur à donner au guichet");
            break;
    }

    $html = new HTML_GEN2($charTitre);

/***********************Ticket Jira At-44******************************************/
    if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0) {
        $jsPreLoadData = "
                    // Default values
                    if (document.ADForm.mnt) {
                        document.ADForm.mnt.value = '$mnt';
                        document.ADForm.mnt.readOnly = true;
                    }
                    if (document.ADForm.frais_retrait_cpt) {
                        document.ADForm.frais_retrait_cpt.value = '$frais_retrait_cpt';
                        if (document.ADForm.frais_retrait_cpt.value > 0) {
                            document.ADForm.frais_retrait_cpt.readOnly = true;
                        }
                    }
                    if (document.ADForm.HTML_GEN_LSB_beneficiaire) {
                        document.ADForm.HTML_GEN_LSB_beneficiaire.value = '$beneficiaire';
                    }
                    if (document.ADForm.nom_ben) {
                        document.ADForm.nom_ben.value = '$nom_ben';
                    }
                    if (document.ADForm.num_chq) {
                        document.ADForm.num_chq.value = '$num_chq';
                        document.ADForm.num_chq.readOnly = true;
                    }
                    if (document.ADForm.HTML_GEN_date_date_chq) {
                        document.ADForm.HTML_GEN_date_date_chq.value = '$date_chq';
                        document.ADForm.HTML_GEN_date_date_chq.readOnly = true;
                    }
                    if (document.ADForm.HTML_GEN_LSB_mandat) {
                        document.ADForm.HTML_GEN_LSB_mandat.value = '$mandat';
                    }
                    if (document.ADForm.denomination) {
                        document.ADForm.denomination.value = '$denomination';
                    }
                    if (document.ADForm.id_pers_ext) {
 		                document.ADForm.id_pers_ext.value = '$id_pers_ext';
                    }
                    if (document.ADForm.id_ben) {
 		                document.ADForm.id_ben.value = '$id_ben';
                    }
                    if (document.ADForm.communication) {
 		                document.ADForm.communication.value = '$communication';
 		                document.ADForm.communication.readOnly = true;
                    }
                    if (document.ADForm.remarque) {
 		                document.ADForm.remarque.value = '$remarque';
 		                document.ADForm.remarque.readOnly = true;
                    }
        ";

        $html->addJS(JSP_FORM, "JS_PRELOAD_DATA", $jsPreLoadData);
    }
    /***********************************************************************************/

    // Ajout des champs ornementaux
    $xtra1 = "<b>" . _("Informations compte") . "</b>";
    $html->addHTMLExtraCode("htm1", $xtra1);
    $html->setHTMLExtraCodeProperties("htm1", HTMP_IN_TABLE, true);
    $xtra2 = "<b>" . _("Montant à retirer") . "</b>";
    $html->addHTMLExtraCode("htm2", $xtra2);
    $html->setHTMLExtraCodeProperties("htm2", HTMP_IN_TABLE, true);

    // Init class
    $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);
    $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);
    $DeviseObj = new Devise($pdo_conn, $global_remote_id_agence);

    //Informations compte
    $cpteSource = $CompteObj->getAccountDatas($SESSION_VARS['NumCpte']);

    $soldeCptSource = $cpteSource["solde"];
    $soldeDispo = $EpargneObj->getSoldeDisponible($cpteSource['id_cpte']); // - $cpteSource['frais_retrait_cpt'];

    $DEV_SRC = $DeviseObj->getInfoDevise($cpteSource['devise']);

    // Destroy object
    unset($DeviseObj);

    $precision_dev_src = $DEV_SRC["precision"];
    Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $cpteSource['devise']);
    setMonnaieCourante($cpteSource['devise']);

    $infoCpte = $CompteObj->getAccountDatas($SESSION_VARS['NumCpte']);
    $MANDATS = $EpargneObj->getListeMandatairesActifs($SESSION_VARS['NumCpte']);

    // Destroy object
    unset($CompteObj);

    if ($MANDATS != NULL) {
        foreach ($MANDATS as $key => $value) {
            $MANDATS_LSB[$key] = $value['libelle'];
            if ($key == 'CONJ') {
                $JS_open .= "if (document.ADForm.HTML_GEN_LSB_mandat.value == '$key')
      {
      OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/info_mandat_distant.php?m_agc=".$_REQUEST['m_agc']."&id_cpte=" . $SESSION_VARS['NumCpte'] . "');
      return false;
      }";
            } else {
                $JS_open .=
                        "if (document.ADForm.HTML_GEN_LSB_mandat.value == '$key')
      {
      OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/info_mandat_distant.php?m_agc=".$_REQUEST['m_agc']."&id_mandat=$key');
      return false;
      }";
            }
        }
    }
    $JS_change =
            "if (document.ADForm.HTML_GEN_LSB_mandat.value != 'EXT')
      {
      document.ADForm.denomination.value = '';
      document.ADForm.id_pers_ext.value = '';
      }";

    // Init class
    $ClientObj = new Client($pdo_conn, $global_remote_id_agence);

    $client_name = trim($ClientObj->getClientName($global_remote_id_client));

    if (in_array($SESSION_VARS['type_retrait'], array(8,15))) { //creation liste deroulante pour beneficiaire lor du retrait par cheque voir ticket 728 et 564
        $html->addField("beneficiaire", _("Bénéficiaire"), TYPC_LSB);
        $html->setFieldProperties("beneficiaire", FIELDP_IS_REQUIRED, true);
        $html->setFieldProperties("beneficiaire", FIELDP_ADD_CHOICES, array("TITS" => _("Titulaire") . " (" . $ClientObj->getClientName($global_remote_id_client) . ")"));

        //ajout des mandataires dans la liste deroulante beneficiaire
        $MANDATAIRES = array();
        $LSB_MANDATS = array();
        $mandatClientId = array();
        $EpargneMandatObj = new Epargne($pdo_conn, $global_remote_id_agence);
        $ClientMandatObj = new Client($pdo_conn, $global_remote_id_agence);
        $MANDATAIRES = $EpargneMandatObj->getMandats($SESSION_VARS['NumCpte']);
        if ($MANDATAIRES != null){
            foreach ($MANDATAIRES as $keyMandat => $valueMandat) {
                $mandatClientId = $ClientMandatObj->getPersonneExt(array('id_pers_ext' => $valueMandat['id_pers_ext']));
                if ($mandatClientId[0]['id_client'] != null){
                    $LSB_MANDATS[$mandatClientId[0]['id_client']] = "Mandataire ( ".$valueMandat['denomination']." )";
                }
                else{ //Affichage des mandataires sans id client
                    $LSB_MANDATS[-1*$keyMandat] = "Mandataire ( ".$valueMandat['denomination']." )";
                }
            }
        }
        if ($LSB_MANDATS != null){
            $html->setFieldProperties("beneficiaire", FIELDP_ADD_CHOICES, $LSB_MANDATS);
        }
        unset($EpargneMandatObj);
        unset($ClientMandatObj);

        $html->setFieldProperties("beneficiaire", FIELDP_HAS_CHOICE_AUCUN, true);
        $html->setFieldProperties("beneficiaire", FIELDP_HAS_CHOICE_TOUS, false);
        $html->setFieldProperties("beneficiaire", FIELDP_DEFAULT, $SESSION_VARS['id_mandat']);

        $JS_change_benef =
          "if (document.ADForm.HTML_GEN_LSB_beneficiaire.value == '0')
          {
            document.ADForm.nom_ben.value = '';
            document.ADForm.id_ben.value = '';
          }else if (document.ADForm.HTML_GEN_LSB_beneficiaire.value == 'TITS' || document.ADForm.HTML_GEN_LSB_beneficiaire.value > 0)
          {
            document.ADForm.nom_ben.value = '';
            document.ADForm.id_ben.value = 0;
          }
          else if (document.ADForm.HTML_GEN_LSB_beneficiaire.value < 0){
            document.ADForm.nom_ben.value = '';
            document.ADForm.id_ben.value = document.ADForm.HTML_GEN_LSB_beneficiaire.value;
          }else if (document.ADForm.HTML_GEN_LSB_beneficiaire.value == 'EXT')
          {
            document.ADForm.nom_ben.value = '';
            document.ADForm.id_ben.value = '';
          }";//document.ADForm.nom_ben.value = '" . $ClientObj->getClientName($global_remote_id_client) . "';
        //document.ADForm.id_ben.value = '" . $global_remote_id_client . "';
    }

    // Destroy object
    unset($ClientObj);

    $html->addField("mandat", _("Donneur d'ordre"), TYPC_LSB);
    $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("0" => _("Titulaire") . " (" . $client_name . ")"));
    if ($MANDATS_LSB != NULL) {
        $MANDATS_LSB = array_flip($MANDATS_LSB); // array(valeur = >cle) au lieu de array(cle => valeur)
        unset($MANDATS_LSB[$client_name]); //on supprime le nom du titulaire dans la liste déroulante
        $MANDATS_LSB = array_flip($MANDATS_LSB); // on remet le array(cle => valeur)
        $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, $MANDATS_LSB);
        /*if (in_array($SESSION_VARS['type_retrait'], array(8,15))) { //population liste deroulante pour beneficiaire lor du retrait par cheque et controle JS voir ticket 728 et 564
            $html->setFieldProperties("beneficiaire", FIELDP_ADD_CHOICES, $MANDATS_LSB);

            foreach ($MANDATS_LSB as $key => $value) {
                $JS_change_benef .= "
                 else if (document.ADForm.HTML_GEN_LSB_beneficiaire.value == '$key')
                 {
                   document.ADForm.nom_ben.value = '" . $value . "';
                   document.ADForm.id_ben.value = '" . $key . "';
                 }";
            }
        }*/
    }
    if (in_array($SESSION_VARS['type_retrait'], array(8,15))) { //liste deroulante pour beneficiaire lor du retrait par cheque : controle JS voir ticket 728 et 564
      $html->setFieldProperties("beneficiaire", FIELDP_ADD_CHOICES, array("EXT" => _("Personne non cliente")));
      $html->setFieldProperties("beneficiaire", FIELDP_JS_EVENT, array("onchange" => $JS_change_benef));
    }
    $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_TOUS, false);
    $html->setFieldProperties("mandat", FIELDP_DEFAULT, $SESSION_VARS['id_mandat']);
    $html->setFieldProperties("mandat", FIELDP_JS_EVENT, array("onchange" => $JS_change));
    $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("EXT" => _("Personne non cliente")));
    $html->addJS(JSP_BEGIN_CHECK, "limitation_check", $JS_check);
    $html->addLink("mandat", "afficher", _("Afficher"), "#");
    $html->setLinkProperties("afficher", LINKP_JS_EVENT, array("onclick" => $JS_open));

    $SESSION_VARS['mandat'] = $MANDATS_LSB;
    $JS_rech =
            "if (document.ADForm.HTML_GEN_LSB_mandat.value == 'EXT')
      {
      OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/gest_pers_ext_distant.php?m_agc=".$_REQUEST['m_agc']."&denom=denomination&pers_ext=id_pers_ext');
      return false;
      }";
    $include = array("denomination");
    $html->addTable("ad_pers_ext", OPER_INCLUDE, $include);
    $html->setFieldProperties("denomination", FIELDP_IS_LABEL, true);
    $html->setFieldProperties("denomination", FIELDP_IS_REQUIRED, false);
    $html->setFieldProperties("denomination", FIELDP_DEFAULT, $SESSION_VARS['denomination']);
    $html->addLink("denomination", "rech_pers_ext", _("Rechercher"), "#");
    $html->setLinkProperties("rech_pers_ext", LINKP_JS_EVENT, array("onclick" => $JS_rech));

    $html->addHiddenType("id_pers_ext", $SESSION_VARS['id_pers_ext']);

    $JS_check =
            "if (document.ADForm.HTML_GEN_LSB_mandat.value == 'EXT' && document.ADForm.id_pers_ext.value == '')
      {
      msg += '" . _("- Vous devez choisir une personne non cliente") . "\\n';
      ADFormValid=false;
      }";
    $html->addJS(JSP_BEGIN_CHECK, "JS2", $JS_check);

    $html->addHTMLExtraCode("mandat_sep", "<br/>");
    $champsProduit = array(); // "libel"
    $champsCpte = array(); // "num_complet_cpte", "intitule_compte", "etat_cpte"
    $ordre = array("mandat", "denomination", "mandat_sep", "htm1", "num_complet_cpte", "libel", "intitule_compte", "etat_cpte");
    $labelField = array(); // "libel", "etat_cpte", "num_complet_cpte", "intitule_compte", 

    $html->addField("num_complet_cpte", _("N° compte"), TYPC_TXT);
    $html->setFieldProperties("num_complet_cpte", FIELDP_DEFAULT, $cpteSource["num_complet_cpte"]);
    $html->setFieldProperties("num_complet_cpte", FIELDP_IS_LABEL, true);

    $html->addField("libel", _("Libellé du produit d'épargne"), TYPC_TXT);
    $html->setFieldProperties("libel", FIELDP_DEFAULT, $cpteSource["libel"]);
    $html->setFieldProperties("libel", FIELDP_IS_LABEL, true);

    $html->addField("intitule_compte", _("Intitulé du compte"), TYPC_TXT);
    $html->setFieldProperties("intitule_compte", FIELDP_DEFAULT, $cpteSource["intitule_compte"]);
    $html->setFieldProperties("intitule_compte", FIELDP_IS_LABEL, true);

    $etat_cpte = $adsys["adsys_etat_cpt_epargne"][$cpteSource["etat_cpte"]];
    $html->addField("etat_cpte", _("Etat du compte"), TYPC_TXT);
    $html->setFieldProperties("etat_cpte", FIELDP_DEFAULT, $etat_cpte);
    $html->setFieldProperties("etat_cpte", FIELDP_IS_LABEL, true);
    
    $access_solde = get_profil_acces_solde($global_id_profil, $cpteSource['id_prod']);
	
    if($access_solde) {
        $html->addField("solde", _("Solde"), TYPC_MNT);
        $html->setFieldProperties("solde", FIELDP_DEFAULT, $cpteSource["solde"]);
        $html->setFieldProperties("solde", FIELDP_IS_LABEL, true);

        array_push($ordre, "solde");
    }

    if ($cpteSource['mnt_min_cpte'] > 0) {
        $html->addField("mnt_min_cpte", _("Montant minimum"), TYPC_MNT);
        $html->setFieldProperties("mnt_min_cpte", FIELDP_DEFAULT, $cpteSource["mnt_min_cpte"]);
        $html->setFieldProperties("mnt_min_cpte", FIELDP_IS_LABEL, true);

        array_push($ordre, "mnt_min_cpte");
    }

    if (($cpteSource['mnt_bloq'] + $cpteSource['mnt_bloq_cre']) > 0) {
        $html->addField("mnt_bloq", _("Montant bloqué"), TYPC_MNT);
        $html->setFieldProperties("mnt_bloq", FIELDP_DEFAULT, ($cpteSource['mnt_bloq'] + $cpteSource['mnt_bloq_cre']));
        $html->setFieldProperties("mnt_bloq", FIELDP_IS_LABEL, true);

        array_push($ordre, "mnt_bloq");
    }

    if ($cpteSource['decouvert_max'] > 0) {
        $html->addField("decouvert_max", _("Découvert maximum autorisé"), TYPC_MNT);
        $html->setFieldProperties("decouvert_max", FIELDP_DEFAULT, $cpteSource["decouvert_max"]);
        $html->setFieldProperties("decouvert_max", FIELDP_IS_LABEL, true);

        array_push($ordre, "decouvert_max");
    }

    if($access_solde) {
        $html->addField("solde_dispo", _("Solde disponible"), TYPC_MNT);
        $html->setFieldProperties("solde_dispo", FIELDP_DEFAULT, $soldeDispo);
        $html->setFieldProperties("solde_dispo", FIELDP_IS_LABEL, true);

        array_push($ordre, "solde_dispo");
    }

    $html->addField("frais_retrait_cpt", _("Frais de retrait"), TYPC_MNT);
    $html->setFieldProperties("frais_retrait_cpt", FIELDP_DEFAULT, $cpteSource["frais_retrait_cpt"]);
    $html->setFieldProperties("frais_retrait_cpt", FIELDP_IS_LABEL, true);

    array_push($ordre, "frais_retrait_cpt");

    $InfoProduit = $EpargneObj->getProdEpargne($cpteSource['id_prod']);
    if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0){
        $SESSION_VARS['frais_retrait'] = $frais_retrait_cpt;
    }else {
        $SESSION_VARS['frais_retrait'] = $InfoProduit['frais_retrait_cpt'];
    }

    // Destroy object
    unset($EpargneObj);

    // Montant à retirer
    $html->addField("mnt", $charMnt, TYPC_MNT);
    $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
    array_push($ordre, "htm2", "mnt");

    /*
      if ($global_multidevise) {
      $html->addField("mnt_cv", $charCv, TYPC_DVR);
      $html->linkFieldsChange("mnt_cv", "mnt", "vente", 1, true);
      $html->setFieldProperties("mnt_cv", FIELDP_IS_REQUIRED, true);
      }
     */

    // Informations chèque
    // dans les cas : chèque guichet/autorisation de retrait/travelers cheque
    if ($SESSION_VARS['type_retrait'] == 15 || $SESSION_VARS['type_retrait'] == 4) {
        $xtra3 = "<b>$charCheque</b>";
        $html->addHTMLExtraCode("htm3", $xtra3);
        $html->setHTMLExtraCodeProperties("htm3", HTMP_IN_TABLE, true);

        $html->addField("num_chq", _("Numéro"), TYPC_TXT);
        $html->setFieldProperties("num_chq", FIELDP_IS_REQUIRED, true);

        //Dans le cas du chèque, on rajoute les informations : date, correspondant et bénéficiaire.
        if ($SESSION_VARS['type_retrait'] == 15) {
            $html->addField("date_chq", _("Date du chèque"), TYPC_DTE);
            // FIXME Bernard : il serait peut-être intéressant de préalimenter la date du jour.
            $html->setFieldProperties("date_chq", FIELDP_HAS_CALEND, false);
            $html->setFieldProperties("date_chq", FIELDP_IS_REQUIRED, true);
            $html->setFieldProperties("date_chq", FIELDP_DEFAULT, date("d/m/Y")); // Afficher la date du jour
            $html->addLink("date_chq", "calendrier1", _("Calendrier"), "#");

            //Données du bénéficiaire
            $html->addHiddenType("id_ben");

            $html->addField("nom_ben", _("Nom du bénéficiaire"), TYPC_TXT);
            $html->setFieldProperties("nom_ben", FIELDP_IS_REQUIRED, false);
            array_push($labelField, "nom_ben");
            $html->addLink("nom_ben", "rechercher", _("Rechercher"), "#");
            /*$html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "if (document.ADForm.HTML_GEN_LSB_beneficiaire.value != 'EXT') { alert('La recherche permet de rechercher un bénéficiaire qui soit une personne non cliente.'); return false; } else OpenBrw('$SERVER_NAME/modules/externe/rech_benef.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=r&m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tib-3', '"._("Recherche")."');return false;"));*/
            $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "if (document.ADForm.HTML_GEN_LSB_beneficiaire.value != 'EXT') { alert('La recherche permet de rechercher un bénéficiaire qui soit une personne non cliente.'); return false; } else OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/rech_benef_distant.php?m_agc=".$_REQUEST['m_agc']."&field_name=nom_ben&field_id=id_ben&type=r&m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Tib-3', '" . _("Recherche") . "');return false;")); //liste deroulante pour beneficiaire lor du retrait par cheque : message erreur lor selection voir ticket 728 et 564
          if (in_array($SESSION_VARS['type_retrait'], array(8,15))) {
            array_push($ordre, "htm3", "num_chq", "date_chq", "beneficiaire", "nom_ben");
          }
          else {
            array_push($ordre, "htm3", "num_chq", "date_chq", "nom_ben");
          }
        } else {
            array_push($ordre, "htm3", "num_chq");
        }
    }

    $xtra4 = "<b>" . _("Communication / remarque") . "</b>";
    $html->addHTMLExtraCode("htm4", $xtra4);
    $html->setHTMLExtraCodeProperties("htm4", HTMP_IN_TABLE, true);

    $html->addField("communication", _("Communication"), TYPC_TXT);
    $html->addField("remarque", _("Remarque"), TYPC_ARE);

    array_push($ordre, "htm4", "communication", "remarque");

    //mise en ordre et en label des champs affichés
    $html->setOrder(NULL, $ordre);
    foreach ($labelField as $key => $value) {
        $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
    }

    //Code JavaScript
    $ChkJS = "
      if (recupMontant(document.ADForm.mnt.value) > " . $soldeDispo . " - recupMontant(document.ADForm.frais_retrait_cpt.value))
      {
      msg += '- " . _("Le montant du retrait augmenté des frais de retrait est supérieur au solde disponible") . "\\n';
      ADFormValid=false;
      }
      if (document.ADForm.etat_cpte.value=='3')
      {
      msg += '- " . _("Le compte est bloqué") . "\\n';
      ADFormValid=false;
      }";
    if ($SESSION_VARS['type_retrait'] == 15) {
        $ChkJS .= "
      if (document.ADForm.HTML_GEN_date_date_chq.value == '')
      {
      msg += '- " . _("La date du chèque n\'est pas renseignée") . "\\n';
      ADFormValid=false;
      }
      if (!isDate(document.ADForm.HTML_GEN_date_date_chq.value))
      {
      msg += '- " . _("Le format de la date du chèque est incorrect") . "\\n';
      ADFormValid=false;
      }
      if (isBefore('" . date("d/m/Y") . "', document.ADForm.HTML_GEN_date_date_chq.value))
      {
      msg += '- " . _("la date du chèque doit être antérieure ou égale à la date du jour") . "\\n';
      ADFormValid=false;
      }
      if (document.ADForm.id_ben.value == '')
      {
      msg += ' - " . _("Vous devez choisir un bénéficiaire") . "\\n';
      ADFormValid=false;
      };";

        $codejs = "
      if (! isDate(document.ADForm.HTML_GEN_date_date_chq.value)) document.ADForm.HTML_GEN_date_date_chq.value='';
      open_calendrier(getMonth(document.ADForm.HTML_GEN_date_date_chq.value), getYear(document.ADForm.HTML_GEN_date_date_chq.value), $calend_annee_passe, $calend_annee_futur, 'HTML_GEN_date_date_chq');return false;";
        $html->setLinkProperties("calendrier1", LINKP_JS_EVENT, array("onclick" => $codejs));
    }

    if (!isset($_GET['id_dem'])) {
        $html->setFieldProperties("frais_retrait_cpt", FIELDP_CAN_MODIFY, true);
    }

    $html->addJS(JSP_BEGIN_CHECK, "JS1", $ChkJS);

    // Boutons
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
    $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rcp-31');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Rcp-11');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Ope-13');

    $html->buildHTML();
    echo $html->getHTML();

    // Commit transaction
    $pdo_conn->commit();

    // Restore local monnaie courante
    $global_monnaie_courante = $global_monnaie_courante_tmp;
}
//-----------------------------------------------------------------
//------------ Ecran Rcp-31 Confirmation du montant ----------------
//-----------------------------------------------------------------
else if ($global_nom_ecran == "Rcp-31") {
    global $global_remote_id_agence, $global_id_guichet, $global_monnaie_courante, $global_remote_monnaie_courante, $global_remote_id_client;

    // Store local monnaie courante
    $global_monnaie_courante_tmp = $global_monnaie_courante;
    $global_monnaie_courante = $global_remote_monnaie_courante;

    // Begin remote transaction
    $pdo_conn->beginTransaction();

    // Init class
    $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);

    if ($mandat != 0 && $mandat != 'CONJ') {
        $SESSION_VARS['id_mandat'] = $mandat;
        $infos_pers_ext = $EpargneObj->getInfosMandat($SESSION_VARS['id_mandat']);
        $SESSION_VARS['id_pers_ext'] = $infos_pers_ext['id_pers_ext'];
    } else {
        $SESSION_VARS['id_mandat'] = NULL;
    }

    // Destroy object
    unset($EpargneObj);

    //Creation variable session 'tib' si beneficiaire est 'TITS' ou > 0 - Voir Ticket Trac 728, PP 242, 776
    if ($beneficiaire == 'TITS' || $beneficiaire > 0){
        $ClientData = array();
        $DATA = array();
        //get Client Data : recupere les infos pour le titulaire du compte/mandataire comme etant le beneficiaire
        $ClientDataObj = new Client($pdo_conn, $global_remote_id_agence);
        if ($beneficiaire == 'TITS'){
            $id_client = $global_remote_id_client;
        }
        else{
            $id_client = $beneficiaire;
        }
        $ClientData = $ClientDataObj->getClientDatas($id_client);
        if (isset($SESSION_VARS['tib'])){
            unset($SESSION_VARS['tib']);
        }
        $DATA['beneficiaire'] = 't';
        $DATA['tireur'] = 'f';
        $DATA['denomination'] = $ClientData['pp_nom']." ".$ClientData['pp_prenom'];
        $DATA['adresse'] = $ClientData['adresse'];
        $DATA['code_postal'] = $ClientData['code_postal'];
        $DATA['ville'] = $ClientData['ville'];
        $DATA['pays'] = $ClientData['pays'];
        $DATA['num_tel'] = $ClientData['num_tel'];
        $DATA['type_piece'] = $ClientData['pp_type_piece_id'];
        $DATA['num_piece'] = $ClientData['pp_nm_piece_id'];
        $DATA['lieu_delivrance'] = $ClientData['pp_lieu_delivrance_id'];
        $SESSION_VARS['tib'] = $DATA;
        unset($ClientDataObj);
    }

    if ($mandat == 'EXT') {
        $SESSION_VARS['id_pers_ext'] = $id_pers_ext;
        $SESSION_VARS['denomination'] = $denomination;
    }
    if (isset($SESSION_VARS['denomination_conj'])) {
        unset($SESSION_VARS['denomination_conj']);
    }
    if ($mandat == 'CONJ') {
        $SESSION_VARS['denomination_conj'] = $SESSION_VARS['mandat']['CONJ'];
    }
    // sauvegarde des données postées
    $erreurGuichet = false;
    if ($_POST['mnt_cv']['cv'] != '') // A-t-on réalisé une opération de change ?
        $change_effectue = true;
    else
        $change_effectue = false;

    //on sauvegarde la devise du montant à donner au guichet
    /* Multi-devise : commented
      if ($_POST['mnt_cv']['devise'] != '')
      $SESSION_VARS['devise'] = $_POST['mnt_cv']['devise'];
      else
     */
    $SESSION_VARS['devise'] = $global_remote_monnaie;

    // Init class
    $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);

    if (false) { // Multi-devise : $change_effectue
        //debug($SESSION_VARS);
        //debug("<===");
        $SESSION_VARS['change'] = $_POST['mnt_cv'];
        // on vérifie si le guichet dans la devise du retrait est correctement approvisionné.
        if ($SESSION_VARS['change']['devise'] != $global_monnaie) {
            $cpteGuichet = getCompteCptaGui($global_id_guichet);
            $cpteDevise = $cpteGuichet . "." . $SESSION_VARS['change']['devise'];
            $param['num_cpte_comptable'] = $cpteDevise;
            $infoCpteGuichet = getComptesComptables($param);
            $infoCpteGuichet = $infoCpteGuichet[$cpteDevise];
            debug($infoCpteGuichet);
            if (isset($infoCpteGuichet)) {
                if (($SESSION_VARS["type_retrait"] != 5) && ($SESSION_VARS['change']['cv'] + $infoCpteGuichet['solde']) > 0) {
                    $erreurGuichet = true;
                    $charTitle = _("Solde guichet insuffisant");
                    setMonnaieCourante($SESSION_VARS['change']['devise']);
                    $message = _("Solde insuffisant sur le guichet en") . " " . $SESSION_VARS['change']['devise'] . " (" . Divers::afficheMontant(-$infoCpteGuichet['solde'], true) . ")";
                }
            } else {
                $erreurGuichet = true;
                $charTitle = _("Guichet inexistant");
                $message = _("le guichet dans la devise finale n'existe pas") . " (" . $SESSION_VARS['change']['devise'] . ")";
            }
        }
    }

    $SESSION_VARS["remarque"] = $remarque;
    $SESSION_VARS["communication"] = $communication;
    //verifier le chèque
    if ($SESSION_VARS["type_retrait"] == 15) {
        $rep = $CompteObj->valideCheque($_REQUEST["num_chq"], $SESSION_VARS["NumCpte"]);
        debug($rep->errCode != NO_ERR);
        if ($rep->errCode != NO_ERR) {
            debug($rep);
            $titre = _("Retrait impossible") . " ";
            $ecran_retour = "Rcp-21";
            sendMsgErreur($titre, $rep, $ecran_retour);
        }
    }

    // Destroy object
    unset($CompteObj);

    if (!$erreurGuichet) {
        $SESSION_VARS["mnt"] = recupMontant($mnt);
        if ($SESSION_VARS["type_retrait"] == 15 || $SESSION_VARS["type_retrait"] == 4) {
            $SESSION_VARS["num_chq"] = $num_chq;
        }
        if ($SESSION_VARS["type_retrait"] == 15 || $SESSION_VARS["type_retrait"] == 4) {
            $SESSION_VARS["date_chq"] = $date_chq;
            debug($id_ben, _("Id du bénéficiaire est") . " ");
            if (isset($id_ben))
                $SESSION_VARS['id_ben'] = abs($id_ben);
        }

        if (!isset($SESSION_VARS['id_dem']) && isset($frais_retrait_cpt)) {
            $SESSION_VARS['frais_retrait'] = recupMontant($frais_retrait_cpt);
        }

        //Alimentation des zones d'affichage
        if (isset($SESSION_VARS['change'])) {
            switch ($SESSION_VARS['type_retrait']) {
                case 1:
                case 15:
                    $charTitle = _("Confirmation retrait en déplacé");
                    $charMnt = _("Montant à débiter du compte");
                    $charMntCV = _("Montant guichet");
                    break;
                case 4:
                    $charTitle = _("Confirmation retrait-chèque en déplacé");
                    $charMnt = _("Montant du chèque");
                    $charMntCV = _("Montant guichet");
                    break;
            }
        } else {
            switch ($SESSION_VARS['type_retrait']) {
                case 1:
                case 15:
                    $charTitle = _("Confirmation retrait en déplacé");
                    $charMnt = _("Montant à retirer");
                    break;
                case 4:
                    $charTitle = _("Confirmation retrait-chèque en déplacé");
                    $charMnt = _("Montant du chèque");
                    break;
            }
        }
        $charMntReel = _("Confirmation montant");

        // Init class
        $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);

        //récupérer le infos sur le produit associé au compte sélectionné
        $InfoCpte = $CompteObj->getAccountDatas($SESSION_VARS["NumCpte"]);

        // Destroy object
        unset($CompteObj);

        $info_login = get_login_full_info($global_nom_login);
        $info_agence = getAgenceDatas($global_id_agence);
        $msg = "";
        if (!isset($SESSION_VARS['id_dem']) && $info_agence['plafond_retrait_deplace_guichet'] == 't'){
            if($info_login['depasse_plafond_retrait'] == 'f' && $SESSION_VARS["mnt"] > $info_agence['montant_plafond_retrait_deplace']){
                //$msg = "<center>"._("Le montant demandé dépasse le montant plafond de retrait autorisé. Ce login n'est pas habilité à le faire.");
                //$msg .= " "._("Veuillez contacter votre administrateur.")."</center>";

                // Affichage de la confirmation
                $html_msg = new HTML_message("Demande autorisation de retrait en déplacé");

                $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le montant demandé dépasse le montant plafond de retrait en déplacé autorisé.</span><br /><br />Montant demandé = <span style='color: #FF0000;font-weight: bold;'>".afficheMontant($SESSION_VARS["mnt"], true)."</span><br/>Montant plafond de retrait en déplacé autorisé = ".afficheMontant($info_agence['montant_plafond_retrait_deplace'], true)."<br /><br />Veuillez choisir une option ci-dessous ?<br /><br/></center><input type=\"hidden\" name=\"montant_retrait_deplace\" value=\"".recupMontant($mnt)."\" /><input type=\"hidden\" name=\"frais_retrait_cpt\" value=\"".recupMontant($frais_retrait_cpt)."\" /><input type=\"hidden\" name=\"type_retrait\" value=\"1\" /><input type=\"hidden\" name=\"num_chq\" value=\"".trim($num_chq)."\" /><input type=\"hidden\" name=\"communication\" value=\"".trim($communication)."\" /><input type=\"hidden\" name=\"remarque\" value=\"".trim($remarque)."\" /><input type=\"hidden\" name=\"id_pers_ext\" value=\"".trim($id_pers_ext)."\" /><input type=\"hidden\" name=\"id_ben\" value=\"".trim($id_ben)."\" /><input type=\"hidden\" name=\"date_chq\" value=\"".trim($date_chq)."\" /><input type=\"hidden\" name=\"mandat\" value=\"".trim($mandat)."\" /><input type=\"hidden\" name=\"beneficiaire\" value=\"".trim($beneficiaire)."\" /><input type=\"hidden\" name=\"nom_ben\" value=\"".trim($nom_ben)."\" /><input type=\"hidden\" name=\"denomination\" value=\"".trim($denomination)."\" />");

                $html_msg->addCustomButton("btn_demande_autorisation_retrait_deplace", "Demande d’autorisation", 'Rcp-51');
                $html_msg->addCustomButton("btn_annuler", "Annuler", 'Ope-13');

                $html_msg->buildHTML();

                echo $html_msg->HTML_code;
                die();
            }
        }



        //Affichage du titre
        $html = new HTML_GEN2($charTitle);

        //confirmation du montant à retirer
        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);
        setMonnaieCourante($InfoCpte['devise']);
        $html->addField("mnt", $charMnt, TYPC_MNT);
        $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS["mnt"]);
        $html->setFieldProperties("mnt", FIELDP_IS_LABEL, true);

        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);
        setMonnaieCourante($InfoCpte['devise']);
        $confirmation_amount_field_name = 'mnt_reel';
        $html->addField($confirmation_amount_field_name, $charMntReel, TYPC_MNT);
        $html->setFieldProperties($confirmation_amount_field_name, FIELDP_IS_REQUIRED, true);

        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoCpte['devise']);
        setMonnaieCourante($InfoCpte['devise']);
        $html->addField("frais_retrait", _("Frais de retrait"), TYPC_MNT);
        $html->setFieldProperties("frais_retrait", FIELDP_DEFAULT, $SESSION_VARS["frais_retrait"]);
        $html->setFieldProperties("frais_retrait", FIELDP_IS_LABEL, true);

        // ajout du champs de commission en deplace
        $html->addField("od_comm_retrait", _("Commission en déplacé"), TYPC_MNT);
        $html->setFieldProperties("od_comm_retrait", FIELDP_DEFAULT, 0);
        $html->setFieldProperties("od_comm_retrait", FIELDP_IS_LABEL, true);
        $html->setFieldProperties("od_comm_retrait", FIELDP_JS_EVENT, array("onload" => $CheckOnLoadRetrait));

        $CheckOnLoadRetrait = "
            mnt_retrait = recupMontant(document.ADForm.mnt.value);
           document.ADForm.od_comm_retrait.value = Math.ceil(mnt_retrait * ".$InfoCpte['comm_retrait_od'].");

            if( (recupMontant(document.ADForm.od_comm_retrait.value) < ".$InfoCpte['comm_retrait_od_mnt_min'].") || (recupMontant(document.ADForm.od_comm_retrait.value) > ".$InfoCpte['comm_retrait_od_mnt_max'].") ){
                if (recupMontant(document.ADForm.od_comm_retrait.value) < ".$InfoCpte['comm_retrait_od_mnt_min']."){
                    document.ADForm.od_comm_retrait.value = ".$InfoCpte['comm_retrait_od_mnt_min'].";
                }
                else {
                     document.ADForm.od_comm_retrait.value = ".$InfoCpte['comm_retrait_od_mnt_max'].";
                }
            }
            ";
        $html->addJS(JSP_FORM, "JS01", $CheckOnLoadRetrait);

        // Au cas où on fait un retrait autre qu'un retrait en traveler's, il faudra saisir le billetage
        global $global_billet_req;

        if ($global_billet_req) {

            $html->setFieldProperties($confirmation_amount_field_name, FIELDP_IS_READONLY, true);

            $JS_billetage =
                    "OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/billetage_distant.php?m_agc=".$_REQUEST['m_agc']."&shortName=$confirmation_amount_field_name&direction=out&devise=$global_remote_monnaie_courante');";
            $html->addLink($confirmation_amount_field_name, "set_billetage", _("Billetage"), "#");
            $html->setLinkProperties("set_billetage", LINKP_JS_EVENT, array("onclick" => $JS_billetage));

            // Init class
            $ParametrageObj = new Parametrage($pdo_conn, $global_remote_id_agence);

            $result_billetage = $ParametrageObj->recupeBillet($global_remote_monnaie_courante);

            // Destroy object
            unset($ParametrageObj);

            for ($x = 0; $x < count($result_billetage); $x++) {
                $html->addHiddenType($confirmation_amount_field_name . "_billet_" . $x, 0);
                $html->addHiddenType($confirmation_amount_field_name . "_billet_rendu_" . $x, 0);
            }
        }

        //Crontôler si le montant à retirer ne dépasse pas le montant plafond de retrait autorisé s'il y a lieu
        global $global_nom_login, $colb_tableau; // $global_id_agence
        $info_login = get_login_full_info($global_nom_login);

        // Init class
        $AgenceObj = new Agence($pdo_conn);

        $info_agence = $AgenceObj->getAgenceDatas($global_remote_id_agence);

        // Destroy object
        unset($AgenceObj);

        if (false) { // Multi-devise : isset($SESSION_VARS['change'])
            Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $SESSION_VARS['devise']);
            setMonnaieCourante($SESSION_VARS['devise']);
            $html->addField("mntCV", $charMntCV, TYPC_MNT);
            $html->setFieldProperties("mntCV", FIELDP_DEFAULT, $SESSION_VARS['change']['cv']);
            $html->setFieldProperties("mntCV", FIELDP_IS_LABEL, true);
            setMonnaieCourante($global_monnaie_courante_tmp);
            if ($SESSION_VARS['change']['reste'] > 0) {
                debug($SESSION_VARS["change"]);
                Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $global_remote_monnaie);
                setMonnaieCourante($global_remote_monnaie);
                $html->addField("reste", _("Reste à toucher"), TYPC_MNT);
                $html->setFieldProperties("reste", FIELDP_DEFAULT, $SESSION_VARS["change"]['reste']);
                $html->setFieldProperties("reste", FIELDP_IS_LABEL, true);
                if ($SESSION_VARS["change"]["dest_reste"] == 1) { // Le reste doit etre remis en cash
                    $html->addField("conf_reste", _("Confirmation du reste remis au guichet"), TYPC_MNT);
                    $html->setFieldProperties("conf_reste", FIELDP_HAS_BILLET, true);
                }
                setMonnaieCourante($global_monnaie_courante_tmp);
            }
        }

        //code JavaScript
        if (false) { // Multi-devise : isset($SESSION_VARS['change'])
            $ChkJS = "
               if (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.mnt.value))
             {
               msg += '- " . _("Le montant saisi ne correspond pas au montant à retirer") . "\\n';
               ADFormValid=false;
             };
               ";
            if ($SESSION_VARS["change"]["reste"] > 0 && $SESSION_VARS["change"]["dest_reste"] == 1)
                $ChkJS .= "
                 if (recupMontant(document.ADForm.reste.value) != recupMontant(document.ADForm.conf_reste.value))
                 {
                 msg += '- " . _("Le montant du reste saisi ne correspond pas au montant du reste") . "\\n';
                 ADFormValid=false;
               };
                 ";
        } else {
            $ChkJS = "
               if (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.mnt.value))
             {
               msg += '- " . _("Le montant saisi ne correspond pas au montant à retirer") . "\\n';
               ADFormValid=false;
             };
               ";
        }
        $html->addJS(JSP_BEGIN_CHECK, "JS1", $ChkJS);
        $js_enable_fields = " if(ADFormValid == true) {\n\t\t document.ADForm.od_comm_retrait.removeAttribute('disabled'); }\n";
        $html->addJS(JSP_END_CHECK,"js_enable_fields",$js_enable_fields);

        //Boutons
        $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
        $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
        $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
        $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
        $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
        $SESSION_VARS['envoi'] = 0;
        $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rcp-41');
        $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Rcp-21');
        $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Ope-13');

        $html->buildHTML();
        echo $html->getHTML();
    } else {
        $html_err = new HTML_erreur($charTitle);
        $html_err->setMessage($message);
        $html_err->addButton("BUTTON_OK", "Rcp-2");
        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }

    // Commit transaction
    $pdo_conn->commit();

    // Restore local monnaie courante
    $global_monnaie_courante = $global_monnaie_courante_tmp;
}
//-----------------------------------------------------------------
//------------ Ecran Rcp-41 Confirmation du retrait ----------------
//-----------------------------------------------------------------
else if ($global_nom_ecran == "Rcp-41") {
    global $global_monnaie_courante, $global_remote_monnaie_courante, $global_id_guichet, $global_id_agence, $global_remote_id_agence, $global_remote_monnaie, $global_nom_login;
    global $global_remote_id_client, $global_remote_client;
    global $dbHandler, $global_remote_id_exo;

    // capturer des types de billets de la bd et nombre de billets saisie par l'utilisateur
    $valeurBilletArr = array();
    $dev = $SESSION_VARS['devise'];
    $listTypesBilletArr = buildBilletsVect($dev);
    $total_billetArr = array();
    
    // insert nombre billet into array
    for ($x = 0; $x < 20; $x ++) {
        if (isset($_POST['mnt_reel_billet_' . $x]) && trim($_POST['mnt_reel_billet_' . $x]) != '') {
            $valeurBilletArr[] = trim($_POST['mnt_reel_billet_' . $x]);
        } else {
            if (isset($listTypesBilletArr[$x]['libel']) && trim($listTypesBilletArr[$x]['libel']) != '') {
                $valeurBilletArr[] = 'XXXX';
            }
        }
    }
    
    // calcul total pour chaque billets
    for ($x = 0; $x < 20; $x ++) {
        if ($valeurBilletArr[$x] == 'XXXX') {
            $total_billetArr[] = 'XXXX';
        } else {
            if (isset($listTypesBilletArr[$x]['libel']) && trim($listTypesBilletArr[$x]['libel']) != '' && isset($valeurBilletArr[$x]['libel']) && trim($valeurBilletArr[$x]['libel']) != '') {
                $total_billetArr[] = (int) ($valeurBilletArr[$x]) * (int) ($listTypesBilletArr[$x]['libel']);
            }
        }
    }
    
    // Store local monnaie courante
    $global_monnaie_courante_tmp = $global_monnaie_courante;
    $global_monnaie_courante = $global_remote_monnaie_courante;

    // Begin remote transaction
    $pdo_conn->beginTransaction();

    //controle d'envoie du formulaire
    $SESSION_VARS['envoi']++;
    if ($SESSION_VARS['envoi'] != 1) {
        $html_err = new HTML_erreur(_("Confirmation"));
        $html_err->setMessage(_("Donnée dèjà envoyée"));
        $html_err->addButton("BUTTON_OK", 'Gen-8');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
    }
    //fin contrôle

    if (false) { // Multi-devise : isset($SESSION_VARS["change"])
        $CHANGE = $SESSION_VARS['change'];
    }

    // Init class
    $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);
    $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);

    // récupérer le infos sur le produit associé au compte sélectionné
    $InfoCpte = $CompteObj->getAccountDatas($SESSION_VARS["NumCpte"]);

    $InfoProduit = $EpargneObj->getProdEpargne($InfoCpte["id_prod"]);

    if (isset($SESSION_VARS['frais_retrait'])) {
        $InfoProduit['frais_retrait_cpt'] = $SESSION_VARS['frais_retrait'];
    }

    if (isset($_POST['od_comm_retrait'])){
        $commission_ope_deplace = recupMontant($_POST['od_comm_retrait']);
    }

    $data_cheque = array();
    $dataBef = "";
    if ($SESSION_VARS['type_retrait'] > 1) {
        $data_cheque["num_piece"] = $SESSION_VARS["num_chq"];
        $data_cheque["date_piece"] = $SESSION_VARS["date_chq"];
        $data_cheque["id_ext_benef"] = $SESSION_VARS["id_ben"];
        $data_cheque["type_piece"] = $SESSION_VARS["type_retrait"];

        if ($SESSION_VARS['type_retrait'] == 4) {
            $data_cheque['date_piece'] = date("d/m/Y");
        }

        if ($data_cheque["type_piece"] == 2) { // Il faut distinguer leschèques extérieurs et internes. Dans ce cas-ci, il s'agit d'un chèque guichet
            $data_cheque["type_piece"] = 15;
        }
        if ($SESSION_VARS['type_retrait'] == 15) {
            if (!isset($SESSION_VARS['tib']) || $SESSION_VARS['tib']==null){ //des mandataires
                $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);
                $MANDATS = $EpargneObj->getListeMandatairesActifs($SESSION_VARS['NumCpte']);
                if ($MANDATS != NULL) {
                    foreach ($MANDATS as $key => $value) {
                        if ($SESSION_VARS["id_ben"] == $key){
                            $getInfoMandat = $EpargneObj->getMandatInfo($key);
                            $SESSION_VARS['tib']=$getInfoMandat;
                        }
                    }
                }
            }
            $dataBef = $SESSION_VARS['tib']; // ou personne non cliente
        }
    }

    $data_cheque["communication"] = $SESSION_VARS["communication"];
    $data_cheque["remarque"] = $SESSION_VARS["remarque"];
    $data_cheque["sens"] = "out";
    $data_cheque["commission_op_deplace"] = $_POST['od_comm_retrait'];
    $SESSION_VARS["mnt"] = recupMontant($mnt_reel);

    $rollBackRemote = true;

    // Init class
    $AuditObj = new Audit();
    $ComptaObj = new Compta($pdo_conn, $global_remote_id_agence);

    $date_compta = $ComptaObj->getExercicesComptables($global_remote_id_exo);
    $date_batch = date_create(str_replace('/', '-',pg2phpDate(get_last_batch($global_id_agence))));
    date_add($date_batch, date_interval_create_from_date_string('1 day'));
    $date_batch_timestamp = strtotime(date_format($date_batch, 'd-m-Y'));
    $date_compta_debut = strtotime($date_compta[0]['date_deb_exo']);
    $date_compta_fin = strtotime($date_compta[0]['date_fin_exo']);

//    print_rn(array("date_compta" => $date_compta, "date_batch" => get_last_batch($global_id_agence), "date_batch_timestamp" => $date_batch_timestamp,
//        "date_compta_debut" => $date_compta_debut, "date_compta_fin" => $date_compta_fin));exit();

    if (!(($date_batch_timestamp >= $date_compta_debut) && ($date_batch_timestamp <= $date_compta_fin))) {
        $html_err = new HTML_erreur(_("Echec du rétrait en déplacé sur un compte."));
        $html_err->setMessage(_("La date donnée n'est pas valide"));
        $html_err->addButton("BUTTON_OK", 'Rcp-11');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
    }

    try {
        // Sauvegarder la transaction en cours
        $AuditObj->insertTransacData($global_nom_login, $global_id_agence, $global_remote_id_agence, $global_remote_id_client, $SESSION_VARS["NumCpte"], 'retrait', $SESSION_VARS['type_retrait'], Divers::getTypeTransactionChoixLibel('retrait', $SESSION_VARS['type_retrait']), $SESSION_VARS["mnt"], serialize($SESSION_VARS), $SESSION_VARS["devise"],0,null,$commission_ope_deplace);

        // Mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu
        $erreur_remote = Retrait::retraitCpteRemote($pdo_conn, $global_remote_id_agence, $global_id_guichet, $SESSION_VARS["NumCpte"], $InfoProduit, $InfoCpte, $SESSION_VARS["mnt"], $SESSION_VARS['type_retrait'], $SESSION_VARS['id_mandat'], $data_cheque, NULL, $dataBef); // Multi-devise : $CHANGE

        if ($erreur_remote->errCode == NO_ERR) {

            $erreur_local = Retrait::retraitCpteLocal($global_remote_id_agence, $global_id_guichet, NULL, $InfoProduit, $InfoCpte, $SESSION_VARS["mnt"], $SESSION_VARS['type_retrait'], NULL, NULL, NULL, NULL); // Multi-devise : $CHANGE
            //$erreur_local = new ErrorObj(NO_ERR);

            if ($erreur_local->errCode == NO_ERR) {

                if ($pdo_conn->commit()) { // Commit remote transaction
                    $rollBackRemote = false;

                    if (isset($erreur_remote->param['id_his']) && $erreur_remote->param['id_his'] > 0) {
                        // Sauvegarder l'ID historique en déplacé
                        $AuditObj->updateRemoteHisId($erreur_remote->param['id_his']);
                    }

                    if (isset($erreur_remote->param['id_ecriture']) && $erreur_remote->param['id_ecriture'] > 0) {
                        // Sauvegarder l'ID ecriture en déplacé
                        $AuditObj->updateRemoteEcritureId($erreur_remote->param['id_ecriture']);
                    }

                    if ($dbHandler->closeConnection(true)) { // Commit local transaction
                        if (isset($erreur_local->param['id_his']) && $erreur_local->param['id_his'] > 0) {
                            // Sauvegarder l'ID historique en local
                            $AuditObj->updateLocalHisId($erreur_local->param['id_his']);
                        }

                        if (isset($erreur_local->param['id_ecriture']) && $erreur_local->param['id_ecriture'] > 0) {
                            // Sauvegarder l'ID ecriture en local
                            $AuditObj->updateLocalEcritureId($erreur_local->param['id_ecriture']);
                        }
                        
                        // Valider la transaction en cours
                        $AuditObj->updateTransacFlag('t');
                    } else {

                        $dbHandler->closeConnection(false); // Roll back local transaction
                        // Begin remote transaction
                        $pdo_conn->beginTransaction();

                        // Revert remote transaction
                        $erreur_remote_revert = Retrait::retraitCpteRemoteRevert($pdo_conn, $global_remote_id_agence, $global_id_guichet, $SESSION_VARS["NumCpte"], $InfoProduit, $InfoCpte, (0 - $SESSION_VARS["mnt"]), $SESSION_VARS['type_retrait'], $data_cheque, " - Echec id_his=" . $erreur_remote->param['id_his']);

                        if ($erreur_remote_revert->errCode != NO_ERR) {

                            return $erreur_remote_revert;
                        }

                        // Commit remote transaction
                        $pdo_conn->commit();

                        throw new PDOException('Il y a eu un problème sur le serveur local !');
                    }
                } else {
                    // Save remote data in temp tables
                    throw new PDOException('Il y a eu un problème sur le serveur distant !');
                }
            }
        }
    } catch (PDOException $e) {

        // Sauvegarder le message d'erreur
        $AuditObj->saveErrorMessage($e->getMessage());

        // Sauvegarder le log SQL
        $AuditObj->saveSQLLog($pdo_conn->getError());

        if ($rollBackRemote) {
            $pdo_conn->rollBack(); // Roll back remote transaction
        }

        signalErreur(__FILE__, __LINE__, __FUNCTION__, $e->getMessage());
    }

    if ($erreur_remote->errCode == NO_ERR && $erreur_local->errCode == NO_ERR) {

        // Begin remote transaction
        $pdo_conn->beginTransaction();

        $infos = $EpargneObj->getCompteEpargneInfo($SESSION_VARS['NumCpte']);

        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $InfoProduit['devise']); //Pour etre sûr ke ce la devise du Produit
        setMonnaieCourante($InfoProduit['devise']);

        switch ($SESSION_VARS['type_retrait']) {
            case 1:
                Recu::printRecuRetrait($pdo_conn, $global_remote_id_agence, $global_remote_id_client, $global_remote_client, $InfoProduit, $infos, $SESSION_VARS['mnt'], $erreur_remote->param['id_his'], 'REC-REE', $SESSION_VARS['id_mandat'], $SESSION_VARS["remarque"], $SESSION_VARS["communication"], $SESSION_VARS['id_pers_ext'], NULL, $SESSION_VARS['denomination_conj'], $listTypesBilletArr, $valeurBilletArr, $global_langue_rapport, $total_billetArr, true,$commission_ope_deplace);
                break;
            case 15:
            case 4:
                Recu::printRecuRetraitCheque($pdo_conn, $global_remote_id_agence, $global_remote_id_client, $global_remote_client, $SESSION_VARS['mnt'], $InfoProduit, $infos, $erreur_remote->param['id_his'], $data_cheque["num_piece"], $data_cheque['date_piece'], $SESSION_VARS['id_mandat'], $dataBef['denomination'],$commission_ope_deplace);
                break;
        }

        // Imprime le reçu de change s'il y a lieu
        if (false) { // Multi-devise : isset($CHANGE)
            $cpteSource = $CompteObj->getAccountDatas($SESSION_VARS['NumCpte']);

            $SESSION_VARS["recu_change"]["source_achat"] = $cpteSource["num_complet_cpte"];
            $SESSION_VARS["recu_change"]["dest_vente"] = $dest_change;

            Recu::printRecuChange($pdo_conn, $global_remote_id_agence, $erreur_remote->param['id_his'], $SESSION_VARS["mnt"], $cpteSource["devise"], $SESSION_VARS["recu_change"]["source_achat"], $SESSION_VARS["change"]["cv"], $SESSION_VARS["change"]["devise"], $SESSION_VARS["change"]["comm_nette"], $SESSION_VARS["change"]["taux"], $SESSION_VARS["change"]["reste"], $SESSION_VARS["recu_change"]["dest_vente"], $SESSION_VARS["change"]["dest_reste"], $SESSION_VARS["envoi_reste"]);
        }

        // Mise à jour du bénéficiaire
        if (isset($SESSION_VARS['id_ben']) && ($SESSION_VARS['id_ben'] != NULL)) {

            // Init class
            $TireurBenefObj = new TireurBenef($pdo_conn, $global_remote_id_agence);

            $myError = $TireurBenefObj->setBeneficiaire($SESSION_VARS['id_ben']);

            // Destroy object
            unset($TireurBenefObj);
        }

        $majBenef = FALSE;
        if (is_object($myError) && $myError->errCode == NO_ERR) {
            $majBenef = TRUE;
        }

        /*****************Ticket Jira AT-44********************************************/
        // Mettre à jour le statut de la demande de retrait en deplace
        if (isset($SESSION_VARS['id_dem'])) {
            $erreur2 = updateRetraitDeplaceAttenteEtat($SESSION_VARS['id_dem'], 3, "Demande autorisation retrait : Payé", $erreur_local->param['id']);

            if ($erreur2->errCode == NO_ERR) {
                unset($SESSION_VARS['id_dem']);
            }
        }
        /***********************************************************/

        //Affichage de la confirmation
        $html_msg = new HTML_message(_("Confirmation du retrait en déplacé"));
        Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $infos['devise']);
        setMonnaieCourante($infos['devise']);
        $mntDebit = $SESSION_VARS['mnt'] + $InfoProduit['frais_retrait_cpt']+ $data_cheque["commission_od_retrait"] ;
        $message = "
        <table><tr><td>" . _("Montant débité du compte") . " : </td>
        <td>" . Divers::afficheMontant($mntDebit, true) . "</td>
        </tr>
        <tr><td>" . _("Frais de retrait") . " : </td>
        <td>" . Divers::afficheMontant($InfoProduit['frais_retrait_cpt'], true) . "</td>
        </tr>
        <tr><td>" . _("Commission sur opération en deplacée") . " : </td>
        <td>" . Divers::afficheMontant($data_cheque["commission_od_retrait"], true) . "</td>
        </tr>";
        $mntGuichet = $SESSION_VARS['mnt'];
        if (false) { // Multi-devise : isset($CHANGE)
            Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $CHANGE['devise']);
            setMonnaieCourante($CHANGE['devise']);
            $mntGuichet = $SESSION_VARS['change']['cv'];
        }
        $message.="
        <tr><td>" . _("Remis au client") . " : </td>
        <td>" . Divers::afficheMontant($mntGuichet, true) . "</td>
        </tr>";
        if (false) { // Multi-devise : $CHANGE['reste'] > 0
            Divers::setMonnaieCourante($pdo_conn, $global_remote_id_agence, $global_remote_monnaie);
            setMonnaieCourante($global_remote_monnaie);
            $message.="
            <tr><td>" . _("Liquidié en devise de référence") . "</td>
            <td>" . Divers::afficheMontant($CHANGE['reste'], true) . "</td>";
        }
        $message.="
        </table>
        <br />
        " . _("Le reçu a été imprimé") . "
        <br />";
        if (isset($SESSION_VARS['id_ben'])) {
            debug($majBenef, "majbenef est ");
            if ($majBenef == TRUE)
                $message.="<br />" . _("Bénéficiaire mis à jour") . " <br />";
            else
                $message.="<br />" . _("Bénéficiaire non mis à jour") . " <br />";
        }

        $message .= "<br /><br />" . _("N° de transaction") . " : <B><code>" . sprintf("%09d", $erreur_remote->param['id_his']) . "</code></B>";

        $html_msg->setMessage($message);

        $html_msg->addButton("BUTTON_OK", 'Ope-13');
        $html_msg->buildHTML();
        echo $html_msg->HTML_code;

        // Commit transaction
        $pdo_conn->commit();
    } else {

        if ($erreur_remote->errCode != NO_ERR) {
            $erreur = $erreur_remote;
        } elseif ($erreur_local->errCode != NO_ERR) {
            $erreur = $erreur_local;
        }

        debug($erreur->param);
        $html_err = new HTML_erreur(_("Echec du retrait en déplacé sur un compte.") . " ");
        $html_err->setMessage(_("Erreur") . " : " . $error[$erreur->errCode] . "<br />" . _("Paramètre") . " : " . $erreur->param);
        $html_err->addButton("BUTTON_OK", 'Rcp-11');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }

    // Destroy object
    unset($CompteObj);
    unset($EpargneObj);
    unset($AuditObj);

    // Clear data session variables
    unset($SESSION_VARS['NumCpte'], $SESSION_VARS['type_retrait'], $SESSION_VARS['mandat'], $SESSION_VARS['frais_retrait'], $SESSION_VARS['type_recherche'], $SESSION_VARS['field_name'], $SESSION_VARS['nom_ben'], $SESSION_VARS['field_id'], $SESSION_VARS['id_ben'], $SESSION_VARS['tib'], $SESSION_VARS['beneficiaire'], $SESSION_VARS['tireur'], $SESSION_VARS['denomination'], $SESSION_VARS['adresse'], $SESSION_VARS['code_postal'], $SESSION_VARS['ville'], $SESSION_VARS['num_tel'], $SESSION_VARS['num_piece'], $SESSION_VARS['lieu_delivrance'], $SESSION_VARS['id_mandat'], $SESSION_VARS['remarque'], $SESSION_VARS['communication'], $SESSION_VARS['mnt'], $SESSION_VARS['num_chq'], $SESSION_VARS['date_chq'], $SESSION_VARS['envoi'], $SESSION_VARS['gpe'], $SESSION_VARS['gpe']['denom'], $SESSION_VARS['gpe']['pers_ext'], $SESSION_VARS['gpe']['denomination'], $SESSION_VARS['gpe']['id_pers_ext'], $SESSION_VARS['id_pers_ext']);

    // Restore local monnaie courante
    $global_monnaie_courante = $global_monnaie_courante_tmp;
}

else if ($global_nom_ecran == "Rcp-51"){

    global $global_nom_login, $global_id_client,$global_remote_id_client,$global_remote_id_agence;

    if (!empty($SESSION_VARS['tib']) && sizeof($SESSION_VARS['tib'])>0){
        $tib=json_encode($SESSION_VARS['tib']);
    }

    $id_cpte = $SESSION_VARS['NumCpte'];
    $type_retrait = $SESSION_VARS['type_retrait'];
    if ($type_retrait == 15){
        $nom_ben = $SESSION_VARS['tib']['denomination'];
    }

    /*require_once ('lib/misc/debug.php');
    print_rn($_POST);die;*/


    $erreur = insertRetraitDeplaceAttente($global_remote_id_agence,$global_remote_id_client, $id_cpte, $type_retrait,  $montant_retrait_deplace, $global_nom_login, $communication, $remarque, $id_pers_ext, $mandat, $num_chq, $date_chq, $id_ben, $beneficiaire, $nom_ben, $denomination, $frais_retrait_cpt,$tib);

    if ($erreur->errCode == NO_ERR) {

        /***************Recu de la demande d'autorisation************************/
        $pdo_conn->beginTransaction();
        $ClientObj = new Client($pdo_conn, $global_remote_id_agence);
        $AgenceObj = new Agence($pdo_conn, $global_remote_id_agence);
        $CompteObj = new Compte($pdo_conn, $global_remote_id_agence);
        $InfoClient = $ClientObj->getClientDatas($global_remote_id_client);

        $InfoCompte = $CompteObj->getAccountDatas($id_cpte);
        $num_cpte_complet = $InfoCompte['num_complet_cpte'];
        $InfoAgence = $AgenceObj->getAgenceDatas($global_remote_id_agence);
        $nom_agence = $InfoAgence['libel_ag'];
        //$data_client = getClientDatas($global_id_client);
        switch ($InfoClient['statut_juridique']){
            case 1 :
                $nom = $InfoClient["pp_nom"] . " " . $InfoClient["pp_prenom"];
                break;
            case 2 :
                $nom = $InfoClient["pm_raison_sociale"];
                break;
            case 3 :
                $nom = $InfoClient["gi_nom"];
                break;
            case 4:
                $nom = $InfoClient["gi_nom"];
                break;
        }
        // Commit transaction
        $pdo_conn->commit();
        $now = date("Y-m-d");

        $id_dmde_retrait = getDataRetraitDeplaceAttente($global_remote_id_client,$id_cpte,1,$global_remote_id_agence);
        $id_dem = $id_dmde_retrait['max_id'];

        print_recu_demande_autorisation_retrait_deplace($global_remote_id_client,$nom,$num_cpte_complet,$nom_agence,$montant_retrait_deplace, $now , $global_nom_login, $id_dem );

        /******************************************/

        // Affichage de la confirmation
        $html_msg = new HTML_message("Confirmation demande autorisation retrait");

        $html_msg->setMessage("La demande d'autorisation de retrait a été envoyée.");

        $html_msg->addButton("BUTTON_OK", 'Ope-13');

        $html_msg->buildHTML();
        echo $html_msg->HTML_code;

    } else {
        $html_err = new HTML_erreur("Echec lors de la demande autorisation retrait en déplacé.");

        $err_msg = $error[$erreur->errCode];

        $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Ope-13');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }



}


else {
    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "L'écran '$global_nom_ecran' n'a pas été trouvé"
}

// Fermer la connexion BDD
unset($pdo_conn);