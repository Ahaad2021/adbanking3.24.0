<?php
require_once('lib/dbProcedures/epargne.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/dbProcedures/parametrage.php');
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/engrais_chimiques.php';
require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'lib/misc/divers.php';
require_once 'lib/misc/tableSys.php';
require_once "lib/html/HTML_menu_gen.php";
require_once 'modules/guichet/recu_modules_specifiques.php';
require_once 'lib/dbProcedures/historique.php';


$id_transation = $_GET["id_trans"];
$id_client_source = $_GET["id_client"];

$myForm = new HTML_GEN2("Details beneficiaires");


$xtHTML = "<br /><table align=\"center\" cellpadding=\"5\" width=\"75% \" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding >
    <tr align=\"center\" bgcolor=\"$colb_tableau\"><th>"._("No client")."</th><th>"._("Noms")."</th><th>"._("Montant")."</th></tr>";

$listeDemandeTransfert= getListeTransfertAttenteDetails($id_client_source,$id_transation);


while (list($key1, $DET) = each($listeDemandeTransfert)) {
if ($DET['type_transfert'] != 4){
    $no_client = $DET['id_client_dest'];
  if ($DET['type_transfert'] == 3){
    $get_benef = getTireurBenefDatas($DET['id_beneficiaire']);
    $nom = $get_benef['denomination'];
    $no_client = $get_benef['id'];
  }else {
    $data_client = getClientDatas($no_client);
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
  }
  $mnt_trans = $DET['montant_transfert'];
  if ($DET['type_transfert'] == 3){
    $xtHTML .= "\n<tr bgcolor=\"$color\"><td>" . $no_client . "</td><td>" . $nom . "</td><td>" . afficheMontant($mnt_trans) . "</td></tr>";
  }else {
    $xtHTML .= "\n<tr bgcolor=\"$color\"><td>" . $no_client . "</td><td>" . $nom . "</td><td>" . afficheMontant($mnt_trans) . "</td></tr>";
  }

}
  else {
    $cpte_groupe = explode(',', $DET['groupe_clients']); // FIXME: what if sheetname contains comma?
    $extractedgroupe = array();
    $total_mnt = 0;
    foreach ($cpte_groupe as $compte) {
      $explodes = explode('-', $compte);
      if ($explodes[0] != null) {
        $data_client = getClientDatas($explodes[0]);
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
        $xtHTML .= "\n<tr bgcolor=\"$color\"><td>" . $explodes[0] . "</td><td>" . $nom . "</td><td>" . afficheMontant($explodes[1]) . "</td></tr>";
      }
      $total_mnt += $explodes[1];
    }
    $xtHTML .= "\n<tr bgcolor=\"$color\"><td  colspan = 2> Montant Total </td><td>" . afficheMontant($total_mnt) . "</td></tr>";
  }



}
$xtHTML .= "</table><br /><br/><br />";


$myForm->addHTMLExtraCode("xtHTML".$id_transation, $xtHTML);


$myForm->buildHTML();
echo $myForm->getHTML();
?>