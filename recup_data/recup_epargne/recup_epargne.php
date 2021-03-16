<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

require_once("lib/html/HTML_GEN2.php");
require_once("lib/html/FILL_HTML_GEN2.php");
require_once("lib/dbProcedures/handleDB.php");
require_once("lib/dbProcedures/guichet.php");
require_once("lib/dbProcedures/agence.php");
require_once("lib/misc/divers.php");
require_once("lib/misc/VariablesGlobales.php");

/**
 * Récupération de l'épargne d'une IMF.
 * @package Recupdata
 */

//Variable globale - connection à la base de données
global $dbHandler,$global_id_agence;

$global_id_agence=getNumAgence();
?>

<html>
<head>
<title>
<?php echo $ProjectName; ?>
</title>
<script type="text/javascript" src="<?php echo "$http_prefix/lib/java/scp.php?m_agc=$global_id_agence&http_prefix=$http_prefix";?>"></script>
                                   </head>
                                   <body bgcolor=white>
                                                 <table width="100%" cellpadding=5 cellspacing=0 border=0>
                                                                                 <tr>
                                                                                 <td><a target=_blank href="http://www.aquadev.org"><img border=0 title="<?= _("ADbanking Logo") ?>" alt="<?= _("ADbanking Logo") ?>" width=400 height=40 src="../../images/ADbanking_logo.jpg"></a></td>
                                                                                                           <td valign=bottom align=center><font face="helvetica,verdana" size="+2"><?= _("Module de reprise des comptes des clients<") ?>"</font></td>
                                                                                                                                   </tr>
                                                                                                                                   </table>
                                                                                                                                   <hr>
                                                                                                                                   <br><br><P>

                                                                                                                                   <?php

// Etape 1 : Saisie des informations
if (! isset($etape) || $etape == 1) {
  if (!isset($etape)) {
    $SESSION_VARS['saisie'] = array(); // tableau des données saisies
    $SESSION_VARS['liste_produits'] = getListProdEpargne();// liste des produits d'épargne type financier
    $num_client = array();
    $type_cat = array();
    $tabdat = array();
    $solde = array();
    $date_ouv = array();
    $solde_int = array();
    $date_int = array();
    $num_certif = array();
    $cpt = 0;
    $anc_num_cpte = array();
    session_register('num_client', 'type_cat', 'tabdat', 'solde', 'date_ouv', 'solde_int', 'date_int', 'num_certif', 'cpt', 'anc_num_cpte', 'type_num_compte');

  }

  $SESSION_VARS['ErrLine'] = array(); // compteur d'erreur

  // Récupération du type de numérotation des comptes de l'agence
  $id_agc = getNumAgence();
  $AGD = getAgenceDatas($id_agc);
  $type_num_compte = $AGD['type_numerotation_compte'];

  // Création du formulaire
  $myForm = new HTML_GEN2();

  // Création tableau de 10 colonnes
  $myTable =& $myForm->addHTMLTable("recup_epargne", 13, TABLE_STYLE_ALTERN);
  $myTable->set_property("align", "center");
  $myTable->set_property("border", $tableau_border);
  $myTable->set_property("bgcolor", '#e0e0ff');

  // Entête tableau
  $myTable->add_cell(new TABLE_cell(_("N°"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("N° client"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Produit"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Solde compte"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("date ouverture"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Solde calcul intérêt"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Date calcul intérêt"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("N° certificat"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Ancien n° compte"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Mise / dépôt / retenue"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Périodicité"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Nombre de periode"), 1, 1));
  $myTable->add_cell(new TABLE_cell(_("Date fin"), 1, 1));



  // Contenu du tableu :
  $cpt = 40; // Premier lot de 40 lignes
  for ($i=1; $i <= $cpt; ++$i) {
    // Numéro de ligne
    $myTable->add_cell(new TABLE_cell($i, 1, 1));
    // Numéro client
    $myTable->add_cell(new TABLE_cell_input(TYPC_INT, "num_client$i", $SESSION_VARS['saisie'][$i]['num_client'], ""));
    // Produit
    $produit_associe = "<SELECT NAME=\"produit$i\">";
    $produit_associe .= "<option value=\"0\">[Aucun]</option>";
    if (is_array($SESSION_VARS['liste_produits'])) {
      foreach($SESSION_VARS['liste_produits'] as $key=>$value) {
        if ($value['id'] == $SESSION_VARS['saisie'][$i]['produit'])
          $produit_associe .= "<option value=".$value['id']." selected>".$value['libel']."</option>";
        else
          $produit_associe .= "<option value=".$value['id'].">".$value['libel']."</option>";
      }
    }
    $produit_associe .= "</SELECT>\n";
    $myTable->add_cell(new TABLE_cell($produit_associe, 1, 1));
    // Solde courant
    $myTable->add_cell(new TABLE_cell_input(TYPC_MNT, "solde_courant$i", afficheMontant($SESSION_VARS['saisie'][$i]['solde_courant']),
                                            "OnChange=value=formateMontant(value);"));
    // Date ouverture
    $myTable->add_cell(new TABLE_cell_input(TYPC_DTE, "date_ouverture$i", $SESSION_VARS['saisie'][$i]['date_ouverture'], ""));
    // Solde calcul des intérêts
    $myTable->add_cell(new TABLE_cell_input(TYPC_MNT, "solde_interet$i", AfficheMontant($SESSION_VARS['saisie'][$i]['solde_interet']),
                                            "OnChange=value=formateMontant(value);"));
    // Date calcul des intérêt
    $myTable->add_cell(new TABLE_cell_input(TYPC_DTE, "date_interet$i", $SESSION_VARS['saisie'][$i]['date_interet'], ""));
    // Numéro certificat
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "certificat$i", $SESSION_VARS['saisie'][$i]['certificat'], ""));
    // Ancien numéro du compte
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "ancien_numero$i", $SESSION_VARS['saisie'][$i]['ancien_numero'], ""));
    // Montant
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "montant$i", $SESSION_VARS['saisie'][$i]['montant'], ""));
    // periodicite
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "periodicite$i", $SESSION_VARS['saisie'][$i]['periodicite'], ""));
    // Nombre de periode
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "nb_periode$i", $SESSION_VARS['saisie'][$i]['nb_periode'], ""));
    // Date fin
    $myTable->add_cell(new TABLE_cell_input(TYPC_TXT, "date_fin$i", $SESSION_VARS['saisie'][$i]['date_fin'], ""));


  }

  $myForm->addHiddenType("etape");
  $myForm->addHiddenType("vientde");

  $myForm->addFormButton(1, 1, "Valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "Annuler", _("Annuler"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("Valider", BUTP_JS_EVENT, array("onclick"=>"etape.value= '2';"));
  $myForm->setFormButtonProperties("Annuler", BUTP_JS_EVENT, array("onclick"=>"etape.value= '4';vientde.value = '1';"));

  // Génération HTML
  $myForm->buildHTML();
  echo $myForm->getHTML();

}

// Etape 2 : Confirmation des informations saisies
else if ($etape == 2) {
  // Ne pas réinitialiser au cas ou on abandonne un quitter (annuler annuler)
  if ($vientde != 4) {
    // Récupération des informations
    $i = 1;
    $msg = "";
    $l = 0;

    // Tant que les infos obligatoires pour une ligne sont sasies et qu'on ait pas atteint la fin
    while (($ {'num_client'.$i} != '') AND ($ {'solde_courant'.$i} != '') AND ($ {'date_ouverture'.$i} != '') AND ($ {'produit'.$i} != 0)) {
      $Err = 0; // compteur d'erreur

      // Recupération du num client
      $SESSION_VARS['saisie'][$i]['num_client'] = $ {'num_client'.$i};
      // Vérifie si le client existe
      if (! client_exist($SESSION_VARS['saisie'][$i]['num_client'])) {
        $msg .= sprintf(_("Le client de la ligne n°%s n'existe pas !"),$i." ".$SESSION_VARS['saisie'][$i]['num_client'])."<br>";
        $Err+=1;
      }

      // Recupération du produit
      $SESSION_VARS['saisie'][$i]['produit'] = $ {'produit'.$i};
      $PROD = getProdEpargne($SESSION_VARS['saisie'][$i]['produit']);
      $terme = $PROD['terme'];
      $nb_occ = $PROD['nbre_occurrences'];
      $libele = $PROD['libel'];

      // Récupération du solde courant
      $SESSION_VARS['saisie'][$i]['solde_courant'] = recupMontant($ {'solde_courant'.$i});

      // Récupération date ouverture
      $SESSION_VARS['saisie'][$i]['date_ouverture'] = $ {'date_ouverture'.$i};
      $dummy = splitEuropeanDate($SESSION_VARS['saisie'][$i]['date_ouverture']);

      //recuperation montant
      $SESSION_VARS['saisie'][$i]['montant'] = $ {'montant'.$i};

      //recuperation periodicite
      $SESSION_VARS['saisie'][$i]['periodicite'] = $ {'periodicite'.$i};

      //recuperation nombre de periode
      $SESSION_VARS['saisie'][$i]['nb_periode'] = $ {'nb_periode'.$i};

      //recuperation date de fin
      $SESSION_VARS['saisie'][$i]['date_fin'] = $ {'date_fin'.$i};

      // Dans le cas ou la date d'ouverture est postérieure à la date du jour
      if (gmmktime(0,0,0,$dummy[1],$dummy[0],$dummy[2]) >= gmmktime(0,0,0,date('m'),date('d')+1, date('Y'))) {
        $msg .= sprintf(_("La date de la ligne n°%s se situe après la date du jour !") , $i." ".$SESSION_VARS['saisie'][$i]['date_ouverture'] )."<br>";
        $Err+=1;
      }
      // Dans le cas d'uin DAT, voir si la date de fin est postérieure à celle d'aujourd'hui
      if ($terme != 0) {
        if (gmmktime(0,0,0,$dummy[1]+$terme,$dummy[0],$dummy[2]) < gmmktime(0,0,0,date('m'),date('d')+1, date('Y'))) {
          $msg .= sprintf(_("A la date d'ouverture %s de la ligne n°%s, le compte serait déjà fermé !"),$SESSION_VARS['saisie'][$i]['date_ouverture'],$i)."<br>";
          $Err+=1;
        }
      }

      // Récupération du solde de calcul interêts
      $SESSION_VARS['saisie'][$i]['solde_interet'] = recupMontant($ {'solde_interet'.$i});

      // Récupération de la date de mise à jour du solde calcul interêts
      $SESSION_VARS['saisie'][$i]['date_interet'] = $ {'date_interet'.$i};
      $dummy = splitEuropeanDate($SESSION_VARS['saisie'][$i]['date_interet']);
      if ($SESSION_VARS['saisie'][$i]['date_interet'] != "") {
        $have_date=true;
        // Dans le cas ou la date est postérieure à la date
        if (gmmktime(0,0,0,$dummy[1],$dummy[0],$dummy[2]) >= gmmktime(0,0,0,date('m'),date('d')+1, date('Y'))) {
          $msg .= sprintf(_("La date calcul intérêts de la ligne n°%s se situe après la date du jour !") , $i." ".$SESSION_VARS['saisie'][$i]['date_interet'] ) ."<br>";
          $Err+=1;
        }
        // Dans le cas ou la date de fin est antérieure à celle d'aujourd'hui
        if ($terme != 0) {
          if (gmmktime(0,0,0,$dummy[1]+$terme,$dummy[0],$dummy[2]) < gmmktime(0,0,0,date('m'),date('d')+1, date('Y'))) {
            $msg .= sprintf(_("A la date calcul intérêts %s de la ligne n°%s, le compte serait déjà fermé !"),$SESSION_VARS['saisie'][$i]['date_interet'] , $i)."<br />";
            $Err+=1;
          }
        }
      }

      // Récupération numéro certificat
      $SESSION_VARS['saisie'][$i]['certificat'] = $ {'certificat'.$i};
      // Recup ancien numéro de compte
      $SESSION_VARS['saisie'][$i]['ancien_numero'] = $ {'ancien_numero'.$i};
      // Récupération Ancien numéro de compte
      if ($SESSION_VARS['saisie'][$i]['ancien_numero'] != '') {
        if (isNumComplet($SESSION_VARS['saisie'][$i]['ancien_numero']) == true) {
          if ($type_num_compte == 2)
            $num_cli = ereg_replace("([[:digit:]]{4})-([[:digit:]]{5})([[:digit:]]{2})-([[:digit:]]{2})","\\2",$SESSION_VARS['saisie'][$i]['ancien_numero']);
          else
            $num_cli = ereg_replace("([[:digit:]]{3})-([[:digit:]]{6})-([[:digit:]]{2})-([[:digit:]]{2})", "\\2",$SESSION_VARS['saisie'][$i]['ancien_numero']);
          if ($num_cli != $SESSION_VARS['saisie'][$i]['num_client']) {
            $msg .= _("Ligne")." ".$i." : ".sprintf(_("l'ancien numéro de compte %s ne correspond pas au client %s"),$SESSION_VARS['saisie'][$i]['ancien_numero'],$SESSION_VARS['saisie'][$i]['num_client'])."<br>";
            $Err += 1;
          }

          // Vérifie si l'ancien numéro n'existe pas déjà
          $sql = "select count(*) from ad_cpt where id_ag = $global_id_agence and num_complet_cpte = '".$SESSION_VARS['saisie'][$i]['ancien_numero']."';";
          $result = executeDirectQuery($sql);
          if ($result->errCode == NO_ERR)
            if (count($result->param[0]) > 0) {
              $msg .= _("Ligne")." ".$i." : ".sprintf(_("l'ancien numéro de compte %s existe déjà dans la base de donnée"),$SESSION_VARS['saisie'][$i]['ancien_numero'])."<br />";
              $Err += 1;
            }
        }
        // Ancien numéro de compte non valide
        else {
          $msg .=_("Ligne")." ".$i.": ".sprintf(_("l'ancien numéro de compte %s n'est pas valide"),$SESSION_VARS['saisie'][$i]['ancien_numero'])."<br/>";
          $Err += 1;
        }
      }

      // Vérifie qu'il y a pas d'erreur
      if ($msg != "") {
        $msg .= sprintf(_("Certains champs de la ligne n°%s ne sont pas renseignés !"),$i )."<br>";
        $Err +=1;
      }

      //On récuppère les lignes ayant des erreurs ?
      if ($Err > 0) {
        $SESSION_VARS['ErrLine'][$l] = $i;
        $l++;
      }
      $i++;

    }  // Fin Tant que

    $cpt = $i-1; //nombre de lignes renseignées

  } // Fin si vient de

  // Des infos ont été saisies : nbre de lignes informé > 0
  if ($cpt != 0) {
    // Pas d'erreur constatée
    if ($msg == "") {
      // Afficher infos saisies
      $myForm = new HTML_GEN2(_("Choix des comptes internes"));

      $myTable =&  $myForm->addHTMLTable("recup_epargne", 13, TABLE_STYLE_ALTERN);
      $myTable->set_property("align", "center");
      $myTable->set_property("border", $tableau_border);
      $myTable->set_property("bgcolor", '#e0e0ff');

      // Entête tableau
      $myTable->add_cell(new TABLE_cell(_("N°"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("N° client"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Produit"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Solde compte"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("date ouverture"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Solde calcul intérêt"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Date calcul intérêt"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("N° certificat"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Ancien n° compte"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Mise / dépôt / retenue"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Périodicité"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Nombre de periode"), 1, 1));
      $myTable->add_cell(new TABLE_cell(_("Date fin"), 1, 1));


      /////////$db = $dbHandler->openConnection(); //connexion à la base de données

      $i = 1;
      while ($i <= $cpt) {
        if ($i%2) $color = $colb_tableau;
        else $color = $colb_tableau_altern;

        $infos_client = getClientDatas( $SESSION_VARS['saisie'][$i]['num_client']);
        switch ($infos_client['statut_juridique']) {
        case 1 :
          $nom = $row[2]."  ". $row[1];
          break;
        case 2 :
          $nom = $row[3];
          break;
        case 3 :
        case 4 :
          $nom = $row[4];
          break;
        }

        // Numéro de ligne
        $myTable->add_cell(new TABLE_cell($i, 1, 1));
        // Numéro client
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['num_client'],1,1));
        // Produit
        $PROD = getProdEpargne($SESSION_VARS['saisie'][$i]['produit']);
        $myTable->add_cell(new TABLE_cell($PROD['libel'], 1, 1));
        // Solde courant
        $myTable->add_cell(new TABLE_cell(afficheMontant($SESSION_VARS['saisie'][$i]['solde_courant'],false),1,1));
        // Date ouverture
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['date_ouverture'],1,1));
        // Solde calcul des intérêts
        $myTable->add_cell(new TABLE_cell(afficheMontant($SESSION_VARS['saisie'][$i]['solde_interet'],false),1,1));
        // Date calcul des intérêt
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['date_interet'],1,1));
        // Numéro certificat
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['certificat'],1,1));
        // Ancien numéro du compte
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['ancien_numero'],1,1));
        // Montant
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['montant'],1,1));
        // periodicite
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['periodicite'],1,1));
        // nombre de periode
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['nb_periode'],1,1));
        // date fin
        $myTable->add_cell(new TABLE_cell($SESSION_VARS['saisie'][$i]['date_fin'],1,1));

        $i++;
      }

      // Boutons
      $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
      $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick"=>"etape.value= '4';vientde.value = '2';"));
      $myForm->setFormButtonProperties("retour", BUTP_JS_EVENT,  array("onclick"=>"etape.value= '1';cpt.value =".($i-1).";"));
      $myForm->addFormButton(1, 3, "annuler", _("Annuler"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("annuler", BUTP_JS_EVENT,
                                       array("onclick"=>"etape.value= '1';vientde.value='2';cpt.value =".($i-1).";"));

      // Variables post
      $myForm->addHiddenType("etape");
      $myForm->addHiddenType("vientde");
      $myForm->addHiddenType("cpt");
      $myForm->addHiddenType("estpartide");

      $myForm->buildHTML();
      echo  $myForm->getHTML();
    } else { // Erreur rencontrée sur au moins une ligne
      // Afficher infos saisies
      $myForm = new HTML_GEN2(_("ERREUR"));

      $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
      $myTable->set_property("align", "center");
      $myTable->set_property("border", $tableau_border);
      $myTable->set_property("bgcolor", '#e0e0ff');

      // Entête tableau
      $myTable->add_cell(new TABLE_cell(_($msg), 1, 1));

      // Boutons
      $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
      $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';cpt.value = ".$cpt.";"));

      // Variables post
      $myForm->addHiddenType("etape");
      $myForm->addHiddenType("cpt");

      // Génération HTML
      $myForm->buildHTML();
      echo $myForm->getHTML();
    }
  } else { //Aucune info n'a été saisie
    // Afficher infos saisies
    $myForm = new HTML_GEN2(_("ERREUR"));

    $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
    $myTable->set_property("align", "center");
    $myTable->set_property("border", $tableau_border);
    $myTable->set_property("bgcolor", '#e0e0ff');

    // Entête tableau
    $myTable->add_cell(new TABLE_cell(_("Aucune entrée n'a été renseignée !"), 1, 1));

    // Boutons
    $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';"));

    // Variables post
    $myForm->addHiddenType("etape");

    // Génération HTML
    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
}

// Etape 4 : Quitter
else if ($etape == 4) {
  // Préparation de la liste des produits d'épargne
  $produits = array();
  foreach($SESSION_VARS['liste_produits'] as $key=>$value) {
    $produits[$value['id']] = $value;
    $produits[$value['id']]['cpte_cpta'] = checkCptDeviseOK($value['cpte_cpta_prod_ep'],$value['devise']);
    $produits[$value['id']]['solde'] = 0;
    $produits[$value['id']]['subs'] = "";
    $produits[$value['id']]['cpte_int'] = NULL;
  }

  // Récupération des comptes de substitut des produits d'épargne
  $cptes_subs_ep = array();
  $cpte_subs_ps = "";
  $fp=fopen("../traduction.conf",'r');
  if ($fp == false) {
    echo "<BR/><BR/><P align=center><FONT color=red>".sprintf(_("Le fichier %s n'a pas été trouvé à l'endroit attendu"),"<CODE><B>traduction.conf</B></CODE>")."</FONT></P>";
    die();
  }

  while (!feof($fp)) {
    // Récupération d'une ligne du fichier
    $ligne = fgets($fp, 1024);

    if (ereg("^prod_sub_", $ligne)) {
      $ligne = ereg_replace("\t", "|", $ligne); // Remplacer tabulation par |
      $ligne = ereg_replace("\n", "|", $ligne); // Remplacer fin de ligne par |
      $tab = explode("|", $ligne); // Récupérer la ligne sous forme de tableau
      $tab[0] = ereg_replace("^prod_sub_", "", $tab[0]);
      $produits[$tab[0]]['subs'] = trim($tab[1]);
    }
  }
  fclose($fp);

  // Saisie du premier lot terminé
  if ($vientde == 2) {
    // CumulDAV et CumulDAT
    $CumulDAV = 0;
    $CumulDAT = 0;
    $mnt_total =0;
    // Recup comptes comptables
    $cpte_a_vue = $ {'cptecptable1'};
    $cpte_a_terme = $ {'cptecptable2'};
    $cpte_banq = $ {'cptecptable3'};
    // Les comptes ont été renseignés
    if (($cpte_a_vue != "[Aucun]") && ($cpte_a_terme != "[Aucun]") && ($cpte_banq != "[Aucun]") ) {
      // Insertion des données dans la base
      $mvts_compta = array(); // Tableau des mouvements comptables
      global $db;
      $db = $dbHandler->openConnection(); // Connexion à la base de données

      for ($i=1;$i <= $cpt;++$i) {
        if ($SESSION_VARS['saisie'][$i]['ancien_numero'] == '') {
          // Recherche du compte max du client
          $sql = "SELECT max(num_cpte) from ad_cpt where id_ag = $global_id_agence and id_titulaire = ".$SESSION_VARS['saisie'][$i]['num_client'].";";
          $result = executeDirectQuery($sql, TRUE);
          if ($result->errCode == NO_ERR) {
            $numcpt = $result->param[0] + 1;
            // Récupérer le num complet du compte
            $numcptcpt = makeNumCpte($SESSION_VARS['saisie'][$i]['num_client'], $numcpt);
          } else {
            // Message à afficher
            $myForm = new HTML_GEN2(_("Erreur : ".$error[$result->errCode]));

            // Boutons
            $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
            $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';"));

            // Variables post
            $myForm->addHiddenType("etape");

            // Génération HTML
            $myForm->buildHTML();
            echo $myForm->getHTML();
            exit();
          }
        } else {
          if ($type_num_compte == 2) {
            $numcpt = ereg_replace("([[:digit:]]{4})-([[:digit:]]{5})([[:digit:]]{2})-([[:digit:]]{2})", "\\3",$SESSION_VARS['saisie'][$i]['ancien_numero']);
          } else {
            $numcpt = ereg_replace("([[:digit:]]{3})-([[:digit:]]{6})-([[:digit:]]{2})-([[:digit:]]{2})", "\\3", $SESSION_VARS['saisie'][$i]['ancien_numero']);
          }
          $numcptcpt = $SESSION_VARS['saisie'][$i]['ancien_numero'];
        }

        // Récupérer l'id_prod
        $idtype = $SESSION_VARS['saisie'][$i]['produit'];

        // Préparation de la création du compte
        $DATA = array("id_titulaire"=>$SESSION_VARS['saisie'][$i]['num_client'],"intitule_compte"=>$produits[$idtype]['libel'],
                      "utilis_crea"=>1,"date_ouvert"=>$SESSION_VARS['saisie'][$i]['date_ouverture'],"etat_cpte"=>1,
                      "num_cpte"=>$numcpt,"solde"=>$SESSION_VARS['saisie'][$i]['solde_courant'],"num_complet_cpte"=>$numcptcpt,
                      "type_cpt_vers_int"=>1,"id_prod"=>$idtype,"tx_interet_cpte"=>$produits[$idtype]['tx_interet'],
                      "freq_calcul_int_cpte"=>$produits[$idtype]['freq_calcul_int'],"devise"=>$produits[$idtype]['devise'],
                      "mode_calcul_int_cpte"=>$produits[$idtype]['mode_calcul_int'],
                      "mode_paiement_cpte"=>$produits[$idtype]['mode_paiement']);

        // Contrôle sur les données à saisir non obligatoires
        if ($SESSION_VARS['saisie'][$i]['date_interet'] == "")
          $DATA["date_calcul_interets"] = $SESSION_VARS['saisie'][$i]['date_ouverture'];
        else
          $DATA["date_calcul_interets"] = $SESSION_VARS['saisie'][$i]['date_interet'];

        if ($SESSION_VARS['saisie'][$i]['solde_interet'] == "")
          $DATA["solde_calcul_interets"] = $SESSION_VARS['saisie'][$i]['solde_courant'];
        else
          $DATA["solde_calcul_interets"] = $SESSION_VARS['saisie'][$i]['solde_interet'];

        // Données propres aux compte à terme
        if ($produits[$idtype]['terme'] != 0) {
          // Récupérer la date fin
          $dummy = splitEuropeanDate($SESSION_VARS['saisie'][$i]['date_ouverture']);
          $datefin = date("d/m/Y", mktime(0,0,0,$dummy[1] + $produits[$idtype]['terme'], $dummy[0], $dummy[2]));
          $DATA = array_merge($DATA,array("dat_prolongation"=>"f","dat_nb_prolong"=>0,"dat_date_fin"=>$datefin,
                                          "dat_decision_client"=>"f","terme_cpte"=>$produits[$idtype]['terme']));

          if ($SESSION_VARS['saisie'][$i]['certificat'] != "")
            $DATA["dat_num_certif"] = $SESSION_VARS['saisie'][$i]['certificat'];
        }

        // Création du compte
        $id_cpte = creationCompte($DATA);

        $infos_client =	getClientDatas($SESSION_VARS['saisie'][$i]['num_client']);
        $statut_juridique = $infos_client['statut_juridique'];

        // Insertion du mandat si titulaire PP
        if ($statut_juridique == 1) {
          // Récupération de la personne extérieure du mandataire
          $WHERE['id_client'] = $SESSION_VARS['saisie'][$i]['num_client'];
          $PERS_EXT = getPersonneExt($WHERE);
          $id_pers_ext = $PERS_EXT[0]['id_pers_ext'];

          // Insertion du mandat
          $DATA_MAND = array("id_cpte"=>$id_cpte,"id_pers_ext"=>$id_pers_ext,"type_pouv_sign"=>1,"valide"=>'t');
          $result = ajouterMandat($DATA_MAND);
        }

        //enregistrement des ordres de paiements si existe
        if ($SESSION_VARS['saisie'][$i]['montant'] > 0){
          $id_cpte_from = getCompteData($SESSION_VARS['saisie'][$i]['num_client'],1);
          $date_prem_exe = getDateFinMois(date("Y-m-d"));
          $mnt_total_prevu = $SESSION_VARS['saisie'][$i]['montant'] * $SESSION_VARS['saisie'][$i]['nb_periode'];


          $DATA_ORD = array("cpt_from"=>$id_cpte_from["id_cpte"], "cpt_to"=>$id_cpte, "type_transfert"=> 2,"date_prem_exe"=>$date_prem_exe,"date_fin"=>$SESSION_VARS['saisie'][$i]['date_fin'],"montant"=>$SESSION_VARS['saisie'][$i]['montant'],"actif"=>'t',"periodicite"=>$SESSION_VARS['saisie'][$i]['periodicite'],"nb_periode"=>$SESSION_VARS['saisie'][$i]['nb_periode'],"mnt_total_prevu"=>$mnt_total_prevu,"id_ag"=>1);

          $result = ajouterOrdrepermanentModule($DATA_ORD, $SESSION_VARS['saisie'][$i]['num_client'],1);
        }

        // Si un montant est saisi, créer les deux mouvements comptables pour chaque compte créé
        $mnt = recupMontant($SESSION_VARS['saisie'][$i]['solde_courant']);
        if ($mnt != 0) {
          // Mouvement au débit
          $mv_deb = array();
          $mv_deb['devise'] = $produits[$idtype]['devise'];
          $mv_deb['montant'] = $mnt;
          $mv_deb['cpte_int'] = NULL;

          // S'assurer que le compte de substitut est dans la devise du produit
          $mv_deb['cpte_cpta'] = checkCptDeviseOK($produits[$idtype]['subs'], $produits[$idtype]['devise']);
          $mv_deb['sens'] = 'd';
          array_push($mvts_compta, $mv_deb );

          // Mouvement au crédit
          $mv_cred = array();
          $mv_cred['devise'] = $produits[$idtype]['devise'];

          // S'assurer que le compte comptable dans la devise du produit
          $mv_cred['cpte_cpta'] = checkCptDeviseOK($produits[$idtype]['cpte_cpta'], $produits[$idtype]['devise']);
          $mv_cred['montant'] = $mnt;
          $mv_cred['cpte_int'] = $id_cpte;
          $mv_cred['sens'] = 'c';
          array_push($mvts_compta, $mv_cred );
        }
      }

      // Si y a des mouvements à passer
      if (count($mvts_compta) > 0) {
        // Ajout dans l'historique
        $DATA = array("type_fonction"=>501, "login"=>_("admin"), "infos"=>_("Reprise épargne"), "date"=>date("d/m/Y"));
        $id_his = insertHistorique($DATA);

        // Récupération de l'exercice en cours
        $AG = getAgenceDatas($global_id_agence);
        $exo = $AG['exercice'];

        // Création de l'écriture
        $sql = "INSERT INTO ad_ecriture(id_his,id_ag,date_comptable,libel_ecriture,id_jou,id_exo,ref_ecriture) ";
        $sql.= "VALUES ((SELECT currval('ad_his_id_his_seq')),$global_id_agence,now(),makeTraductionLangSyst('Reprise compte épargne'),1,$exo,makeNumEcriture(1, $exo))";
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);
          signalErreur("comptes_rd.php",_("Reprise des comptes : Historique"),$result->getMessage());
        }

        // Passage des mouvements
        foreach($mvts_compta as $key=>$value) {
          // Vérifier que le compte comptable ou son compte de substitue a été paramétré
          if ($value['cpte_cpta'] == '') {
            $dbHandler->closeConnection(false);
            signalErreur(_("Vérifiez que tous les comptes comptables et leurs substituts ont été bien paramétrés"),"");
          }

          $cpte_cpta = $value['cpte_cpta'];
          $mnt = $value['montant'];
          $sens = $value['sens'];
          $cpte_int = $value['cpte_int'];
          $devise = $value["devise"];

          $sql = "INSERT INTO ad_mouvement (id_ecriture,id_ag,compte,cpte_interne_cli,sens,montant,devise,date_valeur) ";
          $sql.="VALUES((SELECT currval('ad_ecriture_seq')),$global_id_agence,'$cpte_cpta',";
          if ($cpte_int==NULL)
            $sql.="NULL,'$sens',$mnt,'$devise',now())";
          else
            $sql.="$cpte_int,'$sens',$mnt,'$devise',now())";

          $result = $db->query($sql);
          if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur("comptes_rd.php","Reprise de l'épargne ",$result->getMessage());
          }

          // Mise à jour du solde du compte mouvementé
          if ($sens == 'd')
            $mnt = $mnt * (-1);

          $sql ="UPDATE ad_cpt_comptable SET solde=solde + ".$mnt." WHERE num_cpte_comptable='".$cpte_cpta."' AND id_ag=$global_id_agence ;";
          $result = $db->query($sql);
          if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur("comptes_rd.php",_("Mise à jour du compte du produit"),$result->getMessage());
          }
        }

        $dbHandler->closeConnection(true); // Déconnexion de la base

        // Message à afficher
        $myForm = new HTML_GEN2(_("L'opération a été effectuée avec succès"));

        // Boutons
        $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '1';"));
        $SESSION_VARS['saisie'] = array();

        // Variables post
        $myForm->addHiddenType("etape");

        // Génération HTML
        $myForm->buildHTML();
        echo $myForm->getHTML();

      } // fin si les comptes comptables sont paramétrés
      else {
        // Message à afficher
        $myForm = new HTML_GEN2();

        $myTable =&  $myForm->addHTMLTable("recup_epargne", 2, TABLE_STYLE_ALTERN);
        $myTable->set_property("align", "center");
        $myTable->set_property("border", $tableau_border);
        $myTable->set_property("bgcolor", '#e0e0ff');

        // Entête tableau
        $myTable->add_cell(new TABLE_cell(_("Erreur : vous avez omis de renseigner un(les) compte(s) !."), 1, 1));

        // Boutons
        $myForm->addFormButton(1, 1, "ok", _("Ok"), TYPB_SUBMIT);
        $myForm->setFormButtonProperties("ok", BUTP_JS_EVENT, array("onclick"=>"etape.value= '2';"));
        $SESSION_VARS['saisie'] = array();

        // Variables post
        $myForm->addHiddenType("etape");

        // Génération HTML
        $myForm->buildHTML();
        echo $myForm->getHTML();
      }  // fin sin non comptes non paramétrés
    } // fin si y a des mouvements à passer
  } // fin si vient de
}
?>

</body>
</html>