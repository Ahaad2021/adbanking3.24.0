<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Dépôt sur un compte d'épargne
 * @author Hassan Diallo
 * @author Olivier Luickx
 * @since 04/02/2002
 * @package Epargne
 **/

require_once 'lib/dbProcedures/epargne.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'modules/epargne/recu.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/dbProcedures/tireur_benef.php';
require_once 'lib/misc/divers.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/VariablesSession.php';
require_once 'modules/rapports/xml_devise.php';
require_once 'lib/dbProcedures/billetage.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/agency_banking.php';

/*{{{ Dva-1 : Choix du compte */
if ($global_nom_ecran == "Dva-1") {

    $html = new HTML_GEN2(_("Dépôt via agent : choix du client"));
    $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $html->addHTMLExtraCode("html_js",$html_js);
    $xtra1 = "<b>"._("Choix du compte")."</b>";
    $html->addHTMLExtraCode ("htm1", $xtra1);
    $html->addField("cpt_dest",_("Compte client"), TYPC_TXT);
    $html->setFieldProperties("cpt_dest", FIELDP_IS_REQUIRED, true);
    $html->addLink("cpt_dest", "rechercher", _("Rechercher"), "#");
    $str = "if (document.ADForm.cpt_dest.disabled == false) OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=cpt_dest&id_cpt_dest=id_cpt_dest', '"._("Recherche")."');return false;";
    $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => $str));
    $html->addHiddenType("id_cpt_dest", "");
    $html->setHTMLExtraCodeProperties ("htm1",HTMP_IN_TABLE, true);

    $agent = getDatasLogin($global_nom_login);
    $agent_dataset = getDataUtilisateur($agent['id_utilisateur']);
    if($agent_dataset['is_agent_ag'] == 'f'){
        $erreur = new HTML_erreur(_("Agent inexistant"));
        $erreur->setMessage(_("L'utilisateur " .$agent_dataset['nom'] . " n'est pas un agent d'agency banking."));
        $erreur->addButton(BUTTON_OK, "Gen-16");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
    }else{
        if(empty($agent['cpte_flotte_agent'])){
            $erreur = new HTML_erreur(_("Compte de flotte"));
            $erreur->setMessage(_("Le compte de flotte de l'agent " .$agent_dataset['nom'] . " n'est pas parametré."));
            $erreur->addButton(BUTTON_OK, "Gen-16");
            $erreur->buildHTML();
            echo $erreur->HTML_code;
            $ok = false;
        }else{
            $choix2 = array();
            $choix2[1]=_('Dépôt en espèce');

            foreach($choix2 as $key=>$value) {
                //Type de dépôt autorisé : 1:espèce, 2:chèque, 3:ordre de paiement, 5:Travelers cheque
                if ($key!=1 and $key!=2 and $key!=3 and $key!=5) {
                    unset($choix2[$key]);
                }
            }

            $html->addField("type_depot", _("Type de dépôt"), TYPC_TXT);
            $html->setFieldProperties("type_depot", FIELDP_DEFAULT, 'Dépôt en espèce');
            $html->setFieldProperties("type_depot", FIELDP_IS_LABEL, true);

            $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
            $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
            $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
            $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
            $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
            $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dva-2');
            $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-16');
            $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
            $html->addHiddenType("type_depot_id", 1);

            $js = "$('[name=cpt_dest]').attr('readonly', 'readonly');";
            $html->addJS(JSP_FORM, "JS1", $js);
            $html->buildHTML();
            echo $html->getHTML();
        }
    }
}
else if ($global_nom_ecran == "Dva-2") {
    global $global_multidevise, $global_photo_client, $global_signature_client;
    global $adsys;
    $id_titulaire = getDataCpteEpargne($_POST['id_cpt_dest']);

    $IMGS = getImagesClient($id_titulaire["id_titulaire"]);
    $global_photo_client = $IMGS["photo"];
    $global_signature_client = $IMGS["signature"];

    if (isset($NumCpte)) $SESSION_VARS["NumCpte"] = $NumCpte;
    if (isset($type_depot)) $SESSION_VARS["type_depot"] = $type_depot;
    if (isset($SESSION_VARS['id_pers_ext'])) unset ($SESSION_VARS['id_pers_ext']);

    $html = new HTML_GEN2(_("Dépôt compte via Agent : montant"));
    $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $html->addHTMLExtraCode("html_js",$html_js);
    $infoCpte=getAccountDatas($_POST['id_cpt_dest']);
    $SESSION_VARS['id_cpt_dest'] = $_POST['id_cpt_dest'];
    setMonnaieCourante($infoCpte["devise"]);
    $MANDATS = getListeMandatairesActifs($_POST['id_cpt_dest'], true);
    $ad_log =  getDatasLogin();
    $plafond_depot = empty($ad_log['plafond_depot'])?0:$ad_log['plafond_depot'];


    if ($MANDATS != NULL) {
        foreach($MANDATS as $key=>$value) {
            $MANDATS_LSB[$key] = $value['libelle'];
            if ($key == 'CONJ') {
                $JS_open .=
                    "if (document.ADForm.HTML_GEN_LSB_mandat.value == '$key')
        {
          OpenBrw('$SERVER_NAME/modules/externe/info_mandat.php?m_agc=".$_REQUEST['m_agc']."&id_cpte=".$_POST['id_cpt_dest']."');
          return false;
        }";
            } else {
                $JS_open .=
                    "if (document.ADForm.HTML_GEN_LSB_mandat.value == $key)
        {
          OpenBrw('$SERVER_NAME/modules/externe/info_mandat.php?m_agc=".$_REQUEST['m_agc']."&id_mandat=$key');
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

    $html->addField("mandat", _("Donneur d'ordre"), TYPC_LSB);
    $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("0" => _("Titulaire (".getClientName($id_titulaire['id_titulaire']).")")));
    if ($MANDATS_LSB != NULL) {
        $MANDATS_LSB = array_flip($MANDATS_LSB); // array(valeur = >cle) au lieu de array(cle => valeur)
        unset($MANDATS_LSB[getClientName($id_titulaire['id_titulaire'])]); //on supprime le nom du titulaire dans la liste déroulante
        $MANDATS_LSB = array_flip($MANDATS_LSB); // on remet le array(cle => valeur)
        $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, $MANDATS_LSB);
    }
    $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_AUCUN, false);
    $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_TOUS, false);
    $html->setFieldProperties("mandat", FIELDP_JS_EVENT, array("onchange" => $JS_change));
    $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("EXT" => _("Personne non cliente")));
    $html->setFieldProperties("mandat", FIELDP_DEFAULT, $SESSION_VARS['id_mandat']);
    $html->addLink("mandat", "afficher", _("Afficher"), "#");
    $html->setLinkProperties("afficher", LINKP_JS_EVENT, array("onclick" => $JS_open));
    $SESSION_VARS['mandat'] = $MANDATS_LSB;
    $JS_rech =
        "if (document.ADForm.HTML_GEN_LSB_mandat.value == 'EXT')
  {
    OpenBrw('$SERVER_NAME/modules/externe/gest_pers_ext.php?m_agc=".$_REQUEST['m_agc']."&denom=denomination&pers_ext=id_pers_ext');
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
    msg += ' - "._("Vous devez choisir une personne non cliente")."\\n';
    ADFormValid=false;
  }";
    $html->addJS(JSP_BEGIN_CHECK, "JS1", $JS_check);
    $html->addHTMLExtraCode("mandat_sep", "<br/>");

    $choix_depot_comm = getParamCommissionInsti();
    if ($choix_depot_comm['choix_depot_comm'] == null || $choix_depot_comm['cpte_compta_comm_depot'] == null){
        $erreur = new HTML_erreur(_("Paramétrage compte commissions"));
        $erreur->setMessage(_("Le compte de commission pour l'institution n'est pas parametré."));
        $erreur->addButton(BUTTON_OK, "Gen-16");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        $ok = false;
        die();
    }

    $ag_commission = getCommissionDepotRetrait(2);

    $xtra2 = "<b>"._("Informations sur compte")."</b>";
    $html->addHTMLExtraCode ("htm2", $xtra2);
    $html->setHTMLExtraCodeProperties ("htm2", HTMP_IN_TABLE, true);

    $html->addField("NumCpte", _("Numéro de compte"), TYPC_TXT);
    $html->setFieldProperties("NumCpte", FIELDP_DEFAULT, $infoCpte["num_complet_cpte"]." ".$infoCpte["intitule_compte"]);
    $html->setFieldProperties("NumCpte", FIELDP_IS_READONLY, true);
    $html->addField("trans_lib", _("Libellé du produit d'épargne"), TYPC_TXT);
    $html->setFieldProperties("trans_lib", FIELDP_DEFAULT, $adsys["type_depot_agent"][1]);
    $html->setFieldProperties("trans_lib", FIELDP_IS_LABEL, true);
//
//    //Ajout des tables
    $access_solde = get_profil_acces_solde($global_id_profil, $infoCpte["id_prod"]);
    $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);
    if(!manage_display_solde_access($access_solde, $access_solde_vip)){
        $html->addField("solde_cpte", _("Solde disponible"), TYPC_MNT);
        $html->setFieldProperties("solde_cpte", FIELDP_DEFAULT, $infoCpte["solde"]);
        $html->setFieldProperties("solde_cpte", FIELDP_IS_LABEL,true);
    }
    $html->addField("mnt_max", _("Montant maximum (0 si aucun)"), TYPC_MNT);
    $html->setFieldProperties("mnt_max", FIELDP_DEFAULT, $infoCpte["mnt_max"]);
    $html->setFieldProperties("mnt_max", FIELDP_IS_LABEL, true);
    $html->addField("etat_cpte", _("Etat du compte"), TYPC_TXT);
    $html->setFieldProperties("etat_cpte", FIELDP_DEFAULT, $adsys["adsys_etat_cpt_epargne"][$infoCpte["etat_cpte"]]);
    $html->setFieldProperties("etat_cpte", FIELDP_IS_LABEL, true);

    if(manage_display_solde_access($access_solde, $access_solde_vip))
        $ordre = array("mandat", "denomination", "mandat_sep", "htm2" ,"NumCpte", "trans_lib",  "mnt_max", "etat_cpte");
    else
        $ordre = array("mandat", "denomination", "mandat_sep", "htm2" ,"NumCpte", "trans_lib", "solde_cpte","mnt_max", "etat_cpte");

    $xtra2 = "<b>"._("Commission / Montant du dépôt")."</b>";
    $html->addHTMLExtraCode ("htm3", $xtra2);
    $html->setHTMLExtraCodeProperties ("htm3", HTMP_IN_TABLE, true);

    $html->addField("mnt",_("Montant déposé"),TYPC_MNT);
    $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS['mnt']);
    $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
    $html->setFieldProperties("mnt", FIELDP_JS_EVENT, array("onChange"=>"setCommision();"));

    $mntjs = "
    var dataset = ".json_encode($ag_commission).";    
    function setCommision(){
        setTimeout(function(){
            var plafond_depot = parseInt(".$plafond_depot."); 
            var element_val = parseInt($('[name=mnt]').val().replace(/\s/g, ''));
   
            if(dataset == null){
                $('[name=mnt]').val('');
                $('[name=commission_agent]').val(0);
                $('[name=commission_institution]').val(0);
                alert('Les commissions n\'ont pas été paramétré');
            }else if(element_val > plafond_depot){
                alert('Le montant saisi est supérieur au plafond de dépot');
                $('[name=commission_agent]').val(0);
                $('[name=commission_institution]').val(0);
                $('[name=mnt]').val('');
            }else{
                for(var index = 0; index < dataset.length; index++){
                    var element_val = parseInt($('[name=mnt]').val().replace(/\s/g, ''));
                    var commission_choice = ".$choix_depot_comm["choix_depot_comm"].";
                    var calculator_agent = (dataset[index].comm_agent_mnt == null)?((dataset[index].comm_agent_prc/100)*element_val):dataset[index].comm_agent_mnt;
                    var calculator_inst = (dataset[index].comm_inst_mnt == null)?((dataset[index].comm_inst_prc/100)*element_val):dataset[index].comm_inst_mnt;
                   
                    console.log('dataset: ', dataset);
                    console.log('element: ', element_val);
                    if((element_val >= dataset[index].mnt_min) && (element_val <= dataset[index].mnt_max)){
                        if(commission_choice == '2'){
                            $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
                            $('[name=commission_institution]').val(Math.floor(parseInt(calculator_inst)));
                        }else{
                            $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
                            $('[name=commission_institution]').val(0);
                        }
                        break;
                    }
                    if((dataset.length - 1) == index){
                        alert('Le montant déposé est supérieure au palier maximal');
                        $('[name=mnt]').val('');
                        $('[name=commission_agent]').val('');
                        $('[name=commission_institution]').val('');
//                        if(commission_choice == '2'){
//                            $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
//                            $('[name=commission_institution]').val(Math.floor(parseInt(calculator_inst)));
//                        }else{
//                            $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
//                            $('[name=commission_institution]').val(0);
//                        }
                    }
                }
            }
        },1000);
    }
        
        $('[name=mnt]').keyup(function(e){
            if(e.keyCode == 8 || ($('[name=mnt]').val() == '')){
                $('[name=commission_agent]').val('');
                $('[name=commission_institution]').val('');
            }
        });
        
        $('[name=commission_agent]').attr('readonly', true);
        $('[name=commission_institution]').attr('readonly', true);
        
    ";

    $html->addJS(JSP_FORM, "JS8", $mntjs);

    if ($global_multidevise) {
        $html->addField("mnt_cv", _("Montant guichet/chèque"), TYPC_DVR);
        $html->setFieldProperties("mnt_cv", FIELDP_IS_REQUIRED, true);
        $html->linkFieldsChange("mnt_cv", "mnt", "achat", 1, true);
        $html->add_js_enable_disable("mnt_cv");
        if (is_array($SESSION_VARS['mnt_cv'])) {
            if ($SESSION_VARS['mnt_cv']['devise'] == $infoCpte['devise']) {
                $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $SESSION_VARS['mnt']);
                $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $SESSION_VARS['mnt_cv']['devise']);
                $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, true);
            } else {
                $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $SESSION_VARS['mnt_cv']['cv']);
                $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $SESSION_VARS['mnt_cv']['devise']);
                $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, false);
                $html->addJS(JSP_FORM, "js_mnt_cv_taux", "document.ADForm.HTML_GEN_dvr_mnt_cv_taux.value = '".$SESSION_VARS['mnt_cv']['taux']."';\n");
                $html->addJS(JSP_FORM, "js_mnt_cv_comm_nette", "document.ADForm.HTML_GEN_dvr_mnt_cv_comm_nette.value = '".$SESSION_VARS['mnt_cv']['comm_nette']."';\n");
                $html->addJS(JSP_FORM, "js_mnt_cv_dest_reste", "document.ADForm.HTML_GEN_dvr_mnt_cv_dest_reste.value = '".$SESSION_VARS['mnt_cv']['dest_reste']."';\n");
            }
        }
    }


    $html->addField("commission_agent", _("Commission Agent"), TYPC_MNT);
    $html->setFieldProperties("commission_agent", FIELDP_IS_LABEL,FALSE);

    $html->addField("commission_institution", _("Commission pour l'institution"), TYPC_MNT);
    $html->setFieldProperties("commission_institution", FIELDP_IS_LABEL,FALSE);

    array_push($ordre,"htm3", "mnt", "commission_agent","commission_institution");


    $xtra4 = "<b>"._("Communication / remarque")."</b>";
    $html->addHTMLExtraCode ("htm4", $xtra4);
    $html->setHTMLExtraCodeProperties ("htm4", HTMP_IN_TABLE, true);

    //Communication
    $html->addField("communication", _("Communication"), TYPC_TXT);
    $html->setFieldProperties("communication",FIELDP_DEFAULT,$SESSION_VARS['communication']);

    //Remarque
    $html->addField("remarque", _("Remarque"), TYPC_ARE);
    $html->setFieldProperties("remarque",FIELDP_DEFAULT,$SESSION_VARS['remarque']);

    array_push($ordre,"htm4", "communication", "remarque");

    //Ordonner les champs pour l'affichage
    $html->setOrder(NULL, $ordre);

    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
    $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dva-3');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Dva-1');

    $html->buildHTML();
    echo $html->getHTML();

    $SESSION_VARS["set_monnaie_courante"]=$InfoCpte['devise'];
}
else if ($global_nom_ecran == "Dva-3") {
    global $global_id_client, $dbHandler, $global_id_agence, $global_mouvements;

    // /REM/ $mnt_cv est un Array qui n'est posté qu'en mode multidevise !!
    if ($global_multidevise) {
        $SESSION_VARS["mnt_cv"] = $mnt_cv;
    } else {
        // Fabrication de l'array $mnt_cv comme si on était en multidevise
        $mnt_cv = array("devise" => $global_monnaie);
        $SESSION_VARS["mnt_cv"] = $mnt_cv;
    }

    if (isset($mnt))			$SESSION_VARS["mnt"]		= recupMontant($mnt);
    if (isset($frais_depot_cpt))	$SESSION_VARS["frais_depot_cpt"]= recupMontant($frais_depot_cpt);
    if (isset($num_chq))		$SESSION_VARS["num_chq"]	= $num_chq;
    if (isset($date_chq))		$SESSION_VARS["date_chq"]	= $date_chq;
    if (isset($correspondant))		$SESSION_VARS["id_correspondant"]= $correspondant;
    if (isset($id_ben))		$SESSION_VARS["id_ben"]		= $id_ben;
    if (isset($remarque))		$SESSION_VARS["remarque"]	= $remarque;
    if (isset($communication))		$SESSION_VARS["communication"]	= $communication;

    if ( isset($SESSION_VARS['id_mandat'])) unset ($SESSION_VARS['id_mandat']);
    if ($mandat == 'EXT') {
        $SESSION_VARS['id_pers_ext'] = $id_pers_ext;
        $SESSION_VARS['denomination'] = $denomination;

    } elseif ($mandat != 0 && $mandat != 'CONJ') {
        $MANDAT = getInfosMandat($mandat);
        if($SESSION_VARS['mandat'][$mandat] == getClientName($global_id_client)){
            $SESSION_VARS['id_pers_ext'] = NULL ;
        }else{
            $SESSION_VARS['id_mandat']=$MANDAT['id_mandat'];
            $SESSION_VARS['id_pers_ext'] = $MANDAT['id_pers_ext'];
        }
    } elseif($mandat == ''){
        $SESSION_VARS['id_pers_ext'] = NULL ;
    } elseif ($mandat == 'CONJ') {
        $infos_pers_ext = getInfosPersExt($SESSION_VARS['mandat']['CONJ']);
        $SESSION_VARS['id_pers_ext'] = $infos_pers_ext['id_pers_ext'];
    }

    //initializing empty session vars
    $SESSION_VARS["mnt_depot"] = $_POST['mnt'];

    // Recherche des données des différents opérateurs (banque, tireur, correspondant, ...)
    if (isset($SESSION_VARS['id_correspondant'])) {
        $infosCorrespondant = getInfosCorrespondant($SESSION_VARS['id_correspondant']);
    }
    if (isset($SESSION_VARS['id_ben']) && $SESSION_VARS['id_ben']!='') {
        $majTireur = setTireur($SESSION_VARS['id_ben']);
        $infoTireur=getTireurBenefDatas($SESSION_VARS["id_ben"]);
        $infosbanque = getInfosBanque($infoTireur['id_banque']);
        $SESSION_VARS["banque"] = $infosbanque['nom_banque'];
    }
    $InfoCpte = getAccountDatas  ($SESSION_VARS["id_cpt_dest"]);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);

    if (!isset($frais_depot_cpt))
        $SESSION_VARS["frais_depot_cpt"] = $InfoProduit["frais_depot_cpt"];

    // Dans le cas d'un chèque, on vérifie que la devise est identique à celle des comptes du Correspondant et qu'elle est bien paramétrée dans la table correspondant.
    $message_erreur=NULL;
    if ($infosCorrespondant['devise']==NULL && $SESSION_VARS['type_depot']==2) {
        $message_erreur=_("Les comptes du correspondant ont des devises différentes.")."<br />";
        $message_erreur.=sprintf(_("Veuillez changer les paramètres de %s avant de continuer"), $infosCorrespondant['nom_banque']."-".$infosCorrespondant['numero_cpte'])."<br /><br />";
    }
    if ($infosCorrespondant['devise']!=$SESSION_VARS['mnt_cv']['devise'] && $SESSION_VARS['type_depot']==2 && $message_erreur==NULL) {
        $message_erreur.=sprintf(_("La devise du chèque (%s) est différente de la devise des comptes du Correspondant (%s)"),$SESSION_VARS['mnt_cv']['devise'],$infosCorrespondant['devise']);
    }

    $html = new HTML_GEN2(_("Confirmation du montant a déposer"));

    if (($global_multidevise) && ( $InfoCpte["devise"] != $mnt_cv["devise"] )) {	// Dépôt au guichet, avec change.
        $champ_mnt = "mnt_cv";

        $html->addField("mnt",_("Montant déposé sur le compte"),TYPC_MNT);

        $html->addField("mnt_cv",_("Montant déposé au guichet"),TYPC_MNT);
        $html->setFieldProperties("mnt_cv", FIELDP_DEFAULT, $mnt_cv["cv"]);
        $html->setFieldProperties("mnt_cv", FIELDP_DEVISE, $mnt_cv["devise"]);
        $html->setFieldProperties("mnt_cv", FIELDP_IS_LABEL, true);

        $html->addField("mnt_reel",_("Confirmation montant"),TYPC_MNT);
        $html->setFieldProperties("mnt_reel", FIELDP_DEVISE, $mnt_cv["devise"]);

        $html->addField("taux",_("Taux"),TYPC_TXT);
        $html->setFieldProperties("taux", FIELDP_DEFAULT, $mnt_cv["taux"]);
        $html->setFieldProperties("taux", FIELDP_IS_LABEL, true);

        $html->addField("un_sur_taux",_("1 / Taux"),TYPC_TXT);
        $html->setFieldProperties("un_sur_taux", FIELDP_DEFAULT, 1/$mnt_cv["taux"]);
        $html->setFieldProperties("un_sur_taux", FIELDP_IS_LABEL, true);

        $html->addField("comm_nette",_("Commission nette"),TYPC_MNT);
        $html->setFieldProperties("comm_nette", FIELDP_DEFAULT, $mnt_cv["comm_nette"]);
        $html->setFieldProperties("comm_nette", FIELDP_IS_LABEL, true);
        if($SESSION_VARS['mnt_cv']['reste'] > 0){
            setMonnaieCourante($global_monnaie);
            $html->addField("reste",_("Reste à toucher"),TYPC_MNT);
            $html->setFieldProperties("reste", FIELDP_DEFAULT, $SESSION_VARS["mnt_cv"]['reste']);
            $html->setFieldProperties("reste", FIELDP_IS_LABEL, true);
            if ($SESSION_VARS["mnt_cv"]["dest_reste"] == 1) { // Le reste doit etre remis en cash
                $html->addField("conf_reste", "Confirmation du reste remis au guichet", TYPC_MNT);
                $html->setFieldProperties("conf_reste", FIELDP_HAS_BILLET, true);
                $html->setFieldProperties("conf_reste", FIELDP_IS_REQUIRED, true);
            }
        }
        $html->addTableRefField("dest_reste",_("Destination du reste"),"adsys_change_dest_reste");
        $html->setFieldProperties("dest_reste", FIELDP_DEFAULT, $mnt_cv["dest_reste"]);
        $html->setFieldProperties("dest_reste", FIELDP_IS_LABEL, true);

        $html->addField("confirm_commission_depot", _("Commission sur dépôt"), TYPC_MNT);
        $html->setFieldProperties("confirm_commission_depot", FIELDP_DEFAULT, $_POST['commission_agent']);
        $html->setFieldProperties("confirm_commission_depot", FIELDP_IS_READONLY,TRUE);

        $html->addField("confirm_commission_institution", _("Commission pour l'institution"), TYPC_MNT);
        $html->setFieldProperties("confirm_commission_institution", FIELDP_DEFAULT, $_POST['commission_institution']);
        $html->setFieldProperties("confirm_commission_institution", FIELDP_IS_READONLY,TRUE);
    }else{
        $champ_mnt = "mnt";

        $html->addField("mnt",_("Montant déposé au guichet"),TYPC_MNT);

        $html->addField("mnt_reel",_("Confirmation montant"),TYPC_MNT);
        $html->setFieldProperties("mnt_reel", FIELDP_DEVISE, $InfoCpte["devise"]);

        $html->addField("confirm_commission_depot", _("Commission sur dépôt"), TYPC_MNT);
        $html->setFieldProperties("confirm_commission_depot", FIELDP_DEFAULT, $_POST['commission_agent']);
        $html->setFieldProperties("confirm_commission_depot", FIELDP_IS_READONLY,TRUE);

        $html->addField("confirm_commission_institution", _("Commission pour l'institution"), TYPC_MNT);
        $html->setFieldProperties("confirm_commission_institution", FIELDP_DEFAULT, $_POST['commission_institution']);
        $html->setFieldProperties("confirm_commission_institution", FIELDP_IS_READONLY,TRUE);
    }

    $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS["mnt"]);
    $html->setFieldProperties("mnt", FIELDP_DEVISE, $InfoCpte["devise"]);
    $html->setFieldProperties("mnt", FIELDP_IS_LABEL, true);


    $ChkJS = "
             if (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.mnt.value))
           {
             msg += '-"._("Le montant saisi ne correspond pas au montant à déposer")."\\n';
             ADFormValid=false;
           };";

    $html->addJS(JSP_BEGIN_CHECK, "JS3",$ChkJS);
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
    $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Dva-4');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Dva-1');

    $html->buildHTML();
    echo $html->getHTML();

    $SESSION_VARS["set_monnaie_courante"]=$InfoCpte['devise'];
}
else if ($global_nom_ecran == "Dva-4") {
    $client_dataset = getClient($SESSION_VARS['id_cpt_dest']);

    $SESSION_VARS['type_depot'] = '1';
    $isbilletage = getParamAffichageBilletage(); //recuperation du parametre d'affichage de billetage sur les recu

    // capturer des types de billets de la bd et nombre de billets saisie par l'utilisateur
    $valeurBilletArr = array();

    $hasBilletageRecu = true;
    $hasBilletageChange = false;

    // Multidevises
    if (!empty($SESSION_VARS['mnt_cv']['cv'])) {
        $dev = $SESSION_VARS['mnt_cv']['devise'];
        $hasBilletageRecu = false;
        $hasBilletageChange = true;
    } else {
        $dev = $SESSION_VARS["set_monnaie_courante"];
    }

    $listTypesBilletArr = buildBilletsVect($dev);
    $total_billetArr = array();

    //insert nombre billet into array
    for ($x = 0; $x < 20; $x++) {
        if (isset($_POST['mnt_reel_billet_' . $x]) && trim($_POST['mnt_reel_billet_' . $x]) != '') {
            $valeurBilletArr[] = trim($_POST['mnt_reel_billet_' . $x]);
        } else {
            if (isset($listTypesBilletArr[$x]['libel']) && trim($listTypesBilletArr[$x]['libel']) != '') {
                $valeurBilletArr[] = 'XXXX';
            }
        }
    }
    // calcul total pour chaque billets
    for ($x = 0; $x < 20; $x++) {

        if ($valeurBilletArr [$x] == 'XXXX') {
            $total_billetArr [] = 'XXXX';
        } else {
            if (isset ($listTypesBilletArr [$x] ['libel']) && trim($listTypesBilletArr [$x] ['libel']) != '' && isset ($valeurBilletArr [$x] ['libel']) && trim($valeurBilletArr [$x] ['libel']) != '') {
                $total_billetArr [] = ( int )($valeurBilletArr [$x]) * ( int )($listTypesBilletArr [$x] ['libel']);
            }
        }
    }

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
    setMonnaieCourante($SESSION_VARS["set_monnaie_courante"]);

    //mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu
    //$NumCpte et $mnt ont été postés de l'écran précédent; $mnt est le montant net à verser non compris les frais d'opération
    //Vérification si le client n'est pas "débiteur"
    // recupére les information sur le compte
    $InfoCpte = getAccountDatas($SESSION_VARS["id_cpt_dest"]);
    $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
    if ($SESSION_VARS['mnt_cv']['cv'] == '')
        $SESSION_VARS["mnt"] = recupMontant($mnt_reel);
    // remplacer les frais de dépot par la valeur saisie s'il y'a possibilité de modification de frais
    if (isset($SESSION_VARS['frais_depot_cpt']))
        $InfoProduit["frais_depot_cpt"] = $SESSION_VARS["frais_depot_cpt"];

    if ($SESSION_VARS['mnt_cv']['cv'] != '')
        $CHANGE = $SESSION_VARS['mnt_cv'];
    else
        $CHANGE = NULL;

    $data['id_pers_ext'] = $SESSION_VARS['id_pers_ext'];

    if ($SESSION_VARS["type_depot"] == 1) { // dépôt au guichet
        $data['sens'] = 'in ';
        $data['communication'] = $SESSION_VARS['communication'];
        $data['remarque'] = $SESSION_VARS['remarque'];
        $data['commission'] = array('agent' => recupMontant($_POST['confirm_commission_depot']), 'institution' => recupMontant($_POST['confirm_commission_institution']));

        $type_depot = NULL;
        $erreur = depot_cpte_agency($SESSION_VARS["id_cpt_dest"], $SESSION_VARS["id_cpt_dest"], $SESSION_VARS["mnt"], $InfoProduit, $InfoCpte, $data, $type_depot, $CHANGE); //mnt = montant net à déposer

        if ($erreur->errCode == NO_ERR) {

            $id_his = $erreur->param['id'];

            $num_compte = $SESSION_VARS["id_cpt_dest"];
            debug($num_compte, "num cpte");

            $remboursement_cap_lcr = false;
            $total_mnt_cap_lcr = 0;
            //Kheshan ticket pp178p1 bon valeur du montant de depo
            // [Ligne de crédit] : Remboursement Capital
            $lcrErr = rembourse_cap_lcr(date("d/m/Y"), $num_compte, $SESSION_VARS['mnt'], $id_his);

            if ($lcrErr->errCode == NO_ERR) {
                $total_mnt_cap_lcr = $lcrErr->param[1];

                if ($total_mnt_cap_lcr > 0) {
                    $remboursement_cap_lcr = true;
                }
            }

            //prélèvement des frais en attente si solde_disponible > montant_frais
            $prelevement_frais = false;
            $mnt_frais_attente = 0;

            //Y a t-il des frais en attente sur le compte ?
            if (hasFraisAttenteCompte($num_compte)) {
                $result = getFraisAttenteCompte($num_compte);
                $liste_frais_attente = $result->param;
                //Pour chaque frais en attente
                foreach ($liste_frais_attente as $key => $frais_attente) {
                    //Recupération du solde disponible sur le compte
                    $solde_disponible = getSoldeDisponible($num_compte);
                    $montant_frais = $frais_attente['montant'];
                    $type_frais = $frais_attente['type_frais'];
                    $date_frais = $frais_attente['date_frais'];
                    $comptable = array();//pour passage ecritures
                    //vois si le solde disponible est suffisant pour prélever les frais
                    if ($solde_disponible >= $montant_frais) {
                        $erreurs = paieFraisAttente($num_compte, $type_frais, $montant_frais, $comptable);
                        if ($erreurs->errCode != NO_ERR) {
                            return $erreurs;
                        }
                        //Suppression dans la table des frais en attente
                        $sql = "DELETE FROM ad_frais_attente WHERE id_cpte = $num_compte AND date(date_frais) = date('$date_frais') AND type_frais = $type_frais;";
                        $result = executeDirectQuery($sql);
                        if ($result->errCode != NO_ERR) {
                            return new ErrorObj($result->errCode);
                        }
                        $prelevement_frais = true;
                        //memoriser montant des frais prélevés
                        $mnt_frais_attente += $montant_frais;
                        //Historiser le prelevement
                        $myErr = ajout_historique(763, $InfoCpte["id_titulaire"], '', $global_nom_login, date("r"), $comptable, null, $id_his);
                        if ($myErr->errCode != NO_ERR) {
                            $dbHandler->closeConnection(false);
                            return $myErr;
                        }
                    }
                }
            }

            $infos = get_compte_epargne_info($SESSION_VARS['id_cpt_dest']);

            //affectation du parametre hasBilletageChange en cas de multidevise
            ($isbilletage == 'f') ? $hasBilletageChange = false : $hasBilletageChange = true;

            $nom = '';
            switch ($client_dataset['statut_juridique']) {
                case 1 :
                    $nom = $client_dataset['pp_nom'] . " " . $client_dataset['pp_prenom'];
                    break;
                case 2 :
                    $nom = $client_dataset['pm_raison_sociale'];
                    break;
                case 3 :
                    $nom = $client_dataset['gi_nom'];
                    break;
                case 4 :
                    $nom = $client_dataset['gi_nom'];
                    break;
                default :
                    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Statut juridique inconnu !"
            }

            print_recu_depot_agent($client_dataset['id_client'], $nom, floatval(str_replace(" ", '', $SESSION_VARS['mnt_depot'])), $InfoProduit, $infos, $id_his, $data['id_pers_ext'], $SESSION_VARS["remarque"], $SESSION_VARS["communication"], $mnt_frais_attente, $SESSION_VARS['id_mandat'], $listTypesBilletArr, $valeurBilletArr, $global_langue_utilisateur, $total_billetArr, $hasBilletageRecu, $isbilletage,$confirm_commission_depot,$confirm_commission_institution);

            $html_msg = new HTML_message(_("Confirmation de dépôt sur un compte"));
            setMonnaieCourante($InfoCpte['devise']);
            $message = _("Montant déposé sur le compte : ") . afficheMontant(str_replace(" ", '', $SESSION_VARS['mnt_depot']), true);
            if (isset($CHANGE)) {
                // Impression du bordereau de change
                $cpteSource = getAccountDatas($SESSION_VARS['id_cpt_dest']);


                $cpteGuichet = getCompteCptaGui($global_id_guichet);
                $cpteDevise = $cpteGuichet . "." . $SESSION_VARS['mnt_cv']['devise'];

                $SESSION_VARS["mnt_cv"]["source_achat"] = $cpteSource["num_complet_cpte"];//." ".$cpteSource["intitule_compte"];
                $SESSION_VARS["mnt_cv"]["dest_vente"] = $global_guichet;

                //printRecuChange($id_his, $SESSION_VARS["mnt_cv"]["cv"], $SESSION_VARS["mnt_cv"]["devise"], $SESSION_VARS["mnt_cv"]["source_achat"], $SESSION_VARS["mnt"], 'fr_BE', $SESSION_VARS["mnt_cv"]["comm_nette"], $SESSION_VARS["mnt_cv"]["taux"], $SESSION_VARS["mnt_cv"]["reste"], $SESSION_VARS["mnt_cv"]["dest_vente"], NULL, NULL, $listTypesBilletArr, $valeurBilletArr, $global_langue_rapport, $total_billetArr, $hasBilletageChange);

                setMonnaieCourante($CHANGE['devise']);
                $message .= "<br>" . _("Montant déposé au guichet : ") . afficheMontant($CHANGE['cv'], true);
            }
            if ($confirm_commission_depot > 0) {
                setMonnaieCourante($InfoCpte['devise']);
                $message .= "<br>" . _("Commission pour agent : ") . afficheMontant(recupMontant($confirm_commission_depot), true);
            }
            if ($confirm_commission_institution > 0) {
                setMonnaieCourante($InfoCpte['devise']);
                $message .= "<br>" . _("Commission pour institution : ") . afficheMontant(recupMontant($confirm_commission_institution), true);
            }

            if ($erreur->param["mnt"] > 0) {
                $message .= "<br>" . _("Des frais impayés ont été débités de votre compte de base pour un montant de") . " :<br>";
                $message .= afficheMontant($erreur->param["mnt"], true);
            }
            if ($prelevement_frais) {
                $message .= "<br>" . _("Des frais en attente ont été débités de votre compte de base pour un montant de") . " :<br>";
                $message .= afficheMontant($mnt_frais_attente, true);
            }
            if ($remboursement_cap_lcr) {
                $message .= "<br>" . _("Ligne de crédit : Le capital restant dû a été débité de votre compte de base pour un montant de") . " :<br>";
                $message .= afficheMontant($total_mnt_cap_lcr, true);
            }
            $message .= "<br /><br />" . _("N° de transaction") . " : <B><code>" . sprintf("%09d", $erreur->param['id']) . "</code></B>";
            $html_msg->setMessage($message);
            $html_msg->addButton("BUTTON_OK", 'Gen-16');
            $html_msg->buildHTML();
            echo $html_msg->HTML_code;
        } else {
            $html_err = new HTML_erreur(_("Echec du dépôt sur un compte. "));
            $html_err->setMessage("Erreur : " . $error[$erreur->errCode]);
            $html_err->addButton("BUTTON_OK", 'Gen-16');
            $html_err->buildHTML();
            echo $html_err->HTML_code;
        };
    }
    else {
        $html_err = new HTML_erreur(_("Echec du dépôt d'un chèque sur un compte.") . " ");
        $html_err->setMessage("Erreur : " . $error[$erreur->errCode]);
        $html_err->addButton("BUTTON_OK", 'Dcp-1');
        $html_err->buildHTML();
        echo $html_err->HTML_code;
    }
    // On vérifie si le client n'est plus débiteur
    if (!isClientDebiteur($client_dataset['id_client']))
        $global_client_debiteur = false;
}
?>