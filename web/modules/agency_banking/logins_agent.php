<?php

/**
 * Gestion des logins
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

//Recup var globales
if (isset($id_utilisateur)) {
  $SESSION_VARS['id_utilisateur'] = $id_utilisateur;
  $SESSION_VARS['nom_utilisateur'] = get_utilisateur_nom($id_utilisateur);
}
if (isset($logins)) $SESSION_VARS['logins'] = $logins;

function get_fct_libel($libel_courant="", $profil_courant=NULL) {
  /*
    Renvoie la fonction javascript qui gère le libellé enabled en fonction du profil
  */
  $profils = get_profils_guichet(); //Renvoie un tableau contenant tous les profils ayant un guichet associé

  //$retour = "\ndocument.ADForm.HTML_GEN_LSB_cptecpta.disabled = true;\n";

  $retour = "\nfunction verif_libel(){\n";

  if (sizeof($profils)>0) {//Si au-moins un profil a un guichet
    if ($libel_courant != "") {
      $retour .= "if (document.ADForm.HTML_GEN_LSB_profil.value == $profil_courant){";
      $retour .= "document.ADForm.libelGuichet.value = '$libel_courant';";
      $retour .= "document.ADForm.libelGuichet.disabled = false;";
      $retour .= "}";
      $retour .= "else";
    }
    $retour .= "  if (";
    reset($profils);
    while (list($key, $value) = each($profils)) { //Pour chaque profil avec guichet
      $value=addslashes($value);
      $retour .= "(document.ADForm.HTML_GEN_LSB_profil.value == $value) ||";
    }
    $retour = substr($retour, 0, strlen($retour)-3); //Enlève le ' ||' final
    $retour .= "  ){\n"; //Début if : si guichet associé
    $retour .= "    document.ADForm.libelGuichet.value = '';\n";
    $retour .= "    document.ADForm.libelGuichet.disabled = false;\n";

    $retour .= "    document.ADForm.HTML_GEN_LSB_cptecpta.value = 0;\n";
    $retour .= "    document.ADForm.HTML_GEN_LSB_cptecpta.disabled = false;\n";

    $retour .= "  }\n"; //Fin du if
    $retour .= "  else{\n"; //Si aucun guichet associé
  }

  $retour .= "    document.ADForm.libelGuichet.value = '"._("Pas de guichet")."';\n";
  $retour .= "    document.ADForm.libelGuichet.disabled = true;\n";

 // $retour .= "    document.ADForm.HTML_GEN_LSB_cptecpta.value = 0;\n";
  //$retour .= "    document.ADForm.HTML_GEN_LSB_cptecpta.disabled = true;\n";

  if (sizeof($profils)>0) {//Si au-moins un profil a un guichet
    $retour .= "  }\n"; //Fin du sinon
  }
  $retour .= "}\n";//Fin function
  return $retour;
}
//Ecrans
  if ($global_nom_ecran == "Gla-1") {

    $MyPage = new HTML_GEN2(_("Gestion des codes utilisateurs agents"));

    $logins = get_logins_and_utilisateurs_agent();

    //Javascript
    $js = "function updateLogins(){\n";
    $js .= "for (i=0; i < document.ADForm.HTML_GEN_LSB_logins.length; ++i) document.ADForm.HTML_GEN_LSB_logins.options[i] = null;\n"; //Vide les choix
    $js .= "document.ADForm.HTML_GEN_LSB_logins.length = 0;";
    $js .= "document.ADForm.HTML_GEN_LSB_logins.options[document.ADForm.HTML_GEN_LSB_logins.length] = new Option('[Aucun]', 0, true, true);\n"; //[Aucun]
    $js .= "document.ADForm.HTML_GEN_LSB_logins.selectedIndex = 0; document.ADForm.HTML_GEN_LSB_logins.length = 1; \n";
    reset($logins);
    while (list(,$value) = each($logins)) {
      $login=addslashes($value['login']);
      $js .= "if (document.ADForm.HTML_GEN_LSB_id_utilisateur.value == ".$value['id_utilisateur']."){\n";
      $js .= "document.ADForm.HTML_GEN_LSB_logins.options[document.ADForm.HTML_GEN_LSB_logins.length] = new Option('".$login."', '".$login."', false, false);\n";
      $js .= "}\n";
    }
    $js .= "}\n";
    $MyPage->addJS(JSP_FORM, "js1", $js);

    //Javascript2
    $js2 = "";
    //  $js2 .= "\t document.ADForm.butCons.disabled = false;document.ADForm.butMod.disabled = false;document.ADForm.butSup.disabled = false;\n;document.ADForm.modAssoc.disabled = false;";
    $js2 .= "\t document.ADForm.butCons.disabled = false;document.ADForm.butMod.disabled = false;document.ADForm.butSup.disabled = false;\n;";
    if (check_access(288)) $js2 .= "document.ADForm.butAj.disabled = true;"; //Ajouter
    if (check_access(289)) $js2 .= "document.ADForm.butCons.disabled = true;"; //Consulter
    if (check_access(290)) $js2 .= "document.ADForm.butMod.disabled = true;"; //Modifier
    if (check_access(291)) $js2 .= "document.ADForm.butSup.disabled = true;";//Supprimer
//  if (check_access(297)) $js2 .= "document.ADForm.modAssoc.disabled = true;";//Modifier association

    $js2 .= "function activateButtons(){\n";
    $js2 .= "activate = ((document.ADForm.HTML_GEN_LSB_id_utilisateur.value != 0) && (document.ADForm.HTML_GEN_LSB_logins.value != 0));";
    $js2 .= "activate2 = (activate && (document.ADForm.HTML_GEN_LSB_logins.value != 'admin'));";
    $js2 .= "if (document.ADForm.HTML_GEN_LSB_logins.value == '$global_nom_login') activate2 = false;";

    if (check_access(288)) $js2 .= "document.ADForm.butAj.disabled = ((document.ADForm.HTML_GEN_LSB_id_utilisateur.value == 0) || (document.ADForm.HTML_GEN_LSB_id_utilisateur.value == 1));";
    if (check_access(289)) $js2 .= "document.ADForm.butCons.disabled = !activate;";
    if (check_access(290)) $js2 .= "document.ADForm.butMod.disabled = !activate2;";
    if (check_access(291)) $js2 .= "document.ADForm.butSup.disabled = !activate2;\n";
    // if (check_access(297)) $js2 .= "document.ADForm.modAssoc.disabled = !activate2;";


    //  $js2 .= "\tif (document.ADForm.HTML_GEN_LSB_logins.value == 'admin') {document.ADForm.butCons.disabled = false;document.ADForm.butMod.disabled = true;document.ADForm.butSup.disabled = true;\n;document.ADForm.modAssoc.disabled = true;} ";

    $js2 .= "\tif (document.ADForm.HTML_GEN_LSB_logins.value == 'admin') {document.ADForm.butCons.disabled = false;document.ADForm.butMod.disabled = true;document.ADForm.butSup.disabled = true;\n;} ";

    $js2 .= "}\n";
    $MyPage->addJS(JSP_FORM, "js2", $js2);

    $allUtilisateursAgent = getAllUtiAgent();

    //Champs utilisateur
    $MyPage->addField("id_utilisateur", _("Utilisateur"), TYPC_LSB);
    $MyPage->setFieldProperties("id_utilisateur", FIELDP_ADD_CHOICES, $allUtilisateursAgent);
    $MyPage->setFieldProperties("id_utilisateur", FIELDP_HAS_CHOICE_TOUS, false);
    $MyPage->setFieldProperties("id_utilisateur", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("id_utilisateur", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("id_utilisateur", FIELDP_JS_EVENT, array("onchange"=>"updateLogins();activateButtons();"));

    //Champs logins
    $MyPage->addField("logins", _("Codes utilisateur"), TYPC_LSB);
    $MyPage->setFieldProperties("logins", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("logins", FIELDP_ADD_CHOICES, array("0"=>"["._("Aucun")."]"));
    $MyPage->setFieldProperties("logins", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();"));

    //Boutons
    $MyPage->addButton("logins", "butCons", _("Consulter"), TYPB_SUBMIT);
    $MyPage->setButtonProperties("butCons", BUTP_PROCHAIN_ECRAN, "Cla-1");
    $MyPage->setButtonProperties("butCons", BUTP_AXS, 753);
    $MyPage->addButton("logins", "butMod", _("Modifier"), TYPB_SUBMIT);
    $MyPage->setButtonProperties("butMod", BUTP_PROCHAIN_ECRAN, "Mla-1");
    $MyPage->setButtonProperties("butMod", BUTP_AXS, 753);
    // $MyPage->addButton("logins", "modAssoc", _("Modifier association profil"), TYPB_SUBMIT); // FIXME:Que se passe-t-il si l'utilisateur n'avait pas de guichet et qu'on lui assigne un profil avec guichet ? -> pour l'instant, l'accès à cette fonction interdit pour cette raison
//  $MyPage->setButtonProperties("modAssoc", BUTP_PROCHAIN_ECRAN, "Mlp-1");
//  $MyPage->setButtonProperties("modAssoc", BUTP_AXS, 297);
    $MyPage->addButton("logins", "butSup", _("Supprimer"), TYPB_SUBMIT);
    $MyPage->setButtonProperties("butSup", BUTP_PROCHAIN_ECRAN, "Sla-1");
    $MyPage->setButtonProperties("butSup", BUTP_AXS, 753);

    //Break
    $MyPage->addHTMLExtraCode("break", "<br>");

    //Bouton ajouter
    $MyPage->addFormButton(1,1, "butAj", _("Créer un nouveau code utilisateur"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butAj", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butAj", BUTP_AXS, 753);
    $MyPage->setFormButtonProperties("butAj", BUTP_JS_EVENT, array("onclick"=>"if (document.ADForm.HTML_GEN_LSB_id_utilisateur.value == 0){ADFormValid = false; alert('"._("Vous devez sélectionner un utilisateur avant de pouvoir créer un code utilisateur !")."');}"));
    $MyPage->setFormButtonProperties("butAj", BUTP_PROCHAIN_ECRAN, "Ala-1");

    //Bouton retour
    $MyPage->addFormButton(2,1, "butRet", _("Retour"), TYPB_SUBMIT);
    $MyPage->setFormButtonProperties("butRet", BUTP_CHECK_FORM, false);
    $MyPage->setFormButtonProperties("butRet", BUTP_PROCHAIN_ECRAN, "Gen-16");

    //HTML
    $MyPage->buildHTML();
    echo $MyPage->getHTML();

}
else if ($global_nom_ecran == "Ala-1") {
  $MyPage = new HTML_GEN2(_("Création d'un login pour l'utilisateur")." '".$SESSION_VARS['nom_utilisateur']."'");
  // recuperation des données de l'agence'
  $AG=getAgenceDatas($global_id_agence);
  if($AG['duree_pwd']>0){ // verifier si on definit la durée de mot de passe, alors mettre le champs pwd_non_expire
      $liste_champ = array("login", "pwd_non_expire", "langue","plafond_retrait","plafond_depot","masquer_solde_client");
      $liste_champ_ord = array("idu", "nome", "login", "profil", "langue", "pwd1", "pwd2","pwd_non_expire");

  }else{
    $liste_champ = array("login", "langue","plafond_retrait","plafond_depot","masquer_solde_client");
    $liste_champ_ord = array("idu", "nome", "login", "profil", "langue", "pwd1", "pwd2");

  }
  $MyPage->addTable("ad_log", OPER_INCLUDE, $liste_champ);
  //$MyPage->setFieldProperties("have_left_frame", FIELDP_DEFAULT, true);

  //Javascript pour vérifier que le login n'existe pas encore
  $liste_login = get_logins(); //Récupère la liste des logins existants
  $i=0;
  while (list($key, $value) = each($liste_login)) {
    $value=addslashes($value);
    $MyPage->addJS(JSP_BEGIN_CHECK, "js12$i", "console.log(document.ADForm.login);if (document.ADForm.login.value == '$value'){ msg += '"._("Cet identificateur de login existe déjà !")."\\n'; ADFormValid = false;}\n");
    ++$i;
  }

  //Javascript pour vérifier que le libellé du guichet n'existe pas
  $liste_libel_guichet = get_libels_guichets(); //Récupère la liste des libellés de guichets
  $i=0;
  while (list($key, $value) = each($liste_libel_guichet)) {
    $value=addslashes($value);
    $MyPage->addJS(JSP_BEGIN_CHECK, "js40$i", "if (document.ADForm.libelGuichetAgent.value == '$value'){ msg += '"._("Ce libellé de guichet existe déjà !")."\\n'; ADFormValid = false;}\n");
    ++$i;
  }

  //Javascript pour champs profil
  $MyPage->addJS(JSP_FORM, "js11", get_fct_libel());
  $profil_agent = getProfilAgent();
  $MyPage->addField("profil", _("Profil"), TYPC_LSB);
  $MyPage->setFieldProperties("profil", FIELDP_ADD_CHOICES, $profil_agent);
  $MyPage->setFieldProperties("profil", FIELDP_HAS_CHOICE_AUCUN, true);
  $MyPage->setFieldProperties("profil", FIELDP_JS_EVENT, array("onchange"=>"verif_libel()"));
  $MyPage->setFieldProperties("profil", FIELDP_IS_REQUIRED,true);
  //Champs id_uti
  $MyPage->addField("idu", _("Identificateur utilisateur"), TYPC_INT);
  $MyPage->setFieldProperties("idu", FIELDP_DEFAULT, $SESSION_VARS['id_utilisateur']);
  $MyPage->setFieldProperties("idu", FIELDP_IS_LABEL, true);
  //Champs Nom
  $MyPage->addField("nome", _("Utilisateur"), TYPC_TXT);
  $MyPage->setFieldProperties("nome", FIELDP_DEFAULT, $SESSION_VARS['nom_utilisateur']);
  $MyPage->setFieldProperties("nome", FIELDP_IS_LABEL, true);
  //Champs mot de passe 1
  $MyPage->addField("pwd1", _("Mot de passe"), TYPC_PWD);
  $MyPage->setFieldProperties("pwd1", FIELDP_IS_REQUIRED, true);
  //Champs mot de passe 2
  $MyPage->addField("pwd2", _("Ré-entrez le mot de passe"), TYPC_PWD);
  $MyPage->setFieldProperties("pwd2", FIELDP_IS_REQUIRED, true);
  //Contrôle JS : pwd1 ?= pwd2
  //Javascript vérification mot de passe
  $js = "if (document.ADForm.pwd1.value != document.ADForm.pwd2.value) {msg += '"._("Les mots de passe doivent être identiques !")."\\n'; ADFormValid = false;}\n";
  $nbre_car_min=$AG["nbre_car_min_pwd"];
  if($nbre_car_min>0){

    $js.="if (document.ADForm.pwd1.value.length>0 && document.ADForm.pwd1.value.length <$nbre_car_min)" .
      " {msg+= '-".sprintf(_("la longueur minimale du mot de passe doit être de %s caractères !"),$nbre_car_min)."\\n'; ADFormValid = false;}";


  }
  $MyPage->addJS(JSP_BEGIN_CHECK, "js2", "if (document.ADForm.pwd1.value != document.ADForm.pwd2.value) {msg += '"._("Les mots de passe ne sont pas équivalents")."\\n'; ADFormValid = false;}");

  $MyPage->addField("libelGuichetAgent", _("Libellé de la caise de l'agent"), TYPC_TXT);
  //$MyPage->setFieldProperties("libelGuichetAgent", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("libelGuichetAgent", FIELDP_IS_REQUIRED, true);

  $MyPage->addTableRefField("cpte_flotte_agent",_("Compte de flotte de l'agent"), "ad_cpt_comptable");
  $MyPage->setFieldProperties("cpte_flotte_agent", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("cpte_flotte_agent", FIELDP_HAS_CHOICE_AUCUN, true);
  $MyPage->setFieldProperties("cpte_flotte_agent", FIELDP_IS_LABEL, false);
  if ($global_multidevise)
    $include = getNomsComptesComptables(	array(  "devise"        => NULL,
      "compart_cpte"  => 2    // Passif
    ));
  else
    $include = getNomsComptesComptables(array("compart_cpte"  => 2));   // Comptes Passif

  $MyPage->setFieldProperties("cpte_flotte_agent",FIELDP_INCLUDE_CHOICES, array_keys($include));


  $MyPage->addField("cpte_base_agent", _("Compte de base de l'agent"), TYPC_TXT);
  $MyPage->setFieldProperties("cpte_base_agent", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("cpte_base_agent", FIELDP_IS_LABEL, true);
  $MyPage->addLink("cpte_base_agent", "rechercher_cpt_cli", _("Rechercher"), "#");
  $MyPage->setLinkProperties("rechercher_cpt_cli", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=" . $_REQUEST['m_agc'] . "&choixCompte=1&cpt_dest=cpte_base_agent&id_cpt_dest=num_cpte_agent','" . _("Recherche") . "');return false;"));
  $MyPage->addHiddenType("num_cpte_agent", "");
  $checkJS .= "if (document.ADForm.num_cpte_agent.value == '')
             {
               msg += '- "._("Le champ \'Compte de base de l\'agent\' doit être renseigné")."\\n';
               ADFormValid=false;
             }";

    $MyPage->addJS(JSP_BEGIN_CHECK, "js9", $checkJS);


    $MyPage->addField("cpte_comm_agent",_("Compte de commission de l'agent"),TYPC_LSB);
  $MyPage->setFieldProperties("cpte_comm_agent", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("cpte_comm_agent", FIELDP_HAS_CHOICE_AUCUN, true);
  $array_choice_comm = array (1=>"Compte de flotte", 2=>"Compte de base");
  $MyPage->setFieldProperties("cpte_comm_agent",FIELDP_ADD_CHOICES, $array_choice_comm);


  //Ordre
  if($AG['duree_pwd']>0) {
    $MyPage->setOrder(NULL, array("idu", "nome", "login", "profil", "langue", "pwd1", "pwd2", "pwd_non_expire", "cpte_flotte_agent", "cpte_base_agent", "cpte_comm_agent", "plafond_retrait", "plafond_depot", "masquer_solde_client"));
  }else{
    $MyPage->setOrder(NULL, array("idu", "nome", "login", "profil", "langue", "pwd1", "pwd2","cpte_flotte_agent", "cpte_base_agent", "cpte_comm_agent", "plafond_retrait", "plafond_depot", "masquer_solde_client"));
  }

  //Boutons
  $MyPage->addFormButton(1,1,"butok",_("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Ala-2");
  $MyPage->addFormButton(1,2,"butno",_("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butno", BUTP_PROCHAIN_ECRAN, "Gla-1");
  $MyPage->setFormButtonProperties("butno", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
else if ($global_nom_ecran == "Ala-2") {
  if ($pwd1 != $pwd2) {
    $html_err = new HTML_erreur(_("Echec du traitement.") . " ");
    $html_err->setMessage(_("Les mots de passe ne sont pas identiques"));
    $html_err->addButton("BUTTON_OK", 'Glo-1');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();

  }

  $DATA['id_utilisateur'] = $SESSION_VARS['id_utilisateur'];
  $DATA['login'] = $login;
  $DATA['pwd'] = $pwd1;
  $DATA['profil'] = $profil;
  $profils_avec_guichet = get_profils_guichet(); //Renvoie un tableau contenant tous les profils ayant un guichet associé
  $DATA['guichet'] = in_array($profil, $profils_avec_guichet);//Présence d'un guichet ?
  $DATA['libelGuichet'] = $libelGuichetAgent;
  $DATA['date'] = php2pg(date("d/m/Y"));
  $DATA['utilis'] = $global_id_utilisateur;
  $DATA['have_left_frame'] = isset($have_left_frame);
  //$DATA['billet_req'] = isset($billet_req);
  $DATA['langue'] = $langue;
  $DATA['cptecpta_gui'] = $cpte_flotte_agent;
  $DATA['plafond_retrait'] = recupMontant($plafond_retrait);
  $DATA['plafond_depot'] = recupMontant($plafond_depot);
  if (isset($masquer_solde_client) && $masquer_solde_client == 1){
    $DATA['masquer_solde_client'] = 't';
  }else {
    $DATA['masquer_solde_client'] = 'f';
  }
  $DATA['cpte_comm_agent'] = $cpte_comm_agent;
  $DATA['cpte_base_agent'] = $num_cpte_agent;
  $DATA['cpte_flotte_agent'] = $cpte_flotte_agent;

  $retour = ajout_login_agent($DATA);

  if ($retour) {
    $MyPage = new HTML_message(_('Confirmation création login'));
    $MyPage->setMessage(sprintf(_("Le login '%s' pour l'utilisateur '%s' a été créé avec succès !"), $DATA['login'], $SESSION_VARS['nom_utilisateur']));
    $MyPage->addButton(BUTTON_OK, "Gla-1");
  } else {
    $MyPage = new HTML_erreur(_("Echec création login") . " '" . $DATA['login'] . "'");
    $MyPage->setMessage(_("Le login n'a pas été créé ! Il faut choisir un compte comptable qui n'est pas associé à un autre guichet"));
    $MyPage->addButton(BUTTON_OK, "Ala-1");
  }

  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}
else if ($global_nom_ecran == "Cla-1") {
  global $global_id_agence;
  //Consultation login
  //FIXME : vérifier si pb de fusion entre version compta et TMB
  ajout_historique(289,NULL, $SESSION_VARS['logins'], $global_nom_login, date("r"), NULL); //Consultation

  $info = get_login_full_info(addslashes($SESSION_VARS['logins']));

  $MyPage = new HTML_GEN2(_("Consultation login agent"));
  $AG =  getAgenceDatas($global_id_agence);
  if($AG['duree_pwd'] > 0){
    if (isEngraisChimiques()) {
      $liste_champ = array("login", "pwd_non_expire",   "langue","is_agent_ec", "profil","plafond_retrait","plafond_depot","masquer_solde_client");
    }else{
      $liste_champ = array("login", "pwd_non_expire",   "langue","profil","plafond_retrait","plafond_depot","masquer_solde_client");
    }
  }else{
    if (isEngraisChimiques()) {
      $liste_champ = array("login",   "langue","is_agent_ec", "plafond_retrait","plafond_depot","masquer_solde_client");
    }else{
      $liste_champ = array("login",  "langue", "profil","plafond_retrait","plafond_depot","masquer_solde_client");
    }
  }
  $MyPage->addTable('ad_log',OPER_INCLUDE,$liste_champ);

  //Champs utilisateur
  $MyPage->addField("uti", _("Utilisateur"), TYPC_TXT, $SESSION_VARS['nom_utilisateur']);
  //Champs numero du compte complet de 'agent
  $MyPage->addField("cpte_base_agent", _("Compte de base  de l'agent"), TYPC_TXT, getnumcptecompletAgent($info['cpte_base_agent']));
  //Champs compte de commission pour l'agent
  $MyPage->addField("cpte_comm_agent", _("Compte de commissions de l'agent"), TYPC_TXT, adb_gettext($adsys['cpt_comm_agent'][$info['cpte_comm_agent']]));

  //Champs guichet container
  $MyPage->addField("guic", _("Guichet"), TYPC_CNT);
  //Champs guichet
  $MyPage->addField("gui", _("Existence"), TYPC_BOL, $info['guichet']);
  $MyPage->makeNested("guic", "gui");

  //Champs compte flotte de l'agent
  $MyPage->addField("cpte_flotte_agent", _("Compte flotte de l'agent"), TYPC_TXT, getLibelCompta($info['cpte_flotte_agent']));
  $MyPage->makeNested("guic", "cpte_flotte_agent");


  $defaultVal = new FILL_HTML_GEN2();
  $defaultVal->addFillClause("log","ad_log");
  $defaultVal->addCondition("log","login",$SESSION_VARS['logins']);
  $defaultVal->addManyFillFields("log", OPER_INCLUDE, $liste_champ);

  if ($info['guichet']) {
    $MyPage->addTable('ad_gui',OPER_INCLUDE,array("date_crea","utilis_crea","date_modif","utilis_modif"));
    $defaultVal->addFillClause("gui","ad_gui");
    $defaultVal->addCondition("gui","id_gui",$info['guichet']);
    $defaultVal->addCondition("gui","id_ag",$global_id_agence);
    $defaultVal->addManyFillFields("gui", OPER_INCLUDE, array("date_crea","utilis_crea","date_modif","utilis_modif"));

    $MyPage->makeNested("guic", "date_crea");
    $MyPage->makeNested("guic", "utilis_crea");
    $MyPage->makeNested("guic", "date_modif");
    $MyPage->makeNested("guic", "utilis_modif");

    //Champs: remplace le nom du champ libelle caisse par "Libelle de la caisse de l'agent"
    $MyPage->addField("libel_gui", _("Libellé caisse de l'agent"), TYPC_TXT, getGuichetAgentFromLogin($SESSION_VARS['logins'])); //FIXME: date_enc n'est pas dans le vecteur $info
    $MyPage->makeNested("guic", "libel_gui");


    //Champs date encaisse
    $MyPage->addField("dtegui", _("Date encaisse"), TYPC_TXT, pg2phpdate($info['date_enc'])); //FIXME: date_enc n'est pas dans le vecteur $info
    $MyPage->makeNested("guic", "dtegui");

    //Montant encaisse
    $MyPage->addField("mntgui", _("Montant encaisse"), TYPC_MNT, $info['encaisse']*(-1));
    $MyPage->makeNested("guic", "mntgui");

    //Vérifier le nombre de tentative de conexion
    ($info['login_attempt'] >= 5) ? $blocked = true : $blocked = false;

    //Champs statut du login container
    $MyPage->addField("blockCNT", _("Le code utilisateur est bloqué?"), TYPC_CNT);
    //Champs statut du login (bloqué)
    $MyPage->addField("bloc", _(" "), TYPC_BOL,$blocked);
    $MyPage->makeNested("blockCNT", "bloc");
  }

  $MyPage->setFieldProperties("*", FIELDP_IS_LABEL, true);
  //Bouton
  $MyPage->addFormButton(1,1,"butret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butret", BUTP_PROCHAIN_ECRAN, "Gla-1");
  $MyPage->setFormButtonProperties("butret", BUTP_KEY, KEYB_ENTER);
  $defaultVal->fill($MyPage);
  //HTML
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
else if ($global_nom_ecran == "Mla-1"){
  global $global_id_agence;
  //Modification login
  if ($SESSION_VARS['logins'] == 'admin') {
    $html_err = new HTML_erreur(_("Refus de la modification.")." ");
    $html_err->setMessage(_("On ne peut pas modifier le login de l'administrateur."));
    $html_err->addButton("BUTTON_OK", 'Gen-12');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();

  }

  $info = get_login_full_info(addslashes($SESSION_VARS['logins']));
  $SESSION_VARS = array_merge($SESSION_VARS, $info);

  $MyPage = new HTML_GEN2(_("Modification login"));
  //recuperation des données de l'agence'
  $AG = getAgenceDatas($global_id_agence);
  //test sur la durée de vie du mot de passe
  if($AG['duree_pwd'] > 0) {
      $liste_champ = array("login", "profil", "pwd_non_expire",  "langue","plafond_retrait","plafond_depot","masquer_solde_client");
      $ordre_champ = array("profil", "login", "pwd1", "pwd2", "pwd_non_expire", "uti", "langue","plafond_retrait","plafond_depot","masquer_solde_client","cpte_base_agent","cpte_comm_agent", "guic");

  }
   else{
      $liste_champ = array("login", "profil", "langue","plafond_retrait","plafond_depot","masquer_solde_client");
      $ordre_champ = array("profil", "login", "pwd1", "pwd2",   "uti", "langue", "plafond_retrait","plafond_depot","masquer_solde_client","cpte_base_agent","cpte_comm_agent", "guic");
  }


  $MyPage->addTable('ad_log',OPER_INCLUDE,$liste_champ);
  debug($liste_champ, "liste des champs ad_log");
  //Champs utilisateur
  $MyPage->addField("uti", _("Utilisateur"), TYPC_TXT, $SESSION_VARS['nom_utilisateur']);

  //Champs mot de passe 1
  $MyPage->addField("pwd1", _("Nouveau mot de passe"), TYPC_PWD);
  //Champs mot de passe 2
  $MyPage->addField("pwd2", _("Confirmation nouveau mot de passe"), TYPC_PWD);

  // Compte de base de l'agent
  $MyPage->addField("cpte_base_agent", _("Compte de base de l'agent"), TYPC_TXT);
  $MyPage->setFieldProperties("cpte_base_agent", FIELDP_DEFAULT, getnumcptecompletAgent($info['cpte_base_agent']));
  $MyPage->setFieldProperties("cpte_base_agent", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("cpte_base_agent", FIELDP_IS_LABEL, true);
  $MyPage->addLink("cpte_base_agent", "rechercher_cpt_cli", _("Rechercher"), "#");
  $MyPage->setLinkProperties("rechercher_cpt_cli", LINKP_JS_EVENT, array("onclick" => "OpenBrw('$SERVER_NAME/modules/clients/rech_client.php?m_agc=" . $_REQUEST['m_agc'] . "&choixCompte=1&cpt_dest=cpte_base_agent&id_cpt_dest=num_cpte_agent','" . _("Recherche") . "');return false;"));
  $MyPage->addHiddenType("num_cpte_agent", "");
  $checkJS .= "if (document.ADForm.num_cpte_agent.value == '')
             {
               msg += '- "._("Le champ \'Compte agent associé\' doit être renseigné")."\\n';
               ADFormValid=false;
             }";

  $MyPage->addField("cpte_comm_agent",_("Compte de commission de l'agent"),TYPC_LSB);
  $MyPage->setFieldProperties("cpte_comm_agent", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("cpte_comm_agent",FIELDP_ADD_CHOICES, $adsys['cpt_comm_agent']);
  $MyPage->setFieldProperties("cpte_comm_agent", FIELDP_DEFAULT, $info['cpte_comm_agent']);


  //Champs guichet container
  $MyPage->addField("guic", _("Guichet"), TYPC_CNT);
  //Champs guichet
  $MyPage->addField("gui", _("Existence"), TYPC_BOL, $info['guichet']);
  $MyPage->makeNested("guic", "gui");

  //Vérifier le nombre tentative de conexion
  ($info['login_attempt'] >= 5) ? $blocked = true : $blocked = false;

  //Champs statut du login container
  $MyPage->addField("blockCNT", _("Le code utilisateur est bloqué?"), TYPC_CNT);
  //Champs statut du login (bloqué)
  $MyPage->addField("bloc", _(" "), TYPC_BOL,$blocked);
  $MyPage->makeNested("blockCNT", "bloc");

  $defaultVal = new FILL_HTML_GEN2();
  $defaultVal->addFillClause("log","ad_log");
  $defaultVal->addCondition("log","login",$SESSION_VARS['logins']);
  $defaultVal->addManyFillFields("log", OPER_INCLUDE, $liste_champ);

  if ($info['guichet']) {
    $MyPage->addTable('ad_gui',OPER_INCLUDE,array("date_crea","utilis_crea","date_modif","utilis_modif"));

    $defaultVal->addFillClause("gui","ad_gui");
    $defaultVal->addCondition("gui","id_gui",$info['guichet']);
    $defaultVal->addCondition("gui","id_ag",$global_id_agence);
    $defaultVal->addManyFillFields("gui", OPER_INCLUDE, array("date_crea","utilis_crea","date_modif","utilis_modif"));

    //Champs: remplace le nom du champ libelle caisse par "Libelle de la caisse de l'agent"
    $MyPage->addField("libel_gui", _("Libellé caisse de l'agent"), TYPC_TXT, getGuichetAgentFromLogin($SESSION_VARS['logins'])); //FIXME: date_enc n'est pas dans le vecteur $info
    $MyPage->makeNested("guic", "libel_gui");

    $MyPage->makeNested("guic", "date_crea");
    $MyPage->makeNested("guic", "utilis_crea");
    $MyPage->makeNested("guic", "date_modif");
    $MyPage->makeNested("guic", "utilis_modif");
    // Le compte comptable associé
    $liste=array();
    $comptes = getComptesComptables();
    if (isset($comptes))
      foreach($comptes as $key=>$value)
        $liste[$value["num_cpte_comptable"]]=$value["num_cpte_comptable"]." ".$value["libel_cpte_comptable"];

    $MyPage->addField("cpte_flotte_agent",_("Compte flotte de l'agent"), TYPC_LSB, $SESSION_VARS['cpte_cpta_gui']);
    $MyPage->setFieldProperties("cpte_flotte_agent", FIELDP_ADD_CHOICES, $liste);
    $MyPage->setFieldProperties("cpte_flotte_agent", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("cpte_flotte_agent", FIELDP_IS_REQUIRED, true);

    if ($global_multidevise)
      $include = getNomsComptesComptables(	array(  "devise"        => NULL,
        "compart_cpte"  => 2    // Passif
      ));
    else
      $include = getNomsComptesComptables(array("compart_cpte"  => 2));   // Comptes Passif

    $MyPage->setFieldProperties("cpte_flotte_agent",FIELDP_INCLUDE_CHOICES, array_keys($include));
    $MyPage->makeNested("guic", "cpte_flotte_agent");

  }

  // Ordre d'affichage des champs
  $MyPage->setOrder(NULL, $ordre_champ);

  //Javascript pour vérifier que le login n'existe pas encore
  $liste_login = get_logins(); //Récupère la liste des logins existants
  $i = 0;
  while (list($key, $value) = each($liste_login)) {
    ++$i;
    if ($value != $SESSION_VARS['logins']) {
      $value=addslashes($value);
      $MyPage->addJS(JSP_BEGIN_CHECK, "js12$i", "if (document.ADForm.login.value == '$value'){ msg += '"._("Cet identificateur de login existe déjà !")."\\n'; ADFormValid = false;}\n");
    }
  }

  //Javascript vérification mot de passe
  $js = "if (document.ADForm.pwd1.value != document.ADForm.pwd2.value) {msg += '"._("Les mots de passe doivent être identiques !")."\\n'; ADFormValid = false;}\n";
  $nbre_car_min=$AG["nbre_car_min_pwd"];
  if($nbre_car_min>0){

    $js.="if (document.ADForm.pwd1.value.length>0 && document.ADForm.pwd1.value.length <$nbre_car_min)" .
      " {msg+= '".sprintf(_("la longueur minimale du mot de passe doit être de %s caractères !"),$nbre_car_min)."\\n'; ADFormValid = false;}";


  }
  $MyPage->addJS(JSP_BEGIN_CHECK, "js1", $js);

  //Boutons
  $MyPage->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Mla-2");
  $MyPage->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gla-1");
  $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

  $MyPage->setFieldProperties(array("uti","profil"),FIELDP_IS_LABEL,true);
  $MyPage->setFieldProperties(array("login","langue"), 	FIELDP_IS_LABEL,false);
  if ($info['guichet'])
    $MyPage->setFieldProperties("libel_gui",					FIELDP_IS_LABEL,false);

  //HTML
  $defaultVal->fill($MyPage);
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
else if ($global_nom_ecran == "Mla-2") { //Modification login, confirmation
  //Modif login
  $DATA['guichet'] = $SESSION_VARS['guichet'];
  $DATA['libel_gui'] = $libel_gui;
  $DATA['date_modif_gui'] = date("d/m/Y");
  $DATA['utilis_modif_gui'] = $global_id_utilisateur;
  $DATA['login'] = strtolower($login);
  $DATA['cpte_flotte_agent']= $cpte_flotte_agent;
  if ($pwd1 != '')
    $DATA['pwd'] = $pwd1;
  //$DATA['billet_req'] = isset($billet_req);
  $DATA['plafond_retrait'] = recupMontant($plafond_retrait);
  $DATA['plafond_depot'] = recupMontant($plafond_depot);
  $DATA['pwd_non_expire'] = isset($pwd_non_expire);
  $DATA['cpte_comm_agent'] = $cpte_comm_agent;
  if (isset($num_cpte_agent) & $num_cpte_agent !=null) {
    $DATA['cpte_base_agent'] = $num_cpte_agent;
  }
  if (isset($masquer_solde_client)){
    $DATA['masquer_solde_client'] = 't';
  }else{
    $DATA['masquer_solde_client'] = 'f';
  }
  $DATA['langue'] = $langue;

  (!$bloc) ? $DATA['login_attempt'] = 0 : $DATA['login_attempt'] = 5;

  $retour = modif_login_agent($SESSION_VARS['logins'], $DATA);

  if ($retour < 0) {//Si erreur
    switch ($retour) {
      case -1 :
        $msg = sprintf(_("Soit le login '%s' est connecté soit le compte comptable n'est pas renseigné ou il est déjà associé à un autre guichet !"),$SESSION_VARS['logins']);
        break;
      default :
        $msg = _("Erreur inconnue")." (#{$retour}) !";
        break;
    }
    //HTML
    $MyPage = new HTML_erreur(_("Erreur modification")." '".$SESSION_VARS['logins']."'");
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Gla-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    //HTML
    $MyPage = new HTML_message(_("Confirmation modification")." '".$DATA['login']."'");
    $MyPage->setMessage(sprintf(_("Le login '%s' a été modifié avec succès !"),$DATA['login']));
    $MyPage->addButton(BUTTON_OK, "Gla-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}

else if ($global_nom_ecran == "Sla-1"){
  if ($SESSION_VARS['logins'] == 'admin') {
    $html_err = new HTML_erreur(_("Refus de la suppression.")." ");
    $html_err->setMessage(_("On ne peut pas supprimer le login de l'administrateur."));
    $html_err->addButton("BUTTON_OK", 'Gen-16');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  // Vérifie que l'encaisse est null si le login est associé à un guichet
  if (!isEncaisseNul($SESSION_VARS['logins'])) {
    $MyPage = new HTML_erreur(_("Erreur suppression login"));
    $MyPage->setMessage(sprintf(_("Le code utilisateur '%s' possède un guichet dont l'encaisse est non nulle : impossible de le supprimer !"),$SESSION_VARS['logins']));
    $MyPage->addButton(BUTTON_OK, "Gla-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    $MyPage = new HTML_message(_("Demande confirmation suppression"));
    $MyPage->setMessage(sprintf(_("Etes-vous sûr de vouloir supprimer le login '%s' ?"),$SESSION_VARS['logins']));
    $MyPage->addButton(BUTTON_OUI, "Sla-2");
    $MyPage->addButton(BUTTON_NON, "Gla-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
else if ($global_nom_ecran == "Sla-2") {
  $retour = del_login($SESSION_VARS['logins']);
  if ($retour == -1) { //Si login loggé
    $MyPage = new HTML_erreur(_("Erreur suppression") . " '" . $SESSION_VARS['logins'] . "'");
    $MyPage->setMessage(sprintf(_("Le login '%s' est loggé sur le système : vous ne pouvez pas le supprimer !"), $SESSION_VARS['logins']));
    $MyPage->addButton(BUTTON_OK, "Gla-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else if ($retour == -2) { //Si login encaisse != 0
    $MyPage = new HTML_erreur(_("Erreur suppression") . " '" . $SESSION_VARS['logins'] . "'");
    $MyPage->setMessage(sprintf(_("Le guichet associé au login '%s' possède un encaisse différent de 0 (zéro). Vous ne pouvez donc pas supprimer ce login !"), $SESSION_VARS['logins']));
    $MyPage->addButton(BUTTON_OK, "Gla-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    //HTML
    $MyPage = new HTML_message(_("Confirmation suppression") . " '" . $SESSION_VARS['logins'] . "'");
    $MyPage->setMessage(_("Le login '%s' a été supprimé avec succès !"), $SESSION_VARS['logins']);
    $MyPage->addButton(BUTTON_OK, "Gla-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
}
/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>