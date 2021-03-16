<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */
/**
 * Récupération des crédits d'une IMF.
 * @package Recupdata
 */

require_once ("lib/misc/divers.php");
require_once ("lib/html/HTML_GEN2.php");
require_once ("lib/html/FILL_HTML_GEN2.php");
require_once ("lib/html/HTML_menu_gen.php");
require_once ("lib/html/HTML_message.php");
require_once ("lib/misc/VariablesGlobales.php");
require_once ("lib/misc/VariablesSession.php");
require_once ("DB.php");
require_once ("lib/misc/Erreur.php");
require_once ("lib/misc/tableSys.php");
require_once ("lib/dbProcedures/agence.php");
require_once ("lib/dbProcedures/client.php");
require_once ("lib/dbProcedures/handleDB.php");
require_once ("lib/dbProcedures/credit.php");
require_once ("lib/algo/ech_theorique.php");
require_once ("lib/html/html_table_gen.php");
require_once 'batch/batch_declarations.php';


// liste des fonctions
function getDossierReprise() {
  global $dbHandler,$global_id_agence;
  $db = $dbHandler->openConnection();

  $sql = "select * from ad_dcr where etat = 10;";

  $result=$db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql);
  }
  $retour = array();
  while ($row = $result->fetchrow(DB_FETCHMODE_ASSOC))
    $retour[$row["id_doss"]] = $row;

  $dbHandler->closeConnection(true);
  return $retour;
}
/**
 * Cette fonction est une copie de la fonction completeEcheancier du fichier echeancier.php
 * A la différence qu'elle ne se base pas sur un produit de crédit mais sur les caractéristiques fournies par l'utilisateur
 *
 * @param array $echeancier : Tableau associatif renvoyé par la fonction calcul_echeancier_theorique
 * @param array $parametre :Tableau associatif avec les éléments suivants
 * 					 - ['index'] => Index du début de l'échéancier (Ex: 0)
 * 	         - ['periodicite'] => Périodicité de remboursement (Ex: 3)
 * 	         - ['mode_calc_int'] => Mode de calcul des intérêts (Ex: 1)
 * 	         - ['nbre_jour_mois'] => Nombre de jours par mois (Ex: 30)
 * 	         - ['differe_jours'] => Différé (en jours) (Ex: 0)
 * 	         - ['date'] => Date de déboursement ou rééchelonnement
 * 	         - ['id_doss'] => ID du dossier de crédit (-1 si pas de dossier)
 * 	         - ['duree'] => Durée du crédit en mois (utile si remboursement en une fois)
 * @return arrray $retour : vecteur contenant pour chaque échéance un tableau associatif avec les infos sur les échéances
 *	         -  $DATAECH["id_doss"] =  Numéro du dossier (-1 si pas de dossier)
 * 	         -  $DATAECH["id_ech"] = Numéro de l'échéance
 * 	         -  $DATAECH["mnt_cap"] = Montant en capital
 * 	         -  $DATAECH["mnt_int"] = Montant en intérêts
 * 	         -  $DATAECH["mnt_gar"] = Montant en garantie
 * 	         -  $DATAECH["remb"] = Echéance remboursée (toujours à false)
 * 	         -  $DATAECH["solde_cap"] = Solde en capital
 * 	         -  $DATAECH["solde_int"] = Solde en intérêts
 * 	         -  $DATAECH["solde_gar"] = Solde en garantie
 * 	         -  $DATAECH["solde_pen"] = Solde en pénalités (toujours 0)
 * 	         -  $DATAECH["mnt_reech"] = Montant rééchelonné (toujours 0)
 * 	         -  $DATAECH["date_ech"] = Date de l'échéance
 *
 */
function completeEcheancierLibre($echeancier,$parametre) {
  global $adsys, $global_id_agence;
  $index=$parametre["periodicite"];
  $global_id_agence=getNumAgence();
  // Récupération de la base pour le calcul des intérpets
  // 1 => 360 jours => Mois de 30 jours
  // 2 => 365 jours => Mois correspondent au calendrier
  $AG = getAgenceDatas($global_id_agence);
  $base_taux = $AG["base_taux"];

  // Echéancier non stocké dans la base de données
  // D'où la nécessité de le générer entièrement
  $total_cap = 0;
  $total_int = 0;
  $total_gar = 0;
  $period = $adsys["adsys_duree_periodicite"][$index];
  reset($echeancier); // Réinitialise le pointeur de tableau des écheances

  // Calcul de la périodicité en jour
  if ($parametre["periodicite"] == 6) // Si on doit tout rembourser en une fois
    $duree_Periode = $parametre["duree"] * $parametre["nbre_jour_mois"];
  elseif ($parametre["periodicite"] == 8)
  $duree_Periode = $adsys["adsys_duree_periodicite"][$parametre["periodicite"]]*7;
  else
    // FIXME nbre_jours_mois est toujours 30 et ne sert à rien en fait. A supprimer
    $duree_Periode = $adsys["adsys_duree_periodicite"][$parametre["periodicite"]]*$parametre["nbre_jour_mois"];
  $diff = $parametre["differe_jours"];
  $date = $parametre["date"];//Date de déboursement ou rééchelonnement
  $retour = array();
  $dern_jour = false; // Variable utilisée pour le cas particulier du dernier jour du mois
  $mm_save = $jj_save = 0;
  while (list($key,$echanc) = each($echeancier)) {
    $i = $key + $parametre["index"];
    $DATAECH = array();
    // Remplissage de $DATAECH avec les données retournées par l'échéancier.
    $DATAECH["id_doss"] =  $parametre["id_doss"];
    $DATAECH["id_ech"] = $i;
    $DATAECH["mnt_cap"] = ceil($echanc["mnt_cap"]).'';
    $DATAECH["mnt_int"] = ceil($echanc["mnt_int"]).'';
    $DATAECH["mnt_gar"] = ceil($echanc["mnt_gar"]).'';
    $DATAECH["remb"] ='f';
    $DATAECH["solde_cap"] = ceil($echanc["mnt_cap"]).'';
    $DATAECH["solde_gar"] = ceil($echanc["mnt_gar"]).'';

    /* Dégressif variable, les intérêts à venir seront comptabilisés dynamiquement au jour le jour */
    ////if ($parametre["mode_calc_int"] == 3)
    ////  $DATAECH["solde_int"] = 0;
    ////else
    $DATAECH["solde_int"] = ceil($echanc["mnt_int"]).'';


    $DATAECH["solde_pen"] ='0';
    $DATAECH["mnt_reech"] ='0';

    // Calcul des dates d'échéance la date doit être au format jj/mm/aaaa
    $periodicite = $parametre["periodicite"];

    if ($date != "") { // Rappel : $date = Date du déboursement / rééchelonnement
      $r = explode("/", $date);
      $jj = (int) 1*$r[0];
      $mm = (int) 1*$r[1];
      $aa = (int) 1*$r[2];

      if ($base_taux == 1) // 360 jours
        $date = date("d/m/Y",mktime(0,0,0,$mm,$jj + $duree_Periode + $diff,$aa,0));
      else if ($base_taux == 2) {
        if (in_array($periodicite, array(8))) // hebdomadaire
          $date = date("d/m/Y",mktime(0,0,0,$mm,$jj + $duree_Periode + $diff,$aa,0));
        else if (in_array($periodicite, array(1,3,4,5,7))) { // Périodes de mois entiers
          $nbre_mois_periode = $adsys["adsys_duree_periodicite"][$periodicite];
          if ($dern_jour)
            $date = date("d/m/Y", mktime(0,0,0,$mm+$nbre_mois_periode+1,0,$aa));
          else
            $date = date("d/m/Y", mktime(0,0,0,$mm+$nbre_mois_periode,$jj+$diff,$aa));
        } else if ($periodicite == 2) {
          if ($i%2 == 1) { // Impair ==> d(j) = d(j-1) + 15 jours.
            if ($dern_jour)
              $date = date("d/m/Y", mktime(0,0,0,$mm+1,15,$aa));
            else
              $date = date("d/m/Y", mktime(0,0,0,$mm,$jj+$diff+15,$aa));
            // On enregistre le jour et le mois de d(j-1)
            $mm_save = $mm;
            $jj_save = $jj+$diff;
          } else // Pair ==> d(j) = d(j-2) + 1 mois.
            if ($dern_jour)
              $date = date("d/m/Y", mktime(0,0,0,$mm_save+2,0,$aa));
            else
              $date = date("d/m/Y", mktime(0,0,0,$mm_save+1,$jj_save,$aa));
        }
        // Remboursement en une fois
        else if ($periodicite == 6) {
          $date = date("d/m/Y", mktime(0,0,0,$mm+$parametre["duree"],$jj+$diff, $aa));
        }
        if ($i == 1) { // On est à la première échéance
          // On va rechercher si cette première échéance correspond à la fin d'un mois
          // Dans ce cas, on considère que l'utilisateur
          // désire que toutes les échéances tombent à la fin du mois
          $r = explode("/", $date);
          $jj = (int) 1*$r[0];
          $mm = (int) 1*$r[1];
          $aa = (int) 1*$r[2];
          if (mktime(0,0,0,$mm,$jj,$aa) == mktime(0,0,0,$mm+1,0,$aa))
            $dern_jour = true; // On est au dernier jour du mois
          else
            $dern_jour = false;
        }
      } else
        signalErreur("echeancier.php", "completeEcheancier()", "Type de base de calcul inconnue : [$base_taux]");
      $diff = 0;
      $DATAECH["date_ech"] = $date;
    }
    $retour[$key] = $DATAECH;
  }
  return $retour;
}

/**
 * Cette fonction calcule le montant normalement dû en pénalités pour une échéance donnée
 *
 * @param integer $id_doss : ID du dossier que l'on désire activer durant la reprise des crédits
 * @param integer $id_ech : ID de l'échéance pour laquelle on calcule le retard
 * @param montant $solde_cap
 * @param date $a_dateRepriseBilan: date foramt postgres, date de la reprise du bilan
 * @return montant $solde_pen = Le montant attendu à titre de pénalités pour cette échéance
 */
function calculePenalitesCreditRepris($id_doss, $id_ech, $solde_cap, $a_dateRepriseBilan) {
  global $dbHandler, $global_id_agence;
  $db = $dbHandler->openConnection();

  // Récupération des infos sur le produit de crédit
  $sql = "SELECT delai_grac, tx_interet, mode_calc_int, mnt_penalite_jour, prc_penalite_retard, typ_pen_pourc_dcr, type_duree_credit FROM ad_dcr a, adsys_produit_credit b WHERE a.id_ag = b.id_ag and b.id_ag = $global_id_agence and a.id_prod = b.id AND a.id_doss = $id_doss";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur("recup_credit.php", "calculePenalitesCreditRepris()", $result->getMessage());
  }
  $tmprow = $result->fetchrow();
  $delai_grac = $tmprow[0];
  $tx_interet = $tmprow[1];
  $mode_calc_int = $tmprow[2];
  $mnt_penalite_jour = $tmprow[3];
  $prc_penalite_retard = $tmprow[4];
  $typ_pen_pourc_dcr = $tmprow[5];
  $type_duree_credit = $tmprow[6];

  // Récupération des infos sur l'échéance
  $sql = "SELECT date_ech, mnt_cap, mnt_int FROM ad_etr WHERE id_ag = $global_id_agence and id_doss = $id_doss AND id_ech = $id_ech";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur("recup_credit.php", "calculePenalitesCreditRepris()", $result->getMessage());
  }
  $tmprow = $result->fetchrow();
  $date_ech = pg2phpDate($tmprow[0]);
  $mnt_cap = $tmprow[1];
  $mnt_int = $tmprow[2];

  // Recherche du nombre de jours de retard
  if (isBefore($date_ech, pg2phpDate($a_dateRepriseBilan))) {
    global $global_monnaie_prec;
    $nbre_jours_retard = nbreDiffJours($date_ech, pg2phpDate($a_dateRepriseBilan));
    // On tient compte du délai de grace
    $nbre_jours_retard -= $delai_grac;
    if ($nbre_jours_retard < 0)
      $nbre_jours_retard = 0;

    if ($typ_pen_pourc_dcr == 1) { // Pourcentage du solde restant dû en capital
      if ($type_duree_credit == 1) { // Le crédit est calculé sur une base mensuelle
        $solde_pen = 0;
        $solde_pen += $nbre_jours_retard * $mnt_penalite_jour; // Montant fixe par jour
        $solde_pen += round(($prc_penalite_retard) * ($nbre_jours_retard/30) * ($solde_cap), $global_monnaie_prec);
      } else if ($type_duree_credit == 2) { // Le crédit est calculé sur une base hebdomadaire
        $solde_pen = 0;
        $solde_pen += $nbre_jours_retard * $mnt_penalite_jour; // Montant fixe par jour
        $solde_pen += round(($prc_penalite_retard) * ($nbre_jours_retard/7) * ($solde_cap), $global_monnaie_prec);
      }
    } else if ($typ_pen_pourc_dcr == 2) { // Pourcentage du montant de l'échéance en capital et intérêts
      if ($type_duree_credit == 1) { // Le crédit est calculé sur une base mensuelle
        $solde_pen = 0;
        $solde_pen += $nbre_jours_retard * $mnt_penalite_jour; // Montant fixe par jour
        $solde_pen += round(($prc_penalite_retard) * ($nbre_jours_retard/30) * ($mnt_cap+$mnt_int), $global_monnaie_prec);
      } else if ($type_duree_credit == 2) { // Le crédit est calculé sur une base hebdomadaire
        $solde_pen = 0;
        $solde_pen += $nbre_jours_retard * $mnt_penalite_jour; // Montant fixe par jour
        $solde_pen += round(($prc_penalite_retard) * ($nbre_jours_retard/7) * ($mnt_cap+$mnt_int), $global_monnaie_prec);
      }
    }
    else{
      $solde_pen = 0;
    }
  } else // L'échéance n'est pas en retard
    $solde_pen = 0;

  $dbHandler->closeConnection(true);
  return $solde_pen;
}


/**
 * Fonction qui renvoie l'échéance en cours d'un échéancier
 *
 * @author Papa Ndiaye
 * @since 2.6.3
 * @param array  Tableau contenant les infos de l'échénacier : les index sont les numéros des échéances
 * @return int le numéro de l'échéance en cours sinon Null
 */
function getEcheanceCourante($ECH,$a_dateRepriseBilan) {
  // le nombre d'échéances
  $nb_ech = sizeof($ECH);

  // Recherche de l'échéance précédente à l'échéance en cours
  $ech_prec = 0 ;

  for ($i = $nb_ech; $i > 0; $i--) {
    if ($ech_prec == 0 && (isBefore($ECH[$i]["date_ech"], pg2phpDate($a_dateRepriseBilan))))
      $ech_prec = $i;
  }

  if ($ech_prec == $nb_ech)
    $ech_cour = NULL;
  else
    $ech_cour = $ech_prec + 1;

  return $ech_cour;
}


?>

<!-- Header commun -->
<html>
<head>
<title><?=_("Assistant de reprise des crédits ADbanking")?></title>
<?php  require_once 'lib/html/stylesheet.php';
?>
<style type="text/css">
            h1 {font:14pt helvetica,verdana;
                margin-top:
                15px;
                margin-bottom:
                15px;
               }
            tr.tablealternheader { background-color : #e0e0ff; }
            tr.tablealternligneimpaire  { background-color : #ffd5d5; }
            tr.tablealternlignepaire { background-color : #e0e0ff; }
            table.tableclassic { background-color : #e0e0ff; }
            </style>
            <script type="text/javascript" src="<?php echo "$http_prefix/lib/java/scp.php?m_agc=".$_REQUEST['m_agc']."&http_prefix=$http_prefix";?>"></script>
                                               </head>
                                               <body bgcolor=white>
                                                             <table width="100%" cellpadding=5 cellspacing=0 border=0>
                                                                                             <tr>
                                                                                             <td><a target=_blank href="http://www.aquadev.org"><img border=0 title="ADbanking Logo" alt="ADbanking Logo" width=400 height=40 src="../../images/ADbanking_logo.jpg"></a></td>
                                                                                                                       <td valign=bottom align=center><font face="helvetica,verdana" size="+2"><?=_("Module de reprise des crédits")?></font></td>
                                                                                                                                               </tr>
                                                                                                                                               </table>

                                                                                                                                               <?php

// Pour l'écran 2, on n'est pas sûr que le client existe donc pas d'appel à ce niveau
if (isset($id_client) && $prochain_ecran > 2) {
  echo "<hr><p align=\"center\"><font face=\"helvetica,verdana\">Client $id_client (".getClientName($id_client).")</p><hr>";
}

// Ecran 1 : Sélection du client
//if (!isset($prochain_ecran) || ($prochain_ecran == 1)) {
                                                                                                                                               if ($prochain_ecran == 1) {

  $db = $dbHandler->openConnection();
  $sql = "SELECT * FROM ad_ses";
  $result = $db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__);
  } else if ($result->numrows() == 0) {
    $myForm = new HTML_message(_("Erreur de Connexion"));
    $myForm->setMessage(_("Avertissement : Vous devez ouvrir une session ADbanking avant d'éffectuer cette opération. Voulez vous vous connecter maintenant?"));
    $myForm->addButton(BUTTON_OUI,"login");;
    $myForm->addButton(BUTTON_NON, "nologin");
    $myForm->buildHTML();
    echo $myForm->HTML_code;

    echo "<br><p align=center>"._("L'écran d'ouverture de session sur ADbanking se fermera automatiquement si l'authentification réussie.")."</p>"      		;
  } else if ($result->numrows() >= 1) {
    session_unregister("id_client");
    $SESSION_VARS = array();
    session_register("SESSION_VARS");
    $SESSION_VARS['infos_client'] = NULL; // tableau des infos sur le client
    $SESSION_VARS['liste_membres'] = array(); //liste des membres si GS sinon le client lui-même
    $SESSION_VARS['fictif'] = array(); // infos des dossiers fictifs des membres d'un groupe solidaire

    $colb_tableau = '#e0e0ff';
    $myForm = new HTML_GEN2(_("Veuillez sélectionner le client avec lequel vous désirez travailler"));
    $myForm->addField("id_client", _("N° de client"), TYPC_INT);
    $myForm->addLink("id_client", "rechercher", _("Rechercher"), "#");
    $myForm->setLinkProperties("rechercher", LINKP_JS_EVENT, array("onclick" => "OpenBrw('../../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=id_client', '"._("Recherche")."');return false;"));
    $myForm->setFieldProperties("id_client", FIELDP_JS_EVENT, array("onkeypress" => "return true;"));
    $myForm->addButton("id_client", "ok", _("OK"), TYPB_SUBMIT);
    $myForm->setButtonProperties("ok", BUTP_PROCHAIN_ECRAN, '2');
    $myForm->setButtonProperties("ok", BUTP_AXS, 3);
    $myForm->setFieldProperties("id_client", FIELDP_IS_REQUIRED, true);

    $SESSION_VARS['ecran_precedent'] = 1; // suivi des écrans
    $dbHandler->closeConnection(true);

    $myForm->buildHTML();
    echo $myForm->getHTML();
  }
}

// Ecran login : Redirection vers l'Ecran de connexion
else if ($prochain_ecran == "login") {
  echo "<SCRIPT type=\"text/javascript\">\n";
  echo "window.open(\"$SERVER_NAME/login/main_login.php?recup_data=Vrai\", '$window_name', \"menubar=no,resizable=yes,status=yes,toolbar=no,location=no\");\n";
  echo "</SCRIPT>\n";

  $myForm = new HTML_message(_("Recharger la page"));
  $myForm->setMessage(_("Info : Si l'ouverture de session sur ADbanking a réussie, recharger cette page"));
  $myForm->addButton(BUTTON_OK,1);

  $myForm->buildHTML();
  echo $myForm->HTML_code;
}

// Ecran nologin : Fermeture de la fenetre Reprise de crédit
else if ($prochain_ecran == "nologin") {
  echo "<SCRIPT type=\"text/javascript\">\n";
  echo "opener = self;\n";
  echo "self.close();\n";
  echo "</SCRIPT>\n";
}

// Ecran 2 : Informations sur le client et sur les membres si c'est un groupe solidaire

else if ($prochain_ecran == 2) {

 /* $result = executeDirectQuery("SELECT date_comptable FROM ad_ecriture WHERE id_ag = $global_id_agence and libel_ecriture = 'Reprise du bilan'",true);
  if ($result->errCode == NO_ERR && $result->param[0] != NULL) {
    $SESSION_VARS['date_reprise_bilan'] = $result->param[0];
  } else {
    // Si on ne trouve pas la date de reprise du bilan, on reprend le crédit à la date d'aujourd'hui
    $SESSION_VARS['date_reprise_bilan'] = php2pg(date("d/m/Y"));
  }*/

	// Si on ne trouve pas la date de reprise du bilan, on reprend le crédit à la date d'aujourd'hui
  $SESSION_VARS['date_reprise_credit'] = php2pg(date("d/m/Y"));

  // Vérifier que le numéro du client est correct
  //$db = $dbHandler->openConnection();
  $SESSION_VARS['infos_doss'] = array();

  // Récupération des infos du client
  if ($id_client != '') {
    $SESSION_VARS['infos_client'] = getClientDatas($id_client);


    if ($SESSION_VARS['infos_client'] == NULL) { // Le client n'existe pas, retour au menu précédent
      $colb_tableau = '#e0e0ff';
      $html_err = new HTML_erreur(_("Numéro incorrect"));
      $html_err->setMessage(_("Le client n'existe pas"));
      $html_err->addButton("BUTTON_OK", "1");
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit;
    }

    // A ce niveau , on est sûr que le client existe, vérifier qu'il est actif
    if ($SESSION_VARS['infos_client']['etat'] != 2) { // Le client n'est pas actif
      $etat_client = getLibel("adsys_etat_client", $SESSION_VARS['infos_client']['etat']);
      $colb_tableau = '#e0e0ff';
      $html_err = new HTML_erreur(_("Le client est $etat_client"));
      $html_err->setMessage(_("On reprend les crédits uniquement pour les clients actifs"));
      $html_err->addButton("BUTTON_OK", "1");
      $html_err->buildHTML();
      echo $html_err->HTML_code;
      exit;
    }

    // Le client existe est actif, afficher ses informations
    $SESSION_VARS['global_id_agence'] = getNumAgence();
    $SESSION_VARS['AGC'] = getAgenceDatas($global_id_agence);

  } // Fin si id_client est renseigné

  $colb_tableau = '#e0e0ff';
  $myForm = new HTML_GEN2("");

  /* Creation d'un tableau pour les infos du client */
  $table1 = new HTML_TABLE_table(2, TABLE_STYLE_ALTERN);
  $table1->set_property("title",_("Informations sur le client "));
  $table1->add_cell(new TABLE_cell(_("Numéro du client ")));
  $table1->add_cell(new TABLE_cell(_($SESSION_VARS['infos_client']['id_client'])));

  $table1->add_cell(new TABLE_cell(_("Nom ou raison sociale du client ")));
  $table1->add_cell(new TABLE_cell(_(getClientName($SESSION_VARS['infos_client']['id_client']))));

  $table1->add_cell(new TABLE_cell(_("Statut juridique ")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_stat_jur"][$SESSION_VARS['infos_client']['statut_juridique']])));

  if ($CLI["statut_juridique"] == 1) {
    $table1->add_cell(new TABLE_cell(_("Date de naissane ")));
    $table1->add_cell(new TABLE_cell(_(pg2phpDate($SESSION_VARS['infos_client']['pp_date_naissance']))));

    $table1->add_cell(new TABLE_cell(_("Lieu de naissance ")));
    $table1->add_cell(new TABLE_cell(_($SESSION_VARS['infos_client']['pp_lieu_naissance'])));
  }

  $table1->add_cell(new TABLE_cell(_("Sociétaire ")));
  if ($SESSION_VARS['infos_client']["nbre_parts"] > 0)
    $table1->add_cell(new TABLE_cell(_("OUI")));
  else
    $table1->add_cell(new TABLE_cell(_("NON")));

  $table1->set_property("align", "center");
  $table1->set_property("border", $tableau_border);
  $table1->set_property("bgcolor", $colb_tableau);
  echo $table1->gen_HTML();

  $dossiers = array(); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
  $liste = array(); // Liste des dossiers à afficher
  $index = 1;

  // Liste des dossiers de crédit encours de reprise
  $table = new HTML_TABLE_table(4, TABLE_STYLE_ALTERN);
  $table->set_property("title",_("Liste des dossiers de crédit encours de reprise "));
  $table->add_cell(new TABLE_cell(_("N° dossier ")));
  $table->add_cell(new TABLE_cell(_("Date demande")));
  ///$table->add_cell(new TABLE_cell(_("Etat")));
  $table->add_cell(new TABLE_cell(_("Activer")));
  $table->add_cell(new TABLE_cell(_("Supprimer")));

  // Liste des dossiers individuels et de groupe encours de reprise du client
  $whereCl=" AND etat=10";
  $dossiers_reels = getIdDossier($SESSION_VARS['infos_client']['id_client'], $whereCl);
  // Liste des dossiers individuels de crédit encours de reprise
  if (is_array($dossiers_reels))
    foreach($dossiers_reels as $id_doss=>$value)
    if ($value['gs_cat'] != 2) { // exclure les dossiers repris en groupe. Ils doivent être validés en même via le groupe
      $dossiers[$index] = $value;
      $table->add_cell(new TABLE_cell($value['id_doss']));
      $table->add_cell(new TABLE_cell(pg2phpDate($value['date_dem'])));
      ///$table->add_cell(new TABLE_cell($adsys["adsys_etat_dossier_credit"][$value['etat']]));
      $contenu = "Activer" ;
      $lien = "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=12&index=".$index;
      $table->add_cell(new TABLE_cell_link($contenu, $lien));

      $contenu = "Supprimer";
      $lien = "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=10&index=".$index;
      $table->add_cell(new TABLE_cell_link($contenu, $lien));
      $index++;
    }

  // Si GS, récupération des membres du groupe et les dossiers des membres dans le cas de dossiers multiples
  if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
    // Récupération des membres du groupe
    $result = getListeMembresGrpSol($SESSION_VARS['infos_client']['id_client']);
    if (is_array($result->param))
      foreach($result->param as $key=>$id_cli) {
      $nom_client = getClientName($id_cli);
      $SESSION_VARS['liste_membres'][$id_cli] = $nom_client;
    }

    // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
    $whereCl = " WHERE id_membre=".$SESSION_VARS['infos_client']['id_client']." and gs_cat = 2";
    $dossiers_fictifs = getCreditFictif($whereCl);
    // Pour chaque dossier fictif du GS, récupération des dossiers réels des membres du GS
    $dossiers_membre = getDossiersMultiplesGS($SESSION_VARS['infos_client']['id_client']);
    foreach($dossiers_fictifs as $id=>$value) {
      // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
      $infos = '';
      foreach($dossiers_membre as $id_doss=>$val)
      if (($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 10)) {
        $date_dem = $val['date_dem'];
        $infos .= " N° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
      }
      if ($infos != '') { // Si au moins on a 1 dossier
        $dossiers[$index] = $value; // on garde les infos du dossier fictif
        $table->add_cell(new TABLE_cell($infos)); // on affiche les id des dossiers réels
        $table->add_cell(new TABLE_cell(pg2phpDate($date_dem)));
        ///$table->add_cell(new TABLE_cell($adsys["adsys_etat_dossier_credit"][$value['etat']]));
        $contenu = "Activer" ;
        $lien = "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=12&index=".$index;
        $table->add_cell(new TABLE_cell_link($contenu, $lien));

        $contenu = "Supprimer";
        $lien = "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=10&index=".$index;
        $table->add_cell(new TABLE_cell_link($contenu, $lien));
        $index++;
      }
    }
  } else { // Personne physique, Personne morale ou  Groupe Informel
    // on considère le client lui-même comme étant le seul membre
    $nom_client = getClientName($SESSION_VARS['infos_client']['id_client']);
    $SESSION_VARS['liste_membres'][$SESSION_VARS['infos_client']['id_client']] = $SESSION_VARS['infos_client']['id_client']." ".$nom_client;
  }


  $SESSION_VARS['dossiers'] = $dossiers;

  $table->set_property("align", "center");
  $table->set_property("border", $tableau_border);
  $table->set_property("bgcolor", $colb_tableau);
  echo $table->gen_HTML();

  // Le client a t-il droit à un crédit ?
  if ($SESSION_VARS['infos_client']["nbre_parts"] > 0 || $SESSION_VARS['AGC']["type_structure"] == 2 || $SESSION_VARS['AGC']["octroi_credit_non_soc"] == 't') {
    $myForm->addFormButton(1, 1, "ajout_doss", _("Création dossier"), TYPB_SUBMIT);
    $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, '1');
    $myForm->setFormButtonProperties("ajout_doss", BUTP_PROCHAIN_ECRAN, '3');
  } else {
    $myForm->addFormButton(1, 1, "retour", _("Retour"), TYPB_SUBMIT);
    $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, '1');
  }

  $SESSION_VARS['ecran_precedent'] = 2; // suivi des écrans

  $myForm->buildHTML();
  echo $myForm->getHTML();
}
// Ecran 3 : choix du produit de crédit
elseif($prochain_ecran == 3) {
  $colb_tableau = '#e0e0ff';
  $myForm = new HTML_GEN2(_("Etape 1: Choix du produit de crédit"));

  // Liste des produits octroyables au client
  $SESSION_VARS['produits_credit'] = array(); // tableaux des produits
  $SESSION_VARS['choix_produit'] = array(); // liste de choix

  if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) // si Groupe solidaire (GS)
    $condition = "WHERE gs_cat=1 OR gs_cat=2"; // Si c'est un GS, récupérer que les produits de crédit destinés aux GS
  else // Personne physique, Personne morale ou  Groupe Informel
    $condition = "WHERE gs_cat IS NULL OR (gs_cat!=1 AND gs_cat!=2)";// Ne pas récupérer les produits destinés aux GS

  $PRODS = getProdInfo($condition);
  foreach($PRODS as $key=>$infos_prod) {
    $SESSION_VARS['produits_credit'][$infos_prod['id']] = $infos_prod; // tableaux des produits
    $SESSION_VARS['choix_produit'][$infos_prod['id']] = $infos_prod['libel']; // liste de choix
  }
  $myForm->addField("id_prod", _("Type de produit de crédit"), TYPC_LSB);
  $myForm->setFieldProperties("id_prod", FIELDP_ADD_CHOICES, $SESSION_VARS['choix_produit']);
  $myForm->setFieldProperties("id_prod", FIELDP_IS_REQUIRED,true);
  $myForm->addLink("id_prod", "produit",_("Détail produit"), "#");
  $myForm->setLinkProperties("produit",LINKP_JS_EVENT,array("onClick"=>"open_produit_recup(document.ADForm.HTML_GEN_LSB_id_prod.value);"));

  // Ajout boutons
  $myForm->addFormButton(1, 1, "valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1, 2, "retour", _("Retour"), TYPB_SUBMIT);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, '2');
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, '4');

  $SESSION_VARS['ecran_precedent'] = 3; // suivi des écrans
  $myForm->buildHTML();
  echo $myForm->getHTML();

}

// Ecran 4 : Création des dossiers de crédit
elseif($prochain_ecran == 4) {
  global $adsys;
  $colb_tableau = '#e0e0ff';
  $Myform = new HTML_GEN2(_("Etape 2: Création du dossier de crédit"));

  // Récupération du id du produit choisi
  if (isset($HTML_GEN_LSB_id_prod))
    $SESSION_VARS['id_prod'] = $HTML_GEN_LSB_id_prod;

  // id du client choisi au premier écran
  $id_client = $SESSION_VARS['infos_client']['id_client'];

  // Client pouvant avoir un dossier de crédit réel
  $SESSION_VARS['clients_dcr'] = array(); // liste des clients pouvant avoir un dossier réel
  if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 1) // Si GS à dossier unique, seul le GS a un dossier réel
    $SESSION_VARS['clients_dcr'][$id_client] = $SESSION_VARS['liste_membres'][$id_client]." ".getClientName($id_client);
  elseif($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2) //GS avec doss multiples,chaque mbre a son dossier
  $SESSION_VARS['clients_dcr'] = $SESSION_VARS['liste_membres'];
  else // Crédit pour personne physique, Personne morale ou groupe informel : un seul dossier pour le client
    $SESSION_VARS['clients_dcr'][$id_client] = $SESSION_VARS['liste_membres'][$id_client];

  // Récupérations des utilisateurs. C'est à dire les agents gestionnaires
  $SESSION_VARS['utilisateurs'] = array();
  $utilisateurs = getUtilisateurs();
  foreach($utilisateurs as $id_uti=>$val_uti)
  $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];

  // Objets de crédit
  $obj_dem = getObjetsCredit();

  // Affichage des champs
  $CPT_PRELEV_FRAIS  = array(); // compte sur les quels on peut ptrélever les frais de dossier
  $JS_check = ""; // Javascript de validation de la saisie
  $js_copie_mnt_dem = ""; // sauvegare de mnt_dem si le champ est désactivé

  // Contrôle des dates et des montants
  $js_date = "";
  // Contrôle des montants
  $js_mnt = "";
  // precision de la devise du credit
  $devise_prod=$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise'];
  $DEV = getInfoDevise($devise_prod);// recuperation d'info sur la devise'
  $precision_devise=pow(10,$DEV["precision"]);

  // Pour chaque client pouvant avoir un dossier reel
  foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
    $Myform->addHTMLExtraCode("espace".$id_cli,"<BR><b><P align=center><b>"._("Saisie du dossier de")." $nom </b></P>");
    // Ajout de champs
    $Myform->addField("nom_client".$id_cli, _("Client"), TYPC_TXT);
    $Myform->addField("num_cre".$id_cli, _("Numéro crédit"), TYPC_TXT);
    $Myform->addField("id_prod".$id_cli, _("Produit de crédit"), TYPC_TXT);
    $Myform->addField("devise".$id_cli, _("Devise"), TYPC_TXT);
    $Myform->addField("date_dem".$id_cli, _("Date de la demande"), TYPC_DTE);
    $Myform->addField("obj_dem".$id_cli, _("Objet de la demande"), TYPC_LSB);
    $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_ADD_CHOICES, $obj_dem);
    $Myform->addField("detail_obj_dem".$id_cli, _("Détail de la demande"), TYPC_TXT);
    $Myform->addField("mnt_dem".$id_cli, _("Montant de la demande"), TYPC_MNT);
    $Myform->addHiddenType("hid_mnt_dem".$id_cli, $SESSION_VARS['infos_doss'][$id_cli]['mnt_dem']);

    //type de durée : en mois ou en semaine
    $type_duree = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['type_duree_credit'];
    $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules
    $Myform->addField("duree_mois".$id_cli, _("Durée en ".$libelle_duree), TYPC_INT);
    $Myform->addField("differe_jours".$id_cli, _("Différé en jours"), TYPC_INT);
    $Myform->addField("differe_ech".$id_cli, _("Différé en échéances"), TYPC_INT);
    $Myform->addField("delai_grac".$id_cli, _("Délai de grace"), TYPC_INT);
    $Myform->addField("etat".$id_cli, _("Etat du dossier"), TYPC_TXT);
    $Myform->addField("cpt_liaison".$id_cli, _("Compte de liaison"), TYPC_LSB);
    $cptes_liaison = getComptesLiaison ($id_cli, $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);
    $CPT_LIE = array(); // compte de liaison pour un client
    foreach($cptes_liaison as $id_cpte=>$compte) {
      $CPT_LIE[$id_cpte] = $compte["num_complet_cpte"]." ".$compte["intitule_compte"];
      $CPT_PRELEV_FRAIS[$id_cpte] =  $compte["num_complet_cpte"]." ".$compte["intitule_compte"];
    }
    $Myform->setFieldProperties("cpt_liaison".$id_cli, FIELDP_ADD_CHOICES, $CPT_LIE);
    $Myform->addField("id_agent_gest".$id_cli, _("Agent gestionnaire"), TYPC_LSB);
    $Myform->setFieldProperties("id_agent_gest".$id_cli, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
    $Myform->addField("prelev_auto".$id_cli, _("Prélèvement automatique"), TYPC_BOL);
    $Myform->addField("cre_date_approb".$id_cli, _("Date approbation"), TYPC_DTE);
    $Myform->addField("cre_date_debloc".$id_cli, _("Date déblocage"), TYPC_DTE);
    $Myform->addField("cre_mnt_octr".$id_cli, _("Montant octroyé"), TYPC_MNT);
    $Myform->addField("mnt_commission".$id_cli, _("Commission"), TYPC_MNT);
    $Myform->addField("mnt_assurance".$id_cli, _("Assurance"), TYPC_MNT);
    $Myform->addField("gar_num".$id_cli, _("Garantie numéraire attendue"), TYPC_MNT);
    $Myform->addField("gar_mat".$id_cli, _("Garantie matérielle attendue"), TYPC_MNT);
    $Myform->addField("gar_tot".$id_cli, _("Garantie totale attendue"), TYPC_MNT);
    $Myform->addField("gar_num_encours".$id_cli, _("Garantie encours attendue"), TYPC_MNT);

    // Copie de  mnt dem au cas ou mnt_dem est désactivé
    $js_copie_mnt_dem .= "document.ADForm.hid_mnt_dem".$id_cli.".value = recupMontant(document.ADForm.mnt_dem".$id_cli.".value);";
    $js_init = "initMnt($id_cli);"; // initialisation de champs, au chargement de la page, en fonction de mnt demandé
    $Myform->addJS(JSP_FORM, "js_int".$id_cli, $js_init);

    $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_JS_EVENT, array("OnFocus"=>"resetMnt($id_cli);"));
    // Calcule des montants dépendant du montant demandé
    $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_JS_EVENT, array("OnChange"=>"initMnt($id_cli);"));

    // GS avec dossiers reels multiples, calcul du montant total demandé
    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2)
      $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_JS_EVENT, array("onchange"=>"setMontantDemande();"));

    //  Champs obligatoires
    $Myform->setFieldProperties("num_cre".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("id_prod".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("date_dem".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("duree_mois".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("etat".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("cre_mnt_octr".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("cre_date_approb".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("cre_date_debloc".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("cpt_liaison".$id_cli, FIELDP_IS_REQUIRED, true);
    $Myform->setFieldProperties("detail_obj_dem".$id_cli, FIELDP_IS_REQUIRED, true);

    // Valeurs par défaut
    $nom_cli = getClientName($id_cli);
    $Myform->setFieldProperties("nom_client".$id_cli,FIELDP_DEFAULT, $nom_cli);
    $Myform->setFieldProperties("id_prod".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["libel"]);
    $SESSION_VARS['infos_doss'][$id_cli]['num_cre'] = getNumCredit($id_cli);
    if ($SESSION_VARS['infos_doss'][$id_cli]['num_cre'] == NULL)
      $SESSION_VARS['infos_doss'][$id_cli]['num_cre'] = 1;
    else
      $SESSION_VARS['infos_doss'][$id_cli]['num_cre'] += 1;

    $Myform->setFieldProperties("num_cre".$id_cli,FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['num_cre']);
    $Myform->setFieldProperties("etat".$id_cli,FIELDP_DEFAULT, 10); // en cours de reprise
    if ($SESSION_VARS['infos_doss'][$id_cli]["prelev_auto"] == 't')
      $Myform->setFieldProperties("prelev_auto".$id_cli, FIELDP_DEFAULT, true);
    else
      $Myform->setFieldProperties("prelev_auto".$id_cli, FIELDP_DEFAULT, false);

    $Myform->setFieldProperties("devise".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise']);
    $Myform->setFieldProperties("date_dem".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['date_dem']);
    $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['mnt_dem']);
    $Myform->setFieldProperties("duree_mois".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['duree_mois']);
    $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['obj_dem']);
    $Myform->setFieldProperties("detail_obj_dem".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['detail_obj_dem']);
    $Myform->setFieldProperties("differe_jours".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['differe_jours']);
    $Myform->setFieldProperties("differe_ech".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['differe_ech']);
    $Myform->setFieldProperties("delai_grac".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['delai_grac']);

    $Myform->setFieldProperties("cre_mnt_octr".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['cre_mnt_octr']);
    $Myform->setFieldProperties("cre_date_approb".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['cre_date_approb']);
    $Myform->setFieldProperties("cre_date_debloc".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['cre_date_debloc']);
    $Myform->setFieldProperties("cpt_liaison".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['infos_doss'][$id_cli]['cpt_liaison']);

    // Champs grisés
    $Myform->setFieldProperties("nom_client".$id_cli,FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("id_prod".$id_cli,FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("etat".$id_cli,FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("num_cre".$id_cli,FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("nom_client".$id_cli,FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("devise".$id_cli,FIELDP_IS_LABEL, true);

    // Mise à jour date approb / debloc en fonction de date de demande
    $majdate="document.ADForm.HTML_GEN_date_cre_date_approb".$id_cli.".value=document.ADForm.HTML_GEN_date_date_dem".$id_cli.".value;";
    $majdate.="document.ADForm.HTML_GEN_date_cre_date_debloc".$id_cli.".value=document.ADForm.HTML_GEN_date_date_dem".$id_cli.".value;";
    // Mise à jour montant octroyée en fonction du montant de demande
    $majmnt ="document.ADForm.cre_mnt_octr".$id_cli.".value = formateMontant(document.ADForm.mnt_dem".$id_cli.".value); ";

    $Myform->setFieldProperties("date_dem".$id_cli, FIELDP_JS_EVENT, array("OnChange" => $majdate));
    $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_JS_EVENT, array("OnChange" => $majmnt));

    // Contrôle des dates
    $js_date .= "if (isBefore(document.ADForm.HTML_GEN_date_cre_date_approb".$id_cli.".value, document.ADForm.HTML_GEN_date_date_dem".$id_cli.".value))\n ";
    $js_date .= "{msg +='- ".sprintf(_("La date de demande du client %s doit être antérieure ou égale à la date d\'approbation\\n';"),$id_cli);
    $js_date .= " ADFormValid = false;}\n";

    $js_date .="if (isBefore(document.ADForm.HTML_GEN_date_cre_date_debloc".$id_cli.".value, document.ADForm.HTML_GEN_date_cre_date_approb".$id_cli.".value))\n ";
    $js_date .="{msg +='- ".sprintf(_("La date d\'approbation du client %s doit être antérieure ou égale à la date de déblocage\\n';\n"),$id_cli);
    $js_date .= " ADFormValid = false;}\n";

    //Contôle des montants
    $js_mnt.="if(recupMontant(document.ADForm.mnt_dem".$id_cli.".value )<recupMontant(document.ADForm.cre_mnt_octr".$id_cli.".value))";
    $js_mnt.= "{msg +='- ".sprintf(_("Le montant octroyé du client %s doit être inférieure ou égal au montant demandé\\n';\n"),$id_cli);
    $js_mnt .= "ADFormValid =false;}\n";

  } // fin parcours des clients bénéficiaires

  // Traitement particulier aux dossiers fictifs des GS
  if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 1) { // GS avec dossier unique
    $Myform->setFieldProperties("mnt_dem".$SESSION_VARS['infos_client']['id_client'], FIELDP_IS_LABEL, true);
    //Javascript :
    $js_code  = "function setMontantDemande(){\n"; // calcul le montant total demandé
    $js_code  .="document.ADForm.mnt_dem".$SESSION_VARS['infos_client']['id_client'].".value = formateMontant(";
    foreach($SESSION_VARS['liste_membres'] as $id_cli=>$val)
    $js_code  .="recupMontant(document.ADForm.mnt_dem".$id_cli.".value)+\n";

    $js_code=substr($js_code,0,strlen($js_code)-2);
    $js_code  .= ");";
    $js_code  .= "document.ADForm.cre_mnt_octr".$SESSION_VARS['infos_client']['id_client'].".value = document.ADForm.mnt_dem".$SESSION_VARS['infos_client']['id_client'].".value}\n";

    $js_code  .= "setMontantDemande();\n";

    $Myform->addJS(JSP_FORM, "js1", $js_code);
    $champHidden="<input type=\"hidden\" name=\"nb_mem\" value=\"".sizeof($SESSION_VARS['liste_membres'])."\">";
    $Myform->addHTMLExtraCode("champ_hidden_nb_mem",$champHidden);
    $Myform->addHTMLExtraCode("detail_credit","<BR><Table align=\"center\" valign=\"middle\" bgcolor=\"".$colb_tableau."\"><tr><td><b>"._("Détails du crédit par membre")."</b></td></tr></Table>\n");
    foreach($SESSION_VARS['liste_membres'] as $id_cli=>$val) {
      $Myform->addField("membre".$id_cli, _("Membre"), TYPC_TXT);
      $Myform->setFieldProperties("membre".$id_cli, FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("membre".$id_cli, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("membre".$id_cli, FIELDP_DEFAULT,$id_cli." ".getClientName($id_cli));
      $Myform->addField("obj_dem".$id_cli, _("Objet demande"), TYPC_LSB);
      $obj_dem = getObjetsCredit();
      $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_ADD_CHOICES, $obj_dem);
      $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("obj_dem".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['fictif'][$id_cli]['obj_dem']);
      $Myform->addField("detail_obj_dem".$id_cli, _("Détail demande"), TYPC_TXT);
      $Myform->setFieldProperties("detail_obj_dem".$id_cli, FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("detail_obj_dem".$id_cli, FIELDP_DEFAULT, $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem']);
      $Myform->addField("mnt_dem".$id_cli, _("Montant demande"), TYPC_MNT);
      $Myform->setFieldProperties("mnt_dem".$id_cli, FIELDP_IS_REQUIRED, true);
      $Myform->setFieldProperties("mnt_dem".$id_cli,FIELDP_DEFAULT,$SESSION_VARS['fictif'][$id_cli]['mnt_dem'],true);

      $Myform->setFieldProperties("mnt_dem".$id_cli,FIELDP_JS_EVENT,array("onchange"=>"setMontantDemande();initMnt(".$SESSION_VARS['infos_client']['id_client'].")"));
      $js_init = "initMnt(".$SESSION_VARS['infos_client']['id_client'].");"; // initialisation de champs, au chargement de la page, en fonction de mnt demandé
      $Myform->addJS(JSP_FORM, "js_int".$id_cli, $js_init);
      $Myform->addHTMLExtraCode("epace".$id_cli,"<BR>");
    }

  }
  elseif($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2) { // GS avec dossiers multiples
    $Myform->addHTMLExtraCode("espace".$SESSION_VARS['infos_client']['id_client'],"<BR><b><P align=center><b>"._("Dossier du groupe")." </b></P>");
    $Myform->addField("nom_client".$SESSION_VARS['infos_client']['id_client'], _("Nom du groupe"), TYPC_TXT);
    $Myform->addField("id_prod".$SESSION_VARS['infos_client']['id_client'], _("Produit de crédit"), TYPC_TXT);
    $Myform->addField("gs_cat".$SESSION_VARS['infos_client']['id_client'],_("Catégorie dossier"),TYPC_TXT);
    $Myform->addField("mnt_dem".$SESSION_VARS['infos_client']['id_client'],_("Montant total demandé"),TYPC_MNT);
    ///$Myform->addHiddenType("hid_mnt_dem".$global_id_client, $val_doss['cre_mnt_octr']);

    $Myform->setFieldProperties("nom_client".$SESSION_VARS['infos_client']['id_client'],FIELDP_DEFAULT,$SESSION_VARS['infos_client']['id_client']." ".getClientName($SESSION_VARS['infos_client']['id_client']));
    $Myform->setFieldProperties("id_prod".$SESSION_VARS['infos_client']['id_client'],FIELDP_DEFAULT, $SESSION_VARS['id_prod']." ".$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["libel"]);
    $Myform->setFieldProperties("gs_cat".$SESSION_VARS['infos_client']['id_client'],FIELDP_DEFAULT,$adsys["adsys_categorie_gs"][$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["gs_cat"]]);
    $Myform->setFieldProperties("nom_client".$SESSION_VARS['infos_client']['id_client'], FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("id_prod".$SESSION_VARS['infos_client']['id_client'], FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("gs_cat".$SESSION_VARS['infos_client']['id_client'], FIELDP_IS_LABEL, true);
    $Myform->setFieldProperties("mnt_dem".$SESSION_VARS['infos_client']['id_client'], FIELDP_IS_LABEL, true);

    // ajout des comptes du groupe dans les comptes de prélèvement des frais de dossier
    $cptes_liaison = getComptesLiaison ($SESSION_VARS['infos_client']['id_client'], $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["devise"]);
    foreach($cptes_liaison as $id_cpte=>$compte)
    $CPT_PRELEV_FRAIS[$id_cpte] =  $compte["num_complet_cpte"]." ".$compte["intitule_compte"];

    // Javascript
    $js_code  = "function setMontantDemande(){\n"; // calcul le montant total demandé
    $js_code  .="document.ADForm.mnt_dem".$SESSION_VARS['infos_client']['id_client'].".value = formateMontant(";
    foreach($SESSION_VARS['liste_membres'] as $id_cli=>$val)
    $js_code  .="recupMontant(document.ADForm.mnt_dem".$id_cli.".value)+\n";

    $js_code=substr($js_code,0,strlen($js_code)-2);
    $js_code  .= ");}\n";
    $js_code  .= "setMontantDemande();\n";
    $Myform->addJS(JSP_FORM, "js1", $js_code);
  }

  // JAVASCRIP

  // Initialisation de champs dèsque le champ mnt_octr est activé
  $js_mnt_reset = "function resetMnt(id_cli) { \n";
  $js_mnt_reset .= "var mnt_dem = 'mnt_dem'+id_cli;\n";
  $js_mnt_reset .= "var mnt_assurance = 'mnt_assurance'+id_cli;\n";
  $js_mnt_reset .= "var mnt_commission = 'mnt_commission'+id_cli;\n";
  $js_mnt_reset .= "var gar_num ='gar_num'+id_cli;\n";
  $js_mnt_reset .= "var gar_mat ='gar_mat'+id_cli;\n";
  $js_mnt_reset .= "var gar_tot ='gar_tot'+id_cli;\n";
  $js_mnt_reset .= "var gar_num_encours ='gar_num_encours'+id_cli;\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_dem).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_assurance).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(mnt_commission).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(gar_num).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(gar_mat).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(gar_tot).value ='';\n";
  $js_mnt_reset .= "document.ADForm.eval(gar_num_encours).value ='';\n";
  $js_mnt_reset .= "}\n";
  $Myform->addJS(JSP_FORM,"js_mnt_reset",$js_mnt_reset);

  // Calule du montant de l'assurance, de la commission et des garanties en fonction du montant à octoyer
  $prc_assurance = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_assurance"];
  $prc_commission = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_commission"];
  $mnt_commission = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["mnt_commission"];
  $prc_gar_num = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_num"];
  $prc_gar_mat = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_mat"];
  $prc_gar_tot = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_tot"];
  $prc_gar_encours = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_encours"];
  $js_mnt_init = "function initMnt(id_cli) { \n";
  $js_mnt_init .= "var mnt_dem = 'mnt_dem'+id_cli;\n";
  $js_mnt_init .= "var mnt_assurance = 'mnt_assurance'+id_cli;\n";
  $js_mnt_init .= "var mnt_commission = 'mnt_commission'+id_cli;\n";
  $js_mnt_init .= "var gar_num ='gar_num'+id_cli;\n";
  $js_mnt_init .= "var gar_mat ='gar_mat'+id_cli;\n";
  $js_mnt_init .= "var gar_tot ='gar_tot'+id_cli;\n";
  $js_mnt_init .= "var gar_num_encours ='gar_num_encours'+id_cli;\n";
  $js_mnt_init .="\t\tdocument.ADForm.eval(mnt_assurance).value = Math.round(".$prc_assurance."*parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(mnt_assurance).value =formateMontant(document.ADForm.eval(mnt_assurance).value);\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(mnt_commission).value = Math.round((".$prc_commission."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))+ ".$mnt_commission.")*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(mnt_commission).value =formateMontant(document.ADForm.eval(mnt_commission).value);\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_num).value =Math.round(".$prc_gar_num."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_num).value =formateMontant(document.ADForm.eval(gar_num).value);\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_mat).value =Math.round(".$prc_gar_mat."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_mat).value =formateMontant(document.ADForm.eval(gar_mat).value);\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_tot).value =Math.round(".$prc_gar_tot."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_tot).value =formateMontant(document.ADForm.eval(gar_tot).value);\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_num_encours).value =Math.round(".$prc_gar_encours."* parseFloat(recupMontant(document.ADForm.eval(mnt_dem).value))*".$precision_devise.")/".$precision_devise.";\n";
  $js_mnt_init .="\t\t\tdocument.ADForm.eval(gar_num_encours).value = formateMontant(document.ADForm.eval(gar_num_encours).value);\n";
  $js_mnt_init .= "}";
  $Myform->addJS(JSP_FORM,"js_mnt_init",$js_mnt_init);

  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  $Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 5);
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // JS des boutons
  $Myform->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => $js_copie_mnt_dem));
  $Myform->addJS(JSP_BEGIN_CHECK, "jsdate", $js_date);
  $Myform->addJS(JSP_BEGIN_CHECK, "jsmnt", $js_mnt);

  $SESSION_VARS['ecran_precedent'] = 4; // suivi des écrans

  $Myform->buildHTML();
  echo $Myform->getHTML();
}

// Ecran 5 : Modification des paramètres du produit de crédit
else if ($prochain_ecran == 5) {
  $colb_tableau = '#e0e0ff';
  $Myform = new HTML_GEN2(_("Etape 3: Paramétrage du produit de crédit"));

  // Modification du paramétrage du produit de credit
  $includefields=array("libel","tx_interet","mode_calc_int","mode_perc_int","periodicite","prc_gar_num","prc_gar_mat","prc_gar_encours");
  $Myform->addTable("adsys_produit_credit",OPER_INCLUDE, $includefields);

  $def = new FILL_HTML_GEN2();
  $colb_tableau = '#e0e0ff';

  $def->addFillClause("produit", "adsys_produit_credit");
  $def->addCondition("produit", "id", $SESSION_VARS['id_prod']);
  $def->addManyFillFields("produit", OPER_INCLUDE, $includefields);
  $def->fill($Myform);

  // si on vient de l'écran de saisie, récupere les informations
  if ($SESSION_VARS['ecran_precedent'] == 4) {
    $tot_mnt_dem = 0;
    foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
      $SESSION_VARS['infos_doss'][$id_cli]["id_prod"] = $SESSION_VARS['id_prod'];
      $SESSION_VARS['infos_doss'][$id_cli]["date_dem"] = $ {'HTML_GEN_date_date_dem'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["mnt_dem"] = recupMontant($ {'hid_mnt_dem'.$id_cli});
      $tot_mnt_dem += $SESSION_VARS['infos_doss'][$id_cli]["mnt_dem"];
      $SESSION_VARS['infos_doss'][$id_cli]["duree_mois"] = $ {'duree_mois'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["obj_dem"] = $ {'HTML_GEN_LSB_obj_dem'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["detail_obj_dem"] = $ {'detail_obj_dem'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["delai_grac"] = $ {'delai_grac'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["differe_jours"] = $ {'differe_jours'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["differe_ech"] = $ {'differe_ech'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["cre_date_approb"] = $ {'HTML_GEN_date_cre_date_approb'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["cre_date_debloc"] = $ {'HTML_GEN_date_cre_date_debloc'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] = recupMontant($ {'cre_mnt_octr'.$id_cli});
      $SESSION_VARS['infos_doss'][$id_cli]["prelev_auto"] = ($ {'HTML_GEN_BOL_prelev_auto'.$id_cli} == 'on'? 't' : 'f');
      $SESSION_VARS['infos_doss'][$id_cli]["id_agent_gest"] = $ {'HTML_GEN_LSB_id_agent_gest'.$id_cli};
      $SESSION_VARS['infos_doss'][$id_cli]["cpt_liaison"] = $ {'HTML_GEN_LSB_cpt_liaison'.$id_cli};
    }

    // Dossiers fictifs si gs
    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["gs_cat"] == 1) {
      foreach($SESSION_VARS['liste_membres'] as $id_cli=>$nom_cli) {
        $SESSION_VARS['fictif'][$id_cli]['gs_cat'] = 1;
        $SESSION_VARS['fictif'][$id_cli]['id_membre'] = $id_cli;
        $SESSION_VARS['fictif'][$id_cli]['obj_dem'] = $ {'HTML_GEN_LSB_obj_dem'.$id_cli};
        $SESSION_VARS['fictif'][$id_cli]['detail_obj_dem'] = $ {'detail_obj_dem'.$id_cli};
        $SESSION_VARS['fictif'][$id_cli]['mnt_dem'] = recupMontant($ {'mnt_dem'.$id_cli});
      }
    }
    elseif($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["gs_cat"] == 2) {
      $SESSION_VARS['fictif'][$SESSION_VARS['infos_client']['id_client']]['gs_cat'] = 2;
      $SESSION_VARS['fictif'][$SESSION_VARS['infos_client']['id_client']]['id_membre'] = $SESSION_VARS['infos_client']['id_client'];
      $SESSION_VARS['fictif'][$SESSION_VARS['infos_client']['id_client']]['mnt_dem'] = $tot_mnt_dem;
      $SESSION_VARS['fictif'][$SESSION_VARS['infos_client']['id_client']]['obj_dem'] = NULL;
      $SESSION_VARS['fictif'][$SESSION_VARS['infos_client']['id_client']]['detail_obj_dem'] = NULL;
    }
  }
  elseif($SESSION_VARS['ecran_precedent'] == 6) { // On vient de l'écran de mobilisation des garanties
    // Récupération des valeurs qui avaient été déjà saisies dans écran 4
    $Myform->setFieldProperties("periodicite", FIELDP_DEFAULT, $SESSION_VARS["periodicite"]);
    $Myform->setFieldProperties("prc_gar_num", FIELDP_DEFAULT, $SESSION_VARS["prc_gar_num"] * 100);
    $Myform->setFieldProperties("prc_gar_mat", FIELDP_DEFAULT, $SESSION_VARS["prc_gar_mat"] * 100);
    $Myform->setFieldProperties("prc_gar_encours", FIELDP_DEFAULT, $SESSION_VARS["prc_gar_encours"] * 100);
  }

  // Désactivation des champs non modifiables
  $Myform->setFieldProperties("libel", FIELDP_IS_LABEL, true);
  $Myform->setFieldProperties("tx_interet", FIELDP_IS_LABEL, true);
  $Myform->setFieldProperties("mode_calc_int", FIELDP_IS_LABEL, true);
  $Myform->setFieldProperties("mode_perc_int", FIELDP_IS_LABEL, true);

  $Myform->setFieldProperties("mode_calc_int",FIELDP_HAS_CHOICE_AUCUN,false);
  $Myform->setFieldProperties("mode_perc_int",FIELDP_HAS_CHOICE_AUCUN,false);
  $Myform->setFieldProperties("periodicite",FIELDP_HAS_CHOICE_AUCUN,false);

  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"retour", _("Retour"), TYPB_SUBMIT);
  $Myform->addFormButton(1,3,"annuler", _("Annuler"), TYPB_SUBMIT);
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $Myform->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 4);

  // Aller à l'ecran de mobilisation des garanties s'il y a des garanties numéraires ou matérielles à bloquer
  $js_gar = "if (document.ADForm.prc_gar_num.value > 0 || document.ADForm.prc_gar_mat.value > 0 ) {
            document.ADForm.prochain_ecran.value = 6;
            } else {
            document.ADForm.prochain_ecran.value = 7; } document.ADForm.m_agc.value='".$_REQUEST['m_agc']."';";

  $Myform->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => $js_gar));
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $Myform->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $SESSION_VARS['ecran_precedent'] = 5; // suivi des écrans

  $Myform->buildHtml();
  echo $Myform->getHtml();
}

// Ecran 6 : Mobilisation des garanties
else if ($prochain_ecran == 6) {
  $SESSION_VARS["is_gar_mob"] = true;

  if ($SESSION_VARS['ecran_precedent'] == 5) { // On vient de l'écran 5 : modification du paramétrage du produit
    $SESSION_VARS["periodicite"] = $HTML_GEN_LSB_periodicite;
    $SESSION_VARS["prc_gar_num"] = $prc_gar_num / 100;
    $SESSION_VARS["prc_gar_mat"] = $prc_gar_mat / 100;
    $SESSION_VARS["prc_gar_encours"] = $prc_gar_encours / 100;
  }

  $devise_prod = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise'];

  $colb_tableau = '#e0e0ff';
  $myForm = new HTML_GEN2(_("Mobilisation des garanties"));
  
  $xtHTML = "";

  // Creation d'un tableau pour les garanties numéraires
  if ( $SESSION_VARS["prc_gar_num"] > 0) {
    $xtHTML .= "<h1 align=\"center\">"._("Garanties numéraires")."</h1>\n";
    $xtHTML .= "<TABLE align=\"center\">";
    $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
    $xtHTML .= "<td><b>"._("Type de garantie")."</b></td>";
    $xtHTML .= "<td><b>"._("Bénéficiaire")."</b></td>";
    $xtHTML .= "<td><b>"._("Montant")." *</b></td>";
    $xtHTML .= "<td><b>"._("Compte de prélèvement")." *</b></td>";
    $xtHTML .= "</tr>";

    // Une garanties numéraire par client
    foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
      $nom_benef = getClientName($id_cli);
      $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"type_gar_num".$id_cli."\" value=\"".$adsys["adsys_type_garantie"][1]."\" disabled=true></td>\n";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"benef".$id_cli."\" value=\"".$nom_benef."\" disabled=true></td>\n";

      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"mnt_gar_num".$id_cli."\" value=\"".$SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['valeur']."\" size=12 onchange=\"value=formateMontant(value);\"></td>\n";
      if ($SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['descr_ou_compte'] != '')
        $CPT_PRELEV_GAR = getAccountDatas($SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['descr_ou_compte']);

      $xtHTML .= "<TD><INPUT TYPE=\"text\" NAME=\"cpte_client".$id_cli."\" size=32 value=\"".$CPT_PRELEV_GAR["num_complet_cpte"]."\" disabled=true>\n";

      $xtHTML .= "<FONT size=\"2\"><A href=# onclick =\"open_compte('cpte_client$id_cli','id_compte$id_cli');return false;\">"._("Recherche")."</A></FONT></TD>\n";

      $xtHTML .="<INPUT TYPE=\"hidden\" NAME=\"id_compte$id_cli\" value=\"".$SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['descr_ou_compte']."\">\n";
    }
    $xtHTML .= "</table><br>";

   
  }  /* Fin if if( $prc_gar_num > 0) */


  /* Creation d'un tableau pour les garanties matérielles */
  if ( $SESSION_VARS["prc_gar_mat"] > 0) {
    $xtHTML .= "<h1 align=\"center\">"._("Garanties matérielles")."</h1>\n";
    $xtHTML .= "<TABLE align=\"center\">";
    $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
    $xtHTML .= "<td><b>"._("Type de garantie")."</b></td>";
    $xtHTML .= "<td><b>"._("Description")." *</b></td>";
    $xtHTML .= "<td><b>"._("Type de bien")." *</b></td>";
    $xtHTML .= "<td><b>"._("Bénéficiaire")."</b></td>";
    $xtHTML .= "<td><b>"._("Valeur")." *</b></td>";
    $xtHTML .= "<td><b>"._("Pièce justificative")."</b></td>";
    $xtHTML .= "<td><b>"._("Remarque")."</b></td>";
    $xtHTML .= "<td><b>"._("Client garant")." *</b></td>";
    $xtHTML .= "</tr>";

    foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
      $nom_benef = getClientName($id_cli);
      $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"type_gar_mat".$id_cli."\" size=12 disabled=true value=\"".$adsys["adsys_type_garantie"][2]."\"></td>\n";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"desc_gar_mat".$id_cli."\" size=20 value=\"".$SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['descr_ou_compte']."\"></td>\n";
      $xtHTML .= "<td><SELECT NAME=\"type_gar_mat".$id_cli."\">";
      $xtHTML .= "<option value=\"0\">["._("Aucun")."]</option>\n";
      $types_biens = getTypesBiens();
      foreach($types_biens as $key=>$value) {
        if ($SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['type_bien'] == $key)
          $xtHTML .= "<option value=$key selected>".$key." ".$value."</option>\n";
        else
          $xtHTML .= "<option value=$key>".$key." ".$value."</option>\n";
      }
      $xtHTML .= "</SELECT>\n";
      $xtHTML .= "</td>";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"benef".$id_cli."\" value=\"".$nom_benef."\" disabled=true></td>\n";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"mnt_gar_mat".$id_cli."\" onchange=\"value=formateMontant(value);\" size=12 value=\"".$SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['valeur']."\"></td>\n";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"piece_gar_mat".$id_cli."\" size=15 value=\"".$SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['piece_just']."\"></td>\n";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"remarq_gar_mat".$id_cli."\" size=15 value=\"".$SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['remarq']."\"></td>\n";
      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"num_client".$id_cli."\" disabled=true size=10 value=\"".$SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['num_client']."\">\n";
      $xtHTML .= "<FONT size=\"2\"><A href=# onclick=\"OpenBrw('../../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client$id_cli&num_client_dest=num_client_rel$id_cli', '"._("Recherche")."');return false;\">Recherche</A></FONT></TD>\n";
      $xtHTML .= "<INPUT TYPE=\"HIDDEN\" NAME=\"num_client_rel$id_cli\" value=\"".$SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['num_client']."\">\n";
      $xtHTML .= "</tr>";
    }
    $xtHTML .= "</table><br><br>";
  } /* Fin if( $prc_gar_mat > 0) */
  
  $myForm->addHTMLExtraCode ("infos_cli", $xtHTML);

  $myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"retour",_("Retour"),TYPB_SUBMIT);
  $myForm->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);

  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 7);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 5);

  $SESSION_VARS['ecran_precedent'] = 6; // suivi des écrans
  $myForm->buildHTML();
  echo $myForm->getHTML();
}

// Ecran 7 : Echéancier théorique
else if ($prochain_ecran == 7) {

  // Récupération des garanties mobilisées, si on vient de l'écran 6
  if ($SESSION_VARS['ecran_precedent'] == 6) {
    $msg = "";
    // Récupération des garanties numéraires mobilisées par chaque client
    foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
      // Garantie numéraire : le montant et le compte de prélèvement doivent être saisis ou non à la fois
      if (($ {'mnt_gar_num'.$id_cli} != '') and ($ {'id_compte'.$id_cli} !='')) {
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['benef'] = $id_cli;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['type'] = 1 ;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['descr_ou_compte'] = $ {'id_compte'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['id_gar'] = NULL ;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['type_bien'] = NULL;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['num_client'] = NULL;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['piece_just'] = NULL;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['remarq'] = NULL;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['etat'] = 3; // Mobilisée
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['valeur'] = recupMontant($ {'mnt_gar_num'.$id_cli});
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['devise_vente'] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise'];

        // Vérifier que les garanties mobilisée sont suffisantes
        $gar_num_attendue = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_num"];

		global $global_monnaie_courante_prec;
        if ( round($gar_num_attendue,$global_monnaie_courante_prec) > round ($SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']['valeur'],$global_monnaie_courante_prec)) {
          $msg = _("La garantie numéraire mobilisée est insuffisante");
		}
      }elseif($ {'mnt_gar_num'.$id_cli} == ''){
      	$msg = _("Le montant de la garantie numéraire doit être renseigné pour le client: ".$nom);
      }else{
      	$msg = _("Le compte de prélèvelement de la garantie numéraire doit être renseigné pour le client: ".$nom);
      }

      // Récupération des garanties matérielles mobilisées
      // Les champs obligatoires doivent être renseignés ou non à la fois
      if ($ {'mnt_gar_mat'.$id_cli} != '' and $ {'desc_gar_mat'.$id_cli} != '' and $ {'type_gar_mat'.$id_cli} != 0 and $ {'num_client_rel'.$id_cli} != '') {
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['benef'] = $id_cli;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['type'] = 2 ;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['id_gar'] = NULL ;
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['descr_ou_compte'] = $ {'desc_gar_mat'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['num_client'] = $ {'num_client_rel'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['type_bien'] = $ {'type_gar_mat'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['piece_just'] = $ {'piece_gar_mat'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['remarq'] = $ {'remarq_gar_mat'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['etat'] = 3; // Mobilisée
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['valeur'] = recupMontant($ {'mnt_gar_mat'.$id_cli});
        $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['devise_vente'] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise'];
        // Vérifier que les garanties mobilisée sont suffisantes
        $gar_mat_attendue = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_mat"];

        if ($gar_mat_attendue > $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']['valeur'])
          $msg = _("La garantie matérielle mobilisée est insuffisante");
      }
      elseif($ {'mnt_gar_mat'.$id_cli} =='' and ($ {'desc_gar_mat'.$id_cli} != '' or $ {'type_gar_mat'.$id_cli} != 0 or $ {'num_client_rel'.$id_cli} != ''))
      $msg = sprintf(_("Le champ montant du client %s des garanties matérielles n'est pas renseigné"),$id_cli);
      elseif($ {'desc_gar_mat'.$id_cli} == '' and ($ {'mnt_gar_mat'.$id_cli} != '' or $ {'type_gar_mat'.$id_cli} != 0 or $ {'num_client_rel'.$id_cli} != ''))
      $msg = sprintf(_("Le champ description du client %s des garanties matérielles n'est pas renseigné"),$id_cli);
      elseif($ {'type_gar_mat'.$id_cli} == 0 and ($ {'mnt_gar_mat'.$id_cli} != '' or $ {'desc_gar_mat'.$id_cli} != '' or $ {'num_client_rel'.$id_cli} != ''))
      $msg = sprintf(_("Le champ type de bien du client %s des garanties matérielles n'est pas renseigné"),$id_cli);
      elseif($ {'num_client_rel'.$id_cli} == '' and ($ {'mnt_gar_mat'.$id_cli} != '' or $ {'desc_gar_mat'.$id_cli} != '' or $ {'type_gar_mat'.$id_cli} != 0))
      $msg = sprintf(_("Le champ client garant du client %s des garanties matérielles n'est pas renseigné"),$id_cli);
    } // fin parcours clients

    // Vérifier que tous les champs obligatoires ont été saisis
    if ($msg != "") {
      $colb_tableau = '#e0e0ff';
      $MyPage = new HTML_erreur(_("Erreur dans la mobilisation des garanties "));
      $MyPage->setMessage($msg);
      $MyPage->addButton(BUTTON_OK, "5");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
      exit();
    }
  } // Fin si on vient de l'écran de mobilisation

  /**********************  Génération de l'échéancier. L'utilisateur peut le modifier ***********/
  $myForm = new HTML_GEN2(_("Etape 4: Génération de l'échéancier"));

  // Si on vient de l'écran 5, modification paramétrage
  if ($SESSION_VARS['ecran_precedent'] == 5) {
    $SESSION_VARS["periodicite"] = $HTML_GEN_LSB_periodicite;
    $SESSION_VARS["prc_gar_num"] = $prc_gar_num / 100;
    $SESSION_VARS["prc_gar_mat"] = $prc_gar_mat / 100;
    $SESSION_VARS["prc_gar_encours"] = $prc_gar_encours / 100;
  }
  // Génération des échéanciers
  $HTML_code_echeancier = '';
  foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
    $ECH_THEO = calcul_echeancier_theorique($SESSION_VARS["id_prod"], $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"], $SESSION_VARS['infos_doss'][$id_cli]["duree_mois"], $SESSION_VARS['infos_doss'][$id_cli]["differe_jours"], $SESSION_VARS['infos_doss'][$id_cli]["differe_ech"], $SESSION_VARS["periodicite"]);

    // Construction du tableau paramètre
    $param["index"] = 0;
    $param["periodicite"] = $SESSION_VARS["periodicite"];
    $param["mode_calc_int"] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["mode_calc_int"];
    $param["nbre_jour_mois"] = 30; // FIXME Backward compatibility. Cfr fonction completeEcheancier
    $param["differe_jours"] = $SESSION_VARS['infos_doss'][$id_cli]["differe_jours"];
    $param["date"] = $SESSION_VARS['infos_doss'][$id_cli]["cre_date_debloc"];
    $param["id_doss"] = -1;
    $param["duree"] = $SESSION_VARS['infos_doss'][$id_cli]["duree_mois"];

    $ECH_REEL = completeEcheancierLibre($ECH_THEO, $param);

    // l'échéance en cours, à la date de reprise du bilan
    $ech_cours = getEcheanceCourante($ECH_REEL,$SESSION_VARS['date_reprise_credit']);

    /* Modification :
     - du format de la date de l'échéance pour la fonction HTML_echeancier_remboursement
     - Calcul au prorata des intérêts des échéances pour le dégressif variable
    */
    foreach($ECH_REEL as $cle=>$val) {
      $ECH_REEL[$cle]["date_ech"] = php2pg($val["date_ech"]);

      /* Dégressif variable, les intérêts à venir seront comptabilisés dynamiquement au jour le jour */
      if ($param["mode_calc_int"] == 3 ) {
        if ( ($ech_cours != NULL) and ($cle > $ech_cours))
          $ECH_REEL[$cle]["solde_int"] = 0;
        elseif($cle == $ech_cours) {
          $date_ech_courante = $val["date_ech"];
          if ($ech_cours == 1) // première echéance
            $date_prec_ech_courante = $SESSION_VARS["cre_date_debloc"];
          else { // Cas Général
            $prec_ech_courante = $ech_cours - 1;
            $date_prec_ech_courante = pg2phpDate($ECH_REEL[$prec_ech_courante]["date_ech"]);
          }

          // calcul ratio nbre de jours écoulés depuis cette échéance sur nombre de jours entre cette échéance et l'échéance active
          $nominateur = nbreDiffJours($date_prec_ech_courante, pg2phpDate($SESSION_VARS['date_reprise_credit']));
          $denominateur = nbreDiffJours($date_ech_courante, $date_prec_ech_courante);

          $ratio = (($nominateur*1.0) / ($denominateur*1.0));

          // Calcul de l'intérêt réel à prendre en compte
          $ECH_REEL[$cle]["solde_int"] = arrondiMonnaie($ECH_REEL[$ech_cours]["solde_int"] * $ratio, 1);
        }
      }
    }

    $SESSION_VARS['infos_doss'][$id_cli]["ECH"] = $ECH_REEL;

    $PROD = getProdInfo(" where id =".$SESSION_VARS["id_prod"]);
    $libel_prod = $PROD[0]["libel"];

    /* Construction du code HTML de l'échéancier: préparation des paramètres à donner à HTML_echeancier_remboursement */
    $parametre["lib_date"] = _("Date de déblocage du crédit ");
    $parametre["titre"] = _("Echéancier de remboursement à modifier pour ").getClientName($id_cli);
    $parametre["mnt_reech"] = '0';
    $parametre["date"] = $SESSION_VARS['infos_doss'][$id_cli]["cre_date_debloc"];
    $parametre["id_client"] = $id_cli;
    $parametre["id_doss"] = $id_cli;

    $champs_modifiables["int"] = true;
    //$champs_modifiables["pen"] = true;
    $champs_modifiables["cap"] = true;
    $champs_modifiables["gar"] = true;
    $champs_modifiables["date_ech"] = true;

    /* Les informations sur le dossier de crédit sélectionné */
    $DOSSIER = array();
    $DOSSIER['differe_jours'] = $SESSION_VARS['infos_doss'][$id_cli]["differe_jours"];
    $DOSSIER['differe_ech'] = $SESSION_VARS['infos_doss'][$id_cli]["differe_ech"];
    $DOSSIER['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"];
    $DOSSIER['duree_mois'] = $SESSION_VARS['infos_doss'][$id_cli]["duree_mois"];
    $DOSSIER['gar_num'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_num"];
    $DOSSIER['gar_mat'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_mat"];
    $DOSSIER['gar_tot'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_tot"];
    $DOSSIER['gar_encours'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_encours"];

    /* Génération du code HTML de l'échéancier : HTML_echeancier_remboursement présente les soldes.
    Ce que ne pose pas de problème les montant cap, int , pen et gar sont égaux aux soldes cap, int pen et gar à cfe niveau
    */
    global $adsys;

    $HTML_code_echeancier .= HTML_echeancier_remboursement($parametre, $ECH_REEL, $champs_modifiables, $DOSSIER, $PROD );

    // Vérifications javascript :
    //  - Les dates se suivent dans l'ordre chronologique
    //  - La somme des montants en capital est égale au montant octroyé
    $checkDates = "";
    $checkCap = "";
    reset($ECH_REEL);
    while (list($key, $ech) = each($ECH_REEL)) {
      if ($key > 1) // On fait ces vérifications à partir de la seconde échéance
        $checkDates .= "\nif (!isBefore(document.ADForm.date".($key-1).".value, document.ADForm.date$key.value)) {msg += '- ".sprintf(_("La date de l\\'échéance %s doit être postérieure à la date de l\\'échéance"), $key)." ".($key-1)."\\n';ADFormValid=false;}\n";
      $computeSumCap .= "if (document.ADForm.mnt_cap$key.value == '') total_cap = total_cap + 0; else total_cap = total_cap + parseInt(recupMontant(document.ADForm.mnt_cap$key.value));\n";  // Si Montant rensiengé est vide
    }

    $checkSumCap = "if (total_cap != ".( $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"]).") {msg += '- ".sprintf(_("Le montant du capital à rembourser doit être égal à %s et est pour le moment égal à "),afficheMontant($SESSION_VARS["cre_mnt_octr"], true))."'+recupMontant(total_cap)+'\\n';ADFormValid = false;}";

    $myForm->addJS(JSP_BEGIN_CHECK, "init".$id_cli, "total_cap = 0;\n");
    $myForm->addJS(JSP_BEGIN_CHECK, "checkDates".$id_cli, $checkDates);
    $myForm->addJS(JSP_BEGIN_CHECK, "computeSumCap".$id_cli, $computeSumCap);
    $myForm->addJS(JSP_BEGIN_CHECK, "checkSumCap".$id_cli, $checkSumCap);

    // Sauvegarde du nombre d'échéances
    $SESSION_VARS['infos_doss'][$id_cli]['nbre_ech'] = sizeof($ECH_REEL);
    $myForm->addHiddenType("nbre_ech".$id_cli, $SESSION_VARS['infos_doss'][$id_cli]['nbre_ech']);

  } // fin parcours clients

  $myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"retour",_("Retour"),TYPB_SUBMIT);
  $myForm->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 5);

  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 8);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $SESSION_VARS['ecran_precedent'] = 7; // suivi des écrans
  $myForm->addHTMLExtraCode("echeancier", $HTML_code_echeancier);
  $myForm->buildHTML();
  echo $myForm->getHtml();
}

// Ecran 8 : Confirmation de l'échéancier
elseif ($prochain_ecran == 8) {
  $SESSION_VARS["is_gar_mob"] = false;

  $myForm = new HTML_GEN2(_("Etape 5: Confirmation de l'échéancier"));
  $HTML_code_echeancier = '';
  // Parcours des dossiers des clients
  foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
    $ECH = array();
    for ($i=1; $i<= $SESSION_VARS['infos_doss'][$id_cli]['nbre_ech']; $i++) {
      // Montant du remboursement
      if ($ {'solde_cap'.$id_cli.$i} == '') {
        $SESSION_VARS['infos_doss'][$id_cli]["ECH"][$i]['solde_cap'] = 0;
        $SESSION_VARS['infos_doss'][$id_cli]['ECH'][$i]["mnt_cap"] = 0;
      }
      else {
        $SESSION_VARS['infos_doss'][$id_cli]["ECH"][$i]['solde_cap'] = recupMontant($ {'solde_cap'.$id_cli.$i});
        $SESSION_VARS['infos_doss'][$id_cli]['ECH'][$i]["mnt_cap"] = recupMontant($ {'solde_cap'.$id_cli.$i});
      }

      // Montant des intérêts
      if ($ {'solde_int'.$id_cli.$i} == '')
        $SESSION_VARS['infos_doss'][$id_cli]["ECH"][$i]['solde_int'] = 0;
      else {
        $SESSION_VARS['infos_doss'][$id_cli]["ECH"][$i]['solde_int'] = recupMontant($ {'solde_int'.$id_cli.$i});
        $SESSION_VARS['infos_doss'][$id_cli]['ECH'][$i]["mnt_int"] = recupMontant($ {'solde_int'.$id_cli.$i});
      }

      // Montant des garanties encours
      if ($ {'solde_gar'.$id_cli.$i} == '')
        $SESSION_VARS['infos_doss'][$id_cli]["ECH"][$i]['solde_gar'] = 0;
      else
        $SESSION_VARS['infos_doss'][$id_cli]["ECH"][$i]['solde_gar'] = recupMontant($ {'solde_gar'.$id_cli.$i});

      // Montant des pénalités
      if ($ {'solde_pen'.$id_cli.$i} == '')
        $SESSION_VARS['infos_doss'][$id_cli]["ECH"][$i]['solde_pen'] = 0;
      else
        $SESSION_VARS['infos_doss'][$id_cli]["ECH"][$i]['solde_pen'] = recupMontant($ {'solde_pen'.$id_cli.$i});
    }

    // Enregistrement du tableau
    $gar_debut = $ {'gar_num'.$id_cli};
    $SESSION_VARS['infos_doss'][$id_cli]["gar_num"] = recupMontant($gar_debut);
    $SESSION_VARS['infos_doss'][$id_cli]["gar_num_encours"] = recupMontant($ {'gar_num_encours'.$id_cli});
    $SESSION_VARS['infos_doss'][$id_cli]["gar_mat"] = $ {'gar_mat'.$id_cli};

    $PROD = getProdInfo(" where id =".$SESSION_VARS["id_prod"]);
    $libel_prod = $PROD[0]["libel"];

    global $adsys;
    $colb_tableau = '#e0e0ff';
    $colb_tableau_altern = '#ffd5d5';

    // Construction du code HTML de l'échéancier: préparation des paramètres à donner à HTML_echeancier_remboursement */
    $parametre["lib_date"] = _("Date de déblocage du crédit ");
    $parametre["titre"] = _("Echéancier de remboursement à modifier pour ").getClientName($id_cli);
    $parametre["mnt_reech"] = '0';
    $parametre["date"] = $SESSION_VARS['infos_doss'][$id_cli]["cre_date_debloc"];

    // Pas de champs modifiable
    $champs_modifiables = array();

    // Les informations sur le dossier de crédit sélectionné
    $DOSSIER = array();
    $DOSSIER['differe_jours'] = $SESSION_VARS['infos_doss'][$id_cli]["differe_jours"];
    $DOSSIER['differe_ech'] = $SESSION_VARS['infos_doss'][$id_cli]["differe_ech"];
    $DOSSIER['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"];
    $DOSSIER['duree_mois'] = $SESSION_VARS['infos_doss'][$id_cli]["duree_mois"];
    $DOSSIER['gar_num'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_num"];
    $DOSSIER['gar_mat'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_mat"];
    $DOSSIER['gar_tot'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_tot"];
    $DOSSIER['gar_encours'] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_encours"];

    // Génération du code HTML de l'échéancier
    $HTML_code_echeancier .= HTML_echeancier_remboursement($parametre, $SESSION_VARS['infos_doss'][$id_cli]["ECH"], $champs_modifiables, $DOSSIER, $PROD);
  } // fin parcours des clients

  // Boutons du formulaire
  $myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"retour",_("Retour"),TYPB_SUBMIT);
  $myForm->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 7);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 9);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $myForm->addHTMLExtraCode("echeancier", $HTML_code_echeancier);

  $SESSION_VARS['ecran_precedent'] = 8; // suivi des écrans

  $myForm->buildHTML();
  echo $myForm->getHtml();
}

// Ecran 9 : Insertion dans la base de données
else if ($prochain_ecran == 9) {
  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();
  if ($SESSION_VARS['produits_credit'][$SESSION_VARS["id_prod"]]['gs_cat'] == 2) { //GS avec dossiers multiples
    $next_id_fictif = getNextDossierFictifID();
    $SESSION_VARS['fictif'][$SESSION_VARS['infos_client']['id_client']]['id'] = $next_id_fictif;

  } else
    $next_id_fictif = NULL;

  // Création des dossiers dans la db
  $total_mn_dem = 0;
  foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
    if (is_array($SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM']))
      $GARANTIE[] = $SESSION_VARS['infos_doss'][$id_cli]['GAR_NUM'];
    if (is_array($SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT']))
      $GARANTIE[] = $SESSION_VARS['infos_doss'][$id_cli]['GAR_MAT'];

    $DOSS[$id_cli]["id_dcr_grp_sol"] = $next_id_fictif;
    $DOSS[$id_cli]["id_client"] = $id_cli;
    $DOSS[$id_cli]["id_prod"] = $SESSION_VARS['infos_doss'][$id_cli]["id_prod"];
    $DOSS[$id_cli]["date_dem"] = $SESSION_VARS['infos_doss'][$id_cli]["date_dem"];
    $DOSS[$id_cli]["mnt_dem"] = $SESSION_VARS['infos_doss'][$id_cli]["mnt_dem"];
    $DOSS[$id_cli]["obj_dem"] = $SESSION_VARS['infos_doss'][$id_cli]["obj_dem"];
    $DOSS[$id_cli]["detail_obj_dem"] = $SESSION_VARS['infos_doss'][$id_cli]["detail_obj_dem"];
    $DOSS[$id_cli]["etat"] = 10;
    $DOSS[$id_cli]["doss_repris"] = 't';
    $DOSS[$id_cli]["date_etat"] = pg2phpDate($SESSION_VARS['date_reprise_credit']);
    if (($SESSION_VARS['infos_doss'][$id_cli]["id_agent_gest"] > 0))
      $DOSS[$id_cli]["id_agent_gest"] = $SESSION_VARS['infos_doss'][$id_cli]["id_agent_gest"];
    $DOSS[$id_cli]["delai_grac"] = $SESSION_VARS['infos_doss'][$id_cli]["delai_grac"];
    $DOSS[$id_cli]["differe_jours"] = $SESSION_VARS['infos_doss'][$id_cli]["differe_jours"];
    $DOSS[$id_cli]["differe_ech"] = $SESSION_VARS['infos_doss'][$id_cli]["differe_ech"];
    $DOSS[$id_cli]["prelev_auto"] = $SESSION_VARS['infos_doss'][$id_cli]["prelev_auto"];
    $DOSS[$id_cli]["duree_mois"] = $SESSION_VARS['infos_doss'][$id_cli]["duree_mois"];
    $DOSS[$id_cli]["terme"] = getTerme($SESSION_VARS['infos_doss'][$id_cli]["duree_mois"]);
    $DOSS[$id_cli]["gar_num"] =  $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_num"];
    $DOSS[$id_cli]["gar_mat"] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_mat"];
    $DOSS[$id_cli]["gar_tot"] = $DOSS[$id_cli]["gar_num"] + $DOSS[$id_cli]["gar_mat"];
    $DOSS[$id_cli]["gar_num_encours"] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"] * $SESSION_VARS["prc_gar_encours"];
    $DOSS[$id_cli]["num_cre"] =  $SESSION_VARS['infos_doss'][$id_cli]["num_cre"];
    $DOSS[$id_cli]["cre_date_approb"] = $SESSION_VARS['infos_doss'][$id_cli]["cre_date_approb"];
    $DOSS[$id_cli]["cre_date_debloc"] = $SESSION_VARS['infos_doss'][$id_cli]["cre_date_debloc"];
    $DOSS[$id_cli]["cre_nbre_reech"] = 0;
    $DOSS[$id_cli]["cre_mnt_octr"] = $SESSION_VARS['infos_doss'][$id_cli]["cre_mnt_octr"];
    $DOSS[$id_cli]["suspension_pen"] = 'f';
    $DOSS[$id_cli]["cre_retard_etat_max"] = NULL;
    $DOSS[$id_cli]["cre_retard_etat_max_jour"] = NULL;
    $DOSS[$id_cli]["cpt_liaison"] = $SESSION_VARS['infos_doss'][$id_cli]["cpt_liaison"];
    $DOSS[$id_cli]["gs_cat"] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'];
    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_assurance'] > 0)
      $DOSS[$id_cli]["assurances_cre"] = 'f' ;
     else
      $DOSS[$id_cli]["assurances_cre"] = 't' ;

    $total_mn_dem += $SESSION_VARS['infos_doss'][$id_cli]["mnt_dem"];
  } // fin parcours des clients

  // Insertion dossier et les garanties
  $FRAIS = NULL;
  $myErr = insereDossier($DOSS,$FRAIS,$GARANTIE, 1, $SESSION_VARS['fictif']);
  if ($myErr->errCode != NO_ERR) {
    $MyPage = new HTML_erreur(_("Erreur lors de l'insertion du dossier "));
    $MyPage->setMessage(_("Erreur : ".$error[$myErr->errCode]));
    $MyPage->addButton(BUTTON_OK, "7");
    $MyPage->buildHTML();
    echo $MyPage->HTML_code;
    exit();
  }

  // les id dossiers créés
  $ID_DOSS = $myErr->param;

  // Insertions dans la table ad_etr
  foreach ($SESSION_VARS['clients_dcr'] as $id_cli=>$nom) {
    while (list($key, $ech) = each($SESSION_VARS['infos_doss'][$id_cli]["ECH"])) {
      $etr['id_doss'] = $ID_DOSS[$id_cli];
      $etr['id_ech'] = $ech['id_ech'];
      $etr['date_ech'] = $ech['date_ech'];
      $etr['mnt_cap'] = $ech['mnt_cap'];
      $etr['mnt_int'] = $ech['mnt_int'];
      $etr['mnt_gar'] = $ech['mnt_gar'];
      $etr['mnt_reech'] = 0;
      $etr['solde_cap'] = $ech['solde_cap'];
      $etr['solde_int'] = $ech['solde_int'];
      $etr['solde_gar'] = $ech['solde_gar'];
      $etr['solde_pen'] = $ech['solde_pen'];
      $etr['id_ag']     = $global_id_agence;
      $sql = buildInsertQuery("ad_etr", $etr);

      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur("recup_credit.php", "Ecran 9", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
      }
    }

    // Mise à jour du nombre de crédits chez le client
    $sql = "UPDATE ad_cli SET nbre_credits = ".$SESSION_VARS['infos_doss'][$id_cli]['num_cre']." WHERE id_client = $id_cli AND id_ag=$global_id_agence ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 9", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }
  }

  $dbHandler->closeConnection(true);

  /* Génération du message de confirmation */
  $colb_tableau = '#e0e0ff';
  $msg = new HTML_message(_("Confirmation creation dossier de credit"));
  $msg->setMessage(_("Les dossiers de crédit ont été enregistrés avec succès.<BR>Pour rendre la reprise de ces dossiers effective, activez ces dossiers à partir du menu principal"));
  $msg->addButton(BUTTON_OK,1);
  $msg->buildHTML();
  echo $msg->HTML_code;
}

// Ecran 10 : Suppression du dossier de crédit en cours de reprise
elseif ($prochain_ecran == 10) {
  unset($SESSION_VARS['id_doss_fic']);

  // Récupération des dossiers à supprimer
  if ($SESSION_VARS['dossiers'][$index]['gs_cat'] != 2 ) { // dossier individuel
    // Les informations sur le dossier
    $id_doss = $SESSION_VARS['dossiers'][$index]['id_doss'];
    $id_prod = $SESSION_VARS['dossiers'][$index]['id_prod'];;
    $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
    // Infos dossiers fictifs dans le cas de GS avec dossier unique
    if ($SESSION_VARS['dossiers'][$index]['gs_cat'] == 1) {
      $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
      $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] = getCreditFictif($whereCond);
    }

    $infos = "N° $id_doss";
  }
  elseif($SESSION_VARS['dossiers'][$index]['gs_cat'] == 2 ) { // GS avec dossiers multiples
    // id du dossier fictif : id du dossier du groupe
    $SESSION_VARS['id_doss_fic'] = $SESSION_VARS['dossiers'][$index]['id'];
    $infos = '';

    // dossiers réels des membre du GS
    $dossiers_membre = getDossiersMultiplesGS($SESSION_VARS['infos_client']['id_client']);
    foreach($dossiers_membre as $id_doss=>$val) {
      if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$index]['id'] and $val['etat']==10) {
        $infos .= "N° $id_doss";
        $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
        $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
      }
    }
  }

  $colb_tableau = '#e0e0ff';
  $msg = new HTML_message(_("Suppression de dossier de crédit"));
  $msg->setMessage(_("Vous vous apprétez à supprimer les dossiers de crédit")." <B>".$infos." </B><BR>"._("Etes-vous certain de vouloir supprimer ces dossiers?"));
  $msg->addButton(BUTTON_OUI,11);
  $msg->addButton(BUTTON_NON,2);

  $SESSION_VARS['ecran_precedent'] = 10; // suivi des écrans

  $msg->buildHTML();
  echo $msg->HTML_code;
}

// Ecran 11 : Confirmation de la suppression du dossier de crédit en cours de reprise
else if ($prochain_ecran == 11) {
  $db = $dbHandler->openConnection();

  // Parcours des dossiers à supprimer
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // Mise à jour de rang de crédit à 0 dans la table des clients
    $sql = "UPDATE ad_cli SET nbre_credits = nbre_credits-1 WHERE id_client = ".$val_doss['id_client']." AND id_ag=$global_id_agence ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 11", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }

    // Suppression des échéances
    $sql = "DELETE FROM ad_etr WHERE id_doss = ".$id_doss;
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 11", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }

    // Récupération des garanties mobilisées
    $liste_gar = getListeGaranties($id_doss);

    // Suppression des garanties mobilisées
    $sql = "DELETE FROM ad_gar WHERE id_doss = ".$id_doss;
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 15", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }

    // Il n'est pas nécessaire de débloquer les garanties car elle n'ont pas été prélevées sur le compte du client (reprise !)

    // Suppression d'éventuels dossiers fictifs de membres
    $sql = "DELETE FROM ad_dcr_grp_sol WHERE id_dcr_grp_sol = ".$id_doss;
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 15", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }

    // Suppression de l'entrée dans ad_dcr
    $sql = "DELETE FROM ad_dcr WHERE id_doss = ".$id_doss;
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 15", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }

    // Suppression d'un éventuel dossier fictifs de groupe
    if ($SESSION_VARS['id_doss_fic'] != '') {
      $sql = "DELETE FROM ad_dcr_grp_sol WHERE id = ".$SESSION_VARS['id_doss_fic'];
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur("recup_credit.php", "Ecran 15", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
      }
    }

  } // Fin parcours dossiers

  $dbHandler->closeConnection(true);

  // Message de confirmation
  $colb_tableau = '#e0e0ff';
  $msg = new HTML_message(_("Confirmation suppression du dossier de credit"));
  $msg->setMessage(_("Le dossier de crédit a été supprimé avec succès."));
  $msg->addButton(BUTTON_OK,1);
  $msg->buildHTML();
  echo $msg->HTML_code;
  $SESSION_VARS['ecran_precedent'] = 11; // suivi des écrans
}

// Ecran 12 : Activation du dossier de crédit en cours de reprise
else if ($prochain_ecran == 12) {
  $db = $dbHandler->openConnection();
  $myForm = new HTML_GEN2(_("Etape 1: Renseignement des échéances remboursées"));
  // Récupération des dossiers à activer
  if ($SESSION_VARS['dossiers'][$index]['gs_cat'] != 2 ) { // dossier individuel
    // Les informations sur le dossier
    $id_doss = $SESSION_VARS['dossiers'][$index]['id_doss'];
    $id_prod = $SESSION_VARS['dossiers'][$index]['id_prod'];
    $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
    // Infos dossiers fictifs dans le cas de GS avec dossier unique
    if ($SESSION_VARS['dossiers'][$index]['gs_cat'] == 1) {
      $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
      $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] = getCreditFictif($whereCond);
    }
  }
  elseif($SESSION_VARS['dossiers'][$index]['gs_cat'] == 2 ) { // GS avec dossiers multiples
    // id du dossier fictif : id du dossier du groupe
    $id_doss_fic = $SESSION_VARS['dossiers'][$index]['id'];

    // dossiers réels des membre du GS
    $dossiers_membre = getDossiersMultiplesGS($SESSION_VARS['infos_client']['id_client']);
    foreach($dossiers_membre as $id_doss=>$val) {
      if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$index]['id'] and $val['etat']==10) {
        $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
        $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
      }
    }
  }

  echo "<p align=\"center\">"._("La date de reprise utilisée est la date du jour.  ADbanking va utiliser pour ce crédit la date de reprise : ")."<B>".pg2phpDate($SESSION_VARS['date_reprise_credit'])."</B>. "._("Veuillez cocher ci-dessous les échéances remboursées pour ce crédit")."</p>";

  // Parcours des dossiers
  $xtHTML = "";
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $myForm->addHiddenType("id_doss".$id_doss, $id_doss);

    $id_prod = $val_doss['id_prod'];
    $cre_mnt_octr = $val_doss['cre_mnt_octr'];
    $duree_mois = $val_doss['duree_mois'];
    $differe_jours = $val_doss['differe_jours'];
    $differe_ech = $val_doss['differe_ech'];
    $gar_num = $val_doss['gar_num'];
    $gar_num_encours = $val_doss['gar_num_encours'];
    $cre_date_debloc = pg2phpDate($val_doss['cre_date_debloc']);

    // Récupération des données concernant l'échéancier théorique de remboursement
    $sql = "SELECT * FROM ad_etr WHERE id_ag = $global_id_agence and id_doss = $id_doss ORDER BY id_ech";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 12", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }

    // Construction du tableau des échéances à partir de la base de données
    $ECH = array();
    while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC))
      $ECH[$tmprow["id_ech"]] = $tmprow;

    $SESSION_VARS['Ech_values_'.$id_doss] = $ECH;

    // Recupere nombre total des echeances pour dossier (control JS pour les totaux dans le tableau)
    $sql = "SELECT count(id_ech) as  total_ech FROM ad_etr WHERE id_ag = $global_id_agence and id_doss = $id_doss";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 12", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }
    $countrow = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $nbrTotalEcheance = $countrow['total_ech'];
    $SESSION_VARS['tot_ech'][$id_doss] = $nbrTotalEcheance;

    $PROD = getProdInfo(" where id =".$id_prod);
    $libel_prod = $PROD[0]["libel"];

    global $adsys;

    $colb_tableau = '#e0e0ff';
    $colb_tableau_retard = '#ffd5d5';
    $colb_tableau_rembourse = '#ccffcc';

    //Tableau principal
    $xtHTML .= "<TABLE width=\"70%\" align=\"center\" valign=\"middle\" cellspacing=0 cellpadding=0  border=$tableau_border>\n"; //border=1
    $xtHTML .= "<TR bgcolor=$colb_tableau>\n";
    $xtHTML .= "<TD>";

    //Tableau des détail produit
    $xtHTML .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=\"0\" border=\"0\>\n";
    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2 width=\"10%\">"._("Produit").":</TD>\n";
    $xtHTML .= "<TD width=\"40%\">$libel_prod</TD>\n"; //Produit
    $xtHTML .= "<TD colspan=2 width=\"20%\">"._("Montant octroyé")." :</TD>\n";
    $xtHTML .= "<TD>".afficheMontant ($cre_mnt_octr,true)."</TD>\n"; //Mnt octroyé
    $xtHTML .= "</TR>\n";

    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2 >"._("Durée").":</TD>\n";
    $xtHTML .= "<TD>".$duree_mois."</TD>\n"; //Durée
    $xtHTML .= "<TD colspan=2 >"._("Montant rééchel").".:</TD>\n";
    $xtHTML .= "<TD>".afficheMontant(0, true)."</TD>\n"; //mnt rééch
    $xtHTML .= "</TR>\n";

    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2>"._("Différé")." :</TD>\n";

    $xtHTML .= "<TD>".str_affichage_diff($differe_jours, $differe_ech)."</TD>\n";
    $xtHTML .= "<TD colspan=2>"._("Garanties").":</TD>\n";
    $xtHTML .= "<TD>".afficheMontant($gar_num + $gar_num_encours, true)."</TD>\n"; //Garantie
    $xtHTML .= "</TR>\n";
    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2>"._("Date de déboursement").":</TD><TD>".$cre_date_debloc."</TD>\n";
    $xtHTML .= "<TD colspan=2>"._("Nom client").":</TD><TD>".getClientName($val_doss['id_client'])."</TD>\n";
    $xtHTML .= "</TR>\n";

    $xtHTML .= "</TABLE>\n";

    // Tableau des echéances

    // En-tête
    $xtHTML .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding  border=$tableau_border>\n";
    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD align=\"center\">"._("Remb")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("N°")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Date")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde capital")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde intérêts")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde garantie")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Total de l'échéance")."</TD>\n";
    $xtHTML .= "</TR>\n";

    // Contenu
    $total_cap = 0;
    $total_int = 0;
    $total_gar = 0;

    // Fonction JS de changement des couleurs
    $js = "function toggleColor(el)
          {
          if (el.checked == true)
          el.parentNode.parentNode.style.backgroundColor='$colb_tableau_rembourse;';
          else
          el.parentNode.parentNode.style.backgroundColor='$colb_tableau_retard;';
        }
          ";

    while (list($key,$echanc) = each($ECH)) {
      $total_cap = $total_cap + ceil($echanc["solde_cap"]);
      $total_int = $total_int + ceil($echanc["solde_int"]);
      $total_gar = $total_gar + ceil($echanc["solde_gar"]);

      // Affichage
      // Si la date d'échéance est antérieure à la date de reprise de credit, on affiche en rouge, si postérieure, on affiche en bleu
      // Egalement si on vient de l'écran précédent et que la checkbox était coché, on affiche le tout en vert
      if (is_array($SESSION_VARS['infos_doss'][$id_doss]["rembourse"]) && $SESSION_VARS['infos_doss'][$id_doss]["rembourse"][$key] == true) {
        $xtHTML .= "<TR bgcolor=\"$colb_tableau_rembourse\">\n";
        $checked = "checked"; // On indique qu'il faudra cocher la checkbox
      } else if ($echanc["remb"] == 't'){
				$xtHTML .= "<TR bgcolor=\"$colb_tableau_rembourse\">\n";
        $checked = "checked disabled";
      } else if (isBefore(pg2phpDate($echanc["date_ech"]), pg2phpDate($SESSION_VARS['date_reprise_credit']))) {
        $xtHTML .= "<TR bgcolor=\"$colb_tableau_retard\">\n";
        $checked = "";
      } else {
        $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
        $checked = "";
      }
      $xtHTML .= "<TD align=\"center\"><INPUT type=\"checkbox\" name=\"rembourse".$id_doss."_".$key."\" $checked onclick=\"toggleColor(this);\"></INPUT></TD>\n";
      $xtHTML .= "<TD align=\"center\">".$echanc["id_ech"]."</TD>\n";
      $xtHTML .= "<TD align=\"left\">".pg2phpDate($echanc["date_ech"])."</TD>\n";
      /*$xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_cap"], false)."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_int"], false)."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_gar"], false)."</TD>\n";*/

      //$xtHTML .= "<TD align=\"right\">".afficheMontant ($som,false)."</TD>\n";
      //calculTotaux(document.ADForm.solde_cap$key,document.ADForm.solde_int$key,document.ADForm.solde_gar$key,document.ADForm.som$key); document.ADForm.solde_cap$key.value = recupMontant(document.ADForm.solde_cap$key.value);
      $id_doss_pos = $id_doss."_".$key;
      $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"solde_cap_$id_doss_pos\" size=7 value=\"".afficheMontant ($echanc["solde_cap"])."\" onchange=\"calculTotaux('$id_doss_pos',$id_doss,$nbrTotalEcheance);\"></TD>\n";
      $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"solde_int_$id_doss_pos\" size=7 value=\"".afficheMontant ($echanc["solde_int"])."\" onchange=\"calculTotaux('$id_doss_pos',$id_doss,$nbrTotalEcheance);\"></TD>\n";
      $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"solde_gar_$id_doss_pos\" size=7 value=\"".afficheMontant ($echanc["solde_gar"])."\" onchange=\"calculTotaux('$id_doss_pos',$id_doss,$nbrTotalEcheance);\"></TD>\n";
      $som=$echanc["solde_cap"] + $echanc["solde_int"] + $echanc["solde_gar"];
      $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"som_$id_doss_pos\" size=7 value=\"".afficheMontant ($som)."\" onchange=\"document.ADForm.som$id_doss_pos.value = recupMontant(document.ADForm.som$id_doss_pos.value);\" readOnly = true ></TD>\n";

      //$rest=$SESSION_VARS["cre_mnt_octr"] - $total_cap;
      //if($rest<=0)$rest='0';
      //$xtHTML .= "<TD align=\"right\">".afficheMontant ($rest,false)."</TD>\n";
      $xtHTML .= "</TR>\n";

    }

    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=3>Total:</TD>\n";
    //$xtHTML .= "<TD align=\"right\">".afficheMontant ($total_cap,false)."</TD>\n";
    $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"solde_cap_$id_doss\" size=7 value=\"".afficheMontant ($total_cap,false)."\" onchange=\"formateMontant(this.value)\" readOnly = true ></TD>\n";
    //$xtHTML .= "<TD align=\"right\">".afficheMontant ($total_int,false)."</TD>\n";
    $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"solde_int_$id_doss\" size=7 value=\"".afficheMontant ($total_int,false)."\" onchange=\"formateMontant(this.value)\" readOnly = true ></TD>\n";
    //$xtHTML .= "<TD align=\"right\">".afficheMontant ($total_gar,false)."</TD>\n";
    $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"solde_gar_$id_doss\" size=7 value=\"".afficheMontant ($total_gar,false)."\" onchange=\"formateMontant(this.value)\" readOnly = true ></TD>\n";
    $total = $total_cap + $total_int + $total_gar;
    //$xtHTML .= "<TD align=\"right\">".afficheMontant ($total,false)."</TD>\n";
    $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"tot_ech_$id_doss\" size=7 value=\"".afficheMontant ($total,false)."\" onchange=\"formateMontant(this.value)\" readOnly = true ></TD>\n";
    //$xtHTML .= "<TD>&nbsp;</TD>\n";
    $xtHTML .= "</TR>\n";
    $xtHTML .= "</TABLE>\n";


    //Fin Tableau des détail produit
    $xtHTML .= "</TD>\n";
    $xtHTML .= "</TR>\n";

    // Fin Tableau principal
    $xtHTML .= "</TABLE><BR>\n";

    // Ajout du javascript pour les couleurs
    $myForm->addJS(JSP_FORM, "js".$id_doss, $js);

    // Control JS pour les totaux cap, int et gar dans le tableau : document.ADForm.solde_cap
    $jsTotaux = "\n";
    $jsTotaux .= "function calculTotaux(id,doss,totEch){\n";
    //$jsTotaux .= "\t if (recupMontant(document.getElementsByName('solde_cap_'+id).item(0).value) > 0){\n";
    //$jsTotaux .=
    $jsTotaux .= "\t var solde_cap = recupMontant(document.getElementsByName('solde_cap_'+id).item(0).value);\n";
    $jsTotaux .= "\t var solde_int = recupMontant(document.getElementsByName('solde_int_'+id).item(0).value);\n";
    $jsTotaux .= "\t var solde_gar = recupMontant(document.getElementsByName('solde_gar_'+id).item(0).value);\n";
    $jsTotaux .= "\t var som = solde_cap + solde_int + solde_gar;\n";
    $jsTotaux .= "\t document.getElementsByName('som_'+id).item(0).value = formateMontant(som);\n";
    $jsTotaux .= "\t document.getElementsByName('solde_cap_'+id).item(0).value = formateMontant(solde_cap);\n";
    $jsTotaux .= "\t document.getElementsByName('solde_int_'+id).item(0).value = formateMontant(solde_int);\n";
    $jsTotaux .= "\t document.getElementsByName('solde_gar_'+id).item(0).value = formateMontant(solde_gar);\n";
    $jsTotaux .= "\t var tot_solde_cap = 0; var tot_solde_int = 0; var tot_solde_gar = 0;\n";
    $jsTotaux .= "\t for(var i=1; i<=totEch; i++){\n";
    $jsTotaux .= "\t\t tot_solde_cap += recupMontant(document.getElementsByName('solde_cap_'+doss+'_'+i).item(0).value);\n";
    $jsTotaux .= "\t\t tot_solde_int += recupMontant(document.getElementsByName('solde_int_'+doss+'_'+i).item(0).value);\n";
    $jsTotaux .= "\t\t tot_solde_gar += recupMontant(document.getElementsByName('solde_gar_'+doss+'_'+i).item(0).value);\n";
    $jsTotaux .= "\t}\n";
    $jsTotaux .= "\t document.getElementsByName('solde_cap_'+doss).item(0).value = formateMontant(tot_solde_cap);\n";
    $jsTotaux .= "\t document.getElementsByName('solde_int_'+doss).item(0).value = formateMontant(tot_solde_int);\n";
    $jsTotaux .= "\t document.getElementsByName('solde_gar_'+doss).item(0).value = formateMontant(tot_solde_gar);\n";
    $jsTotaux .= "\t document.getElementsByName('tot_ech_'+doss).item(0).value = formateMontant(tot_solde_cap + tot_solde_int + tot_solde_gar);\n";
    $jsTotaux .= "}\n";
    $myForm->addJS(JSP_FORM, "jstotal".$id_doss, $jsTotaux);


    // Au niveau du checkform, il faut vérifier que toutes les échéances remboursées sont consécutives à partir de la 1ère
    // la variable etat possède 3 niveaux
    //   2 => Un remboursement au moins a été constaté
    //   3 => Des remboursements ont été constatés, ensuite ils se sont terminés

    $jsCheck ="echRemb = true;"; // Au départ, aucune échéance n'a encore été remboursée
    reset($ECH);
    while (list($key, $ech) = each($ECH)) {
      $jsCheck .= "if (echRemb == true)
                {
                  if(document.ADForm.rembourse".$id_doss."_".$key."checked == false)
                {echRemb = false;}
                }
                  else if (echRemb == false)
                {
                  if (document.ADForm.rembourse".$id_doss."_".$key."checked == true)
                {
                  ADFormValid = false;msg = '"._("Les échéances doivent être remboursées à partir de la première et consécutivement")."\\n';
                }
                }
                  ";
    }

    $myForm->addJS(JSP_BEGIN_CHECK, "jsCheck".$id_doss, $jsCheck);
  } // fin parcours dossiers

  $dbHandler->closeConnection(true);
  // Ajout du tableau
  $myForm->addHTMLExtraCode("echeancier".$id_doss, $xtHTML);
	if (sizeof($ECH) == 0){
			// Boutons du formulaire
			 echo "<p align=\"center\"><B>"._("Les echéanciers ne sont pas générés. Veuillez les générer avant d'activer le dossier")."</p>";

		 	$myForm->addHiddenType("index",$index);
  		$myForm->addFormButton(1,1,"generer", _("Generer"), TYPB_SUBMIT);
  		$myForm->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);
  		$myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  		$myForm->setFormButtonProperties("generer", BUTP_PROCHAIN_ECRAN, 16);
  		$myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

		}
		else{
  		// Boutons du formulaire
  		$myForm->addHiddenType("index",$index);
  		$myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  		$myForm->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);
  		$myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  		$myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 13);
  		$myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
		}

  $myForm->buildHTML();
  echo $myForm->getHtml();
  $SESSION_VARS['ecran_precedent'] = 12; // suivi des écrans

}

// Ecran 13 : Présentation de l'échéancier
else if ($prochain_ecran == 13) {
  $db = $dbHandler->openConnection();

  //Truncate table 'ad_etr_temp' avant d'alimenter
  $sql = "TRUNCATE ad_etr_temp";
  $result = $db->query($sql);
  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    signalErreur("recup_credit.php", "Ecran 13", _("La requête ne s'est pas exécutée correctement1")." : ".$result->getMessage());
  }

  /* Récupération de tous les états de crédit */
  $etats_cr = array();
  $etats_cr = getTousEtatCredit();

  /* Récupération de l'état */
  $SESSION_VARS['id_etat_perte'] = getIDEtatPerte();
  // Parcours des dossiers
  $xtHTML = "";
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {

    for($i = 1; $i <= $SESSION_VARS['tot_ech'][$id_doss]; $i++){
      //print_rn('Solde cap '.$i.' = '.$_POST['solde_cap_'.$id_doss.'_'.$i]);
      // Insertion des echeances avec les valeurs de l'ecran precedent dans la table 'ad_etr_temp'
      if(empty(${"rembourse".$id_doss."_".$SESSION_VARS['Ech_values_'.$id_doss][$i]['id_ech']})){
        $SESSION_VARS['Ech_values_'.$id_doss][$i]['remb'] = 'f';
      }else{
        $SESSION_VARS['Ech_values_'.$id_doss][$i]['remb'] = 't';
      }
      $sql = "INSERT INTO ad_etr_temp (id_doss, id_ech, date_ech, mnt_cap, mnt_int, mnt_gar, mnt_reech, remb, solde_cap, solde_int, solde_gar, solde_pen, id_ag, date_creation) VALUES ($id_doss, $i, '".$SESSION_VARS['Ech_values_'.$id_doss][$i]['date_ech']."', '".$SESSION_VARS['Ech_values_'.$id_doss][$i]['mnt_cap']."', '".$SESSION_VARS['Ech_values_'.$id_doss][$i]['mnt_int']."', '".$SESSION_VARS['Ech_values_'.$id_doss][$i]['mnt_gar']."', '".$SESSION_VARS['Ech_values_'.$id_doss][$i]['mnt_reech']."', '".$SESSION_VARS['Ech_values_'.$id_doss][$i]['remb']."', '".recupMontant($_POST['solde_cap_'.$id_doss.'_'.$i])."', '".recupMontant($_POST['solde_int_'.$id_doss.'_'.$i])."', '".recupMontant($_POST['solde_gar_'.$id_doss.'_'.$i])."', '".$SESSION_VARS['Ech_values_'.$id_doss][$i]['solde_pen']."', numagc(), '".$SESSION_VARS['Ech_values_'.$id_doss][$i]['date_creation']."')";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur("recup_credit.php", "Ecran 13", _("La requête ne s'est pas exécutée correctement2")." : ".$result->getMessage());
      }
    }

    $id_prod = $val_doss['id_prod'];
    $PROD = getProdInfo(" where id =".$id_prod);
    $cre_mnt_octr = $val_doss['cre_mnt_octr'];
    $duree_mois = $val_doss['duree_mois'];
    $differe_jours = $val_doss['differe_jours'];
    $differe_ech = $val_doss['differe_ech'];
    $gar_num = $val_doss['gar_num'];
    $gar_num_encours = $val_doss['gar_num_encours'];
    $cre_date_debloc = pg2phpDate($val_doss['cre_date_debloc']);
    $mode_calc_int = $PROD[0]['mode_calc_int'];

    if (isset($val_doss["ECH"])) { // On vient de l'écran suivant
      $ECH = $val_doss["ECH"];
      $cre_etat = $val_doss["cre_etat"];
      $ech_courante = $val_doss["ech_courante"];
    } else {
      // Récupération des données concernant l'échéancier théorique de remboursement
      $sql = "SELECT * FROM ad_etr_temp WHERE id_ag = $global_id_agence and id_doss = $id_doss ORDER BY id_ech";
      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur("recup_credit.php", "Ecran 13", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
      }

      // Construction du tableau des échéances à partir de la base de données
      $SESSION_VARS['infos_doss'][$id_doss]['ECH'] = array();
      while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
        $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$tmprow["id_ech"]] = $tmprow;
        $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$tmprow["id_ech"]]["date_ech"] = pg2phpDate($SESSION_VARS['infos_doss'][$id_doss]['ECH'][$tmprow["id_ech"]]["date_ech"]);
      }

      // Recherche de la dernière échéance remboursée et remplissage du tableau
      $i = sizeof($SESSION_VARS['infos_doss'][$id_doss]['ECH']);
      $last_ech_remb = 0;
      $prec_ech_courante = 0; // On va utiliser cette varialbe pour trouver l'échéance courante, càd la prochaine échéance à partir d'aujourd'hui
      $solde_cap = 0;
      $rembourse = array(); // Array utilisé pour sauvegarder les valeurs

      while ($i > 0) {
        if (($ {'rembourse'.$id_doss.'_'.$i} == 'on')||($SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["remb"] == 't')) {
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["remb"] = 't';
          //mnt_cap prend le montant remboursé qui peut ètre modifié dans $solde_cap
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["mnt_cap"] = $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_cap"] ;
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["mnt_int"] = $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_int"] ;
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_cap"] = 0;
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_int"] = 0;
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_gar"] = 0;
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_pen"] = 0;
          if ($last_ech_remb == 0)
            $last_ech_remb = $i;
          $rembourse[$i] = true;
        }
        else {
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["remb"] = 'f';
          $solde_cap += $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_cap"];
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_pen"] = calculePenalitesCreditRepris($id_doss, $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["id_ech"], $solde_cap, $SESSION_VARS['date_reprise_credit']);
          $rembourse[$i] = false;
        }

        // Est-ce que c'est l'échéance courante - 1 ?
        if ($prec_ech_courante == 0 && (isBefore($SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["date_ech"], pg2phpDate($SESSION_VARS['date_reprise_credit']))))
          $prec_ech_courante = $i;

        $i--;

        $SESSION_VARS['infos_doss'][$id_doss]["rembourse"] = $rembourse;
      }
    }

    //Si la dernière échéance remboursée est la dernière échéance du crédit,crédit devait être soldé et ne pouvait donc pas être repris
    if ($last_ech_remb == sizeof($SESSION_VARS['infos_doss'][$id_doss]['ECH'])) {
      $colb_tableau = '#e0e0ff';
      $erreur = new HTML_erreur(_("Impossible de reprendre un crédit soldé"));
      $erreur->setMessage(_("ADbanking a calculé que ce crédit devait être déjà soldé."));
      $erreur->addButton(BUTTON_OK,1);
      $erreur->buildHTML();
      echo $erreur->HTML_code;
      die();
    }

    /* Détermination de l'état du crédit */
    $SESSION_VARS['infos_doss'][$id_doss]["cre_etat_max_jour"] = 0;
    $date_last_ech_remb = $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$last_ech_remb]["date_ech"];
    $date_first_ech_non_remb = $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$last_ech_remb+1]["date_ech"];

     if (isBefore($date_first_ech_non_remb, pg2phpDate($SESSION_VARS['date_reprise_credit']))) { // Le crédit est en retard
      /* Recherche du nombre de jours de retard à la date de reprise du bilan */
      $nbre_jours_retard = nbreDiffJours(pg2phpDate($SESSION_VARS['date_reprise_credit']), $date_first_ech_non_remb);

      /* Détermination de l'état en fonction du nombre de jours de retart */
      $cre_etat = calculeEtatCredit($nbre_jours_retard);
      $SESSION_VARS['infos_doss'][$id_doss]["cre_etat_max_jour"] = $nbre_jours_retard;
    } else /* le crédit est sain */
      $cre_etat = 1;

    ////echo "<p align=\"center\">ADbanking a déduit les informations suivantes concernant ce dossier de crédit";

    $myForm=new HTML_GEN2(_("Etape 2: Validation de l'état du crédit - fixation des pénalités"));
    $myForm->addHiddenType("id_doss", $id_doss);

    $SESSION_VARS['infos_doss'][$id_doss]["cre_etat"] = $cre_etat;

    // Construction du code HTML de l'échéancier
    // Inspiré de la fonction HTML_echeancier

    $PROD = getProdInfo(" where id =".$id_prod);
    $libel_prod = $PROD[0]["libel"];

    global $adsys;

    $colb_tableau = '#e0e0ff';
    $colb_tableau_retard = '#ffd5d5';
    $colb_tableau_rembourse = '#ccffcc';


    //Tableau principal

    $xtHTML .= "<br>"._("ADbanking a déduit les informations suivantes : le crédit est ").$etats_cr[$cre_etat]['libel'];
    $xtHTML .= "<TABLE width=\"70%\" align=\"center\" valign=\"middle\" cellspacing=0 cellpadding=0  border=$tableau_border>\n"; //border=1
    $xtHTML .= "<TR bgcolor=$colb_tableau>\n";
    $xtHTML .= "<TD>";

    //Tableau des détail produit
    $xtHTML .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=\"0\" border=\"0\>\n";
    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2 width=\"10%\">"._("Produit").":</TD>\n";
    $xtHTML .= "<TD width=\"40%\">$libel_prod</TD>\n"; //Produit
    $xtHTML .= "<TD colspan=2 width=\"20%\">"._("Montant octroyé")." 4:</TD>\n";
    $xtHTML .= "<TD>".afficheMontant ($cre_mnt_octr,true)."</TD>\n"; //Mnt octroyé
    $xtHTML .= "</TR>\n";

    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2 >"._("Durée").":</TD>\n";
    $xtHTML .= "<TD>".$duree_mois."</TD>\n"; //Durée
    $xtHTML .= "<TD colspan=2 >"._("Montant rééchel").".:</TD>\n";
    $xtHTML .= "<TD>".afficheMontant(0, true)."</TD>\n"; //mnt rééch
    $xtHTML .= "</TR>\n";

    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2>"._("Différé").":</TD>\n";

    $xtHTML .= "<TD>".str_affichage_diff($differe_jours, $differe_ech)."</TD>\n";
    $xtHTML .= "<TD colspan=2>"._("Garanties").":</TD>\n";
    $xtHTML .= "<TD>".afficheMontant($gar_num + $gar_num_encours, true)."</TD>\n"; //Garantie
    $xtHTML .= "</TR>\n";


    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2>"._("Date de déboursement").":</TD><TD>".$cre_date_debloc."</TD>\n";
    $xtHTML .= "<TD colspan=2>"._("Nom client").":</TD><TD>".getClientName($val_doss['id_client'])."</TD>\n";
    $xtHTML .= "</TR>\n";

    $xtHTML .= "</TABLE>\n";

    // Tableau des echéances

    // En-tête
    $xtHTML .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding  border=$tableau_border>\n";
    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD align=\"center\">"._("Remb")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("N°")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Date")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Capital")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Intérêts")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Garantie")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde Capital")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde Intérêts")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde Garantie")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde Pénalités")."</TD>\n";
    $xtHTML .= "</TR>\n";

    // Contenu
    $total_cap = 0;
    $total_int = 0;
    $total_gar = 0;
    $total_solde_cap = 0;
    $total_solde_int = 0;
    $total_solde_gar = 0;
    $total_solde_pen = 0;

    reset($SESSION_VARS['infos_doss'][$id_doss]['ECH']);
    while (list($key,$echanc) = each($SESSION_VARS['infos_doss'][$id_doss]['ECH'])) {
      $total_cap += ceil($echanc["mnt_cap"]);
      $total_int += ceil($echanc["mnt_int"]);
      $total_gar += ceil($echanc["mnt_gar"]);
      $total_solde_cap += ceil($echanc["solde_cap"]);
      $total_solde_int += ceil($echanc["solde_int"]);
      $total_solde_gar += ceil($echanc["solde_gar"]);
      $total_solde_pen += ceil($echanc["solde_pen"]);

      // Affichage
      // Si l'échéance est remboursée, on affiche en vert,
      // Sinon, si la date d'échéance est antérieure à la date de reprise bilan, on affiche en rouge,
      // Sinon on affiche en bleu
      if ($echanc["remb"] == 't')
        $xtHTML .= "<TR bgcolor=\"$colb_tableau_rembourse\">\n";
      else if (isBefore($echanc["date_ech"], pg2phpDate($SESSION_VARS['date_reprise_credit']))) {
        $xtHTML .= "<TR bgcolor=\"$colb_tableau_retard\">\n";
        $retard = true;
      } else {
        $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
        $retard = false;
      }
      if ($echanc['remb'] == 't')
        $xtHTML .= "<TD align=\"center\">OUI</TD>\n";
      else
        $xtHTML .= "<TD align=\"center\">NON</TD>\n";
      $xtHTML .= "<TD align=\"center\">".$echanc["id_ech"]."</TD>\n";
      $xtHTML .= "<TD align=\"left\">".$echanc["date_ech"]."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_cap"], false)."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_int"], false)."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_gar"], false)."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_cap"], false)."</TD>\n";

      {
        $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_int"], false)."</TD>\n";

        $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_gar"], false)."</TD>\n";

        if ($echanc['remb'] == 't' || $retard == false)
          $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_pen"], false)."</TD>\n";
        else
          $xtHTML .= "<TD align=\"right\"><INPUT type=\"text\" name=\"pen$key\" size=7 value=\"".afficheMontant($echanc["solde_pen"])."\" onchange=\"document.ADForm.pen$key.value = recupMontant(document.ADForm.pen$key.value);\"></INPUT></TD>\n";
        $xtHTML .= "</TR>\n";
      }
    }

    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD align=\"center\" colspan=3><B>Total</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_cap,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_int,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_gar,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_solde_cap,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_solde_int,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_solde_gar,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_solde_pen,false)."</B></TD>\n";
    $xtHTML .= "</TR>\n";
    $xtHTML .= "</TABLE>\n";

    // Fin Tableau des échéances
    $xtHTML .= "</TD>\n";
    $xtHTML .= "</TR>\n";

    // Fin Tableau principal
    $xtHTML .= "</TABLE><BR>\n";

  } // fin parcours dossiers

  $dbHandler->closeConnection(true);

  // Ajout du tableau
  $myForm->addHTMLExtraCode("echeancier", $xtHTML);

  // Boutons du formulaire
  $myForm->addHiddenType("index",$index);
  $myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"retour",_("Retour"),TYPB_SUBMIT);
  $myForm->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 12);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 14);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();
  $SESSION_VARS['ecran_precedent'] = 13; // suivi des écrans
}

// Ecran 14 : Confirmation de l'échéancier et paramétrage de la comptabilité
else if ($prochain_ecran == 14) {

  // Récupération des états de crédit
  $etats_cr = array();
  $etats_cr = getTousEtatCredit();
  $xtHTML = "";
  // Parcours des dossiers
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    // Remplissage du tableau avec les montants en pénalités entrés par l'utilisateur
    while (list($key, $ech) = each($val_doss['ECH'])) {
      if (isset($ {'pen'.$key})) {
        if ($ {'pen'.$key} == '') { // Si Montant rensiengé est vide
          $pen[$key] = 0;
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$key]["solde_pen"] = 0;
        }
        else {
          $pen[$key] = recupMontant($ {'pen'.$key});
          $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$key]["solde_pen"] = recupMontant($ {'pen'.$key});
        }
      }
    }

    $id_prod = $val_doss['id_prod']; // Même si c'est plusieurs dossiers, on a un seul produit de crédit
    $PROD = getProdInfo(" where id =".$id_prod);
    $libel_prod = $PROD[0]["libel"];

    global $adsys;

    $colb_tableau = '#e0e0ff';
    $colb_tableau_retard = '#ffd5d5';
    $colb_tableau_rembourse = '#ccffcc';

    //Tableau principal
    $xtHTML .= "<BR>";
    $xtHTML .= "<TABLE width=\"70%\" align=\"center\" valign=\"middle\" cellspacing=0 cellpadding=0  border=$tableau_border>\n"; //border=1
    $xtHTML .= "<TR bgcolor=$colb_tableau>\n";
    $xtHTML .= "<TD>";

    //Tableau des détail produit
    $xtHTML .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=\"0\" border=\"0\>\n";
    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2 width=\"10%\">"._("Produit").":</TD>\n";
    $xtHTML .= "<TD width=\"40%\">$libel_prod</TD>\n"; //Produit
    $xtHTML .= "<TD colspan=2 width=\"20%\">"._("Montant octroyé 5").":</TD>\n";
    $xtHTML .= "<TD>".afficheMontant ($SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'],true)."</TD>\n"; //Mnt octroyé
    $xtHTML .= "</TR>\n";

    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2 >"._("Durée").":</TD>\n";
    $xtHTML .= "<TD>".$SESSION_VARS['infos_doss'][$id_doss]['duree_mois']." "._("mois")."</TD>\n"; //Durée
    $xtHTML .= "<TD colspan=2 >"._("Montant rééchel.").":</TD>\n";
    $xtHTML .= "<TD>".afficheMontant(0, true)."</TD>\n"; //mnt rééch
    $xtHTML .= "</TR>\n";

    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2>"._("Différé")." :</TD>\n";

    $xtHTML .= "<TD>".str_affichage_diff($SESSION_VARS['infos_doss'][$id_doss]['differe_jours'], $SESSION_VARS['infos_doss'][$id_doss]['differe_ech'])."</TD>\n";
    $xtHTML .= "<TD colspan=2>"._("Garanties").":</TD>\n";
    $xtHTML .= "<TD>".afficheMontant($SESSION_VARS['infos_doss'][$id_doss]['gar_num'] + $SESSION_VARS['infos_doss'][$id_doss]['gar_num_encours'], true)."</TD>\n"; //Garantie
    $xtHTML .= "</TR>\n";


    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD colspan=2>"._("Date de déboursement").":</TD><TD>".$SESSION_VARS['infos_doss'][$id_doss]['cre_date_debloc']."</TD>\n";
    $xtHTML .= "<TD colspan=2>"._("Nom client").":</TD><TD>".getClientName($val_doss['id_client'])."</TD>\n";
    $xtHTML .= "</TR>\n";

    $xtHTML .= "</TABLE>\n";

    // Tableau des echéances

    // En-tête
    $xtHTML .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding  border=$tableau_border>\n";
    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD align=\"center\">"._("Remb")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("N°")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Date")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Capital")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Intérêts")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Garantie")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde Capital")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde Intérêts")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde Garantie")."</TD>\n";
    $xtHTML .= "<TD align=\"center\">"._("Solde Pénalités")."</TD>\n";
    $xtHTML .= "</TR>\n";

    // Contenu
    $total_cap = 0;
    $total_int = 0;
    $total_gar = 0;
    $total_solde_cap = 0;
    $total_solde_int = 0;
    $total_solde_gar = 0;
    $total_solde_pen = 0;

    reset($SESSION_VARS['infos_doss'][$id_doss]['ECH']);
    while (list($key,$echanc) = each($SESSION_VARS['infos_doss'][$id_doss]['ECH'])) {
      $total_cap += ceil($echanc["mnt_cap"]);
      $total_int += ceil($echanc["mnt_int"]);
      $total_gar += ceil($echanc["mnt_gar"]);
      $total_solde_cap += ceil($echanc["solde_cap"]);
      $total_solde_int += ceil($echanc["solde_int"]);
      $total_solde_gar += ceil($echanc["solde_gar"]);
      $total_solde_pen += ceil($echanc["solde_pen"]);

      // Affichage
      // Si l'échéance est remboursée, on affiche en vert,
      // Sinon, si la date d'échéance est antérieure à la date de reprise du bilan, on affiche en rouge,
      // Sinon on affiche en bleu
      if ($echanc["remb"] == 't')
        $xtHTML .= "<TR bgcolor=\"$colb_tableau_rembourse\">\n";
      else if (isBefore($echanc["date_ech"], pg2phpDate($SESSION_VARS['date_reprise_credit']))) {
        $xtHTML .= "<TR bgcolor=\"$colb_tableau_retard\">\n";
        $retard = true;
      } else {
        $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
        $retard = false;
      }
      if ($echanc['remb'] == 't')
        $xtHTML .= "<TD align=\"center\">OUI</TD>\n";
      else
        $xtHTML .= "<TD align=\"center\">NON</TD>\n";
      $xtHTML .= "<TD align=\"center\">".$echanc["id_ech"]."</TD>\n";
      $xtHTML .= "<TD align=\"left\">".$echanc["date_ech"]."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_cap"], false)."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_int"], false)."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["mnt_gar"], false)."</TD>\n";
      $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_cap"], false)."</TD>\n";

      {
        $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_int"], false)."</TD>\n";
        $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_gar"], false)."</TD>\n";
        $xtHTML .= "<TD align=\"right\">".afficheMontant ($echanc["solde_pen"], false)."</TD>\n";
      }
      $xtHTML .= "</TR>\n";

    }
    $xtHTML .= "<TR bgcolor=\"$colb_tableau\">\n";
    $xtHTML .= "<TD align=\"center\" colspan=3><B>Total</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_cap,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_int,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_gar,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_solde_cap,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_solde_int,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_solde_gar,false)."</B></TD>\n";
    $xtHTML .= "<TD align=\"right\"><B>".afficheMontant ($total_solde_pen,false)."</B></TD>\n";
    $xtHTML .= "</TR>\n";
    $xtHTML .= "</TABLE>\n";

    // Fin Tableau des échéances
    $xtHTML .= "</TD>\n";
    $xtHTML .= "</TR>\n";

    // Fin Tableau principal
    $xtHTML .= "</TABLE><BR>\n";

    $myForm = new HTML_GEN2(_("Etape 4: Confirmation de l'échéancier<BR>Le crédit est ")."'". $etats_cr[$SESSION_VARS['infos_doss'][$id_doss]['cre_etat']]['libel']."'<BR>"._("Total des pénalités attendues")." : ".afficheMontant($total_solde_pen, true));
    $myForm->addHiddenType("id_doss", $id_doss);

    // Récupération des comptes de substitut du produit de crédit et de la garantie
    $cpte_subs_cr = "";
    $cpte_subs_gar = "";
    $fp = fopen("../traduction.conf",'r'); // ouverture du fichier de paramétrage de la reprise
    if ($fp == false) {
      echo "<BR/><BR/><P align=center><FONT color=red>"._("Le fichier <CODE><B>traduction.conf</B></CODE> n'a pas été trouvé à l'endroit attendu")."</FONT></P>";
      die();
    }

    while (!feof($fp)) {
      // Récupération d'une ligne du fichier
      $ligne = fgets($fp,1024);

      // Si c'est le paramétrage du compte du produit
      $id = $id_prod;
      $ch = "cpt_etat_cr_".$id."_".$SESSION_VARS['infos_doss'][$id_doss]['cre_etat'];
      $long = strlen($ch);
      $debut = substr($ligne,0,$long);
      if ($debut == $ch) {
        $ligne = ereg_replace("$ch","",$ligne); /* éliminer "cpt_etat_cr_$id_$etat" de la ligne */
        $ligne = ereg_replace("\t","|",$ligne); /* Remplacer tabulation par | */
        $ligne = ereg_replace("\n","|",$ligne); /* Remplacer fin de ligne par | */
        $tab = explode("|",$ligne); /* récupérer la ligne sous forme de tableau */
        $cpte_subs_cr = trim($tab[1]);  /* éliminer les espaces de début et de fin de ligne */
       }
      // Compte de substitut de la garantie
      $ch = "cpte_gar_".$id;
      $long = strlen($ch);
      $debut = substr($ligne,0,$long);
      if ($debut == $ch) {
        $ligne = ereg_replace("$ch","",$ligne); /* éliminer "cpte_gar_$id" de la ligne */
        $ligne = ereg_replace("\t","|",$ligne); /* Remplacer tabulation par | */
        $ligne = ereg_replace("\n","|",$ligne); /* Remplacer fin de ligne par | */
        $tab = explode("|",$ligne); /* récupérer la ligne sous forme de tableau */
        $cpte_subs_gar = trim($tab[1]);  /* éliminer les espaces de début et de fin de ligne */
      }
    }
    fclose($fp);

    $SESSION_VARS['infos_doss'][$id_doss]["cpte_subs_cr"] = $cpte_subs_cr;
    $SESSION_VARS['infos_doss'][$id_doss]["cpte_subs_gar"] = $cpte_subs_gar;

    $table1 = new HTML_TABLE_table(2, TABLE_STYLE_ALTERN);
    ////$table1->set_property("title",_("Informations sur le client "));
    $table1->add_cell(new TABLE_cell(_("Compte de substitut du produit ")));
    $table1->add_cell(new TABLE_cell($cpte_subs_cr));

    $table1->add_cell(new TABLE_cell(_("Compte de substitut de la garantie")));
    $table1->add_cell(new TABLE_cell($cpte_subs_gar));

    $table1->set_property("align", "center");
    $table1->set_property("border", $tableau_border);
    $table1->set_property("bgcolor", $colb_tableau);
    $xtHTML .= $table1->gen_HTML();
    // Enregistreement du solde en capital
    $SESSION_VARS['infos_doss'][$id_doss]["solde_cap"] = $total_solde_cap;
    // Enregistreement de la garantie remboursée
    $SESSION_VARS['infos_doss'][$id_doss]["gar_remb"] = $total_gar - $total_solde_gar;

  } // fin parcours dossiers

  // Ajout du tableau
  $myForm->addHTMLExtraCode("echeancier", $xtHTML);

  // Boutons du formulaire
  $myForm->addHiddenType("index",$index);
  $myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"retour",_("Retour"),TYPB_SUBMIT);
  $myForm->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 13);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 15);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $myForm->buildHTML();
  echo $myForm->getHTML();
  $SESSION_VARS['ecran_precedent'] = 14; // suivi des écrans

}

// Ecran 15 : Création du compte de crédit et du compte de garantie
else if ($prochain_ecran == 15) {
  global $db, $dbHandler;
  $db = $dbHandler->openConnection();

  // Exercice en cours
  $global_id_agence = getNumAgence();
  $compteur_mv = 0;
  $compteur_ecr = 1;
  $AG = getAgenceDatas($global_id_agence);
  $exo = $AG["exercice"];

  $adsys_pr_cr = getProdInfoByID();

	// recupère le type de passage en perte
	$sql_type_perte = "select passage_perte_automatique from ad_agc";
	$result_type_perte = $db->query($sql_type_perte);
	if (DB::isError($result_type_perte)) {
	$dbHandler->closeConnection(false);
	signalErreur(__FILE__, __LINE__, __FUNCTION__);
	}
	$row_perte = $result_type_perte->fetchrow();
	$type_radiation_credit = $AG["passage_perte_automatique"];

  // Parcours des dossiers
  $comptable = array(); // Passage des écritures comptables
  $compteur_mv = 0;
  $compteur_ecr = 1;
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $ECH = $val_doss['ECH'];
    $id_client = $val_doss['id_client'];
    $id_prod = $val_doss['id_prod'];
    $cre_mnt_octr = $val_doss['cre_mnt_octr'];
    $gar_num = $val_doss['gar_num'];
    $gar_num_encours = $val_doss['gar_num_encours'];
    $cre_date_debloc = pg2phpDate($val_doss['cre_date_debloc']);
    $solde_cap = 0;

    // Mise à jour de solde_cap, solde_int et solde_pen dans ad_etr
    while (list($i, $ech) = each($ECH)) {
      $solde_cap += $ech["solde_cap"];

      // Si le crédit est en perte, le restant dû en garantie dans l'échéancier est mis à 0
      $sql = "UPDATE ad_etr SET solde_cap = ".$ech["solde_cap"].", solde_int = ".$ech["solde_int"].", solde_gar = ".$ech["solde_gar"].", solde_pen = ".$ech["solde_pen"].", remb = '".$ech["remb"]."' WHERE id_doss = $id_doss AND id_ech = $i AND id_ag=$global_id_agence ";
      $result = $db->query($sql);
      if (DB::isError($result)) {

        $dbHandler->closeConnection(false);
        signalErreur("recup_credit.php", "Ecran 15", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
      }

      /* Insertion du remboursement  */
      if ($ech["remb"]=='t') {
        $mnt_remb_cap = 0;
        $mnt_remb_int = 0;
        $mnt_remb_gar = 0;
        $mnt_remb_pen = 0;

        //FIXME: on suppose qu'il a tout payé: les montants présentés par l'échéancier */
        if (recupMontant($ech["mnt_cap"])!="")
          $mnt_remb_cap = recupMontant($ech["mnt_cap"]);
        if (recupMontant($ech["mnt_int"])!="")
          $mnt_remb_int = recupMontant($ech["mnt_int"]);
        if (recupMontant($ech["mnt_gar"])!="")
          $mnt_remb_gar = recupMontant($ech["mnt_gar"]);
        if (recupMontant($ech["mnt_pen"])!="")
          $mnt_remb_pen = recupMontant($ech["mnt_pen"]);

        $id_ech = $i;
        $num_rem = getNextNumRemboursement($id_doss, $id_ech);

        $sql = "INSERT INTO ad_sre(id_doss, id_ag, num_remb, date_remb, id_ech, mnt_remb_cap, mnt_remb_int, mnt_remb_pen, mnt_remb_gar) ";
        $sql .= "VALUES($id_doss, $global_id_agence, $num_rem, '".$SESSION_VARS['date_reprise_credit']."', $id_ech, $mnt_remb_cap, $mnt_remb_int, $mnt_remb_pen, $mnt_remb_gar)";
        $result=$db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);

          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }
      }
    }

    $array_credit = getCompteCptaDcr($id_doss);
    $cre_etat = $val_doss["cre_etat"] ;

    // Création du compte de crédit associé
    $rang = getRangDisponible($id_client);
    $numCompletCptCredit = makeNumCpte($id_client, $rang);
    $CPT_CRED = array();
    $CPT_CRED["id_titulaire"] = $id_client;
    $CPT_CRED["date_ouvert"] = $cre_date_debloc;
    $CPT_CRED["utilis_crea"] = 1; // Par défaut 'Administrateur'
    if ($val_doss["cre_etat"] == $SESSION_VARS['id_etat_perte']) // crédit en perte
      $CPT_CRED["etat_cpte"] = 2; // Compte bloqué
    else
      $CPT_CRED["etat_cpte"] = 1; // Compte ouvert
    $CPT_CRED["solde"] = 0; // Le solde sera mis à jour par le passage des écriture comptables
    $CPT_CRED["num_cpte"] = $rang;
    $CPT_CRED["num_complet_cpte"] = $numCompletCptCredit;
    $CPT_CRED["id_prod"] = 3;
    $CPT_CRED["cpt_vers_int"] = NULL;

    $CPT_CRED["devise"] = $adsys_pr_cr[$id_prod]['devise'];
		$message = "";
    // Création du compte de crédit
    $id_cpte_cr = creationCompte($CPT_CRED);
		if($id_cpte_cr == NULL){
			$message = sprintf(_("Erreur interne lors de la création du compte de crédit: "), $numCompletCptCredit);
		}
    // Mise à jour du dossier de crédit
    $DATA_DOSS = array();
    if ($val_doss["cre_etat"] == $SESSION_VARS['id_etat_perte']) { // crédit en perte
      $DATA_DOSS['etat'] = 9; // dossier passé en perte
      $DATA_DOSS['suspension_pen'] = true;
      $DATA_DOSS['perte_capital'] = $solde_cap;
    } else
      $DATA_DOSS['etat'] = 5;

    $DATA_DOSS['cre_id_cpte'] = $id_cpte_cr;
    $DATA_DOSS['cre_etat'] = $val_doss["cre_etat"];
    $DATA_DOSS['cre_retard_etat_max'] = $val_doss["cre_etat"];
    $DATA_DOSS['cre_retard_etat_max_jour'] = $val_doss['cre_etat_max_jour'];
    $DATA_DOSS['cre_date_etat'] = $SESSION_VARS['date_reprise_credit'];
    updateCredit($id_doss, $DATA_DOSS);

    // Compte associé à l'état du crédit
    $cr_etat_cptes = array();
    $cr_etat_cptes = getComptesEtatsProduits();

    $DOSSIER = getDossierCrdtInfo($id_doss);
    foreach($cr_etat_cptes as $key=>$value) {
      if ($value['id_prod_cre']==$id_prod and  $value['id_etat_credit']== $DOSSIER['cre_etat'])
        $cpte_credit = $value['num_cpte_comptable'];
    }

    if ($val_doss["cre_etat"] != $SESSION_VARS['id_etat_perte']) { // Si le crédit n'est pas en perte
      /****** Pasage du mouvement comptable : contrepassation du mouvement de la reprise du bilan  *******/
    	$libel_ecriture = new Trad();
        $libel_ecriture->set_traduction($global_langue_utilisateur,  _('Reprises de crédit'));
            
      $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_cr"], $adsys_pr_cr[$id_prod]['devise']);
      $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
      $comptable[$compteur_mv]["montant"] = $solde_cap;
      $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
      $comptable[$compteur_mv]["type_operation"] = 2001;
      $comptable[$compteur_mv]["id"] = $compteur_ecr;
      $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
      $compteur_mv++;

      $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($cpte_credit, $adsys_pr_cr[$id_prod]['devise']);
      $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
      $comptable[$compteur_mv]["montant"] = $solde_cap;
      $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
      $comptable[$compteur_mv]["type_operation"] = 2001;
      $comptable[$compteur_mv]["id"] = $compteur_ecr;
      $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_cr;
      $compteur_mv++;  /* incrémentation du id des mouvements  */
      $compteur_ecr++; /* incrémentation du id des écritures */
	      // Création d'un compte d'épargne nantie pour chaque garantie numéraire bloquée au début
      if ($gar_num > 0) {

        $liste_gar = getListeGaranties($id_doss);
        foreach($liste_gar as $key=>$val) {
          if ($val['type_gar'] == 1 and ($val['gar_num_id_cpte_prelev'] != '')) {
            $CPT_EN = array();

            // S'il y a un compte de prélèvement, le compte de garantie appartient au garant
            $InfoCpte = getAccountDatas($val['gar_num_id_cpte_prelev']);
            $CPT_EN["id_titulaire"] = $InfoCpte["id_titulaire"];

            // Recherche du rang disponible
            $rang = getRangDisponible($CPT_EN["id_titulaire"]);
            $CPT_EN["num_complet_cpte"] = makeNumCpte($CPT_EN["id_titulaire"], $rang);
            $CPT_EN["date_ouvert"] = $cre_date_debloc;
            $CPT_EN["utilis_crea"] = 1;  // Par défaut 'Administrateur'
            $CPT_EN["etat_cpte"] = 3;   // Bloqué
            $CPT_EN["solde"] = 0; /* Le solde sera mis à jour par le passage des écriture comptables*/
            $CPT_EN["num_cpte"] = $rang;
            $CPT_EN["id_prod"] = 4;
            $CPT_EN["devise"] = $adsys_pr_cr[$id_prod]['devise'];
            $CPT_EN["cpt_vers_int"] = NULL;

            /* Création du compte nantie*/
            $id_cpte_en = creationCompte($CPT_EN);
						if($id_cpte_en == NULL){
							$message = sprintf(_("Erreur interne lors de la création du compte de garantie numéraire: "), $CPT_EN['num_complet_cpte']);
						}

            /* Renseigner le compte de garantie dans ad_gar */
            $sql = "UPDATE ad_gar SET gar_num_id_cpte_nantie = $id_cpte_en WHERE id_gar = ".$val['id_gar']." AND id_ag= $global_id_agence ";
            $result=$db->query($sql);
            if (DB::isError($result)) {
              $dbHandler->closeConnection(false);
              signalErreur(__FILE__,__LINE__,__FUNCTION__);
            }
            $libel_ecriture = new Trad();
            $libel_ecriture->set_traduction($global_langue_utilisateur,  _("Transfert de garantie - Reprises de crédit"));
            

            /****** Passage du mouvement comptable: contrepassation du mouvement de la reprise du bilan  *******/
            $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_gar"],$adsys_pr_cr[$id_prod]['devise']);
            $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
            $comptable[$compteur_mv]["montant"] = $val['montant_vente'];
            $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
            $comptable[$compteur_mv]["type_operation"] = 220;
            $comptable[$compteur_mv]["id"] = $compteur_ecr;
            $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
            $compteur_mv++;

            $comptable[$compteur_mv]["compte"]=checkCptDeviseOK($array_credit["cpte_cpta_prod_cr_gar"],$adsys_pr_cr[$id_prod]['devise']);
            $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
            $comptable[$compteur_mv]["montant"] = $val['montant_vente'];
            $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
            $comptable[$compteur_mv]["type_operation"] = 220;
            $comptable[$compteur_mv]["id"] = $compteur_ecr;
            $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_en;
            $compteur_mv++; /* Incrémentation du id des mouvements */
            $compteur_ecr++; /* Incrémentation du id des écritures */

          } /* Fin if($val['type_gar'] == 1) */
        } /* Fin foreach sur les garanties*/
      } /* Fin if ($gar_num > 0) */

      /* S'il y a des garanties numéraires encours */
      if ($gar_num_encours > 0) {
        /* Le compte des garanties encours appartient toujours au client lui-même */
        $rang = getRangDisponible($id_client);
        $numCompletCptCredit = makeNumCpte($id_client, $rang);

        $CPT_EN = array();
        $CPT_EN["id_titulaire"] = $id_client;
        $CPT_EN["date_ouvert"] = $cre_date_debloc;
        $CPT_EN["utilis_crea"] = 1;  // Par défaut 'Administrateur'
        $CPT_EN["etat_cpte"] = 1;   // le compte est ouvert
        $CPT_EN["solde"] = 0; /* Le solde sera mis à jour par le passage des écriture comptables*/
        $CPT_EN["num_cpte"] = $rang;
        $CPT_EN["num_complet_cpte"] = $numCompletCptCredit;
        $CPT_EN["id_prod"] = 4;
        $CPT_EN["devise"] = $adsys_pr_cr[$id_prod]['devise'];
        $CPT_EN["cpt_vers_int"] = NULL;

        /* Création du compte nantie*/
        $id_cpte_en = creationCompte($CPT_EN);
				if($id_cpte_en == NULL){
					$message = sprintf(_("Erreur interne lors de la création du compte de garantie numéraire en cours: "), $numCompletCptCredit);
				}
        /* Ajout du compte des garanties encours dans le dossier de crédit */
        $DATA_DOSS = array();
        $DATA_DOSS['cpt_gar_encours'] = $id_cpte_en;
        updateCredit($id_doss, $DATA_DOSS);

        /* Insertion de la garantie numéraire à constituer dans la tables des garanties */
        $GAR_ENCOURS = array();
        $GAR_ENCOURS['type_gar'] = 1 ;
        $GAR_ENCOURS['id_doss'] = $id_doss;
        $GAR_ENCOURS['gar_num_id_cpte_prelev'] = NULL;
        $GAR_ENCOURS['gar_num_id_cpte_nantie'] = $id_cpte_en;
        $GAR_ENCOURS['etat_gar'] = 1; /* En cours de mobilisation */
        $GAR_ENCOURS['montant_vente'] = $val_doss["gar_remb"] ;
        $GAR_ENCOURS['devise_vente'] = $adsys_pr_cr[$id_prod]['devise'];
        $GAR_ENCOURS['id_ag'] = $global_id_agence;
        $sql = buildInsertQuery ("ad_gar", $GAR_ENCOURS);
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);

          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        /****** Ecriture comptable du remboursement de la garantie : contrepassation du mouvement de la reprise du bilan  *******/
        if ($SESSION_VARS["gar_remb"] > 0) {
        	$libel_ecriture = new Trad();
            $libel_ecriture->set_traduction($global_langue_utilisateur,  _("Transfert de garantie - Reprises de crédit"));
            
          $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_gar"], $adsys_pr_cr[$id_prod]['devise']);
          $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
          $comptable[$compteur_mv]["montant"] = $val_doss["gar_remb"];
          $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
          $comptable[$compteur_mv]["type_operation"] = 220;
          $comptable[$compteur_mv]["id"] = $compteur_ecr;
          $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
          $compteur_mv++;

          $comptable[$compteur_mv]["compte"]=checkCptDeviseOK($array_credit["cpte_cpta_prod_cr_gar"],$adsys_pr_cr[$id_prod]['devise']);
          $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
          $comptable[$compteur_mv]["montant"] = $val_doss["gar_remb"];                                     ;
          $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
          $comptable[$compteur_mv]["type_operation"] = 220;
          $comptable[$compteur_mv]["id"] = $compteur_ecr;
          $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_en;
          $compteur_mv++;
          $compteur_ecr++;
        } /* Fin if($SESSION_VARS["gar_remb"] > 0) */

      } /* Fin if($gar_num_encours > 0) */

    } // Fin si crédit n'est pas en perte
		else {//crédit est en perte
    		if ($type_radiation_credit == 'f') { // ce cas se présentera uniquement pour une radiation manuelle des crédits
			      /****** Pasage du mouvement comptable : contrepassation du mouvement de la reprise du bilan  *******/
      		$comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_cr"], $adsys_pr_cr[$id_prod]['devise']);
      		$comptable[$compteur_mv]["sens"] = SENS_CREDIT;
     			$comptable[$compteur_mv]["montant"] = $solde_cap;
     			$libel_ecriture = new Trad();
                $libel_ecriture->set_traduction($global_langue_utilisateur,  _('Reprise de crédit'));
            
     			$comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
     			$comptable[$compteur_mv]["type_operation"] = 2001;
      		$comptable[$compteur_mv]["id"] = $compteur_ecr;
      		$comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
      		$compteur_mv++;

      		$comptable[$compteur_mv]["compte"] = checkCptDeviseOK($cpte_credit, $adsys_pr_cr[$id_prod]['devise']);
      		$comptable[$compteur_mv]["sens"] = SENS_DEBIT;
      		$comptable[$compteur_mv]["montant"] = $solde_cap;
      		
            $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();     
      		$comptable[$compteur_mv]["type_operation"] = 2001;
      		$comptable[$compteur_mv]["id"] = $compteur_ecr;
      		$comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_cr;
      		$compteur_mv++;  /* incrémentation du id des mouvements  */
      		$compteur_ecr++; /* incrémentation du id des écritures */


    		// Création d'un compte d'épargne nantie pour chaque garantie numéraire bloquée au début
      	if ($gar_num > 0) {

        $liste_gar = getListeGaranties($id_doss);
        foreach($liste_gar as $key=>$val) {
          if ($val['type_gar'] == 1 and ($val['gar_num_id_cpte_prelev'] != '')) {
            $CPT_EN = array();

            // S'il y a un compte de prélèvement, le compte de garantie appartient au garant
            $InfoCpte = getAccountDatas($val['gar_num_id_cpte_prelev']);
            $CPT_EN["id_titulaire"] = $InfoCpte["id_titulaire"];

            // Recherche du rang disponible
            $rang = getRangDisponible($CPT_EN["id_titulaire"]);
            $CPT_EN["num_complet_cpte"] = makeNumCpte($CPT_EN["id_titulaire"], $rang);
            $CPT_EN["date_ouvert"] = $cre_date_debloc;
            $CPT_EN["utilis_crea"] = 1;  // Par défaut 'Administrateur'
            $CPT_EN["etat_cpte"] = 3;   // Bloqué
            $CPT_EN["solde"] = 0; /* Le solde sera mis à jour par le passage des écriture comptables*/
            $CPT_EN["num_cpte"] = $rang;
            $CPT_EN["id_prod"] = 4;
            $CPT_EN["devise"] = $adsys_pr_cr[$id_prod]['devise'];
            $CPT_EN["cpt_vers_int"] = NULL;

            /* Création du compte nantie*/
            $id_cpte_en = creationCompte($CPT_EN);
            if($id_cpte_en == NULL){
							$message = sprintf(_("Erreur interne lors de la création du compte de garantie numéraire: "), $CPT_EN["num_complet_cpte"]);
						}

            /* Renseigner le compte de garantie dans ad_gar */
            $sql = "UPDATE ad_gar SET gar_num_id_cpte_nantie = $id_cpte_en WHERE id_gar = ".$val['id_gar']." AND id_ag= $global_id_agence ";
            $result=$db->query($sql);
            if (DB::isError($result)) {
              $dbHandler->closeConnection(false);
              signalErreur(__FILE__,__LINE__,__FUNCTION__);
            }
             $libel_ecriture = new Trad();
            $libel_ecriture->set_traduction($global_langue_utilisateur,  _('Transfert de garantie - Reprises de crédit'));
                                             
            /****** Passage du mouvement comptable: contrepassation du mouvement de la reprise du bilan  *******/
            $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_gar"],$adsys_pr_cr[$id_prod]['devise']);
            $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
            $comptable[$compteur_mv]["montant"] = $val['montant_vente'];
            $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();  
            $comptable[$compteur_mv]["id"] = $compteur_ecr;
            $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
            $compteur_mv++;

            $comptable[$compteur_mv]["compte"]=checkCptDeviseOK($array_credit["cpte_cpta_prod_cr_gar"],$adsys_pr_cr[$id_prod]['devise']);
            $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
            $comptable[$compteur_mv]["montant"] = $val['montant_vente'];
            
            $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();     
            $comptable[$compteur_mv]["type_operation"] = 220;
            $comptable[$compteur_mv]["id"] = $compteur_ecr;
            $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_en;
            $compteur_mv++; /* Incrémentation du id des mouvements */
            $compteur_ecr++; /* Incrémentation du id des écritures */

          	} /* Fin if($val['type_gar'] == 1) */
        	} /* Fin foreach sur les garanties*/
      	} /* Fin if ($gar_num > 0) */

      /* S'il y a des garanties numéraires encours */
      if ($gar_num_encours > 0) {
        /* Le compte des garanties encours appartient toujours au client lui-même */
        $rang = getRangDisponible($id_client);
        $numCompletCptCredit = makeNumCpte($id_client, $rang);

        $CPT_EN = array();
        $CPT_EN["id_titulaire"] = $id_client;
        $CPT_EN["date_ouvert"] = $cre_date_debloc;
        $CPT_EN["utilis_crea"] = 1;  // Par défaut 'Administrateur'
        $CPT_EN["etat_cpte"] = 1;   // le compte est ouvert
        $CPT_EN["solde"] = 0; /* Le solde sera mis à jour par le passage des écriture comptables*/
        $CPT_EN["num_cpte"] = $rang;
        $CPT_EN["num_complet_cpte"] = $numCompletCptCredit;
        $CPT_EN["id_prod"] = 4;
        $CPT_EN["devise"] = $adsys_pr_cr[$id_prod]['devise'];
        $CPT_EN["cpt_vers_int"] = NULL;

        /* Création du compte nantie*/
        $id_cpte_en = creationCompte($CPT_EN);
        if($id_cpte_en == NULL){
					$message = sprintf(_("Erreur interne lors de la création du compte de garantie numéraire en cours: "), $numCompletCptCredit);
				}

        /* Ajout du compte des garanties encours dans le dossier de crédit */
        $DATA_DOSS = array();
        $DATA_DOSS['cpt_gar_encours'] = $id_cpte_en;
        updateCredit($id_doss, $DATA_DOSS);

        /* Insertion de la garantie numéraire à constituer dans la tables des garanties */
        $GAR_ENCOURS = array();
        $GAR_ENCOURS['type_gar'] = 1 ;
        $GAR_ENCOURS['id_doss'] = $id_doss;
        $GAR_ENCOURS['gar_num_id_cpte_prelev'] = NULL;
        $GAR_ENCOURS['gar_num_id_cpte_nantie'] = $id_cpte_en;
        $GAR_ENCOURS['etat_gar'] = 1; /* En cours de mobilisation */
        $GAR_ENCOURS['montant_vente'] = $val_doss["gar_remb"] ;
        $GAR_ENCOURS['devise_vente'] = $adsys_pr_cr[$id_prod]['devise'];
        $GAR_ENCOURS['id_ag'] = $global_id_agence;
        $sql = buildInsertQuery ("ad_gar", $GAR_ENCOURS);
        $result = $db->query($sql);
        if (DB::isError($result)) {
          $dbHandler->closeConnection(false);

          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

        /****** Ecriture comptable du remboursement de la garantie : contrepassation du mouvement de la reprise du bilan  *******/
        if ($SESSION_VARS["gar_remb"] > 0) {
        	 $libel_ecriture = new Trad();
          $libel_ecriture->set_traduction($global_langue_utilisateur,  _('Transfert de garantie - Reprises de crédit'));
          $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_gar"], $adsys_pr_cr[$id_prod]['devise']);
          $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
          $comptable[$compteur_mv]["montant"] = $val_doss["gar_remb"];
          $comptable[$compteur_mv]["libel"] = $libel_ecriture->save(); 
          $comptable[$compteur_mv]["type_operation"] = 220;
          $comptable[$compteur_mv]["id"] = $compteur_ecr;
          $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
          $compteur_mv++;

          $comptable[$compteur_mv]["compte"]=checkCptDeviseOK($array_credit["cpte_cpta_prod_cr_gar"],$adsys_pr_cr[$id_prod]['devise']);
          $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
          $comptable[$compteur_mv]["montant"] = $val_doss["gar_remb"];    
         
            
          $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();       
          $comptable[$compteur_mv]["id"] = $compteur_ecr;
          $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_en;
           $compteur_mv++;
          $compteur_ecr++;
        	} /* Fin if($SESSION_VARS["gar_remb"] > 0) */

      	} /* Fin if($gar_num_encours > 0) */
    	 } /* if ($type_radiation_credit == 'f') */

			}//fin de crédit en perte

      	if (!empty($comptable)) {
	    	/* Renseigner les infos communes des mouvements et vérifier si les données obligatoires sont saisis */
       		for ($i = 0 ; $i < $compteur_mv ; $i++) {
        		/* Renseignement des infos communes des mouvements */
        		$comptable[$i]["jou"] = 1;
        		$comptable[$i]["date_valeur"] = date("Y-m-d");
        		$comptable[$i]["exo"] = $exo;
        		$comptable[$i]["validation"] = 't';
        		$comptable[$i]["devise"] = $adsys_pr_cr[$id_prod]['devise'];
        		$comptable[$i]["date_comptable"] = date("r");

        		if ($comptable[$i]["compte"] == '')
         		 	$message = sprintf(_("Le compte de substitut des garanties ou du crédit n'est pas paramétré"));

        		if ($comptable[$i]["sens"] == '')
          			$message = sprintf(_("Le sens n'est pas renseigné"));

        		if ($comptable[$i]["montant"] == NULL)
          			$message = sprintf(_("Le montant n'est pas renseigné"));
      		}
      	}
    // Vérification de la devise
    $devise = $adsys_pr_cr[$id_prod]['devise'];
    if ($devise == '')
      $message = sprintf(_("La devise n'est pas paramétrée"));
  } // Fin parcours dossiers

  if ($exo == '')
    $message = sprintf(_("L'exercice n'est pas renseigné"));

  /* S'il y a des erreurs */
  if (strlen($message) != 0) {
    $dbHandler->closeConnection(false);
    $colb_tableau = '#e0e0ff';
    $msg = new HTML_message(_("Echec activation du dossier de credit"));
    $msg->setMessage($message);
    $msg->addButton(BUTTON_OK,2);
    $msg->buildHTML();
    echo $msg->HTML_code;
    exit();

  }

  $result = ajout_historique(503, NULL, NULL, 'administrateur', date("r"), $comptable);
  if ($result->errCode != NO_ERR) {
    /* Génération du message de confirmation */
    $dbHandler->closeConnection(false);
    $colb_tableau = '#e0e0ff';
    $msg = new HTML_message(_("Echec lors de l'activation du dossier de credit"));
    $msg->setMessage(_("Le dossier de crédit n'a pas été activé").".<BR>".$error[$result->errCode].$result->param);
  } else {
    /* Génération du message de confirmation */
    $dbHandler->closeConnection(true);
    $colb_tableau = '#e0e0ff';
    $msg = new HTML_message(_("Confirmation activation dossier de credit"));
    $msg->setMessage(_("Le dossier de crédit est activé. <BR>La prise en charge du crédit est à présent effective dans le logiciel"));
  }

  $msg->addButton(BUTTON_OK,1);
  $msg->buildHTML();
  echo $msg->HTML_code;
  $SESSION_VARS['ecran_precedent'] = 15; // suivi des écrans
}
// Ecran 16 : Présentation du dossier de credit
else if ($prochain_ecran == 16) {
  global $adsys;
  $colb_tableau = '#e0e0ff';
  $Myform = new HTML_GEN2(_("Etape 2: Présentation du dossier de credit"));

		  if ($SESSION_VARS['dossiers'][$index]['gs_cat'] != 2 ) { // dossier individuel
    // Les informations sur le dossier
    $id_doss = $SESSION_VARS['dossiers'][$index]['id_doss'];
    $id_prod = $SESSION_VARS['dossiers'][$index]['id_prod'];
    $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
    $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] = getListeGaranties($id_doss); // infos sur les garanties mobilisés
    // Infos dossiers fictifs dans le cas de GS avec dossier unique
    if ($SESSION_VARS['dossiers'][$index]['gs_cat'] == 1) {
      $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
      $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] = getCreditFictif($whereCond);
    }
  }
  elseif($SESSION_VARS['dossiers'][$index]['gs_cat'] == 2 ) { // GS avec dossiers multiples
    // id du dossier fictif : id du dossier du groupe
    $id_doss_fic = $SESSION_VARS['dossiers'][$index]['id'];

    // dossiers réels des membre du GS
    $dossiers_membre = getDossiersMultiplesGS($SESSION_VARS['infos_client']['id_client']);
    foreach($dossiers_membre as $id_doss=>$val) {
      if ($val['id_dcr_grp_sol']==$SESSION_VARS['dossiers'][$index]['id'] and $val['etat']==10) {
        $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
        $SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] = getListeGaranties($id_doss); // infos sur les garanties mobilisés
        $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
      }
    }
  }

  $SESSION_VARS['id_prod'] = $id_prod;

     // Liste des produits octroyables au client
  $SESSION_VARS['produits_credit'] = array(); // tableaux des produits
  $condition = "WHERE id = $id_prod";// Ne pas récupérer les produits destinés aux GS

  $PRODS = getProdInfo($condition);
  foreach($PRODS as $key=>$infos_prod) {
    $SESSION_VARS['produits_credit'][$infos_prod['id']] = $infos_prod; // tableaux des produits
  }

  // id du client choisi au premier écran
  $id_client = $SESSION_VARS['infos_client']['id_client'];

  // Client pouvant avoir un dossier de crédit réel
  $SESSION_VARS['clients_dcr'] = array(); // liste des clients pouvant avoir un dossier réel
  if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 1) // Si GS à dossier unique, seul le GS a un dossier réel
    $SESSION_VARS['clients_dcr'][$id_client] = $SESSION_VARS['liste_membres'][$id_client]." ".getClientName($id_client);
  elseif($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['gs_cat'] == 2) //GS avec doss multiples,chaque mbre a son dossier
  $SESSION_VARS['clients_dcr'] = $SESSION_VARS['liste_membres'];
  else // Crédit pour personne physique, Personne morale ou groupe informel : un seul dossier pour le client
    $SESSION_VARS['clients_dcr'][$id_client] = $SESSION_VARS['liste_membres'][$id_client];



  // Récupérations des utilisateurs. C'est à dire les agents gestionnaires
  $SESSION_VARS['utilisateurs'] = array();
  $utilisateurs = getUtilisateurs();
  foreach($utilisateurs as $id_uti=>$val_uti)
  $SESSION_VARS['utilisateurs'][$id_uti] = $val_uti['nom']." ".$val_uti['prenom'];


  // Affichage des champs
  $CPT_PRELEV_FRAIS  = array(); // compte sur les quels on peut ptrélever les frais de dossier
  $JS_check = ""; // Javascript de validation de la saisie
  $js_copie_mnt_dem = ""; // sauvegare de mnt_dem si le champ est désactivé

  // Contrôle des dates et des montants
  $js_date = "";
  // Contrôle des montants
  $js_mnt = "";

  // Pour chaque dossier reel
  foreach($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
    $nom_cli = getClientName($val_doss['id_client']);
	  $Myform->addHTMLExtraCode("espace".$id_doss,"<BR><b><P align=center><b> ".sprintf(_("Dossier N° %s de"),$id_doss)." $nom_cli</b></P>");
    $Myform->addField("id_doss".$id_doss, _("Numéro de dossier"), TYPC_TXT);
    $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_doss".$id_doss,FIELDP_DEFAULT,$val_doss['id_doss']);
    $Myform->addField("id_prod".$id_doss, _("Produit de crédit"), TYPC_LSB);
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_ADD_CHOICES, array("$id_prod"=>$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['libel']));
    $Myform->setFieldProperties("id_prod".$id_doss,FIELDP_DEFAULT, $id_prod);
    // Ajout de liens
    $Myform->addLink("id_prod".$id_doss, "produit".$id_doss,_("Détail produit"), "#");
    $Myform->setLinkProperties("produit".$id_doss,LINKP_JS_EVENT,array("onClick"=>"open_produit(".$id_prod.");"));
    $Myform->addField("periodicite".$id_doss, _("Périodicité"), TYPC_INT);
    $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_DEFAULT,$adsys["adsys_type_periodicite"][$SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['periodicite']]);
    $Myform->setFieldProperties("periodicite".$id_doss,FIELDP_IS_LABEL,true);

    $Myform->addField("obj_dem".$id_doss, _("Objet de la demande"), TYPC_TXT);
    $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['obj_dem']);


    $Myform->addField("detail_obj_dem".$id_doss, _("Détail objet demande"), TYPC_TXT);
    $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("detail_obj_dem".$id_doss,FIELDP_DEFAULT,$val_doss['detail_obj_dem']);

    $Myform->addField("date_dem".$id_doss, _("Date demande"), TYPC_DTE);
    $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_dem".$id_doss,FIELDP_DEFAULT,$val_doss['date_dem']);
    $Myform->addField("cre_date_debloc".$id_doss, _("Date de déblocage"), TYPC_DTE);
    $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("cre_date_debloc".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_debloc']);
    $Myform->addField("mnt_dem".$id_doss, _("Montant demandé"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_dem".$id_doss,FIELDP_DEFAULT, $val_doss['mnt_dem'],true);
    $Myform->addField("num_cre".$id_doss, _("Numéro de crédit"), TYPC_INT);
    $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("num_cre".$id_doss,FIELDP_DEFAULT,$val_doss['num_cre']);
    $Myform->addField("cpt_liaison".$id_doss, _("Compte de liaison"), TYPC_TXT);
    $Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_IS_LABEL,true);
    $cpt_lie = getAccountDatas($val_doss['cpt_liaison']);
    if($cpt_lie->errCode == 0)
    $Myform->setFieldProperties("cpt_liaison".$id_doss,FIELDP_DEFAULT,$cpt_lie["num_complet_cpte"]." ".$cpt_lie['intitule_compte']);
    $Myform->addField("id_agent_gest".$id_doss, _("Agent gestionnaire"), TYPC_LSB);
    $Myform->setFieldProperties("id_agent_gest".$id_doss, FIELDP_ADD_CHOICES, $SESSION_VARS['utilisateurs']);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("id_agent_gest".$id_doss,FIELDP_DEFAULT,$val_doss['id_agent_gest']);
    $Myform->addField("etat".$id_doss, _("Etat du dossier"), TYPC_TXT);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("etat".$id_doss,FIELDP_DEFAULT,$adsys["adsys_etat_dossier_credit"][$val_doss['etat']]);
    $Myform->addField("date_etat".$id_doss, _("Date état du dossier"), TYPC_DTE);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("date_etat".$id_doss,FIELDP_DEFAULT,$val_doss['date_etat']);
    $Myform->addField("cre_mnt_octr".$id_doss, _("Montant octroyé"), TYPC_MNT);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_DEFAULT,$val_doss['cre_mnt_octr']);
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnFocus"=>"reset($id_doss);"));
    $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_JS_EVENT,array("OnChange"=>"init($id_doss);"));
    $Myform->addHiddenType("mnt_octr".$id_doss, $val_doss['cre_mnt_octr']);

 if ($SESSION_VARS['infos_doss'][$id_doss]["gs_cat"]==1)
      $Myform->setFieldProperties("cre_mnt_octr".$id_doss,FIELDP_IS_LABEL,true);
    //type de durée : en mois ou en semaine
    $type_duree = $SESSION_VARS['infos_prod']['type_duree_credit'];
    $libelle_duree = mb_strtolower(adb_gettext($adsys['adsys_type_duree_credit'][$type_duree])); // libellé type durée en minuscules

    $Myform->addField("duree_mois".$id_doss, _("Durée en ".$libelle_duree), TYPC_INT);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("duree_mois".$id_doss,FIELDP_DEFAULT,$val_doss['duree_mois']);
    $Myform->addField("cre_date_approb".$id_doss, _("Date approbation"), TYPC_DTE);
    $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_REQUIRED,true);
    $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_IS_LABEL, false);
    $Myform->setFieldProperties("cre_date_approb".$id_doss,FIELDP_DEFAULT,$val_doss['cre_date_approb']);


    $Myform->addField("differe_jours".$id_doss, _("Différé en jours"), TYPC_INN);
    $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_IS_LABEL,false);
    $Myform->setFieldProperties("differe_jours".$id_doss,FIELDP_DEFAULT,$val_doss['differe_jours']);
    $Myform->addField("differe_ech".$id_doss, _("Différé en échéance"), TYPC_INT);
    $Myform->setFieldProperties("differe_ech".$id_doss,FIELDP_DEFAULT,$val_doss['differe_ech']);
    $Myform->addField("delai_grac".$id_doss, _("Délai de grace"), TYPC_INT);
    $Myform->setFieldProperties("delai_grac".$id_doss,FIELDP_DEFAULT,$val_doss['delai_grac']);
    $Myform->addField("mnt_commission".$id_doss, _("Montant commission"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_commission".$id_doss,FIELDP_DEFAULT,$val_doss['mnt_commission']);



    $Myform->addField("mnt_assurance".$id_doss, _("Montant assurance"), TYPC_MNT);
    $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("mnt_assurance".$id_doss,FIELDP_DEFAULT,$val_doss["mnt_dem"]*$SESSION_VARS['infos_prod']['prc_assurance']);

    $Myform->addField("prelev_auto".$id_doss, _("Prélèvement automatique"), TYPC_BOL);
    $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_IS_LABEL,true);
    $Myform->setFieldProperties("prelev_auto".$id_doss,FIELDP_DEFAULT,$val_doss['prelev_auto']);

    $Myform->addField("cpt_prelev_frais".$id_doss, _("Compte de prélévement des frais"), TYPC_TXT);
    $Myform->setFieldProperties("cpt_prelev_frais".$id_doss,FIELDP_IS_LABEL,true);
    $cpt_frais = getAccountDatas($val_doss['cpt_prelev_frais']);
    if($cpt_frais->errCode == 0)
    $Myform->setFieldProperties("cpt_prelev_frais".$id_doss,FIELDP_DEFAULT,$cpt_frais["num_complet_cpte"]." ".$cpt_frais['intitule_compte']);

		$SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] = 0; // garanties numéraires totales mobilisées
    $SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] = 0; // garanties matérilles totales mobilisées

      if (is_array($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR']))
        foreach($SESSION_VARS['infos_doss'][$id_doss]['DATA_GAR'] as $key=>$value ) {
        if ($value['type_gar'] == 1){
          $SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob'] += recupMontant($value['montant_vente']);
          $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['descr_ou_compte'] = $value['gar_num_id_cpte_prelev'];
        }

        elseif($value['type_gar'] == 2){
        	$SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob'] += recupMontant($value['montant_vente']);

        	$id_bien = $value['gar_mat_id_bien'];
          $infos_bien = getInfosBien($id_bien);
          $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['descr_ou_compte'] = $infos_bien['description'];
          $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['type_bien'] = $infos_bien['type_bien'];
          $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['piece_just'] = $infos_bien['piece_just'];
          $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['num_client'] = $infos_bien['id_client'];
          $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['remarq'] = $infos_bien['remarque'];
          $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['gar_mat_id_bien'] = $id_bien;
        }
      }
      if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_tot'] > 0) {
			$mnt_octr = arrondiMonnaie($val_doss['cre_mnt_octr'],0); // montant octroyé
			$gar_num_att = round(recupMontant($mnt_octr) * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'], $global_monnaie_courante_prec);
			$Myform->addField("gar_num".$id_doss, _("Garantie numéraire attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num".$id_doss, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("gar_num".$id_doss,FIELDP_DEFAULT, $gar_num_att);
      $Myform->addField("gar_num_mob".$id_doss, _("Garantie numéraire mobilisée"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num_mob".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['gar_num_mob']);

			$gar_mat_att = round(recupMontant($mnt_octr) * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_mat'], $global_monnaie_courante_prec);
			$Myform->addField("gar_mat".$id_doss, _("Garantie matérielle attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat".$id_doss, FIELDP_IS_LABEL, true);
      $Myform->setFieldProperties("gar_mat".$id_doss,FIELDP_DEFAULT, $gar_mat_att);
      $Myform->addField("gar_mat_mob".$id_doss, _("Garantie matérielle mobilisée"), TYPC_MNT);
      $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_mat_mob".$id_doss,FIELDP_DEFAULT,$SESSION_VARS['infos_doss'][$id_doss]['gar_mat_mob']);

			$gar_tot_att = round(recupMontant($mnt_octr) * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_tot'], $global_monnaie_courante_prec);
      $Myform->addField("gar_tot".$id_doss, _("Garantie totale attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_tot".$id_doss,FIELDP_DEFAULT,$gar_tot_att);
    }

    if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_encours']>0) {
			$mnt_octr = arrondiMonnaie($val_doss['cre_mnt_octr'],0); // montant octroyé
			$gar_num_encours = round(recupMontant($mnt_octr) * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_encours'], $global_monnaie_courante_prec);
      $Myform->addField("gar_num_encours".$id_doss, _("Garantie encours attendue"), TYPC_MNT);
      $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_IS_LABEL,true);
      $Myform->setFieldProperties("gar_num_encours".$id_doss,FIELDP_DEFAULT,$gar_num_encours);
    }

		// Afficahge des dossiers fictifs dans le cas d'un GS avec dossier réel unique
    if ($SESSION_VARS['infos_doss'][$id_doss]['gs_cat'] == 1) {
      $js_mnt_octr = "function calculeMontant() {"; // function de calcule du montant octroyé
      $js_mnt_octr .= "var tot_mnt_octr = 0;\n";

      foreach($SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] as $id_fic=>$val_fic) {
        $Myform->addHTMLExtraCode("espace_fic".$id_fic,"<BR>");
        $Myform->addField("membre".$id_fic, _("Membre"), TYPC_TXT);
        $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("membre".$id_fic, FIELDP_IS_LABEL, true);
        $Myform->setFieldProperties("membre".$id_fic,FIELDP_DEFAULT,$val_fic['id_membre']." ".getClientName($val_fic['id_membre']));
        $Myform->addField("obj_dem_fic".$id_fic, _("Objet demande"), TYPC_TXT);
        $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("obj_dem_fic".$id_fic, FIELDP_DEFAULT, $val_fic['obj_dem']);
        $Myform->addField("detail_obj_dem_fic".$id_fic, _("Détail demande"), TYPC_TXT);
        $Myform->setFieldProperties("detail_obj_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("detail_obj_dem_fic".$id_fic, FIELDP_DEFAULT, $val_fic['detail_obj_dem']);
        $Myform->addField("mnt_dem_fic".$id_fic, _("Montant demande"), TYPC_MNT);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic, FIELDP_IS_REQUIRED, true);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_DEFAULT,$val_fic['mnt_dem'],true);
        $Myform->setFieldProperties("mnt_dem_fic".$id_fic,FIELDP_JS_EVENT,array("OnChange"=>"calculeMontant();"));

        $js_mnt_octr .= "tot_mnt_octr = tot_mnt_octr + recupMontant(document.ADForm.mnt_dem_fic".$id_fic.".value);\n";
      }
      $js_mnt_octr .= "document.ADForm.cre_mnt_octr".$id_doss.".value = formateMontant(tot_mnt_octr);\n";
      $js_mnt_octr .= "document.ADForm.mnt_octr".$id_doss.".value = formateMontant(tot_mnt_octr);\n";
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num"] > 0) {
        $js_mnt_octr .="\n\tdocument.ADForm.gar_num".$id_doss.".value =formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_num']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_mat"] > 0) {
        $js_mnt_octr .="\n\tdocument.ADForm.gar_mat".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_mat']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_tot"] > 0) {
        $js_mnt_octr .="\n\tdocument.ADForm.gar_tot".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_tot']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
      }
      if ($SESSION_VARS['infos_doss'][$id_doss]["gar_num_encours"] > 0) {
        $js_mnt_octr .="\n\tdocument.ADForm.gar_num_encours".$id_doss.".value = formateMontant(Math.round(".$SESSION_VARS['infos_prod']['prc_gar_encours']."*parseFloat(tot_mnt_octr))*".$precision_devise.")/".$precision_devise.";";
      }
      $js_mnt_octr .= "}\n";
    }
    // Evenement JavaScript des champs


      // Vérifier que le montant demandé est correct
      $field = "document.ADForm.mnt_dem".$id_doss;
      $JS_check .= " if (!parseFloat(recupMontant($field.value)))
                 {
                 $field.value = '';
                 msg += '".sprintf(_("Le montant demandé par le dossier %s doit être correctement renseigné"),$id_doss)."\\n';
                 ADFormValid=false;
               }";
      // Vérifier que le montant demandé est entre le max et le min
      $min = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['mnt_min'];
      $max = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['mnt_max'];
      if ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['mnt_max'] > 0)
        $JS_check .= "if ((parseFloat(recupMontant($field.value)) < parseFloat(".$min.")) || (parseFloat(recupMontant($field.value)) > parseFloat(".$max.")))
                   {
                   $field.value = '';
                   msg += '- ".sprintf(_("Le montant demandé par le dossier %s doit être compris entre %s et %s comme défini dans le produit"),$id_doss, afficheMontant($min), afficheMontant($max))."\\n';
                   ADFormValid=false;
                 }";
      else
        $JS_check .= "if (parseFloat(recupMontant($field.value)) < parseFloat(".$min."))
                   {
                   $field.value = '';
                   msg += '- ".sprintf(_("Le montant demandé par le dossier %s doit être au moins égal à %s comme défini dans le produit"),$id_doss,afficheMontant($min))." \\n'
                   ADFormValid=false;
                 }";

    // Vérifier que la durée est comprise entre le max et le min
    $duree_max = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['duree_max_mois'];
    $duree_min = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['duree_min_mois'];
    if ($duree_max > 0)
      $JS_check .= "if ((parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$duree_min.")) || (parseInt(document.ADForm.duree_mois".$id_doss.".value) > parseInt(".$duree_max.")))
                   {
                   msg+=' - ".sprintf(_("La durée du crédit pour le dossier %s doit être comprise entre %s et %s comme définie dans le produit"),$id_doss,$duree_min,$duree_max).".\\n';
                   ADFormValid=false;
                 }";
    else
      $JS_check .= "if (parseInt(document.ADForm.duree_mois".$id_doss.".value) < parseInt(".$duree_min."))
                   {
                   msg+=' - ".sprintf(_("La durée du crédit pour le dossier %s doit être au moins égale à %s comme définie dans le produit"),$id_doss,$duree_min)."\\n';
                   ADFormValid=false;
                 }";

      // Test de la durée demandée à faire uniquement si le nombre de mois de la période est > 1
    $periodicite = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['periodicite'];

    if ($adsys["adsys_duree_periodicite"][$periodicite] > 1) {
      $JS_check .= "
                   if(parseInt(document.ADForm.duree_mois".$id_doss.".value) % parseInt(".$adsys["adsys_duree_periodicite"][$periodicite].")!=0)
                 {
                   msg +='- ".sprintf(_("La durée pour le dossier %s doit être multiple de %s"),$id_doss,$adsys["adsys_duree_periodicite"][$periodicite])."';
                   ADFormValid = false;
                 }";
    }


  } // fin parcours des dossiers


  $Myform->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $Myform->addFormButton(1,2,"annuler",_("Annuler"),TYPB_SUBMIT);

  if(($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_mat"] > 0) || ($SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_num"]) > 0)
  	$Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 17);
  else
  	$Myform->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 18);
  $Myform->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $Myform->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);

  // JS des boutons
  $Myform->setFormButtonProperties("valider", BUTP_JS_EVENT, array("onclick" => $js_copie_mnt_dem));
  $Myform->addJS(JSP_BEGIN_CHECK, "jsdate", $js_date);
  $Myform->addJS(JSP_BEGIN_CHECK, "jsmnt", $js_mnt);

	 // Ajout des codes javascript
  $Myform->addJS(JSP_BEGIN_CHECK,"test",$JS_check);

  $SESSION_VARS['ecran_precedent'] = 16; // suivi des écrans

  $Myform->buildHTML();
  echo $Myform->getHTML();
}
// Ecran 17 : Présentation des garanties
else if ($prochain_ecran == 17) {
  global $adsys;

		 if ($SESSION_VARS['ecran_precedent'] == 16){
			//Recuperation des informations modifiées sur le dossier de crédit
			foreach ($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
  	  $SESSION_VARS['infos_doss'][$id_doss]['id_agent_gest'] = $ {'id_agent_gest'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = recupMontant($ {'cre_mnt_octr'.$id_doss});
			$SESSION_VARS['infos_doss'][$id_doss]['duree_mois'] = $ {'duree_mois'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = $ {'cre_date_approb'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['differe_jours'] = $ {'differe_jours'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['differe_ech'] = $ {'differe_ech'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['delai_grac'] = $ {'delai_grac'.$id_doss};
			}// fin parcours dossiers
   }
	  $SESSION_VARS["is_gar_mob"] = true;

	  $devise_prod = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise'];


	  $colb_tableau = '#e0e0ff';
	  $myForm = new HTML_GEN2(_("Mobilisation des garanties"));
	 	// Modification du paramétrage du produit de credit

	  $xtHTML = "";

	  // Creation d'un tableau pour les garanties numéraires
	  if ( $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_num"] > 0) {
	    $xtHTML .= "<h1 align=\"center\">"._("Garanties numéraires")."</h1>\n";
	    $xtHTML .= "<TABLE align=\"center\">";
	    $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
	    $xtHTML .= "<td><b>"._("Type de garantie")."</b></td>";
	    $xtHTML .= "<td><b>"._("Bénéficiaire")."</b></td>";
	    $xtHTML .= "<td><b>"._("Montant")." *</b></td>";
	    $xtHTML .= "<td><b>"._("Compte de prélèvement")." *</b></td>";
	    $xtHTML .= "</tr>";
	    // Une garanties numéraire par client
	    foreach ($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {

	    	$id_cli = $val_doss["id_client"];
	      $nom_benef = getClientName($id_cli);
	      $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"type_gar_num".$id_cli."\" value=\"".adb_gettext($adsys["adsys_type_garantie"][1])."\" disabled=true></td>\n";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"benef".$id_cli."\" value=\"".$nom_benef."\" disabled=true></td>\n";

	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"mnt_gar_num".$id_cli."\" value=\"".$val_doss['gar_num_mob']."\" size=12 onchange=\"value=formateMontant(value);\"></td>\n";
	      if ($SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['descr_ou_compte'] != '')
	        $CPT_PRELEV_GAR = getAccountDatas($SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['descr_ou_compte']);

	      $xtHTML .= "<TD><INPUT TYPE=\"text\" NAME=\"cpte_client".$id_cli."\" size=32 value=\"".$CPT_PRELEV_GAR["num_complet_cpte"]."\" disabled=true>\n";

	      $xtHTML .= "<FONT size=\"2\"><A href=# onclick =\"open_compte('cpte_client$id_cli','id_compte$id_cli');return false;\">"._("Recherche")."</A></FONT></TD>\n";

	      $xtHTML .="<INPUT TYPE=\"hidden\" NAME=\"id_compte$id_cli\" value=\"".$SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['descr_ou_compte']."\">\n";
	    }
	    $xtHTML .= "</table><br>";

	    //JavaScript
	    $js_cpt_prelev = "<SCRIPT type=\"text/javascript\">\n";

	    //function  getCompte : ouvre une fenêtre de recherche de compte de client
	    $js_cpt_prelev .= "function open_compte(cpte_cli,id_compte)
	                    {
	                      url = '".$http_prefix."/modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&choixCompte=1&cpt_dest='+cpte_cli+'&id_cpt_dest='+id_compte;garant = OpenBrwXY(url, 'Compte de prélèvement', 400, 500);
	                    }";

	    $js_cpt_prelev .= "</SCRIPT>\n";
	  }  /* Fin if if( $prc_gar_num > 0) */


	  /* Creation d'un tableau pour les garanties matérielles */
	  if ( $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["prc_gar_mat"] > 0) {
	    $xtHTML .= "<h1 align=\"center\">"._("Garanties matérielles")."</h1>\n";
	    $xtHTML .= "<TABLE align=\"center\">";
	    $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
	    $xtHTML .= "<td><b>"._("Type de garantie")."</b></td>";
	    $xtHTML .= "<td><b>"._("Description")." *</b></td>";
	    $xtHTML .= "<td><b>"._("Type de bien")." *</b></td>";
	    $xtHTML .= "<td><b>"._("Bénéficiaire")."</b></td>";
	    $xtHTML .= "<td><b>"._("Valeur")." *</b></td>";
	    $xtHTML .= "<td><b>"._("Pièce justificative")."</b></td>";
	    $xtHTML .= "<td><b>"._("Remarque")."</b></td>";
	    $xtHTML .= "<td><b>"._("Client garant")." *</b></td>";
	    $xtHTML .= "</tr>";

	   foreach ($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
	    	$id_cli = $val_doss["id_client"];
	      $nom_benef = getClientName($id_cli);
	      $xtHTML .= "\n<tr bgcolor=\"$colb_tableau\">";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"type_gar_mat".$id_cli."\" size=12 disabled=true value=\"".adb_gettext($adsys["adsys_type_garantie"][2])."\"></td>\n";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"desc_gar_mat".$id_cli."\" size=20 value=\"".$SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['descr_ou_compte']."\"></td>\n";
	      $xtHTML .= "<td><SELECT NAME=\"type_gar_mat".$id_cli."\">";
	      $xtHTML .= "<option value=\"0\">[Aucun]</option>\n";
	      $types_biens = getTypesBiens();
	      foreach($types_biens as $key=>$value) {
	        if ($SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['type_bien'] == $key)
	          $xtHTML .= "<option value=$key selected>".$key." ".$value."</option>\n";
	        else
	          $xtHTML .= "<option value=$key>".$key." ".$value."</option>\n";
	      }
	      $xtHTML .= "</SELECT>\n";
	      $xtHTML .= "</td>";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"benef".$id_cli."\" value=\"".$nom_benef."\" disabled=true></td>\n";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"mnt_gar_mat".$id_cli."\" onchange=\"value=formateMontant(value);\" size=12 value=\"".$val_doss['gar_mat_mob']."\"></td>\n";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"piece_gar_mat".$id_cli."\" size=15 value=\"".$SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['piece_just']."\"></td>\n";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"remarq_gar_mat".$id_cli."\" size=15 value=\"".$SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['remarq']."\"></td>\n";
	      $xtHTML .= "<td><INPUT TYPE=\"text\" NAME=\"num_client".$id_cli."\" disabled=true size=10 value=\"".$SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['num_client']."\">\n";
	      $xtHTML .= "<FONT size=\"2\"><A href=# onclick=\"OpenBrw('../../modules/clients/rech_client.php?m_agc=".$_REQUEST['m_agc']."&field_name=num_client$id_cli&num_client_dest=num_client_rel$id_cli', '"._("Recherche")."');return false;\">Recherche</A></FONT></TD>\n";
	      $xtHTML .= "<INPUT TYPE=\"HIDDEN\" NAME=\"num_client_rel$id_cli\" value=\"".$SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['num_client']."\">\n";
	      $xtHTML .= "</tr>";
	    }
	    $xtHTML .= "</table><br><br>";
	  } /* Fin if( $prc_gar_mat > 0) */

	  $myForm->addHTMLExtraCode ("infos_cli", $xtHTML);

	  $myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
	  $myForm->addFormButton(1,2,"retour",_("Retour"),TYPB_SUBMIT);
	  $myForm->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);

	  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 18);
	  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
	  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
	  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
	  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 2);

  $SESSION_VARS['ecran_precedent'] = 17; // suivi des écrans
  $myForm->addHTMLExtraCode ("cpt_prelev", $js_cpt_prelev);
  $myForm->buildHTML();
  echo $myForm->getHTML();

}
// Ecran 18 : Présentation de l'échéancier
else if ($prochain_ecran == 18) {
  global $adsys;

   if ($SESSION_VARS['ecran_precedent'] == 16){
			//Recuperation des informations modifiées sur le dossier de crédit
			foreach ($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
  	  $SESSION_VARS['infos_doss'][$id_doss]['id_agent_gest'] = $ {'id_agent_gest'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'] = recupMontant($ {'cre_mnt_octr'.$id_doss});
			$SESSION_VARS['infos_doss'][$id_doss]['duree_mois'] = $ {'duree_mois'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'] = $ {'cre_date_approb'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['differe_jours'] = $ {'differe_jours'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['differe_ech'] = $ {'differe_ech'.$id_doss};
			$SESSION_VARS['infos_doss'][$id_doss]['delai_grac'] = $ {'delai_grac'.$id_doss};
			}// fin parcours dossiers
   }
   // Récupération des garanties mobilisées, si on vient de l'écran 17

  if ($SESSION_VARS['ecran_precedent'] == 17) {
    $msg = "";
    // Récupération des garanties numéraires mobilisées par chaque client
    foreach ($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
  	  $id_cli = $val_doss["id_client"];

	    // Récupération des garanties numéraires mobilisées par chaque client
      // Garantie numéraire : le montant et le compte de prélèvement doivent être saisis ou non à la fois
      if (($ {'mnt_gar_num'.$id_cli} != '') and ($ {'id_compte'.$id_cli} !='')) {
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['benef'] = $id_cli;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['type'] = 1 ;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['descr_ou_compte'] = ${'id_compte'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['id_gar'] = NULL ;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['type_bien'] = NULL;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['num_client'] = NULL;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['piece_just'] = NULL;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['remarq'] = NULL;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['etat'] = 3; // Mobilisée
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['valeur'] = recupMontant($ {'mnt_gar_num'.$id_cli});
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['devise_vente'] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise'];

        // Vérifier que les garanties mobilisée sont suffisantes
        $gar_num_attendue = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'];

        if ($gar_num_attendue > $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']['valeur'])
          $msg = _("La garantie numéraire mobilisée est insuffisante");
      }elseif($ {'mnt_gar_num'.$id_cli} == ''){
      	$msg = _("Le champs montant de la garantie numéraire du client ".$id_cli." n'est pas renseigné");
      }else{
      	$msg = _("Le champs compte de prélèvement de la garantie du client ".$id_cli." n'est pas renseigné");
      }

      // Récupération des garanties matérielles mobilisées
      // Les champs obligatoires doivent être renseignés ou non à la fois
      if ($ {'mnt_gar_mat'.$id_cli} != '' and $ {'desc_gar_mat'.$id_cli} != '' and $ {'type_gar_mat'.$id_cli} != 0 and $ {'num_client_rel'.$id_cli} != '') {
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['benef'] = $id_cli;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['type'] = 2 ;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['id_gar'] = NULL ;
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['descr_ou_compte'] = $ {'desc_gar_mat'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['num_client'] = $ {'num_client_rel'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['type_bien'] = $ {'type_gar_mat'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['piece_just'] = $ {'piece_gar_mat'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['remarq'] = $ {'remarq_gar_mat'.$id_cli};
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['etat'] = 3; // Mobilisée
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['valeur'] = recupMontant($ {'mnt_gar_mat'.$id_cli});
        $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['devise_vente'] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['devise'];
        // Vérifier que les garanties mobilisée sont suffisantes
        $gar_mat_attendue = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_mat'];

        if ($gar_mat_attendue > $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']['valeur'])
          $msg = _("La garantie matérielle mobilisée est insuffisante");
      }
      elseif($ {'mnt_gar_mat'.$id_cli} =='' and ($ {'desc_gar_mat'.$id_cli} != '' or $ {'type_gar_mat'.$id_cli} != 0 or $ {'num_client_rel'.$id_cli} != ''))
      $msg = "Le champ montant du client ".$id_cli." des garanties matérielles n'est pas renseigné ";
      elseif($ {'desc_gar_mat'.$id_cli} == '' and ($ {'mnt_gar_mat'.$id_cli} != '' or $ {'type_gar_mat'.$id_cli} != 0 or $ {'num_client_rel'.$id_cli} != ''))
      $msg = "Le champ description du client ".$id_cli." des garanties matérielles n'est pas renseigné ";
      elseif($ {'type_gar_mat'.$id_cli} == 0 and ($ {'mnt_gar_mat'.$id_cli} != '' or $ {'desc_gar_mat'.$id_cli} != '' or $ {'num_client_rel'.$id_cli} != ''))
      $msg = "Le champ type de bien du client ".$id_cli." des garanties matérielles n'est pas renseigné ";
      elseif($ {'num_client_rel'.$id_cli} == '' and ($ {'mnt_gar_mat'.$id_cli} != '' or $ {'desc_gar_mat'.$id_cli} != '' or $ {'type_gar_mat'.$id_cli} != 0))
      $msg = "Le champ client garant du client ".$id_cli." des garanties matérielles n'est pas renseigné ";
    } // fin parcours clients

    // Vérifier que tous les champs obligatoires ont été saisis
    if ($msg != "") {
      $colb_tableau = '#e0e0ff';
      $MyPage = new HTML_erreur(_("Erreur dans la mobilisation des garanties "));
      $MyPage->setMessage($msg);
      $MyPage->addButton(BUTTON_OK, "17");
      $MyPage->buildHTML();
      echo $MyPage->HTML_code;
      exit();
    }
  } // Fin si on vient de l'écran de mobilisation

	/**********************  Génération de l'échéancier. L'utilisateur peut le modifier ***********/
  $myForm = new HTML_GEN2(_("Etape 4: Génération de l'échéancier"));

  // Génération des échéanciers
  $HTML_code_echeancier = '';
  foreach ($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
  	$id_cli = $val_doss["id_client"];

    $ECH_THEO = calcul_echeancier_theorique($val_doss["id_prod"], $val_doss["cre_mnt_octr"], $val_doss["duree_mois"], $val_doss["differe_jours"], $val_doss["differe_ech"], $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["periodicite"]);




    // Construction du tableau paramètre
    $param["index"] = 0;
    $param["periodicite"] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["periodicite"];
    $param["mode_calc_int"] = $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]["mode_calc_int"];
    $param["nbre_jour_mois"] = 30; // FIXME Backward compatibility. Cfr fonction completeEcheancier
    $param["differe_jours"] = $val_doss["differe_jours"];
    $param["date"] = pg2phpDate($val_doss["cre_date_debloc"]);
    $param["id_doss"] = $id_doss;
    $param["duree"] = $val_doss["duree_mois"];

    $ECH_REEL = completeEcheancierLibre($ECH_THEO, $param);

    // l'échéance en cours, à la date de reprise du bilan
    $ech_cours = getEcheanceCourante($ECH_REEL,$SESSION_VARS['date_reprise_credit']);
    /* Modification :
     - du format de la date de l'échéance pour la fonction HTML_echeancier_remboursement
     - Calcul au prorata des intérêts des échéances pour le dégressif variable
    */

    foreach($ECH_REEL as $cle=>$val) {
      $ECH_REEL[$cle]["date_ech"] = php2pg($val["date_ech"]);
      /* Dégressif variable, les intérêts à venir seront comptabilisés dynamiquement au jour le jour */
      if ($param["mode_calc_int"] == 3 ) {
        if ( ($ech_cours != NULL) and ($cle > $ech_cours))
          $ECH_REEL[$cle]["solde_int"] = 0;
        elseif($cle == $ech_cours) {
          $date_ech_courante = $val["date_ech"];
          if ($ech_cours == 1) // première echéance
            $date_prec_ech_courante = $SESSION_VARS["cre_date_debloc"];
          else { // Cas Général
            $prec_ech_courante = $ech_cours - 1;
            $date_prec_ech_courante = pg2phpDate($ECH_REEL[$prec_ech_courante]["date_ech"]);
          }

          // calcul ratio nbre de jours écoulés depuis cette échéance sur nombre de jours entre cette échéance et l'échéance active
          $nominateur = nbreDiffJours($date_prec_ech_courante, pg2phpDate($SESSION_VARS['date_reprise_credit']));
          $denominateur = nbreDiffJours($date_ech_courante, $date_prec_ech_courante);

          $ratio = (($nominateur*1.0) / ($denominateur*1.0));

          // Calcul de l'intérêt réel à prendre en compte
          $ECH_REEL[$cle]["solde_int"] = arrondiMonnaie($ECH_REEL[$ech_cours]["solde_int"] * $ratio, 1);
        }
      }
    }

    $SESSION_VARS['infos_doss'][$id_doss]["ECH"] = $ECH_REEL;

    $PROD = getProdInfo(" where id =".$SESSION_VARS["id_prod"]);
    $libel_prod = $PROD[0]["libel"];

    /* Construction du code HTML de l'échéancier: préparation des paramètres à donner à HTML_echeancier_remboursement */
    $parametre["lib_date"] = _("Date de déblocage du crédit ");
    $parametre["titre"] = _("Echéancier de remboursement à modifier pour ").getClientName($id_cli);
    $parametre["mnt_reech"] = '0';
    $parametre["date"] = $val_doss["cre_date_debloc"];
    $parametre["id_client"] = $id_cli;
    $parametre["id_doss"] = $id_doss;

    $champs_modifiables["int"] = true;
    //$champs_modifiables["pen"] = true;
    $champs_modifiables["cap"] = true;
    $champs_modifiables["gar"] = true;
    $champs_modifiables["date_ech"] = true;

    /* Les informations sur le dossier de crédit sélectionné */
    $DOSSIER = array();
    $DOSSIER['differe_jours'] = $val_doss["differe_jours"];
    $DOSSIER['differe_ech'] = $val_doss["differe_ech"];
    $DOSSIER['cre_mnt_octr'] = $val_doss["cre_mnt_octr"];
    $DOSSIER['duree_mois'] = $val_doss["duree_mois"];
    $DOSSIER['gar_num'] = $val_doss["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'];
    $DOSSIER['gar_mat'] = $val_doss["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_mat'];
    $DOSSIER['gar_tot'] = $val_doss["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_tot'];

    $DOSSIER['gar_num_encours'] = $val_doss["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_encours'];


    /* Génération du code HTML de l'échéancier : HTML_echeancier_remboursement présente les soldes.
    Ce que ne pose pas de problème les montant cap, int , pen et gar sont égaux aux soldes cap, int pen et gar à cfe niveau
    */
    global $adsys;
    $HTML_code_echeancier .= HTML_echeancier_remboursement($parametre, $ECH_REEL, $champs_modifiables, $DOSSIER, $PROD );
    // Vérifications javascript :
    //  - Les dates se suivent dans l'ordre chronologique
    //  - La somme des montants en capital est égale au montant octroyé
    $checkDates = "";
    $checkCap = "";
    reset($ECH_REEL);
    while (list($key, $ech) = each($ECH_REEL)) {
      if ($key > 1) // On fait ces vérifications à partir de la seconde échéance
        $checkDates .= "\nif (!isBefore(document.ADForm.date".($key-1).".value, document.ADForm.date$key.value)) {msg += '- La date de l\\'échéance $key doit être postérieure à la date de l\\'échéance ".($key-1)."\\n';ADFormValid=false;}\n";
      $computeSumCap .= "if (document.ADForm.mnt_cap$key.value == '') total_cap = total_cap + 0; else total_cap = total_cap + parseInt(recupMontant(document.ADForm.mnt_cap$key.value));\n";  // Si Montant rensiengé est vide
    }

    $checkSumCap = "if (total_cap != ".( $val_doss["cre_mnt_octr"]).") {msg += '- ".sprintf(_("Le montant du capital à rembourser doit être égal à %s et est pour le moment égal à"),afficheMontant($SESSION_VARS["cre_mnt_octr"], true))." '+recupMontant(total_cap)+'\\n';ADFormValid = false;}";

    $myForm->addJS(JSP_BEGIN_CHECK, "init".$id_cli, "total_cap = 0;\n");
    $myForm->addJS(JSP_BEGIN_CHECK, "checkDates".$id_cli, $checkDates);
    $myForm->addJS(JSP_BEGIN_CHECK, "computeSumCap".$id_cli, $computeSumCap);
    $myForm->addJS(JSP_BEGIN_CHECK, "checkSumCap".$id_cli, $checkSumCap);

    // Sauvegarde du nombre d'échéances
    $SESSION_VARS['infos_doss'][$id_doss]['nbre_ech'] = sizeof($ECH_REEL);
    $myForm->addHiddenType("nbre_ech".$id_cli, $SESSION_VARS['infos_doss'][$id_doss]['nbre_ech']);

  } // fin parcours clients

	$myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"retour",_("Retour"),TYPB_SUBMIT);
  $myForm->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  if( $SESSION_VARS['ecran_precedent'] == 17)
  	$myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 17);
  else
  	$myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 2);


  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 19);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);

  $SESSION_VARS['ecran_precedent'] = 18; // suivi des écrans
  $myForm->addHTMLExtraCode("echeancier", $HTML_code_echeancier);
  $myForm->buildHTML();
  echo $myForm->getHtml();


}

// Ecran 19 : Confirmation de l'échéancier
else if ($prochain_ecran == 19) {

  $SESSION_VARS["is_gar_mob"] = false;

  $myForm = new HTML_GEN2(_("Etape 5: Confirmation de l'échéancier"));
  $HTML_code_echeancier = '';
  // Parcours des dossiers des clients
  foreach ($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {

		$id_cli = $val_doss["id_client"];
    $PROD = getProdInfo(" where id =".$SESSION_VARS["id_prod"]);
    $libel_prod = $PROD[0]["libel"];

    global $adsys;
    $colb_tableau = '#e0e0ff';
    $colb_tableau_altern = '#ffd5d5';

    // Construction du code HTML de l'échéancier: préparation des paramètres à donner à HTML_echeancier_remboursement */
    $parametre["lib_date"] = _("Date de déblocage du crédit ");
    $parametre["titre"] = _("Echéancier de remboursement à modifier pour ").getClientName($id_cli);
    $parametre["mnt_reech"] = '0';
    $parametre["date"] = $SESSION_VARS['infos_doss'][$id_doss]["cre_date_debloc"];

    // Pas de champs modifiable
    $champs_modifiables = array();

    // Les informations sur le dossier de crédit sélectionné
    $DOSSIER = array();
    $DOSSIER['differe_jours'] = $SESSION_VARS['infos_doss'][$id_doss]["differe_jours"];
    $DOSSIER['differe_ech'] = $SESSION_VARS['infos_doss'][$id_doss]["differe_ech"];
    $DOSSIER['cre_mnt_octr'] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"];
    $DOSSIER['duree_mois'] = $SESSION_VARS['infos_doss'][$id_doss]["duree_mois"];
    $DOSSIER['gar_num'] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'];
    $DOSSIER['gar_mat'] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_mat'];
    $DOSSIER['gar_tot'] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_tot'];
    $DOSSIER['gar_num_encours'] = $SESSION_VARS['infos_doss'][$id_doss]["cre_mnt_octr"] * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_encours'];

    // Génération du code HTML de l'échéancier
    $HTML_code_echeancier .= HTML_echeancier_remboursement($parametre, $SESSION_VARS['infos_doss'][$id_doss]["ECH"], $champs_modifiables, $DOSSIER, $PROD);
  } // fin parcours des clients

  // Boutons du formulaire
  $myForm->addFormButton(1,1,"valider", _("Valider"), TYPB_SUBMIT);
  $myForm->addFormButton(1,2,"retour",_("Retour"),TYPB_SUBMIT);
  $myForm->addFormButton(1,3,"annuler",_("Annuler"),TYPB_SUBMIT);
  $myForm->setFormButtonProperties("annuler", BUTP_PROCHAIN_ECRAN, 2);
  $myForm->setFormButtonProperties("retour", BUTP_PROCHAIN_ECRAN, 18);
  $myForm->setFormButtonProperties("valider", BUTP_PROCHAIN_ECRAN, 20);
  $myForm->setFormButtonProperties("annuler", BUTP_CHECK_FORM, false);
  $myForm->setFormButtonProperties("retour", BUTP_CHECK_FORM, false);
  $myForm->addHTMLExtraCode("echeancier", $HTML_code_echeancier);

  $SESSION_VARS['ecran_precedent'] = 19; // suivi des écrans

  $myForm->buildHTML();
  echo $myForm->getHtml();


}
// Ecran 20 : Confirmation et insertion dans la base de données
else if ($prochain_ecran == 20) {
	 $myForm = new HTML_GEN2(_("Etape 5: Confirmation de la génération de l'échéancier"));


  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();


  // Mise à jour du dossier de credit, Insertion dans la table ad_etr et blocage des garanties
  foreach ($SESSION_VARS['infos_doss'] as $id_doss=>$val_doss) {
		$id_cli = $val_doss["id_client"];

		//Mise à jour du dossier de credit
		$mnt_octr = $SESSION_VARS['infos_doss'][$id_doss]['cre_mnt_octr'];
		$DATA_DOSS['id_agent_gest'] = $SESSION_VARS['infos_doss'][$id_doss]['id_agent_gest'];
		$DATA_DOSS['cre_mnt_octr'] = $mnt_octr;
		$DATA_DOSS['duree_mois'] = $SESSION_VARS['infos_doss'][$id_doss]['duree_mois'];
		$DATA_DOSS['cre_date_approb'] = $SESSION_VARS['infos_doss'][$id_doss]['cre_date_approb'];
		$DATA_DOSS['differe_jours'] = $SESSION_VARS['infos_doss'][$id_doss]['differe_jours'];
		$DATA_DOSS['differe_ech'] = $SESSION_VARS['infos_doss'][$id_doss]['differe_ech'];
		$DATA_DOSS['delai_grac'] = $SESSION_VARS['infos_doss'][$id_doss]['delai_grac'];
		$DATA_DOSS['gar_num'] = $mnt_octr * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_num'];
		$DATA_DOSS['gar_mat'] = $mnt_octr * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_mat'];
		$DATA_DOSS['gar_tot'] = $mnt_octr * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_tot'];
		$DATA_DOSS['gar_num_encours'] = $mnt_octr * $SESSION_VARS['produits_credit'][$SESSION_VARS['id_prod']]['prc_gar_encours'];
		$DATA_DOSS['doss_repris'] = 't';
    updateCredit($id_doss, $DATA_DOSS);

		//Recupération du tableau des garanties
		if (is_array($SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM']))
      $GARANTIE[] = $SESSION_VARS['infos_doss'][$id_doss]['GAR_NUM'];
    if (is_array($SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT']))
      $GARANTIE[] = $SESSION_VARS['infos_doss'][$id_doss]['GAR_MAT'];

   /* Blocage des garanties */
    if (is_array($GARANTIE)) {
      // récupérer les garanties du client
      $gar_mobilisee = array();
      foreach($GARANTIE as $key=>$gar_cli)
      if ($gar_cli["benef"] == $id_cli)
        $gar_mobilisee[] = $gar_cli;
      $myErr = prepareGarantie($id_doss, $gar_mobilisee);
      if ($myErr->errCode != NO_ERR) {
        $dbHandler->closeConnection(false);
        return $myErr;
      }
    }
    /*// Insertion dans la table ad_etr des échéanciers*/
    while (list($key, $ech) = each($SESSION_VARS['infos_doss'][$id_doss]["ECH"])) {
      $etr['id_doss'] = $id_doss;
      $etr['id_ech'] = $ech['id_ech'];
      $etr['date_ech'] = $ech['date_ech'];
      $etr['mnt_cap'] = $ech['mnt_cap'];
      $etr['mnt_int'] = $ech['mnt_int'];
      $etr['mnt_gar'] = $ech['mnt_gar'];
      $etr['mnt_reech'] = 0;
      $etr['solde_cap'] = $ech['solde_cap'];
      $etr['solde_int'] = $ech['solde_int'];
      $etr['solde_gar'] = $ech['solde_gar'];
      $etr['solde_pen'] = $ech['solde_pen'];
      $etr['id_ag']     = $global_id_agence;
      $sql = buildInsertQuery("ad_etr", $etr);

      $result = $db->query($sql);
      if (DB::isError($result)) {
        $dbHandler->closeConnection(false);
        signalErreur("recup_credit.php", "Ecran 9", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
      }
    }

    // Mise à jour du nombre de crédits chez le client
    $sql = "UPDATE ad_cli SET nbre_credits = nbre_credits + 1 WHERE id_client = $id_cli AND id_ag=$global_id_agence ";
    $result = $db->query($sql);
    if (DB::isError($result)) {
      $dbHandler->closeConnection(false);
      signalErreur("recup_credit.php", "Ecran 9", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }
  }

  $dbHandler->closeConnection(true);

  /* Génération du message de confirmation */
  $colb_tableau = '#e0e0ff';
  $msg = new HTML_message(_("Confirmation génération de l'échéancier du dossier de credit"));
  $msg->setMessage(_("Les échéanciers sont générés avec succès.<BR>Pour rendre la reprise de ces dossiers effective, activez ces dossiers à partir du menu principal"));
  $msg->addButton(BUTTON_OK,2);
  $msg->buildHTML();
  echo $msg->HTML_code;

}
 // Ecran 21 : choix de reprise de credits
 else if (!isset($prochain_ecran) || ($prochain_ecran == 21)) {
   $db = $dbHandler->openConnection();
   $sql = "SELECT * FROM ad_ses";
   $result = $db->query($sql);

   if (DB::isError($result)) {
     $dbHandler->closeConnection(false);
     signalErreur(__FILE__,__LINE__,__FUNCTION__);
   }else if ($result->numrows() == 0) {
     $myForm = new HTML_message(_("Erreur de Connexion"));
     $myForm->setMessage(_("Avertissement : Vous devez ouvrir une session ADbanking avant d'éffectuer cette opération. Voulez vous vous connecter maintenant?"));
     $myForm->addButton(BUTTON_OUI,"login");;
     $myForm->addButton(BUTTON_NON, "nologin");
     $myForm->buildHTML();
     echo $myForm->HTML_code;

     echo "<br><p align=center>"._("L'écran d'ouverture de session sur ADbanking se fermera automatiquement si l'authentification réussie.")."</p>" ;
   }else if ($result->numrows() >= 1) {
     $dbHandler->closeConnection(true);
     $myForm = new HTML_GEN2(_("Veuillez cliquer l'un parmi les deux options Reprise des Crédits ci-dessous pour proceder"));

     $myForm->addFormButton(1, 1, "manuel", _("Reprise Manuel"), TYPB_SUBMIT);
     $myForm->addFormButton(1, 2, "auto", _("Reprise Automatique"), TYPB_SUBMIT);
     $myForm->setFormButtonProperties("manuel", BUTP_PROCHAIN_ECRAN, '1');
     $myForm->setFormButtonProperties("auto", BUTP_PROCHAIN_ECRAN, '22');

     $myForm->buildHTML();
     echo $myForm->getHTML();
   }
 }
  else if ($prochain_ecran == 22){
    $dossier_eligible = getDossierReprise();
    if (sizeof($dossier_eligible)>0) {
      $index_curr = 0;
      $index = 1;
      foreach ($dossier_eligible as $id_doss_eligible => $value_eligible) {
        $db = $dbHandler->openConnection();

// Ecran 2 :
// Si on ne trouve pas la date de reprise du bilan, on reprend le crédit à la date d'aujourd'hui
        $SESSION_VARS['date_reprise_credit'] = php2pg(date("d/m/Y"));

// Vérifier que le numéro du client est correct
        //$db = $dbHandler->openConnection();
        $SESSION_VARS['infos_doss'] = array();

// Récupération des infos du client
        if ($value_eligible["id_client"] != '') {
          $SESSION_VARS['infos_client'] = getClientDatas($value_eligible['id_client']);


          if ($SESSION_VARS['infos_client'] == NULL) { // Le client n'existe pas, retour au menu précédent
            $colb_tableau = '#e0e0ff';
            $html_err = new HTML_erreur(_("Numéro incorrect"));
            $html_err->setMessage(_("Le client n'existe pas"));
            $html_err->addButton("BUTTON_OK", "1");
            $html_err->buildHTML();
            echo $html_err->HTML_code;
            exit;
          }

          // A ce niveau , on est sûr que le client existe, vérifier qu'il est actif
          if ($SESSION_VARS['infos_client']['etat'] != 2) { // Le client n'est pas actif
            $etat_client = getLibel("adsys_etat_client", $SESSION_VARS['infos_client']['etat']);
            $colb_tableau = '#e0e0ff';
            $html_err = new HTML_erreur(_("Le client est $etat_client"));
            $html_err->setMessage(_("On reprend les crédits uniquement pour les clients actifs"));
            $html_err->addButton("BUTTON_OK", "1");
            $html_err->buildHTML();
            echo $html_err->HTML_code;
            exit;
          }

          // Le client existe est actif, afficher ses informations
          $SESSION_VARS['global_id_agence'] = getNumAgence();
          $SESSION_VARS['AGC'] = getAgenceDatas($global_id_agence);

        } // Fin si id_client est renseigné


        $dossiers = array(); // tableau contenant les infos sur dossiers réels (dans ad_dcr) et fictifs (ad_dcr_grp_sol)
        $liste = array(); // Liste des dossiers à afficher

        //$index_curr = $index;

// Liste des dossiers individuels et de groupe encours de reprise du client
        $whereCl = " AND etat=10";
        $dossiers_reels = getIdDossier($SESSION_VARS['infos_client']['id_client'], $whereCl);
// Liste des dossiers individuels de crédit encours de reprise
        if (is_array($dossiers_reels))
        foreach ($dossiers_reels as $id_doss => $value) {
          if ($value['gs_cat'] != 2) { // exclure les dossiers repris en groupe. Ils doivent être validés en même via le groupe
            $index_curr = $index;
            $dossiers[$index_curr] = $value;
            $index++;
          }
        }

// Si GS, récupération des membres du groupe et les dossiers des membres dans le cas de dossiers multiples
        if ($SESSION_VARS['infos_client']['statut_juridique'] == 4) {
          // Récupération des membres du groupe
          $result = getListeMembresGrpSol($SESSION_VARS['infos_client']['id_client']);
          if (is_array($result->param))
            foreach ($result->param as $key => $id_cli) {
              $nom_client = getClientName($id_cli);
              $SESSION_VARS['liste_membres'][$id_cli] = $nom_client;
            }

          // Récupération des dossiers fictifs du groupe avec dossiers multiples : cas 2
          $whereCl = " WHERE id_membre=" . $SESSION_VARS['infos_client']['id_client'] . " and gs_cat = 2";
          $dossiers_fictifs = getCreditFictif($whereCl);
          // Pour chaque dossier fictif du GS, récupération des dossiers réels des membres du GS
          $dossiers_membre = getDossiersMultiplesGS($SESSION_VARS['infos_client']['id_client']);
          foreach ($dossiers_fictifs as $id => $value) {
            // Récupération, pour chaque dossier fictif, des dossiers réels associés : CS avec dossiers multiples
            $infos = '';
            foreach ($dossiers_membre as $id_doss => $val)
              if (($val['id_dcr_grp_sol'] == $id) AND ($val['etat'] == 10)) {
                $date_dem = $val['date_dem'];
                $infos .= " N° $id_doss "; // on affiche les numéros de dossiers réels sur une ligne
              }
            if ($infos != '') {  // Si au moins on a 1 dossier
              $index_curr = $index;
              $dossiers[$index_curr] = $value; // on garde les infos du dossier fictif
              $index++;
            }
          }
        } else { // Personne physique, Personne morale ou  Groupe Informel
          // on considère le client lui-même comme étant le seul membre
          $nom_client = getClientName($SESSION_VARS['infos_client']['id_client']);
          $SESSION_VARS['liste_membres'][$SESSION_VARS['infos_client']['id_client']] = $SESSION_VARS['infos_client']['id_client'] . " " . $nom_client;
        }
        $SESSION_VARS['dossiers'] = $dossiers;


// Ecran 12

        if ($SESSION_VARS['dossiers'][$index_curr]['gs_cat'] != 2) { // dossier individuel
          // Les informations sur le dossier
          $id_doss = $SESSION_VARS['dossiers'][$index_curr]['id_doss'];
          $id_prod = $SESSION_VARS['dossiers'][$index_curr]['id_prod'];
          $SESSION_VARS['infos_doss'][$id_doss] = getDossierCrdtInfo($id_doss); // infos du dossier reel
          // Infos dossiers fictifs dans le cas de GS avec dossier unique
          if ($SESSION_VARS['dossiers'][$index_curr]['gs_cat'] == 1) {
            $whereCond = " WHERE id_dcr_grp_sol = $id_doss";
            $SESSION_VARS['infos_doss'][$id_doss]['doss_fic'] = getCreditFictif($whereCond);
          }
        } elseif ($SESSION_VARS['dossiers'][$index_curr]['gs_cat'] == 2) { // GS avec dossiers multiples
          // id du dossier fictif : id du dossier du groupe
          $id_doss_fic = $SESSION_VARS['dossiers'][$index_curr]['id'];

          // dossiers réels des membre du GS
          $dossiers_membre = getDossiersMultiplesGS($SESSION_VARS['infos_client']['id_client']);
          foreach ($dossiers_membre as $id_doss => $val) {
            if ($val['id_dcr_grp_sol'] == $SESSION_VARS['dossiers'][$index_curr]['id'] and $val['etat'] == 10) {
              $SESSION_VARS['infos_doss'][$id_doss] = $val; // infos d'un dossier reel d'un membre
              $id_prod = $SESSION_VARS['infos_doss'][$id_doss]['id_prod']; // même produit pour tous les dossiers
            }
          }
        }
// Parcours des dossiers
        foreach ($SESSION_VARS['infos_doss'] as $id_doss => $val_doss) {

          $id_prod = $val_doss['id_prod'];
          $cre_mnt_octr = $val_doss['cre_mnt_octr'];
          $duree_mois = $val_doss['duree_mois'];
          $differe_jours = $val_doss['differe_jours'];
          $differe_ech = $val_doss['differe_ech'];
          $gar_num = $val_doss['gar_num'];
          $gar_num_encours = $val_doss['gar_num_encours'];
          $cre_date_debloc = pg2phpDate($val_doss['cre_date_debloc']);

          // Récupération des données concernant l'échéancier théorique de remboursement
          $sql = "SELECT * FROM ad_etr WHERE id_ag = $global_id_agence and id_doss = $id_doss ORDER BY id_ech";
          $result = $db->query($sql);
          if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur("recup_credit.php", "Ecran 12", _("La requête ne s'est pas exécutée correctement") . " : " . $result->getMessage());
          }

          // Construction du tableau des échéances à partir de la base de données
          $ECH = array();
          while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC))
            $ECH[$tmprow["id_ech"]] = $tmprow;

          $SESSION_VARS['Ech_values_' . $id_doss] = $ECH;

          // Recupere nombre total des echeances pour dossier (control JS pour les totaux dans le tableau)
          $sql = "SELECT count(id_ech) as  total_ech FROM ad_etr WHERE id_ag = $global_id_agence and id_doss = $id_doss";
          $result = $db->query($sql);
          if (DB::isError($result)) {
            $dbHandler->closeConnection(false);
            signalErreur("recup_credit.php", "Ecran 12", _("La requête ne s'est pas exécutée correctement") . " : " . $result->getMessage());
          }
          $countrow = $result->fetchrow(DB_FETCHMODE_ASSOC);
          $nbrTotalEcheance = $countrow['total_ech'];
          $SESSION_VARS['tot_ech'][$id_doss] = $nbrTotalEcheance;

          $PROD = getProdInfo(" where id =" . $id_prod);
          $libel_prod = $PROD[0]["libel"];

          global $adsys;

        } // fin parcours dossiers

//$dbHandler->closeConnection(true);


//Ecran 13

        /* Récupération de tous les états de crédit */
        $etats_cr = array();
        $etats_cr = getTousEtatCredit();

        /* Récupération de l'état */
        $SESSION_VARS['id_etat_perte'] = getIDEtatPerte();
// Parcours des dossiers

        foreach ($SESSION_VARS['infos_doss'] as $id_doss => $val_doss) {

          $id_prod = $val_doss['id_prod'];
          $PROD = getProdInfo(" where id =" . $id_prod);
          $cre_mnt_octr = $val_doss['cre_mnt_octr'];
          $duree_mois = $val_doss['duree_mois'];
          $differe_jours = $val_doss['differe_jours'];
          $differe_ech = $val_doss['differe_ech'];
          $gar_num = $val_doss['gar_num'];
          $gar_num_encours = $val_doss['gar_num_encours'];
          $cre_date_debloc = pg2phpDate($val_doss['cre_date_debloc']);
          $mode_calc_int = $PROD[0]['mode_calc_int'];

          if (isset($val_doss["ECH"])) { // On vient de l'écran suivant
            $ECH = $val_doss["ECH"];
            $cre_etat = $val_doss["cre_etat"];
            $ech_courante = $val_doss["ech_courante"];
          } else {
            // Récupération des données concernant l'échéancier théorique de remboursement
            $sql = "SELECT * FROM ad_etr WHERE id_ag = $global_id_agence and id_doss = $id_doss ORDER BY id_ech";
            $result = $db->query($sql);
            if (DB::isError($result)) {
              $dbHandler->closeConnection(false);
              signalErreur("recup_credit.php", "Ecran 13", _("La requête ne s'est pas exécutée correctement") . " : " . $result->getMessage());
            }

            // Construction du tableau des échéances à partir de la base de données
            $SESSION_VARS['infos_doss'][$id_doss]['ECH'] = array();
            while ($tmprow = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
              $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$tmprow["id_ech"]] = $tmprow;
              $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$tmprow["id_ech"]]["date_ech"] = pg2phpDate($SESSION_VARS['infos_doss'][$id_doss]['ECH'][$tmprow["id_ech"]]["date_ech"]);
            }

            // Recherche de la dernière échéance remboursée et remplissage du tableau
            $i = sizeof($SESSION_VARS['infos_doss'][$id_doss]['ECH']);
            $last_ech_remb = 0;
            $prec_ech_courante = 0; // On va utiliser cette varialbe pour trouver l'échéance courante, càd la prochaine échéance à partir d'aujourd'hui
            $solde_cap = 0;
            $rembourse = array(); // Array utilisé pour sauvegarder les valeurs

            while ($i > 0) {
              if ((${'rembourse' . $id_doss . '_' . $i} == 'on') || ($SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["remb"] == 't')) {
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["remb"] = 't';
                //mnt_cap prend le montant remboursé qui peut ètre modifié dans $solde_cap
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["mnt_cap"] = $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_cap"];
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["mnt_int"] = $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_int"];
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_cap"] = 0;
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_int"] = 0;
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_gar"] = 0;
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_pen"] = 0;
                if ($last_ech_remb == 0)
                  $last_ech_remb = $i;
                $rembourse[$i] = true;
              } else {
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["remb"] = 'f';
                $solde_cap += $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_cap"];
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["solde_pen"] = calculePenalitesCreditRepris($id_doss, $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["id_ech"], $solde_cap, $SESSION_VARS['date_reprise_credit']);
                $rembourse[$i] = false;
              }

              // Est-ce que c'est l'échéance courante - 1 ?
              if ($prec_ech_courante == 0 && (isBefore($SESSION_VARS['infos_doss'][$id_doss]['ECH'][$i]["date_ech"], pg2phpDate($SESSION_VARS['date_reprise_credit']))))
                $prec_ech_courante = $i;

              $i--;

              $SESSION_VARS['infos_doss'][$id_doss]["rembourse"] = $rembourse;
            }
          }

          //Si la dernière échéance remboursée est la dernière échéance du crédit,crédit devait être soldé et ne pouvait donc pas être repris
          if ($last_ech_remb == sizeof($SESSION_VARS['infos_doss'][$id_doss]['ECH'])) {
            $colb_tableau = '#e0e0ff';
            $erreur = new HTML_erreur(_("Impossible de reprendre un crédit soldé"));
            $erreur->setMessage(_("ADbanking a calculé que ce crédit devait être déjà soldé."));
            $erreur->addButton(BUTTON_OK, 1);
            $erreur->buildHTML();
            echo $erreur->HTML_code;
            die();
          }

          /* Détermination de l'état du crédit */
          $SESSION_VARS['infos_doss'][$id_doss]["cre_etat_max_jour"] = 0;
          $date_last_ech_remb = $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$last_ech_remb]["date_ech"];
          $date_first_ech_non_remb = $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$last_ech_remb + 1]["date_ech"];

          if (isBefore($date_first_ech_non_remb, pg2phpDate($SESSION_VARS['date_reprise_credit']))) { // Le crédit est en retard
            /* Recherche du nombre de jours de retard à la date de reprise du bilan */
            $nbre_jours_retard = nbreDiffJours(pg2phpDate($SESSION_VARS['date_reprise_credit']), $date_first_ech_non_remb);

            /* Détermination de l'état en fonction du nombre de jours de retart */
            $cre_etat = calculeEtatCredit($nbre_jours_retard);
            $SESSION_VARS['infos_doss'][$id_doss]["cre_etat_max_jour"] = $nbre_jours_retard;
          } else /* le crédit est sain */
            $cre_etat = 1;

          ////echo "<p align=\"center\">ADbanking a déduit les informations suivantes concernant ce dossier de crédit";

          $SESSION_VARS['infos_doss'][$id_doss]["cre_etat"] = $cre_etat;

          // Construction du code HTML de l'échéancier
          // Inspiré de la fonction HTML_echeancier

          $PROD = getProdInfo(" where id =" . $id_prod);
          $libel_prod = $PROD[0]["libel"];

        } // fin parcours dossiers

//$dbHandler->closeConnection(true);

// Ecran 14

// Récupération des états de crédit
        $etats_cr = array();
        $etats_cr = getTousEtatCredit();
        $xtHTML = "";
// Parcours des dossiers
        foreach ($SESSION_VARS['infos_doss'] as $id_doss => $val_doss) {
          // Remplissage du tableau avec les montants en pénalités entrés par l'utilisateur
          while (list($key, $ech) = each($val_doss['ECH'])) {
            if (isset(${'pen' . $key})) {
              if (${'pen' . $key} == '') { // Si Montant rensiengé est vide
                $pen[$key] = 0;
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$key]["solde_pen"] = 0;
              } else {
                $pen[$key] = recupMontant(${'pen' . $key});
                $SESSION_VARS['infos_doss'][$id_doss]['ECH'][$key]["solde_pen"] = recupMontant(${'pen' . $key});
              }
            }
          }

          $id_prod = $val_doss['id_prod']; // Même si c'est plusieurs dossiers, on a un seul produit de crédit
          $PROD = getProdInfo(" where id =" . $id_prod);
          $libel_prod = $PROD[0]["libel"];

          // Récupération des comptes de substitut du produit de crédit et de la garantie
          $cpte_subs_cr = "";
          $cpte_subs_gar = "";
          $fp = fopen("../traduction.conf", 'r'); // ouverture du fichier de paramétrage de la reprise
          if ($fp == false) {
            echo "<BR/><BR/><P align=center><FONT color=red>" . _("Le fichier <CODE><B>traduction.conf</B></CODE> n'a pas été trouvé à l'endroit attendu") . "</FONT></P>";
            die();
          }

          while (!feof($fp)) {
            // Récupération d'une ligne du fichier
            $ligne = fgets($fp, 1024);

            // Si c'est le paramétrage du compte du produit
            $id = $id_prod;
            $ch = "cpt_etat_cr_" . $id . "_" . $SESSION_VARS['infos_doss'][$id_doss]['cre_etat'];
            $long = strlen($ch);
            $debut = substr($ligne, 0, $long);
            if ($debut == $ch) {
              $ligne = ereg_replace("$ch", "", $ligne); /* éliminer "cpt_etat_cr_$id_$etat" de la ligne */
              $ligne = ereg_replace("\t", "|", $ligne); /* Remplacer tabulation par | */
              $ligne = ereg_replace("\n", "|", $ligne); /* Remplacer fin de ligne par | */
              $tab = explode("|", $ligne); /* récupérer la ligne sous forme de tableau */
              $cpte_subs_cr = trim($tab[1]);  /* éliminer les espaces de début et de fin de ligne */
            }
            // Compte de substitut de la garantie
            $ch = "cpte_gar_" . $id;
            $long = strlen($ch);
            $debut = substr($ligne, 0, $long);
            if ($debut == $ch) {
              $ligne = ereg_replace("$ch", "", $ligne); /* éliminer "cpte_gar_$id" de la ligne */
              $ligne = ereg_replace("\t", "|", $ligne); /* Remplacer tabulation par | */
              $ligne = ereg_replace("\n", "|", $ligne); /* Remplacer fin de ligne par | */
              $tab = explode("|", $ligne); /* récupérer la ligne sous forme de tableau */
              $cpte_subs_gar = trim($tab[1]);  /* éliminer les espaces de début et de fin de ligne */
            }
          }
          fclose($fp);

          $SESSION_VARS['infos_doss'][$id_doss]["cpte_subs_cr"] = $cpte_subs_cr;
          $SESSION_VARS['infos_doss'][$id_doss]["cpte_subs_gar"] = $cpte_subs_gar;

          // Enregistreement du solde en capital
          $SESSION_VARS['infos_doss'][$id_doss]["solde_cap"] = $total_solde_cap;
          // Enregistreement de la garantie remboursée
          $SESSION_VARS['infos_doss'][$id_doss]["gar_remb"] = $total_gar - $total_solde_gar;

        }

//Ecran 15

// Exercice en cours
        $global_id_agence = getNumAgence();
        $compteur_mv = 0;
        $compteur_ecr = 1;
        $AG = getAgenceDatas($global_id_agence);
        $exo = $AG["exercice"];

        $adsys_pr_cr = getProdInfoByID();

// recupère le type de passage en perte
        $sql_type_perte = "select passage_perte_automatique from ad_agc";
        $result_type_perte = $db->query($sql_type_perte);
        if (DB::isError($result_type_perte)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__, __LINE__, __FUNCTION__);
        }
        $row_perte = $result_type_perte->fetchrow();
        $type_radiation_credit = $AG["passage_perte_automatique"];

// Parcours des dossiers
        $comptable = array(); // Passage des écritures comptables
        $compteur_mv = 0;
        $compteur_ecr = 1;
        foreach ($SESSION_VARS['infos_doss'] as $id_doss => $val_doss) {
          $ECH = $val_doss['ECH'];
          $id_client = $val_doss['id_client'];
          $id_prod = $val_doss['id_prod'];
          $cre_mnt_octr = $val_doss['cre_mnt_octr'];
          $gar_num = $val_doss['gar_num'];
          $gar_num_encours = $val_doss['gar_num_encours'];
          $cre_date_debloc = pg2phpDate($val_doss['cre_date_debloc']);
          $solde_cap = 0;

          // Mise à jour de solde_cap, solde_int et solde_pen dans ad_etr
          while (list($i, $ech) = each($ECH)) {
            $solde_cap += $ech["solde_cap"];

            // Si le crédit est en perte, le restant dû en garantie dans l'échéancier est mis à 0
            $sql = "UPDATE ad_etr SET solde_cap = " . $ech["solde_cap"] . ", solde_int = " . $ech["solde_int"] . ", solde_gar = " . $ech["solde_gar"] . ", solde_pen = " . $ech["solde_pen"] . ", remb = '" . $ech["remb"] . "' WHERE id_doss = $id_doss AND id_ech = $i AND id_ag=$global_id_agence ";
            $result = $db->query($sql);
            if (DB::isError($result)) {

              $dbHandler->closeConnection(false);
              signalErreur("recup_credit.php", "Ecran 15", _("La requête ne s'est pas exécutée correctement") . " : " . $result->getMessage());
            }

            /* Insertion du remboursement  */
            /*if ($ech["remb"] == 't') {
              $mnt_remb_cap = 0;
              $mnt_remb_int = 0;
              $mnt_remb_gar = 0;
              $mnt_remb_pen = 0;

              //FIXME: on suppose qu'il a tout payé: les montants présentés par l'échéancier */
            /* if (recupMontant($ech["mnt_cap"]) != "")
               $mnt_remb_cap = recupMontant($ech["mnt_cap"]);
             if (recupMontant($ech["mnt_int"]) != "")
               $mnt_remb_int = recupMontant($ech["mnt_int"]);
             if (recupMontant($ech["mnt_gar"]) != "")
               $mnt_remb_gar = recupMontant($ech["mnt_gar"]);
             if (recupMontant($ech["mnt_pen"]) != "")
               $mnt_remb_pen = recupMontant($ech["mnt_pen"]);

             $id_ech = $i;
             $num_rem = getNextNumRemboursement($id_doss, $id_ech);

             $sql = "INSERT INTO ad_sre(id_doss, id_ag, num_remb, date_remb, id_ech, mnt_remb_cap, mnt_remb_int, mnt_remb_pen, mnt_remb_gar) ";
             $sql .= "VALUES($id_doss, $global_id_agence, $num_rem, '" . $SESSION_VARS['date_reprise_credit'] . "', $id_ech, $mnt_remb_cap, $mnt_remb_int, $mnt_remb_pen, $mnt_remb_gar)";
             $result = $db->query($sql);
             if (DB::isError($result)) {
               $dbHandler->closeConnection(false);

               signalErreur(__FILE__, __LINE__, __FUNCTION__);
             }
           }*/
          }

          $array_credit = getCompteCptaDcr($id_doss);
          $cre_etat = $val_doss["cre_etat"];

          // Création du compte de crédit associé
          $rang = getRangDisponible($id_client);
          $numCompletCptCredit = makeNumCpte($id_client, $rang);
          $CPT_CRED = array();
          $CPT_CRED["id_titulaire"] = $id_client;
          $CPT_CRED["date_ouvert"] = $cre_date_debloc;
          $CPT_CRED["utilis_crea"] = 1; // Par défaut 'Administrateur'
          if ($val_doss["cre_etat"] == $SESSION_VARS['id_etat_perte']) // crédit en perte
            $CPT_CRED["etat_cpte"] = 2; // Compte bloqué
          else
            $CPT_CRED["etat_cpte"] = 1; // Compte ouvert
          $CPT_CRED["solde"] = 0; // Le solde sera mis à jour par le passage des écriture comptables
          $CPT_CRED["num_cpte"] = $rang;
          $CPT_CRED["num_complet_cpte"] = $numCompletCptCredit;
          $CPT_CRED["id_prod"] = 3;
          $CPT_CRED["cpt_vers_int"] = NULL;

          $CPT_CRED["devise"] = $adsys_pr_cr[$id_prod]['devise'];
          $message = "";
          // Création du compte de crédit
          $id_cpte_cr = creationCompte($CPT_CRED);
          if ($id_cpte_cr == NULL) {
            $message = sprintf(_("Erreur interne lors de la création du compte de crédit: "), $numCompletCptCredit);
          }
          // Mise à jour du dossier de crédit
          $DATA_DOSS = array();
          if ($val_doss["cre_etat"] == $SESSION_VARS['id_etat_perte']) { // crédit en perte
            $DATA_DOSS['etat'] = 9; // dossier passé en perte
            $DATA_DOSS['suspension_pen'] = true;
            $DATA_DOSS['perte_capital'] = $solde_cap;
          } else
            $DATA_DOSS['etat'] = 5;

          $DATA_DOSS['cre_id_cpte'] = $id_cpte_cr;
          $DATA_DOSS['cre_etat'] = $val_doss["cre_etat"];
          $DATA_DOSS['cre_retard_etat_max'] = $val_doss["cre_etat"];
          $DATA_DOSS['cre_retard_etat_max_jour'] = $val_doss['cre_etat_max_jour'];
          $DATA_DOSS['cre_date_etat'] = $SESSION_VARS['date_reprise_credit'];
          updateCredit($id_doss, $DATA_DOSS);

          // Compte associé à l'état du crédit
          $cr_etat_cptes = array();
          $cr_etat_cptes = getComptesEtatsProduits();

          $DOSSIER = getDossierCrdtInfo($id_doss);
          foreach ($cr_etat_cptes as $key => $value) {
            if ($value['id_prod_cre'] == $id_prod and $value['id_etat_credit'] == $DOSSIER['cre_etat'])
              $cpte_credit = $value['num_cpte_comptable'];
          }

          if ($val_doss["cre_etat"] != $SESSION_VARS['id_etat_perte']) { // Si le crédit n'est pas en perte
            /****** Pasage du mouvement comptable : contrepassation du mouvement de la reprise du bilan  *******/
            $libel_ecriture = new Trad();
            $libel_ecriture->set_traduction($global_langue_utilisateur, _('Reprises de crédit'));

            $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_cr"], $adsys_pr_cr[$id_prod]['devise']);
            $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
            $comptable[$compteur_mv]["montant"] = $solde_cap;
            $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
            $comptable[$compteur_mv]["type_operation"] = 2001;
            $comptable[$compteur_mv]["id"] = $compteur_ecr;
            $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
            $compteur_mv++;

            $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($cpte_credit, $adsys_pr_cr[$id_prod]['devise']);
            $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
            $comptable[$compteur_mv]["montant"] = $solde_cap;
            $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
            $comptable[$compteur_mv]["type_operation"] = 2001;
            $comptable[$compteur_mv]["id"] = $compteur_ecr;
            $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_cr;
            $compteur_mv++;  /* incrémentation du id des mouvements  */
            $compteur_ecr++; /* incrémentation du id des écritures */
            // Création d'un compte d'épargne nantie pour chaque garantie numéraire bloquée au début
            if ($gar_num > 0) {

              $liste_gar = getListeGaranties($id_doss);
              foreach ($liste_gar as $key => $val) {
                if ($val['type_gar'] == 1 and ($val['gar_num_id_cpte_prelev'] != '')) {
                  $CPT_EN = array();

                  // S'il y a un compte de prélèvement, le compte de garantie appartient au garant
                  $InfoCpte = getAccountDatas($val['gar_num_id_cpte_prelev']);
                  $CPT_EN["id_titulaire"] = $InfoCpte["id_titulaire"];

                  // Recherche du rang disponible
                  $rang = getRangDisponible($CPT_EN["id_titulaire"]);
                  $CPT_EN["num_complet_cpte"] = makeNumCpte($CPT_EN["id_titulaire"], $rang);
                  $CPT_EN["date_ouvert"] = $cre_date_debloc;
                  $CPT_EN["utilis_crea"] = 1;  // Par défaut 'Administrateur'
                  $CPT_EN["etat_cpte"] = 3;   // Bloqué
                  $CPT_EN["solde"] = 0; /* Le solde sera mis à jour par le passage des écriture comptables*/
                  $CPT_EN["num_cpte"] = $rang;
                  $CPT_EN["id_prod"] = 4;
                  $CPT_EN["devise"] = $adsys_pr_cr[$id_prod]['devise'];
                  $CPT_EN["cpt_vers_int"] = NULL;

                  /* Création du compte nantie*/
                  $id_cpte_en = creationCompte($CPT_EN);
                  if ($id_cpte_en == NULL) {
                    $message = sprintf(_("Erreur interne lors de la création du compte de garantie numéraire: "), $CPT_EN['num_complet_cpte']);
                  }

                  /* Renseigner le compte de garantie dans ad_gar */
                  $sql = "UPDATE ad_gar SET gar_num_id_cpte_nantie = $id_cpte_en WHERE id_gar = " . $val['id_gar'] . " AND id_ag= $global_id_agence ";
                  $result = $db->query($sql);
                  if (DB::isError($result)) {
                    $dbHandler->closeConnection(false);
                    signalErreur(__FILE__, __LINE__, __FUNCTION__);
                  }
                  $libel_ecriture = new Trad();
                  $libel_ecriture->set_traduction($global_langue_utilisateur, _("Transfert de garantie - Reprises de crédit"));


                  /****** Passage du mouvement comptable: contrepassation du mouvement de la reprise du bilan  *******/
                  $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_gar"], $adsys_pr_cr[$id_prod]['devise']);
                  $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
                  $comptable[$compteur_mv]["montant"] = $val['montant_vente'];
                  $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
                  $comptable[$compteur_mv]["type_operation"] = 220;
                  $comptable[$compteur_mv]["id"] = $compteur_ecr;
                  $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
                  $compteur_mv++;

                  $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($array_credit["cpte_cpta_prod_cr_gar"], $adsys_pr_cr[$id_prod]['devise']);
                  $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
                  $comptable[$compteur_mv]["montant"] = $val['montant_vente'];
                  $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
                  $comptable[$compteur_mv]["type_operation"] = 220;
                  $comptable[$compteur_mv]["id"] = $compteur_ecr;
                  $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_en;
                  $compteur_mv++; /* Incrémentation du id des mouvements */
                  $compteur_ecr++; /* Incrémentation du id des écritures */

                } /* Fin if($val['type_gar'] == 1) */
              } /* Fin foreach sur les garanties*/
            } /* Fin if ($gar_num > 0) */

            /* S'il y a des garanties numéraires encours */
            if ($gar_num_encours > 0) {
              /* Le compte des garanties encours appartient toujours au client lui-même */
              $rang = getRangDisponible($id_client);
              $numCompletCptCredit = makeNumCpte($id_client, $rang);

              $CPT_EN = array();
              $CPT_EN["id_titulaire"] = $id_client;
              $CPT_EN["date_ouvert"] = $cre_date_debloc;
              $CPT_EN["utilis_crea"] = 1;  // Par défaut 'Administrateur'
              $CPT_EN["etat_cpte"] = 1;   // le compte est ouvert
              $CPT_EN["solde"] = 0; /* Le solde sera mis à jour par le passage des écriture comptables*/
              $CPT_EN["num_cpte"] = $rang;
              $CPT_EN["num_complet_cpte"] = $numCompletCptCredit;
              $CPT_EN["id_prod"] = 4;
              $CPT_EN["devise"] = $adsys_pr_cr[$id_prod]['devise'];
              $CPT_EN["cpt_vers_int"] = NULL;

              /* Création du compte nantie*/
              $id_cpte_en = creationCompte($CPT_EN);
              if ($id_cpte_en == NULL) {
                $message = sprintf(_("Erreur interne lors de la création du compte de garantie numéraire en cours: "), $numCompletCptCredit);
              }
              /* Ajout du compte des garanties encours dans le dossier de crédit */
              $DATA_DOSS = array();
              $DATA_DOSS['cpt_gar_encours'] = $id_cpte_en;
              updateCredit($id_doss, $DATA_DOSS);

              /* Insertion de la garantie numéraire à constituer dans la tables des garanties */
              $GAR_ENCOURS = array();
              $GAR_ENCOURS['type_gar'] = 1;
              $GAR_ENCOURS['id_doss'] = $id_doss;
              $GAR_ENCOURS['gar_num_id_cpte_prelev'] = NULL;
              $GAR_ENCOURS['gar_num_id_cpte_nantie'] = $id_cpte_en;
              $GAR_ENCOURS['etat_gar'] = 1; /* En cours de mobilisation */
              $GAR_ENCOURS['montant_vente'] = $val_doss["gar_remb"];
              $GAR_ENCOURS['devise_vente'] = $adsys_pr_cr[$id_prod]['devise'];
              $GAR_ENCOURS['id_ag'] = $global_id_agence;
              $sql = buildInsertQuery("ad_gar", $GAR_ENCOURS);
              $result = $db->query($sql);
              if (DB::isError($result)) {
                $dbHandler->closeConnection(false);

                signalErreur(__FILE__, __LINE__, __FUNCTION__);
              }

              /****** Ecriture comptable du remboursement de la garantie : contrepassation du mouvement de la reprise du bilan  *******/
              if ($SESSION_VARS["gar_remb"] > 0) {
                $libel_ecriture = new Trad();
                $libel_ecriture->set_traduction($global_langue_utilisateur, _("Transfert de garantie - Reprises de crédit"));

                $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_gar"], $adsys_pr_cr[$id_prod]['devise']);
                $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
                $comptable[$compteur_mv]["montant"] = $val_doss["gar_remb"];
                $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
                $comptable[$compteur_mv]["type_operation"] = 220;
                $comptable[$compteur_mv]["id"] = $compteur_ecr;
                $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
                $compteur_mv++;

                $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($array_credit["cpte_cpta_prod_cr_gar"], $adsys_pr_cr[$id_prod]['devise']);
                $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
                $comptable[$compteur_mv]["montant"] = $val_doss["gar_remb"];;
                $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
                $comptable[$compteur_mv]["type_operation"] = 220;
                $comptable[$compteur_mv]["id"] = $compteur_ecr;
                $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_en;
                $compteur_mv++;
                $compteur_ecr++;
              } /* Fin if($SESSION_VARS["gar_remb"] > 0) */

            } /* Fin if($gar_num_encours > 0) */

          } // Fin si crédit n'est pas en perte
          else {//crédit est en perte
            if ($type_radiation_credit == 'f') { // ce cas se présentera uniquement pour une radiation manuelle des crédits
              /****** Pasage du mouvement comptable : contrepassation du mouvement de la reprise du bilan  *******/
              $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_cr"], $adsys_pr_cr[$id_prod]['devise']);
              $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
              $comptable[$compteur_mv]["montant"] = $solde_cap;
              $libel_ecriture = new Trad();
              $libel_ecriture->set_traduction($global_langue_utilisateur, _('Reprise de crédit'));

              $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
              $comptable[$compteur_mv]["type_operation"] = 2001;
              $comptable[$compteur_mv]["id"] = $compteur_ecr;
              $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
              $compteur_mv++;

              $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($cpte_credit, $adsys_pr_cr[$id_prod]['devise']);
              $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
              $comptable[$compteur_mv]["montant"] = $solde_cap;

              $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
              $comptable[$compteur_mv]["type_operation"] = 2001;
              $comptable[$compteur_mv]["id"] = $compteur_ecr;
              $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_cr;
              $compteur_mv++;  /* incrémentation du id des mouvements  */
              $compteur_ecr++; /* incrémentation du id des écritures */


              // Création d'un compte d'épargne nantie pour chaque garantie numéraire bloquée au début
              if ($gar_num > 0) {

                $liste_gar = getListeGaranties($id_doss);
                foreach ($liste_gar as $key => $val) {
                  if ($val['type_gar'] == 1 and ($val['gar_num_id_cpte_prelev'] != '')) {
                    $CPT_EN = array();

                    // S'il y a un compte de prélèvement, le compte de garantie appartient au garant
                    $InfoCpte = getAccountDatas($val['gar_num_id_cpte_prelev']);
                    $CPT_EN["id_titulaire"] = $InfoCpte["id_titulaire"];

                    // Recherche du rang disponible
                    $rang = getRangDisponible($CPT_EN["id_titulaire"]);
                    $CPT_EN["num_complet_cpte"] = makeNumCpte($CPT_EN["id_titulaire"], $rang);
                    $CPT_EN["date_ouvert"] = $cre_date_debloc;
                    $CPT_EN["utilis_crea"] = 1;  // Par défaut 'Administrateur'
                    $CPT_EN["etat_cpte"] = 3;   // Bloqué
                    $CPT_EN["solde"] = 0; /* Le solde sera mis à jour par le passage des écriture comptables*/
                    $CPT_EN["num_cpte"] = $rang;
                    $CPT_EN["id_prod"] = 4;
                    $CPT_EN["devise"] = $adsys_pr_cr[$id_prod]['devise'];
                    $CPT_EN["cpt_vers_int"] = NULL;

                    /* Création du compte nantie*/
                    $id_cpte_en = creationCompte($CPT_EN);
                    if ($id_cpte_en == NULL) {
                      $message = sprintf(_("Erreur interne lors de la création du compte de garantie numéraire: "), $CPT_EN["num_complet_cpte"]);
                    }

                    /* Renseigner le compte de garantie dans ad_gar */
                    $sql = "UPDATE ad_gar SET gar_num_id_cpte_nantie = $id_cpte_en WHERE id_gar = " . $val['id_gar'] . " AND id_ag= $global_id_agence ";
                    $result = $db->query($sql);
                    if (DB::isError($result)) {
                      $dbHandler->closeConnection(false);
                      signalErreur(__FILE__, __LINE__, __FUNCTION__);
                    }
                    $libel_ecriture = new Trad();
                    $libel_ecriture->set_traduction($global_langue_utilisateur, _('Transfert de garantie - Reprises de crédit'));

                    /****** Passage du mouvement comptable: contrepassation du mouvement de la reprise du bilan  *******/
                    $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_gar"], $adsys_pr_cr[$id_prod]['devise']);
                    $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
                    $comptable[$compteur_mv]["montant"] = $val['montant_vente'];
                    $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
                    $comptable[$compteur_mv]["id"] = $compteur_ecr;
                    $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
                    $compteur_mv++;

                    $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($array_credit["cpte_cpta_prod_cr_gar"], $adsys_pr_cr[$id_prod]['devise']);
                    $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
                    $comptable[$compteur_mv]["montant"] = $val['montant_vente'];

                    $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
                    $comptable[$compteur_mv]["type_operation"] = 220;
                    $comptable[$compteur_mv]["id"] = $compteur_ecr;
                    $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_en;
                    $compteur_mv++; /* Incrémentation du id des mouvements */
                    $compteur_ecr++; /* Incrémentation du id des écritures */

                  } /* Fin if($val['type_gar'] == 1) */
                } /* Fin foreach sur les garanties*/
              } /* Fin if ($gar_num > 0) */

              /* S'il y a des garanties numéraires encours */
              if ($gar_num_encours > 0) {
                /* Le compte des garanties encours appartient toujours au client lui-même */
                $rang = getRangDisponible($id_client);
                $numCompletCptCredit = makeNumCpte($id_client, $rang);

                $CPT_EN = array();
                $CPT_EN["id_titulaire"] = $id_client;
                $CPT_EN["date_ouvert"] = $cre_date_debloc;
                $CPT_EN["utilis_crea"] = 1;  // Par défaut 'Administrateur'
                $CPT_EN["etat_cpte"] = 1;   // le compte est ouvert
                $CPT_EN["solde"] = 0; /* Le solde sera mis à jour par le passage des écriture comptables*/
                $CPT_EN["num_cpte"] = $rang;
                $CPT_EN["num_complet_cpte"] = $numCompletCptCredit;
                $CPT_EN["id_prod"] = 4;
                $CPT_EN["devise"] = $adsys_pr_cr[$id_prod]['devise'];
                $CPT_EN["cpt_vers_int"] = NULL;

                /* Création du compte nantie*/
                $id_cpte_en = creationCompte($CPT_EN);
                if ($id_cpte_en == NULL) {
                  $message = sprintf(_("Erreur interne lors de la création du compte de garantie numéraire en cours: "), $numCompletCptCredit);
                }

                /* Ajout du compte des garanties encours dans le dossier de crédit */
                $DATA_DOSS = array();
                $DATA_DOSS['cpt_gar_encours'] = $id_cpte_en;
                updateCredit($id_doss, $DATA_DOSS);

                /* Insertion de la garantie numéraire à constituer dans la tables des garanties */
                $GAR_ENCOURS = array();
                $GAR_ENCOURS['type_gar'] = 1;
                $GAR_ENCOURS['id_doss'] = $id_doss;
                $GAR_ENCOURS['gar_num_id_cpte_prelev'] = NULL;
                $GAR_ENCOURS['gar_num_id_cpte_nantie'] = $id_cpte_en;
                $GAR_ENCOURS['etat_gar'] = 1; /* En cours de mobilisation */
                $GAR_ENCOURS['montant_vente'] = $val_doss["gar_remb"];
                $GAR_ENCOURS['devise_vente'] = $adsys_pr_cr[$id_prod]['devise'];
                $GAR_ENCOURS['id_ag'] = $global_id_agence;
                $sql = buildInsertQuery("ad_gar", $GAR_ENCOURS);
                $result = $db->query($sql);
                if (DB::isError($result)) {
                  $dbHandler->closeConnection(false);

                  signalErreur(__FILE__, __LINE__, __FUNCTION__);
                }

                /****** Ecriture comptable du remboursement de la garantie : contrepassation du mouvement de la reprise du bilan  *******/
                if ($SESSION_VARS["gar_remb"] > 0) {
                  $libel_ecriture = new Trad();
                  $libel_ecriture->set_traduction($global_langue_utilisateur, _('Transfert de garantie - Reprises de crédit'));
                  $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($val_doss["cpte_subs_gar"], $adsys_pr_cr[$id_prod]['devise']);
                  $comptable[$compteur_mv]["sens"] = SENS_DEBIT;
                  $comptable[$compteur_mv]["montant"] = $val_doss["gar_remb"];
                  $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
                  $comptable[$compteur_mv]["type_operation"] = 220;
                  $comptable[$compteur_mv]["id"] = $compteur_ecr;
                  $comptable[$compteur_mv]["cpte_interne_cli"] = NULL;
                  $compteur_mv++;

                  $comptable[$compteur_mv]["compte"] = checkCptDeviseOK($array_credit["cpte_cpta_prod_cr_gar"], $adsys_pr_cr[$id_prod]['devise']);
                  $comptable[$compteur_mv]["sens"] = SENS_CREDIT;
                  $comptable[$compteur_mv]["montant"] = $val_doss["gar_remb"];


                  $comptable[$compteur_mv]["libel"] = $libel_ecriture->save();
                  $comptable[$compteur_mv]["id"] = $compteur_ecr;
                  $comptable[$compteur_mv]["cpte_interne_cli"] = $id_cpte_en;
                  $compteur_mv++;
                  $compteur_ecr++;
                } /* Fin if($SESSION_VARS["gar_remb"] > 0) */

              } /* Fin if($gar_num_encours > 0) */
            } /* if ($type_radiation_credit == 'f') */

          }//fin de crédit en perte

          if (!empty($comptable)) {
            /* Renseigner les infos communes des mouvements et vérifier si les données obligatoires sont saisis */
            for ($i = 0; $i < $compteur_mv; $i++) {
              /* Renseignement des infos communes des mouvements */
              $comptable[$i]["jou"] = 1;
              $comptable[$i]["date_valeur"] = date("Y-m-d");
              $comptable[$i]["exo"] = $exo;
              $comptable[$i]["validation"] = 't';
              $comptable[$i]["devise"] = $adsys_pr_cr[$id_prod]['devise'];
              $comptable[$i]["date_comptable"] = date("r");

              if ($comptable[$i]["compte"] == '')
                $message = sprintf(_("Le compte de substitut des garanties ou du crédit n'est pas paramétré"));

              if ($comptable[$i]["sens"] == '')
                $message = sprintf(_("Le sens n'est pas renseigné"));

              if ($comptable[$i]["montant"] == NULL)
                $message = sprintf(_("Le montant n'est pas renseigné"));
            }
          }
          // Vérification de la devise
          $devise = $adsys_pr_cr[$id_prod]['devise'];
          if ($devise == '')
            $message = sprintf(_("La devise n'est pas paramétrée"));
        } // Fin parcours dossiers

        if ($exo == '')
          $message = sprintf(_("L'exercice n'est pas renseigné"));

        /* S'il y a des erreurs */
        if (strlen($message) != 0) {
          $dbHandler->closeConnection(false);
          $colb_tableau = '#e0e0ff';
          $msg = new HTML_message(_("Echec activation du dossier de credit"));
          $msg->setMessage($message);
          $msg->addButton(BUTTON_OK, 2);
          $msg->buildHTML();
          echo $msg->HTML_code;
          exit();

        }
        $Verif_transaction = false;
        $result = ajout_historique(503, NULL, NULL, 'administrateur', date("r"), $comptable);
        if ($result->errCode != NO_ERR) {
          /* Génération du message de confirmation */
          $dbHandler->closeConnection(false);
          $colb_tableau = '#e0e0ff';
          $msg = new HTML_message(_("Echec lors de l'activation du dossier de credit"));
          $msg->setMessage(_("Le dossier de crédit n'a pas été activé") . ".<BR>" . $error[$result->errCode] . $result->param);
          $msg->buildHTML();
          echo $msg->HTML_code;
          exit();
        } else {
          /* Génération du message de confirmation */
          $dbHandler->closeConnection(true);
          $Verif_transaction = true;
          $colb_tableau = '#e0e0ff';
          affiche(_("Le dossier de crédit " . $id_doss . " est activé.La prise en charge du crédit est à présent effective dans le logiciel"));
          /*$msg = new HTML_message(_("Confirmation activation dossier de credit"));
          $msg->setMessage(_("Le dossier de crédit  est activé. <BR>La prise en charge du crédit est à présent effective dans le logiciel"));
          $msg->buildHTML();
          echo $msg->HTML_code;*/
        }
      }
      if ($Verif_transaction == true){
        //$dbHandler->closeConnection(true);
        $msg_terminer = new HTML_message(_("Reprise de crédit terminée!!!"));
        $msg_terminer->setMessage(_("Tous les dossiers de crédits ont été repris avec succès!!!"));
        $msg_terminer->addButton(BUTTON_OK, 21);
        $msg_terminer->buildHTML();
        echo $msg_terminer->HTML_code;
        exit();
      }
    }
    else{
      $msg_terminer = new HTML_message(_("Reprise de crédit"));
      $msg_terminer->setMessage(_("Il n'y a pas de dossiers de crédits éligibles pour la reprise"));
      $msg_terminer->addButton(BUTTON_OK, 21);
      $msg_terminer->buildHTML();
      echo $msg_terminer->HTML_code;
      exit();
    }

  }

?>

</body>
</html>
