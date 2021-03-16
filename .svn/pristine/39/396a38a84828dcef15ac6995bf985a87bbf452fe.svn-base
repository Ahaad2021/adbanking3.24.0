<?php

require_once 'lib/dbProcedures/compta.php';
require_once('lib/html/HTML_GEN2.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/misc/VariablesGlobales.php');
require("lib/html/HtmlHeader.php");


echo "<script language=\"javascript\"\n>";
echo "opener.onfocus= react;\n";
echo "function react() { window.focus();}\n";
echo "</script>";

if (!isset($Recherche)) {
  if (! isset($field_name))
    $field_name="compte_client"; //Nom du champs dans lequel va être inscrit le résultat
  $Title = _("Recherche d'un compte interne");
  $myForm = new HTML_GEN2($Title);

  $myForm->addField("cpt_comptable",_("Compte comptable"), TYPC_TXT);
  $myForm->setFieldProperties("cpt_comptable", FIELDP_IS_REQUIRED, true);
  $myForm->setFieldProperties("cpt_comptable", FIELDP_DEFAULT, $field_cpte_comptable);
  $myForm->setFieldProperties("cpt_comptable", FIELDP_IS_LABEL, true);

  $myForm->addField("num_client",_("Numéro client"), TYPC_TXT);
  $myForm->setFieldProperties("num_client", FIELDP_IS_REQUIRED, true);

  $myForm->addHiddenType("Recherche");
  $myForm->addHiddenType("field_name");
  $myForm->addHiddenType("id_compte");
  $myForm->addHiddenType("cpt_compta");

  //Bouton Rechercher
  $myForm->addFormButton(1, 1, "rechercher", _("Rechercher"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("rechercher", BUTP_CHECK_FORM, true);
  $myForm->setFormButtonProperties("rechercher", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';document.ADForm.Recherche.value = 'OK';document.ADForm.field_name.value='$field_name';document.ADForm.id_compte.value='$id_compte';document.ADForm.cpt_compta.value =document.ADForm.cpt_comptable.value;"));

  //Bouton Annuler
  $myForm->addFormButton(1, 2, "annuler", _("Annuler"), TYPB_BUTTON);
  $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  $myForm->addJS(JSP_FORM, "JScode1", $JScode1);
  $myForm->buildHTML();
  echo $myForm->getHTML();

}

else if ($Recherche == "OK") {
  $myForm = new HTML_GEN2(_("Résultat de la recherche"));
  global  $global_id_agence;

  /* Infos sur le client */
  $CLI = getClientDatas($num_client);
  if ($CLI["statut_juridique"] == 1) { // Personne physique
    $nom_cli = STR_replace("'","",$CLI["pp_nom"])." ".STR_replace("'","",$CLI["pp_prenom"]);
    $myForm->addField("nom_client",_("Nom du client"), TYPC_TXT);
    $myForm->setFieldProperties("nom_client", FIELDP_DEFAULT, $CLI["pp_nom"]);
    $myForm->setFieldProperties("nom_client", FIELDP_IS_LABEL, true);

    $myForm->addField("prenom_client",_("Prénom du client"), TYPC_TXT);
    $myForm->setFieldProperties("prenom_client", FIELDP_DEFAULT, $CLI["pp_prenom"]);
    $myForm->setFieldProperties("prenom_client", FIELDP_IS_LABEL, true);

  }
  elseif($CLI["statut_juridique"] == 2) { // Personne morale
    $nom_cli = STR_replace("'","",$CLI["pm_raison_sociale"]);
    $myForm->addField("raison_sociale",_("Raison sociale"), TYPC_TXT);
    $myForm->setFieldProperties("raison_sociale", FIELDP_DEFAULT, $CLI["pm_raison_sociale"]);
    $myForm->setFieldProperties("raison_sociale", FIELDP_IS_LABEL, true);

  }
  elseif($CLI["statut_juridique"] == 3 || $CLI["statut_juridique"] == 4) { // Groupe informel ou solidaire
    $nom_cli = STR_replace("'","",$CLI["gi_nom"]);
    $myForm->addField("nom_groupe",_("Nom du groupe"), TYPC_TXT);
    $myForm->setFieldProperties("nom_groupe", FIELDP_DEFAULT, $CLI["gi_nom"]);
    $myForm->setFieldProperties("nom_groupe", FIELDP_IS_LABEL, true);

  }

  //Affichage des comptes du client
  $xtHTML = "<br><h3> "._("Comptes associés")."</h3>";
  $xtHTML .= "<TABLE align=\"center\">";
  $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>"._("ID")."</b></td><td><b>"._("Numéro")."</b></td><td><b>"._("Date ouverture")."</b></td><td><b>"._("Produi associé")."</b></td></tr>";

  $where="where id_ag = $global_id_agence AND id_titulaire = $num_client and etat_cpte = 1 and id_prod in (select id from adsys_produit_epargne where id_ag = $global_id_agence AND cpte_cpta_prod_ep='$cpt_compta')";
  $cptes = getComptesClients($where);
  foreach($cptes as $DATAS) {
    $InfoProduit = getProdEpargne($DATAS["id_prod"]);
    $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
    $xtHTML .="<td><a OnClick=\"validateSearch('".$DATAS["num_complet_cpte"]." ".urlencode($nom_cli)."','".$DATAS["id_cpte"]."')\" href=\"#\">".$DATAS["id_cpte"]."</a></td>";
    $xtHTML .="<td align=\"center\">".$DATAS["num_complet_cpte"]."</td>";
    $xtHTML .="<td align=\"center\">".pg2phpDate($DATAS["date_ouvert"])."</td>";
    $xtHTML .="<td align=\"center\">".$InfoProduit["libel"]."</td></tr>";

  }
  $xtHTML .= "</TABLE>";
  $xtHTML .= "<br>";
  $myForm->addHTMLExtraCode("xtHTML", $xtHTML);

  $JScode1 =  "function validateSearch(id,id_compte) {";
  $JScode1 .=  " var lsRegExp = /\+/g;";
  $JScode1 .=  " var  iden =String(unescape(id));";
  $JScode1 .=  " var  iden_cpt =String(unescape(id_compte));";
  $JScode1 .= "\n\t window.opener.document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';";
  $JScode1 .= "\n\t window.opener.document.ADForm.$field_name.value=iden.replace(lsRegExp,' ');";
  $JScode1 .= "\n\t window.opener.document.ADForm.$id_compte.value=iden_cpt.replace(lsRegExp,' ');";
  $JScode1 .= "\n\t window.close();\n}";

  $myForm->addJS(JSP_FORM, "JScode1", $JScode1);
  $myForm->buildHTML();
  echo $myForm->getHTML();

}
require("lib/html/HtmlFooter.php");

?>