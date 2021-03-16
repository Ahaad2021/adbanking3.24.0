<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [89] Bloquage et débloquage de compte
 * Cette opération comprends les écrans :
 * - Bdc-1 : Liste de tous les comptes du client
 * - Bdc-2 : Demande de confirmation du bloquage
 * - Bdc-3 : Confirmation du bloquage
 * @package Epargne
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/epargne.php';

/*{{{ Bdc-1 : Liste de tous les comptes du client */
if ($global_nom_ecran == "Bdc-1") {
  $html = new HTML_GEN2(_("Bloquer / débloquer un compte"));
  // Création du formulaire
  $table =& $html->addHTMLTable('tablecomptes', 8 /*nbre colonnes*/, TABLE_STYLE_ALTERN);

  $table->add_cell(new TABLE_cell(_("Numéro"),		/*colspan*/1,	/*rowspan*/1	));
  $table->add_cell(new TABLE_cell(_("Intitulé"),	/*colspan*/1,	/*rowspan*/1	));
// $table->add_cell(new TABLE_cell(_("Devise"),		/*colspan*/1,	/*rowspan*/1	));
  $table->add_cell(new TABLE_cell(_("Type de produit"),	/*colspan*/1,	/*rowspan*/1	));
  $table->add_cell(new TABLE_cell(_("Etat"),		/*colspan*/1,	/*rowspan*/1	));
  $table->add_cell(new TABLE_cell(_("Date blocage"),		/*colspan*/1,	/*rowspan*/1	));
  $table->add_cell(new TABLE_cell(_("Raison blocage"),		/*colspan*/1,	/*rowspan*/1	));
  $table->add_cell(new TABLE_cell(_("Login"),		/*colspan*/1,	/*rowspan*/1	));
  $table->add_cell(new TABLE_cell(_("Action"),		/*colspan*/1,	/*rowspan*/1	));

  // Liste des comptes
  $ListeComptes = getAllAccounts($global_id_client);
  $liste = array();
  if (is_array($ListeComptes)) {
    foreach($ListeComptes as $key=>$value) {
      $etat_cpte = $value['etat_cpte'];
      $id_prod = $value['id_prod'];
      $account_datas = getAccountDatas($key);
      $table->add_cell(new TABLE_cell($value['num_complet_cpte']));
      $table->add_cell(new TABLE_cell($value['intitule_compte']));
      //$table->add_cell(new TABLE_cell($value['devise']));
      $table->add_cell(new TABLE_cell($account_datas['libel']));
      $cell = new TABLE_cell(adb_gettext($adsys['adsys_etat_cpt_epargne'][$etat_cpte]));
      if ($etat_cpte == '3') {
        $cell->set_property("color","red");
      }
      $table->add_cell($cell);

      if ($id_prod != '2' && $id_prod != '4' && nbrCredAttache($key) == 0) {
        if ($etat_cpte == '1') {
          $cell = new TABLE_cell_link(_("Bloquer"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Bdc-2&id_cpte=$key&action=1&etat_cpte=$etat_cpte");
          $date_bloc = NULL;
          $raison_bloc = NULL;
          $login = NULL;
        } else if ($etat_cpte == '3' || $etat_cpte == '6' || $etat_cpte == '7') {
          $cell = new TABLE_cell_link(_("Débloquer"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Bdc-2&id_cpte=$key&action=2&etat_cpte=$etat_cpte");
          $date_bloc = $value['date_blocage'];
          $date_bloc=pg2phpDate($date_bloc);
          $raison_bloc = $value['raison_blocage'];
          $login = $value['utilis_bloquant'];
        } else {
          $cell = new TABLE_cell("");
          $date_bloc = NULL;
          $raison_bloc = NULL;
          $login = NULL;
        }
      } else {
        $cell = new TABLE_cell("");
        $date_bloc = NULL;
        $raison_bloc = NULL;
        $login = NULL;
      }
      $table->add_cell(new TABLE_cell($date_bloc));
      $table->add_cell(new TABLE_cell($raison_bloc));
      $table->add_cell(new TABLE_cell(getLibel("ad_uti", $login)));
      $table->add_cell($cell);

    }
  }

//Boutons
  $html->addFormButton(1,1, "retour", _("Retour menu"), TYPB_SUBMIT);
  $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gen-10");
  $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $html->buildHTML();
  echo $html->getHTML();
}
/*}}}*/

/*{{{ Bdc-2 : Demande de confirmation du bloquage */
else if ($global_nom_ecran == "Bdc-2") {
	global $global_nom_login;
  // Liste des utlisateurs
  $SESSION_VARS["userId"]=get_login_utilisateur($global_nom_login);
  $userName=get_utilisateur_nom($SESSION_VARS["userId"]);
  /*$utilisateurs = getUtilisateurs();
  foreach($utilisateurs as $id_uti=>$val_uti)
  $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];
*/
  $SESSION_VARS['id_cpte'] = $id_cpte;
  $SESSION_VARS['action'] = $action;
  $SESSION_VARS['etat_cpte'] = $etat_cpte;  
  $InfoCompte = get_compte_epargne_info($id_cpte);

  // Bloquer un compte
  if ($action == 1) {
    $html = new HTML_GEN2(_("Selection type de blocage du compte"));
    //Sélectionner le type de blocage
    $html->addField("type_blocage",_("Type de blocage"),TYPC_LSB);
    $types_blocage = array ("3" => _("Blocage retrait et dépôt"), "6" => _("Blocage dépôt"), "7" => _("Blocage retrait"));
    $html->setFieldProperties("type_blocage", FIELDP_ADD_CHOICES, $types_blocage);
    $html->setFieldProperties("type_blocage", FIELDP_IS_REQUIRED, true);
    
    $html->addField("raison",_("Raison blocage"),TYPC_TXT);
    $html->setFieldProperties("raison", FIELDP_IS_REQUIRED, true);
    $html->addField("login",_("Gestionnaire"),TYPC_TXT);
    $html->setFieldProperties("login", FIELDP_DEFAULT, $userName);
    $html->setFieldProperties("login", FIELDP_IS_REQUIRED, true);
    $html->setFieldProperties("login", FIELDP_IS_LABEL, true);

    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $html->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Bdc-3');
    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Bdc-1');
    $html->buildHTML();
    echo $html->getHTML();
  }
  // Débloquer un compte
  else if ($action == 2) {
    //Si les dépôts et retraits étaient bloqués, définir quel type de déblocage
    if($SESSION_VARS['etat_cpte'] == 3){
    	$html = new HTML_GEN2(_("Selection type de déblocage du compte"));
	    debug($SESSION_VARS['etat_cpte'], _("etat cpte"));
	    //Sélectionner le type de blocage
	    $html->addField("type_deblocage",_("Type déblocage"),TYPC_LSB);
	    $types_deblocage = array ("3" => _("Déblocage retrait et dépôt"), "6" => _("Déblocage dépôt"), "7" => _("Déblocage retrait"));
	    $html->setFieldProperties("type_deblocage", FIELDP_ADD_CHOICES, $types_deblocage);
	    $html->setFieldProperties("type_deblocage", FIELDP_IS_REQUIRED, true);
	    
	    $html->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
	    $html->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
	    $html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
	
	    $html->setFormButtonProperties("ok", BUTP_PROCHAIN_ECRAN, 'Bdc-3');
	    $html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN,'Bdc-1');
	    $html->buildHTML();
	    echo $html->getHTML();
	 } else {
	    //Si blocage dépôts seul ou retraits seul, débloquer directement
	    $html_msg = new HTML_message(_("Demande de confirmation de déblocage du compte"));
	    $html_msg->setMessage(_("Êtes-vous sûr de vouloir débloquer le compte n° ").$InfoCompte['num_complet_cpte']." ?");
	
	    $html_msg->addButton("BUTTON_OUI", "Bdc-3");
	    $html_msg->addButton("BUTTON_NON", "Bdc-1");
	    $html_msg->buildHTML();
	    echo $html_msg->HTML_code;
	 }
 }
  
}
/*}}}*/

/*{{{ Bdc-3 : Confirmation du bloquage */
else if ($global_nom_ecran == "Bdc-3") {

  global $global_cpt_base_ouvert, $global_depot_bloque, $global_retrait_bloque;

  $SESSION_VARS["raison"]= $raison;
  $SESSION_VARS["type_blocage"] = $type_blocage;
  $SESSION_VARS["type_deblocage"] = $type_deblocage;
  $InfoCompte = get_compte_epargne_info($SESSION_VARS['id_cpte']);

  // Bloquer un compte
  if ($SESSION_VARS['action'] == 1) {
    $erreur = bloquerCompte($SESSION_VARS['id_cpte'], $SESSION_VARS["raison"], $SESSION_VARS["userId"], $SESSION_VARS["type_blocage"]);

    if ($erreur->errCode == NO_ERR) {
      $html_msg = new HTML_message(_("Confirmation de blocage du compte"));
      if($type_blocage != NULL){
      	if($SESSION_VARS["type_blocage"] == 3){
      	$blocage = "(blocage retraits et dépôts)";
	      } else if($SESSION_VARS["type_blocage"] == 6){
	      	$blocage = "(blocage dépôts seulement)";
	      } else if($SESSION_VARS["type_blocage"] == 7){
	      	$blocage = "(blocage retraits seulement)";
	      } debug($blocage,"blocage");
	      $html_msg->setMessage(sprintf(_("Le compte n° %s est maintenant bloqué %s"), $InfoCompte['num_complet_cpte'],$blocage)."<br/><br/>"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>");
      } else {
      	$html_msg->setMessage(sprintf(_("Le compte n° %s est maintenant bloqué"), $InfoCompte['num_complet_cpte'])."<br /><br />"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>");
      }

      $html_msg->addButton("BUTTON_OK", 'Gen-8');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;

      // Si on a bloqué le compte de base, mettre à jour la variable global_cpt_base_ouvert
      if ($id_cpte == getBaseAccountID($global_id_client)) {

        switch($SESSION_VARS["type_blocage"])
        {
          case (3) : // blocage retraits et dépôts
            $global_cpt_base_ouvert = false;
            $global_depot_bloque = true;
            $global_retrait_bloque = true;
          break;

          case (6) :  // blocage dépôts
            $global_depot_bloque = true;
            $global_retrait_bloque = false;
          break;

          case (7) : // blocage retraits
            $global_depot_bloque = false;
            $global_retrait_bloque = true;
          break;

          default :
          break;
        }

      }
    } else {
      $html_err = new HTML_erreur(_("Echec de blocage du compte"));
      $html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]);
      $html_err->addButton("BUTTON_OK", 'Gen-10');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }

  // Débloquer un compte
  else if ($SESSION_VARS['action'] == 2) {
    debug($SESSION_VARS["type_deblocage"],"type deblocage");

    $erreur = debloquerCompte($SESSION_VARS['id_cpte'], $SESSION_VARS["type_deblocage"]);

    if ($erreur->errCode == NO_ERR) {
      $html_msg =new HTML_message(_("Confirmation de déblocage du compte"));
      //Personsaliser le type de déblocage
      if($type_deblocage != NULL){
      	if($SESSION_VARS["type_deblocage"] == 3){
      	$deblocage = "(deblocage retraits et dépôts)";
	      } else if($SESSION_VARS["type_deblocage"] == 6){
	      	$deblocage = "(deblocage dépôts seulement)";
	      } else if($SESSION_VARS["type_deblocage"] == 7){
	      	$deblocage = "(deblocage retraits seulement)";
	      }
	      $html_msg->setMessage(sprintf(_("Le compte n° %s est maintenant débloqué %s"), $InfoCompte['num_complet_cpte'],$deblocage)."<br/><br/>"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>");
      } else {
      	$html_msg->setMessage(sprintf(_("Le compte n° %s est maintenant débloqué"), $InfoCompte['num_complet_cpte'])."<br/><br/>"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>");
      }

      $html_msg->addButton("BUTTON_OK", 'Gen-8');
      $html_msg->buildHTML();
      echo $html_msg->HTML_code;

      // Si on a débloqué le compte de base, mettre à jour la variable global_cpt_base_ouvert
      if ($id_cpte == getBaseAccountID($global_id_client)) {

        switch($SESSION_VARS["type_blocage"])
        {
          case (3) : // deblocage retraits et dépôts
            $global_cpt_base_ouvert = true;
            $global_depot_bloque = false;
            $global_retrait_bloque = false;
            break;

          case (6) :  // deblocage dépôts
            $global_depot_bloque = false;
            $global_retrait_bloque = true;
            break;

          case (7) : // deblocage retraits
            $global_depot_bloque = true;
            $global_retrait_bloque = false;
            break;

          default :
          break;
        }
      }
    } else {
      $html_err = new HTML_erreur(_("Echec de déblocage du compte"));
      $html_err->setMessage("Erreur : ".$error[$erreur->errCode]);
      $html_err->addButton("BUTTON_OK", 'Gen-10');
      $html_err->buildHTML();
      echo $html_err->HTML_code;
    }
  }
}
/*}}}*/

else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));

?>
