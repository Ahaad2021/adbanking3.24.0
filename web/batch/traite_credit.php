<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2 */

/**
 * Batch : traitements des crédits
 * @package Systeme
 **/

require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/credit_lcr.php';
require_once 'lib/dbProcedures/client.php';
require_once 'lib/dbProcedures/historique.php';
require_once 'lib/dbProcedures/agence.php';
require_once 'batch/librairie.php';
require_once 'lib/dbProcedures/ferie.php';
require_once 'lib/dbProcedures/compte.php';
require_once 'lib/misc/tableSys.php';
require_once 'lib/misc/divers.php';



/**
 * prelev_auto Prélèvement automatique pour le remboursement d'une échéance d'un DCR
 *
 * @return void
 */
function prelev_auto() {
  global $dbHandler ,$error;
  global $date_total;
  global $date_jour;
  global $date_mois;
  global $date_annee;
  global $global_id_agence;
  global  $global_mouvements_credit;
  global $rembourse_auto;

  $count = 0;
  $countSoldes = 0;
  $INFOSREMBAUTO = array();
  $_SESSION['mode'] = 2;
  $_SESSION['int_cal_traite'] = 0;
  $_SESSION['int_cal'] = 0;

  affiche(_("Prélèvements automatiques ..."));
  incLevel();

  $db = $dbHandler->openConnection();

  //Récupère les crédits dont l'échéance est passée et qui sont soumis au prélèvement automatique
  $date_max = $date_total;
  if (is_ferie($date_jour, $date_mois, $date_annee)) { //Si c'est ferié alors on doit tenir compte de jour ouvrable +1 ou -1
    $report = get_report($global_id_agence);
    if ($report == 2) $date_max = jour_ouvrable($date_jour, $date_mois, $date_annee, 1);
    else if ($report == 3) $date_max = jour_ouvrable($date_jour, $date_mois, $date_annee, -1);
  }

  bloque_mnt_ech_dossier_batch ($date_max); // Bloque et mettre a jour les montants des echeances des dossiers concernes -> Ticket 693

    $sql = "SELECT main.id_doss, main.id_ech, main.date_ech, main.solde_gar, main.solde_pen, main.solde_int, main.solde_cap, main.id_client, main.cre_id_cpte, main.cpt_liaison, main.etat,main.differe_ech, main.etat_client, main.is_ligne_credit, main.cre_mnt_bloq, main.mnt_bloq_cre FROM (select A.*,case when lag(solde_apres,1) over (partition by id_doss order by id_ech) <= 0 then  row_number() over(partition by id_doss order by id_ech) end as test from (SELECT a.id_doss, id_ech, date_ech, solde_gar, solde_pen, solde_int, solde_cap, b.id_client, b.cre_id_cpte, b.cpt_liaison, b.etat,b.differe_ech, c.etat as etat_client, b.is_ligne_credit, b.cre_mnt_bloq, cpt.mnt_bloq_cre, coalesce(solde_gar,0) + coalesce(solde_pen,0) +coalesce(solde_int,0) + coalesce(solde_cap,0) as total_remb, ROUND(cpt.solde - coalesce(cpt.mnt_bloq,0) - coalesce(cpt.mnt_min_cpte,0) + coalesce(cpt.decouvert_max,0)) as solde_dispo, sum(coalesce(solde_gar,0) + coalesce(solde_pen,0) +coalesce(solde_int,0) + coalesce(solde_cap,0)) OVER (PARTITION BY a.id_doss ORDER BY id_ech ) as cumul, ROUND(cpt.solde - coalesce(cpt.mnt_bloq,0) - coalesce(cpt.mnt_min_cpte,0) + coalesce(cpt.decouvert_max,0)) - sum(coalesce(solde_gar,0) + coalesce(solde_pen,0) +coalesce(solde_int,0) + coalesce(solde_cap,0)) OVER (PARTITION BY a.id_doss ORDER BY id_ech ) as  solde_apres FROM ad_etr a INNER JOIN ad_dcr b on a.id_ag = b.id_ag and a.id_doss = b.id_doss INNER JOIN ad_cli c on b.id_ag = c.id_ag and b.id_client = c.id_client INNER JOIN ad_cpt cpt on b.id_ag = cpt.id_ag and b.cpt_liaison = cpt.id_cpte WHERE b.prelev_auto = TRUE AND (a.date_ech <= '$date_max') AND (a.remb = 'f') AND (b.etat = 5 OR b.etat = 7 OR b.etat = 9 OR b.etat = 13 OR b.etat = 14 OR b.etat = 15) AND ROUND(cpt.solde - coalesce(cpt.mnt_bloq,0) - coalesce(cpt.mnt_min_cpte,0) + coalesce(cpt.decouvert_max,0)) > 0 ORDER BY b.id_doss, a.date_ech) A) main WHERE main.test is null ORDER BY main.id_client ASC, main.id_doss ASC, main.id_ech ASC;";

  $result = $db->query($sql);
  if (DB::isError($result)){
	erreur("prelev_auto()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
	$dbHandler->closeConnection(false);  	
  }

  $id_doss = -1;
  $cpt_liaison = -1;
  $cpt_mnt_bloq_cre = 0;
  $solde_dispo = array();

  $DCR = $Produitx = $PROD = $DEV = $array_credit = $cpta_debit = $cpta_credit_gar = $CPTS_ETAT = NULL;

  $id_etat_perte = getIDEtatPerte();

  // Drop trigger trig_before_update_ad_cpt_mnt_bloq_cre
  $sql_drop_trigger = "DROP TRIGGER IF EXISTS trig_before_update_ad_cpt_mnt_bloq_cre ON ad_cpt;";

  $result_drop_trigger = $db->query($sql_drop_trigger);
  if (DB::isError($result_drop_trigger)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql_dcr."\n".$result->getMessage());
  }




  //Pour chaque échéance passée (trié par ordre chronologique puis par dossier)
  while ($credit = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    

    if ($id_doss != $credit['id_doss']) {
      // Si ce n'est pas le même dossier de crédit
      $id_client = $credit['id_client'];
      $etat = $credit['etat_client'];
      $id_doss = $credit['id_doss'];
      $id_ech_reup = $credit['id_ech'];
      $_SESSION['int_cal'] = get_calcInt_cpteInt(true, false,$id_doss,$id_ech_reup);
      $dcr_cre_mnt_bloq = $credit['cre_mnt_bloq'];
      if ($cpt_liaison != $credit['cpt_liaison']) {
        $cpt_liaison = $credit['cpt_liaison'];
        $cpt_mnt_bloq_cre = $credit['mnt_bloq_cre'];
        /*$solde_dispo[$cpt_liaison] = getSoldeDisponible($cpt_liaison);
        if ($solde_dispo[$cpt_liaison] > 0 && $dcr_cre_mnt_bloq > 0) {
          $solde_dispo[$cpt_liaison] += $dcr_cre_mnt_bloq;
        }*/
      }
      if (!isset($solde_dispo[$cpt_liaison])) {
        $solde_dispo[$cpt_liaison] = getSoldeDisponible($cpt_liaison);
      }

      if ($solde_dispo[$cpt_liaison] > 0 && $dcr_cre_mnt_bloq > 0) {
        $solde_dispo[$cpt_liaison] += $dcr_cre_mnt_bloq;
      }
      if ($solde_dispo[$cpt_liaison] == 0 && $dcr_cre_mnt_bloq > 0) {
        $solde_dispo[$cpt_liaison] = $dcr_cre_mnt_bloq;
      }
      if ($solde_dispo[$cpt_liaison] > 0 && $dcr_cre_mnt_bloq == 0) {
        $solde_dispo[$cpt_liaison] = $solde_dispo[$cpt_liaison];
      }


      //$cpt_mnt_bloq_cre = $credit['mnt_bloq_cre'];
      $differe_ech = $credit['differe_ech'];

      $DCR = getDossierCrdtInfo($id_doss);
      $Produitx = getProdInfo(" where id =".$DCR["id_prod"], $id_doss);
      $PROD = $Produitx[0];
      $devise = $PROD["devise"];
      $DEV = getInfoDevise($devise);

      $array_credit = getCompteCptaDcr($id_doss);
      $cpta_debit = getCompteCptaProdEp($cpt_liaison);
      if (isset($DCR['cpt_gar_encours']) && $DCR['cpt_gar_encours'] != NULL) {
        $cpta_credit_gar = getCompteCptaProdEp($DCR['cpt_gar_encours']);
      }
      $CPTS_ETAT = recup_compte_etat_credit($DCR["id_prod"]);

      $has_mnt_bloq_cre = FALSE;
    }


    /* Récupération du solde du compte lié au crédit*/
    /*if ($solde_dispo[$cpt_liaison] == 0 && $dcr_cre_mnt_bloq > 0) {
      $solde_dispo[$cpt_liaison] = $dcr_cre_mnt_bloq;
    }
    if (!isset($solde_dispo[$cpt_liaison])) {
      $solde_dispo[$cpt_liaison] = getSoldeDisponible($cpt_liaison);

      $solde_dispo[$cpt_liaison] += $dcr_cre_mnt_bloq;
    }*/


    // Assign ech montant bloqué
    if ($dcr_cre_mnt_bloq > 0 && ($cpt_mnt_bloq_cre - $dcr_cre_mnt_bloq) >= 0) {
      $has_mnt_bloq_cre = TRUE;
    }

    if ($solde_dispo[$cpt_liaison] > 0 && $etat == 2) {
    	++$count;

        if ($credit['is_ligne_credit'] == 't') {
            $info['solde_frais'] = getCalculFraisLcr($id_doss, php2pg(demain($date_max)));
            
            $total_credit = round($credit['solde_pen']+$info['solde_frais']+$credit['solde_int']+$credit['solde_cap']+$credit["solde_gar"], EPSILON_PRECISION);
            $func_sys_remb_credit = 607;
        } else {
          if($DCR["interet_remb_anticipe"] > 0){
            $total_credit = round($DCR["interet_remb_anticipe"],$credit['solde_pen']+$credit['solde_int']+$credit['solde_cap']+$credit["solde_gar"], EPSILON_PRECISION);
          }else{
            $total_credit = round($credit['solde_pen']+$credit['solde_int']+$credit['solde_cap']+$credit["solde_gar"], EPSILON_PRECISION);
          }
            $func_sys_remb_credit = 147;
        }

    	// On rembourse la totalité des échéances dues si le montant disponible est suffisant, sinon on ne rembourse qu'à concurence du montant disponible.
    	$mnt_rembours = min($solde_dispo[$cpt_liaison], $total_credit);

        if (($mnt_rembours > 0) || ($mnt_rembours >=0 && $differe_ech >0)) {
          if($credit['etat'] != 9){
            if ($credit['is_ligne_credit'] == 't') {
              $myErr = rembourse_lcr($id_doss, $mnt_rembours, 2, $global_mouvements_credit, NULL, $date_max);
            } else {

              $myErr = rembourse($id_doss, $mnt_rembours, 2, $global_mouvements_credit, NULL, NULL, NULL, $credit['id_ech'], $date_max, NULL, $DCR, $Produitx, $DEV, $array_credit, $cpta_debit, $cpta_credit_gar, $CPTS_ETAT, $id_etat_perte);
              if ($myErr->errCode == NO_ERR) {
                array_push($INFOSREMBAUTO,$myErr->param);
              }
            }
          }else{
            $myErr = recouvrementCreditPerte($id_doss, $mnt_rembours, $global_mouvements_credit, $func_sys_remb_credit);
          }
        }

    	if ($myErr->errCode != NO_ERR) {
          $msg_erreur = _("Traitement du client $id_client, dossier $id_doss, ");
          if ($myErr->errCode == ERR_SOLDE_INSUFFISANT)
            $msg_erreur .= _("le solde du compte lié est insuffisant !");
          else
            $msg_erreur .= $error[$myErr->errCode] . ':' .$myErr->param;
           $dbHandler->closeConnection(false);
           erreur("prelev_auto()", $msg_erreur);

        } else { // Pas d'erreur
          $solde_dispo[$cpt_liaison] -= $mnt_rembours;

          if ($has_mnt_bloq_cre == TRUE) {
            $dcr_cre_mnt_bloq -= $mnt_rembours;

            // Fix negative values
            if ($dcr_cre_mnt_bloq < 0) {
              $dcr_cre_mnt_bloq = 0;
            }

            $sql_dcr = "UPDATE ad_dcr SET cre_mnt_bloq = $dcr_cre_mnt_bloq WHERE id_ag = $global_id_agence AND id_doss = $id_doss";

            $result_dcr = $db->query($sql_dcr);
            if (DB::isError($result_dcr)) {
              $dbHandler->closeConnection(false);
              signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql_dcr."\n".$result->getMessage());
            }

            $cpt_mnt_bloq_cre -= $mnt_rembours;

            // Fix negative values
            if ($cpt_mnt_bloq_cre < 0) {
              $cpt_mnt_bloq_cre = 0;
            }

            $sql_cpt = "UPDATE ad_cpt SET mnt_bloq_cre = $cpt_mnt_bloq_cre WHERE id_ag = $global_id_agence AND id_cpte = $cpt_liaison";

            $result_cpt = $db->query($sql_cpt);
            if (DB::isError($result_cpt)) {
              $dbHandler->closeConnection(false);
              signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql_cpt."\n".$result->getMessage());
            }
          }

          if ($myErr->param['result'] == 2)
            $countSoldes++;
          array_push($rembourse_auto, $credit);
          $INFOSREMB = $myErr->param;
        }
    }
  } //end while credit

  // Create trigger trig_before_update_ad_cpt_mnt_bloq_cre
  $sql_recreate_trigger = "CREATE TRIGGER trig_before_update_ad_cpt_mnt_bloq_cre BEFORE UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_before_update_ad_cpt_mnt_bloq_cre();";

  $result_recreate_trigger = $db->query($sql_recreate_trigger);
  if (DB::isError($result_recreate_trigger)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql_dcr."\n".$result->getMessage());
  }

  affiche(sprintf(_("OK (%s prélèvements effectués et %s crédits soldés"),$count,$countSoldes), true);
  decLevel();
  affiche(_("Prélèvements automatiques terminés !"));

  $dbHandler->closeConnection(true);
  return new ErrorObj(NO_ERR, $INFOSREMBAUTO);
}

/**
 * update_interets Met à jour l'intérêt attendu sur les dossiers de crédit
 *
 * @access public
 * @return void
 */
function update_interets() {
  // Seuls les DCR d'un produit dont le mode de calcul des intérêts est 3 (Dégressif variable) seront considérés ici

  global $dbHandler;
  global $date_total;
  global $global_id_agence;
  global $global_monnaie_prec,$global_monnaie_courante_prec;

  affiche(_("Mise à jour des intérêts ..."));
  incLevel();

  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();
  // Récupération du nombre de jours par an
  $AGC = getAgenceDatas($global_id_agence);
  if ($AGC["base_taux"] == 1) // 360 jours
    $nbre_jours_an = 360;
  else   if ($AGC["base_taux"] == 2) // 365 jours
    $nbre_jours_an = 365;

  // Récupération de tous les DCR concernés
  $sql = "SELECT a.id_doss, a.id_prod , a.type_duree_credit, a.devise, a.tx_interet ";
  $sql .= "FROM get_ad_dcr_ext_credit(null, null, 5, null, $global_id_agence) a WHERE a.etat = 5 AND a.mode_calc_int = 3";

  //FIXME : il faut dès le départ enlever les dossiers pour lesquels il n'y a aucune échéance à considérer
  $result = $db->query($sql);
  if (DB::isError($result)) erreur("update_interets()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  $count = 0;
  while ($doss = $result->fetchrow()) {
    $count++;
    $id_doss = $doss[0];
    $id_prod = $doss[1];
    $type_duree_credit = $doss[2];
    //$DOSS = getDossierCrdtInfo($id_doss);
    //$devise = $DOSS["devise"];
    $devise = $doss[3];
    $tx_interet = $doss[4];

    setMonnaieCourante($devise);
    // Recherche de l'échéance courante
    $sql = " SELECT id_ech, remb FROM ad_etr WHERE date_ech > '$date_total' AND id_doss = $id_doss ORDER BY id_ech LIMIT 1";
    $result2 = $db->query($sql);
    if (DB::isError($result2))
      erreur("update_interets()", _("La requête ne s'est pas exécutée correctement")." : ".$result2->getMessage());
    if ($result2->numRows() != 0) {
      $ech = $result2->fetchrow();
      $id_ech = $ech[0];
      if ($ech[1] != 't') {
        // Recherche du capital restant dû pour ce dossier
        $sql = " SELECT sum(solde_cap) FROM ad_etr WHERE id_doss = $id_doss";
        $result2 = $db->query($sql);
        if (DB::isError($result2)) erreur("update_interets()", _("La requête ne s'est pas exécutée correctement")." : ".$result2->getMessage());
        $solde_cap = $result2->fetchrow();
        $solde_cap = $solde_cap[0];

        // Ajout de l'intérêt
        if ($type_duree_credit == 2)
          $interet = round(($tx_interet / 7) * $solde_cap, $global_monnaie_courante_prec);
        else
          $interet = round( ($tx_interet/$nbre_jours_an) * $solde_cap, $global_monnaie_courante_prec);
        $sql = "UPDATE ad_etr SET solde_int = solde_int + NUMERIC '".$interet."' WHERE id_ag=$global_id_agence AND id_doss = $id_doss AND id_ech = $id_ech";
        $result2 = $db->query($sql);
        if (DB::isError($result2)) erreur("update_interets()", _("La requête ne s'est pas exécutée correctement")." : ".$result2->getMessage());
      }
    }

  }

  affiche(sprintf(_("OK (%s dossiers de crédit)"),$count), true);

  decLevel();
  affiche(_("Mise à jour des intérêts terminée !"));

  $dbHandler->closeConnection(true);
}

/**
 * calcul_penalites Calcule les pénalités dues sur un DCR
 *
 * @access public
 * @return void
 */
function calcul_penalites() {
  global $dbHandler;
  global $date_total;
  global $date_jour;
  global $date_mois;
  global $date_annee;
  global $global_id_agence;
  global $adsys;

  affiche(_("Calcul des pénalités ..."));
  incLevel();

  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();

  //Récupère les crédits en retard, en souffrance mais pas en perte (dans ce cas on ne calcule plus de pénalités)
  $date_max = $date_total;
  if (is_ferie($date_jour, $date_mois, $date_annee)) { //Si c'est ferié alors on doit tenir compte de jour ouvrable +1 ou -1
    $report = get_report($global_id_agence);
    if ($report == 2) $date_max = jour_ouvrable($date_jour, $date_mois, $date_annee, 1);
    else if ($report == 3) $date_max = jour_ouvrable($date_jour, $date_mois, $date_annee, -1);
  }

  $id_perte = getIDEtatPerte();
  $AGC = getAgenceDatas($global_id_agence);

  //Recherche de toutes les échéances non remboursées
  $sql = "SELECT e.id_doss, e.id_ech, e.date_ech, e.solde_pen, e.solde_cap, solde_int, e.mnt_cap, mnt_int ";
  $sql .= ", d.devise, d.delai_grac, d.max_jours_compt_penalite, d.id_prod, dv.precision, d.cre_etat, cli.etat as
  etat_client, d.suspension_pen";
  $sql .= ", d.id, d.tx_interet, d.mode_calc_int, d.type_duree_credit, d.mnt_penalite_jour, d.prc_penalite_retard, d.typ_pen_pourc_dcr, d.delai_grace_jour";
  $sql .= " FROM ad_etr e INNER JOIN get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) d ON e.id_doss = d.id_doss AND e.id_ag = d.id_ag";
  $sql .= " INNER JOIN devise dv ON d.devise = dv.code_devise AND d.id_ag = dv.id_ag";
  $sql .= " INNER JOIN ad_cli cli ON d.id_client = cli.id_client AND d.id_ag = cli.id_ag";
  $sql .= " WHERE (e.date_ech <= '$date_max') AND (e.remb = 'f')";

  if ($AGC ["calcul_penalites_credit_radie"] != 't') {
    $sql .= " AND d.etat <> 9  ";
  }

  $result = $db->query($sql);
  if (DB::isError($result)) {
    erreur("calcul_penalites()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }

  $count = 0; //Compteur du nombre d'échéances en retard (pour afficher)

  while ($credit = $result->fetchrow(DB_FETCHMODE_ASSOC)) { //Pour chaque échéance passée
    //Calculs dates

    // Le delai de grace doit être pris ur le dossier de credit et non sur le produit de credit **
    $produit 	= $credit['id_prod'];
    $ts 		= getPhpDateTimestamp($credit['date_ech']);
    $ts2 		= mktime(0,0,0,$date_mois,$date_jour-$credit["delai_grac"],$date_annee);
    $ts3 		= mktime(0,0,0,$date_mois,$date_jour-$credit["max_jours_compt_penalite"],$date_annee);
    
    // Recherche l'état du crédit, du flag de suspension et de l'état du client
    $etat_credit = $credit['cre_etat'];
    $etat_client = $credit['etat_client'];
    $suspendu = $credit['suspension_pen'];

    // Condition de décompte des pénalités
    //  * Crédit pas en perte
    //  * Echéance en retard (en tenant compte du délai de grâce)
    //  * Client actif
    //  * Pénalités non suspendues

			// if ((($etat_credit < $id_perte) || $AGC["calcul_penalites_credit_radie"]=='t') && (date("Y/m/d", $ts) <= date("Y/m/d", $ts2)) && $etat_client == 2 && $suspendu == 'f'&& (date("Y/m/d", $ts) < date("Y/m/d", $ts3)) ) {
			// ticket 442 part 1: max jour compte penalié>0 and ts> ts3
		if ((($etat_credit < $id_perte) || $AGC ["calcul_penalites_credit_radie"] == 't') && (date ( "Y/m/d", $ts ) <= date ( "Y/m/d", $ts2 )) && $etat_client == 2 && $suspendu == 'f' && $credit ["max_jours_compt_penalite"] > 0 && (date ( "Y/m/d", $ts ) > date ( "Y/m/d", $ts3 ))) {
			
			++ $count;
			// Calcul montant des pénalités
			if ($credit['mode_calc_int'] == 3) {
				// Cas particulier mode dégressif
				// On continue à appliquer le taux d'intérêts journalisé et on y ajoute les pénalités si elles sont définies
				
				// Récupération du nombre de jours par an
				if ($AGC ["base_taux"] == 1) // 360 jours
					$nbre_jours_an = 360;
				else if ($AGC ["base_taux"] == 2) // 365 jours
					$nbre_jours_an = 365;
					
					// Calcul du montant sur lequel on applique la pénalité en pourcentage, en fonction du type de pénalité du produit
				if ($credit['typ_pen_pourc_dcr'] == 1) {
					// Récupération du capital restant dû pour le crédit
					$sql = " SELECT sum(solde_cap) FROM ad_etr WHERE id_doss = " . $credit ['id_doss'];
					$result2 = $db->query ( $sql );
					if (DB::isError ( $result2 ))
						erreur ( "calcul_penalites()", _ ( "La requête ne s'est pas exécutée correctement" ) . " : " . $result2->getMessage () );
					$row = $result2->fetchrow ();
					
					$mnt_calc_pen = $row [0];
				} elseif ($credit['typ_pen_pourc_dcr'] == 2) {
					// Calcul sur le solde de l'échéance capital et intérêts
					$mnt_calc_pen = $credit ['solde_cap'] + $credit ['solde_int']; // ? intéret connu
				} else {
					// Pas de pénalité
					$mnt_calc_pen = 0;
				}
				
				// Calcul du montant de la pénalité
				if ($credit['type_duree_credit'] == 2) {
					// d'abord le cas des échéanciers hebdomadaires; le taux d'intéret est exprimé par semaine
					$ajout = round ( ($credit['prc_penalite_retard'] / 7 * $mnt_calc_pen) + $credit['mnt_penalite_jour'] + ($credit['tx_interet'] / 7 * $credit ['solde_cap']), $credit ["precision"] );
				} else // ['type_duree_credit'] == 1
				     // le cas général autre que échéanciers hebdos, le taux d'intérêts est annuel
					$ajout = round ( ($credit['prc_penalite_retard'] / $nbre_jours_an * $mnt_calc_pen) + $credit['mnt_penalite_jour'] + ($credit['tx_interet'] / $nbre_jours_an * $credit ['solde_cap']), $credit ['precision'] );
			} else { // Cas général autre que dégressif variable
			         
				// Quel type de pénalité en %
				if ($credit['typ_pen_pourc_dcr'] == 1) {
					// Pénalités sur le capital restant dû uniquement
					$sql = " SELECT sum(solde_cap) FROM ad_etr WHERE id_doss = " . $credit ['id_doss'];
					$result2 = $db->query ( $sql );
					if (DB::isError ( $result2 ))
						erreur ( "update_interets()", _ ( "La requête ne s'est pas exécutée correctement" ) . " : " . $result2->getMessage () );
					$row = $result2->fetchrow ();
					$mnt_calc_pen = $row [0];
				} elseif ($credit['typ_pen_pourc_dcr'] == 2) {
					// Pénalités sur l'échéance complète capital + intérêts
					$mnt_calc_pen = $credit ['solde_cap'] + $credit ['solde_int'];
				} else {
					// Pas de pénalités proportionnelles
					$mnt_calc_pen = 0;
				}
				
				if ($credit['type_duree_credit'] == 2) { // échéanciers hebdos
					$ajout = round ( $credit['mnt_penalite_jour'] + ($credit['prc_penalite_retard'] / 7 * $mnt_calc_pen), $credit ['precision'] );
				}				// type duree credit = 1 ticket 442
				elseif ($credit['type_duree_credit'] == 1) { // échéanciers en terme de mois
					$ajout = round ( $credit['mnt_penalite_jour'] + ($credit['prc_penalite_retard'] / 30 * $mnt_calc_pen), $credit ['precision'] );
				} else { // pas de pénalité proportionnelles ,peut être fixe (Peut-on vraiment arriver dans ce cas ? - antoine)
					$ajout = $credit['mnt_penalite_jour'];
				}
			} // fin calcul des pénalités dégressif variable, constant, dégressif simple
			  
			// Met à jour pénalité
			$new_pen = $credit ['solde_pen'];
			$new_pen += $ajout;
			
			$sql = "UPDATE ad_etr SET solde_pen=$new_pen ";
			$sql .= "WHERE (id_doss=" . $credit ['id_doss'] . ") ";
			$sql .= "AND id_ag=$global_id_agence AND (id_ech=" . $credit ['id_ech'] . ")";
			$result2 = $db->query ( $sql );
			if (DB::isError ( $result2 ))
				erreur ( "calcul_penalites()", _ ( "La requête ne s'est pas exécutée correctement" ) . " : " . $result2->getMessage () );
		}		// end if calcul montant pénalité
			  
		// ticket 442 part2: traitement pour le cas where max jour compte penalité==0 and ts<ts3(ie a -ve value)
		else if ((($etat_credit < $id_perte) || $AGC ["calcul_penalites_credit_radie"] == 't') && (date ( "Y/m/d", $ts ) <= date ( "Y/m/d", $ts2 )) && $etat_client == 2 && $suspendu == 'f' && $credit ["max_jours_compt_penalite"] == 0 && (date ( "Y/m/d", $ts ) < date ( "Y/m/d", $ts3 ))) {
			
			++ $count;
			// Calcul montant des pénalités
			if ($credit['mode_calc_int'] == 3) {
				// Cas particulier mode dégressif
				// On continue à appliquer le taux d'intérêts journalisé et on y ajoute les pénalités si elles sont définies
				
				// Récupération du nombre de jours par an
				if ($AGC ["base_taux"] == 1) // 360 jours
					$nbre_jours_an = 360;
				else if ($AGC ["base_taux"] == 2) // 365 jours
					$nbre_jours_an = 365;
					
					// Calcul du montant sur lequel on applique la pénalité en pourcentage, en fonction du type de pénalité du produit
				if ($credit['typ_pen_pourc_dcr'] == 1) {
					// Récupération du capital restant dû pour le crédit
					$sql = " SELECT sum(solde_cap) FROM ad_etr WHERE id_doss = " . $credit ['id_doss'];
					$result2 = $db->query ( $sql );
					if (DB::isError ( $result2 ))
						erreur ( "calcul_penalites()", _ ( "La requête ne s'est pas exécutée correctement" ) . " : " . $result2->getMessage () );
					$row = $result2->fetchrow ();
					
					$mnt_calc_pen = $row [0];
				} elseif ($credit['typ_pen_pourc_dcr'] == 2) {
					// Calcul sur le solde de l'échéance capital et intérêts
					$mnt_calc_pen = $credit ['solde_cap'] + $credit ['solde_int']; // ? intéret connu
				} else {
					// Pas de pénalité
					$mnt_calc_pen = 0;
				}
				
				// Calcul du montant de la pénalité
				if ($credit['type_duree_credit'] == 2) {
					// d'abord le cas des échéanciers hebdomadaires; le taux d'intéret est exprimé par semaine
					$ajout = round ( ($credit['prc_penalite_retard'] / 7 * $mnt_calc_pen) + $credit['mnt_penalite_jour'] + ($credit['tx_interet'] / 7 * $credit ['solde_cap']), $credit ['precision'] );
				} else // ['type_duree_credit'] == 1
				     // le cas général autre que échéanciers hebdos, le taux d'intérêts est annuel
					$ajout = round ( ($credit['prc_penalite_retard'] / $nbre_jours_an * $mnt_calc_pen) + $credit['mnt_penalite_jour'] + ($credit['tx_interet'] / $nbre_jours_an * $credit ['solde_cap']), $credit ['precision'] );
			} else { // Cas général autre que dégressif variable
			         
				// Quel type de pénalité en %
				if ($credit['typ_pen_pourc_dcr'] == 1) {
					// Pénalités sur le capital restant dû uniquement
					$sql = " SELECT sum(solde_cap) FROM ad_etr WHERE id_doss = " . $credit ['id_doss'];
					$result2 = $db->query ( $sql );
					if (DB::isError ( $result2 ))
						erreur ( "update_interets()", _ ( "La requête ne s'est pas exécutée correctement" ) . " : " . $result2->getMessage () );
					$row = $result2->fetchrow ();
					$mnt_calc_pen = $row [0];
				} elseif ($credit['typ_pen_pourc_dcr'] == 2) {
					// Pénalités sur l'échéance complète capital + intérêts
					$mnt_calc_pen = $credit ['solde_cap'] + $credit ['solde_int'];
				} else {
					// Pas de pénalités proportionnelles
					$mnt_calc_pen = 0;
				}
				
				if ($credit['type_duree_credit'] == 2) { // échéanciers hebdos
					$ajout = round ( $credit['mnt_penalite_jour'] + ($credit['prc_penalite_retard'] / 7 * $mnt_calc_pen), $credit ['precision'] );
				}				// type duree credit = 1 ticket 442
				elseif ($credit['type_duree_credit'] == 1) { // échéanciers en terme de mois
					$ajout = round ( $credit['mnt_penalite_jour'] + ($credit['prc_penalite_retard'] / 30 * $mnt_calc_pen), $credit ['precision'] );
				} else { // pas de pénalité proportionnelles ,peut être fixe (Peut-on vraiment arriver dans ce cas ? - antoine)
					$ajout = $credit['mnt_penalite_jour'];
				}
			} // fin calcul des pénalités dégressif variable, constant, dégressif simple
			  
			// Met à jour pénalité
			$new_pen = $credit ['solde_pen'];
			$new_pen += $ajout;
			$sql = "UPDATE ad_etr SET solde_pen=$new_pen ";
			$sql .= "WHERE (id_doss=" . $credit ['id_doss'] . ") ";
			$sql .= "AND id_ag=$global_id_agence AND (id_ech=" . $credit ['id_ech'] . ")";
			$result2 = $db->query ( $sql );
			if (DB::isError ( $result2 ))
				erreur ( "calcul_penalites()", _ ( "La requête ne s'est pas exécutée correctement" ) . " : " . $result2->getMessage () );
		}
	} // end while credit

  affiche("OK ($count échéances)", true);
  decLevel();
  affiche("Calcul des pénalités terminé !");

  $dbHandler->closeConnection(true);
}

/**
 * update_etat_dossier Met à jour l'état d'un dossier (et les transferts des comptes comptables)
 *
 * @param mixed $id_doss Identifiant du DCR
 * @param mixed $etat_courant Etat courant du DCR
 * @param mixed $new_etat Nouvel état du DCR
 * @param mixed $devise La devise du DCR
 * @access public
 * @return int La valeur courante du solde du DCR
 */
function update_etat_dossier($id_doss, $etat_courant, $new_etat, $devise) {
  global $dbHandler;
  global  $global_mouvements_credit;
  global $adsys;
  global $error,$global_id_agence;
  global $date_total;//501/512

  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();
  $id_etat_perte = getIDEtatPerte();
  $infos_ag = getAgenceDatas($global_id_agence);
  if ($new_etat == $id_etat_perte) { // Si on passe en perte, il y a un traitement particulier à effectuer
    if ($infos_ag['passage_perte_automatique'] == "t")
      $myErr = passagePerte($id_doss,  $global_mouvements_credit , $date_total); 
    if ($myErr->errCode != NO_ERR)
      erreur("update_etat_dossier()", sprintf(_("Erreur lors du passage en perte du dossier '%s' : Erreur '%s', Message : '%s', Paramètre : '%s'"),$id_doss, $myErr->errCode,$error[$myErr->errCode],$myErr->param));
  } else {             // Cas général
    //Recherche le montant du capital qui reste à rembourser
    $sql = "SELECT cre_id_cpte FROM ad_dcr WHERE id_doss=$id_doss";
    $result = $db->query($sql);
    if (DB::isError($result))
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $id_cpte = $row['cre_id_cpte'];

    $sql = "SELECT solde, id_titulaire FROM ad_cpt WHERE id_cpte=$id_cpte";
    $result = $db->query($sql);
    if (DB::isError($result))
      signalErreur(__FILE__,__LINE__,__FUNCTION__, _("La requête ne s'est pas exécutée correctement")." :".$result->getMessage());

    $row = $result->fetchrow(DB_FETCHMODE_ASSOC);
    $solde = abs($row['solde']);
    $id_client = $row["id_titulaire"];
    $myErr = placeCapitalCredit($id_doss, $etat_courant, $new_etat,  $global_mouvements_credit, $devise);
    if ($myErr->errCode != NO_ERR) {
      signalErreur(__FILE__,__LINE__,__FUNCTION__, $error[$myErr->errCode].$myErr->param.' - $id_doss='.$id_doss.', $etat_courant='.$etat_courant.', $new_etat='.$new_etat.', $global_mouvements_credit='.var_dump($global_mouvements_credit).', $devise='.$devise);
      $dbHandler->closeConnection(false);
    }

    //Met à jour l'état
    $row = getDossierCrdtInfo($id_doss);
    if ($row['cre_retard_etat_max']<$new_etat) {
      $sql = "UPDATE ad_dcr SET cre_retard_etat_max =". $new_etat." where id_ag=$global_id_agence AND id_doss =". $id_doss;
      $result3 = $db->query($sql);
      if (DB::isError($result3))
        signalErreur(__FILE__,__LINE__,__FUNCTION__, _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
    }
    $sql = "UPDATE ad_dcr SET cre_etat=$new_etat, cre_date_etat = now() WHERE id_ag=$global_id_agence AND id_doss=$id_doss";
    $result2 = $db->query($sql);
    if (DB::isError($result2)) erreur("update_etat_dossier()", _("La requête ne s'est pas exécutée correctement")." : ".$result2->getMessage());

    echo sprintf(_("Le dossier %s est passé à l'état %s"),$id_doss,$new_etat);
  }
  $dbHandler->closeConnection(true);
  return($solde);
}

/**
 * update_etat Met à jour les états de tous les DCR
 *
 * @access public
 * @return void
 */
function update_etat() {
  global $dbHandler;
  global $date_total;
  global $date_jour;
  global $date_mois;
  global $date_annee;
  global $adsys;
  global $declasse_credit,$global_id_agence;

  $db = $dbHandler->openConnection();
  $global_id_agence=getNumAgence();
  affiche(_("Mise à jour de l'état des crédits ..."));
  incLevel();
  $date_max = $date_total;

  //Recherche toutes les échéances qui sont au minimum en retard
  $date_curr = demain($date_jour."/".$date_mois."/".$date_annee);

  $sql = "SELECT d.id_doss, d.cre_etat, d.cre_retard_etat_max, d.cre_retard_etat_max_jour, calculnombrejoursretardech(d.id_doss, e.id_ech,'$date_curr',$global_id_agence) AS nb_jours_retard_new, calculetatcredit(d.id_doss,'$date_curr',$global_id_agence) AS cre_etat_new, d.devise ";
  $sql .= " FROM get_ad_dcr_ext_credit(null, null, null, null, $global_id_agence) d INNER JOIN  ";
  $sql .= " (SELECT id_ag, id_doss, min(id_ech) AS id_ech FROM ad_etr WHERE (date_ech <= '$date_max') AND (remb = 'f') GROUP BY id_ag, id_doss) e ON e.id_doss = d.id_doss AND e.id_ag = d.id_ag ";
  $sql .= " WHERE d.etat NOT IN (1,2,3,4,6,9,10,12) "; // exclure les crédits en attente, non déboursé, rejeté, annulé, soldé, perte, en cours de reprise, supprimé
  $sql .= " ORDER BY d.id_doss;";

  $result = $db->query($sql);

  if (DB::isError($result)) {
    erreur("update_etat()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
  }

  $count = 0; //Compteur
  $id = -1;
  $credits_declasses = array();
  $idDossiersATraiter = array();

  while ($credit = $result->fetchrow(DB_FETCHMODE_ASSOC)) { //Pour chaque échéance passée
    if ($id != $credit['id_doss']) { //On traite uniquement par rapport à l'échéance la plus en retard
      $id = $credit['id_doss'];
      $idDossiersATraiter[] = $id; // Gardes les id dans un array

      //Calcule le nombre de jours de retard
      $nbre_jours = $credit["nb_jours_retard_new"];

      //Calcule l'état du crédit
      $devise = $credit["devise"];
      if ($credit['cre_retard_etat_max'] > 1 && $credit['cre_retard_etat_max_jour'] < $nbre_jours) {
        $sql = "UPDATE ad_dcr set cre_retard_etat_max_jour = ".$nbre_jours." where id_ag=$global_id_agence AND id_doss = ".$credit['id_doss'];
        $result4 = $db->query($sql);
        if (DB::isError($result4)){
            erreur("update_etat_dossier()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
        }
      }

      $etat = $credit["cre_etat_new"];
      $etat_courant = $credit["cre_etat"];

      // FIXME Seconde expression du if toujours false si prem!re true non ?
      if (($etat_courant != $etat) && ($etat_courant < getIDEtatPerte()))
      { //Vérifie si l'état a changé et que l'on était pas en perte (car dans ce cas on le reste)
        $credits_declasses['solde'] = update_etat_dossier($credit["id_doss"], $etat_courant, $etat, $devise);

        // Construction de la liste pour le rapport compte rendu batch
        $credits_declasses['id_doss'] = $credit["id_doss"];
        $credits_declasses['etat_courant'] = $etat_courant;
        $credits_declasses['etat_nouveau'] = $etat;
        array_push($declasse_credit, $credits_declasses);

        ++$count;
      }

    }
  }  
  
  // #357 : équilibre inventaire - comptabilité
  /*
  $cre_id_cpte_array = array();
  $gar_num_id_cpte_nantie_array = array();
  foreach ($idDossiersATraiter as $id_doss)
  {
	  	// Update le num_cpt comptable pour le compte interne associe au produit de credit
	  	$sql = "SELECT cre_id_cpte FROM ad_dcr WHERE id_doss = '$id_doss'";
	  	
	  	$result = $db->query ( $sql );
	  	if (DB::isError($result)) {
	  		erreur("update_etat()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
	  	}	  
	  	$row = $result->fetchrow (DB_FETCHMODE_ASSOC);	  
	  	$cre_id_cpte = $row['cre_id_cpte'];
	  	$myErr = setNumCpteComptableForCompte ($cre_id_cpte, $db);
	  	
	  	// @todo: delete TEST
	  	$cre_id_cpte_array[] = $cre_id_cpte;
	  	
	  	// Update le num_cpt comptable pour le compte interne associe au garantie
	  	$sql = "SELECT gar_num_id_cpte_nantie FROM ad_gar WHERE id_doss = '$id_doss'";
	  	$result = $db->query ( $sql );
  		if (DB::isError($result)) {
	  		erreur("update_etat()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());
	  	}	
	  	$row = $result->fetchrow (DB_FETCHMODE_ASSOC);	  
	  	$gar_num_id_cpte_nantie = $row['gar_num_id_cpte_nantie'];
	  	$myErr = setNumCpteComptableForCompte ($gar_num_id_cpte_nantie, $db);
	  	
	  	// @todo: delete TEST
	  	$gar_num_id_cpte_nantie_array[] = $gar_num_id_cpte_nantie;	  	
  }
  */
  // #357 fin : équilibre inventaire - comptabilité
  
  affiche(sprintf(_("OK (%s mises à jour)"),$count), true);

  decLevel();
  affiche(_("Mise à jour terminée !"));

  $dbHandler->closeConnection(true);
}

/**
 * realisation_garanties Réalisation des garanties pour les DCR en souffrance
 *
 * @access public
 * @return void
 */
function realisation_garanties() {
  global $dbHandler;
  global $adsys;
  global $global_mouvements_credit;

  $db = $dbHandler->openConnection();
  affiche(_("Réalisation des garanties pour crédits en souffrance"));

  //Recherche des dossiers de crédits en souffrance
  $sql ="SELECT id_doss,id_client,cre_etat FROM ad_dcr WHERE (cre_etat=2) ORDER BY id_doss";
  $result = $db->query($sql);

  if (DB::isError($result))
    erreur("realisation_garanties()", _("La requête ne s'est pas exécutée correctement")." : ".$result->getMessage());

//Pour chaque dossier en souffrance
  while ($dossier = $result->fetchrow(DB_FETCHMODE_ASSOC)) {
    $id_client= $dossier['id_client'];
    $id_doss= $dossier['id_doss'];

    // Récupérer le solde du compte de la ganrantie
    $sql = "SELECT solde FROM ad_cpt WHERE id_titulaire='$id_client' AND id_prod=4";
    $result2 = $db->query($sql);

    if (DB::isError($result2))
      erreur("realisation_garanties()", _("La requête ne s'est pas exécutée correctement pour la garantie").": ".$result2->getMessage());

    $garantie = $result2->fetchrow(DB_FETCHMODE_ASSOC);
    $solde= $garantie['solde'];

    //Récupérer le restant du : captital,intérêtc et pénalité
    if ($solde > 0) {
      $sql = "SELECT id_ech,solde_cap,solde_int,solde_pen FROM ad_etr WHERE id_doss='$id_doss'and remb='f' ORDER BY id_ech";
      $result3 = $db->query($sql);

      if (DB::isError($result3))
        erreur("realisation_garanties()", _("La requête ne s'est pas exécutée correctement pour l'échéancier").": ".$result3->getMessage());

      $echeancier = $result3->fetchrow(DB_FETCHMODE_ASSOC);
      $solde_cap= $echeancier['solde_cap'];
      $solde_int= $echeancier['solde_int'];
      $solde_pen= $echeancier['solde_pen'];

      if ($solde< $solde_pen) {
        $solde_pen=$solde_pen-$solde;
        $solde=0;
      } else {
        $solde_pen=0;
        $solde=$solde-$solde;
      }

      if ($solde< $solde_int) {
        $solde_int=$solde_int-$solde;
        $solde=0;
      } else {
        $solde_int=0;
        $solde=$solde-$solde;
      }

      if ($solde< $solde_cap) {
        $solde_cap=$solde_cap-$solde;
        $solde=0;
      } else {
        $solde_cap=0;
        $solde=$solde-$solde;
      }
    }

  }

}

/**
 * calcul des interet a recevoir...
 * @return ErrorObj
 */
function  calcul_interets_a_recevoir() {
  global $global_mouvements_credit;
  global $global_id_agence, $global_monnaie;
  global $dbHandler;
  global $date_total;
  global $global_nom_login;

  $db = $dbHandler->openConnection();
  $global_id_agence = getNumAgence();

  // Initialisation des compteurs de comptes avec interets a calculer /
  $count_tot = 0;
  $count_tot_traite = 0;
  $freq_calc_int = NULL;


  // Recuperation du parametrage des frequences des interets a recevoir
  $sql = "SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = $global_id_agence;";

  $result=$db->query($sql);

  if (DB::isError($result)) {
    $dbHandler->closeConnection(false);
    erreur("calcul_interets_a_recevoir()", $result->getUserInfo());
  }

  $tmprow = $result->fetchrow();
  $freq_calc_int = $tmprow[0];

  affiche(_("Calcul des intérêts à recevoir sur comptes crédit ..."));
  incLevel();
  set_time_limit(0);

  // Si les interets sont parametrés pour etre calculés :
  if (!is_null($freq_calc_int) && $freq_calc_int > 0) {
    // recuperation des dossier eligibles.
    $sql_get_doss = " SELECT d.id_doss, d.id_client, d.id_prod, p.devise, d.etat, d.cre_etat, d.cre_date_debloc, d.cre_mnt_octr, d.cre_mnt_deb, p.periodicite,get_nb_jrs_calc_int_recevoir(date('$date_total'),date(d.cre_date_debloc),(select date(max(date_traitement)) from ad_calc_int_recevoir_his where id_doss = d.id_doss and etat_int = 1),(select date(max(date_traitement)) from ad_calc_int_recevoir_his where id_doss = d.id_doss and etat_int = 2)) as nb_jours FROM ad_dcr d INNER JOIN adsys_etat_credits e ON d.cre_etat = e.id INNER JOIN adsys_produit_credit p ON d.id_prod = p.id WHERE e.provisionne = 'f' AND p.mode_perc_int <> 1    AND d.etat IN (5, 7, 13, 14, 15) AND (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) > 0 AND ( ( (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) = 1 AND isFinMois(date('$date_total')) ) OR ( (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) = 2 AND isFinTrimestre(date('$date_total')) ) OR ( (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) = 3 AND isFinSemestre(date('$date_total')) ) OR ( (SELECT freq_calc_int_recevoir FROM adsys_calc_int_recevoir WHERE id_ag = numagc() LIMIT 1) = 4 AND isFinAnnee(date('$date_total')) )) AND d.id_ag = e.id_ag AND d.id_ag = numagc() ORDER BY cre_etat, d.id_doss;";

    $result_get_doss = $db->query($sql_get_doss);

    if (DB::isError($result_get_doss)) {
      $dbHandler->closeConnection(false);
      erreur("erreur query", $result_get_doss->getUserInfo());
    }

    $count = $result_get_doss->numrows();

    while ($row_get_doss = $result_get_doss->fetchrow(DB_FETCHMODE_ASSOC)) {
      $count_tot++;

      // IF condition to check if Id doss exist in  ad_calc_int_recevoir_his
      $sql_exist_doss = "SELECT count(*) as nb_row from ad_calc_int_recevoir_his where id_doss = ".$row_get_doss['id_doss']." and id_ag = $global_id_agence";
      $result_exist_doss = $db->query($sql_exist_doss);

      if (DB::isError($result_exist_doss)) {
        $dbHandler->closeConnection(false);
        erreur("erreur query", $result_exist_doss->getUserInfo());
      }
      $row_exist_doss = $result_exist_doss->fetchrow(DB_FETCHMODE_ASSOC);
      $nb_row = $row_exist_doss['nb_row'];

//--------------------------------------Ajout Solde interet Precedent a IAR---------------------------------------------
      if ($nb_row == 0)
      {
        $id_doss_param = $row_get_doss['id_doss'];
        $sql_solde_prec_nonpaye = "select * from calculsoldeiardoss_echnonpaye('$date_total',$id_doss_param,numagc(),'".$row_get_doss['devise']."')";
        $result_solde_prec_nonpaye = $db->query($sql_solde_prec_nonpaye);
        if (DB::isError($result_solde_prec_nonpaye)) {
          $dbHandler->closeConnection(false);
          erreur("erreur query", $result_solde_prec_nonpaye->getUserInfo());
        }
        while ($row_solde_prec_nonpaye = $result_solde_prec_nonpaye->fetchrow(DB_FETCHMODE_ASSOC)) {
          $id_ech_doss_nonpaye = $row_solde_prec_nonpaye['id_ech_iar'];
          $sum_solde_int_nonpaye = $row_solde_prec_nonpaye['id_ech_iar'];
          $date_ech_prec_nonpaye = $row_solde_prec_nonpaye['date_ech_prec'];
          $date_ech_iar_nonpaye = $row_solde_prec_nonpaye['date_ech_iar'];
          $solde_nonpaye = $row_solde_prec_nonpaye['solde_iar'];
          $solde_cap_nonpaye = $row_solde_prec_nonpaye['solde_cap'];

          $sql_cpte_int_recevoir = "select * from adsys_calc_int_recevoir";

          $result_cpte_int_recevoir = $db->query($sql_cpte_int_recevoir);

          if (DB::isError($result_cpte_int_recevoir)) {
            $dbHandler->closeConnection(false);
            erreur("Erreur compte couru a recevoir", $result_cpte_int_recevoir->getUserInfo());
          }
          if ($solde_nonpaye > 0) {

            $row_cpte_int_recevoir = $result_cpte_int_recevoir->fetchrow(DB_FETCHMODE_ASSOC);

            // Operation correspondant au mouvement int a recevoir
            $operation = 374;
            //if (isset($CHANGE)) $deviseCheque = $CHANGE['devise'];
            //else $deviseCheque = $InfoCpte['devise'];

            // recuperer le compte comptable associer au produit credit
            $sql_get_prod_cpte = "select * from adsys_produit_credit where id=" . $row_get_doss["id_prod"] . " and id_ag=numagc()";

            $result_get_prod_cpte = $db->query($sql_get_prod_cpte);

            if (DB::isError($result_get_prod_cpte)) {
              $dbHandler->closeConnection(false);
              erreur("Erreur Master query", $result_get_prod_cpte->getUserInfo());
            }

            $row_get_prod_cpte = $result_get_prod_cpte->fetchrow(DB_FETCHMODE_ASSOC);

            // recuperation des comptes comptables
            $comptable = array();
            $cptes_substitue = array();
            $cptes_substitue["cpta"] = array();
            $cptes_substitue["int"] = array();
            $cptes_substitue["cpta"]["credit"] = $row_get_prod_cpte['cpte_cpta_prod_cr_int'];
            $cptes_substitue["cpta"]["debit"] = $row_cpte_int_recevoir['cpte_cpta_int_recevoir'];

            $myErr = passageEcrituresComptablesAuto($operation, $solde_nonpaye, $comptable, $cptes_substitue, null, $date_total);

            if ($myErr->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              return $myErr;
            }

            $MyError = ajout_historique(374, $row_get_doss['id_client'], $row_get_doss['id_doss'], $global_nom_login, $date_total, $comptable, null, null);

            if ($MyError->errCode != NO_ERR) {
              $dbHandler->closeConnection(false);
              return $MyError;
            }
            $sql_insert_his_nonpaye = "insert into ad_calc_int_recevoir_his (id_doss,date_traitement,nb_jours,periodicite_jours,id_ech,solde_int_ech,
            calcul_iar_theorique,solde_relica,montant,etat_int,solde_cap,cre_etat,devise,id_his_calc, id_ecriture_calc,id_ag)
            VALUES ($id_doss_param,'$date_total', 0, 0, $id_ech_doss_nonpaye, 0, 0, 0, $solde_nonpaye,1,$solde_cap_nonpaye, 1,
            '".$row_get_doss['devise']."',(SELECT currval('ad_his_id_his_seq')),(SELECT currval('ad_ecriture_seq')),numagc())";
            $result_insert_his_nonpaye = $db->query($sql_insert_his_nonpaye);

            if (DB::isError($result_insert_his_nonpaye)) {
              $dbHandler->closeConnection(false);
              signalErreur(__FILE__, __LINE__, __FUNCTION__);
            }
          }
        }

      }
//----------------------------------------------------------------------------------------------------------------------
      // Fin If condition
      $sql_calc_int = "select * from calculsoldeiardoss('$date_total', ".$row_get_doss['id_doss'].", numagc() , '".$row_get_doss['devise']."' );";

      $result_calc_int = $db->query($sql_calc_int);

      if (DB::isError($result_calc_int)) {
        $dbHandler->closeConnection(false);
        erreur("Erreur Master query", $result_calc_int->getUserInfo());
      }
      
      $row_calc_int = $result_calc_int->fetchrow(DB_FETCHMODE_ASSOC);
      $id_doss = $row_get_doss['id_doss'];
      $nb_jour = $row_calc_int['nb_jours_prorata'];
      $periodicite = $row_calc_int['periodicite'];
      $id_ech = $row_calc_int['id_ech_iar'];
      $solde_int_ech = $row_calc_int['sum_solde_int'];
      $int_calc = $row_calc_int['solde_iar'];
      $solde_cap_tot = $row_calc_int['solde_cap'];
      $calcul_iar_th = $row_calc_int["iar_calc_theorique"];
      $solde_relica = $row_calc_int["solde_iar_his"];

      $sql_calc_nb_jour_ret = "select (date('$date_total') - min(date_ech)) as nb_jours_retard from ad_etr where id_doss =".$row_get_doss['id_doss']." and remb = false;";

      $result_calc_nb_jour_ret = $db->query($sql_calc_nb_jour_ret);

      if (DB::isError($result_calc_nb_jour_ret)) {
        $dbHandler->closeConnection(false);
        erreur("Erreur Master query", $result_calc_nb_jour_ret->getUserInfo());
      }

      $row_calc_nb_jour_ret = $result_calc_nb_jour_ret->fetchrow(DB_FETCHMODE_ASSOC);

      if ($row_calc_nb_jour_ret['nb_jours_retard']>0) {
        if ($row_calc_nb_jour_ret['nb_jours_retard'] >= 29 && $row_calc_nb_jour_ret['nb_jours_retard'] < 60) {
          $cre_etat = 2;
        } else if ($row_calc_nb_jour_ret['nb_jours_retard'] >= 1 && $row_calc_nb_jour_ret['nb_jours_retard'] < 29) {
          $cre_etat = 1;
        } else if ($row_calc_nb_jour_ret['nb_jours_retard'] >= 60) {
          $cre_etat = 3;
        }
      }
      else{
        $cre_etat = 1;
      }

      $devise = $row_get_doss['devise'];

      if ($int_calc != 0) {
        $count_tot_traite++;

        //Mouvement comptable: parametrage des arrays*/

        //recuperation du compte interet couru a recevoir
        $sql_cpte_int_recevoir = "select * from adsys_calc_int_recevoir";

        $result_cpte_int_recevoir = $db->query($sql_cpte_int_recevoir);

        if (DB::isError($result_cpte_int_recevoir)) {
          $dbHandler->closeConnection(false);
          erreur("Erreur compte couru a recevoir", $result_cpte_int_recevoir->getUserInfo());
        }

        $row_cpte_int_recevoir = $result_cpte_int_recevoir->fetchrow(DB_FETCHMODE_ASSOC);

        // Operation correspondant au mouvement int a recevoir
        $operation = 374;
        //if (isset($CHANGE)) $deviseCheque = $CHANGE['devise'];
        //else $deviseCheque = $InfoCpte['devise'];

        // recuperer le compte comptable associer au produit credit
        $sql_get_prod_cpte = "select * from adsys_produit_credit where id=" . $row_get_doss["id_prod"] . " and id_ag=numagc()";

        $result_get_prod_cpte = $db->query($sql_get_prod_cpte);

        if (DB::isError($result_get_prod_cpte)) {
          $dbHandler->closeConnection(false);
          erreur("Erreur Master query", $result_get_prod_cpte->getUserInfo());
        }

        $row_get_prod_cpte = $result_get_prod_cpte->fetchrow(DB_FETCHMODE_ASSOC);

        // recuperation des comptes comptables
        $comptable = array();
        $cptes_substitue = array();
        $cptes_substitue["cpta"] = array();
        $cptes_substitue["int"] = array();
        $cptes_substitue["cpta"]["credit"] = $row_get_prod_cpte['cpte_cpta_prod_cr_int'];
        $cptes_substitue["cpta"]["debit"] = $row_cpte_int_recevoir['cpte_cpta_int_recevoir'];

        $myErr = passageEcrituresComptablesAuto($operation, $int_calc, $comptable, $cptes_substitue, null,$date_total);

        if ($myErr->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $myErr;
        }


        // ajout dans les tables mouvements, ecritures et historisation.
        $MyError = ajout_historique(374, $row_get_doss['id_client'],$row_get_doss['id_doss'], $global_nom_login, $date_total, $comptable, null, null);

        if ($MyError->errCode != NO_ERR) {
          $dbHandler->closeConnection(false);
          return $MyError;
        }


        // insertion dans la table ad_calc_int_recevoir_his.
        // TODO : Changement de la requete pour ajouter les nouvelles colonnes.
        if ($solde_relica == NULL){
          $solde_relica = 0;
        }
        $sql_insert_his = "insert into ad_calc_int_recevoir_his (id_doss,date_traitement,nb_jours,periodicite_jours,id_ech,solde_int_ech,
        calcul_iar_theorique,solde_relica,montant,etat_int,solde_cap,cre_etat,devise,id_his_calc, id_ecriture_calc,id_ag)
        VALUES ($id_doss,'$date_total', $nb_jour, $periodicite, $id_ech, $solde_int_ech, $calcul_iar_th, $solde_relica, $int_calc,1,$solde_cap_tot, $cre_etat,
        '$devise',(SELECT currval('ad_his_id_his_seq')),(SELECT currval('ad_ecriture_seq')),numagc())";
        $result_insert_his = $db->query($sql_insert_his);
        if (DB::isError($result_insert_his)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

      }
    }

  }
  $dbHandler->closeConnection(true);

  affiche(sprintf(_("OK. %s sur %s dossiers de crédit ont été traités"),$count_tot_traite,$count_tot), true);
  decLevel();
  affiche(_("Calcul des intérêts à recevoir sur comptes crédit terminé !"));

  return new ErrorObj(NO_ERR);
}

/**
 * traite_credit Traitement des crédits en batch
 *
 * @access public
 *
 */
function traite_credit() {
  affiche(_("Démarre le traitement des crédits ..."));
  incLevel();

  global $dbHandler;
  global $global_id_his;
  global $error;
  global $global_mouvements_credit;
  global $date_work;
  $INFOREMAUTO = array();



  $myErr=prelev_auto(); //Prélèvements automatiques
  if ($myErr->errCode == NO_ERR) {
    $INFOREMAUTO = $myErr->param;
  }

  calcul_interets_a_recevoir(); // calcule les interets a recevoir

  update_interets(); // Met à jour les intérêts sur certains crédits

  /* Ligne de crédit */
  update_interets_lcr(); // Met à jour les intérêts sur les dossiers ligne de crédit

  blocage_periode_de_nettoyage_lcr(); // Passage en période de nettoyage - ligne de crédit

  prelevement_frais_interets_fin_mois_lcr(); // Prélèvements des frais et intérêts ligne de crédit

  calcul_penalites(); //Pénalités

  update_etat(); //Mise à jour de l'état : sain, souffrance, perte

  // Fix date comptable et date valeur
  overwrite_date_compta($global_mouvements_credit);

  // On inscrit tous les mouvements dans la table historique

  $myErr = ajout_historique(212, NULL, NULL, NULL, date("r"), $global_mouvements_credit, NULL,$global_id_his);

  if ($myErr->errCode != NO_ERR) {
    $dbHandler->closeConnection(false);
    erreur(_("appel à")." ajout_historique", $error[$myErr->errCode].$myErr->param);
  }

  $id_his = $myErr->param;

  if (sizeof($INFOREMAUTO) > 0){ // call to function traite int calculer prelev auto taking as parameter $INFOREMAUTO and $id_his
    traite_interet_calculer_prelev_auto ($INFOREMAUTO, $id_his);
  }

  //libere la memoire du tableau
  unset($global_mouvements_credit);
  decLevel();
  affiche(_("Traitement des crédits terminé !"));

  return $myErr->datas;
}

/*
 * Function pour traiter les interets calculé pour les dossiers de crédit traiter par Prélèvement automatique
 * @param 1 : array -> $INFOREMAUTO
 * @param 2 : int -> $id_his
 */
function traite_interet_calculer_prelev_auto ($INFOREMAUTO, $id_his){
  /*------------Debut enregistrement intérêts calculé (reprise) pour les dossiers de crédit traiter par Prélèvement automatique------------------------*/
  affiche(_("Démarre le traitement des intérêts calculé pour les dossiers de crédit traité par Prélèvement automatique ..."));
  incLevel();
  global $dbHandler;
  global $global_mouvements_credit;
  global $date_total;
  $count = 0;
  $countCredittraite = 0;
  $db = $dbHandler->openConnection();

  for ($count=0;$count<sizeof($INFOREMAUTO);$count++){

    if ($INFOREMAUTO[$count]['int_cal'] != 0 && $INFOREMAUTO[$count]['int_cal_traite'] != 0){

      $interet_calculer = $INFOREMAUTO[$count]['int_cal_traite'];


      if ($interet_calculer > 0){

        $countCredittraite++;

        $sql_insert_his_repris = "INSERT INTO ad_calc_int_recevoir_his(id_doss, date_traitement, nb_jours, periodicite_jours,id_ech, solde_int_ech, montant, etat_int, solde_cap, cre_etat, devise, id_his_reprise, id_ecriture_reprise, id_ag) VALUES (" . $INFOREMAUTO[$count]['id_doss'] . ",'$date_total',0,0," . $INFOREMAUTO[$count]['id_ech'] . ",0,$interet_calculer, 2, 0, 1, '" . $INFOREMAUTO[$count]['devise'] . "', $id_his, (select distinct(e.id_ecriture) from ad_mouvement m, ad_ecriture e where m.id_ecriture = e.id_ecriture and e.id_his = $id_his and e.type_operation = 375 and e.info_ecriture = '" . $INFOREMAUTO[$count]['id_doss'] . "' and m.compte in (select cpte_cpta_int_recevoir from adsys_calc_int_recevoir where id_ag = numagc()) and m.cpte_interne_cli is null and (m.montant = '" . $INFOREMAUTO[$count]['int_cal_traite'] . "' or round(m.montant) = round(" . $INFOREMAUTO[$count]['int_cal_traite'] . ")) and m.sens = 'c'), numagc())";
        $result_insert_his_repris = $db->query($sql_insert_his_repris);
        if (DB::isError($result_insert_his_repris)) {
          $dbHandler->closeConnection(false);
          signalErreur(__FILE__,__LINE__,__FUNCTION__);
        }

      }
    }
  }

  $dbHandler->closeConnection(true);

  affiche(sprintf(_("OK ( %s sur %s dossiers de crédits traité"),$countCredittraite,$count), true);
  decLevel();
  affiche(_("Traitement des intérêts calculé pour les dossiers de crédit traité par Prélèvement automatique terminé !"));
  /*------------Fin enregistrement intérêts calculé (reprise) pour les dossiers de crédit traiter par Prélèvement automatique------------------------*/
}

/*
 * Ticket 693 : Bloquer montant par batch
 * Fonction pour bloquer les montants des echeances pour les dossiers respectifs avant les dates des echeances
 */
function bloque_mnt_ech_dossier_batch ($dateNow){
  global $dbHandler;
  $db = $dbHandler->openConnection();

  // Drop trigger trig_before_update_ad_cpt_mnt_bloq_cre
  $sql_drop_trigger = "DROP TRIGGER IF EXISTS trig_before_update_ad_cpt_mnt_bloq_cre ON ad_cpt;";

  $result_drop_trigger = $db->query($sql_drop_trigger);
  if (DB::isError($result_drop_trigger)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql_drop_trigger."\n".$result_drop_trigger->getMessage());
  }

  //Bloqué le montant d'un echeance pour un dossier avant la date d'echeance
  affiche(_("Debut Traitement automatiques : Bloqué montant écheance pour les dossiers concernés"));
  $sql_bloqMontant = "select * from block_montant_ech_par_batch(date('".$dateNow."'))";
  incLevel();
  affiche(_("Mise a jour des montants bloqué en cours ..."));
  decLevel();
  $result_bloqMontant = $db->query($sql_bloqMontant);
  if (DB::isError($result_bloqMontant)){
    erreur("prelev_auto()", _("La requête ne s'est pas exécutée correctement")." : ".$result_bloqMontant->getMessage());
    $dbHandler->closeConnection(false);
  }
  $numDossier = $result_bloqMontant->fetchrow(DB_FETCHMODE_ASSOC);
  affiche(_("Fin Traitement automatiques : Bloqué montant écheance pour ".$numDossier['block_montant_ech_par_batch']." dossiers eligibles !"));

  // Create trigger trig_before_update_ad_cpt_mnt_bloq_cre
  $sql_recreate_trigger = "CREATE TRIGGER trig_before_update_ad_cpt_mnt_bloq_cre BEFORE UPDATE ON ad_cpt FOR EACH ROW EXECUTE PROCEDURE trig_before_update_ad_cpt_mnt_bloq_cre();";

  $result_recreate_trigger = $db->query($sql_recreate_trigger);
  if (DB::isError($result_recreate_trigger)) {
    $dbHandler->closeConnection(false);
    signalErreur(__FILE__,__LINE__,__FUNCTION__,_("Erreur dans la requête SQL")." : ".$sql_recreate_trigger."\n".$result_recreate_trigger->getMessage());
  }

  $dbHandler->closeConnection(true);
}

function traite_declassement ($mouvement_declassement,$type_operation,$type_fonction,$date_jour){
  affiche(_("Traitement des mouvements de declassement pour les dossiers en fin d'années !"));
  incLevel();

  $process = update_declassement($mouvement_declassement,$type_operation,$type_fonction,$date_jour);

  if($process == true) {
    affiche(_("OK"));
  }
  else {
    affiche(_("Erreur lors du traitement des mouvements de declassement pour les dossiers en fin d'années"));
  }

  decLevel();
  affiche(_("Traitement des mouvements de declassement pour les dossiers en fin d'années terminé !"));
}


?>
