<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Gestion des profils
 * @package Parametrage
 */

require_once('lib/dbProcedures/agence.php');
require_once('lib/dbProcedures/parametrage.php');
require_once('lib/misc/guichet_lib.php');
require_once('lib/misc/divers.php');

// Fonction systeme index
$fonc_sys_delimiter = array(array('counter'=>1, 'limit'=>499), array('counter'=>600, 'limit'=>699), array('counter'=>700, 'limit'=>750),array('counter'=>751, 'limit'=>799), array('counter'=>800, 'limit'=>899),array('counter'=>900, 'limit'=>950));

// Vérification si Compensation au siège
if(!isCurrentAgenceSiege())
{
    // Remove index from array
    $index214 = array_search('Traitement compensation au siège', $adsys["adsys_fonction_systeme"]);
    unset($adsys["adsys_fonction_systeme"][$index214]);

    //$adsys["adsys_fonction_systeme"][214] = _("Traitement compensation au siège");
}

/*{{{ function get_fils */
/**
 * Recherche fils et petits-fils.
 * @param int $fonction l'identificateur de la fonction dont on recherche les fils
 * @return array un tableau contenant les identifiants des fils
 */
function get_fils($fonction) {
  global $adsys;

  $result = array();

  for ($i=1; $i<500; ++$i) { //Pour chaque fonction
    if ((isset($adsys["adsys_fonction_systeme_dependance"][$i])) &&  //Si la variable existe
        (is_array($adsys["adsys_fonction_systeme_dependance"][$i])) && //Et qu'il s'agit d'un tableau
        (in_array($fonction, $adsys["adsys_fonction_systeme_dependance"][$i]))) { //Et que c'est bien un fils
      array_push($result, $i); //Insère le fils
      $result = array_merge($result, get_fils($i)); //Va a la recherche des petits fils
    }
  }
  return $result;
}
/*}}}*/

/*{{{ function get_peres */
/**
 * Recherche les pères et grands-pères.
 * @param int $fonction l'identificateur de la fonction dont on recherche les pères
 * @return array un tableau contenant les identifiants des pères
 */
function get_peres($fonction) {
  global $adsys;

  $result = array();

  if ((isset($adsys["adsys_fonction_systeme_dependance"][$fonction])) &&  //Si la variable existe
      (is_array($adsys["adsys_fonction_systeme_dependance"][$fonction]))) { //Et qu'il s'agit d'un tableau
    $result = $adsys["adsys_fonction_systeme_dependance"][$fonction];
    $result2 = $result;
    while (list($key, $value) = each($result)) { //Pour chaque pere on recherche grand-pere
      $result2 = array_merge($result2, get_peres($value));
    }
    $result = $result2;
  }
  return $result;
}
/*}}}*/

/**
 * @desc Recherche les fonctions exclusives à une fonction
 * A est exclusive à B si on a un des deux cas suivants :
 * <UL>
 *   <LI> $adsys["adsys_fonction_systeme_exclusivite"][A] = array(B,...) </LI>
 *   <LI> $adsys["adsys_fonction_systeme_exclusivite"][B] = array(A,...) </LI>
 * </UL>
 * @author papa
 * @since 2.8
 * @param int $fonction l'identificateur de la fonction dont on recherche les fonctions exclusives
 * @return array $fonctions_exclusives un tableau contenant les identifiants des fonctions exclusives
 */
function get_fonctions_exclusives($fonction) {
  global $adsys;

  $fonctions_exclusives = array(); // tableau des fonctions exclusives

  if ((isset($adsys["adsys_fonction_systeme_exclusivite"][$fonction]))
      && (is_array($adsys["adsys_fonction_systeme_exclusivite"][$fonction]))) {
    foreach($adsys["adsys_fonction_systeme_exclusivite"][$fonction] as $key=>$value)
    array_push($fonctions_exclusives, $value);
  }

  // Pour chaque fonction , vérifier si elle n'est pas exclusive
  for ($i=1; $i < 500; ++$i) {
    if (isset($adsys["adsys_fonction_systeme_exclusivite"][$i]) && is_array($adsys["adsys_fonction_systeme_exclusivite"][$i])) {
      if (in_array($fonction, $adsys["adsys_fonction_systeme_exclusivite"][$i]))
        array_push($fonctions_exclusives, $i);
    }
  }

  return $fonctions_exclusives;
}


/*{{{ function double_each_quote */
function double_each_quote($input) { //Renvoie l'input en ayant doublé chaque quote (')
  return str_replace("'", "\\'", $input);
}
/*}}}*/

/*{{{ function get_javascript_function */
/**
 * Renvoie le code javascript de la fonction contrôle associé à un checkbox
 * @param int $fonction l'identificateur de la fonction dont on recherche le code javascript.
 * @return string $retour le code javascript
 */
function get_javascript_function($fonction) {
  global $adsys;

  $fils = get_fils($fonction); // Récupération des fonctions qui dépendent cette fonction
  $peres = get_peres($fonction); // Récupération des fonctions dont dépend cette fonction
  $fonctions_exclusives = get_fonctions_exclusives($fonction);

  // Créationde la fonction Javascript
  $retour = "function verif_fonction$fonction(){\n";
  $retour .= " liste_fils = ''; \n"; // liste des fonctions désactivées

  // Si la fonction est désactivée alors désactiver aussi les autres fonctions dépendantes
  if (sizeof($fils) > 0) { // S'il y a au moins une autre fonction dépendante
    $retour .= "if (document.ADForm.HTML_GEN_BOL_fonction$fonction.checked == false) {\n"; // si la fonction est désactivée
    while (list($key, $value) = each($fils)) {
      $retour .= "if(document.ADForm.HTML_GEN_BOL_fonction$value.checked == true) {\n";
      $retour .= "    document.ADForm.HTML_GEN_BOL_fonction$value.checked = false;\n";
      $retour .= "    verif_fonction$value();\n";
      $retour .= "    liste_fils += '- `".double_each_quote(adb_gettext($adsys["adsys_fonction_systeme"][$value]))."`\\n';\n";
      $retour .= "  }\n";
    }

    // Si au moins une fonction dépendante a été désactivée
    $retour .= "if(liste_fils != '')";
    $retour .="alert('"._("En désactivant cette fonction vous désactivez également les fonctions dépendantes suivantes:")."\\n'+liste_fils);\n";
    //Fin de la fonction
    $retour .= "} \n"; // Fin si fonction désactivée
  }

  // Si la fonction est activée alors activer aussi les fonctions dont elle dépend
  // Ou si la fonction est activée et qu'il faut un guichet alors activer aussi l'option présence de guichet
  // Ou si la fonction est exclusive à la présence de guichet alors désactiver l'option présence de guichet
  // Ou si la fonction est activée et qu'il y a des fonctions exclusives alors désactiver ces dernières
  if ((sizeof($peres) > 0) || (in_array($fonction, $adsys["adsys_fonction_systeme_guichet"]))
      || (in_array($fonction, $adsys["adsys_fonction_systeme_exclusivite_guichet"])) || (sizeof($fonctions_exclusives) > 0)) {
    $retour .= "if(document.ADForm.HTML_GEN_BOL_fonction$fonction.checked == true) {\n"; // Si la fonction est activée

    // On active les autres fonctions dont elle dépend
    reset($peres);
    $retour .= "  liste_peres = ''; \n"; // texte contenant les fonctions activées
    while (list($key, $value) = each($peres)) {
      $retour .= "  if(document.ADForm.HTML_GEN_BOL_fonction$value.checked == false){\n";
      $retour .= "    document.ADForm.HTML_GEN_BOL_fonction$value.checked = true;\n";
      $retour .= "    verif_fonction$value();\n";
      $retour .= "    liste_peres += '- `".double_each_quote(adb_gettext($adsys["adsys_fonction_systeme"][$value]))."`\\n';\n";
      $retour .= "  }\n";
    }

    // On active l'option présence de guichet si la fonction nécessite un guichet
    if (in_array($fonction, $adsys["adsys_fonction_systeme_guichet"])) {
      $retour .= "if(document.ADForm.HTML_GEN_BOL_guichet.checked == false) {\n";
      $retour .= "    document.ADForm.HTML_GEN_BOL_guichet.checked = true;\n";
      $retour .= "    verif_guichet();\n";
      $retour.= "     liste_peres += '- "._("! Presence d\'un guichet !")."\\n';\n";
      $retour .= "}\n";
    }

    // On désactive l'option présence guichet si elle est exclusive à la fonction
    if (in_array($fonction, $adsys["adsys_fonction_systeme_exclusivite_guichet"])) {
      $retour .= "if(document.ADForm.HTML_GEN_BOL_guichet.checked == true) {\n";
      $retour .= "    document.ADForm.HTML_GEN_BOL_guichet.checked = false;\n";
      $retour .= "    verif_guichet();\n";
      $retour.= "     liste_peres += '- "._("! Presence d\'un guichet !")."\\n';\n";
      $retour .= "}\n";
    }

    // On désactive les fonctions exclusives
    if (sizeof($fonctions_exclusives) > 0 ) {
      foreach($fonctions_exclusives as $key=>$value) {
        $retour .= "  if(document.ADForm.HTML_GEN_BOL_fonction$value.checked == true){\n";
        $retour .= "    document.ADForm.HTML_GEN_BOL_fonction$value.checked = false;\n";
        $retour .= "    verif_fonction$value();\n";
        $retour .= "    liste_peres += '- `".double_each_quote(adb_gettext($adsys["adsys_fonction_systeme"][$value]))."`\\n';\n";
        $retour .= "  }\n";
      }
    }

    // Si la fonction et l'option gestion caisse centrale sont exclusives, désactivée cette option et les fonction associées
    if (isset($adsys["adsys_fonction_systeme_exclusivite_cc"])
        && is_array($adsys["adsys_fonction_systeme_exclusivite_cc"]))
      if (in_array($fonction, $adsys["adsys_fonction_systeme_exclusivite_cc"])) {
        $retour .= "  if(document.ADForm.HTML_GEN_BOL_cc.checked == true){\n";
        $retour .= "    document.ADForm.HTML_GEN_BOL_cc.checked = false;\n";
        $retour .= "    liste_peres += '- "._("Gestion caisse centrale")." \\n';\n";
        $retour .= " }\n";

        if (isset($adsys["fonctions_cc"]) && is_array($adsys["fonctions_cc"]))
          foreach($adsys["fonctions_cc"] as $key=>$value) {
          $retour .= "  if(document.ADForm.HTML_GEN_BOL_fonction$value.checked == true){\n";
          $retour .= "    document.ADForm.HTML_GEN_BOL_fonction$value.checked = false;\n";
          $retour .= "    verif_fonction$value();\n";
          $retour .= "    liste_peres += '- `".double_each_quote(adb_gettext($adsys["adsys_fonction_systeme"][$value]))."`\\n';\n";
          $retour .= " }\n";
        }
      }

    $retour .= " if(liste_peres != '')";
    $retour .= "alert('"._("En activant cette fonction vous activez les fonctions dépendantes ou désactivez les fonctions exclusives suivantes")." :\\n'+liste_peres);\n";
    $retour .= "}\n"; // Fin si fonction activée
  }

  $retour .= "}\n"; // Fin de la fonction

  return $retour;
}
/*}}}*/

/*{{{ function get_guichet_fct */
/**
 * Renvoie le code javascript controlant les fonctions en dépendance ou en exclusivité avec la présence d'un guichet
 * @return string $retour : un code javascript
 */
function get_guichet_fct() {
  global $adsys;

  $fils = array(); // tableau des fonctions qui dépendent de la présence d'un guichet
  // Récupérations des fonctions qui dépendent de la présence d'un guichet
  while (list($key, $value) = each($adsys["adsys_fonction_systeme_guichet"])) {
    array_push($fils, $value);
    $fils = array_merge($fils, get_fils($value));
  }

  // Récupération des fonctions en exclusivité de la présence d'un guichet
  $fonctions_exclusives = array(); // tableau des fonctions exclusives à la présence d'un guichet
  if (isset($adsys["adsys_fonction_systeme_exclusivite_guichet"]) and is_array($adsys["adsys_fonction_systeme_exclusivite_guichet"])) {
    foreach($adsys["adsys_fonction_systeme_exclusivite_guichet"] as $key=>$value) {
      array_push($fonctions_exclusives, $value);
      $fonctions_exclusives = array_merge($fonctions_exclusives, get_fils($value));
    }
  }

  // Création de la fonction javascript
  $retour = ""; // code javascript de retour
  $retour .= "\nfunction verif_guichet() {\n";
  $retour .= "  gui_msg = '';\n";
  $retour .= " gui_msg_exclusivite = '';\n";

  // Si présence d'un guichet est désactivée alors désactiver les fonctions qui dépendent de la présence d'un guichet
  $retour .= "  if (document.ADForm.HTML_GEN_BOL_guichet.checked == false) {\n"; //  Si présence d'un guichet est désactivée
  while (list($key, $value) = each($fils)) {
    $retour .= "    if (document.ADForm.HTML_GEN_BOL_fonction$value.checked == true){\n";
    $retour .= "      document.ADForm.HTML_GEN_BOL_fonction$value.checked = false;\n";
    $retour .= "       verif_fonction$value();\n";
    $retour .= "      gui_msg += '- `".double_each_quote(adb_gettext($adsys["adsys_fonction_systeme"][$value]))."`\\n'\n";
    $retour .= "    }\n";
  }
  $retour .="if(gui_msg !='') alert('"._("En désactivant le guichet vous avez désactivé les fonctions dépendantes suivantes").":\\n'+gui_msg);\n";
  $retour .= " }\n"; //Fin Si présence d'un guichet est désactivée

  // Si présence d'un guichet est activée alors désactiviter les fonctions exclusives avec présence guichet
  $retour .= "else {\n";
  foreach($fonctions_exclusives as $key=>$value) {
    $retour .= "if(document.ADForm.HTML_GEN_BOL_fonction$value.checked == true){\n";
    $retour .= "      document.ADForm.HTML_GEN_BOL_fonction$value.checked = false;\n";
    $retour .= "    verif_fonction$value();\n";
    $retour .= "      gui_msg_exclusivite += '- `".double_each_quote(adb_gettext($adsys["adsys_fonction_systeme"][$value]))."`\\n'\n";
    $retour .= "    }\n";
  }
  $retour .= "}";
  $retour .="if(gui_msg_exclusivite !='') alert('"._("En activant le guichet vous avez désactivé les fonctions exclusives suivantes").":\\n'+gui_msg_exclusivite);\n";

  $retour .= "}\n";//Fin fonction
  return $retour;
}
/*}}}*/

/*{{{ Gpf-1 : Gestion des profils */
if ($global_nom_ecran == "Gpf-1") {
  $MyPage = new HTML_GEN2(_("Gestion des profils"));

  //javascript
  $js = "document.ADForm.consult.disabled = true; document.ADForm.modif.disabled = true; document.ADForm.supr.disabled = true;\n";
  $js .= "function activateButtons(){\n";
  $js .= "activate = (document.ADForm.HTML_GEN_LSB_cprofil.value != 0);";
  $js .= "activate2 = (activate && (document.ADForm.HTML_GEN_LSB_cprofil.value != 1));";
  $js .= "document.ADForm.consult.disabled = !activate; document.ADForm.modif.disabled = !activate2; document.ADForm.supr.disabled = !activate2;";
  $js .= "}\n";
  $MyPage->addJS(JSP_FORM, "js", $js);

  //Champs profil
  $profils = getAllProfil();
  $choix =array();
  foreach ($profils as $key =>$value) {
    $choix[$key] = $value['libel'];
  }

//  $MyPage->addTable("ad_log", OPER_INCLUDE, array("profil"));
//  $MyPage->setFieldProperties("profil", FIELDP_SHORT_NAME, "cprofil");
    $MyPage->addField("cprofil",_("Profil"),TYPC_LSB);
    $MyPage->setFieldProperties("cprofil", FIELDP_IS_REQUIRED, true);
    $MyPage->setFieldProperties("cprofil", FIELDP_HAS_CHOICE_AUCUN, true);
    $MyPage->setFieldProperties("cprofil",FIELDP_ADD_CHOICES, $choix);
  $MyPage->setFieldProperties("cprofil", FIELDP_JS_EVENT, array("onchange"=>"activateButtons();"));

  //Bouton consulter
  $MyPage->addButton("cprofil", "consult", _("Consulter"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("consult", BUTP_AXS, 257);
  $MyPage->setButtonProperties("consult", BUTP_PROCHAIN_ECRAN, "Cpf-1");

  //Bouton modifier
  $MyPage->addButton("cprofil", "modif", _("Modifier"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("modif", BUTP_AXS, 258);
  $MyPage->setButtonProperties("modif", BUTP_PROCHAIN_ECRAN, "Mpf-1");

  //Bouton supprimer
  $MyPage->addButton("cprofil", "supr", _("Supprimer"), TYPB_SUBMIT);
  $MyPage->setButtonProperties("supr", BUTP_AXS, 259);
  $MyPage->setButtonProperties("supr", BUTP_PROCHAIN_ECRAN, "Spf-1");

  //Bouton créer
  $MyPage->addFormButton(1, 1, "cree", _("Créer un nouveau profil"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("cree", BUTP_AXS, 256);
  $MyPage->setFormButtonProperties("cree", BUTP_PROCHAIN_ECRAN, "Apf-1");
  $MyPage->setFormButtonProperties("cree", BUTP_CHECK_FORM, false);

  //Bouton Retour
  $MyPage->addFormButton(2, 1, "ret", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ret", BUTP_PROCHAIN_ECRAN, "Gen-12");
  $MyPage->setFormButtonProperties("ret", BUTP_CHECK_FORM, false);
  $MyPage->buildHTML();

  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Mpf-1 : Modification d'un profil */
else if ($global_nom_ecran == "Mpf-1") {
  if ($cprofil == 1) {
    $html_err = new HTML_erreur(_("Refus de la modification.")." ");
    $html_err->setMessage(_("On ne peut pas modifier le profil administrateur."));
    $html_err->addButton("BUTTON_OK", 'Gen-12');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();
  }

  $javascript = "";
  $SESSION_VARS['profil_axs'] = get_profil_axs($cprofil); //Recherche l'info du profil choisit
  $SESSION_VARS['nom_profil'] = get_profil_nom($cprofil); //Recherche le libellé du profil
  $SESSION_VARS['cprofil'] = $cprofil;

  //Se préoccupe du guichet
  $SESSION_VARS['conn_agc'] = get_connexion_agence($SESSION_VARS['cprofil']);
  $SESSION_VARS['guichet'] = get_profil_guichet($SESSION_VARS['cprofil']); //Recherche si un guichet est associé au profil

  // Si le profil possede un guichet, ne pas permettre l'acces lorsque l'agence est fermée.
  if($SESSION_VARS['guichet']) {
    $SESSION_VARS['conn_agc'] = false;
  }

  // Parametrage connection agence fermé
  $isLabelParamConnect = false;
  $canConnect = false;

  // Si le profil possede un guichet, ne peut pas se connecter si l'agence est fermée
  if($SESSION_VARS['guichet']) {
    $isLabelParamConnect = true;
  }

  // L'admin peut toujours se connecter, mais le checkbox reste desactivé
  if($cprofil == 1) {
    $isLabelParamConnect = true;
    $canConnect = true;
  }
  else { // Les autres profils dependent du parametrage.
    $canConnect = $SESSION_VARS['conn_agc'];
  }

  $SESSION_VARS['masque_solde'] = !get_profil_acces_solde($SESSION_VARS['cprofil']);
  $SESSION_VARS['masque_solde_vip'] = !get_profil_acces_solde_vip($SESSION_VARS['cprofil']);
  $need_guichet = $adsys["adsys_fonction_systeme_guichet"];//Fonctions nécessitant un guichet
  while (list($key, $value) = each($adsys["adsys_fonction_systeme_guichet"])) { //Fils nécessitant un guichet
    $need_guichet = array_merge($need_guichet, get_fils($value));
  }

  //Recup timeout
  $SESSION_VARS['timeout'] = get_profil_timeout($SESSION_VARS['cprofil']);

  //Début génération HTML
  $MyPage = new HTML_GEN2("Modification du profil '".$SESSION_VARS['nom_profil']."'");
    $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
    $MyPage->addHTMLExtraCode("html_js",$html_js);

  //Champs libellé profil
  $MyPage->addField("libel", _("Libellé du profil"), TYPC_TXT);
  $MyPage->setFieldProperties("libel", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("libel", FIELDP_DEFAULT, $SESSION_VARS['nom_profil']);

  //Champs timeout
  $MyPage->addField("timeout", _("Temps d'inactivité maximum du profil (minutes)"), TYPC_INT);
  $MyPage->setFieldProperties("timeout", FIELDP_DEFAULT, $SESSION_VARS['timeout']);

  //Champs caisse centrale
  $MyPage->addField("conn_agc", _("Le profil possède-t-il le droit de se connecter à une agence fermée ?"), TYPC_BOL);
  $MyPage->setFieldProperties("conn_agc", FIELDP_DEFAULT, $canConnect);
  $MyPage->setFieldProperties("conn_agc", FIELDP_IS_LABEL, $isLabelParamConnect);

  //Champs guichet
  $MyPage->addField("guichet", _("Le profil possède-t-il un guichet ?"), TYPC_BOL);
  $MyPage->setFieldProperties("guichet", FIELDP_DEFAULT, $SESSION_VARS['guichet']);
  $MyPage->setFieldProperties("guichet", FIELDP_IS_LABEL, true);

  //Champs caisse centrale
  $is_cc = isCaisseCentrale($SESSION_VARS['guichet'], $SESSION_VARS['profil_axs']);
  $MyPage->addField("cc", _("Le profil gère-t-il la caisse centrale ?"), TYPC_BOL);
  $MyPage->setFieldProperties("cc", FIELDP_DEFAULT, $is_cc);
  $MyPage->setFieldProperties("cc", FIELDP_IS_LABEL, true);

  //Masquer solde des comptes clients
  $MyPage->addField("masque_solde", _("Masquer le solde des comptes clients ?"), TYPC_BOL);
  $MyPage->setFieldProperties("masque_solde", FIELDP_DEFAULT, $SESSION_VARS['masque_solde']);

  //Masquer solde des comptes clients VIP
  $MyPage->addField("masque_solde_vip", _("Masquer le solde des clients VIP ?"), TYPC_BOL);
  $MyPage->setFieldProperties("masque_solde_vip", FIELDP_DEFAULT, $SESSION_VARS['masque_solde_vip']);

  //Breakline
  $MyPage->addHTMLExtraCode("br1","<br>");


foreach ($fonc_sys_delimiter as $val) {
  //Tableau principal
  for ($i=$val['counter']; $i<=$val['limit']; ++$i) { //Pour toutes les fonctions
    switch ($i) { //Affiche les sous-titres
    case 1 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module client")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 51 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module épargne")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 101 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module crédit")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 151 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module guichet")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 201 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module système")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 251 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module paramétrage")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 301 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module rapports")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 401 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module comptabilité")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 600 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module ligne de crédit")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 700 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module budget")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 751 :
      if(isAgencyBanking()) {
        $MyPage->addHTMLExtraCode("html$i", "<b>" . _("Module Agency Banking") . "</b>");
        $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      }
      break;
    case 800 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module guichet supplementaire")."</b>");
        $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;

      case 900 :
          if (isMobileLending()) {
              $MyPage->addHTMLExtraCode("html$i", "<b>" . _("Module Mobile Lending") . "</b>");
              $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
          }
          break;
        }

    if (isset($adsys["adsys_fonction_systeme"][$i])) { // Si la $i ème fonction est définie
        $required_field = array(260 => '- Le droit de changer le mot de passe n\'a pas été accordé au profil');
      $MyPage->addField("fonction$i", adb_gettext($adsys["adsys_fonction_systeme"][$i]), TYPC_BOL);
        if(in_array($i, array_keys($required_field))){
            $MyPage->setFieldProperties("fonction$i", FIELDP_IS_REQUIRED, true);
        }

      // Si le profil ne demande pas de guichet, désactiver la fonction si elle nécessite la présence de guichet
      if ((! $SESSION_VARS['guichet']) && (in_array($i, $need_guichet))) {
        $MyPage->setFieldProperties("fonction$i", FIELDP_IS_LABEL, true);
        $MyPage->setFieldProperties("fonction$i", FIELDP_DEFAULT, false);
      } else {
        if (in_array($i, $SESSION_VARS['profil_axs']))
          $MyPage->setFieldProperties("fonction$i", FIELDP_DEFAULT, true);
        $MyPage->setFieldProperties("fonction$i", FIELDP_JS_EVENT, array("onchange"=>"verif_fonction$i();"));
        $javascript .= get_javascript_function($i);
      }

      // S'il exsite des fonctions exclusives à la présence de guichet, vérifier que la fonction n'en fait pas partie
      if ( isset($adsys["adsys_fonction_systeme_exclusivite_guichet"])
           && (is_array($adsys["adsys_fonction_systeme_exclusivite_guichet"]))) {
        // Si le profil nécessite un guichet, désactiver la fonction $i si elle est exclusive à la présence de guichet
        if (($SESSION_VARS['guichet']) && (in_array($i, $adsys["adsys_fonction_systeme_exclusivite_guichet"]))) {
          $MyPage->setFieldProperties("fonction$i", FIELDP_DEFAULT, false);
          $MyPage->setFieldProperties("fonction$i", FIELDP_IS_LABEL, true);
        }
      }

      // s'il le profil est pour un gestionnaire
      if (isAgencyBanking()) {
        if (isset($adsys["adsys_exclu_fonc_agent"]) && (is_array($adsys["adsys_exclu_fonc_agent"]))
        ) {
          // Si le profil nécessite un guichet, désactiver la fonction $i si elle est exclusive à la présence de guichet
          if (($global_nom_ecran == "Mpf-1") && (in_array($i, $adsys["adsys_exclu_fonc_agent"]))) {
            $MyPage->setFieldProperties("fonction$i", FIELDP_DEFAULT, false);
            $MyPage->setFieldProperties("fonction$i", FIELDP_IS_LABEL, true);
          }
        }
      }

      // S'il exsite des fonctions exclusives à la gestion de la caisse centarle, vérifier que la fonction n'en fait pas partie
      if ( isset($adsys["adsys_fonction_systeme_exclusivite_cc"])
           && (is_array($adsys["adsys_fonction_systeme_exclusivite_cc"]))) {
        // Si le profil gère la caisse centrale, désactiver la fonction $i si elle est exclusive à la gestion de la cc
        if ($is_cc && in_array($i, $adsys["adsys_fonction_systeme_exclusivite_cc"])) {
          $MyPage->setFieldProperties("fonction$i", FIELDP_DEFAULT, false);
          $MyPage->setFieldProperties("fonction$i", FIELDP_IS_LABEL, true);
        }
      }

    } // Fin Si la $i ème fonction est définie
  } // Fin boucle FOR
}

  //Bouton valider
  $MyPage->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, "Mpf-2");
  $MyPage->setFormButtonProperties("ok", BUTP_KEY, KEYB_ENTER);

  //Bouton annuler
  $MyPage->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gpf-1");
  $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

    $js_check_custom = "
        var dataset = JSON.parse('".addslashes(json_encode($required_field))."');
        $('[name=ok]').click(function(e){
            var err_logs = '';
            e.preventDefault();
            
            $(document.ADForm).find('input[type=checkbox]').each(function(index, node){
                const match = $(node).attr('name').match(/\d+/g);
                if(!$(node).is(':checked')){
                    if(dataset.hasOwnProperty(match)){
                        err_logs += '\\n' + (dataset[match]);
                    }
                }
            });
            
            if(err_logs != ''){ 
                alert(err_logs);      
            }else{
                $(document.ADForm).submit();
            }
        });
  ";

  //Javascript
  $MyPage->addJS(JSP_FORM, "js1", $javascript);
  $MyPage->addJS(JSP_FORM, "js_checked_default", $js_check_custom);
  $MyPage->buildHTML();

  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Mpf-2 : Modification d'un profil (confirmation) */
else if ($global_nom_ecran == "Mpf-2") {
  //Récupère les valeurs des checkbox :
  $j=1;
  $fonctions = array();
  foreach ($fonc_sys_delimiter as $val) {
    for ($i=$val['counter']; $i<=$val['limit']; ++$i) {
      if (isset($ {"fonction".$i})) {
        $fonctions[$j] = $i;
        ++$j;
      }
    }
  }
  if (! in_array(0, $fonctions)) $fonctions[$i] = 0; //Tout le monde a accès à la fonction 0 
  //Appel procédures stockées
  $Err = update_profil($SESSION_VARS['cprofil'], $libel, $timeout, isset($conn_agc), isset($masque_solde), $fonctions, isset($masque_solde_vip));

  $MyPage = new HTML_message(_("Confirmation modification"));
  if ($Err->errCode == ERR_TIMEOUT_INVALID) {
    $MyPage->setMessage(sprintf(_("La valeur du temps d'inactivité donnée pour le profil '%s' (%d secondes) pose problème.  ").$error[$Err->errCode], $SESSION_VARS['nom_profil'], $Err->param));
  } else {
    $MyPage->setMessage(sprintf(_("La modifications des droits d'accès du profil '%s' a été réalisée avec succès!"), $SESSION_VARS['nom_profil']));
  }
  $MyPage->addButton(BUTTON_OK, "Gpf-1");

  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}
/*}}}*/

/*{{{ Spf-1 : Suppression d'un profil (demande de confirmation) */
else if ($global_nom_ecran == "Spf-1") {
  if ($cprofil == 1) {
    $html_err = new HTML_erreur(_("Refus de la suppression.")." ");
    $html_err->setMessage(_("On ne peut pas supprimer le login de l'administrateur."));
    $html_err->addButton("BUTTON_OK", 'Gen-12');
    $html_err->buildHTML();
    echo $html_err->HTML_code;
    exit();

  }

  $SESSION_VARS['nom_profil'] = get_profil_nom($cprofil); //Recherche le libellé du profil
  $SESSION_VARS['logins'] = logins_profil($cprofil); //On récupère la liste des logins de ce profil
  $SESSION_VARS['cprofil'] = $cprofil;

  if (sizeof($SESSION_VARS['logins']) > 0) { //Si il y a encore des logins attachés : erreur
    $MyPage = new HTML_erreur(_("Suppression du profil")." '".$SESSION_VARS['nom_profil']."'");
    $msg = sprintf(_("Vous ne pouvez pas supprimer le profil '%s' car les logins suivant y sont encore attachés"),$SESSION_VARS['nom_profil'])." : ";
    while (list($key, $value) = each($SESSION_VARS['logins'])) $msg.= " '$value';";
    $MyPage->setMessage($msg);
    $MyPage->addButton(BUTTON_OK, "Gpf-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {//Sinon, demande de confirmation
    $MyPage = new HTML_message(_("Suppression du profil")." '".$SESSION_VARS['nom_profil']."'");
    $MyPage->setMessage(sprintf(_("Etes-vous certain de vouloir supprimer le profil '%s' ?"),$SESSION_VARS['nom_profil']));
    $MyPage->addButton(BUTTON_OUI, "Spf-2");
    $MyPage->addButton(BUTTON_NON, "Gpf-1");

    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  }
} else if ($global_nom_ecran == "Spf-2") { //Suppression effective d'un profil, confirmation
  supprime_profil($SESSION_VARS['cprofil']);

  $MyPage = new HTML_message(_("Suppression du profil")." '".$SESSION_VARS['nom_profil']."'");
  $MyPage->setMessage(sprintf(_("Le profil '%s' a été supprimé avec succès !"),$SESSION_VARS['nom_profil']));
  $MyPage->addButton(BUTTON_OK, "Gpf-1");

  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}
/*}}}*/

/*{{{ Apf-1 : Ajout d'un profil */
else if ($global_nom_ecran == "Apf-1") {
  $javascript = "";

  //Début génération HTML
  $MyPage = new HTML_GEN2(_("Création profil"));
  $html_js .= "<script src=\"/lib/misc/js/lib/jquery.min.js\" type=\"text/javascript\"></script>";
  $MyPage->addHTMLExtraCode("html_js",$html_js);

  //Champs libellé profil
  $MyPage->addField("libel", _("Libellé du profil"), TYPC_TXT);
  $MyPage->setFieldProperties("libel", FIELDP_IS_REQUIRED, true);

  //Champs timeout
  $MyPage->addField("timeout", _("Temps d'inactivité maximum du profil (minutes)"), TYPC_INT);  //Champs guichet
  $MyPage->addField("guichet", _("Le profil possède-t-il un guichet ?"), TYPC_BOL);
  $MyPage->setFieldProperties("guichet", FIELDP_JS_EVENT, array("onchange"=>"verif_guichet();"));
  $javascript .= get_guichet_fct();

  $js_agence_fermer = "if (document.ADForm.HTML_GEN_BOL_guichet.checked){
                          document.ADForm.HTML_GEN_BOL_cc.checked = false;
                          document.ADForm.HTML_GEN_BOL_conn_agc.checked = false;
                          document.ADForm.HTML_GEN_BOL_conn_agc.setAttribute('disabled', 'true');
                        }
                        else {
                          document.ADForm.HTML_GEN_BOL_conn_agc.removeAttribute('disabled');
                        }
                        ";

  $MyPage->setFieldProperties("guichet", FIELDP_JS_EVENT, array("onchange"=>$js_agence_fermer));

  // Champs caisse centrale
  $MyPage->addField("cc", _("Le profil gère-t-il la caisse centrale ?"), TYPC_BOL);

  // Connexion a une agence fermée
  $MyPage->addField("conn_agc", _("Le profil possède-t-il le droit de se connecter à une agence fermée ?"), TYPC_BOL);

  //Masquer solde des comptes clients
  $MyPage->addField("masque_solde", _("Masquer le solde des comptes clients ?"), TYPC_BOL);
  
  //Masquer solde des comptes clients VIP
  $MyPage->addField("masque_solde_vip", _("Masquer le solde des cients VIP ?"), TYPC_BOL);

  // Si option caisse centrale est activée, activer les fonctions de gestion de la caisse centrale et désactiver les fonctions exclusives
  $liste = "";
  $js = "if (document.ADForm.HTML_GEN_BOL_cc.checked){"; // Option caisse centrale activée
  $js .= " msg = '';\n";
  $js .= " msg_exlusivite = '';\n";
  // Activation des fonctions de la caisse centrale
  reset($adsys['fonctions_cc']);
  while (list(,$value) = each($adsys["fonctions_cc"])) {
    $js .= "document.ADForm.HTML_GEN_BOL_fonction$value.checked=true;";
    $liste .= adb_gettext($adsys["adsys_fonction_systeme"][$value])."  ";
  }
  if ($liste != "")
    $js .= "msg = '"._("Afin de permettre à ce profil de gérer la caisse centrale, les fonctions suivantes ont étés activées").": $liste \\n';\n";

  // Désactivation des fonctions exclusives à la gestion de la caisse centrale
  $liste = "";
  if (isset($adsys["adsys_fonction_systeme_exclusivite_cc"]) && is_array($adsys["adsys_fonction_systeme_exclusivite_cc"]))
    foreach($adsys["adsys_fonction_systeme_exclusivite_cc"] as $key=>$value) {
    $js .= " \nif(document.ADForm.HTML_GEN_BOL_fonction$value.checked = true) {";
    $js .= " \ndocument.ADForm.HTML_GEN_BOL_fonction$value.checked = false;";
    $js .= " \nmsg_exlusivite = msg_exlusivite + '  ".adb_gettext($adsys["adsys_fonction_systeme"][$value])."';";
    $js .= "}";
  }

  $js .="\nif(msg_exlusivite !='') msg =msg +'"._("En activant cette option, les fonctions suivantes ont étés désactivées").": '+msg_exlusivite;";
  $js .= "\nif(msg != '') alert(msg);";
  $js .= "}";

  $MyPage->setFieldProperties("cc", FIELDP_JS_EVENT, array("onchange"=>$js));
  $MyPage->setFieldProperties("cc", FIELDP_JS_EVENT, array("onchange"=>"if (document.ADForm.HTML_GEN_BOL_cc.checked) document.ADForm.HTML_GEN_BOL_guichet.checked = false;"));

  //Breakline
  $MyPage->addHTMLExtraCode("br1","<br>");

foreach ($fonc_sys_delimiter as $val) {
  //Tableau principal
  for ($i=$val['counter']; $i<=$val['limit']; ++$i) { //Pour toutes les fonctions
    switch ($i) { //Affiche les sous-titres
    case 1 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module client")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 51 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module épargne")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 101 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module crédit")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 151 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module guichet")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 201 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module système")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 251 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module paramétrage")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 301 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module rapports")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 401 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module comptabilité")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 600 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module ligne de crédit")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 700 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module budget")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 751 :
      if(isAgencyBanking()) {
        $MyPage->addHTMLExtraCode("html$i", "<b>" . _("Module Agency Banking") . "</b>");
        $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      }
      break;
    case 800 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module guichet")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 900 :
        if (isMobileLending()) {
            $MyPage->addHTMLExtraCode("html$i", "<b>" . _("Module Mobile Lending") . "</b>");
            $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
        }
        break;
    }

    if (isset($adsys["adsys_fonction_systeme"][$i])) { //Si la $i ème fonction est définie
      $required_field = array(260 => '- Le droit de changer le mot de passe n\'a pas été accordé au profil');
      $MyPage->addField("fonction$i", adb_gettext($adsys["adsys_fonction_systeme"][$i]), TYPC_BOL);
      $MyPage->setFieldProperties("fonction$i", FIELDP_JS_EVENT, array("onchange"=>"verif_fonction$i();"));
      if(in_array($i, array_keys($required_field))){
          $MyPage->setFieldProperties("fonction$i", FIELDP_IS_REQUIRED, true);
      }

      $javascript .= get_javascript_function($i);

      if (in_array($i, $adsys['fonctions_cc'])) { //Si la fonction appartient aux fonctions nécessaires pour 'caisse centrale'
        $js = "if (! document.ADForm.HTML_GEN_BOL_fonction$i.checked) document.ADForm.HTML_GEN_BOL_cc.checked = false;";
        $MyPage->setFieldProperties("fonction$i", FIELDP_JS_EVENT, array("onchange"=>$js));
      }

      // s'il le profil est pour un gestionnaire
      if (isAgencyBanking()) {
        if (isset($adsys["adsys_exclu_fonc_agent"]) && (is_array($adsys["adsys_exclu_fonc_agent"]))
        ) {
          // Si le profil nécessite un guichet, désactiver la fonction $i si elle est exclusive à la présence de guichet
          if (($global_nom_ecran == "Apf-1") && (in_array($i, $adsys["adsys_exclu_fonc_agent"]))) {
            $MyPage->setFieldProperties("fonction$i", FIELDP_DEFAULT, false);
            $MyPage->setFieldProperties("fonction$i", FIELDP_IS_LABEL, true);
          }
        }
      }
    }
  }
}

  //Bouton valider
  $MyPage->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, "Apf-2");

  //Bouton annuler
  $MyPage->addFormButton(1, 2, "annul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gpf-1");
  $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

  $js_check_custom = "
        var dataset = JSON.parse('".addslashes(json_encode($required_field))."');
        $('[name=ok]').click(function(e){
            var err_logs = '';
            e.preventDefault();
            
            $(document.ADForm).find('input[type=checkbox]').each(function(index, node){
                const match = $(node).attr('name').match(/\d+/g);
                if(!$(node).is(':checked')){
                    if(dataset.hasOwnProperty(match)){
                        err_logs += '\\n' + (dataset[match]);
                    }
                }
            });
            
            if(err_logs != ''){ 
                alert(err_logs);      
            }else{
                $(document.ADForm).submit();
            }
        });
  ";

  //Javascript
  $MyPage->addJS(JSP_FORM, "js2", $javascript);
  $MyPage->addJS(JSP_FORM, "js_checked_default", $js_check_custom);
  $MyPage->buildHTML();

  echo $MyPage->getHTML();
}
/*}}}*/

/*{{{ Apf-2 : Ajout d'un profil (confirmation) */
else if ($global_nom_ecran == "Apf-2") {

  $fonctions = array();
  //Récupère les valeurs des checkbox :
  $j=1;
  foreach ($fonc_sys_delimiter as $val) {
  for ($i=$val['counter']; $i<=$val['limit']; ++$i) {
    if (isset($ {"fonction".$i})) {
      $fonctions[$j] = $i;
      ++$j;
    }
  }
  }
  if (! in_array(0, $fonctions)) $fonctions[$i] = 0; //Tout le monde a accès à la fonction 0
  //Insère profil
  cree_profil($libel, $fonctions, isset($guichet), $timeout, isset($masque_solde), isset($masque_solde_vip));

  //Message
  $MyPage = new HTML_message(_("Création du profil")." '$libel'");
  $MyPage->setMessage(sprintf(_("Le profil '%s' a été créé avec succès !"),$libel));
  $MyPage->addButton(BUTTON_OK, "Gpf-1");

  $MyPage->buildHTML();
  echo $MyPage->HTML_code;
}
/*}}}*/

/*{{{ Cpf-1 : Consultation d'un profil */
else if ($global_nom_ecran == "Cpf-1") {
  $SESSION_VARS['profil_axs'] = get_profil_axs($cprofil); //Recherche l'info du profil choisit
  $SESSION_VARS['nom_profil'] = get_profil_nom($cprofil); //Recherche le libellé du profil
  $SESSION_VARS['cprofil'] = $cprofil;

  ajout_historique(257,NULL, $SESSION_VARS['cprofil'], $global_nom_login, date("r"), NULL); //Consultation

  //Se préoccupe du guichet et du timeout
  $conn_agc = get_connexion_agence($SESSION_VARS['cprofil']);
  $guichet = get_profil_guichet($SESSION_VARS['cprofil']); //Recherche si un guichet est associé au profil
  $timeout = get_profil_timeout($SESSION_VARS['cprofil']);
  $masque_solde = !get_profil_acces_solde($SESSION_VARS['cprofil']);
  $masque_solde_vip = !get_profil_acces_solde_vip($SESSION_VARS['cprofil']);

  //Début génération HTML
  $MyPage = new HTML_GEN2(_("Consultation du profil")." '".$SESSION_VARS['nom_profil']."'");

  //Champs libellé profil
  $MyPage->addField("libel", _("Libellé du profil"), TYPC_TXT);
  $MyPage->setFieldProperties("libel", FIELDP_IS_REQUIRED, true);
  $MyPage->setFieldProperties("libel", FIELDP_DEFAULT, $SESSION_VARS['nom_profil']);
  $MyPage->setFieldProperties("libel", FIELDP_IS_LABEL, true);

  //Champs timeout
  $MyPage->addField("timeout", _("Temps d'inactivité maximum du profil (minutes)"), TYPC_INT);
  $MyPage->setFieldProperties("timeout", FIELDP_DEFAULT, $timeout);
  $MyPage->setFieldProperties("timeout", FIELDP_IS_LABEL, true);

  //Masquer solde des comptes clients
  $MyPage->addField("conn_agc", _("Le profil possède-t-il le droit de se connecter à une agence fermée ?"), TYPC_BOL);
  $MyPage->setFieldProperties("conn_agc", FIELDP_DEFAULT, $conn_agc);
  $MyPage->setFieldProperties("conn_agc", FIELDP_IS_LABEL, true);

  //Champs guichet
  $MyPage->addField("gui", _("Le profil possède-t-il un guichet ?"), TYPC_BOL);
  $MyPage->setFieldProperties("gui", FIELDP_DEFAULT, $guichet);
  $MyPage->setFieldProperties("gui", FIELDP_IS_LABEL, true);

  //Masquer solde des comptes clients
  $MyPage->addField("masque_solde", _("Masquer le solde des comptes clients ?"), TYPC_BOL);
  $MyPage->setFieldProperties("masque_solde", FIELDP_DEFAULT, $masque_solde);
  $MyPage->setFieldProperties("masque_solde", FIELDP_IS_LABEL, true);
  
  //Masquer solde des comptes clients VIP
  $MyPage->addField("masque_solde_vip", _("Masquer le solde des clients VIP ?"), TYPC_BOL);
  $MyPage->setFieldProperties("masque_solde_vip", FIELDP_DEFAULT, $masque_solde_vip);
  $MyPage->setFieldProperties("masque_solde_vip", FIELDP_IS_LABEL, true);

  //Breakline
  $MyPage->addHTMLExtraCode("br1","<br>");

foreach ($fonc_sys_delimiter as $val) {
  //Tableau principal
  for ($i=$val['counter']; $i<=$val['limit']; ++$i) { //Pour toutes les fonctions
    switch ($i) { //Affiche les sous-titres
    case 1 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module client")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 51 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module épargne")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 101 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module crédit")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 151 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module guichet")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 201 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module système")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 251 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module paramétrage")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 301 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module rapports")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 401 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module comptabilité")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 600 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module ligne de crédit")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 700 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module budget")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 751 :
      if(isAgencyBanking()) {
        $MyPage->addHTMLExtraCode("html$i", "<b>" . _("Module Agency Banking") . "</b>");
        $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      }
      break;
     case 800 :
      $MyPage->addHTMLExtraCode("html$i", "<b>"._("Module Engrais Chimiques")."</b>");
      $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
      break;
    case 900 :
        if (isMobileLending()) {
            $MyPage->addHTMLExtraCode("html$i", "<b>" . _("Module Mobile Lending") . "</b>");
            $MyPage->setHTMLExtraCodeProperties("html$i", HTMP_IN_TABLE, true);
        }
        break;
    }

    if (isset($adsys["adsys_fonction_systeme"][$i])) { //Si la $i ème fonction est définie
      $MyPage->addField("fonction$i", adb_gettext($adsys["adsys_fonction_systeme"][$i]), TYPC_BOL);
      if (in_array($i, $SESSION_VARS['profil_axs'])) $MyPage->setFieldProperties("fonction$i", FIELDP_DEFAULT, true);
      $MyPage->setFieldProperties("fonction$i", FIELDP_IS_LABEL, true);
    }
  }
}

  //Bouton OK
  $MyPage->addFormButton(1, 1, "ok", _("Retour"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, "Gpf-1");
  $MyPage->setFormButtonProperties("ok", BUTP_KEY, KEYB_ENTER);

  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>