<?php
require_once 'modules/rapports/xml_agence.php';
require_once 'modules/rapports/xslt.php';
require_once 'lib/misc/csv.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/dbProcedures/engrais_chimiques.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/misc/divers.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/dbProcedures/rapports.php';
require_once 'modules/rapports/xml_engrais_chimiques.php';
require_once 'ad_ma/app/models/Globalisation.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/controllers/misc/class.db.oo.php';
require_once 'lib/misc/excel.php';
//require_once 'lib/java/scp.php';

if ($global_nom_ecran == "Pnr-1") {

  unset($SESSION_VARS['contenu']);
  $MyPage = new HTML_GEN2(_("Liste des rapports:"));
  if (isEngraisChimiques() && isCompensationSiege() && isCurrentAgenceSiege()){
    $array_menu_rapport = array(
      'glob_act'=>_("Globalisation des transactions PNSEB Annee Culturale Courante"),
      'glob_his'=>_("Globalisation des transactions PNSEB Annee Culturale Historique")
    );
  }
  else{
    $array_menu_rapport = array(
      'sit_pai'=>_("Situation des paiements / commandes"),
      'lis_ben'=>_("Liste des beneficiaires ayant payes"),
      'qti_zon'=>_("Repartition des quantites selon les zones"),//isEngraisChimiques() isCompensationSiege() && isCurrentAgenceSiege()
      'lis_aut'=>_("Liste des beneficiaires ayant eu une autorisation de depassement de plafond")
    );
  }
  $MyPage->addField("contenu", _("Liste des rapports"), TYPC_LSB);
  $MyPage->setFieldProperties("contenu", FIELDP_HAS_CHOICE_AUCUN,true);
  $MyPage->setFieldProperties("contenu", FIELDP_ADD_CHOICES, $array_menu_rapport);
  $MyPage->setFieldProperties("contenu", FIELDP_IS_REQUIRED, true);

  //Bouton formulaire
  $MyPage->addButton("contenu", "butparam", _("Parametrer"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Pnr-2");
  $MyPage->setButtonProperties("butparam", BUTP_AXS, 179);


  $MyPage->addFormButton(1,1, "butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-1");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}

else if ($global_nom_ecran == "Pnr-2") {
  if ($contenu == 'sit_pai' || $SESSION_VARS['contenu']=='sit_pai'){
    $Myform = new HTML_GEN2(_("Les parametres"));
    $list_annee_agri = getListeAnneeAgricoleActif();

    if (!isset($_POST['id_annee'])) {
      $Myform->addField("id_annee", _("Annee agricole"), TYPC_LSB);
      $Myform->setFieldProperties("id_annee", FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties('id_annee', FIELDP_ADD_CHOICES, $list_annee_agri);
      $SESSION_VARS['contenu']= $contenu;

      $Myform->addFormButton(1,1, "butparam", _("Parametrer"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Pnr-2");


    }
    else if ((isset($_POST['id_annee'])) && (isset($SESSION_VARS['contenu'])=='sit_pai')) {
      $SESSION_VARS['id_annee_selected'] = $id_annee;
      $list_saison_cultu = getListeSaisonPNSEB("id_annee=".$id_annee);
      $Myform->addField("id_saison", _("Saison culturale"), TYPC_LSB);
      $Myform->setFieldProperties("id_saison", FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties('id_saison', FIELDP_ADD_CHOICES, $list_saison_cultu);

      $Myform->addField("choix_period", _("Choix de la periode"), TYPC_LSB);
      $Myform->setFieldProperties('choix_period', FIELDP_ADD_CHOICES, $adsys["adsys_choix_periode"]);
      $Myform->setFieldProperties("choix_period", FIELDP_HAS_CHOICE_TOUS, false);
      $Myform->setFieldProperties("choix_period", FIELDP_HAS_CHOICE_AUCUN, true);
      $Myform->setFieldProperties("choix_period", FIELDP_IS_REQUIRED, true);

      $Myform->addFormButton(1,1, "butparam", _("Parametrer"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Pnr-3");

    }

    $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
    $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
    $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-1");



    $Myform->buildHTML();
    echo $Myform->getHTML();
  }
  if ($contenu == 'lis_ben' || (isset($SESSION_VARS['contenu']) && $SESSION_VARS['contenu'] == 'lis_ben')){
    $MyPage = new HTML_GEN2(_("Personalisation du Rapport Liste des beneficiaires ayant payes"));

    $MyPage->addField("annee", _("Annee Agricole"), TYPC_LSB);
    $MyPage->setFieldProperties("annee", FIELDP_HAS_CHOICE_AUCUN,true);
    if (!isset($annee) && $annee == null){
      $MyPage->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
      $liste_AnneeAgricolePNSEB=getListeAnneeAgricolePNSEB();
      //Trier par ordre alphabetique
      natcasesort($liste_AnneeAgricolePNSEB);
      if (sizeof($liste_AnneeAgricolePNSEB)>0)
        $MyPage->setFieldProperties("annee", FIELDP_ADD_CHOICES, $liste_AnneeAgricolePNSEB);
    }
    $codejsROnly = "";
    //$codejsROnly.= "function setReadOnly() {\n";
    $codejsROnly.= " if (document.ADForm.HTML_GEN_date_date_debut && document.ADForm.HTML_GEN_date_date_fin){\n";
    $codejsROnly.= "    document.ADForm.HTML_GEN_date_date_debut.readOnly = true;\n";
    $codejsROnly.= "    document.ADForm.HTML_GEN_date_date_fin.readOnly = true;\n";
    $codejsROnly.= " }";
    //$codejsROnly.= "}";
    $MyPage->addJS(JSP_FORM, "JS_ReadOnly", $codejsROnly);
    $MyPage->setFieldProperties("annee", FIELDP_JS_EVENT, array("onChange"=>"assign('Pnr-2'); this.form.submit();"));

    if (isset($annee) && $annee != null){
      $MyPage->setFieldProperties("annee", FIELDP_HAS_CHOICE_AUCUN,false);
      $MyPage->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
      $AnneeAgricolePNSEB=getListeAnneeAgricolePNSEB("id_annee = ".$annee);
      //Trier par ordre alphabétique
      natcasesort($AnneeAgricolePNSEB);
      if (sizeof($AnneeAgricolePNSEB)>0)
        $MyPage->setFieldProperties("annee", FIELDP_ADD_CHOICES, $AnneeAgricolePNSEB);
      $MyPage->setFieldProperties("annee", FIELDP_DEFAULT, $annee);
      $MyPage->setFieldProperties("annee", FIELDP_CAN_MODIFY, false);
      $MyPage->addField("saison", _("Saison"), TYPC_LSB);
      $MyPage->setFieldProperties("saison", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties("saison", FIELDP_HAS_CHOICE_TOUS,false);
      if (!isset($saison) && $saison == null){
        $MyPage->setFieldProperties("saison", FIELDP_IS_REQUIRED, true);
        $where = "id_annee = ".$annee;
        $liste_SaisonPNSEB=getListeSaisonPNSEB($where);
        //Trier par ordre alphabetique
        natcasesort($liste_SaisonPNSEB);
        if (sizeof($liste_SaisonPNSEB)>0){
          $MyPage->setFieldProperties("saison", FIELDP_ADD_CHOICES, $liste_SaisonPNSEB);
        }
      }
      else{
        $MyPage->setFieldProperties("saison", FIELDP_IS_REQUIRED, true);
        $where = "id_annee = ".$annee." AND id_saison = ".$saison;
        $SaisonPNSEB=getListeSaisonPNSEB($where);
        //Trier par ordre alphabétique
        natcasesort($SaisonPNSEB);
        if (sizeof($SaisonPNSEB)>0)
          $MyPage->setFieldProperties("saison", FIELDP_ADD_CHOICES, $SaisonPNSEB);
      }
      $MyPage->addField("periode", _("Choix de la Periode"), TYPC_LSB);
      $MyPage->setFieldProperties("periode", FIELDP_HAS_CHOICE_AUCUN,false);
      $MyPage->setFieldProperties("periode", FIELDP_HAS_CHOICE_TOUS,true);
      $MyPage->setFieldProperties('periode', FIELDP_ADD_CHOICES, $adsys["adsys_choix_periode"]);
      //$MyPage->setFieldProperties("periode", FIELDP_IS_REQUIRED, true);
      $MyPage->setFieldProperties("periode", FIELDP_JS_EVENT, array("onChange"=>"setDateAvanceSolde();"));
      $MyPage->setFieldProperties("saison", FIELDP_JS_EVENT, array("onChange"=>"setDateAvanceSolde();"));
      $MyPage->addField("date_debut", _("Date Debut"), TYPC_DTG);
      $MyPage->setFieldProperties("date_debut", FIELDP_HAS_CALEND, false);
      $MyPage->setFieldProperties("date_debut", FIELDP_CAN_MODIFY, false);
      $MyPage->addField("date_fin", _("Date Fin"), TYPC_DTG);
      $MyPage->setFieldProperties("date_fin", FIELDP_HAS_CALEND, false);
      $MyPage->setFieldProperties("date_debut", FIELDP_CAN_MODIFY, false);

      //Javascript check pour date debut et date fin si ils sont dans les limites des dates debut/fin des periodes avance/solde respectivement
      $codejs = "";
      $codejs.= " function setDateAvanceSolde() {";
      $codejs.= " var _cQueueDates = [];
            var valueToPush = {}; ";
      $where = "id_annee = ".$annee;
      $valueToPush = getListeSaisonCultuDetails($where);
      $arrCount = 0;
      $date_debut_default = '';
      $date_fin_default = '';
      while (list($key, $value) = each($valueToPush)) {
        $date_debut_avance1 = pg2phpDatebis($value['date_debut']);
        $date_debut_avance = date("d/m/Y",mktime(0, 0, 0, (int)$date_debut_avance1[0], (double)$date_debut_avance1[1], (double)$date_debut_avance1[2]));
        if(empty($value['date_debut'])) {
          $date_debut_avance = date("d")."/".date("m")."/".date("Y");//date("Y")."-".date("m")."-".date("d");
        }
        if ($arrCount == 0) {
          $date_debut_default = $date_debut_avance; //pour parametre saison tous
        }
        $date_fin_avance1 = pg2phpDatebis($value['date_fin_avance']);
        $date_fin_avance = date("d/m/Y",mktime(0, 0, 0, (int)$date_fin_avance1[0], (double)$date_fin_avance1[1], (double)$date_fin_avance1[2]));
        if(empty($value['date_fin_avance'])) {
          $date_fin_avance = ifSameMonthGetDate($date_debut_avance1);//date("Y")."-".date("m")."-".date("d");
        }
        $date_debut_solde1 = pg2phpDatebis($value['date_debut_solde']);
        $date_debut_solde = date("d/m/Y",mktime(0, 0, 0, (int)$date_debut_solde1[0], (double)$date_debut_solde1[1], (double)$date_debut_solde1[2]));
        if(empty($value['date_debut_solde'])) {
          $date_debut_solde = ifSameMonthGetDate($date_fin_avance1);//date("Y")."-".date("m")."-".date("d");
        }
        $date_fin_solde = pg2phpDatebis($value['date_fin_solde']);
        $date_fin_solde = date("d/m/Y",mktime(0, 0, 0, (int)$date_fin_solde[0], (double)$date_fin_solde[1], (double)$date_fin_solde[2]));
        if(empty($value['date_fin_solde'])) {
          $date_fin_solde = ifSameMonthGetDate($date_debut_solde1);//date("Y")."-".date("m")."-".date("d");
        }
        $codejs.=" valueToPush['_id'+".$arrCount."] = ".$value['id_saison'].";"; //'".$key."
        $codejs.=" valueToPush['_avance_debut'+".$arrCount."] = '".$date_debut_avance."';";
        $codejs.=" valueToPush['_avance_fin'+".$arrCount."] = '".$date_fin_avance."';";
        $codejs.=" valueToPush['_solde_debut'+".$arrCount."] = '".$date_debut_solde."';";
        $codejs.=" valueToPush['_solde_fin'+".$arrCount."] = '".$date_fin_solde."';";
        $codejs.=" _cQueueDates.push(valueToPush);";
        $date_fin_default = $date_fin_solde; //pour parametre saison tous
        $arrCount++;
      }
      //pour parametre saison tous
      $MyPage->setFieldProperties("date_debut", FIELDP_DEFAULT, $date_debut_default);
      $MyPage->setFieldProperties("date_fin", FIELDP_DEFAULT, $date_fin_default);

      $codejs.=" var obj = {};
            for (var i=0; i<_cQueueDates.length; i++) { // iterate on the array
              obj['_id'+i] = _cQueueDates[i]['_id'+i];
              if (obj['_id'+i] == document.ADForm.HTML_GEN_LSB_saison.value && document.ADForm.HTML_GEN_LSB_periode.value == 1){
                document.ADForm.HTML_GEN_date_date_debut.value = _cQueueDates[i]['_avance_debut'+i];
                document.ADForm.HTML_GEN_date_date_debut.readOnly = true;
                document.ADForm.HTML_GEN_date_date_fin.value = _cQueueDates[i]['_avance_fin'+i];
                document.ADForm.HTML_GEN_date_date_fin.readOnly = true;
              }
              if (obj['_id'+i] == document.ADForm.HTML_GEN_LSB_saison.value && document.ADForm.HTML_GEN_LSB_periode.value == 2){
                document.ADForm.HTML_GEN_date_date_debut.value = _cQueueDates[i]['_solde_debut'+i];
                document.ADForm.HTML_GEN_date_date_debut.readOnly = true;
                document.ADForm.HTML_GEN_date_date_fin.value = _cQueueDates[i]['_solde_fin'+i];
                document.ADForm.HTML_GEN_date_date_fin.readOnly = true;
              }
              if (obj['_id'+i] == document.ADForm.HTML_GEN_LSB_saison.value && document.ADForm.HTML_GEN_LSB_periode.value == 0){
                document.ADForm.HTML_GEN_date_date_debut.value = _cQueueDates[i]['_avance_debut'+i];
                document.ADForm.HTML_GEN_date_date_debut.readOnly = true;
                document.ADForm.HTML_GEN_date_date_fin.value = _cQueueDates[i]['_solde_fin'+i];
                document.ADForm.HTML_GEN_date_date_fin.readOnly = true;
              }
              if (document.ADForm.HTML_GEN_LSB_saison.value == 0){
                document.ADForm.HTML_GEN_date_date_debut.value = _cQueueDates[0]['_avance_debut'+0];
                document.ADForm.HTML_GEN_date_date_debut.readOnly = true;
                document.ADForm.HTML_GEN_date_date_fin.value = _cQueueDates[($arrCount-1)]['_solde_fin'+($arrCount-1)];
                document.ADForm.HTML_GEN_date_date_fin.readOnly = true;
                document.ADForm.HTML_GEN_LSB_periode.value = 0;
              }
            }";
      $codejs.="}";

      //Boutons
      $MyPage->addFormButton(1, 1, "pdf_lis_ben", _("Rapport PDF"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("pdf_lis_ben", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $MyPage->addFormButton(1, 2, "excel_lis_ben", _("Export EXCEL"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("excel_lis_ben", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $MyPage->addFormButton(1, 3, "csv_lis_ben", _("Export CSV"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("csv_lis_ben", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Pnr-1");
      $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

      $MyPage->addJS(JSP_FORM, "JS1", $codejs);

    }

    if (!isset($SESSION_VARS['contenu'])){
      $SESSION_VARS['contenu'] = $contenu;
    }

    $MyPage->buildHTML();
    echo $MyPage->getHTML();
  }
  if ($contenu == 'qti_zon' || (isset($SESSION_VARS['contenu']) && $SESSION_VARS['contenu'] == 'qti_zon')){
    $MyPage = new HTML_GEN2(_("Personalisation du Rapport Liste des quantités selon les zones"));

    $MyPage->addField("annee", _("Annee Agricole"), TYPC_LSB);
    $MyPage->setFieldProperties("annee", FIELDP_HAS_CHOICE_AUCUN,true);
    if (!isset($annee) && $annee == null){
      $MyPage->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
      $liste_AnneeAgricolePNSEB=getListeAnneeAgricolePNSEB();
      //Trier par ordre alphabetique
      natcasesort($liste_AnneeAgricolePNSEB);
      if (sizeof($liste_AnneeAgricolePNSEB)>0)
        $MyPage->setFieldProperties("annee", FIELDP_ADD_CHOICES, $liste_AnneeAgricolePNSEB);
    }
    $codejsROnly = "";
    //$codejsROnly.= "function setReadOnly() {\n";
    $codejsROnly.= " if (document.ADForm.HTML_GEN_date_date_debut && document.ADForm.HTML_GEN_date_date_fin){\n";
    $codejsROnly.= "    document.ADForm.HTML_GEN_date_date_debut.readOnly = true;\n";
    $codejsROnly.= "    document.ADForm.HTML_GEN_date_date_fin.readOnly = true;\n";
    $codejsROnly.= " }";
    //$codejsROnly.= "}";
    $MyPage->addJS(JSP_FORM, "JS_ReadOnly", $codejsROnly);
    $MyPage->setFieldProperties("annee", FIELDP_JS_EVENT, array("onChange"=>"assign('Pnr-2'); this.form.submit();"));

    if (isset($annee) && $annee != null){
      $MyPage->setFieldProperties("annee", FIELDP_HAS_CHOICE_AUCUN,false);
      $MyPage->setFieldProperties("annee", FIELDP_IS_REQUIRED, true);
      $AnneeAgricolePNSEB=getListeAnneeAgricolePNSEB("id_annee = ".$annee);
      //Trier par ordre alphabétique
      natcasesort($AnneeAgricolePNSEB);
      if (sizeof($AnneeAgricolePNSEB)>0)
        $MyPage->setFieldProperties("annee", FIELDP_ADD_CHOICES, $AnneeAgricolePNSEB);
      $MyPage->setFieldProperties("annee", FIELDP_DEFAULT, $annee);
      $MyPage->setFieldProperties("annee", FIELDP_CAN_MODIFY, false);
      $MyPage->addField("saison", _("Saison"), TYPC_LSB);
      $MyPage->setFieldProperties("saison", FIELDP_HAS_CHOICE_AUCUN,true);
      $MyPage->setFieldProperties("saison", FIELDP_HAS_CHOICE_TOUS,false);
      $MyPage->setFieldProperties("saison", FIELDP_IS_REQUIRED,true);
      if (!isset($saison) && $saison == null){
        //$MyPage->setFieldProperties("saison", FIELDP_IS_REQUIRED, true);
        $where = "id_annee = ".$annee;
        $liste_SaisonPNSEB=getListeSaisonPNSEB($where);
        //Trier par ordre alphabetique
        natcasesort($liste_SaisonPNSEB);
        if (sizeof($liste_SaisonPNSEB)>0){
          $MyPage->setFieldProperties("saison", FIELDP_ADD_CHOICES, $liste_SaisonPNSEB);
        }
      }
      else{
        //$MyPage->setFieldProperties("saison", FIELDP_IS_REQUIRED, true);
        $where = "id_annee = ".$annee." AND id_saison = ".$saison;
        $SaisonPNSEB=getListeSaisonPNSEB($where);
        //Trier par ordre alphabétique
        natcasesort($SaisonPNSEB);
        if (sizeof($SaisonPNSEB)>0)
          $MyPage->setFieldProperties("saison", FIELDP_ADD_CHOICES, $SaisonPNSEB);
      }

      //Boutons
      $MyPage->addFormButton(1, 1, "pdf_qti_zon", _("Rapport PDF"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("pdf_qti_zon", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $MyPage->addFormButton(1, 2, "excel_qti_zon", _("Export EXCEL"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("excel_qti_zon", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $MyPage->addFormButton(1, 3, "csv_qti_zon", _("Export CSV"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("csv_qti_zon", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
      $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Pnr-1");
      $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

      $MyPage->addJS(JSP_FORM, "JS1", $codejs);

    }

    if (!isset($SESSION_VARS['contenu'])){
      $SESSION_VARS['contenu'] = $contenu;
    }

    $MyPage->buildHTML();
    echo $MyPage->getHTML();

  }
  if ($contenu == 'lis_aut' || $SESSION_VARS['contenu']=='lis_aut'){
    $Myform = new HTML_GEN2(_("Les parametres"));
    $list_annee_agri = getListeAnneeAgricoleActif();

    if (!isset($_POST['id_annee'])) {
      $Myform->addField("id_annee", _("Annee agricole"), TYPC_LSB);
      $Myform->setFieldProperties("id_annee", FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties('id_annee', FIELDP_ADD_CHOICES, $list_annee_agri);
      $SESSION_VARS['contenu']= $contenu;

      $Myform->addFormButton(1,1, "butparam", _("Parametrer"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Pnr-2");

      $Myform->addFormButton(1,2, "butret", _("Retour"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
      $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-1");

    }
    else if ((isset($_POST['id_annee'])) && (isset($SESSION_VARS['contenu'])=='lis_aut')) {
      $SESSION_VARS['id_annee_selected'] = $id_annee;
      $list_saison_cultu = getListeSaisonPNSEB("id_annee=".$id_annee);
      $Myform->addField("id_saison", _("Saison culturale"), TYPC_LSB);
      $Myform->setFieldProperties('id_saison', FIELDP_ADD_CHOICES, $list_saison_cultu);
      $Myform->setFieldProperties("id_saison", FIELDP_HAS_CHOICE_TOUS, true);
      $Myform->setFieldProperties("id_saison", FIELDP_HAS_CHOICE_AUCUN, false);

      $Myform->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $Myform->addFormButton(1, 2, "csv", _("Export CSV"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Pnr-3");

      $Myform->addFormButton(1, 3, "butret", _("Retour"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
      $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-1");

    }
    $Myform->buildHTML();
    echo $Myform->getHTML();
  }
  if ($contenu == 'glob_act' || (isset($SESSION_VARS['contenu']) && $SESSION_VARS['contenu'] == 'glob_act')){
    $MyPage = new HTML_GEN2(_("Rapport Globalisation de l'Annee Culturale Courante"));

    $MyPage->addField("sit_pai_glob", _("Situation Paiement Globale"), TYPC_BOL);
    $MyPage->setFieldProperties("sit_pai_glob", FIELDP_DEFAULT, true);
    $MyPage->addField("lis_ben_glob", _("Liste Beneficiare ayant payé Globale"), TYPC_BOL);
    $MyPage->setFieldProperties("lis_ben_glob", FIELDP_DEFAULT, true);
    $MyPage->addField("rpt_zon_glob", _("Repartition par zone Globale"), TYPC_BOL);
    $MyPage->setFieldProperties("rpt_zon_glob", FIELDP_DEFAULT, true);

    $codeJsCSV = "";
    $codeJsCSV .= "function fieldCheckCSV(){\n";
    $codeJsCSV .= "\tvar check = 0;\n";
    $codeJsCSV .= "\tif (document.ADForm.HTML_GEN_BOL_sit_pai_glob.checked == true){\n";
    $codeJsCSV .= "\t\tcheck = check + 1;\n";
    $codeJsCSV .= "\t}\n";
    $codeJsCSV .= "\tif (document.ADForm.HTML_GEN_BOL_lis_ben_glob.checked == true){\n";
    $codeJsCSV .= "\t\tcheck = check + 1;\n";
    $codeJsCSV .= "\t}\n";
    $codeJsCSV .= "\tif (document.ADForm.HTML_GEN_BOL_rpt_zon_glob.checked == true){\n";
    $codeJsCSV .= "\t\tcheck = check + 1;\n";
    $codeJsCSV .= "\t}\n";
    $codeJsCSV .= "\tif (check > 1){\n";
    $codeJsCSV .= "\t\tADFormValid = false;\n";
    $codeJsCSV .= "\t\talert('Veuillez cocher une seule case pour la sortie du Rapport en CSV!!');\n";
    $codeJsCSV .= "\t\texit;\n";
    $codeJsCSV .= "\t}\n";
    $codeJsCSV .= "\tif (check == 0){\n";
    $codeJsCSV .= "\t\tADFormValid = false;\n";
    $codeJsCSV .= "\t\talert('Veuillez cocher une case pour la sortie du Rapport en CSV!!');\n";
    $codeJsCSV .= "\t\texit;\n";
    $codeJsCSV .= "\t}\n";
    $codeJsCSV .= "";
    $codeJsCSV .= "}\n";

    $codeJsPDF = "";
    $codeJsPDF .= "function fieldCheckPDF(){\n";
    $codeJsPDF .= "\tvar check = 0;\n";
    $codeJsPDF .= "\tif (document.ADForm.HTML_GEN_BOL_sit_pai_glob.checked == true){\n";
    $codeJsPDF .= "\t\tcheck = check + 1;\n";
    $codeJsPDF .= "\t}\n";
    $codeJsPDF .= "\tif (document.ADForm.HTML_GEN_BOL_lis_ben_glob.checked == true){\n";
    $codeJsPDF .= "\t\tcheck = check + 1;\n";
    $codeJsPDF .= "\t}\n";
    $codeJsPDF .= "\tif (document.ADForm.HTML_GEN_BOL_rpt_zon_glob.checked == true){\n";
    $codeJsPDF .= "\t\tcheck = check + 1;\n";
    $codeJsPDF .= "\t}\n";
    $codeJsPDF .= "\tif (check == 0){\n";
    $codeJsPDF .= "\t\tADFormValid = false;\n";
    $codeJsPDF .= "\t\talert('Veuillez cocher au moin une case pour la sortie du Rapport en PDF!!');\n";
    $codeJsPDF .= "\t\texit;\n";
    $codeJsPDF .= "\t}\n";
    $codeJsPDF .= "";
    $codeJsPDF .= "}\n";

    $codeJsExcel = "";
    $codeJsExcel .= "function fieldCheckExcel(){\n";
    $codeJsExcel .= "\tvar check = 0;\n";
    $codeJsExcel .= "\tif (document.ADForm.HTML_GEN_BOL_sit_pai_glob.checked == true){\n";
    $codeJsExcel .= "\t\tcheck = check + 1;\n";
    $codeJsExcel .= "\t}\n";
    $codeJsExcel .= "\tif (document.ADForm.HTML_GEN_BOL_lis_ben_glob.checked == true){\n";
    $codeJsExcel .= "\t\tcheck = check + 1;\n";
    $codeJsExcel .= "\t}\n";
    $codeJsExcel .= "\tif (document.ADForm.HTML_GEN_BOL_rpt_zon_glob.checked == true){\n";
    $codeJsExcel .= "\t\tcheck = check + 1;\n";
    $codeJsExcel .= "\t}\n";
    $codeJsExcel .= "\tif (check > 1){\n";
    $codeJsExcel .= "\t\tADFormValid = false;\n";
    $codeJsExcel .= "\t\talert('Veuillez cocher une seule case pour la sortie du Rapport en Excel!!');\n";
    $codeJsExcel .= "\t\texit;\n";
    $codeJsExcel .= "\t}\n";
    $codeJsExcel .= "\tif (check == 0){\n";
    $codeJsExcel .= "\t\tADFormValid = false;\n";
    $codeJsExcel .= "\t\talert('Veuillez cocher au moin une case pour la sortie du Rapport en Excel!!');\n";
    $codeJsExcel .= "\t\texit;\n";
    $codeJsExcel .= "\t}\n";
    $codeJsExcel .= "";
    $codeJsExcel .= "}\n";

    $MyPage->addJS(JSP_FORM, "codeJsCSV", $codeJsCSV);
    $MyPage->addJS(JSP_FORM, "codeJsPDF", $codeJsPDF);
    $MyPage->addJS(JSP_FORM, "codeJsExcel", $codeJsExcel);

    //Boutons
    $MyPage->addFormButton(1, 1, "pdf_glob_act", _("Rapport PDF"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("pdf_glob_act", BUTP_PROCHAIN_ECRAN, "Pnr-3");
    $MyPage->setFormButtonProperties("pdf_glob_act", BUTP_JS_EVENT, array("onClick"=>"fieldCheckPDF();"));
    $MyPage->addFormButton(1, 2, "csv_glob_act", _("Export CSV"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("csv_glob_act", BUTP_JS_EVENT, array("onClick"=>"fieldCheckCSV();"));
    $MyPage->addFormButton(1, 3, "excel_glob_act", _("Export Excel"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("excel_glob_act", BUTP_JS_EVENT, array("onClick"=>"fieldCheckExcel();"));
    //$MyPage->setFormButtonProperties("csv_glob_act", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("csv_glob_act", BUTP_PROCHAIN_ECRAN, "Pnr-3");
    $MyPage->setFormButtonProperties("excel_glob_act", BUTP_PROCHAIN_ECRAN, "Pnr-3");
    $MyPage->addFormButton(1, 4, "annuler", _("Annuler"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, "Pnr-1");
    $MyPage->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

    if (!isset($SESSION_VARS['contenu'])){
      $SESSION_VARS['contenu'] = $contenu;
    }

    $MyPage->buildHTML();
    echo $MyPage->getHTML();

  }
  if ($contenu == 'glob_his' || (isset($SESSION_VARS['contenu']) && $SESSION_VARS['contenu'] == 'glob_his')){
    $Myform = new HTML_GEN2(_("Les parametres"));
    $list_annee_agri = getListeAnneeAgricoleActif();

    if (!isset($_POST['id_annee'])) {
      $Myform->addField("id_annee", _("Annee agricole"), TYPC_LSB);
      $Myform->setFieldProperties("id_annee", FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("id_annee", FIELDP_HAS_CHOICE_AUCUN, false);
      $Myform->setFieldProperties('id_annee', FIELDP_ADD_CHOICES, $list_annee_agri);
      $SESSION_VARS['contenu']= $contenu;

      $Myform->addFormButton(1,1, "butparam", _("Parametrer"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("butparam", BUTP_PROCHAIN_ECRAN, "Pnr-2");

    }

    else if ((isset($_POST['id_annee'])) && (isset($SESSION_VARS['contenu'])=='glob_his')) {

      $SESSION_VARS['id_annee_selected'] = $id_annee;
      $list_saison_cultu = getListeSaisonPNSEB("id_annee=".$id_annee);
      $Myform->addField("id_saison", _("Saison culturale"), TYPC_LSB);
      $Myform->setFieldProperties("id_saison", FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("id_saison", FIELDP_HAS_CHOICE_AUCUN, false);
      $Myform->setFieldProperties('id_saison', FIELDP_ADD_CHOICES, $list_saison_cultu);

      $Myform->addField("choix_period", _("Choix de la periode"), TYPC_LSB);
      $Myform->setFieldProperties('choix_period', FIELDP_ADD_CHOICES, $adsys["adsys_choix_periode"]);
      $Myform->setFieldProperties("choix_period", FIELDP_HAS_CHOICE_TOUS, false);
      $Myform->setFieldProperties("choix_period", FIELDP_HAS_CHOICE_AUCUN, true);
      $Myform->setFieldProperties("choix_period", FIELDP_IS_REQUIRED, true);

      $Myform->setFieldProperties("choix_period", FIELDP_JS_EVENT, array("onChange"=>"setDateAvanceSolde();"));
      $Myform->setFieldProperties("id_saison", FIELDP_JS_EVENT, array("onChange"=>"setDateAvanceSolde();"));
      $Myform->addField("date_debut", _("Date Debut"), TYPC_DTG);
      $Myform->setFieldProperties("date_debut", FIELDP_HAS_CALEND, true);
      $Myform->setFieldProperties("date_debut", FIELDP_CAN_MODIFY, false);
      $Myform->addField("date_fin", _("Date Fin"), TYPC_DTG);
      $Myform->setFieldProperties("date_fin", FIELDP_HAS_CALEND, true);
      $Myform->setFieldProperties("date_debut", FIELDP_CAN_MODIFY, false);

      //Javascript check pour date debut et date fin si ils sont dans les limites des dates debut/fin des periodes avance/solde respectivement
      $codejs = "";
      $codejs.= " function setDateAvanceSolde() {";
      $codejs.= " var _cQueueDates = [];
            var valueToPush = {}; ";
      $where = "id_annee = ".$id_annee;
      $valueToPush = getListeSaisonCultuDetails($where);
      $arrCount = 0;
      $date_debut_default = '';
      $date_fin_default = '';
      while (list($key, $value) = each($valueToPush)) {
        $date_debut_avance1 = pg2phpDatebis($value['date_debut']);
        $date_debut_avance = date("d/m/Y",mktime(0, 0, 0, (int)$date_debut_avance1[0], (double)$date_debut_avance1[1], (double)$date_debut_avance1[2]));
        if(empty($value['date_debut'])) {
          $date_debut_avance = date("d")."/".date("m")."/".date("Y");//date("Y")."-".date("m")."-".date("d");
        }
        if ($arrCount == 0) {
          $date_debut_default = $date_debut_avance; //pour parametre saison tous
        }
        $date_fin_avance1 = pg2phpDatebis($value['date_fin_avance']);
        $date_fin_avance = date("d/m/Y",mktime(0, 0, 0, (int)$date_fin_avance1[0], (double)$date_fin_avance1[1], (double)$date_fin_avance1[2]));
        if(empty($value['date_fin_avance'])) {
          $date_fin_avance = ifSameMonthGetDate($date_debut_avance1);//date("Y")."-".date("m")."-".date("d");
        }
        $date_debut_solde1 = pg2phpDatebis($value['date_debut_solde']);
        $date_debut_solde = date("d/m/Y",mktime(0, 0, 0, (int)$date_debut_solde1[0], (double)$date_debut_solde1[1], (double)$date_debut_solde1[2]));
        if(empty($value['date_debut_solde'])) {
          $date_debut_solde = ifSameMonthGetDate($date_fin_avance1);//date("Y")."-".date("m")."-".date("d");
        }
        $date_fin_solde = pg2phpDatebis($value['date_fin_solde']);
        $date_fin_solde = date("d/m/Y",mktime(0, 0, 0, (int)$date_fin_solde[0], (double)$date_fin_solde[1], (double)$date_fin_solde[2]));
        if(empty($value['date_fin_solde'])) {
          $date_fin_solde = ifSameMonthGetDate($date_debut_solde1);//date("Y")."-".date("m")."-".date("d");
        }
        $codejs.=" valueToPush['_id'+".$arrCount."] = ".$value['id_saison'].";"; //'".$key."
        $codejs.=" valueToPush['_avance_debut'+".$arrCount."] = '".$date_debut_avance."';";
        $codejs.=" valueToPush['_avance_fin'+".$arrCount."] = '".$date_fin_avance."';";
        $codejs.=" valueToPush['_solde_debut'+".$arrCount."] = '".$date_debut_solde."';";
        $codejs.=" valueToPush['_solde_fin'+".$arrCount."] = '".$date_fin_solde."';";
        $codejs.=" _cQueueDates.push(valueToPush);";
        $date_fin_default = $date_fin_solde; //pour parametre saison tous
        $arrCount++;
      }
      //pour parametre saison tous
      $Myform->setFieldProperties("date_debut", FIELDP_DEFAULT, $date_debut_default);
      $Myform->setFieldProperties("date_fin", FIELDP_DEFAULT, $date_fin_default);

      $codejs.=" var obj = {};
            for (var i=0; i<_cQueueDates.length; i++) { // iterate on the array
              obj['_id'+i] = _cQueueDates[i]['_id'+i];
              if (obj['_id'+i] == document.ADForm.HTML_GEN_LSB_id_saison.value && document.ADForm.HTML_GEN_LSB_choix_period.value == 1){
                document.ADForm.HTML_GEN_date_date_debut.value = _cQueueDates[i]['_avance_debut'+i];
                document.ADForm.HTML_GEN_date_date_debut.readOnly = true;
                document.ADForm.HTML_GEN_date_date_fin.value = _cQueueDates[i]['_avance_fin'+i];
                document.ADForm.HTML_GEN_date_date_fin.readOnly = true;
              }
              if (obj['_id'+i] == document.ADForm.HTML_GEN_LSB_id_saison.value && document.ADForm.HTML_GEN_LSB_choix_period.value == 2){
                document.ADForm.HTML_GEN_date_date_debut.value = _cQueueDates[i]['_solde_debut'+i];
                document.ADForm.HTML_GEN_date_date_debut.readOnly = true;
                document.ADForm.HTML_GEN_date_date_fin.value = _cQueueDates[i]['_solde_fin'+i];
                document.ADForm.HTML_GEN_date_date_fin.readOnly = true;
              }
              if (obj['_id'+i] == document.ADForm.HTML_GEN_LSB_id_saison.value && document.ADForm.HTML_GEN_LSB_choix_period.value == 0){
                document.ADForm.HTML_GEN_date_date_debut.value = _cQueueDates[i]['_avance_debut'+i];
                document.ADForm.HTML_GEN_date_date_debut.readOnly = true;
                document.ADForm.HTML_GEN_date_date_fin.value = _cQueueDates[i]['_solde_fin'+i];
                document.ADForm.HTML_GEN_date_date_fin.readOnly = true;
              }
              if (document.ADForm.HTML_GEN_LSB_id_saison.value == 0){
                document.ADForm.HTML_GEN_date_date_debut.value = _cQueueDates[0]['_avance_debut'+0];
                document.ADForm.HTML_GEN_date_date_debut.readOnly = true;
                document.ADForm.HTML_GEN_date_date_fin.value = _cQueueDates[($arrCount-1)]['_solde_fin'+($arrCount-1)];
                document.ADForm.HTML_GEN_date_date_fin.readOnly = true;
                document.ADForm.HTML_GEN_LSB_periode.value = 0;
              }
            }";
      $codejs.="}";

      $Myform->addField("sit_pai_glob_his", _("Situation Paiement Globale"), TYPC_BOL);
      $Myform->setFieldProperties("sit_pai_glob_his", FIELDP_DEFAULT, true);
      $Myform->addField("lis_ben_glob_his", _("Liste Beneficiare ayant payé Globale"), TYPC_BOL);
      $Myform->setFieldProperties("lis_ben_glob_his", FIELDP_DEFAULT, true);
      $Myform->addField("rpt_zon_glob_his", _("Repartition par zone Globale"), TYPC_BOL);
      $Myform->setFieldProperties("rpt_zon_glob_his", FIELDP_DEFAULT, true);

      $codeJsCSV = "";
      $codeJsCSV .= "function fieldCheckCSV(){\n";
      $codeJsCSV .= "\tvar check = 0;\n";
      $codeJsCSV .= "\tif (document.ADForm.HTML_GEN_BOL_sit_pai_glob_his.checked == true){\n";
      $codeJsCSV .= "\t\tcheck = check + 1;\n";
      $codeJsCSV .= "\t}\n";
      $codeJsCSV .= "\tif (document.ADForm.HTML_GEN_BOL_lis_ben_glob_his.checked == true){\n";
      $codeJsCSV .= "\t\tcheck = check + 1;\n";
      $codeJsCSV .= "\t}\n";
      $codeJsCSV .= "\tif (document.ADForm.HTML_GEN_BOL_rpt_zon_glob_his.checked == true){\n";
      $codeJsCSV .= "\t\tcheck = check + 1;\n";
      $codeJsCSV .= "\t}\n";
      $codeJsCSV .= "\tif (check > 1){\n";
      $codeJsCSV .= "\t\tADFormValid = false;\n";
      $codeJsCSV .= "\t\talert('Veuillez cocher une seule case pour la sortie du Rapport en CSV!!');\n";
      $codeJsCSV .= "\t\texit;\n";
      $codeJsCSV .= "\t}\n";
      $codeJsCSV .= "\tif (check == 0){\n";
      $codeJsCSV .= "\t\tADFormValid = false;\n";
      $codeJsCSV .= "\t\talert('Veuillez cocher une case pour la sortie du Rapport en CSV!!');\n";
      $codeJsCSV .= "\t\texit;\n";
      $codeJsCSV .= "\t}\n";
      $codeJsCSV .= "";
      $codeJsCSV .= "}\n";

      $codeJsPDF = "";
      $codeJsPDF .= "function fieldCheckPDF(){\n";
      $codeJsPDF .= "\tvar check = 0;\n";
      $codeJsPDF .= "\tif (document.ADForm.HTML_GEN_BOL_sit_pai_glob_his.checked == true){\n";
      $codeJsPDF .= "\t\tcheck = check + 1;\n";
      $codeJsPDF .= "\t}\n";
      $codeJsPDF .= "\tif (document.ADForm.HTML_GEN_BOL_lis_ben_glob_his.checked == true){\n";
      $codeJsPDF .= "\t\tcheck = check + 1;\n";
      $codeJsPDF .= "\t}\n";
      $codeJsPDF .= "\tif (document.ADForm.HTML_GEN_BOL_rpt_zon_glob_his.checked == true){\n";
      $codeJsPDF .= "\t\tcheck = check + 1;\n";
      $codeJsPDF .= "\t}\n";
      $codeJsPDF .= "\tif (check == 0){\n";
      $codeJsPDF .= "\t\tADFormValid = false;\n";
      $codeJsPDF .= "\t\talert('Veuillez cocher au moin une case pour la sortie du Rapport en PDF!!');\n";
      $codeJsPDF .= "\t\texit;\n";
      $codeJsPDF .= "\t}\n";
      $codeJsPDF .= "";
      $codeJsPDF .= "}\n";

      $codeJsExcel = "";
      $codeJsExcel .= "function fieldCheckExcel(){\n";
      $codeJsExcel .= "\tvar check = 0;\n";
      $codeJsExcel .= "\tif (document.ADForm.HTML_GEN_BOL_sit_pai_glob_his.checked == true){\n";
      $codeJsExcel .= "\t\tcheck = check + 1;\n";
      $codeJsExcel .= "\t}\n";
      $codeJsExcel .= "\tif (document.ADForm.HTML_GEN_BOL_lis_ben_glob_his.checked == true){\n";
      $codeJsExcel .= "\t\tcheck = check + 1;\n";
      $codeJsExcel .= "\t}\n";
      $codeJsExcel .= "\tif (document.ADForm.HTML_GEN_BOL_rpt_zon_glob_his.checked == true){\n";
      $codeJsExcel .= "\t\tcheck = check + 1;\n";
      $codeJsExcel .= "\t}\n";
      $codeJsExcel .= "\tif (check > 1){\n";
      $codeJsExcel .= "\t\tADFormValid = false;\n";
      $codeJsExcel .= "\t\talert('Veuillez cocher une seule case pour la sortie du Rapport en Excel!!');\n";
      $codeJsExcel .= "\t\texit;\n";
      $codeJsExcel .= "\t}\n";
      $codeJsExcel .= "\tif (check == 0){\n";
      $codeJsExcel .= "\t\tADFormValid = false;\n";
      $codeJsExcel .= "\t\talert('Veuillez cocher une case pour la sortie du Rapport en Excel!!');\n";
      $codeJsExcel .= "\t\texit;\n";
      $codeJsExcel .= "\t}\n";
      $codeJsExcel .= "";
      $codeJsExcel .= "}\n";


      $Myform->addJS(JSP_FORM, "codeJsCSV", $codeJsCSV);
      $Myform->addJS(JSP_FORM, "codeJsPDF", $codeJsPDF);
      $Myform->addJS(JSP_FORM, "codeJsExcel", $codeJsExcel);
      $Myform->addJS(JSP_FORM, "JS10", $codejs);

      //Boutons
      $Myform->addFormButton(1, 1, "pdf_glob_act_his", _("Rapport PDF"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("pdf_glob_act_his", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $Myform->setFormButtonProperties("pdf_glob_act_his", BUTP_JS_EVENT, array("onClick"=>"fieldCheckPDF();"));
      $Myform->addFormButton(1, 2, "csv_glob_act_his", _("Export CSV"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("csv_glob_act_his", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $Myform->setFormButtonProperties("csv_glob_act_his", BUTP_JS_EVENT, array("onClick"=>"fieldCheckCSV();"));
      $Myform->addFormButton(1, 3, "excel_glob_act_his", _("Export Excel"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("excel_glob_act_his", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $Myform->setFormButtonProperties("excel_glob_act_his", BUTP_JS_EVENT, array("onClick"=>"fieldCheckExcel();"));
    }

    $Myform->addFormButton(1,4, "butret", _("Retour"), TYPB_SUBMIT);
    $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
    $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-1");

    $Myform->buildHTML();
    echo $Myform->getHTML();
  }


}
else if ($global_nom_ecran == "Pnr-3") {
  if ($SESSION_VARS['contenu'] == 'sit_pai') {
    if (!isset($SESSION_VARS['page_reload'])) {
      $Myform = new HTML_GEN2(_("Les donnees"));
      $list_annee_agri_selected = getListeAnneeAgricoleActif("id_annee=" . $SESSION_VARS['id_annee_selected']);
      $list_saison_cultu_selected = getListeSaisonPNSEB("id_saison=" . $id_saison);
      $Myform->addField("id_annee", _("Annee agricole"), TYPC_TXT);
      $Myform->setFieldProperties("id_annee", FIELDP_DEFAULT, $list_annee_agri_selected[$SESSION_VARS['id_annee_selected']]);
      $Myform->setFieldProperties("id_annee", FIELDP_IS_LABEL, true);

      $Myform->addField("id_saison", _("Saison culturale"), TYPC_TXT);
      $Myform->setFieldProperties("id_saison", FIELDP_DEFAULT, $list_saison_cultu_selected[$id_saison]);
      $Myform->setFieldProperties("id_saison", FIELDP_IS_LABEL, true);

      if ($choix_period > 0) {
        $periode = $adsys['adsys_choix_periode'][$choix_period];
      } else {
        $periode = "Tous";
      }
      $Myform->addField("choix_period", _("Choix periode"), TYPC_TXT);
      $Myform->setFieldProperties("choix_period", FIELDP_DEFAULT, $periode);
      $Myform->setFieldProperties("choix_period", FIELDP_IS_LABEL, true);

      $date_saison_selected = getAnneeAgricoleFromSaison($id_saison);

      if ($choix_period == 1) {
        $Myform->addField("date_debut_avance", _("Date debut des avances"), TYPC_TXT);
        $Myform->setFieldProperties("date_debut_avance", FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("date_debut_avance", FIELDP_DEFAULT, pg2phpDate($date_saison_selected['date_debut']));
        $date_debut = pg2phpDate($date_saison_selected['date_debut']);

        $Myform->addField("date_fin_avance", _("Date fin des avances"), TYPC_TXT);
        $Myform->setFieldProperties("date_fin_avance", FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("date_fin_avance", FIELDP_DEFAULT, pg2phpDate($date_saison_selected['date_fin_avance']));
        $date_fin = pg2phpDate($date_saison_selected['date_fin_avance']);
      } else if ($choix_period == 2) {
        $Myform->addField("date_debut_soldes", _("Date debut des soldes"), TYPC_TXT);
        $Myform->setFieldProperties("date_debut_soldes", FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("date_debut_soldes", FIELDP_DEFAULT, pg2phpDate($date_saison_selected['date_debut_solde']));
        $date_debut = pg2phpDate($date_saison_selected['date_debut']);

        $Myform->addField("date_fin_solde", _("Date fin des soldes"), TYPC_TXT);
        $Myform->setFieldProperties("date_fin_solde", FIELDP_IS_LABEL, true);
        if ($date_saison_selected['date_fin_solde'] == null){
          $Myform->setFieldProperties("date_fin_solde", FIELDP_DEFAULT, date('d/m/y'));
          $date_fin = date('d/m/y');
        }else {
          $Myform->setFieldProperties("date_fin_solde", FIELDP_DEFAULT, pg2phpDate($date_saison_selected['date_fin_solde']));
          $date_fin = pg2phpDate($date_saison_selected['date_fin_solde']);
        }
      } else if ($choix_period == 0) {
        $Myform->addField("date_debut_soldes", _("Date debut des soldes"), TYPC_TXT);
        $Myform->setFieldProperties("date_debut_soldes", FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("date_debut_soldes", FIELDP_DEFAULT, pg2phpDate($date_saison_selected['date_debut']));
        $date_debut = pg2phpDate($date_saison_selected['date_debut']);

        $Myform->addField("date_fin_solde", _("Date fin des soldes"), TYPC_TXT);
        $Myform->setFieldProperties("date_fin_solde", FIELDP_IS_LABEL, true);
        if ($date_saison_selected['date_fin_solde'] == null){
          $Myform->setFieldProperties("date_fin_solde", FIELDP_DEFAULT,date('d/m/y'));
          $date_fin = date('d/m/y');
        }else {
          $Myform->setFieldProperties("date_fin_solde", FIELDP_DEFAULT, pg2phpDate($date_saison_selected['date_fin_solde']));
          $date_fin = pg2phpDate($date_saison_selected['date_fin_solde']);
        }
      }
      $datas_paiement_commande = getMontantPaiementCommande(null, $choix_period, $date_debut, $date_fin);

      $Myform->addField("nb_agriculteur", _("Nombre agriculteur"), TYPC_INT);
      $Myform->setFieldProperties("nb_agriculteur", FIELDP_DEFAULT, $datas_paiement_commande['nb_agri']);
      $Myform->setFieldProperties("nb_agriculteur", FIELDP_IS_LABEL, true);

      $data_total_mnt_prod = getTotalSituation($date_debut, $date_fin, $SESSION_VARS['id_annee_selected'], $id_saison, $choix_period);

      $Myform->addField("total_mnt_encaisse", _("Total Montant encaisse"), TYPC_MNT);
      $Myform->setFieldProperties("total_mnt_encaisse", FIELDP_DEFAULT, $data_total_mnt_prod['total']);
      $Myform->setFieldProperties("total_mnt_encaisse", FIELDP_IS_LABEL, true);


      $datas_detail_produit = getDetailSituation($date_debut,$date_fin,$SESSION_VARS['id_annee_selected'],$id_saison,$choix_period);
      $xtHTML = "<br><table  cellpadding=\"5\" width=\"60% \" align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
     <tr align=\"center\" bgcolor=\"$colb_tableau\"><th>" . _("Produit") . "</th><th>" . _("Montant avance paye") . "</th><th>" . _("Montant solde paye") . "</th><th>" . _("Montant total") . "</th></tr>";
      $mnt_tot_avance = 0;
      $mnt_paye_tot = 0;
      $xtHTML .= "\n<tr bgcolor=\"$color\">";
      while (list($key, $DET) = each($datas_detail_produit)) {
        $mnt_tot_avance +=$DET['mnt_avance'];
        $mnt_tot_solde += $DET['mnt_solde'] ;
        $mnt_paye_tot += $DET['mnt_avance'] + $DET['mnt_solde'];

        $xtHTML .= "\n<tr bgcolor=\"$color\"><td>" . $DET['libel_produit']. "</td><td>" . afficheMontant($DET['mnt_avance']) . "</td><td>" . afficheMontant($DET['mnt_solde']) . "</td><td>" . afficheMontant( $DET['mnt_avance'] + $DET['mnt_solde']) . "</td></tr>";

      }
      $xtHTML .= "\n<td><b> Total </b></td><td><b>" . afficheMontant($mnt_tot_avance) . "</b></td><td><b>" . afficheMontant($mnt_tot_solde) . "</b></td><td><b>" . afficheMontant($mnt_paye_tot) . "</b></td></tr>";
      $xtHTML .= "</table>";

      $Myform->addHTMLExtraCode("xtHTML", $xtHTML);

      $SESSION_VARS['page_reload'] = true;
      $SESSION_VARS['data_produit'] = $datas_produit_encaisse;
      $SESSION_VARS['choix_period'] = $choix_period;
      $SESSION_VARS['date_saison_selected'] = $date_saison_selected;
      $SESSION_VARS['paiement_commande'] = $datas_paiement_commande;
      $SESSION_VARS['date_debut'] = $date_debut;
      $SESSION_VARS['date_fin'] = $date_fin;
      $SESSION_VARS['id_saison'] =$id_saison;
      $SESSION_VARS['id_annee'] = $SESSION_VARS['id_annee_selected'];

      if($choix_period ==  1){
        $date_rapport_debut = pg2phpDate($date_saison_selected['date_debut']);
        $date_rapport_fin = pg2phpDate($date_saison_selected['date_fin_avance']);
      } elseif($choix_period == 2) {
        $date_rapport_debut = pg2phpDate($date_saison_selected['date_debut_solde']);
        $date_rapport_fin = pg2phpDate($date_saison_selected['date_fin_solde']);
      }
      $data_solde_total_mnt_prod = getTotalSoldeSituation($date_debut, $date_fin, $SESSION_VARS['id_annee_selected'], $id_saison, $choix_period);
      

      if ($choix_period == 1) {
        $criteres = array(
          _("Annee agricole") => $list_annee_agri_selected[$SESSION_VARS['id_annee_selected']],
          _("Saison culturale") => $list_saison_cultu_selected[$id_saison],
          _("Choix periode") => $periode,
          _("Date debut") => $date_rapport_debut,
          _("Date fin") => $date_rapport_fin,
          _("Nombre agriculteurs") => $datas_paiement_commande['nb_agri'],
          _("Total montant encaissee") => $data_total_mnt_prod['total']

        );
      }else {
        if ($date_saison_selected['date_fin_solde'] == null){
          $date_rapport_fin = date('d/m/y');
        }
        $criteres = array(
          _("Annee agricole") => $list_annee_agri_selected[$SESSION_VARS['id_annee_selected']],
          _("Saison culturale") => $list_saison_cultu_selected[$id_saison],
          _("Choix periode") => $periode,
          _("Date debut") => $date_rapport_debut,
          _("Date fin") => $date_rapport_fin,
          _("Nombre agriculteurs") => $datas_paiement_commande['nb_agri'],
          _("Total montant encaissee") => $data_solde_total_mnt_prod['total_solde']

        );
      }
      $SESSION_VARS['criteres'] = $criteres;
      $Myform->addFormButton(1, 1, "valider", _("Rapport PDF"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $Myform->addFormButton(1, 2, "excel", _("Export EXCEL"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("excel", BUTP_PROCHAIN_ECRAN, "Pnr-3");
      $Myform->addFormButton(1, 3, "csv", _("Export CSV"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("csv", BUTP_PROCHAIN_ECRAN, "Pnr-3");

      $Myform->addFormButton(1, 4, "butret", _("Retour"), TYPB_SUBMIT);
      $Myform->setFormButtonProperties("butret", BUTP_CHECK_FORM, false);
      $Myform->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Pns-1");


      $Myform->buildHTML();
      echo $Myform->getHTML();
    }
    else {
      $datas_detail_produit_rapport = getDetailSituationTest($SESSION_VARS['date_debut'],$SESSION_VARS['date_fin'],$SESSION_VARS['id_annee'],$SESSION_VARS['id_saison'],$SESSION_VARS['choix_period']);

      if (isset($valider)) {

        //$xml = xml_situation_paiement($SESSION_VARS['criteres'], $SESSION_VARS['date_saison_selected'], $SESSION_VARS['paiement_commande'], $SESSION_VARS['data_produit'], $SESSION_VARS['choix period']);
        $xml = xml_situation_paiement($SESSION_VARS['criteres'],$datas_detail_produit_rapport,$SESSION_VARS['choix_period']);
        $fichier = xml_2_xslfo_2_pdf($xml, 'engraischimiques_situation_paiement.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Pns-1", $fichier);
        unset($SESSION_VARS['page_reload']);
        unset($SESSION_VARS['data_produit']);
        unset($SESSION_VARS['choix']);
        unset($SESSION_VARS['date_saison']);
        unset($SESSION_VARS['paiement_commande']);
        unset($SESSION_VARS['date_saison_selected']);
        unset($SESSION_VARS['criteres']);
        unset($SESSION_VARS['contenu']);
        unset($SESSION_VARS['id_annee_selected']);
        unset($SESSION_VARS['choix_period']);

      } else if (isset($csv) || isset($excel)) {
        unset($SESSION_VARS['page_reload']);
        //$xml = xml_situation_paiement($SESSION_VARS['criteres'], $SESSION_VARS['date_saison_selected'], $SESSION_VARS['paiement_commande'], $SESSION_VARS['data_produit'], $SESSION_VARS['choix period'], true);
        $xml = xml_situation_paiement($SESSION_VARS['criteres'],$datas_detail_produit_rapport,$SESSION_VARS['choix_period'],true);
        $fichier = xml_2_csv($xml, 'engraischimiques_situation_paiement.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        if ($excel == 'Export EXCEL'){
          echo getShowEXCELHTML("Pns-1", $fichier);
        }
        else{
          echo getShowCSVHTML("Pns-1", $fichier);
        }
        unset($SESSION_VARS['page_reload']);
        unset($SESSION_VARS['data_produit']);
        unset($SESSION_VARS['choix']);
        unset($SESSION_VARS['date_saison']);
        unset($SESSION_VARS['paiement_commande']);
        unset($SESSION_VARS['date_saison_selected']);
        unset($SESSION_VARS['criteres']);
        unset($SESSION_VARS['contenu']);
        unset($SESSION_VARS['id_annee_selected']);
        unset($SESSION_VARS['choix_period']);
        //$Myform->buildHTML();
        //echo $Myform->getHTML();
      }
    }
  }

  if ($SESSION_VARS['contenu'] == 'lis_ben') {
    if ($periode == null){
      $periode = 0;
    }
    //Effacement de la vue actuelle
    $delete_view = DeleteViewRapportBenefPaye();
    if($delete_view !=true ){
      $html_err = new HTML_erreur();

      //$err_msg = $error[$erreur->errCode];
      $err_msg = "Veuillez contacter votre administrateur";

      $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

      $html_err->addButton("BUTTON_OK", 'Pnr-1');

      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }

    //Creation du view pour recuperation des donnees
    $view = CreateViewRapportBenefPaye($annee, $saison, $periode, $date_debut, $date_fin);

    if($view !=true ){
      $html_err = new HTML_erreur();

      //$err_msg = $error[$erreur->errCode];
      $err_msg = "Veuillez contacter votre administrateur";

      $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

      $html_err->addButton("BUTTON_OK", 'Pnr-1');

      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
    if (isset($csv_lis_ben) || isset($excel_lis_ben)) {
      //Génération du CSV
      $xml = xml_list_beneficiaire_payant($annee, $saison, $periode, $date_debut, $date_fin, true);

      if ($xml != NULL) {
        $csv_file = xml_2_csv($xml, 'engraisChimiques_listbenefpayant.xslt');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        if ($excel_lis_ben == 'Export EXCEL'){
          echo getShowEXCELHTML("Pnr-1", $csv_file);
        }
        else{
          echo getShowCSVHTML("Pnr-1", $csv_file);
        }
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée correspond au criteres de recherche. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

    } elseif (isset($pdf_lis_ben) && $pdf_lis_ben == 'Rapport PDF') {

      //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
      $xml = xml_list_beneficiaire_payant($annee, $saison, $periode, $date_debut, $date_fin, false);

      if ($xml != NULL) {
        $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'engraisChimiques_listbenefpayant.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Pnr-1", $fichier_pdf);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée correspond au criteres de recherche. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
  }

  if ($SESSION_VARS['contenu'] == 'lis_aut') {

    if($id_saison == null){
      $saison = "Tous";
    }else{
      $saison = $id_saison;
    }


    $criteres = array(
      _("Annee agricole") => $SESSION_VARS['id_annee_selected'],
      _("Saison culturale") => $saison
    );

    if ($id_saison == null) {
      $liste_saison = getListeSaisonPNSEB("id_annee =" . $SESSION_VARS['id_annee_selected']);
      $DATAS = array();
      $DATAS_rec = array();
      while (list($key, $COM) = each($liste_saison)) {
        $DATAS = getListeBenefPlafond($SESSION_VARS['id_annee_selected'], $key);
        $DATAS_rec = array_merge($DATAS_rec, $DATAS);
      }
    } else {
      $DATAS_rec = getListeBenefPlafond($SESSION_VARS['id_annee_selected'], $saison);
    }
    if (isset($valider)) {
      $xml = xml_liste_benef_autorisation_plafond($SESSION_VARS['id_annee_selected'], $id_saison, $criteres, $DATAS_rec);

      $fichier = xml_2_xslfo_2_pdf($xml, 'engraischimiques_liste_benef_plafond.xslt');
      //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
      echo get_show_pdf_html("Pnr-1", $fichier);
    } else if (isset($csv)) {
      unset($SESSION_VARS['page_reload']);
      $xml = xml_liste_benef_autorisation_plafond($SESSION_VARS['id_annee_selected'], $id_saison, $criteres, $DATAS_rec, true);
      $fichier = xml_2_csv($xml, 'engraischimiques_liste_benef_plafond.xslt');
      //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
      echo getShowCSVHTML("Pnr-1", $fichier);
    }

    unset($SESSION_VARS['choix_period']);
    unset($SESSION_VARS['contenu']);
    unset($SESSION_VARS['id_annee_selected']);
  }

  if ($SESSION_VARS['contenu'] == 'qti_zon') {

    $criteres = array(
      _("Annee agricole") => $annee,
      _("Saison culturale") => $saison,
    );

    $date_saison_selected = getAnneeAgricoleFromSaison($saison);
    $date_debut = pg2phpDate($date_saison_selected['date_debut']);
    $date_fin = pg2phpDate($date_saison_selected['date_fin_solde']);

    if (isset($pdf_qti_zon)) {
      $xml = xml_repartition_qtite_zone($annee, $saison,$date_debut,$date_fin);

      $fichier = xml_2_xslfo_2_pdf($xml, 'engraischimiques_repartition_qtite_zone.xslt');
      //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
      echo get_show_pdf_html("Pnr-1", $fichier);
    }
    else if (isset($csv_qti_zon) || isset($excel_qti_zon)) {
      $xml = xml_repartition_qtite_zone($annee, $saison,$date_debut,$date_fin,false);
      $fichier = xml_2_csv($xml, 'engraischimiques_repartition_qtite_zone.xslt');
      //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
      if ($excel_qti_zon == 'Export EXCEL'){
        echo getShowEXCELHTML("Pnr-1", $fichier);
      }
      else{
        echo getShowCSVHTML("Pnr-1", $fichier);
      }
    }

    unset($SESSION_VARS['choix_period']);
    unset($SESSION_VARS['contenu']);
    unset($SESSION_VARS['id_annee_selected']);

  }

  if (isset($sit_pai_glob) && $SESSION_VARS['contenu'] == 'glob_act') {

    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
    $date_total = $date_jour."/".$date_mois."/".$date_annee;

    $info_general = getPeriodeEC($date_total);

    $datas_detail_produit_rapport = getDetailSituationGlobal($info_general['date_debut'],$info_general['date_fin'],$info_general['id_annee'],$info_general['id_saison'],$info_general['periode']);

    if (isset($pdf_glob_act)) {

      $xml = xml_situation_paiement_global($datas_detail_produit_rapport,$info_general['periode'],$info_general['id_saison'],$info_general['id_annee']);
      if ($xml != null){
        $fichier = xml_2_xslfo_2_pdf($xml, 'engraischimiques_situation_paiement.xslt',false, 'sit_paye');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Pnr-1", $fichier);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

    } else if (isset($csv_glob_act)) {
      unset($SESSION_VARS['page_reload']);

      $xml = xml_situation_paiement_global($datas_detail_produit_rapport,$info_general['periode'],$info_general['id_saison'],$info_general['id_annee'],true);

      if ($xml != null){
        $fichier = xml_2_csv($xml, 'engraischimiques_situation_paiement.xslt');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowCSVHTML("Pnr-1", $fichier);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
    else if (isset($excel_glob_act)) {
      unset($SESSION_VARS['page_reload']);

      $xml = xml_situation_paiement_global($datas_detail_produit_rapport,$info_general['periode'],$info_general['id_saison'],$info_general['id_annee'],true);

      if ($xml != null){
        $fichier = xml_2_csv($xml, 'engraischimiques_situation_paiement.xslt');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Pnr-1", $fichier);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
  }

  if (isset($lis_ben_glob) && $SESSION_VARS['contenu'] == 'glob_act') {

    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
    $datedujour = $date_jour."/".$date_mois."/".$date_annee;

    $info_general = getPeriodeEC($datedujour);

    $date_debut = pg2phpDate($info_general['date_debut']);
    $date_fin = pg2phpDate($info_general['date_fin']);

    if (isset($csv_glob_act) && $csv_glob_act == 'Export CSV') {
      //Génération du CSV
      $xml = xml_list_beneficiaire_payant_globale($info_general['id_annee'], $info_general['id_saison'], $info_general['periode'], $date_debut, $date_fin, true);

      if ($xml != NULL) {
        $csv_file = xml_2_csv($xml, 'engraisChimiques_listbenefpayant.xslt');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowCSVHTML("Pnr-1", $csv_file);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

    } elseif (isset($pdf_glob_act) && $pdf_glob_act == 'Rapport PDF') {

      //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
      $xml = xml_list_beneficiaire_payant_globale($info_general['id_annee'], $info_general['id_saison'], $info_general['periode'], $date_debut, $date_fin, false);

      if ($xml != NULL) {
        $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'engraisChimiques_listbenefpayant.xslt', false, 'listbenef');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Pnr-1", $fichier_pdf);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
    elseif (isset($excel_glob_act) && $excel_glob_act == 'Export Excel') {
      //Génération du CSV
      $xml = xml_list_beneficiaire_payant_globale($info_general['id_annee'], $info_general['id_saison'], $info_general['periode'], $date_debut, $date_fin, true);

      if ($xml != NULL) {
        $csv_file = xml_2_csv($xml, 'engraisChimiques_listbenefpayant.xslt');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Pnr-1", $csv_file);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

    }
  }

  if (isset($rpt_zon_glob) && $SESSION_VARS['contenu'] == 'glob_act') {
    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
    $date_total = $date_jour."/".$date_mois."/".$date_annee;

  $info_general = getPeriodeEC($date_total);

    if (isset($pdf_glob_act)) {
      $xml = xml_repartition_qtite_zone_global($info_general['id_annee'], $info_general["id_saison"],$info_general['date_debut'],$info_general['date_fin']);

      if ($xml != null){
        $fichier = xml_2_xslfo_2_pdf($xml, 'engraischimiques_repartition_qtite_zone.xslt',false,'qtite_zone');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Pnr-1", $fichier);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
    else if (isset($csv_glob_act)) {
      $xml = xml_repartition_qtite_zone_global($info_general['id_annee'], $info_general["id_saison"],$info_general['date_debut'],$info_general['date_fin'],true);

      if($xml != null){
        $fichier = xml_2_csv($xml, 'engraischimiques_repartition_qtite_zone.xslt');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowCSVHTML("Pnr-1", $fichier);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
    else if (isset($excel_glob_act)) {
      $xml = xml_repartition_qtite_zone_global($info_general['id_annee'], $info_general["id_saison"],$info_general['date_debut'],$info_general['date_fin'],true);

      if($xml != null){
        $fichier = xml_2_csv($xml, 'engraischimiques_repartition_qtite_zone.xslt');

        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Pnr-1", $fichier);
      }
      else{
        $html_err = new HTML_erreur();

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }
    }
  }

  if ($SESSION_VARS['contenu'] == 'glob_his'){
    global $dbHandler;

    $date_jour = date("d");
    $date_mois = date("m");
    $date_annee = date("Y");
    $datedujour = $date_jour."/".$date_mois."/".$date_annee;

    $info_general = getPeriodeEC($datedujour);
    $details_saison = getAnneeAgricoleFromSaison($id_saison);
    if ($choix_period ==1){
      $date_debut_db = pg2phpDate($details_saison['date_debut']); 
      $date_fin_db = pg2phpDate($details_saison['date_fin_avance']);
    }else{
      $date_debut_db = pg2phpDate($details_saison['date_debut_solde']);
      $date_fin_db = pg2phpDate($details_saison['date_fin_solde']); 
    }

      if (isAfter($date_debut, $date_fin)){
        $html_err = new HTML_erreur("Globalisation des transaction PNSEB Annee Culturale Historique");

        //$err_msg = $error[$erreur->errCode];
        $err_msg = "La date de debut est superieur a la date fin. Veuillez entrer les bonnes dates!";

        $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Pnr-1');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
        exit();
      }
      else{
        if (isBefore($date_debut , $date_debut_db)){
          $html_err = new HTML_erreur("Globalisation des transaction PNSEB Annee Culturale Historique");

          //$err_msg = $error[$erreur->errCode];
          $err_msg = "La date de debut est anterieur a la date de debut de la periode!";

          $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

          $html_err->addButton("BUTTON_OK", 'Pnr-1');

          $html_err->buildHTML();
          echo $html_err->HTML_code;
          exit();
        }
        else if (isAfter($date_debut, $date_fin_db)){
          $html_err = new HTML_erreur("Globalisation des transaction PNSEB Annee Culturale Historique");

          //$err_msg = $error[$erreur->errCode];
          $err_msg = "La date de debut est superieur a la date de fin de la periode!";

          $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

          $html_err->addButton("BUTTON_OK", 'Pnr-1');

          $html_err->buildHTML();
          echo $html_err->HTML_code;
          exit();
        }
        else if (isAfter($date_fin , $date_fin_db)){
          $html_err = new HTML_erreur("Globalisation des transaction PNSEB Annee Culturale Historique");

          //$err_msg = $error[$erreur->errCode];
          $err_msg = "La date de fin est superieur a la date de fin de la periode!";

          $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

          $html_err->addButton("BUTTON_OK", 'Pnr-1');

          $html_err->buildHTML();
          echo $html_err->HTML_code;
          exit();
        }
      }

    /*if ($info_general['id_annee'] == $SESSION_VARS['id_annee_selected'] && $info_general['id_saison'] == $id_saison && $info_general['periode'] == $choix_period){
      $html_err = new HTML_erreur("Globalisation des transaction PNSEB Annee Culturale Historique");

      //$err_msg = $error[$erreur->errCode];
      $err_msg = "Les parametres choisit correspondent à l'annee culturale courante. Veuillez passer par l'option 'Globalisation des transaction PNSEB Annee Culturale Courante' pour sortir les rapports plus rapide!!";

      $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

      $html_err->addButton("BUTTON_OK", 'Pnr-1');

      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit();
    }*/

    //recuperation depuis base filles
    $truncateTable = truncateTable("ec_situation_paiement_historique");
    $truncateTable = truncateTable("ec_benef_paye_historique");
    $truncateTable = truncateTable("ec_repartition_zone_historique");

    $ListeAgences = AgenceRemote::getListRemoteAgence(true);
    foreach ($ListeAgences as $key => $value){

      $agenceCo = AgenceRemote::getRemoteAgenceConnection($value['id_agc']);
      if($agenceCo != null) {
        $agenceCo->beginTransaction();

        try {
          $id_annee = $SESSION_VARS['id_annee_selected'] ;

          // Init class
          $GlobalisationObj = new Globalisation($agenceCo, $value['id_agc']);
          //$where = "id_saison = ".$id_saison;
         /* $Detail_saison=getAnneeAgricoleFromSaison($id_saison);
          $id_annee = $Detail_saison['id_annee'];
          if ($choix_period == 1){
            $date_debut = $Detail_saison['date_debut'];
            $date_fin = $Detail_saison['date_fin_avance'];

          }else{
            $date_debut = $Detail_saison['date_debut_solde'];
            $date_fin = $Detail_saison['date_fin'];
          }*/
          //Recuperation historique pour les situations de paiement
          $DataSituation = $GlobalisationObj->getSituationPaiement($date_debut,$date_fin,$id_annee,$id_saison,$choix_period);

          //Recuperation historique pour la repartition selon les zone
          if($choix_period == 2){
            $details_saison = getAnneeAgricoleFromSaison($id_saison);
            $date_debut_repartition = pg2phpDate($details_saison['date_debut']);
            $date_fin_repartition = pg2phpDate($details_saison['date_fin_avance']);
          }else{
            $date_debut_repartition = $date_debut;
            $date_fin_repartition = $date_fin;
          }
          $DataRepartition= $GlobalisationObj->getRepartitionZone($id_annee,$id_saison,$date_debut_repartition,$date_fin_repartition);


          //Recuperation historique pour les benefs ayant paye
          $delete_view = $GlobalisationObj->deleteView();
          $create_view = $GlobalisationObj->createViewPaye($id_annee, $id_saison, $choix_period, $date_debut,$date_fin);
          $DataBenefPaye= $GlobalisationObj->getBenefPaye($id_annee,$id_saison,$choix_period,$date_debut,$date_fin);

          unset($GlobalisationObj);

          $agenceCo->commit();

        } catch (PDOException $e) {
          $pdo_conn->rollBack(); // Roll back remote transaction
        }
      }

      unset($agenceCo);

      $db = $dbHandler->openConnection();
      foreach($DataSituation as $keySituation => $valueSituation){
        $result = executeQuery($db, buildInsertQuery("ec_situation_paiement_historique", $valueSituation));
      }
      foreach($DataRepartition as $keyRepartition => $valueRepartition){
        $result = executeQuery($db, buildInsertQuery("ec_repartition_zone_historique", $valueRepartition));
      }
      foreach($DataBenefPaye as $keyBenefPaye => $valueBenefPaye){
        $result = executeQuery($db, buildInsertQuery("ec_benef_paye_historique", $valueBenefPaye));
      }
      $dbHandler->closeConnection(true);

    }

    if(isset($sit_pai_glob_his)){

      /*$Detail_saison=getAnneeAgricoleFromSaison($id_saison);
      $id_annee = $Detail_saison['id_annee'];
      if ($choix_period == 1){
        $date_debut = $Detail_saison['date_debut'];
        $date_fin = $Detail_saison['date_fin_avance'];

      }else{
        $date_debut = $Detail_saison['date_debut_solde'];
        $date_fin = $Detail_saison['date_fin'];
      }*/
      $datas_detail_produit_rapport = getDetailSituationHist($date_debut,$date_fin,$id_annee,$id_saison,$choix_period);

      if (isset($pdf_glob_act_his)) {

        //$xml = xml_situation_paiement($SESSION_VARS['criteres'], $SESSION_VARS['date_saison_selected'], $SESSION_VARS['paiement_commande'], $SESSION_VARS['data_produit'], $SESSION_VARS['choix period']);
        $xml = xml_situation_paiement_historique($datas_detail_produit_rapport,$choix_period,$id_saison,$id_annee,$date_debut,$date_fin);
        $fichier = xml_2_xslfo_2_pdf($xml, 'engraischimiques_situation_paiement.xslt',false, 'sit_paye_his');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Pnr-1", $fichier);

      } else if (isset($csv_glob_act_his)) {
        unset($SESSION_VARS['page_reload']);
        //$xml = xml_situation_paiement($SESSION_VARS['criteres'], $SESSION_VARS['date_saison_selected'], $SESSION_VARS['paiement_commande'], $SESSION_VARS['data_produit'], $SESSION_VARS['choix period'], true);
        $xml = xml_situation_paiement_historique($datas_detail_produit_rapport,$choix_period,$id_saison,$id_annee,true);
        $fichier = xml_2_csv($xml, 'engraischimiques_situation_paiement.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowCSVHTML("Pnr-1", $fichier);
        //$Myform->buildHTML();
        //echo $Myform->getHTML();
      }  else if (isset($excel_glob_act_his)) {
        unset($SESSION_VARS['page_reload']);
        //$xml = xml_situation_paiement($SESSION_VARS['criteres'], $SESSION_VARS['date_saison_selected'], $SESSION_VARS['paiement_commande'], $SESSION_VARS['data_produit'], $SESSION_VARS['choix period'], true);
        $xml = xml_situation_paiement_historique($datas_detail_produit_rapport,$choix_period,$id_saison,$id_annee,true);
        $fichier = xml_2_csv($xml, 'engraischimiques_situation_paiement.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Pnr-1", $fichier);
        //$Myform->buildHTML();
        //echo $Myform->getHTML();
      }
    }
    if(isset($lis_ben_glob_his)){
      /*$Detail_saison=getAnneeAgricoleFromSaison($id_saison);
      $id_annee = $Detail_saison['id_annee'];
      if ($choix_period == 1){
        $date_debut = pg2phpDate($Detail_saison['date_debut']);
        $date_fin = pg2phpDate($Detail_saison['date_fin_avance']);

      }else{
        $date_debut = pg2phpDate($Detail_saison['date_debut_solde']);
        $date_fin = pg2phpDate($Detail_saison['date_fin']);
      }*/
      if (isset($csv_glob_act_his) && $csv_glob_act_his == 'Export CSV') {
        //Génération du CSV
        $xml = xml_list_beneficiaire_payant_globale_his($id_annee, $id_saison, $choix_period, $date_debut, $date_fin, true);

        if ($xml != NULL) {
          $csv_file = xml_2_csv($xml, 'engraisChimiques_listbenefpayant.xslt');

          //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
          echo getShowCSVHTML("Pnr-1", $csv_file);
        }
        else{
          $html_err = new HTML_erreur();

          //$err_msg = $error[$erreur->errCode];
          $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

          $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

          $html_err->addButton("BUTTON_OK", 'Pnr-1');

          $html_err->buildHTML();
          echo $html_err->HTML_code;
        }

      }elseif (isset($excel_glob_act_his) && $excel_glob_act_his == 'Export Excel') {
        //Génération du Excel
        $xml = xml_list_beneficiaire_payant_globale_his($id_annee, $id_saison, $choix_period, $date_debut, $date_fin, true);

        if ($xml != NULL) {
          $csv_file = xml_2_csv($xml, 'engraisChimiques_listbenefpayant.xslt');

          //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
          echo getShowEXCELHTML("Pnr-1", $csv_file);
        }
        else{
          $html_err = new HTML_erreur();

          //$err_msg = $error[$erreur->errCode];
          $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

          $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

          $html_err->addButton("BUTTON_OK", 'Pnr-1');

          $html_err->buildHTML();
          echo $html_err->HTML_code;
        }

      }  elseif (isset($pdf_glob_act_his) && $pdf_glob_act_his == 'Rapport PDF') {

        //Génération du XSL-FO (grâce au XSLT) et du PDF (grâce à FOP)
        $xml = xml_list_beneficiaire_payant_globale_his($id_annee, $id_saison, $choix_period, $date_debut, $date_fin, false);

        if ($xml != NULL) {
          $fichier_pdf = xml_2_xslfo_2_pdf($xml, 'engraisChimiques_listbenefpayant.xslt', false, 'listbenef');
          //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
          echo get_show_pdf_html("Pnr-1", $fichier_pdf);
        }
        else{
          $html_err = new HTML_erreur();

          //$err_msg = $error[$erreur->errCode];
          $err_msg = "Aucune donnée à l'agence siege. Veuillez vérifier qu'on est déjà passé dans la période des avances/soldes pour la Saison !!";

          $html_err->setMessage(sprintf("Attention : %s ", $err_msg));

          $html_err->addButton("BUTTON_OK", 'Pnr-1');

          $html_err->buildHTML();
          echo $html_err->HTML_code;
        }
      }
    }
    if(isset($rpt_zon_glob_his)){
      $Detail_saison=getAnneeAgricoleFromSaison($id_saison);
      $id_annee = $Detail_saison['id_annee'];
      if ($choix_period == 1){
        $date_debut = $Detail_saison['date_debut'];
        $date_fin = $Detail_saison['date_fin_avance'];

      }else{
        $date_debut = $Detail_saison['date_debut_solde'];
        $date_fin = $Detail_saison['date_fin'];
      }

      if (isset($pdf_glob_act_his)) {
        $xml = xml_repartition_qtite_zone_hist($id_annee, $id_saison,$date_debut,$date_fin);

        $fichier = xml_2_xslfo_2_pdf($xml, 'engraischimiques_repartition_qtite_zone.xslt',false,'qtite_zone_hist');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo get_show_pdf_html("Pnr-1", $fichier);
      }
      else if (isset($csv_glob_act_his)) {
        $xml = xml_repartition_qtite_zone_hist($id_annee, $id_saison,$date_debut,$date_fin,true);
        $fichier = xml_2_csv($xml, 'engraischimiques_repartition_qtite_zone.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowCSVHTML("Pnr-1", $fichier);
      }
      else if (isset($excel_glob_act_his)) {
        $xml = xml_repartition_qtite_zone_hist($id_annee, $id_saison,$date_debut,$date_fin,true);
        $fichier = xml_2_csv($xml, 'engraischimiques_repartition_qtite_zone.xslt');
        //Message de confirmation + affichage du rapport dans une nouvelle fenêtre
        echo getShowEXCELHTML("Pnr-1", $fichier);
      }
    }
  }
}
?>