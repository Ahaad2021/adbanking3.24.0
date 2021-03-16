<?php

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

require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_champs_extras.php';
require_once 'modules/rapports/xml_devise.php';

//--------- Mac-3 : Liste des mandats --------------------------------------------------

//--------------------------------------------------------------------------------------
//--------- Mac-3 : Liste des mandats --------------------------------------------------
//--------------------------------------------------------------------------------------
if ($global_nom_ecran == 'Mac-3') {
  // Génération du titre
  $myForm = new HTML_GEN2(_("Liste des mandataires"));

  // Begin remote transaction
  $pdo_conn->beginTransaction();

  // Init class
  $EpargneObj = new Epargne($pdo_conn, $global_remote_id_agence);

  // Liste des comptes
  $COMPTES = $EpargneObj->getComptesEpargne($global_id_client);
  foreach ($COMPTES as $key=>$value) {
    $table =& $myForm->addHTMLTable($key."_1", 4, TABLE_STYLE_ALTERN);
    $table->add_cell(new TABLE_cell($value['num_complet_cpte']."/".$value['devise']." ".$value['libel'], 4, 1));
    $table->add_cell(new TABLE_cell(_("Dénomination"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Type de pouvoir de signature"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Validité du mandat"), 1, 1));
    $table->add_cell(new TABLE_cell(_("Informations"), 1, 1));
    //$MANDATS = getMandats($value['id_cpte']);
    $MANDATS = $EpargneObj->getMandats($value['id_cpte']);
    if ($MANDATS != NULL) {
      foreach($MANDATS as $key=>$value) {
        $table->add_cell(new TABLE_cell($value['denomination'], 1, 1));
        $table->add_cell(new TABLE_cell(adb_gettext($adsys['adsys_type_pouv_sign'][$value['type_pouv_sign']]), 1, 1));
        if ($value['valide'] == 't') {
          $table->add_cell(new TABLE_cell(_("Valide"), 1, 1));
        } else {
          $table->add_cell(new TABLE_cell(_("Invalide"), 1, 1));
        }
        $table->add_cell(new TABLE_cell(_("<A href='#' onclick=\"OpenBrw('$SERVER_NAME/ad_ma/app/views/externe/info_mandat_distant.php?m_agc=".$_REQUEST['m_agc']."&id_mandat=".$key."')\">Afficher</A>"), 1,1));
      }
    }
  }
  
  $myForm->addFormButton(1, 1, "retour", _("Retour Menu"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 'Ope-13');
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  // Génération du code
  $myForm->buildHTML();
  echo $myForm->getHTML();
  
  // Destroy object
  unset($EpargneObj);
  
  // Commit transaction
  $pdo_conn->commit();
}

//--------------------------------------------------------------------------------------
//--------- Erreur ---------------------------------------------------------------------
//--------------------------------------------------------------------------------------
else {
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
}
?>