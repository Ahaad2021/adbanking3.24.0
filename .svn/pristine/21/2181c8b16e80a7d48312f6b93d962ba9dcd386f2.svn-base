<?php

require_once('lib/html/HTML_GEN2.php');
require_once('lib/html/FILL_HTML_GEN2.php');
require_once('lib/misc/VariablesGlobales.php');
require_once('lib/dbProcedures/client.php');
require_once('lib/misc/tableSys.php');
require_once('lib/html/HTML_message.php');
require("lib/html/HtmlHeader.php");


echo "<script language=\"javascript\"\n>";
echo "opener.onfocus= react;\n";
echo "function react() { window.focus();}\n";
echo "</script>";

if (!isset($Recherche) || $Recherche == "KO") {
  $Title = _("Recherche d'un groupe informel");
  $myForm = new HTML_GEN2($Title);
  $include = array("id_client", "gi_nom");
  $myForm->addTable("ad_cli", OPER_INCLUDE, $include);
  $myForm->addHiddenType("Recherche");
  $myForm->setFieldProperties("id_client", FIELDP_LONG_NAME, "N° groupe");
  $myForm->addFormButton(1, 1, "rech", _("Rechercher"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "ann", _("Annuler"), TYPB_BUTTON);
  $myForm->setFormButtonProperties("ann", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  $myForm->setFormButtonProperties("rech", BUTP_JS_EVENT, array("onclick" => "document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';document.ADForm.Recherche.value = 'OK';"));
  $myForm->setFormButtonProperties("ann", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("rech", BUTP_CHECK_FORM, false);
  $myForm->buildHTML();
  echo $myForm->getHTML();
} else if ($Recherche == "OK") {
  global $global_id_agence;
  require_once('lib/misc/divers.php');
  $Where = array();
  $tabNum=explode("-",$id_client);
  if (sizeof($tabNum)>1) {
    $id_client=$tabNum[1];
  } else {
    $id_client=$tabNum[0];
  }
  $Where["id_client"] = $id_client;
  $Where["gi_nom"] = $gi_nom;
  $Where["id_ag"] = $global_id_agence;
  $Where["gi_nbre_membr"] = $gi_nbre_membr;
  $Where["gi_date_agre"] = $gi_date_agre;
  $nombre = countMatchedClients($Where, "gi");
  if ($nombre > 300) {
    $myMsg = new HTML_message();
    $myMsg->setMessage($nombre._("clients correspondent à vos critères, Veuillez affiner votre recherche"));
    $myMsg->addButton(BUTTON_OK, '');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  } else if ($nombre == 0) {
    $myMsg = new HTML_message();
    $myMsg->setMessage(_("Aucun client ne correspond à vos critères de recherche"));
    $myMsg->addButton(BUTTON_OK, '');
    $myMsg->buildHTML();
    echo $myMsg->HTML_code;
  } else {
    $DATAS = getMatchedClients($Where, "gi");
    $myForm = new HTML_GEN2();
    $myForm->setTitle(_("Résultats de la recherche"));
    $xtHTML = "<br/><table align=\"center\">";
    $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\"><td><b>ID</b></td><td><b>"._("Nom")."</b></td><td><b>"._("Nombre de membres")."</b></td><td><b>"._("Date d'agrément")."</b></td></tr>";
    $i = 0;
    while (is_array($DATAS[$i])) {
      $xtHTML .=  "\n<tr bgcolor=\"$colb_tableau\"><td><a href=\"".$PHP_SELF."?Recherche=".$DATAS[$i]["id_client"]."\">".$DATAS[$i]["id_client"]."</a></td><td>".$DATAS[$i]["gi_nom"]."</td><td>".$DATAS[$i]["gi_nbre_membr"]."</td><td>".(isset($DATAS[$i]["gi_date_agre"])? pg2phpDate($DATAS[$i]["gi_date_agre"]) : "&nbsp")."</td></tr>";
      $i++;
    }
    $xtHTML .= "</table>";
    $myForm->addHTMLExtraCode("xtHTML", $xtHTML);
    $myForm->addFormButton(1,1, "new_search", _("Nouvelle recherche"), TYPB_SUBMIT);
    $myForm->addFormButton(1,2, "cancel", _("Annuler"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("cancel", BUTP_JS_EVENT, array("onclick" => "window.close();"));
    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
} else {
  // Affichage de toutes les infos sur le client. LA variable $Recherche contient ici le numéro du client sur lequel on a cliqué.
  require_once('lib/misc/divers.php');
  $Title = _("Confirmation de choix du client");
  $myForm = new HTML_GEN2($Title);
  $DATAS = getClientDatas($Recherche);

  //ref: #618 
  unset($DATAS['date_creation'],$DATAS['date_modif']);
  $include = array();
  while (list($key, $value) = each($DATAS)) {
    if ($value != "" && $value != '0')
      if ($key!='id_ag')
        array_push($include, $key);
  }
  // Remplissage automatique des champs
  $fill = new FILL_HTML_GEN2();
  $fill->addFillClause("client", "ad_cli");
  $fill->addCondition("client", "id_client", $Recherche);
  $fill->addManyFillFields("client", OPER_INCLUDE, $include);
  // Création du formulaire
  $myForm = new HTML_GEN2();
  $myForm->addTable("ad_cli", OPER_INCLUDE, $include);
  while (list($key, $value) = each($include)) {
    if ($value!='id_ag')
     $myForm->setFieldProperties($value, FIELDP_IS_LABEL, true);

  }
  if (isset($fermer)&& $fermer=='yes') {


    // Groupe solidaire : affichage des membres
    $myForm->addHTMLExtraCode("espace_grp_sol","<br/>");
    $myForm->addHTMLExtraCode("membres","<table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Membres du groupe")."</b></td></tr></table>\n");
    $result = getListeMembresGrpSol($Recherche);
    $membres_grp_sol = $result->param;
    for ($i=1 ;  $i<=sizeof($membres_grp_sol) ; $i++) {
      $myForm->addField("num_client$i", _("Membre $i"), TYPC_INT);
      $myForm->setFieldProperties("num_client$i", FIELDP_IS_LABEL, true);
      $myForm->setFieldProperties("num_client$i", FIELDP_DEFAULT, $membres_grp_sol[$i-1]);
    }
    $myForm->addFormButton(1,1, "cancel", _("Fermer"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("cancel", BUTP_JS_EVENT, array("onclick" => "window.close();"));
  } else {
    $myForm->addFormButton(1, 1, "ok", _("Valider"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "annuler", _("annuler"), TYPB_SUBMIT);
    $myForm->addHiddenType("Recherche");
    $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array ("onclick" => "validateSearch();"));
    $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT, array ("onclick" => "document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';document.ADForm.Recherche.value = 'KO';"));
  }
  $myJSCode .= "\n function validateSearch() {";
  $myJSCode .= "\n\t opener.document.ADForm.m_agc.value = '".$_REQUEST['m_agc']."';\n";
  $myJSCode .= "\n\t opener.document.ADForm.pp_id_gi_lab.value = ".$DATAS["id_client"].";\n";
  $myJSCode .= "\n\t opener.document.ADForm.pp_id_gi.value = ".$DATAS["id_client"].";\n";
  $myJSCode .= "\n\t window.close();\n}";
  $myForm->addJS(JSP_FORM, "code", $myJSCode);
  $fill->fill($myForm);
  $myForm->buildHTML();
  echo $myForm->getHTML();
}
?>