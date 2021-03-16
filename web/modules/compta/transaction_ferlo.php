<?Php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * [431] Comptabilité du système Ferlo
 * Cette fonction appelle les écrans suivants :
 * - Trs-1 : Gestion des transactions Ferlo
 * - Trs-2 : Traitement des transactions Ferlo
 * @package Compta
 * @since v2.9
 */

require_once "lib/html/HTML_menu_gen.php";
require_once 'lib/misc/divers.php';
require_once 'lib/misc/xml_lib.php';

/*{{{ Trs-1 : Gestion des transactions Ferlo */
if ($global_nom_ecran == "Trs-1") {
	$MyPage = new HTML_message(_("Gestion des transactions Ferlo"));
	$MyPage->setMessage(_("Ecritures comptables des transactions du sytéme Ferlo !"));
	$MyPage->addButton("BUTTON_OK", 'Trs-2');
	$MyPage->addCustomButton("annuler", _("Annuler"), 'Gen-14');
	$MyPage->buildHTML();
	echo $MyPage->HTML_code;
}
/*}}}*/

/*{{{ Trs-2 : Traitement des transactions Ferlo */
elseif ($global_nom_ecran == "Trs-2") {
	//Traitement des transactions FERLO
	$echangeFerlo = $lib_path . "/ferlo/transaction/";
	echo _("chemin FERLO")." =" . substr($echangeFerlo, 0, strlen($echangeFerlo) - 1);
	$tab_files = listFiles_trans($echangeFerlo);
	debug($tab_files);
	foreach ($tab_files as $key => $file) {
		$XMLarray = traiteFichierXML($file);
		$nbre_trans = count($XMLarray['XMLFILE']['transaction']);
		$erreur = ecrituresCompbleXml($XMLarray, false);
		if ($erreur->errCode == NO_ERR) {
			unlink($file);
		}
		debug($XMLarray, _("contenu du fichier de transaction"));
	}
	$MyPage = new HTML_message(_("Gestion des transactions Ferlo"));
	$MyPage->setMessage(sprintf(_("Ecritures comptables de %s transactions terminées !"),$nbre_trans));
	$MyPage->addButton("BUTTON_OK", 'Gen-14');
	$MyPage->buildHTML();
	echo $MyPage->HTML_code;
}
/*}}}*/
else
	signalErreur(__FILE__, __LINE__, __FUNCTION__, sprintf(_("L'écran %s n'a pas pu être trouvé"), $global_nom_ecran));
?>