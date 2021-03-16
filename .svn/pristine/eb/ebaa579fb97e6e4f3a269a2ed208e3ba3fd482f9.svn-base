<?php
/**
 * Gestion des utilisateurs
 * @package Parametrage
 **/
require_once 'lib/dbProcedures/utilisateurs.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/misc/divers.php';

global $global_id_profil;

function get_fct_libel() {
  /*
    Renvoie la fonction javascript qui gère le libellé enabled en fonction du profil
  */
  $profils = get_profils_guichet(); //Renvoie un tableau contenant tous les profils ayant un guichet associé

  $retour = "\nfunction verif_libel(){\n";
  if (sizeof($profils)>0) {//Si au-moins un profil a un guichet
    $retour .= "  if (";
    reset($profils);
    while (list($key, $value) = each($profils)) { //Pour chaque profil avec guichet
      $retour .= "(document.ADForm.profil.value == $value) ||";
    }
    $retour = substr($retour, 0, strlen($retour)-3); //Enlève le ' ||' final
    $retour .= "  ){\n"; //Début if : si guichet associé
    $retour .= "    document.ADForm.libelGuichet.value = '';\n";
    $retour .= "    document.ADForm.libelGuichet.disabled = false;\n";
    $retour .= "  }\n"; //Fin du if
    $retour .= "  else{\n"; //Si aucun guichet associé
  }

  $retour .= "    document.ADForm.libelGuichet.value = 'Pas de guichet';\n";
  $retour .= "    document.ADForm.libelGuichet.disabled = true;\n";

  if (sizeof($profils)>0) {//Si au-moins un profil a un guichet
    $retour .= "  }\n"; //Fin du sinon
  }
  $retour .= "}\n";//Fin function
  return $retour;
}

if ($global_nom_ecran == "Gus-1") { //Menu principal gestion des utilisateurs
  $MyPage = new HTML_GEN2(_("Gestion des utilisateurs"));
  //Javascript
  $js = "document.ADForm.consult.disabled = true; document.ADForm.modif.disabled = true; document.ADForm.supr.disabled = true;\n";
  $js .= "function activateButtons(){\n";
  $js .= "activate = (document.ADForm.HTML_GEN_LSB_utilisateur.value != 0);";
  $js .= "activate2 = (activate && (document.ADForm.HTML_GEN_LSB_utilisateur.value != 1));";
  $js .= "document.ADForm.consult.disabled = !activate; document.ADForm.modif.disabled = !activate2; document.ADForm.supr.disabled = !activate2;";
  $js .= "}\n";
  $MyPage->addJS(JSP_FORM, "js", $js);

  //Champs utilisateur
  $MyPage->addTable("ad_uti", OPER_INCLUDE, array("utilis_crea"));
  $MyPage->setFieldProperties("utilis_crea", FIELDP_SHORT_NAME, "utilisateur");
  $MyPage->setFieldProperties("utilisateur", FIELDP_LONG_NAME, "Utilisateur");
  $MyPage->setFieldProperties("utilisateur", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();"));

  //Bouton consulter
  $MyPage->addButton("utilisateur", "consult", _("Consulter"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("consult", BUTP_AXS, 271);
  $MyPage->setButtonProperties("consult", BUTP_PROCHAIN_ECRAN, "Cus-1");

  //Bouton modifier
  $MyPage->addButton("utilisateur", "modif", _("Modifier"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("modif", BUTP_AXS, 272);
  $MyPage->setButtonProperties("modif", BUTP_PROCHAIN_ECRAN, "Mus-1");

  //Bouton supprimer
  $MyPage->addButton("utilisateur", "supr", _("Supprimer"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("supr", BUTP_AXS, 273);
  $MyPage->setButtonProperties("supr", BUTP_PROCHAIN_ECRAN, "Sus-1");

  //Bouton créer
  $MyPage->addFormButton(1, 1, "cree", _("Créer un nouvel utilisateur"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("cree", BUTP_AXS, 270);
  $MyPage->setFormButtonProperties("cree", BUTP_PROCHAIN_ECRAN, "Aus-1");
  $MyPage->setFormButtonProperties("cree", BUTP_CHECK_FORM, false);

  //Bouton Afficher tous les utilisateurs

  $MyPage->addFormButton(2, 1, "affich", _("Afficher les utilisateur"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("affich", BUTP_AXS, 278);
  $MyPage->setFormButtonProperties("affich", BUTP_PROCHAIN_ECRAN, "Lus-1");
  $MyPage->setFormButtonProperties("affich", BUTP_CHECK_FORM, false);

  //Bouton retour
  $MyPage->addFormButton(3,1, "ret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, "Gen-12");
  $MyPage->setFormButtonProperties("ret", BUTP_CHECK_FORM, false);
  $MyPage->buildHTML();

  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Aus-1") { //Saisie informations personnelles de l'utilisateur

  $MyPage = new HTML_GEN2(_("Ajout utilisateur"));
  if (isMobileLending()){
      $MyPage->addTable("ad_uti", OPER_INCLUDE, array("id_utilis", "nom", "prenom", "date_naiss", "lieu_naiss", "sexe", "type_piece_id", "num_piece_id", "adresse", "tel", "date_crea", "statut","ml_code_agent","is_agent_ml"));
  }else{
      $MyPage->addTable("ad_uti", OPER_INCLUDE, array("id_utilis", "nom", "prenom", "date_naiss", "lieu_naiss", "sexe", "type_piece_id", "num_piece_id", "adresse", "tel", "date_crea", "statut"));
  }


  //Propriétés champs id_utilis
  $SESSION_VARS['id_utilis'] = get_utilisateur_id();
  $MyPage->setFieldProperties("id_utilis", FIELDP_DEFAULT, $SESSION_VARS['id_utilis']);
  $MyPage->setFieldProperties("id_utilis", FIELDP_IS_LABEL, true);
  //Propriétés champs date_crea
  $MyPage->setFieldProperties("date_crea", FIELDP_DEFAULT, date("d/m/Y"));
  $MyPage->setFieldProperties("date_crea", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_crea", FIELDP_HAS_CALEND, false);
  //Propriétés champs statut
  $MyPage->setFieldProperties("statut", FIELDP_DEFAULT, 1);
  $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);
  //L'utilisateur est-il gestionnaire ?
  $choix = array (1 => _("Non"), 2 => _("Oui"));
  $MyPage->addField("is_gestionnaire", _("L'utilisateur est-il gestionnaire ?")." ", TYPC_LSB);
  $MyPage->setFieldProperties("is_gestionnaire", FIELDP_ADD_CHOICES, $choix);
  $MyPage->setFieldProperties("is_gestionnaire", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("is_gestionnaire", FIELDP_IS_REQUIRED, true);

  //Ajout d'un hidden Field pour la validation des piece d'identité
  $MyPage->addHiddenType("char_length_hidden");

  //affectation de la variable du nombre de charactere du type de piece choisi dans le hidden field
  $MyPage->setFieldProperties("type_piece_id", FIELDP_JS_EVENT, array("onchange"=>"getCharLength()"));

  //liste des pièces d'identité et leurs nombre de caractères
  $listPieceIdentLen=getListPieceIdentLength();

  //Fonction JavaScript pour trouver le type de pièce d'identité choisie et le nombre de caractères correspondant
  $js1 = "function lookup( name , arr)
            {
              for(var i = 0, len = arr.length; i < len; i++)
              {
                  if( arr[ i ].key == name )
                  {
                  return arr[ i ].value;
                  }
              }
              return false;
            };\n ";

    $js1 .= "function getCharLength(){ \n var myArray = [\n";

    //fonction qui construit un tableau en javascript contenant les pièces d'identité et leurs nombre de caractères respectifs.
    foreach($listPieceIdentLen as $key=>$value)
    {
        $js1 .= "{ key: $key, value: $value },";
    }

    $js1 .= "];\n";
    $js1 .= " document.ADForm.char_length_hidden.value='';\n";
    $js1 .= " if( lookup(document.ADForm.HTML_GEN_LSB_type_piece_id.value, myArray ) != false ) { \n";
    $js1 .= "document.ADForm.char_length_hidden.value = lookup(document.ADForm.HTML_GEN_LSB_type_piece_id.value, myArray );\n}";
    $js1 .= "};\n";

    $MyPage->addJS(JSP_FORM, "js", $js1);

  //Validation du nombre de caractères des pièces d'identité
  $jss = "";
  $jss .= "if (document.ADForm.char_length_hidden.value != 0 && (document.ADForm.char_length_hidden.value != '' && document.ADForm.num_piece_id.value.length != document.ADForm.char_length_hidden.value))
                {
                    msg += '"._("- le nombre de caractères ne conrespond pas à ")."';
                    msg += document.ADForm.char_length_hidden.value
                    msg += '"._(" caractères ")."\\n';
                    ADFormValid = false;
                    }";

  $MyPage->addJS(JSP_BEGIN_CHECK, "js8",$jss);


  //Boutons
  $MyPage->addFormButton(1, 1, "butvalid", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butvalid", BUTP_PROCHAIN_ECRAN, "Aus-3");
  $MyPage->addFormButton(1, 2, "butannul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butannul", BUTP_PROCHAIN_ECRAN, "Gus-1");
  $MyPage->setFormButtonProperties("butannul", BUTP_CHECK_FORM, false);

  // Ordre d'affichage des champs
  if (isMobileLending()){
      $MyPage->setOrder(NULL, array("id_utilis","date_crea","statut","nom","prenom","sexe","date_naiss","lieu_naiss","adresse","tel","type_piece_id","num_piece_id", "ml_code_agent", "is_agent_ml"));
  }else{
      $MyPage->setOrder(NULL, array("id_utilis","date_crea","statut","nom","prenom","sexe","date_naiss","lieu_naiss","adresse","tel","type_piece_id","num_piece_id"));
  }

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Aus-2") { //Saisie informations du login associé à l'utilisateur
  //Récupère informations de l'écran précédent

  $MyPage = new HTML_GEN2(_("Création du login pour l'utilisateur")." '".$SESSION_VARS['prenom']." ".$SESSION_VARS['nom']."'");
  $MyPage->addTable("ad_log", OPER_INCLUDE, array("login", "profil"));

  //Javascript pour vérifier que le login n'existe pas encore
  $liste_login = get_logins(); //Récupère la liste des logins existants
  $i=0;
  while (list($key, $value) = each($liste_login)) {
    $MyPage->addJS(JSP_BEGIN_CHECK, "js12$i", "if (document.ADForm.login.value == '$value'){ msg += '"._("Cet identificateur de login existe déjà !")."\\n'; ADFormValid = false;}\n");
    ++$i;
  }
  //Javascript pour champs profil
  $MyPage->addJS(JSP_FORM, "js11", get_fct_libel());
  $MyPage->setFieldProperties("profil", FIELDP_JS_EVENT, array("onchange"=>"verif_libel()"));
  //Champs id_uti
  $MyPage->addField("idu", _("Identificateur utilisateur"), TYPC_INT);
  $MyPage->setFieldProperties("idu", FIELDP_DEFAULT, $SESSION_VARS['id_utilis']);
  $MyPage->setFieldProperties("idu", FIELDP_IS_LABEL, true);
  //Champs Nom
  $MyPage->addField("nome", _("Nom utilisateur"), TYPC_TXT);
  $MyPage->setFieldProperties("nome", FIELDP_DEFAULT, $SESSION_VARS['nom']);
  $MyPage->setFieldProperties("nome", FIELDP_IS_LABEL, true);
  //Champs Prénom
  $MyPage->addField("pre", _("Prénom utilisateur"), TYPC_TXT);
  $MyPage->setFieldProperties("pre", FIELDP_DEFAULT, $SESSION_VARS['prenom']);
  $MyPage->setFieldProperties("pre", FIELDP_IS_LABEL, true);
  //Champs mot de passe 1
  $MyPage->addField("pwd1", _("Mot de passe"), TYPC_PWD);
  $MyPage->setFieldProperties("pwd1", FIELDP_IS_REQUIRED, true);
  //Champs mot de passe 2
  $MyPage->addField("pwd2", _("Ré-entrez le mot de passe"), TYPC_PWD);
  $MyPage->setFieldProperties("pwd2", FIELDP_IS_REQUIRED, true);
  //Contrôle JS : pwd1 ?= pwd2
  $MyPage->addJS(JSP_BEGIN_CHECK, "js2", "if (document.ADForm.pwd1.value != document.ADForm.pwd2.value) {msg += '"._("Les mots de passe ne sont pas équivalents")."\\n'; ADFormValid = false;}");
  //Champs guichet
  $MyPage->addField("libelGuichet", _("Libellé du guichet"), TYPC_TXT);
  $MyPage->setFieldProperties("libelGuichet", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("libelGuichet", FIELDP_DEFAULT, "Pas de guichet");
  $MyPage->setFieldProperties("libelGuichet", FIELDP_IS_REQUIRED, true);

  //Ordre
  $MyPage->setOrder(NULL, array("idu", "nome", "pre", "login", "profil", "pwd1", "pwd2"));

  //Boutons
  $MyPage->addFormButton(1,1,"butok",_("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Aus-3");
  $MyPage->addFormButton(1,2,"butno",_("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butno", BUTP_PROCHAIN_ECRAN, "Gus-1");
  $MyPage->setFormButtonProperties("butno", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Aus-3") { //Ecran confirmation
  //Construction du tableau contenant toutes les infos sur l'utilisateur
  $SESSION_VARS['nom'] = $nom;
  $SESSION_VARS['prenom'] = $prenom;
  $SESSION_VARS['date_naiss'] = $date_naiss;
  $SESSION_VARS['lieu_naiss'] = $lieu_naiss;
  $SESSION_VARS['sexe'] = $sexe;
  $SESSION_VARS['type_piece_id'] = $type_piece_id;
  $SESSION_VARS['num_piece_id'] = $num_piece_id;
  $SESSION_VARS['adresse'] = $adresse;
  $SESSION_VARS['tel'] = $tel;
  $SESSION_VARS['date_crea'] = date("d/m/Y");
  $SESSION_VARS['utilis_crea'] = $global_id_utilisateur;
  $SESSION_VARS['date_modif'] =  date("d/m/Y");
  $SESSION_VARS['utilis_modif'] = $global_id_utilisateur;
  $SESSION_VARS['statut'] = 1;
  if (isMobileLending()){
      if (!isset($is_agent_ml)){
          $SESSION_VARS['is_agent_ml'] = "false";
      }else {
          $SESSION_VARS['is_agent_ml'] = $is_agent_ml?"true":"false";
      }
      if ($ml_code_agent == null){
          $SESSION_VARS['ml_code_agent'] = null;
      }else {
          $code_agent_existant = getUtilisateursViaCodeAgent($ml_code_agent);
          if (sizeof($code_agent_existant) > 0) {
              $html_err = new HTML_erreur(_("Refus de la modification.") . " ");
              $html_err->setMessage(_("Le code Agent Mobile Lending existe deja, Veuillez choisir un autre code."));
              $html_err->addButton("BUTTON_OK", 'Gen-12');
              $html_err->buildHTML();
              echo $html_err->HTML_code;
              exit();
          }else{
              $SESSION_VARS['ml_code_agent'] = $ml_code_agent;
          }
      }
  }else{
      $SESSION_VARS['is_agent_ml'] = "false";
      $SESSION_VARS['ml_code_agent'] = null;
  }


  if($is_gestionnaire == 1){
  	$SESSION_VARS['is_gestionnaire'] = "false";
  } else {
  	$SESSION_VARS['is_gestionnaire'] = "true";
  }
  ajout_utilisateur($SESSION_VARS);

  $MyPage = new HTML_message("Confirmation création utilisateur '".$SESSION_VARS['prenom']." ".$SESSION_VARS['nom']."'");
  $MyPage->setMessage("L'utilisateur '".$SESSION_VARS['prenom']." ".$SESSION_VARS['nom']."' a bien été créé.");
  $MyPage->addButton(BUTTON_OK, "Gus-1");
  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
} else if ($global_nom_ecran == "Cus-1") { //Consultation
  if (isset($utilisateur)) $SESSION_VARS['utilisateur'] = $utilisateur;
  $SESSION_VARS['nom_utilisateur'] = get_utilisateur_nom($SESSION_VARS['utilisateur']);
  ajout_historique(271,NULL, $SESSION_VARS['utilisateur'], $global_nom_login, date("r"), NULL); //Consultation

  //HTML
  $MyPage = new HTML_GEN2("Consultation utilisateur '".$SESSION_VARS['nom_utilisateur']."'");
  if(isMobileLending()){
      $include = array("id_utilis", "nom", "prenom", "date_naiss", "lieu_naiss", "sexe", "type_piece_id", "num_piece_id", "adresse", "tel", "date_crea", "utilis_crea", "date_modif", "utilis_modif", "statut", "is_agent_ml", "ml_code_agent");
  }else{
      $include = array("id_utilis", "nom", "prenom", "date_naiss", "lieu_naiss", "sexe", "type_piece_id", "num_piece_id", "adresse", "tel", "date_crea", "utilis_crea", "date_modif", "utilis_modif", "statut");
  }
  $MyPage->addTable("ad_uti", OPER_INCLUDE,$include);

  $MyPage->setFieldProperties("id_utilis", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("nom", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("prenom", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_naiss", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_naiss", FIELDP_HAS_CALEND, false);
  $MyPage->setFieldProperties("lieu_naiss", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("sexe", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("type_piece_id", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("num_piece_id", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("adresse", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("tel", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_crea", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_crea", FIELDP_HAS_CALEND, false);
  $MyPage->setFieldProperties("utilis_crea", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_modif", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_modif", FIELDP_HAS_CALEND, false);
  $MyPage->setFieldProperties("utilis_modif", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("statut", FIELDP_IS_LABEL, true);
  if(isMobileLending()){
      $MyPage->setFieldProperties("ml_code_agent", FIELDP_IS_LABEL, true);
  }

  $MyData = new FILL_HTML_GEN2();
  $MyData->addFillClause("c1", "ad_uti");
  $MyData->addCondition("c1", "id_utilis", $SESSION_VARS['utilisateur']);
  $MyData->addManyFillFields("c1", OPER_INCLUDE, $include);
  $MyData->fill($MyPage);

  //Boutons
  $MyPage->addFormButton(1,1, "butvis", _("Visualiser les logins"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butvis", BUTP_PROCHAIN_ECRAN, "Cus-2");
  $MyPage->setFormButtonProperties("butvis", BUTP_CHECK_FORM, false);
  $MyPage->addFormButton(2,1, "butok", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Gus-1");
  $MyPage->setFormButtonProperties("butok", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Cus-2") {
  $MyPage = new HTML_GEN2("Consultation logins '".$SESSION_VARS['nom_utilisateur']."'");

  $logins = get_logins_utilisateur($SESSION_VARS['utilisateur']); //Récupère tous les logins & profils de l'utilisateur

  $MyPage->addField("nbre", _("Nombre de logins associés à l'utilisateur"), TYPC_TXT);
  $MyPage->setFieldProperties("nbre", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("nbre", FIELDP_DEFAULT, sizeof($logins));
  $MyPage->addHTMLExtraCode("break", "<br>");

  $i = 1;
  while (list($key, $value) = each($logins)) {
    $MyPage->addField("login$i", _("Login"), TYPC_TXT);
    $MyPage->setFieldProperties("login$i", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("login$i", FIELDP_DEFAULT, $value['login']);

    $MyPage->addField("profil$i", _("Profil"), TYPC_TXT);
    $MyPage->setFieldProperties("profil$i", FIELDP_IS_LABEL, true);
    $MyPage->setFieldProperties("profil$i", FIELDP_DEFAULT, $value['profil']);

    $MyPage->addHTMLExtraCode("code$i","<br>");
    ++$i;
  }

  //Boutons
  $MyPage->addFormButton(1,1, "butok", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Cus-1");

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
} else if ($global_nom_ecran == "Sus-1") {
  if ($utilisateur == 1) {
    $html_err = new HTML_erreur(_("Refus de la suppression.")." ");
    $html_err->setMessage(_("On ne peut pas supprimer l'utilisateur administrateur."));
    $html_err->addButton("BUTTON_OK", 'Gen-12');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();

  }

  $SESSION_VARS['utilisateur'] = $utilisateur;
  $SESSION_VARS['nom_utilisateur'] = get_utilisateur_nom($SESSION_VARS['utilisateur']);
  $logins = get_logins_utilisateur($SESSION_VARS['utilisateur']); //Récupère tous les logins & profils de l'utilisateur
  if (sizeof($logins) != 0) { //S'il reste encore des logins attachés
    $MyPage = new HTML_erreur(_("Impossible de supprimer l'utilisateur")." '".$SESSION_VARS['nom_utilisateur']."'");
    $msg = sprintf(_("Impossible de supprimer l'utilisateur '%s' car celui-ci possède encore les logins suivants"),$SESSION_VARS['nom_utilisateur'])." : ";
    while (list(,$value) = each($logins)) {
      $msg .= "'".$value['login']."' ";
    }
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Gus-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else { //Si ok, demande confirmation
    $MyPage = new HTML_message(_('Demande confirmation'));
    $MyPage->setMessage(sprintf(_("Etes-vous sûr de vouloir supprimer l'utilisateur %s ?"),$SESSION_VARS['nom_utilisateur']));

    $MyPage->addButton(BUTTON_OUI, "Sus-2");
    $MyPage->addButton(BUTTON_NON, "Gus-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
} else if ($global_nom_ecran == "Sus-2") {
  $myErr = delUtilisateur($SESSION_VARS['utilisateur']);
  if ($myErr->errCode != NO_ERR) {
    if ($myErr->errCode == ERR_EXIST_REFERENCE) {
      $MyPage = new HTML_erreur(_("Impossible de supprimer l'utilisateur")." '".$SESSION_VARS['nom_utilisateur']."'");
      $msg = _("Impossible de supprimer l'utilisateur")." '".$SESSION_VARS['nom_utilisateur']."' "._("cet utilisateur est gestionnaire pour des clients ou dossiers de crédit")."<br />".$myErr->param;
      $MyPage->setMessage($msg);
      $MyPage->addButton(BUTTON_OK, "Gus-1");

      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    }
  } else {
    //HTML
    $MyPage = new HTML_message(_("Confirmation suppression"));
    $MyPage->setMessage(sprintf(_("L'utilisateur '%s' a bien été supprimé !"),$SESSION_VARS['nom_utilisateur']));
    $MyPage->addButton(BUTTON_OK, "Gus-1");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
} else if ($global_nom_ecran == "Mus-1") {
  if ($utilisateur == 1) {
    $html_err = new HTML_erreur(_("Refus de la modification.")." ");
    $html_err->setMessage(_("On ne peut pas modifier l'utilisateur administrateur."));
    $html_err->addButton("BUTTON_OK", 'Gen-12');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();

  }

  $SESSION_VARS['utilisateur'] = $utilisateur;
  $SESSION_VARS['nom_utilisateur'] = get_utilisateur_nom($SESSION_VARS['utilisateur']);
  $MyPage = new HTML_GEN2(_("Modification utilisateur")." '".$SESSION_VARS['nom_utilisateur']."'");
  if(isMobileLending()){
      $include = array("id_utilis", "nom", "prenom", "date_naiss", "lieu_naiss", "sexe", "type_piece_id", "num_piece_id", "adresse", "tel", "date_crea", "utilis_crea", "date_modif", "utilis_modif", "statut", "is_agent_ml","ml_code_agent");
  }else{
      $include = array("id_utilis", "nom", "prenom", "date_naiss", "lieu_naiss", "sexe", "type_piece_id", "num_piece_id", "adresse", "tel", "date_crea", "utilis_crea", "date_modif", "utilis_modif", "statut");
  }
    $MyPage->addTable("ad_uti", OPER_INCLUDE,$include);


  $MyPage->setFieldProperties("id_utilis", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_crea", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_crea", FIELDP_HAS_CALEND, false);
  $MyPage->setFieldProperties("utilis_crea", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_modif", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("date_modif", FIELDP_HAS_CALEND, false);
  $MyPage->setFieldProperties("date_modif", FIELDP_DEFAULT, date("d/m/Y"));
  $MyPage->setFieldProperties("utilis_modif", FIELDP_IS_LABEL, true);
  $MyPage->setFieldProperties("utilis_modif", FIELDP_DEFAULT, $global_id_utilisateur);
  $MyPage->setFieldProperties("statut", FIELDP_EXCLUDE_CHOICES, array(3)); //FIXME

  //L'utilisateur est-il gestionnaire ?
  $choix = array (1 => _("Non"), 2 => _("Oui"));
  $MyPage->addField("is_gestionnaire", _("L'utilisateur est-il gestionnaire ?")." ", TYPC_LSB);
  $MyPage->setFieldProperties("is_gestionnaire", FIELDP_ADD_CHOICES, $choix);
  $MyPage->setFieldProperties("is_gestionnaire", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("is_gestionnaire", FIELDP_IS_REQUIRED, true);

  //Recherche données
  $MyData = new FILL_HTML_GEN2();
  $MyData->addFillClause("c1", "ad_uti");
  $MyData->addCondition("c1", "id_utilis", $SESSION_VARS['utilisateur']);
  $MyData->addManyFillFields("c1", OPER_INCLUDE, $include);
  $MyData->fill($MyPage);

    //Ajout d'un hidden Field pour la validation des piece d'identité
    $MyPage->addHiddenType("char_length_hidden");

    //affectation de la variable du nombre de charactere du type de piece choisi dans le hidden field
    $MyPage->setFieldProperties("type_piece_id", FIELDP_JS_EVENT, array("onchange"=>"getCharLength()"));

    //liste des pièces d'identité et leurs nombre de caractères
    $listPieceIdentLen=getListPieceIdentLength();

    $js1 = "document.onload =getCharLength();\n";
    //Fonction JavaScript pour trouver le type de pièce d'identité choisie et le nombre de caractères correspondant
    $js1 .= "function lookup( name , arr)
            {
              for(var i = 0, len = arr.length; i < len; i++)
              {
                  if( arr[ i ].key == name )
                  {
                  return arr[ i ].value;
                  }
              }
              return false;
            };\n ";

    $js1 .= "function getCharLength(){ \n var myArray = [\n";

    //fonction qui construit un tableau en javascript contenant les pièces d'identité et leurs nombre de caractères respectifs.
    foreach($listPieceIdentLen as $key=>$value)
    {
        $js1 .= "{ key: $key, value: $value },";
    }

    $js1 .= "];\n";
    $js1 .= " document.ADForm.char_length_hidden.value='';\n";
    $js1 .= " if( lookup(document.ADForm.HTML_GEN_LSB_type_piece_id.value, myArray ) != false ) { \n";
    $js1 .= "document.ADForm.char_length_hidden.value = lookup(document.ADForm.HTML_GEN_LSB_type_piece_id.value, myArray );\n}";
    $js1 .= "};\n";

    $MyPage->addJS(JSP_FORM, "js", $js1);

    //Validation du nombre de caractères des pièces d'identité
    $jss = "";
    $jss .= "if (document.ADForm.char_length_hidden.value != 0 && (document.ADForm.char_length_hidden.value != '' && document.ADForm.num_piece_id.value.length != document.ADForm.char_length_hidden.value))
                {
                    msg += '"._("- le nombre de caractères ne conrespond pas à ")."';
                    msg += document.ADForm.char_length_hidden.value
                    msg += '"._(" caractères ")."\\n';
                    ADFormValid = false;
                    }";

    $MyPage->addJS(JSP_BEGIN_CHECK, "js8",$jss);



  //Boutons
  $MyPage->addFormButton(1,1, "butvis", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butvis", BUTP_PROCHAIN_ECRAN, "Mus-2");
  $MyPage->addFormButton(1,2, "butok", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("butok", BUTP_PROCHAIN_ECRAN, "Gus-1");
  $MyPage->setFormButtonProperties("butok", BUTP_CHECK_FORM, false);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();

} else if ($global_nom_ecran == "Mus-2") { //Confirmation modification
  $SESSION_VARS['id_utilis'] = $SESSION_VARS['utilisateur'];
  $SESSION_VARS['nom'] = $nom;
  $SESSION_VARS['prenom'] = $prenom;
  $SESSION_VARS['date_naiss'] = $date_naiss;
  $SESSION_VARS['lieu_naiss'] = $lieu_naiss;
  $SESSION_VARS['sexe'] = $sexe;
  $SESSION_VARS['type_piece_id'] = $type_piece_id;
  $SESSION_VARS['num_piece_id'] = $num_piece_id;
  $SESSION_VARS['adresse'] = $adresse;
  $SESSION_VARS['tel'] = $tel;
  $SESSION_VARS['date_modif'] = date("d/m/Y");
  $SESSION_VARS['utilis_modif'] = $global_id_utilisateur;
  $SESSION_VARS['statut'] = $statut;
  $SESSION_VARS['is_gestionnaire'] = $is_gestionnaire;
  if(isMobileLending()){
      if (!isset($is_agent_ml)){
          $SESSION_VARS['is_agent_ml'] = "false";
      }else {
          $SESSION_VARS['is_agent_ml'] = $is_agent_ml?"true":"false";
      }
      if ($ml_code_agent == null){
          $SESSION_VARS['ml_code_agent'] = null;
      }else {
          $code_agent_existant = getUtilisateursViaCodeAgent($ml_code_agent, " id_utilis <> ".$SESSION_VARS['utilisateur']);
          if (sizeof($code_agent_existant) > 0){
              $html_err = new HTML_erreur(_("Refus de la modification.")." ");
              $html_err->setMessage(_("Le code Agent Mobile Lending existe deja, Veuillez choisir un autre code."));
              $html_err->addButton("BUTTON_OK", 'Gen-12');
              $html_err->buildHTML();
              echo $html_err->HTML_code;
              exit();
          }else{
              $SESSION_VARS['ml_code_agent'] = $ml_code_agent;          }

      }
  }else{
      $SESSION_VARS['is_agent_ml'] = "false";
      $SESSION_VARS['ml_code_agent'] = null;
  }
  

  if($is_gestionnaire == 1){
  	$SESSION_VARS['is_gestionnaire'] = "false";
  } else {
  	$SESSION_VARS['is_gestionnaire'] = "true";
  }

  modif_utilisateur($SESSION_VARS);
  //HTML
  $MyPage = new HTML_message(_("Confirmation modification"));
  $MyPage->setMessage(sprintf(_("L'utilisateur '%s' a bien été modifié."),$SESSION_VARS['nom_utilisateur']));
  $MyPage->addButton(BUTTON_OK, "Gus-1");
  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}
else if ($global_nom_ecran == "Lus-1"){ //Ecran affichage de la liste des utilisateurs d'ADBanking.

  $prof = getAllProfil();

  $MyPage = new HTML_GEN2(_("Liste des utilisateurs"));

  $choix =array();
  foreach ($prof as $key =>$value) {
    $choix[$key] = $value['libel'];
  }

  //Si on vient du même écran ayant choisie un profil
  $profils = array();
  if (isset($_POST['Profil'])){
    $id_prof = $_POST['Profil'];
    $profils[$id_prof]=$prof[$id_prof]['libel'];
  }
  else{
    //sinon on prends tous les profils.
    $profils=$choix;
  }

  $MyPage->addField("Profil",_("Profils: "), TYPC_LSB);
  $MyPage->setFieldProperties("Profil", FIELDP_DEFAULT, $id_prof);
  $MyPage->setFieldProperties("Profil", FIELDP_ADD_CHOICES,  $choix);//apparemment, Aucun n'est pas automatiquement sélectionné
  $MyPage->setFieldProperties("Profil", FIELDP_HAS_CHOICE_AUCUN, false);
  $MyPage->setFieldProperties("Profil", FIELDP_HAS_CHOICE_TOUS, true);
  $MyPage->setFieldProperties("Profil",FIELDP_IS_REQUIRED, false);

  //Bouton Affichage
  $MyPage->addButton("Profil", "Affich", _("Afficher"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("Affich", BUTP_PROCHAIN_ECRAN, "Lus-1");
  $MyPage->setButtonProperties("Affich", BUTP_CHECK_FORM, false);
  

  //Bouton retour
  $MyPage->addFormButton(1, 2, "ret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, "Gus-1");
  $MyPage->setFormButtonProperties("ret", BUTP_CHECK_FORM, false);

  foreach ($profils as $key =>$value) {
    $prf = $value;
    $MyPage->addHTMLExtraCode($key."_ExtraCode", "<br>");
    $table =& $MyPage->addHTMLTable("tb"."_$value", 7,TABLE_STYLE_ALTERN);
    $table->add_cell(new TABLE_cell(" <h3>Profil: $prf</h3>", 6, 1));
    $table->add_cell(new TABLE_cell(_("<b>Nom</b>"), 1, 1));
    $table->add_cell(new TABLE_cell(_("<b>Prénom</b>"), 1, 1));
    $table->add_cell(new TABLE_cell(_("<b>Login</b>"), 1, 1));
    $table->add_cell(new TABLE_cell(_("<b>Date création</b>"), 1, 1));
    $table->add_cell(new TABLE_cell(_("<b>Gestionnaire</b>"), 1, 1));
    if (isMobileLending()){
        $table->add_cell(new TABLE_cell(_("<b>Agent Mobile Lending</b>"), 1, 1));
    }
    $table->add_cell(new TABLE_cell(_("<b>Statut</b>"), 1, 1));

    $utils = getUtilisateursInfo($key);
    $count=0;
    if ($utils != NULL) {
      foreach($utils as $key=>$value) {
        $count += 1;
        $table->add_cell(new TABLE_cell($value['nom'], 1, 1));
        $table->add_cell(new TABLE_cell($value['prenom'], 1, 1));
        $table->add_cell(new TABLE_cell($value['login']), 1, 1);
        $table->add_cell(new TABLE_cell($value['date_crea']), 1, 1);
        $table->add_cell(new TABLE_cell($value['is_gestionnaire']), 1, 1);
        if (isMobileLending()){
            $table->add_cell(new TABLE_cell($value['is_agent_ml']), 1, 1);
        }
        $table->add_cell(new TABLE_cell($value['statut']), 1, 1);
      }
    }
    $table->add_cell(new TABLE_cell("<b>Total ".$prf.": ".$count."</b>"), 7, 1);

 }

  $MyPage->buildHTML();
  echo $MyPage->getHTML();


} else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Nom d'écran inconnu : '$global_nom_ecran'"

?>