<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [91] Activez un compte dormant
 * Cette opération comprends les écrans :
 * - Ced-1 : Liste de tous les comptes dormant  du client
 * - Ced-2 : Confirmation d'activation
 * @package Epargne
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/HTML_message.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/dbProcedures/epargne.php';

/*{{{ Ced-1 : Liste de tous les comptes dormant  du client */
if ($global_nom_ecran == "Ced-1") {
	$SESSION_VARS['compte'] =array();
	$html = new HTML_GEN2(_("Activez les comptes dormants / Inactifs"));
	// Création du formulaire
	$table =& $html->addHTMLTable('tablecomptes',6 /*nbre colonnes*/, TABLE_STYLE_ALTERN);

	$table->add_cell(new TABLE_cell(_("Numéro"),		/*colspan*/1,	/*rowspan*/1	));
	$table->add_cell(new TABLE_cell(_("Intitulé"),	/*colspan*/1,	/*rowspan*/1	));
	// $table->add_cell(new TABLE_cell(_("Devise"),		/*colspan*/1,	/*rowspan*/1	));
	$table->add_cell(new TABLE_cell(_("Type de produit"),	/*colspan*/1,	/*rowspan*/1	));
	$table->add_cell(new TABLE_cell(_("Etat"),		/*colspan*/1,	/*rowspan*/1	));
	$table->add_cell(new TABLE_cell(_("Date de désactivation"),		/*colspan*/1,	/*rowspan*/1	));
	$table->add_cell(new TABLE_cell(_("Action"),		/*colspan*/1,	/*rowspan*/1	));

	// Liste des comptes
	$ListeComptes = getComptesDormantsInactifs($global_id_client);
	$SESSION_VARS['compte'] =  $ListeComptes;
	$liste = array();
	if (is_array($ListeComptes)) {
		foreach($ListeComptes as $key=>$value) {
			$etat_cpte = $value['etat_cpte'];
			$id_prod = $value['id_prod'];
			$id_cpte = $value['id_cpte'];
			$table->add_cell(new TABLE_cell($value['num_complet_cpte']));
			$table->add_cell(new TABLE_cell($value['intitule_compte']));
			//$table->add_cell(new TABLE_cell($value['devise']));
			$table->add_cell(new TABLE_cell($ListeComptes['libel']));
			$cell = new TABLE_cell(adb_gettext($adsys['adsys_etat_cpt_epargne'][$etat_cpte]));
			$cell->set_property("color","red");
			$table->add_cell($cell);
			$cell = new TABLE_cell_link(_("Activer"),"$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ced-2&id_cpte=$key&action=1&etat_cpte=$etat_cpte");
			$cell = new TABLE_cell("<input type = 'checkbox' name = 'compte_$id_cpte' />", 1, 1);

			$table->add_cell(new TABLE_cell($date_bloc));
			$table->add_cell($cell);
		}
	}

	//Boutons
	$html->addFormButton(1,1, "Valider", _("Valider"), TYPB_SUBMIT);
	$html->setFormButtonProperties("Valider", BUTP_PROCHAIN_ECRAN, "Ced-2");
	$html->addFormButton(1,2, "retour", _("Retour menu"), TYPB_SUBMIT);
	$html->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, "Gen-10");
	$html->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

	$html->buildHTML();
	echo $html->getHTML();
}
/*}}}*/

/*{{{ Ced-2 : Confirmation d'activation */
else if ($global_nom_ecran == "Ced-2") {
	$tabDataComptes = array();
	//recupérer les comptes selectionnées
	foreach ($SESSION_VARS['compte'] as $id_cpte => $comptes ) {
		if( isset($_POST["compte_$id_cpte"])) {
			$tabDataComptes[$id_cpte] = $comptes;
		}
	}

	unset($SESSION_VARS['compte']);
	if(count($tabDataComptes) > 0 ) {
		$erreur = activerCompteDormant($tabDataComptes);
		debug($erreur,'err');
		if ($erreur->errCode == NO_ERR) {
			$html_msg = new HTML_message(_("Confirmation d'activation des comptes dormants / Inactifs"));
			$html_msg->setMessage(_('compte activer'));
			$html_msg->addButton("BUTTON_OK", 'Gen-10');
			$html_msg->buildHTML();
			echo $html_msg->HTML_code;
			//$html_msg->setMessage(sprintf(_("Le compte n° %s est activé %s"), $InfoCompte['num_complet_cpte'],$blocage)."<br/><br/>"._("N° de transaction")." : <B><code>".sprintf("%09d", $erreur->param['id'])."</code></B>");
		}  else {
			$html_err = new HTML_erreur(_("Echec d'activation des comptes dormants / Inactifs"));
			$html_err->setMessage(_("Erreur")." : ".$error[$erreur->errCode]);
			$html_err->addButton("BUTTON_OK", 'Gen-10');
			$html_err->buildHTML();
			echo $html_err->HTML_code;
			exit();
		}
	}
}
/*}}}*/
else signalErreur(__FILE__,__LINE__,__FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>