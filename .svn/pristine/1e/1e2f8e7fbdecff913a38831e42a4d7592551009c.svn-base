<?php

/**
 * Gestion des retraits via agents
 * @package Parametrage
 */

require_once 'lib/dbProcedures/utilisateurs.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/bdlib.php';
require_once 'lib/dbProcedures/agency_banking.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/divers.php';
require_once 'modules/epargne/recu.php';

// Rva-1 : Saisie du compte client
if ($global_nom_ecran == "Rva-1"){
  $html = new HTML_GEN2(_("Retrait via agent : choix du client"));
  $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $html->addHTMLExtraCode("html_js",$html_js);
  $xtra1 = "<b>"._("Choix du compte")."</b>";
  $html->addHTMLExtraCode ("htm1", $xtra1);
  $html->addField("cpt_dest",_("Compte client"), TYPC_TXT);
  $html->setFieldProperties("cpt_dest", FIELDP_IS_REQUIRED, true);
  $html->addLink("cpt_dest", "rechercher", _("Rechercher"), "#");
  $str = "if (document.ADForm.cpt_dest.disabled == false) OpenBrw('../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest=cpt_dest&id_cpt_dest=id_cpt', '"._("Recherche")."');return false;";
  $html->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => $str));
  $html->addHiddenType("id_cpt", "");
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
  }else {
      if (empty($agent['cpte_flotte_agent'])) {
          $erreur = new HTML_erreur(_("Compte de flotte"));
          $erreur->setMessage(_("Le compte de flotte de l'agent " . $agent_dataset['nom'] . " n'est pas parametré."));
          $erreur->addButton(BUTTON_OK, "Gen-16");
          $erreur->buildHTML();
          echo $erreur->HTML_code;
          $ok = false;
      } else {
          $choix2 = array();
          $choix2[1]=_('Retrait en espèce');

          foreach($choix2 as $key=>$value) {
              //Type de dépôt autorisé : 1:espèce, 2:chèque, 3:ordre de paiement, 5:Travelers cheque
              if ($key!=1 and $key!=2 and $key!=3 and $key!=5) {
                  unset($choix2[$key]);
              }
          }

          $html->addField("type_retrait1", _("Type de retrait"), TYPC_TXT);
          $html->setFieldProperties("type_retrait1", FIELDP_DEFAULT, 'Retrait en espèce');
          $html->setFieldProperties("type_retrait1", FIELDP_IS_LABEL, true);

          $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
          $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
          $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
          $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
          $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
          $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rva-2');
          $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-16');
          $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
          $html->addHiddenType("type_retrait", 1);

          $js = "$('[name=cpt_dest]').attr('readonly', 'readonly');";
          $html->addJS(JSP_FORM, "JS1", $js);
          $html->buildHTML();
          echo $html->getHTML();
      }
  }
}
else if ($global_nom_ecran == "Rva-2"){


  global $global_id_client,$global_id_agence;
  $id_titulaire = getDataCpteEpargne($_POST['id_cpt']);

  $IMGS = getImagesClient($id_titulaire["id_titulaire"]);
  $global_photo_client = $IMGS["photo"];
  $global_signature_client = $IMGS["signature"];


  $html = new HTML_GEN2();

  $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $html->addHTMLExtraCode("html_js",$html_js);
  $communication = $remarque = "";
  unset($SESSION_VARS['cpt_dest']);
  unset($SESSION_VARS['id_cpt_dest']);

  //Enregistrement des informations postées en Rcp-1
  if (isset($id_cpt))      $SESSION_VARS["id_cpt"]      = $id_cpt;
  if (isset($cpt_dest))      $SESSION_VARS["id_cpt_retrait"]      = $cpt_dest;
  if (isset($type_retrait)) $SESSION_VARS["type_retrait"] = $type_retrait;

  $charTitre=_("Retrait compte via Agent : montant");
  $charMnt=_("du compte");
  $charCv=_("à remettre au guichet");


  $html->setTitle($charTitre);

  // Ajout des champs ornementaux
  $xtra1 = "<b>"._("Informations compte")."</b>";
  $html->addHTMLExtraCode ("htm1", $xtra1);
  $html->setHTMLExtraCodeProperties ("htm1",HTMP_IN_TABLE, true);
  $xtra2 = "<b>"._("Montant à retirer")."</b>";
  $html->addHTMLExtraCode ("htm2", $xtra2);
  $html->setHTMLExtraCodeProperties ("htm2",HTMP_IN_TABLE, true);

  //Informations compte
  $cpteSource=getAccountDatas($SESSION_VARS['id_cpt']);
  $soldeCptSource = $cpteSource["solde"];
  $soldeDispo=getSoldeDisponible($cpteSource['id_cpte']);// - $cpteSource['frais_retrait_cpt'];
  $DEV_SRC = getInfoDevise($cpteSource['devise']);
  $precision_dev_src = $DEV_SRC["precision"];
  setMonnaieCourante($cpteSource['devise']);

  $infoCpte=getAccountDatas($SESSION_VARS['id_cpt']);
  $MANDATS = getListeMandatairesActifsV2($SESSION_VARS['id_cpt'],null,true);
  if ($MANDATS != NULL) {
    foreach($MANDATS as $key=>$value) {
      $MANDATS_LSB[$key] = $value['libelle'];
      if ($key == 'CONJ_id'){
        $MANDATS_LSB[$key] = $value['id'];
      }
      elseif ($key == 'CONJ') {
        $JS_open .= "if (document.ADForm.HTML_GEN_LSB_mandat.value == '$key')
        {
          OpenBrw('$SERVER_NAME/modules/externe/info_mandat.php?m_agc=".$_REQUEST['m_agc']."&id_cpte=".$SESSION_VARS['id_cpt']."');
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
  if (in_array($SESSION_VARS['type_retrait'], array(8,15))) {
    $html->addField("beneficiaire", _("Bénéficiaire"), TYPC_LSB);
    $html->setFieldProperties("beneficiaire", FIELDP_IS_REQUIRED, true);
    $html->setFieldProperties("beneficiaire", FIELDP_ADD_CHOICES, array("TITS" => _("Titulaire") . " (" . getClientName($cpteSource['id_titulaire']) . ")"));

    $html->setFieldProperties("beneficiaire", FIELDP_HAS_CHOICE_AUCUN, true);
    $html->setFieldProperties("beneficiaire", FIELDP_HAS_CHOICE_TOUS, false);
    $html->setFieldProperties("beneficiaire", FIELDP_DEFAULT, $SESSION_VARS['id_mandat']);

    $JS_change_benef =
      "if (document.ADForm.HTML_GEN_LSB_beneficiaire.value == '0')
 		  {
 		    document.ADForm.nom_ben.value = '';
 		    document.ADForm.id_ben.value = '';
 		  }else if (document.ADForm.HTML_GEN_LSB_beneficiaire.value == 'TITS')
 		  {
 		    document.ADForm.nom_ben.value = '" . getClientName($cpteSource['id_titulaire']) . "';
 		    document.ADForm.id_ben.value = '" . $cpteSource['id_titulaire'] . "';
 		  }else if (document.ADForm.HTML_GEN_LSB_beneficiaire.value == 'EXT')
 		  {
 		    document.ADForm.nom_ben.value = '';
 		    document.ADForm.id_ben.value = '';
 		  }";
  }
  $html->addField("mandat", _("Donneur d'ordre"), TYPC_LSB);

  $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("0" => _("Titulaire")." (".getClientName($cpteSource['id_titulaire']).")"));

  if ($MANDATS_LSB != NULL) {
    $MANDATS_LSB = array_flip($MANDATS_LSB); // array(valeur = >cle) au lieu de array(cle => valeur)
    unset($MANDATS_LSB[getClientName($cpteSource['id_titulaire'])]); //on supprime le nom du titulaire dans la liste déroulante
    $MANDATS_LSB = array_flip($MANDATS_LSB); // on remet le array(cle => valeur)
    $LSB_MANDATS = $MANDATS_LSB;
    unset($LSB_MANDATS['CONJ_id']);
    $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, $LSB_MANDATS);

    if (in_array($SESSION_VARS['type_retrait'], array(8,15))) {
      $html->setFieldProperties("beneficiaire", FIELDP_ADD_CHOICES, $LSB_MANDATS);

      foreach ($MANDATS_LSB as $key => $value) {
        $JS_change_benef .= "
                 else if (document.ADForm.HTML_GEN_LSB_beneficiaire.value == $key)
                 {
                   document.ADForm.nom_ben.value = '" . $value . "';
                   document.ADForm.id_ben.value = '" . $key . "';
                 }";
      }
    }
  }

  if (in_array($SESSION_VARS['type_retrait'], array(8,15))) {
    $html->setFieldProperties("beneficiaire", FIELDP_ADD_CHOICES, array("EXT" => _("Personne non cliente")));
    $html->setFieldProperties("beneficiaire", FIELDP_JS_EVENT, array("onchange" => $JS_change_benef));
  }

  $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_AUCUN, false);

  $html->setFieldProperties("mandat", FIELDP_HAS_CHOICE_TOUS, false);

  if (isset($SESSION_VARS['denomination_conj']) && $SESSION_VARS['denomination_conj'] != null){
    $html->setFieldProperties("mandat", FIELDP_DEFAULT, "CONJ");
  }
  else{
    $html->setFieldProperties("mandat", FIELDP_DEFAULT, $SESSION_VARS['id_mandat']);
  }

  $html->setFieldProperties("mandat", FIELDP_JS_EVENT, array("onchange" => $JS_change));

  $html->setFieldProperties("mandat", FIELDP_ADD_CHOICES, array("EXT" => _("Personne non cliente")));

  $html->addJS(JSP_BEGIN_CHECK, "limitation_check", $JS_check);
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
 	    msg += '"._("- Vous devez choisir une personne non cliente")."\\n';
 	    ADFormValid=false;
 	  }";
  $html->addJS(JSP_BEGIN_CHECK, "JS2", $JS_check);

  $html->addHTMLExtraCode("mandat_sep", "<br/>");
  $champsProduit = array ("libel");
  $champsCpte = array("num_complet_cpte", "intitule_compte", "etat_cpte");
  $ordre = array("mandat", "denomination", "mandat_sep", "htm1", "num_complet_cpte", "libel", "intitule_compte", "etat_cpte");
  $labelField = array ("num_complet_cpte", "intitule_compte", "etat_cpte", "libel");

  $access_solde = get_profil_acces_solde($global_id_profil, $cpteSource['id_prod']);
  $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $cpteSource['id_titulaire']);

  if(manage_display_solde_access($access_solde, $access_solde_vip)){
    $html->addField("solde_dispo", _("Solde disponible"), TYPC_MNT);
    $html->setFieldProperties("solde_dispo", FIELDP_DEFAULT, $soldeDispo);
    array_push($ordre, "solde_dispo");
    array_push($labelField, "solde_dispo");
    $cpteSource['solde_dispo'] = $soldeDispo;
  }

  //array_push($ordre, "frais_retrait_cpt");
  //array_push($labelField, "frais_retrait_cpt");


  $html->addTable("ad_cpt", OPER_INCLUDE, $champsCpte);
  $fill=new FILL_HTML_GEN2();
  $fill->addFillClause("cpteSource", "ad_cpt");
  $fill->addCondition("cpteSource", "id_cpte", $SESSION_VARS['id_cpt']);
  $fill->addManyFillFields("cpteSource", OPER_INCLUDE, $champsCpte);
  $fill->fill($html);

  $html->addTable("adsys_produit_epargne", OPER_INCLUDE, $champsProduit);
  $fill2 = new FILL_HTML_GEN2();
  $fill2->addFillClause("produit", "adsys_produit_epargne");
  $fill2->addCondition("produit", "id", $cpteSource['id_prod']);
  $fill2->addManyFillFields("produit", OPER_INCLUDE, $champsProduit);
  $fill2->fill($html);


  // Montant à retirer
  $html->addField("mnt",$charMnt,TYPC_MNT);
  $html->setFieldProperties("mnt", FIELDP_IS_REQUIRED, true);
  $html->setFieldProperties("mnt", FIELDP_JS_EVENT, array("onChange"=>"setCommission();"));
  array_push($ordre, "htm2", "mnt");
  if ($global_multidevise) {
      $html->addField("mnt_cv",$charCv,TYPC_DVR);
      $html->linkFieldsChange("mnt_cv","mnt","vente",1,true);
      $html->setFieldProperties("mnt_cv",  FIELDP_IS_REQUIRED, true);
  }



  $html->addField("commission_agent", _("Commission pour agent"), TYPC_MNT);
  $html->setFieldProperties("commission_agent", FIELDP_DEFAULT, 0);
  $html->setFieldProperties("commission_agent", FIELDP_IS_READONLY, true);

  $html->addField("commission_institution", _("Commission pour institution"), TYPC_MNT);
  $html->setFieldProperties("commission_institution", FIELDP_DEFAULT, 0);
  $html->setFieldProperties("commission_institution", FIELDP_IS_READONLY, true);

  array_push($ordre, "commission_agent","commission_institution");


  $xtra4 = "<b>"._("Communication / remarque")."</b>";
  $html->addHTMLExtraCode ("htm4", $xtra4);
  $html->setHTMLExtraCodeProperties ("htm4",HTMP_IN_TABLE, true);

  $html->addField("communication", _("Communication"), TYPC_TXT);
  $html->setFieldProperties("communication", FIELDP_DEFAULT, $communication);
  $html->addField("remarque", _("Remarque"), TYPC_ARE);
  $html->setFieldProperties("remarque", FIELDP_DEFAULT, $remarque);

  array_push($ordre, "htm4", "communication", "remarque");

  //mise en ordre et en label des champs affichés
  $html->setOrder(NULL, $ordre);
  foreach($labelField as $key=>$value) {
    $html->setFieldProperties($value, FIELDP_IS_LABEL, true);
  }

  //Code JavaScript
  $ChkJS = "
           if (recupMontant(document.ADForm.mnt.value) > ".$soldeDispo.")
         {
           msg += '- "._("Le montant du retrait  est supérieur au solde disponible")."\\n';
           ADFormValid=false;
         }
           if (document.ADForm.etat_cpte.value=='3')
         {
           msg += '- "._("Le compte est bloqué")."\\n';
           ADFormValid=false;
         }";


  //$html->setFieldProperties("frais_retrait_cpt", FIELDP_CAN_MODIFY, true);

  $ChkJS .= "
              if (ADFormValid == true) {
                if (document.ADForm.nom_ben) {
                    document.ADForm.nom_ben.disabled = false;
                }
                if (document.ADForm.denomination) {
                    document.ADForm.denomination.disabled = false;
                }
              }
            ";

  $html->addJS(JSP_BEGIN_CHECK, "JS1",$ChkJS);

  $choix_retrait_comm = getParamCommissionInsti();
  if ($choix_retrait_comm['choix_retrait_comm'] == null || $choix_retrait_comm['cpte_compta_comm_retrait'] == null){
    $erreur = new HTML_erreur(_("Paramétrage compte commissions"));
    $erreur->setMessage(_("Le compte de commission pour l'institution n'est pas parametré."));
    $erreur->addButton(BUTTON_OK, "Gen-16");
    $erreur->buildHTML();
    echo $erreur->HTML_code;
    $ok = false;
    die();
  }
  $ag_commission  = getCommissionDepotRetrait(1);
  $ad_log =  getDatasLogin();
  $plafond_retrait = empty($ad_log['plafond_retrait'])?0:$ad_log['plafond_retrait'];


  $mntjs = "
  var dataset = ".json_encode($ag_commission).";
  function setCommission(){
        setTimeout(function(){
        var plafond_retrait = parseInt(".$plafond_retrait.");
        var element_val = parseInt($('[name=mnt]').val().replace(/\s/g, ''));

        if(dataset == null){
            $('[name=mnt]').val('');
            $('[name=commission_agent]').val(0);
            $('[name=commission_institution]').val(0);
            alert('Les commissions n\'ont pas été paramétré');
        }else if(element_val > plafond_retrait){
            alert('Le montant saisi est supérieur au plafond de retrait');
            $('[name=commission_agent]').val(0);
            $('[name=commission_institution]').val(0);
            $('[name=mnt]').val('');
        }else{
            for(var index = 0; index < dataset.length; index++){
                var element_val = parseInt($('[name=mnt]').val().replace(/\s/g, ''));
                var commission_choice = ".$choix_retrait_comm["choix_retrait_comm"].";
                var calculator_agent = (dataset[index].comm_agent_mnt == null)?((dataset[index].comm_agent_prc/100)*element_val):dataset[index].comm_agent_mnt;
                var calculator_inst = (dataset[index].comm_inst_mnt == null)?((dataset[index].comm_inst_prc/100)*element_val):dataset[index].comm_inst_mnt;
               
                
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
                      alert('Le montant de retrait est supérieure au palier maximal');
                      $('[name=mnt]').val('');
                      $('[name=commission_agent]').val('');
                      $('[name=commission_institution]').val('');
//                    if(commission_choice == '2'){
//                        $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
//                        $('[name=commission_institution]').val(Math.floor(parseInt(calculator_inst)));
//                    }else{
//                        $('[name=commission_agent]').val(Math.floor(parseInt(calculator_agent)));
//                        $('[name=commission_institution]').val(0);
//                    }
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
        
        function check_solde(){
            var solde_dispo = parseInt($('[name=solde_dispo]').val().replace(/\s/g, ''));
            var commission_choice = ".$choix_retrait_comm["choix_retrait_comm"].";
            var mnt = parseInt($('[name=mnt]').val().replace(/\s/g, ''));
            var comm_agent = parseInt($('[name=commission_agent]').val().replace(/\s/g, ''));
            var comm_ins = parseInt($('[name=commission_institution]').val().replace(/\s/g, '')); 
            
                var total = mnt + comm_agent + comm_ins;
                if(total > solde_dispo){
                    alert('Le montant saisi ainsi que le montant des commissions ne sont pas suffisant pour ce retrait!');
                    $('[name=mnt]').val('');
                }else{
                    assign('Rva-3');
                    document.ADForm.submit();
                }
        }
    ";

  $html->addJS(JSP_FORM, "JS0",$mntjs);

    // Boutons
  $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_BUTTON);
  $html->addFormButton(1, 2, "retour", _("Précédent"), TYPB_SUBMIT);
  $html->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
//  $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rva-3');
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Gen-16');
  $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');
  $html->setFormButtonProperties("ok", BUTP_JS_EVENT, array('onclick' => 'check_solde();'));

  $html->buildHTML();
  echo $html->getHTML();

}
else if ($global_nom_ecran == "Rva-3"){
global $global_nom_login;

  if (isset($mnt) && $mnt !='')
  {
    $SESSION_VARS["mnt"] = recupMontant($mnt);
  }


  if ($mandat != 0 && $mandat != 'CONJ') {
    $SESSION_VARS['id_mandat'] = $mandat;
    $infos_pers_ext = getInfosMandat($SESSION_VARS['id_mandat']);
    $SESSION_VARS['id_pers_ext'] = $infos_pers_ext['id_pers_ext'];
  } else {
    $SESSION_VARS['id_mandat'] = NULL;
  }
  if ($mandat == 'EXT') {
    $SESSION_VARS['id_pers_ext'] = $id_pers_ext;
    $SESSION_VARS['denomination'] = $denomination;

  }
  if(isset($SESSION_VARS['denomination_conj'])) {
    unset ($SESSION_VARS['denomination_conj']);
  }
  if($mandat == 'CONJ') {
    $SESSION_VARS['denomination_conj']=$SESSION_VARS['mandat']['CONJ'];
  }
  // sauvegarde des données postées
  $erreurGuichet=false;
  if ($_POST['mnt_cv']['cv'] != '') // A-t-on réalisé une opération de change ?
    $change_effectue = true;
  else
    $change_effectue = false;

  //on sauvegarde la devise du montant à donner au guichet
  if ($_POST['mnt_cv']['devise'] != '')
    $SESSION_VARS['devise']= $_POST['mnt_cv']['devise'];
  else
    $SESSION_VARS['devise']= $global_monnaie;

  if ($change_effectue) {
    debug($SESSION_VARS);
    debug("<===");

    $SESSION_VARS['change']= $_POST['mnt_cv'];
    if (isset($mnt_cv_reste)){ //AT-141 - si c'est en multi-devise et on est passé par une demande et approbation de la processus,
      //il faut utiliser les informations qui ont été stocké dans la base
      $SESSION_VARS['change']['reste'] = $mnt_cv_reste;
      $SESSION_VARS['change']['taux'] = $taux;
      $SESSION_VARS['change']['comm_nette'] = $commission;
      $SESSION_VARS['change']['dest_reste'] = $dest_reste;
      $SESSION_VARS['print_recu_change'] = 1;
      $SESSION_VARS['envoi_reste'] = 1;
    }
    // on vérifie si le guichet dans la devise du retrait est correctement approvisionné.
    if ($SESSION_VARS['change']['devise'] != $global_monnaie) {
      $cpteGuichet=getCompteCptaGui($global_id_guichet);
      $cpteDevise=$cpteGuichet.".".$SESSION_VARS['change']['devise'];
      $param['num_cpte_comptable']=$cpteDevise;
      $infoCpteGuichet=getComptesComptables($param);
      $infoCpteGuichet = $infoCpteGuichet[$cpteDevise];
      debug($infoCpteGuichet);
      if (isset($infoCpteGuichet)) {
        if (($SESSION_VARS["type_retrait"] != 5) && ($SESSION_VARS['change']['cv'] + $infoCpteGuichet['solde']) > 0) {
          $erreurGuichet=true;
          $charTitle=_("Solde guichet insuffisant");
          setMonnaieCourante($SESSION_VARS['change']['devise']);
          $message =  _("Solde insuffisant sur le guichet en")." ".$SESSION_VARS['change']['devise']." (".afficheMontant(-$infoCpteGuichet['solde'],true).")";
        }
      } else {
        $erreurGuichet=true;
        $charTitle=_("Guichet inexistant");
        $message = _("le guichet dans la devise finale n'existe pas")." (".$SESSION_VARS['change']['devise'].")" ;
      }
    }
  }

// check si compte de flotte possede assez d'argent pour le retrait

  $SESSION_VARS["remarque"] = $remarque;
  $SESSION_VARS["communication"] = $communication;

  // REL-63 : Ajout verification limitation retrait pour le mandataire choisit
  $Liste_mandataires = getListeMandatairesActifsV2($SESSION_VARS["id_cpt"],null,true);
  $retrait_impossible = false;
  if (isset($mandat)){
    if ($mandat > 0){ //Type seule
      if (isset($Liste_mandataires[$mandat]) && $Liste_mandataires[$mandat] != null && $Liste_mandataires[$mandat]['limitation'] > 0){
        if (recupMontant($Liste_mandataires[$mandat]['limitation']) < recupMontant($mnt)){ //si le montant à retirer est superieure au limit de retrait pour ce mandataire
          $retrait_impossible = true;
          $titre = "Retrait impossible pour le mandataire (".$Liste_mandataires[$mandat]['libelle'].") de type seule";
        }
      }
    }
    if ($mandat == 'CONJ'){ //Type conjointe
      if (isset($Liste_mandataires[$mandat]) && $Liste_mandataires[$mandat] != null){
        $liste_mandats = getMandats($SESSION_VARS['id_cpt']);
        $liste_CONJ_id = explode('-',$SESSION_VARS['mandat']['CONJ_id']);
        $mnt_limite = 0;
        $limitation = 0;
        foreach ($liste_CONJ_id as $conj_id => $value) {
          if ($value != null) {
            $mnt_limite = recupMontant($liste_mandats[$value]['limitation']);
            if ($mnt_limite != null && $mnt_limite != 0) {
              if ($limitation == 0) {
                $limitation = $mnt_limite;
              }
              $limitation = min($limitation, $mnt_limite); // Si on a plusieurs mandataires conjointe on prend le minimum des montants limitation
            }
          }
        }
        if ($limitation != null && $limitation != 0){
          if ($limitation < recupMontant($mnt)) { //si le montant à retirer est superieure au limit de retrait pour ce mandataire
            $retrait_impossible = true;
            $titre = "Retrait impossible pour le(s) mandataire(s) (" . $Liste_mandataires[$mandat]['libelle'] . ") de type conjointe";
            $mnt_conj_limite = "(" . number_format($limitation, 0, '.', ' ') . ")";
          }
        }
      }
    }
    if ($retrait_impossible){
      $msg = "Le montant ($mnt) à retirer est supérieure au limite $mnt_conj_limite de rétrait!! Veuillez cliquer le bouton OK pour re-saisir le montant sur l'ecran precedent!";
      $html_err = new HTML_erreur($titre);
      $html_err->setMessage($msg);
      $html_err->addButton("BUTTON_OK", "Rcp-2");
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit;
    }
  }


  if (!$erreurGuichet) {

    if (isset($frais_retrait_cpt))
      $SESSION_VARS['Frais'] = recupMontant($frais_retrait_cpt);

    //Alimentation des zones d'affichage
    if (isset($SESSION_VARS['change'])) {
      switch ($SESSION_VARS['type_retrait']) {
        case 1:
        case 15:
          $charTitle=_("Confirmation retrait");
          $charMnt=_("Montant à débiter du compte");
          $charMntCV =_("Montant guichet");
          break;
        case 8:
          $charTitle=_("Confirmation retrait chèque certifié");
          $charMnt=_("Montant à débiter du compte");
          $charMntCV =_("Montant guichet");
          break;
        case 4:
          $charTitle=_("Confirmation retrait-chèque");
          $charMnt=_("Montant du chèque");
          $charMntCV =_("Montant guichet");
          break;
        case 5:
          $charTitle=_("Confirmation retrait Travelers");
          $charMnt=_("Montant à débiter du compte");
          $charMntCV =_("Montant des Travelers cheque");
          break;
      }
    } else {
      switch ($SESSION_VARS['type_retrait']) {
        case 1:
        case 15:
          $charTitle=_("Confirmation retrait");
          $charMnt=_("Montant à retirer");
          break;
        case 8:
          $charTitle=_("Confirmation retrait chèque certifié");
          $charMnt=_("Montant à retirer");
          break;
        case 4:
          $charTitle=_("Confirmation retrait-chèque");
          $charMnt=_("Montant du chèque");
          break;
        case 5:
          $charTitle=_("Confirmation retrait Travelers");
          $charMnt=_("Montant des Travelers");
          break;
      }
    }
    $charMntReel=_("Confirmation montant");

    //récupérer le infos sur le produit associé au compte sélectionné
    $InfoCpte = getAccountDatas($SESSION_VARS["id_cpt"]);

    //Affichage du titre
    $html = new HTML_GEN2($charTitle);

    //Crontôler si le montant à retirer ne dépasse pas le montant plafond de retrait autorisé s'il y a lieu
    global $global_nom_login, $global_id_agence, $colb_tableau;
    $info_login = get_login_full_info($global_nom_login);
    $info_agence = getAgenceDatas($global_id_agence);
    /*$msg = "";
    if (!isset($SESSION_VARS['id_dem']) && $info_agence['plafond_retrait_guichet'] == 't'){
      if($info_login['depasse_plafond_retrait'] == 'f' && $SESSION_VARS["mnt"] > $info_agence['montant_plafond_retrait']){
        //$msg = "<center>"._("Le montant demandé dépasse le montant plafond de retrait autorisé. Ce login n'est pas habilité à le faire.");
        //$msg .= " "._("Veuillez contacter votre administrateur.")."</center>";

        // Affichage de la confirmation
        $html_msg = new HTML_message("Demande autorisation de retrait");

        $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le montant demandé dépasse le montant plafond de retrait autorisé.</span><br /><br />Montant demandé = <span style='color: #FF0000;font-weight: bold;'>".afficheMontant($SESSION_VARS["mnt"], true)."</span><br/>Montant plafond de retrait autorisé = ".afficheMontant($info_agence['montant_plafond_retrait'], true)."<br /><br />Veuillez choisir une option ci-dessous ?<br /><br/></center><input type=\"hidden\" name=\"montant_retrait\" value=\"".recupMontant($mnt)."\" /><input type=\"hidden\" name=\"devise\" value=\"".trim($mnt_cv['devise'])."\" /><input type=\"hidden\" name=\"mnt_devise\" value=\"".recupMontant($mnt_cv['cv'])."\" /><input type=\"hidden\" name=\"mnt_reste\" value=\"".recupMontant($mnt_cv['reste'])."\" /><input type=\"hidden\" name=\"taux_devise\" value=\"".recupMontant($mnt_cv['taux'])."\" /><input type=\"hidden\" name=\"taux_commission\" value=\"".recupMontant($mnt_cv['comm_nette'])."\" /><input type=\"hidden\" name=\"dest_reste\" value=\"".recupMontant($mnt_cv['dest_reste'])."\" /><input type=\"hidden\" name=\"frais_retrait_cpt\" value=\"".recupMontant($frais_retrait_cpt)."\" /><input type=\"hidden\" name=\"type_retrait\" value=\"1\" /><input type=\"hidden\" name=\"choix_retrait\" value=\"".$SESSION_VARS['type_retrait']."\" /><input type=\"hidden\" name=\"num_chq\" value=\"".trim($num_chq)."\" /><input type=\"hidden\" name=\"communication\" value=\"".trim($communication)."\" /><input type=\"hidden\" name=\"remarque\" value=\"".trim($remarque)."\" /><input type=\"hidden\" name=\"id_pers_ext\" value=\"".trim($id_pers_ext)."\" /><input type=\"hidden\" name=\"id_ben\" value=\"".trim($id_ben)."\" /><input type=\"hidden\" name=\"date_chq\" value=\"".trim($date_chq)."\" /><input type=\"hidden\" name=\"mandat\" value=\"".trim($mandat)."\" /><input type=\"hidden\" name=\"beneficiaire\" value=\"".trim($beneficiaire)."\" /><input type=\"hidden\" name=\"nom_ben\" value=\"".trim($nom_ben)."\" /><input type=\"hidden\" name=\"denomination\" value=\"".trim($denomination)."\" /><input type=\"hidden\" name=\"num_piece\" value=\"".trim($SESSION_VARS['tib']['num_piece'])."\" /><input type=\"hidden\" name=\"lieu_delivrance\" value=\"".trim($SESSION_VARS['tib']['lieu_delivrance'])."\" />");

        $html_msg->addCustomButton("btn_demande_autorisation_retrait", "Demande d’autorisation", 'Rex-4');
        $html_msg->addCustomButton("btn_annuler", "Annuler", 'Gen-8');

        $html_msg->buildHTML();

        echo $html_msg->HTML_code;
        die();
      }
    }
    /*if ($msg != "") {
       $html = new HTML_erreur(_("Retrait impossible")." ");
       $html->setMessage($msg);
       $html->addButton(BUTTON_OK, "Rcp-2");
       $html->buildHTML();
       echo $html->HTML_code;
       exit();
    }*/

    if (isset($SESSION_VARS['change'])) { // operation multi devises
      $html->addField("mnt",$charMnt,TYPC_MNT);
      $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS["mnt"]);
      $html->setFieldProperties("mnt", FIELDP_IS_LABEL, true);

      setMonnaieCourante($SESSION_VARS['devise']);
      $html->addField("mntCV",$charMntCV,TYPC_MNT);
      $html->setFieldProperties("mntCV", FIELDP_DEFAULT, $SESSION_VARS['change']['cv']);
      $html->setFieldProperties("mntCV", FIELDP_IS_LABEL, true);

      $html->addField("mnt_reel",$charMntReel,TYPC_MNT);
      $html->setFieldProperties("mnt_reel", FIELDP_IS_REQUIRED, true);
      $html->setFieldProperties("mnt_reel", FIELDP_DEVISE, $mnt_cv["devise"]);

      global $global_billet_req;

     /* if ($global_billet_req && $SESSION_VARS["type_retrait"] != 5) {
        $html->setFieldProperties("mnt_reel", FIELDP_HAS_BILLET, true);
        $html->setFieldProperties("mnt_reel", FIELDP_SENS_BIL, SENS_BIL_OUT);
      }*/

      if($SESSION_VARS['change']['reste']>0) {
        debug($SESSION_VARS["change"]);
        setMonnaieCourante($global_monnaie);
        $html->addField("reste",_("Reste à toucher"),TYPC_MNT);
        $html->setFieldProperties("reste", FIELDP_DEFAULT, $SESSION_VARS["change"]['reste']);
        $html->setFieldProperties("reste", FIELDP_IS_LABEL, true);
        if ($SESSION_VARS["change"]["dest_reste"] == 1) { // Le reste doit etre remis en cash
          $html->addField("conf_reste", _("Confirmation du reste remis au guichet"), TYPC_MNT);
          $html->setFieldProperties("conf_reste", FIELDP_HAS_BILLET, true);
          $html->setFieldProperties("conf_reste", FIELDP_IS_REQUIRED, true);
        }
      }
    }
    else {

      $champ_mnt = "mnt";

      //confirmation du montant à retirer
      setMonnaieCourante($InfoCpte['devise']);
      $html->addField("mnt",$charMnt,TYPC_MNT);
      $html->setFieldProperties("mnt", FIELDP_DEFAULT, $SESSION_VARS["mnt"]);
      $html->setFieldProperties("mnt", FIELDP_IS_LABEL, true);

      $html->addField("mnt_reel",$charMntReel,TYPC_MNT);
      $html->setFieldProperties("mnt_reel", FIELDP_IS_REQUIRED, true);
      setMonnaieCourante($SESSION_VARS['devise']);
      // Au cas où on fait un retrait autre qu'un retrait en traveler's, il faudra saisir le billetage
     /* global $global_billet_req;
      if ($global_billet_req && $SESSION_VARS["type_retrait"] != 5) {
        $html->setFieldProperties("mnt_reel", FIELDP_HAS_BILLET, true);
        $html->setFieldProperties("mnt_reel", FIELDP_SENS_BIL, SENS_BIL_OUT);
      }*/
    }

    $SESSION_VARS['commission_agent']=$commission_agent;
    $SESSION_VARS['commission_institution']=$commission_institution;
    // Frais Agent
    $html->addField("commission_agent", _("Commission agent"), TYPC_MNT);
    $html->setFieldProperties("commission_agent", FIELDP_DEFAULT, $SESSION_VARS['commission_agent']);
    $html->setFieldProperties("commission_agent", FIELDP_IS_LABEL, true);

    // Frais Institution
    $html->addField("commission_institution", _("Commission institution"), TYPC_MNT);
    $html->setFieldProperties("commission_institution", FIELDP_DEFAULT, $SESSION_VARS['commission_institution']);
    $html->setFieldProperties("commission_institution", FIELDP_IS_LABEL, true);

    //code JavaScript
    if (isset($SESSION_VARS['change'])) {
      $ChkJS = "
               if (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.mntCV.value))
             {
               msg += '- "._("Le montant saisi ne correspond pas au montant à retirer")."\\n';
               ADFormValid=false;
             };
               ";
      if ($SESSION_VARS["change"]["reste"] > 0 && $SESSION_VARS["change"]["dest_reste"] == 1)
        $ChkJS .= "
                 if (recupMontant(document.ADForm.reste.value) != recupMontant(document.ADForm.conf_reste.value))
                 {
                 msg += '- "._("Le montant du reste saisi ne correspond pas au montant du reste")."\\n';
                 ADFormValid=false;
               };
                 ";
    } else {
      $ChkJS = "
               if (recupMontant(document.ADForm.mnt_reel.value) != recupMontant(document.ADForm.mnt.value))
             {
               msg += '- "._("Le montant saisi ne correspond pas au montant à retirer")."\\n';
               ADFormValid=false;
             };
               ";
    }
    $html->addJS(JSP_BEGIN_CHECK, "JS1",$ChkJS);

    //Boutons
    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
    $html->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $SESSION_VARS['envoi'] = 0;
    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Rva-4');
    $html->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-16');

    $html->buildHTML();
    echo $html->getHTML();
  } else {
    $html_err = new HTML_erreur($charTitle);
    $html_err->setMessage($message);
    $html_err->addButton("BUTTON_OK", "Gen-16");
    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }
}
else if ($global_nom_ecran == "Rva-4"){
    global $global_monnaie_courante, $global_id_guichet;
  $hasBilletageRecu = false;
  if (isset($SESSION_VARS["change"])) {
    $CHANGE = $SESSION_VARS['change'];
    $hasBilletageRecu = false;
    $hasBilletageChange = true;
  }

  // récupérer le infos sur le produit associé au compte sélectionné
  $InfoCpte = getAccountDatas($SESSION_VARS["id_cpt"]);
  $InfoProduit = getProdEpargne($InfoCpte["id_prod"]);
  if (isset($SESSION_VARS['Frais'])) $InfoProduit['frais_retrait_cpt']=$SESSION_VARS['Frais'];

  $data_cheque=array();
  /*if ($SESSION_VARS['type_retrait']>1) {
    $data_cheque["num_piece"] 	= $SESSION_VARS["num_chq"];
    $data_cheque["date_piece"] 	= $SESSION_VARS["date_chq"];
    $data_cheque["id_ext_benef"]	= $SESSION_VARS["id_ben"];
    $data_cheque["type_piece"]	= $SESSION_VARS["type_retrait"];
    if ($SESSION_VARS['type_retrait']==4 || $SESSION_VARS['type_retrait']==5)
      $data_cheque['date_piece']=date("d/m/Y");

    if ($data_cheque["type_piece"] == 2) // Il faut distinguer leschèques extérieurs et internes. Dans ce cas-ci, il s'agit d'un chèque guichet
      $data_cheque["type_piece"] = 15;
    if($SESSION_VARS['type_retrait'] == 8 || $SESSION_VARS['type_retrait'] == 15) {
      $dataBef = $SESSION_VARS['tib'];
    }
  }*/

  $data_cheque["communication"] = $SESSION_VARS["communication"];
  $data_cheque["remarque"] = $SESSION_VARS["remarque"];
  $data_cheque["sens"]	= "out";
  $data_cheque['id_pers_ext'] = $SESSION_VARS['id_pers_ext'];

  if(isset($CHANGE)) {
    $SESSION_VARS["mnt"] = recupMontant($SESSION_VARS["mnt"]);
  }
  else {
    if (!isset($SESSION_VARS["mnt"])){ //ajout frais de non respect de la duree minimum entre 2 retraits
      $SESSION_VARS["mnt"] = recupMontant($mnt_reel);
    }
  }

  if (isset($SESSION_VARS['commission_agent']) && $SESSION_VARS['commission_agent'] !=''){
    $comm_agent = $SESSION_VARS['commission_agent'];
  }
  if (isset($SESSION_VARS['commission_institution']) && $SESSION_VARS['commission_institution'] !=''){
    $comm_inst = $SESSION_VARS['commission_institution'];
  }

  //mouvement des comptes avec gestion des frais d'opérations sur compte s'il y lieu
  $erreur = retrait_cpte_agency($global_id_guichet, $SESSION_VARS["id_cpt"], $InfoProduit, $InfoCpte, $SESSION_VARS["mnt"], $SESSION_VARS['type_retrait'], $SESSION_VARS['id_mandat'], $data_cheque, $CHANGE,null, $SESSION_VARS['ERR_DUREE_MIN_RETRAIT'],$comm_agent,$comm_inst);

  //Affichage des reçus.
  if ($erreur->errCode == NO_ERR) {

    $infos = get_compte_epargne_info($SESSION_VARS['id_cpt']);

    setMonnaieCourante($InfoProduit['devise']); //Pour etre sûr ke ce la devise du Produit

    ($isbilletage == 'f') ? $hasBilletageChange = false : $hasBilletageChange = true;

    $data_client = getClient($SESSION_VARS["id_cpt"]);
    switch ($data_client['statut_juridique']) {
      case 1 :
        $nom = $data_client['pp_nom'] . " " . $data_client['pp_prenom'];
        break;
      case 2 :
        $nom = $data_client['pm_raison_sociale'];
        break;
      case 3 :
        $nom = $data_client['gi_nom'];
        break;
      case 4 :
        $nom = $data_client['gi_nom'];
        break;
      default :
        signalErreur(__FILE__, __LINE__, __FUNCTION__); // "Statut juridique inconnu !"
    }

    print_recu_retrait_agent($data_client['id_client'], $nom, $InfoProduit, $infos, $SESSION_VARS['mnt'], $erreur->param['id'], 'REC-RRA',$SESSION_VARS['id_mandat'], $SESSION_VARS["remarque"], $SESSION_VARS["communication"], $SESSION_VARS['id_pers_ext'],NULL,$SESSION_VARS['denomination_conj'], $listTypesBilletArr, $valeurBilletArr, $global_langue_utilisateur, $total_billetArr, $hasBilletageRecu, $isbilletage,$SESSION_VARS['ERR_DUREE_MIN_RETRAIT'],$comm_agent,$comm_inst);

    // Imprime le reçu de change s'il y a lieu
    if (isset($CHANGE)) {
      $cpteSource = getAccountDatas($SESSION_VARS['NumCpte']);

      $SESSION_VARS["recu_change"]["source_achat"] = $cpteSource["num_complet_cpte"];
      $SESSION_VARS["recu_change"]["dest_vente"] = $dest_change;


      printRecuChange ($erreur->param['id'], $SESSION_VARS["mnt"], $cpteSource["devise"], $SESSION_VARS["recu_change"]["source_achat"], $SESSION_VARS["change"]["cv"], $SESSION_VARS["change"]["devise"], $SESSION_VARS["change"]["comm_nette"],$SESSION_VARS["change"]["taux"],$SESSION_VARS["change"]["reste"],$SESSION_VARS["recu_change"]["dest_vente"],$SESSION_VARS["change"]["dest_reste"],$SESSION_VARS["envoi_reste"], $listTypesBilletArr, $valeurBilletArr, $global_langue_rapport, $total_billetArr, $hasBilletageChange);
    }

    // Mise à jour du bénéficiaire
    if (isset($SESSION_VARS['id_ben']) && ($SESSION_VARS['id_ben']!=NULL))
      $myError = setBeneficiaire($SESSION_VARS['id_ben']);
    if ($myError->errCode == NO_ERR)
      $majBenef = TRUE;

    //Affichage de la confirmation
    $html_msg =new HTML_message(_("Confirmation du retrait"));
    setMonnaieCourante($infos['devise']);
    $fraisDureeMinEntreRetrait = 0; // ticket 805
    if(isset($SESSION_VARS['ERR_DUREE_MIN_RETRAIT']) && $SESSION_VARS['ERR_DUREE_MIN_RETRAIT'] == 't' && $InfoProduit['frais_duree_min2retrait'] > 0){ // ticket 805 : ajout frais de non respect de la duree minimum entre 2 retraits
      $fraisDureeMinEntreRetrait = $InfoProduit['frais_duree_min2retrait'];
    }
    $mntDebit=$SESSION_VARS['mnt']+$InfoProduit['frais_retrait_cpt']+$fraisDureeMinEntreRetrait; // ticket 805 : ajout frais de non respect de la duree minimum entre 2 retraits

    if (isset($comm_agent) && $comm_agent >0){
      $mntDebit += $comm_agent;
    }
    if (isset($comm_inst) && $comm_inst >0){
      $mntDebit += $comm_inst;
    }
    $message="
             <table><tr><td>"._("Montant débité du compte")." : </td>
             <td>".afficheMontant($mntDebit,true)."</td>
             </tr>
             <tr><td>"._("Commission pour agent")." : </td>
             <td>".afficheMontant($comm_agent,true)."</td>
             </tr>
             <tr><td>"._("Commission pour institution")." : </td>
             <td>".afficheMontant($comm_inst,true)."</td>
             </tr>
             <tr><td>"._("Frais de non respect de la duree minimum entre deux retraits")." : </td>
             <td>".afficheMontant($fraisDureeMinEntreRetrait,true)."</td>
             </tr>";
    $mntGuichet=$SESSION_VARS['mnt'];
    if (isset($CHANGE)) {
      setMonnaieCourante($CHANGE['devise']);
      $mntGuichet=$SESSION_VARS['change']['cv'];
    }
    $message.="
              <tr><td>"._("Remis au client")." : </td>
              <td>".afficheMontant($mntGuichet, true)."</td>
              </tr>";
    if ($CHANGE['reste']>0) {
      setMonnaieCourante($global_monnaie);
      $message.="
                <tr><td>"._("Liquidié en devise de référence")."</td>
                <td>".afficheMontant($CHANGE['reste'], true)."</td>";
    }
    $message.="
              </table>
              <br />
              "._("Le reçu a été imprimé")."
              <br />";
    if (isset($SESSION_VARS['id_ben'])) {
      debug($majBenef,"majbenef est ");
      if ($majBenef== TRUE) $message.="<br />"._("Bénéficiaire mis à jour")." <br />";
      else $message.="<br />"._("Bénéficiaire non mis à jour")." <br />";
    }

    $message .= "<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>";

    $html_msg->setMessage($message);

    $html_msg->addButton("BUTTON_OK", 'Gen-16');
    $html_msg->buildHTML();
    echo $html_msg->HTML_code;
  } else {
    debug($erreur->param);
    if ($erreur->errCode == ERR_DUREE_MIN_RETRAIT){ // Ticket 805 : ajout frais de non respect de la duree minimum entre 2 retraits
      $SESSION_VARS['ecran_prec'] = 'Rcp-4';
      $SESSION_VARS['ERR_DUREE_MIN_RETRAIT'] = 't';
      $html_err = new HTML_erreur(_("Retrait sur un compte.")." ");
      $html_err->setMessage(_("ATTENTION")." : ".$error[$erreur->errCode]."<br />"._("Paramètre Numero Compte Client : ")." : ".$erreur->param." <br /> Mais si vous voulez continuer le retrait, sachez que les frais de non respect de la durée minimum entre deux retraits seront prelevés sur le compte du client; alors veuillez cliquer sur le bouton 'OK' pour continuer sinon le bouton 'annuler'!");
      $html_err->addButton("BUTTON_CANCEL", 'Rva-1');
      $html_err->addButton("BUTTON_OK", 'Rva-4');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
    else{
      unset($SESSION_VARS['ERR_DUREE_MIN_RETRAIT']);
      unset($SESSION_VARS['ecran_prec']);
      $html_err = new HTML_erreur(_("Echec du retrait sur un compte.")." ");
      $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]."<br />"._("Paramètre : ")." : ".$erreur->param);
      $html_err->addButton("BUTTON_OK", 'Rva-1');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }
}