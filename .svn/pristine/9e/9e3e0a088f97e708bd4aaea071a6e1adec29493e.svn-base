
<?php
require_once 'lib/dbProcedures/guichet.php';
require_once 'lib/dbProcedures/billetage.php';
require_once 'lib/dbProcedures/parametrage.php';
require_once 'modules/epargne/recu.php';

if (($global_nom_ecran == "Agu-1") || ($global_nom_ecran == "Dgu-1")) {
  $approv = ($global_nom_ecran == "Agu-1");
  if ($approv) {
    $SESSION_VARS['titre1'] = _("Approvisionnement");
    $direction="out_cc";
  } else {
    $SESSION_VARS['titre1'] = _("Délestage");
    $direction="in_cc";
  }
  $SESSION_VARS['direction']=$direction; //AT-39 : Stocker la variable direction dans la session


  global $global_billet_req;

//FIXME : approvisionnement de la caisse centrale ?
  if ($global_id_guichet) $SESSION_VARS['titre2'] = _("du guichet")." ".$global_guichet;
  else $SESSION_VARS['titre2'] = _("de la caisse centrale");

  $MyPage = new HTML_GEN2($SESSION_VARS['titre1']." ".$SESSION_VARS['titre2']);

  $temp=get_table_devises();
//$valeurs = recupeBillet($devise);

  $html  ="<br>";
  $html .= "<TABLE align=\"center\" bgcolor=$colb_tableau border=$tableau_border cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding>\n";

// En-tête du tableau
  $html .= "<TR bgcolor=$colb_tableau>";
  $html.="<TD><b>"._("N°")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Montant")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Confirmation")."<br />"._("montant")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Numéro")."<br />"._("bordereau")."</b></TD>";
  $html.="<TD align=\"center\"><b>"._("Devise")."</b></TD>";
  $html.="</TR>\n";

  $different_devise=get_table_devises();
  $js="";
  $my_js="";
  if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0){
    $SESSION_VARS["nb_ligne"]=1;
    $infos_appro_delestage_autorise = getListeApprovisionnementDelestageSpecifique($global_id_guichet, 2, $_GET['id_dem']);
    $SESSION_VARS['id_dem'] = $_GET['id_dem'];
    $montant = recupMontant($infos_appro_delestage_autorise['montant']);
    $devise = $infos_appro_delestage_autorise['devise'];
    $SESSION_VARS['devise'] = $devise;
  }else{
    $SESSION_VARS["nb_ligne"]=5;
  }

//foreach ($temp as $key => $value)
  $different_devise=get_table_devises();
  for ($key=1 ; $key <= $SESSION_VARS["nb_ligne"] ; $key++) {
    $i=$key;
    // On alterne la couleur de fond
    if ($i%2)
      $color = $colb_tableau;
    else
      $color = $colb_tableau_altern;

    // une ligne de saisie
    $html .= "<TR bgcolor=$color>\n";

    //numéro de la ligne
    $html .= "<TD><b>$i</b></TD>";

    //Montant
    if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0) { //AT-39
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"montant$key\" size=14 value=\"$montant\" Onchange=\"changeMontant($key);\" readonly></TD>\n";
      $html.="<TD><INPUT TYPE=\"text\" NAME=\"confmontant$key\" size=14 value='$montant'  Onchange=\"changeMontant($key);\"";
    }else{
      $html .= "<TD><INPUT TYPE=\"text\" NAME=\"montant$key\" size=14 value='' Onchange=\"changeMontant($key);\" ></TD>\n";
      $html.="<TD><INPUT TYPE=\"text\" NAME=\"confmontant$key\" size=14 value=''  Onchange=\"changeMontant($key);\"";
    }
    if ($global_billet_req)
      $html.=" disabled=true";
    $html.=">";
    $valeurs = recupeBillet($global_monnaie);
    $SESSION_VARS["nbre_billets"]=sizeof($valeurs); //AT-39 $infos_appro_delestage_autorise
    if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0) {
      $billetage_attente = explode(';',$infos_appro_delestage_autorise['billetage']);
    }
    $value_confmontant = ''; //AT-39
    while (list($key_billet, $value_billet) = each($valeurs)) {
      $value_confmontant = ''; //AT-39
      if (isset($_GET['id_dem']) && $_GET['id_dem'] > 0) { //AT-39 - d'en servir le billetage saisie lors de la demande
        for($bltge=0;$bltge<sizeof($billetage_attente);$bltge++){
          $billetage_attente_details = explode('-',$billetage_attente[$bltge]);
          if ($billetage_attente_details[0] == "$key_billet"){
            $value_confmontant = $billetage_attente_details[1];
          }
        }
      }
      $html.="<INPUT TYPE=\"hidden\" NAME=\"confmontant".$key."_billet_$key_billet\" size=14 value='$value_confmontant'>";
      $html.="<INPUT TYPE=\"hidden\" NAME=\"confmontant".$key."_billet_rendu_$key_billet\" size=14 value='$value_confmontant'>";
    }
    if ($global_billet_req && !isset($_GET['id_dem']))
      $html.="<a href=\"#\" onclick=\"if (document.ADForm.HTML_GEN_LSB_devise$i.value != 0) open_billetage_tab('".$direction."','".$key."'); else alert('"._("Vous devez d\'abord choisir la devise !")."');return false;\">"._("Billetage")."</a></TD>\n";

    // numéro du bordoreau
    $html.="<TD><INPUT TYPE=\"text\" NAME=\"numbord$key\" size=14 value=''></TD>\n";
    // Devise
    if (isset($id_dem) && $id_dem > 0){
      $html.="<INPUT TYPE=\"hidden\" NAME=\"devise".$key."\" size=14 value='$devise'>";
      $html.="<TD><select NAME=\"HTML_GEN_LSB_devise$key\" value=\"$devise\" disabled>";
      $html .= "<option value='$devise'>".$devise."</option>";
      $html .= "</select></TD>\n";
    }else {
      $html.="<TD><select NAME=\"HTML_GEN_LSB_devise$key\" value=\"$devise\" >";
      $html .= "<option value=0>[Aucun]</option>";
      foreach ($different_devise as $key1 => $value)
        $html .= "<option value=$key1>$key1</option>";
      $html .= "</select></TD>\n";
    }

    //  $html.="<TD><INPUT TYPE=\"text\" NAME=\"devise$key\" size=14 value='$key'disabled=true ></TD>\n";
    $html.="</TR>";
    $my_js .= "
              if (document.ADForm.montant$key.value != '')
            {
              if (recupMontant(document.ADForm.montant$key.value) != recupMontant(document.ADForm.confmontant$key.value))
            {
              msg += '- ".sprintf(_("La somme ne correspond pas au billetage pour la ligne %s"),$key)."\\n';ADFormValid = false;
            }
              if (document.ADForm.HTML_GEN_LSB_devise$key.value == 0)
            {
              msg += '- ".sprintf(_("La devise doit etre renseignée pour la ligne %s"),$key)."\\n';ADFormValid = false;
            }
            }";
  }

  $js.=" function changeMontant(i)
     {
       eval('document.ADForm.montant'+i+'.value=formateMontant(document.ADForm.montant'+i+'.value)');
       eval('document.ADForm.confmontant'+i+'.value=formateMontant(document.ADForm.confmontant'+i+'.value)');
     }

       function open_billetage_tab(direction,key)
     {
       var  bon=0;
       var devise=eval('document.ADForm.HTML_GEN_LSB_devise'+key+'.value');
       if(devise !='[Aucun]')
     {
       url = '".$http_prefix."/lib/html/billetage.php?m_agc=".$_REQUEST['m_agc']."&shortName=confmontant'+key+'&direction='+direction+'&devise='+devise+'';
       BillettageWindow = window.open(url, \""._("Billettage")."\", 'alwaysRaised=1,dependent=1,scrollbars,resizable=0');
     }
     }";
  $html.="</TABLE>";

// Check JS
  $MyPage->addJS(JSP_BEGIN_CHECK,"checkMoney",$my_js);

  $MyPage->addHTMLExtraCode("html",$html);
  $MyPage->addJS(JSP_FORM, "modif", $js);
//Boutons
  $MyPage->addFormButton(1,1,"valid", _("Valider"), TYPB_SUBMIT);
  if ($approv)
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Agu-2");
  else
    $MyPage->setFormButtonProperties("valid", BUTP_PROCHAIN_ECRAN, "Dgu-2");
  $MyPage->addFormButton(1,2,"annul", _("Annuler"), TYPB_SUBMIT);
  $MyPage->setFormButtonProperties("annul", BUTP_PROCHAIN_ECRAN, "Gen-6");
  $MyPage->setFormButtonProperties("annul", BUTP_CHECK_FORM, false);

//Affiche
  $MyPage->buildHTML();
  echo $MyPage->getHTML();
}
else if (($global_nom_ecran == "Agu-2") || ($global_nom_ecran == "Dgu-2")) {

  /************************Ticket AT-39********************************/
  $isbilletage = getParamAffichageBilletage();
  $SESSION_VARS['isbilletage'] = $isbilletage;
  // capturer des types de billets de la bd et nombre de billets saisie par l'utilisateur

  $message="";
  //Vérifie que le n° de bordereau soit unique
  //FIXME : enlever ce check ?
  $temp=get_table_devises();
  //  foreach ($temp as $key => $value)
    $i = 0;
    for ($key = 1; $key <= $SESSION_VARS["nb_ligne"]; $key++) {
      if (!empty(${"montant" . $key})) {
        if (isset($SESSION_VARS['id_dem']) && $SESSION_VARS['id_dem'] > 0) {
          $devise = $SESSION_VARS['devise'];
        }else{
          $devise = ${"devise" . $key};
        }
        $num = ${"numbord" . $key};
        $mnt = ${"montant" . $key};

        $DATA[$i]["devise"] = $devise;
        $DATA[$i]["num"] = $num;
        $DATA[$i]["mnt"] = recupMontant($mnt);
        $i++;
        // Billetage pour chaque entre dans les billetage
        $dev = ${"devise" . $key};
        $listTypesBilletArr[$dev] = buildBilletsVect($dev);
        $valeurBilletArr[$dev][$key]= array();
        $total_billetArr[$dev][$key] = array();

        $hasBilletageRecu = true;
        $hasBilletageChange = false;

        //if (!isset($SESSION_VARS['ecran_prec'])) { // ticket 805 ajout if statement
        //insert nombre billet into array
        for ($x = 0; $x < 20; $x++) {
          if (isset(${"confmontant" . $key ."_billet_". $x}) && trim(${"confmontant" . $key ."_billet_". $x}) != '') {
            $valeurBilletArr[$dev][$key][] += trim(${"confmontant" . $key ."_billet_". $x});
          } else {
            if (isset($listTypesBilletArr[$dev][$x]['libel']) && trim($listTypesBilletArr[$dev][$x]['libel']) != '') {
              $valeurBilletArr[$dev][$key][] = 'XXXX';
            }
          }
        }
        $SESSION_VARS['valeurBilletArr'] = $valeurBilletArr; // ticket 805
        // calcul total pour chaque billets
        for ($x = 0; $x < 20; $x++) {
          if ($valeurBilletArr[$dev][$key] [$x] == 'XXXX') {
            $total_billetArr[$dev][$key] [] = 'XXXX';
          } else {
            if (isset ($listTypesBilletArr[$dev] [$x] ['libel']) && trim($listTypesBilletArr[$dev] [$x] ['libel']) != '' && isset ($valeurBilletArr[$dev][$key] [$x]) && trim($valeurBilletArr[$dev][$key] [$x]) != '') {
              $total_billetArr[$dev][$key] [] = ( int )($valeurBilletArr[$dev][$key] [$x]) * ( int )($listTypesBilletArr[$dev] [$x] ['libel']);
            }
          }
        }
        $SESSION_VARS['total_billetArr'] = $total_billetArr; // ticket 805

        //AT-39 : Gestion billetage - utilisation pour apres effectuer demande approvisionnement/delestage
        $insertBilletage = '';
        for ($nbre_billet = 0; $nbre_billet < $SESSION_VARS['nbre_billets']; $nbre_billet++){
          if(isset(${"confmontant".$key."_billet_".$nbre_billet}) && ${"confmontant".$key."_billet_".$nbre_billet} != null){
            $insertBilletage .= $nbre_billet."-".${"confmontant".$key."_billet_".$nbre_billet}.";";
          }
        }
        $SESSION_VARS['insertBilletage'][$key] = $insertBilletage;
      }
      if (exist_bordereau($num) && $num != '') {
        $message .= " - " . sprintf(_("Le n° de bordereau '%s' a déjà été utilisé !"), $num) . "<br />";
      }
    }
  // Ticket Trac 806- AT-39 : Merge les differents array dans l'array pour chaque devise afin d'avoir un array merge par devise
  $data_billet_arr = array();
   foreach($valeurBilletArr as $key1 => $value1){
     $listBillet = buildBilletsVect($key1);
     $nb_data = sizeof($listBillet);
     $i = 0;
      foreach($value1 as $key2 => $value2){
        for($i = 0;$i < $nb_data;$i++){
          if ($value2[$i] > 0) {
            $data_billet_arr[$key1][$i] += $value2[$i];
          }else{
            if ($data_billet_arr[$key1][$i] == 'XXXX' || $data_billet_arr[$key1][$i] == '') {
              $data_billet_arr[$key1][$i] = 'XXXX';
            }
          }
        }
      }
   }

  $data_total_arr = array();
  foreach($total_billetArr as $key3 => $value3){
    $listBillet = buildBilletsVect($key3);
    $nb_data_total = sizeof($listBillet);
    $j = 0;
    foreach($value3 as $key4 => $value4){
      for($j = 0;$j < $nb_data_total;$j++){
        if ($value4[$j] > 0) {
          $data_total_arr[$key3][$j] += $value4[$j];
        }else{
          if ($data_total_arr[$key3][$j] == 'XXXX' || $data_total_arr[$key3][$j] == '') {
            $data_total_arr[$key3][$j] = 'XXXX';
          }
        }
      }
    }
  }

  // Creation des sessions pour recuperer les billetages dans l'ecran de blocage
  $SESSION_VARS['DATA'] =$DATA;
  $SESSION_VARS['listTypesBilletArr']=$listTypesBilletArr;
  $SESSION_VARS['data_billet_arr']=$data_billet_arr;
  $SESSION_VARS['data_total_arr']=$data_total_arr;
  if (sizeof($DATA) == 0) {
    $message .= _("Vous devez remplir au moins une ligne");
  }
  if ($message !='') {
    $MyPage = new HTML_erreur($SESSION_VARS['titre1'].$SESSION_VARS['titre2']);
    $MyPage->setMessage($message);
    $MyPage->addButton(BUTTON_OK, "Gen-6");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
  } else {
    //Call DB

    /******************Ticket AT-39*******************************************/
    $data_agc = getAgenceDatas($global_id_agence);
    if ($data_agc['autorisation_approvisionnement_delestage'] == 't') {
      global $global_nom_login, $global_id_agence, $colb_tableau;
      $info_login = get_login_full_info($global_nom_login);
      if (!isset($SESSION_VARS['id_dem'])) {
        $msg = "";
        $i = 0;
        $count_nbre_ligne = 0;
        for ($i = 1; $i <= $SESSION_VARS["nb_ligne"]; $i++) {
          if (${"montant" . $i} != null) {
            $count_nbre_ligne += 1;
          }
        }
        $info_guichet = getGuichetFromLogin($global_nom_login);
        $SESSION_VARS['id_guichet'] = $global_id_guichet;
        $SESSION_VARS['etat_appro_delestage'] = 1;
        if ($SESSION_VARS['direction'] == "out_cc"){//AT-39 : utilisation variable direction stocker dans la session
          $SESSION_VARS['type_action'] = 1;
        }
        else {
          $SESSION_VARS['type_action'] =2;
        }
        // Affichage de la confirmation
        $html_msg = new HTML_message(_("Demande autorisation " . $SESSION_VARS['titre1']));

        if ($count_nbre_ligne == 1){
          $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le montant " . $SESSION_VARS["titre1"] . ".</span>
<br /><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant1)) ." ". $devise1 . "</span>
<br/></center><input type=\"hidden\" name=\"montant1\" value=\"" . recupMontant($montant1) . "\" /><input type=\"hidden\" name=\"devise1\" value=\"$devise1\" />
<br />Veuillez choisir une option ci-dessous ?<br />");

        }elseif($count_nbre_ligne == 2){
          $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le montant " . $SESSION_VARS["titre1"] . ".</span>
<br /><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant1)) ." ". $devise1 . "</span><br/><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant2)) ." ". $devise2 ."</span>
<br/></center><input type=\"hidden\" name=\"montant1\" value=\"" . recupMontant($montant1) . "\" /><input type=\"hidden\" name=\"devise1\" value=\"$devise1\" />
<br/></center><input type=\"hidden\" name=\"montant2\" value=\"" . recupMontant($montant2) . "\" /><input type=\"hidden\" name=\"devise2\" value=\"$devise2\" />
<br />Veuillez choisir une option ci-dessous ?<br />");
        }elseif($count_nbre_ligne == 3){
          $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le montant " . $SESSION_VARS["titre1"] . ".</span>
<br /><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant1)) ." ". $devise1 . "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant2)) ." ". $devise22. "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant3)) ." ". $devise3 . "</span>
<br/></center><input type=\"hidden\" name=\"montant1\" value=\"" . recupMontant($montant1) . "\" /><input type=\"hidden\" name=\"devise1\" value=\"$devise1\" />
<br/></center><input type=\"hidden\" name=\"montant2\" value=\"" . recupMontant($montant2) . "\" /><input type=\"hidden\" name=\"devise2\" value=\"$devise2\" />
<br/></center><input type=\"hidden\" name=\"montant3\" value=\"" . recupMontant($montant3) . "\" /><input type=\"hidden\" name=\"devise3\" value=\"$devise3\" />
<br />Veuillez choisir une option ci-dessous ?<br />");
        }elseif($count_nbre_ligne == 4){
          $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le montant " . $SESSION_VARS["titre1"] . ".</span>
<br /><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant1)) ." ". $devise1 . "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant2)) ." ". $devise2 . "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant3)) ." ". $devise3 . "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant4)) ." ". $devise4 . "</span>
<br/></center><input type=\"hidden\" name=\"montant1\" value=\"" . recupMontant($montant1) . "\" /><input type=\"hidden\" name=\"devise1\" value=\"$devise1\" />
<br/></center><input type=\"hidden\" name=\"montant2\" value=\"" . recupMontant($montant2) . "\" /><input type=\"hidden\" name=\"devise2\" value=\"$devise2\" />
<br/></center><input type=\"hidden\" name=\"montant3\" value=\"" . recupMontant($montant3) . "\" /><input type=\"hidden\" name=\"devise3\" value=\"$devise3\" />
<br/></center><input type=\"hidden\" name=\"montant4\" value=\"" . recupMontant($montant4) . "\" /><input type=\"hidden\" name=\"devise4\" value=\"$devise4\" />
<br />Veuillez choisir une option ci-dessous ?<br />");
        }elseif($count_nbre_ligne == 5){
          $html_msg->setMessage("<center><span style='color: #FF0000;'><br />Le montant " . $SESSION_VARS["titre1"] . ".</span>
<br /><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant1)) ." ". $devise1 . "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant2)) ." ". $devise2 . "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant3)) ." ". $devise3 . "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant4)) ." ". $devise4 . "</span><br />Montant = <span style='color: #FF0000;font-weight: bold;'>" . afficheMontant(recupMontant($montant5)) ." ". $devise5 . "</span>
<br/></center><input type=\"hidden\" name=\"montant1\" value=\"" . recupMontant($montant1) . "\" /><input type=\"hidden\" name=\"devise1\" value=\"$devise1\" />
<br/></center><input type=\"hidden\" name=\"montant2\" value=\"" . recupMontant($montant2) . "\" /><input type=\"hidden\" name=\"devise2\" value=\"$devise2\" />
<br/></center><input type=\"hidden\" name=\"montant3\" value=\"" . recupMontant($montant3) . "\" /><input type=\"hidden\" name=\"devise3\" value=\"$devise3\" />
<br/></center><input type=\"hidden\" name=\"montant4\" value=\"" . recupMontant($montant4) . "\" /><input type=\"hidden\" name=\"devise4\" value=\"$devise4\" />
<br/></center><input type=\"hidden\" name=\"montant5\" value=\"" . recupMontant($montant5) . "\" /><input type=\"hidden\" name=\"devise5\" value=\"$devise5\" />
<br />Veuillez choisir une option ci-dessous ?<br />");
        }

        if($SESSION_VARS["titre1"] == "Approvisionnement"){
          $html_msg->addCustomButton("btn_demande_autorisation_retrait", "Demande d’autorisation", 'Agu-3');
        }else{
          $html_msg->addCustomButton("btn_demande_autorisation_retrait", "Demande d’autorisation", 'Dgu-3');
        }
        $html_msg->addCustomButton("btn_annuler", "Annuler", 'Gen-6');

        $html_msg->buildHTML();

        echo $html_msg->HTML_code;
        die();
      }

      }

    /*************************************************************************/

    $result = appro_delest($global_id_guichet, ($global_nom_ecran == "Agu-2"),$global_id_agence,$DATA,$SESSION_VARS["nb_ligne"]);
    if ($result->errCode == NO_ERR) {
      $msg="<ul>";
      foreach ($DATA as $key => $value) {
        setMonnaieCourante($DATA[$key]["devise"]);
        $msg.="<li>".$SESSION_VARS['titre1']." ".$SESSION_VARS['titre2']." "._("réalisé avec succès")." (".afficheMontant($DATA[$key]["mnt"], true).") !<br /></li>";
      }
      if (isset($SESSION_VARS['id_dem']) && $SESSION_VARS['id_dem'] > 0) {
         $erreur2 = updateEtatApprovisionnementDelestage($SESSION_VARS['id_dem'], 3, $result->param);

          if ($erreur2->errCode == NO_ERR) {
            // Commit
            $dbHandler->closeConnection(true);
            unset($SESSION_VARS['id_dem']);
            unset($SESSION_VARS['devise']);
          }
      }
      ($isbilletage == 'f') ? $hasBilletageChange = false : $hasBilletageChange = true;
      print_recu_appro_delestage($isbilletage,$global_id_guichet,$result->param,$DATA,$listTypesBilletArr,$data_billet_arr,$data_total_arr,$global_nom_ecran_prec);

      $msg .= "</ul><br /><br />"._("Numéro de transaction")." : <code>".sprintf("%09d", $result->param)."</code>";
      $MyPage = new HTML_message($SESSION_VARS['titre1'].$SESSION_VARS['titre2']);
      $MyPage->setMessage($msg);
      $MyPage->addButton(BUTTON_OK, "Gen-6");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    } else {
      $MyPage = new HTML_erreur($SESSION_VARS['titre1'].$SESSION_VARS['titre2']);
      $MyPage->setMessage(sprintf(_("%s a échoué : solde insuffisant !"),$SESSION_VARS['titre1'].$SESSION_VARS['titre2']));
      $MyPage->addButton(BUTTON_OK, "Gen-6");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
    }
  }
}
else if (($global_nom_ecran == "Agu-3") || ($global_nom_ecran == "Dgu-3")) {
  //ticket AT-39
  global $global_id_agence,$global_id_client;

  $id_guichet = $SESSION_VARS['id_guichet'];
  $etat_appro_delestage = $SESSION_VARS['etat_appro_delestage'];
  $type_action = $SESSION_VARS['type_action'];
  $i = 0;
  $num_transaction = array();
  for ($i = 1; $i <= $SESSION_VARS["nb_ligne"]; $i++) {
    if(isset(${"montant".$i}) && ${"montant".$i} != null){
      $insertBilletage=$SESSION_VARS['insertBilletage'][$i]; //AT-39 de stocker le billetage saisie lors de la demande
      $erreur = insertApproDelestageAttente($id_guichet,${"montant".$i},${"devise".$i},$etat_appro_delestage,$type_action,$insertBilletage);
      $num_transaction[$i] = $erreur->param['max'];
    }
  }

  /*$id_dmde_transfert = getDataTransfertAttente($global_id_client,$transfert_id_cpte_client_src,1);
  $id_dem = $id_dmde_transfert['max_id'];
  $myErr = ajout_historique(76, $transfert_id_client_src,'Demande Autorisation de Transfert No.'.$id_dem." Mise en attente", $global_nom_login, date("r"), null, null, null);*/
  if ($erreur->errCode != NO_ERR) {
    $html_err = new HTML_erreur("Echec lors de la demande autorisation approvisionnement/delestage.");

    $err_msg = $error[$myErr->errCode];

    $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

    $html_err->addButton("BUTTON_OK", 'Gen-6');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

  if ($erreur->errCode == NO_ERR) {
    ($isbilletage == 'f') ? $hasBilletageChange = false : $hasBilletageChange = true;
    print_recu_appro_delestage($SESSION_VARS['isbilletage'],$global_id_guichet,$num_transaction,$SESSION_VARS['DATA'],$SESSION_VARS['listTypesBilletArr'],$SESSION_VARS['data_billet_arr'],$SESSION_VARS['data_total_arr'],$global_nom_ecran_prec);

unset($SESSION_VARS['isbilletage']);unset($SESSION_VARS['DATA']);unset($SESSION_VARS['listTypesBilletArr']);unset($SESSION_VARS['data_billet_arr']);unset($SESSION_VARS['data_total_arr']);
    $html_msg = new HTML_message("Confirmation demande autorisation approvisionnement/delestage");

    $html_msg->setMessage("La demande d'autorisation a été envoyée.");

    $html_msg->addButton("BUTTON_OK", 'Gen-6');

    $html_msg->buildHTML();
    echo $html_msg->HTML_code;

  }else {
    $html_err = new HTML_erreur("Echec lors de la demande autorisation approvisionnement/delestage.");

    $err_msg = $error[$erreur->errCode];

    $html_err->setMessage(sprintf("Erreur : %s !", $err_msg));

    $html_err->addButton("BUTTON_OK", 'Gen-6');

    $html_err->buildHTML();
    echo $html_err->HTML_code;
  }

}
else signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Ecran '$global_nom_ecran' inconnu !"
?>