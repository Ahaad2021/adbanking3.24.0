  <?php

/**
 * Created by PhpStorm.
 * User: Ahaad
 * Date: 10/3/2017
 * Time: 10:30 AM
 */

require_once ('lib/misc/tableSys.php');
require_once ('lib/dbProcedures/budget.php');
require_once 'lib/dbProcedures/compta.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';
require_once "lib/html/HTML_menu_gen.php";



/*{{{ Gtc-1 : Choix du type budget */
  if ($global_nom_ecran == "Gtc-1") {

    if (isset($SESSION_VARS['type_budget'])) {
      unset($SESSION_VARS['type_budget']);
    }
    //unset($SESSION_VARS);

    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Saisie du type de budget"));

    if (!isset($type_request)) {
      $choice = array(
        1 => 'Nouvelle table de correspondance',
        2 => 'Table de correspondance exercice en cours'
      );
      $myForm->addField("type_request", _("Type de requete"), TYPC_LSB);
      $myForm->setFieldProperties('type_request', FIELDP_ADD_CHOICES, $choice);
      $myForm->setFieldProperties("type_request", FIELDP_HAS_CHOICE_TOUS, false);
      $myForm->setFieldProperties("type_request", FIELDP_HAS_CHOICE_AUCUN, true);
      $myForm->setFieldProperties("type_request", FIELDP_IS_REQUIRED, true);

      $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
      $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-1');
      $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
      $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    }

    if ($type_request == 1 || $SESSION_VARS['type_request'] == 1) {
      if(isset($type_request)){
        $SESSION_VARS['type_request'] = $type_request;
      }
      if(!isset($choix_table)) {
        $choice = array(
          1 => 'Création nouvelle table de correspondance ',
          2 => 'Utiliser une table de correspondance existante'
        );
        $myForm->addField("choix_table", _("Choix de la création"), TYPC_LSB);
        $myForm->setFieldProperties('choix_table', FIELDP_ADD_CHOICES, $choice);
        $myForm->setFieldProperties("choix_table", FIELDP_HAS_CHOICE_TOUS, false);
        $myForm->setFieldProperties("choix_table", FIELDP_HAS_CHOICE_AUCUN, true);
        $myForm->setFieldProperties("choix_table", FIELDP_IS_REQUIRED, true);
        $myForm->setFieldProperties("choix_table", FIELDP_JS_EVENT, array("onChange"=>"if(this.value == 1){assign('Gtc-7');} else {assign('Gtc-1');}"));

        $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
        $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
        //$myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-7');
        $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
        $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
      }
      else {
        $myForm = new HTML_GEN2();
        $myForm->setTitle(_("Choisir la table de correspondance"));
        if (isset($choix_table)){
          $SESSION_VARS['choix_table'] = $choix_table;
        }

        $exercice =getExoEncoursIdExo() ;
        $myForm->addField("exo_encours",_("Exercice"), TYPC_LSB);
        $myForm->setFieldProperties('exo_encours', FIELDP_ADD_CHOICES, $exercice);
        $myForm->setFieldProperties("exo_encours", FIELDP_HAS_CHOICE_TOUS, false);
        $myForm->setFieldProperties("exo_encours", FIELDP_HAS_CHOICE_AUCUN, true);
        $myForm->setFieldProperties("exo_encours", FIELDP_IS_REQUIRED, true);

        $ref_budget = getRefCorrespondanceExistant();
        $myForm->addField("ref_budget",_("Utiliser la table de correspondance"), TYPC_LSB);
        $myForm->setFieldProperties('ref_budget', FIELDP_ADD_CHOICES, $ref_budget);
        $myForm->setFieldProperties("ref_budget", FIELDP_HAS_CHOICE_TOUS, false);
        $myForm->setFieldProperties("ref_budget", FIELDP_HAS_CHOICE_AUCUN, true);
        $myForm->setFieldProperties("ref_budget", FIELDP_IS_REQUIRED, true);

        $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
        $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-7');
        $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
        $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
      }
    }
    else if ($type_request == 2){
      if(isset($type_request)){
        $SESSION_VARS['type_request'] = $type_request;
      }
      $exo_encours= getExoOuvert();

      $myForm->addField("ref_budget",_("Table de correspondance en cours"), TYPC_LSB);
      $myForm->setFieldProperties('ref_budget', FIELDP_ADD_CHOICES, $exo_encours);
      $myForm->setFieldProperties("ref_budget", FIELDP_HAS_CHOICE_TOUS, false);
      $myForm->setFieldProperties("ref_budget", FIELDP_HAS_CHOICE_AUCUN, true);
      $myForm->setFieldProperties("ref_budget", FIELDP_IS_REQUIRED, true);

      $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
      $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-2');
      $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
      $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    }

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }

/*{{{ Gtc-2 : Tableau de correspondance */
else if ($global_nom_ecran == "Gtc-2") {
  global $global_id_agence;
  $myForm = new HTML_GEN2();

  if (isset($SESSION_VARS['ref_budget']) && $SESSION_VARS['ref_budget'] != null){
    $SESSION_VARS['ref_budget'];
  }else{
    if (isset($ref_budget)){
      $SESSION_VARS['ref_budget']= $ref_budget;
    }
    else{
      //recupere id exercice en cour et creation reference (format : id_agence - id_exo - date du jour - type_budget) pour le budget
      //pour gestion de la table correspondance
      //$id_exo = getExoEnCoursAll();print_rn($id_exo);
      $ref_budget = sprintf("%03d",$global_id_agence)."-".$id_exo."-".date('d').date('m').date('Y')."-".$type_budget;
      if (isset($SESSION_VARS['choix_table']) && $SESSION_VARS['choix_table'] == 1){
        $SESSION_VARS['ref_budget']= $ref_budget;
      }
    }
  }

  //$global_nom_ecran_prec
  if ($global_nom_ecran_prec == "Gtc-7" && isset($SESSION_VARS['choix_table']) && $SESSION_VARS['choix_table'] == 1){
    // generation entre dans la table ad_budget avec un etat en attente de creation de budget ( Etat = 6)
    $checkIfBudgetExist = checkIfBudgetExist($id_exo,$type_budget);
    if ($checkIfBudgetExist === false){
      $err = insertBudgetAttente($id_exo,$type_budget,$SESSION_VARS['ref_budget']);
      if ($err->errCode != NO_ERR){
        $erreur = new HTML_erreur(_("Table de correspondance : Probleme création du budget"));
        $erreur->setMessage(_("Probleme d'entré dans la table 'ad_budget'!!!"));
        $erreur->addButton(BUTTON_OK,"Gen-15");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        exit();
      }
    }
  }

  //Type Budget
  if (isset($type_budget)){
    $SESSION_VARS['type_budget']= $type_budget;
  }else {
    $get_type_budget = getTypeBudgetFromRefBudget($SESSION_VARS['ref_budget']);
    $SESSION_VARS['type_budget'] = $get_type_budget['type_budget'];
  }
  //$type_budget = getTypeBudgetFromRefBudget($SESSION_VARS['ref_budget']);print_rn($type_budget);
  $myForm->setTitle(_("Table de correspondances : ".adb_gettext($adsys['adsys_type_budget'][$SESSION_VARS['type_budget']])));


  if (isset($SESSION_VARS['id_correspondance'])){
    unset($SESSION_VARS['id_correspondance']);
  }
  if (isset($SESSION_VARS['nb_rows'])){
    unset($SESSION_VARS['nb_rows']);
  }

  if (isset($SESSION_VARS['nbr_sous_cpte'])){
    unset($SESSION_VARS['nbr_sous_cpte']);
  }
  if (isset($SESSION_VARS['cpte_sup'])){
    unset($SESSION_VARS['cpte_sup']);
  }
  if (isset($SESSION_VARS['poste_principale'])){
    unset($SESSION_VARS['poste_principale']);
  }
  if (isset($SESSION_VARS['poste_niveau_1'])){
    unset($SESSION_VARS['poste_niveau_1']);
  }
  if (isset($SESSION_VARS['poste_niveau_2'])){
    unset($SESSION_VARS['poste_niveau_2']);
  }
  if (isset($SESSION_VARS['poste_niveau_3'])){
    unset($SESSION_VARS['poste_niveau_3']);
  }
  if (isset($SESSION_VARS['niveau_1'])){
    unset($SESSION_VARS['niveau_1']);
  }
  if (isset($SESSION_VARS['niveau_2'])){
    unset($SESSION_VARS['niveau_2']);
  }
  if (isset($SESSION_VARS['niveau_3'])){
    unset($SESSION_VARS['niveau_3']);
  }


  $myTable =& $myForm->addHTMLTable("tableau_correpondance", 7, TABLE_STYLE_ALTERN);
  $myTable->add_cell(new TABLE_cell(_("POSTE PRINCIPAL"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("SOUS POSTE NIVEAU 1"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("SOUS POSTE NIVEAU 2"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("SOUS POSTE NIVEAU 3"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("DESCRIPTION LIGNE BUDGETAIRE"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("COMPARTIMENT"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("COMPTE COMPTABLE"), 1, 1));

  $post_princ = getPostePrincipal($SESSION_VARS['ref_budget']);

  while (list(,$princ) = each($post_princ)) {
    $myTable->add_cell(new TABLE_cell_link($princ['poste_principal'],"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gtc-5&id_correspondance=".$princ['id']), 1, 1);
    $myTable->add_cell(new TABLE_cell(" - ", 1, 1));
    $myTable->add_cell(new TABLE_cell(" - ", 1, 1));
    $myTable->add_cell(new TABLE_cell(" - ", 1, 1));
    $myTable->add_cell(new TABLE_cell("<b>".$princ['description']."</b>", 1, 1));
    $myTable->add_cell(new TABLE_cell("<b>".adb_gettext($adsys['adsys_compartiment_comptable'][$princ['compartiment']])."</b>", 1, 1));

    $ComptesAssoc = getComptesComptablesAssoc($princ['id']);
    while (list(,$cpte_display) = each($ComptesAssoc)) {
      if ($cpte_display['comptable'] != null){
        $myTable->add_cell(new TABLE_cell("<b>".$cpte_display['comptable']."</b>", 1, 1));
      }else {
        $myTable->add_cell(new TABLE_cell("<b> - </b>", 1, 1));
      }
    }

    // les Sous comptes
    $sousPoste =getSousPosteTableau($princ['poste_principal'], $princ['id'],$SESSION_VARS['ref_budget']);
    while (list(,$details) = each($sousPoste)) {

      $myTable->add_cell(new TABLE_cell_link($princ['poste_principal'], "$PHP_SELF?m_agc=" . $_REQUEST['m_agc'] . "&prochain_ecran=Gtc-5&id_correspondance=" . $details['id']), 1, 1);
      if ($details['poste_niveau_1'] !=null){
        $myTable->add_cell(new TABLE_cell($details['poste_niveau_1'], 1, 1));
      }else{
        $myTable->add_cell(new TABLE_cell(" - ", 1, 1));
      }
      if ($details['poste_niveau_2'] !=null){
        $myTable->add_cell(new TABLE_cell($details['poste_niveau_2'], 1, 1));
      }else{
        $myTable->add_cell(new TABLE_cell(" - ", 1, 1));
      }
      if ($details['poste_niveau_3'] !=null){
        $myTable->add_cell(new TABLE_cell($details['poste_niveau_3'], 1, 1));
      }else{
        $myTable->add_cell(new TABLE_cell(" - ", 1, 1));
      }
      $myTable->add_cell(new TABLE_cell($details['description'], 1, 1));
      $myTable->add_cell(new TABLE_cell(adb_gettext($adsys['adsys_compartiment_comptable'][$details['compartiment']]), 1, 1));

      $ComptesAssocSousCompte = getComptesComptablesAssoc($details['id']);
      if(sizeof($ComptesAssocSousCompte)>0){
        while (list(,$Souscpte_display) = each($ComptesAssocSousCompte)) {
          $myTable->add_cell(new TABLE_cell($Souscpte_display['comptable'], 1, 1));
        }
      }else {
        $myTable->add_cell(new TABLE_cell(" - ", 1, 1));
      }
    }
  }

  $myForm->addFormButton(1, 1, "ajout_princ", _("Ajouter un poste principal"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ajout_sous", _("Ajouter un sous poste"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 4, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ajout_princ", BUTP_PROCHAIN_ECRAN, 'Gtc-3');
  $myForm->setFormButtonProperties("ajout_sous", BUTP_PROCHAIN_ECRAN, 'Gtc-4');
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Gen-15');


  $myForm->buildHTML();
  echo $myForm->getHTML();
}

/*{{{ Gtc-3 : Ajout poste principal */
else if ($global_nom_ecran == "Gtc-3") {

  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Ajout poste principal"));

  $DATA =getPostePrincipal($SESSION_VARS['ref_budget']);
  $rang1=array();
  if (is_array($DATA)) {
    foreach($DATA as $key=>$value)
      array_push($rang1, $value["poste_principal"]);
  }

  for ($i=1;$i<500;$i++)
    if (!in_array($i,$rang1))
      $choix1[$i]=sprintf("%02d",$i);

  $myForm->addField("rang", _("Rang"), TYPC_LSB);
  $myForm->setFieldProperties("rang", FIELDP_ADD_CHOICES, $choix1);
  $myForm->setFieldProperties("rang", FIELDP_HAS_CHOICE_AUCUN,false);
  $myForm->setFieldProperties("rang", FIELDP_IS_REQUIRED, true);

  $myForm->addField("description_ligne",_("Description ligne Budgetaire"), TYPC_TXT);
  $myForm->setFieldProperties("description_ligne", FIELDP_IS_REQUIRED, true);
  if (isset($description_ligne)){
    $myForm->setFieldProperties("description_ligne", FIELDP_DEFAULT, $description_ligne);
  }

  $myForm->addField("compartiment", _("Compartiment"), TYPC_LSB);

  $myForm->setFieldProperties('compartiment', FIELDP_ADD_CHOICES, $adsys["adsys_compartiment_comptable"]);
  $myForm->setFieldProperties("compartiment", FIELDP_HAS_CHOICE_TOUS, false);
  $myForm->setFieldProperties("compartiment", FIELDP_HAS_CHOICE_AUCUN, true);
  $myForm->setFieldProperties("compartiment", FIELDP_IS_REQUIRED, true);
  if (isset($compartiment)){
    $myForm->setFieldProperties("compartiment", FIELDP_DEFAULT, $compartiment);
  }
  $myForm->setFieldProperties("compartiment", FIELDP_JS_EVENT, array("onChange"=>"assign('Gtc-3'); this.form.submit();"));

  $choix = array (1 => _("Actif"), 2 => _("Inactif"));
  $myForm->addField("etat_correspondance", _("Etat correspondance")." ", TYPC_LSB);
  $myForm->setFieldProperties("etat_correspondance", FIELDP_ADD_CHOICES, $choix);
  $myForm->setFieldProperties("etat_correspondance", FIELDP_HAS_CHOICE_AUCUN, false);
  $myForm->setFieldProperties("etat_correspondance", FIELDP_IS_REQUIRED, true);
  if (isset($etat_correspondance)){ //Ticket REL-67
    $myForm->setFieldProperties("etat_correspondance", FIELDP_DEFAULT, $etat_correspondance);
  }

  $checked = false;
  $myForm->addField("check_dernier_niveau", _("Poste de dernier niveau ?"), TYPC_BOL);
  if (isset($check_dernier_niveau)){ //Ticket REL-67
    $checked = true;
  }
  $myForm->setFieldProperties("check_dernier_niveau", FIELDP_DEFAULT, $checked);

  if ($nbr_sous_cpte == NULL) {
    if ($SESSION_VARS['nbr_sous_cpte'] == NULL) {
      $SESSION_VARS['nbr_sous_cpte'] = 4;
    }
  } else {
    if ($global_nom_ecran_prec == "Gtc-3") {
      $SESSION_VARS['nbr_sous_cpte'] = $nbr_sous_cpte;
    }
  }

  $myForm->addHiddenType("nbr_sous_cpte", $SESSION_VARS['nbr_sous_cpte']);
  if (isset($compartiment)) {


    $myTable =& $myForm->addHTMLTable("plan_comptable", 2, TABLE_STYLE_ALTERN);
    $myTable->add_cell(new TABLE_cell(_("Numero"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Compte comptable"), 1, 1));


    $CC = getComptesComptablesBudget($SESSION_VARS['type_budget'],null,$compartiment,$SESSION_VARS['ref_budget']);
    for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++)  {
      $tampon = $compte['num_cpte_comptable'];
      $myTable->add_cell(new TABLE_cell($i, 1, 1));

      $cpt_comptable = "<select name=\"cpte_comptable$i\" onchange=\"verifieCompteSimilaireAjout(this, $i)\">";
      $cpt_comptable .= "<option value=\"0\">[Aucun]</option>";
      foreach($CC as $key=>$value) {
        if ($key === $ {'cpte_comptable'.$i}){
          $cpt_comptable .= "<option value=$key selected>" . $value['num_cpte_comptable'] . " " . $value['libel_cpte_comptable']  . "</option>";
        }else {
          $cpt_comptable .= "<option value=$key>" . $value['num_cpte_comptable'] . " " . $value['libel_cpte_comptable'] . "</option>";
        }
      }
      $cpt_comptable .= "</select>\n";
      $myTable->add_cell(new TABLE_cell($cpt_comptable, 1, 1));
    }
  }


  $JsCheckCompteAjout = "function verifieCompteSimilaireAjout(SelectedValue,id){";
  $JsCheckCompteAjout .= "var nbr_sous_cpte = document.getElementsByName('nbr_sous_cpte').item(0).value;";
  $JsCheckCompteAjout .= "for (var i = 1; i <= nbr_sous_cpte; i++ ){";
  $JsCheckCompteAjout .= " var ForLoopValue = document.getElementsByName('cpte_comptable'+i).item(0).value;";
  $JsCheckCompteAjout .= " if (ForLoopValue != 0 &&  ForLoopValue == SelectedValue.value && i != id ){";
  $JsCheckCompteAjout .= " alert('Vous avez choisi deux comptes similaires');";
  $JsCheckCompteAjout .= " SelectedValue.value = 0;";
  $JsCheckCompteAjout .= "}";
  $JsCheckCompteAjout .= "}";
  $JsCheckCompteAjout .="}";

  $myForm->addJS(JSP_FORM,"VerifCompteAjout",$JsCheckCompteAjout);

  //Boutons
  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ajout", _("Ajouter une ligne"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-6');
  $myForm->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, 'Gtc-3');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("ajout", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("ajout", BUTP_JS_EVENT, array("onclick"=>"nbr_sous_cpte.value++;"));

  $myForm->buildHTML();
  echo $myForm->getHTML();
}

/*{{{ Gtc-4 : Ajout sous poste */
else if ($global_nom_ecran == "Gtc-4") {

  $myForm = new HTML_GEN2();
  $myForm->setTitle(_("Ajouter un Sous Poste"));

  if (!isset($cpte_sup) && !isset($SESSION_VARS['cpte_sup'])) {
    $Where = "poste_niveau_3 is  null and type_budget=".$SESSION_VARS['type_budget']." and dernier_niveau != 't' and ref_budget = '".$SESSION_VARS['ref_budget']."'";
    $cpte_sup = getPosteSup($Where);

    $myForm->addField("cpte_sup", _("Poste Superieur"), TYPC_LSB);
    $myForm->setFieldProperties("cpte_sup", FIELDP_ADD_CHOICES, $cpte_sup);
    $myForm->setFieldProperties("cpte_sup", FIELDP_HAS_CHOICE_TOUS, false);
    $myForm->setFieldProperties("cpte_sup", FIELDP_HAS_CHOICE_AUCUN, true);
    $myForm->setFieldProperties("cpte_sup", FIELDP_IS_REQUIRED, true);


    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-4');
    $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
    $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick" => "this.form.submit();"));
  } else {
    if (isset($SESSION_VARS['cpte_sup']) && $SESSION_VARS['cpte_sup'] != null) {
      $SESSION_VARS['cpte_sup'];
    } else {
      $SESSION_VARS['cpte_sup'] = $cpte_sup;
    }

    $Where = " id = " . $SESSION_VARS['cpte_sup']." and ref_budget = '".$SESSION_VARS['ref_budget']."' ";
    $ligneData = getPosteBudget($Where);

    if (isset($ligneData['poste_niveau_2'])) {
      $SESSION_VARS['poste_niveau_2'] = $ligneData['poste_niveau_2'];
      $SESSION_VARS['poste_niveau_1'] = $ligneData['poste_niveau_1'];
      $SESSION_VARS['poste_principal'] = $ligneData['poste_principal'];
      $SESSION_VARS['compartiment'] = $ligneData['compartiment'];
      $Where = "poste_principal = " . $ligneData['poste_principal'] . " and poste_niveau_1 = " . $ligneData['poste_niveau_1'] . " and poste_niveau_2 = " . $ligneData['poste_niveau_2'] . " and poste_niveau_3 is not null and ref_budget = '".$SESSION_VARS['ref_budget']."'";
      $DATA_ligne = getRangPoste($SESSION_VARS['type_budget'], $Where);
      $rang1 = array();
      $rang2 = array();
      if (is_array($DATA_ligne)) {
        foreach ($DATA_ligne as $key => $value){
          array_push($rang1, $value["poste_niveau_3"]);
          $niveauRang = $value["poste_niveau_3"];
        }
        array_push($rang2, $niveauRang);
      }
    } else if (isset($ligneData['poste_niveau_1'])) {
      $SESSION_VARS['poste_niveau_1'] = $ligneData['poste_niveau_1'];
      $SESSION_VARS['poste_principal'] = $ligneData['poste_principal'];
      $SESSION_VARS['compartiment'] = $ligneData['compartiment'];
      $Where = "poste_principal = " . $ligneData['poste_principal'] . " and poste_niveau_1 = " . $ligneData['poste_niveau_1'] . " and poste_niveau_2 is not null and poste_niveau_3 is null and ref_budget = '".$SESSION_VARS['ref_budget']."'";
      $DATA_ligne = getRangPoste($SESSION_VARS['type_budget'], $Where);
      $rang1 = array();
      $rang2 = array();
      if (is_array($DATA_ligne)) {
        foreach ($DATA_ligne as $key => $value){
          array_push($rang1, $value["poste_niveau_2"]);
          $niveauRang = $value["poste_niveau_2"];
        }
        array_push($rang2, $niveauRang);
      }
    } else if (isset($ligneData['poste_principal'])) {
      $SESSION_VARS['poste_principal'] = $ligneData['poste_principal'];
      $SESSION_VARS['compartiment'] = $ligneData['compartiment'];
      $Where = "poste_principal = " . $ligneData['poste_principal'] . " and poste_niveau_1 is not null and poste_niveau_2 is null and poste_niveau_3 is null and ref_budget = '".$SESSION_VARS['ref_budget']."'";
      $DATA_ligne = getRangPoste($SESSION_VARS['type_budget'], $Where);
      $rang1 = array();
      $rang2 = array();
      if (is_array($DATA_ligne)) {
        foreach ($DATA_ligne as $key => $value){
          array_push($rang1, $value["poste_niveau_1"]);
          $niveauRang = $value["poste_niveau_1"];
        }
        array_push($rang2, $niveauRang);
      }
    }


    $ranking = -1;
    for ($i = 1; $i < 500; $i++) {
      if (!in_array($i, $rang1)) {
        $ranking = $i;
        $choix1[$i] = sprintf("%02d", $i);
      }
    }
    $myForm->addField("rang", _("Rang"), TYPC_LSB);
    $myForm->setFieldProperties("rang", FIELDP_ADD_CHOICES, $choix1);
    $myForm->setFieldProperties("rang", FIELDP_HAS_CHOICE_AUCUN, false);
    $myForm->setFieldProperties("rang", FIELDP_IS_REQUIRED, true);

    $checked = false;
    if (isset($ligneData['poste_niveau_2'])) {
      $ligne = $ligneData['poste_principal'] . "." . $ligneData['poste_niveau_1'] . "." . $ligneData['poste_niveau_2'] . "." . ($rang2[0] + 1);
      $checked = true; //Niveau 3 par defaut le champ poste de dernier niveau c'est coché
    } else if (isset($ligneData['poste_niveau_1'])) {
      $ligne = $ligneData['poste_principal'] . "." . $ligneData['poste_niveau_1'] . "." . ($rang2[0] + 1);
    } else if (isset($ligneData['poste_principal'])) {
      $ligne = $ligneData['poste_principal'] . "." . ($rang2[0] + 1);
    }


    $myForm->addField("ligne_budget", _("Ligne Budgetaire"), TYPC_TXT);
    $myForm->setFieldProperties("ligne_budget", FIELDP_IS_LABEL, true);
    $myForm->setFieldProperties("ligne_budget", FIELDP_DEFAULT, $ligne);
    if (isset($ligne_budget)) {
      $myForm->setFieldProperties("ligne_budget", FIELDP_DEFAULT, $ligne_budget);
    }


    $myForm->addField("description_ligne", _("Description ligne Budgetaire"), TYPC_TXT);
    $myForm->setFieldProperties("description_ligne", FIELDP_IS_REQUIRED, true);
    if (isset($description_ligne)) {
      $myForm->setFieldProperties("description_ligne", FIELDP_DEFAULT, $description_ligne);
    }

    $myForm->addField("compartiment", _("Compartiment"), TYPC_TXT);
    $myForm->setFieldProperties("compartiment", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("compartiment", FIELDP_DEFAULT, adb_gettext($adsys['adsys_compartiment_comptable'][$ligneData['compartiment']]));
    $myForm->setFieldProperties("compartiment", FIELDP_IS_LABEL, true);

    $choix = array(1 => _("Actif"), 2 => _("Inactif"));
    $myForm->addField("etat_correspondance", _("Etat correspondance") . " ", TYPC_LSB);
    $myForm->setFieldProperties("etat_correspondance", FIELDP_ADD_CHOICES, $choix);
    $myForm->setFieldProperties("etat_correspondance", FIELDP_HAS_CHOICE_AUCUN, false);
    $myForm->setFieldProperties("etat_correspondance", FIELDP_IS_REQUIRED, true);
    if (isset($etat_correspondance)){ //REL-67
      $myForm->setFieldProperties("etat_correspondance", FIELDP_DEFAULT, $etat_correspondance);
    }


    if (isset($check_dernier_niveau)){ //REL-67
      $checked = true;
    }
    $myForm->addField("check_dernier_niveau", _("Poste de dernier niveau ?"), TYPC_BOL);
    $myForm->setFieldProperties("check_dernier_niveau", FIELDP_DEFAULT, $checked);



    if ($nbr_sous_cpte == NULL) {
      if ($SESSION_VARS['nbr_sous_cpte'] == NULL) {
        $SESSION_VARS['nbr_sous_cpte'] = 4;
      }
    } else {
      if ($global_nom_ecran_prec == "Gtc-4") {
        $SESSION_VARS['nbr_sous_cpte'] = $nbr_sous_cpte;
      }
    }

    $myForm->addHiddenType("nbr_sous_cpte", $SESSION_VARS['nbr_sous_cpte']);

    $myTable =& $myForm->addHTMLTable("plan_comptable", 2, TABLE_STYLE_ALTERN);
    $myTable->add_cell(new TABLE_cell(_("Numero"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Compte comptable"), 1, 1));

    if (isset($ligneData['poste_niveau_2'])){
      $Where = "poste_niveau_3 is not null and poste_niveau_2 =".$ligneData['poste_niveau_2']." and poste_niveau_1 = ".$ligneData['poste_niveau_1']." and poste_principal =".$ligneData['poste_principal']." AND ref_budget = '".$SESSION_VARS['ref_budget']."'";
      $getIdSousPosteExist = getIdSousPoste($Where);
    } else if (isset($ligneData['poste_niveau_1'])){
      $Where = "poste_niveau_3 is null and poste_niveau_2 is not null and poste_niveau_1 = ".$ligneData['poste_niveau_1']." and poste_principal =".$ligneData['poste_principal']." AND ref_budget = '".$SESSION_VARS['ref_budget']."'";
      $getIdSousPosteExist = getIdSousPoste($Where);

    } else if (isset($ligneData['poste_principal'])){
      $Where = "poste_niveau_3 is null and poste_niveau_2 is null and poste_niveau_1 is not null and poste_principal =".$ligneData['poste_principal']." AND ref_budget = '".$SESSION_VARS['ref_budget']."'";
      $getIdSousPosteExist = getIdSousPoste($Where);
    }
    // recuperation des comptes du poste superieur
    $wherePoste = " id_ligne =".$ligneData["id"]." AND ref_budget = '".$SESSION_VARS['ref_budget']."'";
    $cptePosteSup = getSousComptePoste($wherePoste);

    $array_retrieve = array();
    if (sizeof($getIdSousPosteExist)>0){
      foreach ($getIdSousPosteExist as $key => $value) {
        $where = " id_ligne = ".$value['id'];
        $CC = getSousComptePoste($where);
        $array_retrieve = array_merge($array_retrieve,$CC);
      }
      $cptesPoste = array_diff($cptePosteSup, $array_retrieve);
    } else {
      $cptesPoste = $cptePosteSup;
    }

    if($cptesPoste == null){
      $erreur = new HTML_erreur(_("Ajout du Sous Poste du budget : ".$adsys["adsys_type_budget"][$SESSION_VARS['type_budget']]));
      $erreur->setMessage(_("Aucun compte comptable n'est disponible pour d'autre sous poste!!"));
      $erreur->addButton(BUTTON_OK,"Gtc-1");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      exit();
    }

    for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) {
      $tampon = $compte['num_cpte_comptable'];
      $myTable->add_cell(new TABLE_cell($i, 1, 1));

      $cpt_comptable = "<select name=\"cpte_comptable$i\" onchange=\"verifieCompteSimilaireAjout(this, $i)\">";
      $cpt_comptable .= "<option value=\"0\">[Aucun]</option>";

      if ($array_retrieve == null){
        $lenghtArray = sizeof($cptesPoste);
      }
      else {
        $lenghtArray = sizeof($cptePosteSup);
      }

      for ($j = 1; $j <= $lenghtArray; $j++) {
        $explode_cptesPoste = explode('-',$cptesPoste[$j]);
        if ($explode_cptesPoste[0] !=null && $explode_cptesPoste[1] !=null) {
          if ($explode_cptesPoste[0] === ${'cpte_comptable' . $i}) {
            $cpt_comptable .= "<option value=$explode_cptesPoste[0] selected>" . $explode_cptesPoste[0] . " " . $explode_cptesPoste[1] . "</option>";
          } else {
            $cpt_comptable .= "<option value=$explode_cptesPoste[0]>" . $explode_cptesPoste[0] . " " . $explode_cptesPoste[1] . "</option>";
          }
        }
      }
      $cpt_comptable .= "</select>\n";
      $myTable->add_cell(new TABLE_cell($cpt_comptable, 1, 1));
    }


    $JsCheckCompteAjout = "function verifieCompteSimilaireAjout(SelectedValue,id){";
    $JsCheckCompteAjout .= "var nbr_sous_cpte = document.getElementsByName('nbr_sous_cpte').item(0).value;";
    $JsCheckCompteAjout .= "for (var i = 1; i <= nbr_sous_cpte; i++ ){";
    $JsCheckCompteAjout .= " var ForLoopValue = document.getElementsByName('cpte_comptable'+i).item(0).value;";
    $JsCheckCompteAjout .= " if (ForLoopValue != 0 &&  ForLoopValue == SelectedValue.value && i != id ){";
    $JsCheckCompteAjout .= " alert('Vous avez choisi deux comptes similaires');";
    $JsCheckCompteAjout .= " SelectedValue.value = 0;";
    $JsCheckCompteAjout .= "}";
    $JsCheckCompteAjout .= "}";
    $JsCheckCompteAjout .="}";

    $myForm->addJS(JSP_FORM,"VerifCompteAjout",$JsCheckCompteAjout);

    //Boutons
    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "ajout", _("Ajouter une ligne"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-6');
    $myForm->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, 'Gtc-4');
    $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
    $myForm->setFormButtonProperties("ajout", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $myForm->setFormButtonProperties("ajout", BUTP_JS_EVENT, array("onclick" => "nbr_sous_cpte.value++;"));
  }

  $myForm->buildHTML();
  echo $myForm->getHTML();
}

/*{{{ Gtc-5 : Ecran de modification poste principal/sous poste */
else if ($global_nom_ecran == "Gtc-5") {



  if (isset($SESSION_VARS['id_correspondance']) && $SESSION_VARS['id_correspondance'] != null){
    $SESSION_VARS['id_correspondance'];
  }else{
    $SESSION_VARS['id_correspondance']= $_GET['id_correspondance'];
  }

  $Where = " id = " . $SESSION_VARS['id_correspondance'];
  $ligneData = getPosteBudget($Where);


  if($ligneData['poste_niveau_3']){
    $where = "poste_niveau_3 is null and poste_niveau_2 =".$ligneData['poste_niveau_2']." and poste_niveau_1 =".$ligneData['poste_niveau_1']." and poste_principal = ".$ligneData['poste_principal']." and type_budget =".$SESSION_VARS['type_budget']." and ref_budget='".$SESSION_VARS['ref_budget']."'";
    $Id_correspondanceSup = getPosteBudget($where);
    $rang_poste = $ligneData['poste_principal'].".".$ligneData['poste_niveau_1'].".".$ligneData['poste_niveau_2'].".".$ligneData['poste_niveau_3'];
  } else  if($ligneData['poste_niveau_2']){
    $where = "poste_niveau_3 is null and poste_niveau_2 is null and poste_niveau_1 =".$ligneData['poste_niveau_1']." and poste_principal = ".$ligneData['poste_principal']." and type_budget =".$SESSION_VARS['type_budget']." and ref_budget='".$SESSION_VARS['ref_budget']."'";
    $Id_correspondanceSup = getPosteBudget($where); ;
    $rang_poste = $ligneData['poste_principal'].".".$ligneData['poste_niveau_1'].".".$ligneData['poste_niveau_2'];
  } else if($ligneData['poste_niveau_1']){
    $where = "poste_niveau_3 is null and poste_niveau_2 is null and poste_niveau_1 is null and poste_principal = ".$ligneData['poste_principal']." and type_budget =".$SESSION_VARS['type_budget']." and ref_budget='".$SESSION_VARS['ref_budget']."'";
    $Id_correspondanceSup = getPosteBudget($where);
    $rang_poste = $ligneData['poste_principal'].".".$ligneData['poste_niveau_1'];
  } else  if($ligneData['poste_principal']){
    $DATA_poste_principale =getDataPostePrincipal($SESSION_VARS['id_correspondance'],$SESSION_VARS['ref_budget']);
  }


  if ($ligneData['poste_principal'] != null && $ligneData['poste_niveau_1'] ==null && $ligneData['poste_niveau_2'] ==null && $ligneData['poste_niveau_3'] ==null){

    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Modification du Poste Principal"));

    while (list(,$data_poste) = each($DATA_poste_principale)) {

      $myForm->addField("rang", _("Poste"), TYPC_TXT);
      $myForm->setFieldProperties("rang", FIELDP_IS_REQUIRED, true);
      $myForm->setFieldProperties("rang",FIELDP_DEFAULT , $data_poste["poste_principal"]);
      $myForm->setFieldProperties("rang", FIELDP_IS_LABEL, true);

      $myForm->addField("description_ligne", _("Description ligne Budgetaire"), TYPC_TXT);
      $myForm->setFieldProperties("description_ligne", FIELDP_IS_REQUIRED, true);
      $description = $data_poste["description"]; //REL-67
      if (isset($description_ligne)){
        $description = $description_ligne;
      }
      $myForm->setFieldProperties("description_ligne",FIELDP_DEFAULT , $description);

      $myForm->addField("compartiment", _("Compartiment"), TYPC_TXT);
      $myForm->setFieldProperties("compartiment", FIELDP_IS_REQUIRED, true);
      $myForm->setFieldProperties("compartiment",FIELDP_DEFAULT , adb_gettext($adsys['adsys_compartiment_comptable'][$data_poste['compartiment']]));
      $myForm->setFieldProperties("compartiment", FIELDP_IS_LABEL, true);

      if ($data_poste['etat_correspondance'] == 't'){ //REL-67
        $etat_corres = 1;
      }else{
        $etat_corres = 2;
      }
      if (isset($etat_correspondance)){ //REL-67
        $etat_corres = $etat_correspondance;
      }
      $choix = array(1 => _("Actif"), 2 => _("Inactif"));
      $myForm->addField("etat_correspondance", _("Etat correspondance") . " ", TYPC_LSB);
      $myForm->setFieldProperties("etat_correspondance", FIELDP_ADD_CHOICES, $choix);
      $myForm->setFieldProperties("etat_correspondance", FIELDP_HAS_CHOICE_AUCUN, false);
      $myForm->setFieldProperties("etat_correspondance", FIELDP_IS_REQUIRED, true);
      $myForm->setFieldProperties("etat_correspondance",FIELDP_DEFAULT, $etat_corres);

      $checked = false; //REL-67
      if ($data_poste["dernier_niveau"] == 't'){
        $checked = true;
      }
      else {
        $checked = false;
      }
      if ($check_dernier_niveau == 1){ //REL-67
        $checked = true;
      }
      $myForm->addField("check_dernier_niveau", _("Poste dernier niveau?"), TYPC_BOL);
      $myForm->setFieldProperties("check_dernier_niveau", FIELDP_DEFAULT, $checked);

      $cpte_associer = getCpteComptablesAssoc($SESSION_VARS['id_correspondance']);


      if ($nb_rows == NULL) {
        if ($SESSION_VARS['nb_rows'] == NULL) {
          $SESSION_VARS['nb_rows'] = sizeof($cpte_associer);
        }
      } else {
        if ($global_nom_ecran_prec == "Gtc-5") {
          $SESSION_VARS['nb_rows'] = $nb_rows;
        }
      }

      $myForm->addHiddenType("nb_rows", $SESSION_VARS['nb_rows']);

      $myTable =& $myForm->addHTMLTable("plan_comptable_modif", 2, TABLE_STYLE_ALTERN);
      $myTable->add_cell(new TABLE_cell(_("No. Ordre"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Compte(s) comptable"), 1, 1));

      //recuperation des compte comptable avec la function specifique du budget
      $CC = getComptesComptablesBudgetAll($SESSION_VARS['type_budget'],$SESSION_VARS['id_correspondance'],$data_poste['compartiment'],$SESSION_VARS['ref_budget']);
      $JS_disabled = "";
      for ($i = 1; $i <= $SESSION_VARS['nb_rows']; $i++)  {
        $tampon = $compte['num_cpte_comptable'];
        $myTable->add_cell(new TABLE_cell($i, 1, 1));
        $cpt_comptable = "<select name=\"nb_rows$i\" onchange=\"verifieCompteSimilaire(this, $i)\">";

        $cpt_comptable .= "<option value=\"0\">[Aucun]</option>";
        foreach($CC as $key=>$value) {

          if ($key === $ {'nb_rows'.$i}){
            $cpt_comptable .= "<option value=$key selected>" . $value['num_cpte_comptable'] . " " . $value['libel_cpte_comptable'] . "</option>";
            if ($value['is_actif'] == 'f'){
              $JS_disabled .= "document.getElementsByName('nb_rows".$i."').item(0).disabled = true;";
              //$disabled = "<input type= 'text' name='nb_rows_hidden$i' value='$key' hidden>";
              $myForm->addHiddenType("nb_rows_hidden$i", $key);
            }
          }else {
            if ($cpte_associer[$i - 1] != null && $cpte_associer[$i - 1]['cpte_comptable'] == $key) {
                $cpt_comptable .= "<option value=$key selected>" . $cpte_associer[$i - 1]['cpte_comptable'] . " " . $cpte_associer[$i - 1]['libel_cpte_comptable'] . "</option>";
              if ($value['is_actif'] == 'f'){
                $JS_disabled .= "document.getElementsByName('nb_rows".$i."').item(0).disabled = true;";
                //$disabled .= "<input type= 'text' name='nb_rows_hidden$i' value='$key' hidden>";
                $myForm->addHiddenType("nb_rows_hidden$i", $key);
              }
            } else {
              $cpt_comptable .= "<option value=$key>" . $value['num_cpte_comptable'] . " " . $value['libel_cpte_comptable'] . "</option>";
            }
          }
        }
        $cpt_comptable .= "</select>\n";
        $myTable->add_cell(new TABLE_cell($cpt_comptable, 1, 1));
        $myForm->addHiddenType("id_rows$i", $cpte_associer[$i - 1]['id']);
      }

    }
    $myForm->addJS(JSP_FORM,"disabled",$JS_disabled);
  }
  else{
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Modification du Sous Poste "));

    $myForm->addField("rang", _("Poste"), TYPC_TXT);
    $myForm->setFieldProperties("rang", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("rang",FIELDP_DEFAULT , $rang_poste);
    $myForm->setFieldProperties("rang", FIELDP_IS_LABEL, true);

    $myForm->addField("description_ligne", _("Description ligne Budgetaire"), TYPC_TXT);
    $myForm->setFieldProperties("description_ligne", FIELDP_IS_REQUIRED, true);
    $description = $ligneData["description"]; //REL-67
    if (isset($description_ligne)){
      $description = $description_ligne;
    }
    $myForm->setFieldProperties("description_ligne",FIELDP_DEFAULT ,$description);

    $myForm->addField("compartiment", _("Compartiment"), TYPC_TXT);
    $myForm->setFieldProperties("compartiment", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("compartiment",FIELDP_DEFAULT , adb_gettext($adsys['adsys_compartiment_comptable'][$ligneData['compartiment']]));
    $myForm->setFieldProperties("compartiment", FIELDP_IS_LABEL, true);

    if ($ligneData['etat_correspondance'] == 't'){ //REL-67
      $etat_corres = 1;
    }else{
      $etat_corres = 2;
    }
    if (isset($etat_correspondance)){
      $etat_corres = $etat_correspondance;
    }
    $choix = array(1 => _("Actif"), 2 => _("Inactif"));
    $myForm->addField("etat_correspondance", _("Etat correspondance") . " ", TYPC_LSB);
    $myForm->setFieldProperties("etat_correspondance", FIELDP_ADD_CHOICES, $choix);
    $myForm->setFieldProperties("etat_correspondance", FIELDP_HAS_CHOICE_AUCUN, false);
    $myForm->setFieldProperties("etat_correspondance", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("etat_correspondance",FIELDP_DEFAULT, $etat_corres);

    if ($ligneData["dernier_niveau"] == 't'){
      $checked = true;
    }
    else {
      $checked = false;
    }
    if (isset($check_dernier_niveau)){ //REL-67
      $checked = true;
    }
    $myForm->addField("check_dernier_niveau", _("Poste dernier niveau?"), TYPC_BOL);
    $myForm->setFieldProperties("check_dernier_niveau", FIELDP_DEFAULT, $checked);

    $cpte_associer = getCpteComptablesAssoc($SESSION_VARS['id_correspondance']);


    if ($nb_rows == NULL) {
      if ($SESSION_VARS['nb_rows'] == NULL) {
        $SESSION_VARS['nb_rows'] = sizeof($cpte_associer);
      }
    } else {
      if ($global_nom_ecran_prec == "Gtc-5") {
        $SESSION_VARS['nb_rows'] = $nb_rows;
      }
    }

    $myForm->addHiddenType("nb_rows", $SESSION_VARS['nb_rows']);

    $myTable =& $myForm->addHTMLTable("plan_comptable_modif", 2, TABLE_STYLE_ALTERN);
    $myTable->add_cell(new TABLE_cell(_("No. Ordre"), 1, 1));
    $myTable->add_cell(new TABLE_cell(_("Compte(s) comptable"), 1, 1));


    if (isset($ligneData['poste_niveau_3'])) {
      $Where = "poste_niveau_3 is not null and poste_niveau_2 =" . $ligneData['poste_niveau_2'] . " and poste_niveau_1 = " . $ligneData['poste_niveau_1'] . " and poste_principal =" . $ligneData['poste_principal']." and id <> ".$ligneData['id'] ." and ref_budget='".$SESSION_VARS['ref_budget']."'";
      $getIdSousPosteExist = getIdSousPoste($Where);
    }
    else if (isset($ligneData['poste_niveau_2'])){
      $Where = "poste_niveau_3 is null and poste_niveau_2 is not null and poste_niveau_1 = ".$ligneData['poste_niveau_1']." and poste_principal =".$ligneData['poste_principal']." and id <> ".$ligneData['id']." and ref_budget='".$SESSION_VARS['ref_budget']."'";
      $getIdSousPosteExist = getIdSousPoste($Where);
    } else if (isset($ligneData['poste_niveau_1'])){
      $Where = "poste_niveau_3 is null and poste_niveau_2 is null and poste_niveau_1 is not null and poste_principal =".$ligneData['poste_principal']." and id <> ".$ligneData['id']." and ref_budget='".$SESSION_VARS['ref_budget']."'";
      $getIdSousPosteExist = getIdSousPoste($Where);

    } else if (isset($ligneData['poste_principal'])){
      $Where = "poste_niveau_3 is null and poste_niveau_2 is null and poste_niveau_1 is not null and poste_principal =".$ligneData['poste_principal']." and ref_budget='".$SESSION_VARS['ref_budget']."'";
      $getIdSousPosteExist = getIdSousPoste($Where);
    }

    $SESSION_VARS['niveau_3']= $ligneData['poste_niveau_3'];
    $SESSION_VARS['niveau_2']= $ligneData['poste_niveau_2'];
    $SESSION_VARS['niveau_1']= $ligneData['poste_niveau_1'];
    $SESSION_VARS['poste_principal']= $ligneData['poste_principal'];

    // recuperation des comptes du poste superieur
    $wherePoste = " id_ligne =".$Id_correspondanceSup['id'];
    $cptePosteSup = getSousComptePoste($wherePoste);

    if (sizeof($getIdSousPosteExist)>0){
      $retrieve = array();
      foreach ($getIdSousPosteExist as $key => $value) {
        $where = " id_ligne = ".$value['id'];
        $CC = getSousComptePoste($where);
        $retrieve = array_merge($retrieve,$CC);;
      }
      $cptesPoste = array_diff($cptePosteSup, $retrieve);
    } else {
      $cptesPoste = $cptePosteSup;
    }

    $cpte_associer = getCpteComptablesAssoc($ligneData['id']);

    if ($nb_rows == NULL) {
      if ($SESSION_VARS['nb_rows'] == NULL) {
        $SESSION_VARS['nb_rows'] = sizeof($cpte_associer);
      }
    } else {
      if ($global_nom_ecran_prec == "Gtc-5") {
        $SESSION_VARS['nb_rows'] = $nb_rows;
      }
    }
    $JS_disabled_sous_poste = "";
    for ($i = 1; $i <= $SESSION_VARS['nb_rows']; $i++)  {
      $myTable->add_cell(new TABLE_cell($i, 1, 1));

      $cpt_comptable = "<select name=\"nb_rows$i\" onchange=\"verifieCompteSimilaire(this,$i)\" >";
      $cpt_comptable .= "<option value=\"0\">[Aucun]</option>";
      if ($retrieve == null){
        $lenghtArray = sizeof($cptesPoste);
      }
      else {
        $lenghtArray = sizeof($cptePosteSup);
      }

      for ($j = 1; $j <= $lenghtArray; $j++) {
        $explode_cptesPoste = explode('-', $cptesPoste[$j]);
        if ($explode_cptesPoste[0] != null && $explode_cptesPoste[1] != null) {
          if ($explode_cptesPoste[0] === ${'nb_rows' . $i}) {
            $cpt_comptable .= "<option value=$explode_cptesPoste[0]  selected>" . $explode_cptesPoste[0] . " " . $explode_cptesPoste[1] . "</option>";

          } else {
            if ($cpte_associer[$i - 1] != null && $cpte_associer[$i - 1]['cpte_comptable'] == $explode_cptesPoste[0]) {
              $data_cpte = getDataCpteComptable($cpte_associer[$i - 1]['cpte_comptable']);
              if ($data_cpte['is_actif'] == 'f'){
                $cpt_comptable .= "<option value=$explode_cptesPoste[0] selected >" . $cpte_associer[$i - 1]['cpte_comptable'] . " " . $cpte_associer[$i - 1]['libel_cpte_comptable'] . "</option>";
                $JS_disabled_sous_poste .= "document.getElementsByName('nb_rows".$i."').item(0).disabled = true;";
                $myForm->addHiddenType("nb_rows_hidden$i", $explode_cptesPoste[0]);
              }
              else {
                $cpt_comptable .= "<option value=$explode_cptesPoste[0] selected >" . $cpte_associer[$i - 1]['cpte_comptable'] . " " . $cpte_associer[$i - 1]['libel_cpte_comptable'] . "</option>";
              }
            } else {
              $cpt_comptable .= "<option value=$explode_cptesPoste[0] >" . $explode_cptesPoste[0] . " " . $explode_cptesPoste[1] . "</option>";
            }
          }
        }
      }
      $cpt_comptable .= "</select>\n";
      $myTable->add_cell(new TABLE_cell($cpt_comptable, 1, 1));
      $myForm->addHiddenType("id_rows$i", $cpte_associer[$i - 1]['id']);

    }
    $myForm->addJS(JSP_FORM,"disabled_sous_cpte",$JS_disabled_sous_poste);

  }
  $JsCheckCompte = "function verifieCompteSimilaire(SelectedValue,id){";
  $JsCheckCompte .= "var nb_rows = document.getElementsByName('nb_rows').item(0).value;";
  $JsCheckCompte .= "for (var i = 1; i <= nb_rows; i++ ){";
  $JsCheckCompte .= " var ForLoopValue = document.getElementsByName('nb_rows'+i).item(0).value;";
  $JsCheckCompte .= " if (ForLoopValue != '' &&  ForLoopValue == SelectedValue.value && i != id ){";
  $JsCheckCompte .= " alert('Vous avez choisi deux comptes similaires');";
  $JsCheckCompte .= " SelectedValue.value = 0;";
  $JsCheckCompte .= "}";
  $JsCheckCompte .= "}";
  $JsCheckCompte .="}";

  $myForm->addJS(JSP_FORM,"VerifCompte",$JsCheckCompte);



  //Boutons
  $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ajout", _("Ajouter une ligne"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 3, "cancel", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-6');
  $myForm->setFormButtonProperties("ajout", BUTP_PROCHAIN_ECRAN, 'Gtc-5');
  $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
  $myForm->setFormButtonProperties("ajout", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("ajout", BUTP_JS_EVENT, array("onclick"=>"nb_rows.value++;"));


  $myForm->buildHTML();
  echo $myForm->getHTML();
}

/*{{{ Gtc-6 : Ecran confirmation*/
else if ($global_nom_ecran == "Gtc-6") {

  if ($global_nom_ecran_prec == "Gtc-3") {
    $Where = "poste_principal =" . $rang . " and ref_budget ='" . $SESSION_VARS['ref_budget']."'";
    $check_exist = getPosteBudget($Where);
    if ($check_exist == NULL) {
      if ($etat_correspondance == 1) {
        $DATA['etat_correspondance'] = true;
      } else {
        $DATA['etat_correspondance'] = false;
      }
      $DATA['type_budget'] = $SESSION_VARS['type_budget'];
      $DATA['poste_principal'] = $rang;
      $DATA['description'] = $description_ligne;
      if (isset($check_dernier_niveau) && $check_dernier_niveau == 1){
        $dernier = 't';
      }
      else {
        $dernier = 'f';
      }

      $DATA['dernier_niveau'] = $dernier;
      $DATA['compartiment'] = $compartiment;
      $DATA['date_creation'] = date('r');
      $DATA['date_modif'] = null;
      $DATA['id_ag'] = $global_id_agence;
      if (isset($SESSION_VARS['ref_budget'])){
        $DATA['ref_budget'] = $SESSION_VARS['ref_budget'];
      }
      $insert_correspondace = InsertCorrespondance($DATA);


      $SESSION_VARS['nbr_sous_cpte'] = $nbr_sous_cpte;
      $DATA_compta = array();
      for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) {
        $cpte_donnee = ${'cpte_comptable' . $i};
        if ($cpte_donnee != 0) {
          $DATA_compta[$i]['id_ligne'] = $insert_correspondace;
          $DATA_compta[$i]['cpte_comptable'] = ${'cpte_comptable' . $i};
          $DATA_compta[$i]['etat_compte'] = true;
          $DATA_compta[$i]['date_creation'] = date('r');
          $DATA_compta[$i]['id_ag'] = $global_id_agence;
        }
      }
      if (is_array($DATA_compta)) {
        foreach ($DATA_compta as $key => $value) {
          $insert_cpte_correspondance = InsertCpteCorrespondance($DATA_compta[$key]);
          if ($insert_cpte_correspondance->errCode != NO_ERR) {
            $html_err = new HTML_erreur("Echec de l'ajout du compte associe.");

            $err_msg = $error[$erreur->errCode];

            $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

            $html_err->addButton("BUTTON_OK", 'Gtc-2');

            $html_err->buildHTML();
            echo $html_err->HTML_code;
          }
        }
      }
      $html_msg = new HTML_message("Le Poste Principale a été ajouté avec succes!!");

      $demande_msg = "Reference du Table de Correspondance : ".$SESSION_VARS['ref_budget'];
      $html_msg->setMessage(sprintf(" <br />%s!<br /> ", $demande_msg));

      $html_msg->addButton("BUTTON_OK", 'Gtc-2');

      $html_msg->buildHTML();
      echo $html_msg->HTML_code;
    }
  }

  else if ($global_nom_ecran_prec == "Gtc-5") {
    global $dbHandler, $global_id_agence;
    $db = $dbHandler->openConnection();

    $SESSION_VARS['nb_rows'] = $nb_rows;
    $DATA_compte = array();
    $DATA_suppr =array();
    $DATA_gene = array();

    $etat_corress = 'f'; //REL-67
    if ($etat_correspondance == 1){
      $etat_corress = 't';
    }
    $DATA_gene['etat_correspondance'] = $etat_corress; //REL-67
    $DATA_gene['description'] = $description_ligne;
    if (isset($check_dernier_niveau) && $check_dernier_niveau == 1){
      $dernier = 't';
    }
    else {
      $dernier = 'f';
    }

    $DATA_gene['dernier_niveau'] = $dernier;
    if (isset($SESSION_VARS['ref_budget'])){
      $reference_budget = $SESSION_VARS['ref_budget'];
    }
    if (isset($ref_budget)){
      $reference_budget = $ref_budget;
    }
    if (isset($DATA_gene)){
      $whereData = array(
        'id' => $SESSION_VARS['id_correspondance'],
        'id_ag' => $global_id_agence,
        'ref_budget' => $reference_budget
      );
      $sql = buildUpdateQuery("ad_correspondance", $DATA_gene, $whereData);
      $result = $db->query($sql);
      if (DB :: isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur(__FILE__, __LINE__, __FUNCTION__);
      }
    }

    //Mise a jour ad_ligne_budgetaire et ad_revision_historique si n'est plus dernier niveau
    if ($dernier == 'f'){
      if (verifCptesBloq($SESSION_VARS['id_correspondance'])===true){
        $erreur = new HTML_erreur(_("Modification Poste : Changement Dernier Niveau"));
        $erreur->setMessage(_("Il existe des comptes comptables bloqués pour ce poste! Veuillez debloquer ces comptes avant!"));
        $erreur->addButton(BUTTON_OK,"Gtc-2");
        $erreur->buildHTML();
        echo $erreur->HTML_code;
        exit();
      }
      /*else{
        miseAJourLigneEtRevision($SESSION_VARS['id_correspondance']);
      }*/
    }


    for ($i = 1; $i <= $SESSION_VARS['nb_rows']; $i++) {
      $cpte_donnee = ${'nb_rows' . $i};
      $cpte_donnee_hidden = ${'nb_rows_hidden' . $i};
      if ($cpte_donnee != 0 || $cpte_donnee_hidden) {
        if (${'id_rows' . $i} != null) {
          $DATA_compte[$i]['id'] = ${'id_rows' . $i};
        } else {
          $DATA_compte[$i]['id'] = null;
        }
        $DATA_compte[$i]['id_ligne'] = $SESSION_VARS['id_correspondance'];
        if (${'nb_rows_hidden' . $i} != null){
          $DATA_compte[$i]['cpte_comptable'] = ${'nb_rows_hidden' . $i};
        }else{
          $DATA_compte[$i]['cpte_comptable'] = ${'nb_rows' . $i};
        }
        $DATA_compte[$i]['etat_compte'] = true;
        $DATA_compte[$i]['date_creation'] = date("d/m/Y");
        $DATA_compte[$i]['id_ag'] = $global_id_agence;
      }
      else {
        $DATA_suppr[$i]['id'] = ${'id_rows' . $i};
        $DATA_suppr[$i]['id_ligne'] = $SESSION_VARS['id_correspondance'];
        if (isset($SESSION_VARS['niveau_3'])){
          $DATA_suppr[$i]['id'] = ${'id_rows' . $i};

        }
        else if (isset($SESSION_VARS['niveau_2'])){
          $Where = "poste_niveau_3 is not null and poste_niveau_2 =".$SESSION_VARS['niveau_2']." and poste_niveau_1 = ".$SESSION_VARS['niveau_1']." and poste_principal =".$SESSION_VARS['poste_principal'];
          $getIdSousPosteExist = getIdSousPoste($Where);
        } else if (isset($SESSION_VARS['niveau_1'])){
          $Where = "poste_niveau_3 is null and poste_niveau_2 is not null and poste_niveau_1 = ".$SESSION_VARS['niveau_1']." and poste_principal =".$SESSION_VARS['poste_principal'];
          $getIdSousPosteExist = getIdSousPoste($Where);

        } else if (isset($SESSION_VARS['poste_principal'])){
          $Where = "poste_niveau_3 is null and poste_niveau_2 is null and poste_niveau_1 is not null and poste_principal =".$SESSION_VARS['poste_principal'];
          $getIdSousPosteExist = getIdSousPoste($Where);
        }

        if (sizeof($getIdSousPosteExist)>0) {
          $array_ckech = array();
          foreach($DATA_suppr as $cle =>$valeur) {
            if ($valeur['id'] != null) {
              foreach ($getIdSousPosteExist as $key => $value) {
                $checkIfExist = checkIfSousCompteAssocierExistSupression($value['id'], $valeur['id']);
                $array_ckech = array_merge($array_ckech, $checkIfExist);
              }
            }
          }
          if (sizeof($array_ckech) > 0){
            $html_err = new HTML_erreur(_("Erreur de modification"));
            $html_err->setMessage(_("Erreur")." : La ligne budgetaire ne peut pas etre modifier car il existe des sous comptes associes");
            $html_err->addButton("BUTTON_OK", 'Gtc-2');
            $html_err->buildHTML();
            echo $html_err->HTML_code;
            exit();
          }
        }

      }
    }

    if (is_array($DATA_compte) && $DATA_compte != null) {
      foreach ($DATA_compte as $key => $value) {
        if ($DATA_compte[$key]['id']  != null){
          $Where = "id = ".$DATA_compte[$key]['id'] ;
          $checkcpteExist = checkCompteExist($Where);
          if ($checkcpteExist['cpte_comptable'] != $DATA_compte[$key]['cpte_comptable']){
            $whereData = array(
              'id' => $DATA_compte[$key]['id'],
              'id_ag' => $global_id_agence
            );
            unset($DATA_compte[$key]['id']);
            $sql = buildUpdateQuery("ad_budget_cpte_comptable", $DATA_compte[$key], $whereData);
            $result = $db->query($sql);
            if (DB :: isError($result)) {
              $dbHandler->closeConnection(false);
              signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
          }
        }
        else if($DATA_compte[$key]['id']  == null) {
          unset($DATA_compte[$key]['id']);
          $sql = buildInsertQuery ("ad_budget_cpte_comptable", $DATA_compte[$key]);
          $result = $db->query($sql);
          if (DB :: isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur(__FILE__, __LINE__, __FUNCTION__);
          }
        }
      }
    }
    if (is_array($DATA_suppr) && $DATA_suppr != null) {
      foreach ($DATA_suppr as $key => $value) {
        if ($value['id'] != null) {
        $delete_ligne =deleteCompteComptableAssocie($value['id_ligne'], $value['id']);
          $where = "id_ligne = " . $value['id_ligne'];
          //$checkIfEmpty = checkCompteExist($where);
          $checkIfExist =checkCompte($where);
          if ($checkIfExist == null) {
            $deleteLigne = deleteLigneBudgetaireNull($value['id_ligne']);
          }
        }
      }

    }

    $dbHandler->closeConnection(true);
    $myMsg = new HTML_message(_("Confirmation de la modification du Poste"));
    $msg = _("La modification du Poste a été faite avec succès");
    $myMsg->setMessage($msg);

    $myMsg->addButton(BUTTON_OK, 'Gtc-2');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  }

  else if ($global_nom_ecran_prec == "Gtc-4") {

    if ($etat_correspondance == 1) {
      $DATA['etat_correspondance'] = true;
    } else {
      $DATA['etat_correspondance'] = false;
    }
    $DATA['type_budget'] = $SESSION_VARS['type_budget'];
    $DATA['poste_principal'] = $SESSION_VARS['poste_principal'];
    if (isset($SESSION_VARS['poste_niveau_2'])){
      $DATA['poste_niveau_2'] = $SESSION_VARS['poste_niveau_2'];
      $DATA['poste_niveau_1'] = $SESSION_VARS['poste_niveau_1'];
      $DATA['poste_principal'] = $SESSION_VARS['poste_principal'];
      $DATA['poste_niveau_3'] = $rang;
    }else if (isset($SESSION_VARS['poste_niveau_1'])){
      $DATA['poste_niveau_1'] = $SESSION_VARS['poste_niveau_1'];
      $DATA['poste_principal'] = $SESSION_VARS['poste_principal'];
      $DATA['poste_niveau_2'] = $rang;
    }else if (isset($SESSION_VARS['poste_principal'])){
      $DATA['poste_principal'] = $SESSION_VARS['poste_principal'];
      $DATA['poste_niveau_1'] = $rang;
    }
    $DATA['description'] = $description_ligne;
    $DATA['compartiment'] = $SESSION_VARS['compartiment'];
    if (isset($check_dernier_niveau) && $check_dernier_niveau == 1){
      $DATA['dernier_niveau'] = 't';
    }
    else {
      $DATA['dernier_niveau'] = 'f';
    }

    $DATA['date_creation'] = date('r');
    $DATA['date_modif'] = null;
    $DATA['id_ag'] = $global_id_agence;
    if (isset($SESSION_VARS['ref_budget'])){
      $DATA['ref_budget'] = $SESSION_VARS['ref_budget'];
    }
    $insert_correspondace = InsertCorrespondance($DATA);

    $SESSION_VARS['nbr_sous_cpte'] = $nbr_sous_cpte;
    $DATA_compta = array();
    for ($i = 1; $i <= $SESSION_VARS['nbr_sous_cpte']; $i++) {
      $cpte_donnee = ${'cpte_comptable' . $i};
      if ($cpte_donnee != 0) {
        $DATA_compta[$i]['id_ligne'] = $insert_correspondace;
        $DATA_compta[$i]['cpte_comptable'] = ${'cpte_comptable' . $i};
        $DATA_compta[$i]['etat_compte'] = true;
        $DATA_compta[$i]['date_creation'] = date('r');
        $DATA_compta[$i]['id_ag'] = $global_id_agence;
      }
    }
    if (is_array($DATA_compta)) {
      foreach ($DATA_compta as $key => $value) {
        $insert_cpte_correspondance = InsertCpteCorrespondance($DATA_compta[$key]);
        if ($insert_cpte_correspondance->errCode != NO_ERR) {
          $html_err = new HTML_erreur("Echec de l'ajout du compte associe.");

          $err_msg = $error[$erreur->errCode];

          $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

          $html_err->addButton("BUTTON_OK", 'Gtc-2');

          $html_err->buildHTML();
          echo $html_err->HTML_code;
        }
      }
    }
    $html_msg = new HTML_message("Le Sous Poste a été ajouté avec succes!");

    $demande_msg = "Reference du Table de Correspondance est : ".$SESSION_VARS['ref_budget'];
    $html_msg->setMessage(sprintf(" <br />%s!<br /> ", $demande_msg));

    $html_msg->addButton("BUTTON_OK", 'Gtc-2');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;

  }
}

else if ($global_nom_ecran == "Gtc-7"){

    if ($global_nom_ecran_prec == "Gtc-1" && $SESSION_VARS['choix_table'] == 2){
    $ref_budget_explode = explode('-',$ref_budget);

    $checkIfBudgetExist = checkIfBudgetExist($exo_encours,$ref_budget_explode[3]);
    if ($checkIfBudgetExist == true){
      $erreur = new HTML_erreur(_("Table de correspondance déja existant"));
      $erreur->setMessage(_("La table de correspondance pour l'année selectionnée existe déja!!!"));
      $erreur->addButton(BUTTON_OK,"Gen-15");
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      $ok = false;
    }
    else{
      // generation entre dans la table ad_budget avec un etat en attente de creation de budget ( Etat = 5)
      $err = insertBudgetAttente($exo_encours,$ref_budget_explode[3]);
      if ($err->errCode == NO_ERR){
        $ref_budget_new = $err->param["ref_budget"];
        // lancement de la fonction de replication des donnees d'une table de correspondance
        $create_duplication = duplicationTableCorrespondanceExistant($ref_budget,$ref_budget_explode[3],$ref_budget_explode[3],$ref_budget_new,$exo_encours);
        if ($create_duplication->errCode == NO_ERR){
          $myMsg = new HTML_message(_("Confirmation de la réplication de la table de correspondance"));
          $msg = _("La nouvelle table de correspondance a été créée avec succès.");
          $myMsg->setMessage($msg);

          $myMsg->addButton(BUTTON_OK, 'Gen-15');
          $myMsg->buildHTML();
          echo $myMsg->HTML_code;
        }
      }
      else{
        $html_err = new HTML_erreur("Echec de la replication de la table de correspondance.");

        $err_msg = $error[$erreur->errCode];

        $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

        $html_err->addButton("BUTTON_OK", 'Gen-15');

        $html_err->buildHTML();
        echo $html_err->HTML_code;
      }

    }
  }
  else if (($global_nom_ecran_prec == "Gtc-1" && $choix_table == 1) || isset($id_exo) || empty($id_exo)) {
    if (isset($SESSION_VARS['type_budget'])){
      unset($SESSION_VARS['type_budget']);
    }
    //unset($SESSION_VARS);
    if (isset($choix_table)){
      $SESSION_VARS["choix_table"] = $choix_table;
    }
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Saisie du Exercice/Type de Budget"));

    //$exo_encours= getExoEnCours();
    $exo_encours = getExoEnCoursAll();
    $exo_encours_all = array();
    if ($exo_encours != null){
      foreach($exo_encours as $key => $value){
        $exo_encours_all[$key] = $value["debut_annee"]." - ".$value["debut_annee"];
      }
    }
    $liste_type_budget = getAllExoOuvertWithBudgetAvailable(" > 2");

    $myForm->addField("id_exo",_("Exercice(s) en cours"), TYPC_LSB);
    $myForm->setFieldProperties('id_exo', FIELDP_ADD_CHOICES, $exo_encours_all);
    $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_TOUS, false);
    $myForm->setFieldProperties("id_exo", FIELDP_HAS_CHOICE_AUCUN, true);
    $myForm->setFieldProperties("id_exo", FIELDP_IS_REQUIRED, true);
    $myForm->setFieldProperties("id_exo", FIELDP_JS_EVENT, array("onChange"=>"assign('Gtc-7'); this.form.submit();"));
    if (isset($id_exo)){
      $myForm->setFieldProperties("id_exo", FIELDP_DEFAULT, $id_exo);
    }

    //$adsys_type_budget = $adsys["adsys_type_budget"];

    if (isset($id_exo) && $liste_type_budget != null){
      //filtrer type budget
      foreach($liste_type_budget as $exo => $value_exo){
        if ($exo == $id_exo){
          if (sizeof($value_exo)>0){
            $adsys_type_budget = $value_exo;
          }
          else{
            $adsys_type_budget = null;
          }
          break;
        }
        else{
          $adsys_type_budget = $adsys["adsys_type_budget"];
        }
      }
    }
    else{
      $adsys_type_budget = null;
    }

    $myForm->addField("type_budget", _("Type de budget"), TYPC_LSB);
    $myForm->setFieldProperties('type_budget', FIELDP_ADD_CHOICES, $adsys_type_budget);
    $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_TOUS, false);
    $myForm->setFieldProperties("type_budget", FIELDP_HAS_CHOICE_AUCUN, true);
    $myForm->setFieldProperties("type_budget", FIELDP_IS_REQUIRED, true);

    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "cancel", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Gtc-2');
    $myForm->setFormButtonProperties("cancel", BUTP_PROCHAIN_ECRAN, 'Gen-15');
    $myForm->setFormButtonProperties("cancel", BUTP_CHECK_FORM, false);
    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
}
?>