<?php


/* vim: set expandtab softtabstop=2 shiftwidth=2 foldmethod=marker: */

/**
 * Calcul échéancier théorique
 * @package Credit
 */

require_once 'lib/misc/tableSys.php';
require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/mobile_lending.php';

/**
 * @TODO : Repercuter changements dans l'echeancier du raccourcissement ici
 * 
 * Calcule un échéancier théorique de remboursement (càd un échéancier sans date) à partir de données diverses.
 *
 * @param mixed $id_prod Produit de crédit
 * @param mixed $capital Montant en capital du crédit
 * @param mixed $duree Durée du crédit, en mois
 * @param mixed $differe_jours Différé en jours sur le premier remboursement
 * @param mixed $differe_ech Différé en échéances sur le premier remboursement
 * @return array Tableau des échéances numérotées de 1 à n avec pour chacune d'elles mnt_cap, mnt_int et mnt_gar
 * @since 1.0
 * @author Antoine Delvaux (ancienne version par Drissa Coulibaly)
 */
function calcul_echeancier_theorique($id_prod, $capital, $duree, $differe_jours, $differe_ech, $periodicite = NULL, $echeance_index = 1, $id_doss = NULL, $produits_credit = NULL) {
	global $adsys, $global_id_agence, $global_id_client;
	global $global_monnaie_courante, $global_monnaie_courante_prec;

	// {{{ Initialisations
	$retour = array ();

	// Récupération des infos via le produit de crédit
	if ($produits_credit != NULL && is_array($produits_credit)) {
		$Produit = $produits_credit;
	} else {
		$Produits = getProdInfo("WHERE id = $id_prod", $id_doss);
		$Produit = $Produits[0];
	}

	if(isMobileLending()  && $Produit['is_mobile_lending_credit'] == 't') {
        $premier_credit = isPremierCreditMobileLending($global_id_client);
        if ($premier_credit['premier_credit'] == 't') {
            $Produit['tx_interet'] = 0;
        }
    }

	if ($periodicite == NULL) {
		$periodicite = $Produit["periodicite"];
	}

	$mode_calc_int = $Produit["mode_calc_int"];
	$mode_perc_int = $Produit["mode_perc_int"];
	$tx_interet = $Produit["tx_interet"];
	$freq_paiement_cap = $Produit["freq_paiement_cap"];
	$prc_gar_encours = $Produit["prc_gar_encours"];
	$gar_encours = $prc_gar_encours * $capital;
	// Récupération du nombre de jours par an
	$Agence = getAgenceDatas($global_id_agence);
	if ($Agence["base_taux"] == 1) // 360 jours
		$nbre_jours_an = 360;
	elseif ($Agence["base_taux"] == 2) // 365 jours
			$nbre_jours_an = 365;

	// Vérifie que la durée est bien un multiple du nombre de mois que constitue une période
	// Ceci ne devrait pas arriver si le javascript est activé
	if ($adsys["adsys_duree_periodicite"][$periodicite] > 1)
		if ($duree % $adsys["adsys_duree_periodicite"][$periodicite] != 0)
			signalErreur(__FILE__, __LINE__, __FUNCTION__, _("La durée n'est pas un multiple de la périodicité."));

	// Calcule la durée d'une période (échéance)
	// $period contient la durée d'une échéance exprimée en type de durée de crédit : mois ou semaine
	if ($periodicite != 6)
		$period = $adsys["adsys_duree_periodicite"][$periodicite];
	else
		$period = $duree; // Remboursement en une fois

	// Calcul du nombre d'échéances de remboursement (hors différé)
	$nbr_ech_remb = $duree / $period;
	$nbr_ech_total = $nbr_ech_remb + $differe_ech;

	// Calcul du prorata temporis pour le différé en jours
	if ($periodicite == 8)
		// Périodicité hebdomadaire
		$nbr_jours_ech_courante = date("d", mktime(0, 0, 0, 0, date("d") + $period * 7, date("Y")));
	elseif ($Agence["base_taux"] == 1)
			$nbr_jours_ech_courante = 30 * $period;
		else
			$nbr_jours_ech_courante = date("d", mktime(0, 0, 0, date("m") + $period, 0, date("Y")));
	if (($differe_jours > 0) || ($differe_jours < 0)) {
		$prorata_temp = $differe_jours / $nbr_jours_ech_courante;
		debug(	$prorata_temp,"temp");
		/*  Code à activer si on veut créer une échéance supplémentaire pour le différé en jours
		    $premiere_echeance = 2;
		    $echeancier[1]['mnt_cap'] = 0;
		    $echeancier[1]['mnt_int'] = 0;
		    $echeancier[1]['mnt_gar'] = 0; */
	} else {
		$prorata_temp = 1;
		//    $premiere_echeance = 1;
	}
	$premiere_echeance = 1;

	// Taux d'intérêts ramené sur une période (pas utile pour le mode constant)
	if ($periodicite == 8)
		$tx_int_ech = $tx_interet * $period;
	else
		$tx_int_ech = $tx_interet * $period / 12;

	// Les montants cumulés, pour vérifier les arrondis sur la dernière échéance
	$mnt_cap = 0;
	$mnt_gar = 0;
	$mnt_int = 0;

	// Le flag pour la perception des intérêts au debut de l'échéancier
	$int_percus = false;

	// }}}
	// {{{ Mode Constant

	if ($mode_calc_int == 1) {


		// Montant total des intérêts à payer
		if ($periodicite == 8)
			// Périodicité hebdomadaire
			$int = $capital * ($duree + $differe_ech * $period) * $tx_interet;
		else
			$int = $capital * ($duree + $differe_ech * $period) * $tx_interet / 12;


		for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
			// Pour chaque échéance
			$echeancier[$i]['mnt_int'] = 0;

			if ($i <= $differe_ech) {
				// On est dans le différé, on ne rembourse que les intérêts
				$echeancier[$i]['mnt_cap'] = 0;
				$echeancier[$i]['mnt_gar'] = 0;
				if ($mode_perc_int == 2) {
					// Intérêts inclus dans les remboursements
					if($Produit["calcul_interet_differe"] == 't'){
					  $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_total, $global_monnaie_courante_prec);
					}
				}
				if ($Produit['differe_epargne_nantie'] == 'f') {
					$echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
				}
			} elseif ($i < $nbr_ech_total) {
					// On rembourse les intérêts et le capital et on constitue la garantie
					if (($i - $differe_ech) % $freq_paiement_cap == 0)
						// L'échéance, hors différé, est un multiple de la fréquence de paiement
						$echeancier[$i]['mnt_cap'] = round($capital * $freq_paiement_cap / $nbr_ech_remb, $global_monnaie_courante_prec);
					else
						$echeancier[$i]['mnt_cap'] = 0;
					  if ($Produit['differe_epargne_nantie'] == 'f') {
					     $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
				    }else{
				    	$echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec);
				    }
					if ($mode_perc_int == 1 && !$int_percus) {
						// Perception des intérêts au début
						$echeancier[$i]['mnt_int'] = round($int, $global_monnaie_courante_prec);
						$int_percus = true;
					}
					if ($mode_perc_int == 2) {
						// Intérêts inclus dans les remboursements
						if($Produit["calcul_interet_differe"] == 't'){
						   $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_total, $global_monnaie_courante_prec);
						}else{
						    $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_remb, $global_monnaie_courante_prec);
						}
					}

				} else {
					// On est à la dernière échéance
					// On reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
					$echeancier[$i]['mnt_cap'] = round($capital - $mnt_cap, $global_monnaie_courante_prec);
					$echeancier[$i]['mnt_gar'] = round($gar_encours - $mnt_gar, $global_monnaie_courante_prec);
					if ($mode_perc_int == 2) {
						// Intérêts inclus dans les remboursements
						$echeancier[$i]['mnt_int'] = round($int - $mnt_int, $global_monnaie_courante_prec);
					}
					if ($mode_perc_int == 3) {
						// Perception des intérêts à la fin
						$echeancier[$i]['mnt_int'] = round($int, $global_monnaie_courante_prec);
					}
				}

			// On calcule les sommes pour tomber juste à la fin
			$mnt_cap += $echeancier[$i]['mnt_cap'];
			$mnt_gar += $echeancier[$i]['mnt_gar'];
			$mnt_int += $echeancier[$i]['mnt_int'];
		}

		if (($differe_jours > 0) || ($differe_jours < 0)) {
			// On ajoute les intérêts sur le différé en jours à la première échéance
			if($Produit["calcul_interet_differe"] == 't'){


			     $mnt_int_diff_jour= round($int / $nbr_ech_total * $prorata_temp, $global_monnaie_courante_prec);

			     /* On ajoute les intérêts sur le différé en jours à la première  ou a la derniere   échéance
         selon le report choisi .les montants entre la première échéance et la dernière seront échanger
        */
			     if ($Produit['report_arrondi'] =='t') {
			     	 $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour; // ca sera la premiere écheance

			     }
			     else
			     {
                                 if($echeance_index!=1) {
                                     $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour;
                                 }
                                 else {
                                     $echeancier[$nbr_ech_total]['mnt_int'] +=$mnt_int_diff_jour;//sera la premiere
                                 }
			     }

			}
		}
	}

	// }}}
	// {{{ Mode dégressif prédéfini ou mode dégressif variable
	// Ils sont identiques au niveau de l'échéancier théorique

	elseif ($mode_calc_int == 2 || $mode_calc_int == 3) {
			// Calcul de la somme à payer à chaque échéance
			$mnt_ech = $capital * $tx_int_ech / (1 - 1 / pow(1 + $tx_int_ech, $nbr_ech_remb / $freq_paiement_cap));

			// Cas particuliers de perception des intérêts au début ou à la fin
			if ($mode_perc_int == 1) {
				// Perception des intérêts au début
				$ech_de_remboursement = $differe_ech +1;
			} elseif ($mode_perc_int == 3) {
					// Perception des intérêts à la fin
					$ech_de_remboursement = $nbr_ech_total;
				}

			for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
				// Pour chaque échéance
				$echeancier[$i]['mnt_int'] = 0;

				if ($i <= $differe_ech) {
					// On est dans le différé, on ne rembourse que les intérêts
					$echeancier[$i]['mnt_cap'] = 0;
					$echeancier[$i]['mnt_int'] = 0;
					$echeancier[$i]['mnt_gar'] = 0;
					if ($mode_perc_int == 1 || $mode_perc_int == 3) {
						// Perception des intérêts au début ou à la fin
						$echeancier[$ech_de_remboursement]['mnt_int'] += round($capital * $tx_int_ech, $global_monnaie_courante_prec);
					} else {
						if($Produit["calcul_interet_differe"] == 't') {
							// Intérêts inclus dans les remboursements
							$echeancier[$i]['mnt_int'] = round($capital * $tx_int_ech, $global_monnaie_courante_prec);
						}
					}
					if ($Produit['differe_epargne_nantie'] == 'f') {
						$echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
					}
				} elseif ($i < $nbr_ech_total) {
						// On rembourse les intérêts et le capital et on constitue la garantie
						if ($mode_perc_int == 1 || $mode_perc_int == 3) {
							// Perception des intérêts au début ou à la fin
							$echeancier[$ech_de_remboursement]['mnt_int'] += round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
						} else {
							// Intérêts inclus dans les remboursements
							$echeancier[$i]['mnt_int'] = round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
						}
						if (($i - $differe_ech) % $freq_paiement_cap == 0)
							// L'échéance, hors différé, est un multiple de la fréquence de paiement
							$echeancier[$i]['mnt_cap'] = round($mnt_ech - $echeancier[$i]['mnt_int'], $global_monnaie_courante_prec);
						else
							$echeancier[$i]['mnt_cap'] = 0;
							if ($Produit['differe_epargne_nantie'] == 'f') {
						      $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
					    }else{
						      $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec);
							}

					} else {
						// On est à la dernière échéance, on reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
						$echeancier[$i]['mnt_cap'] = $capital - $mnt_cap;
						$echeancier[$i]['mnt_gar'] = $gar_encours - $mnt_gar;
						if ($mode_perc_int == 1 || $mode_perc_int == 3) {
							// Perception des intérêts au début ou à la fin
							$echeancier[$ech_de_remboursement]['mnt_int'] += round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
						} else {
							// Intérêts inclus dans les remboursements
							$echeancier[$i]['mnt_int'] = round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
						}
					}

				// On calcule les sommes pour tomber juste à la fin
				$mnt_cap += $echeancier[$i]['mnt_cap'];
				$mnt_gar += $echeancier[$i]['mnt_gar'];
				$mnt_int += $echeancier[$i]['mnt_int'];
			}

			if (($differe_jours > 0) || ($differe_jours < 0)) {
         // On ajoute les intérêts sur le différé en jours à la première échéance
				if($Produit["calcul_interet_differe"] == 't') { //si le calcule d'interêt diff'

				   $mnt_int_diff_jour= round($capital * $tx_int_ech * $prorata_temp, $global_monnaie_courante_prec);

          /* On ajoute les intérêts sur le différé en jours à la première  ou a la derniere   échéance
         selon le report choisi .les montants entre la première échéance et la dernière seront échanger
        */
			     if ($Produit['report_arrondi'] =='t') {
                                $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour; //ca sera la pemiere écheance

			     }
			     else
			     {
                                 if($echeance_index!=1) {
                                     $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour;
                                 }
                                 else {
                                     $echeancier[$nbr_ech_total]['mnt_int'] +=$mnt_int_diff_jour; //sera la premiere écheance
                                 }
			     }
				}

  		}

		}

	// }}}
	// {{{ Mode dégressif capital constant

	elseif ($mode_calc_int == 4) {
			// Calcul de la somme à payer à chaque échéance
			$cap_ech = $capital * $freq_paiement_cap / $nbr_ech_remb;

			// Cas particuliers de perception des intérêts au début ou à la fin
			if ($mode_perc_int == 1) {
				// Perception des intérêts au début
				$ech_de_remboursement = $differe_ech +1;
			} elseif ($mode_perc_int == 3) {
					// Perception des intérêts à la fin
					$ech_de_remboursement = $nbr_ech_total;
				}

			for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
				// Pour chaque échéance
				$echeancier[$i]['mnt_int'] = 0;

				if ($i <= $differe_ech) {
					// On est dans le différé, on ne rembourse que les intérêts
					$echeancier[$i]['mnt_cap'] = 0;
					$echeancier[$i]['mnt_int'] = 0;
					$echeancier[$i]['mnt_gar'] = 0;
					if ($mode_perc_int == 1 || $mode_perc_int == 3) {
						// Perception des intérêts au début ou à la fin
						$echeancier[$ech_de_remboursement]['mnt_int'] += round($capital * $tx_int_ech, $global_monnaie_courante_prec);
					} else {
						if($Produit["calcul_interet_differe"] == 't') {
							// Intérêts inclus dans les remboursements
							$echeancier[$i]['mnt_int'] = round($capital * $tx_int_ech, $global_monnaie_courante_prec);
						}
					}
					if ($Produit['differe_epargne_nantie'] == 'f') {
						$echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
					}
				} elseif ($i < $nbr_ech_total) {
						// On rembourse les intérêts et le capital et on constitue la garantie
						if (($i - $differe_ech) % $freq_paiement_cap == 0)
							// L'échéance, hors différé, est un multiple de la fréquence de paiement
							$echeancier[$i]['mnt_cap'] = round($cap_ech, $global_monnaie_courante_prec);
						else
							$echeancier[$i]['mnt_cap'] = 0;
						if ($mode_perc_int == 1 || $mode_perc_int == 3) {
							// Perception des intérêts au début ou à la fin
							$echeancier[$ech_de_remboursement]['mnt_int'] += round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
						} else {
							// Intérêts inclus dans les remboursements
							$echeancier[$i]['mnt_int'] = round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
						}
						if ($Produit['differe_epargne_nantie'] == 'f') {
						  $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
					  }else{
						 $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec);
						}

					} else {
						// On est à la dernière échéance, on reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
						$echeancier[$i]['mnt_cap'] = $capital - $mnt_cap;
						$echeancier[$i]['mnt_gar'] = $gar_encours - $mnt_gar;
						if ($mode_perc_int == 1 || $mode_perc_int == 3) {
							// Perception des intérêts au début ou à la fin
							$echeancier[$ech_de_remboursement]['mnt_int'] += round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
						} else {
							// Intérêts inclus dans les remboursements
							$echeancier[$i]['mnt_int'] = round(($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec);
						}
					}

				// On calcule les sommes pour tomber juste à la fin
				$mnt_cap += $echeancier[$i]['mnt_cap'];
				$mnt_gar += $echeancier[$i]['mnt_gar'];
				$mnt_int += $echeancier[$i]['mnt_int'];
			}

			if (($differe_jours > 0) || ($differe_jours < 0)) {
				// On ajoute les intérêts sur le différé en jours à la première échéance

						if($Produit["calcul_interet_differe"] == 't') {

      		     $mnt_int_diff_jour= round($capital * $tx_int_ech * $prorata_temp, $global_monnaie_courante_prec);

      		     /* On ajoute les intérêts sur le différé en jours à la première  ou a la derniere   échéance
         					selon le report choisi .les montants entre la première échéance et la dernière seront échanger
       				 */
			         if ($Produit['report_arrondi'] =='t') {

			     	    $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour; //sera la primiere écheance

			         }
			         else
			         {
                                     if($echeance_index != 1) {
                                         $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour;
                                     }
                                     else {
                                        $echeancier[$nbr_ech_total]['mnt_int'] +=$mnt_int_diff_jour;// sera la premiere echeance
                                     }
			         }

			     }
			}
		}
        // {{{ Mode Ligne de crédit

	elseif ($mode_calc_int == 5) {

		// Montant total des intérêts à payer
		for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
			// Pour chaque échéance
			$echeancier[$i]['mnt_int'] = 0;
                        $echeancier[$i]['mnt_gar'] = 0;

                        if ($i < $nbr_ech_total) {

                        } else {
                            // On est à la dernière échéance
                            // On reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
                            $echeancier[$i]['mnt_cap'] = round($capital - $mnt_cap, $global_monnaie_courante_prec);

                        }

			// On calcule les sommes pour tomber juste à la fin
			$mnt_cap += $echeancier[$i]['mnt_cap'];
			$mnt_gar += $echeancier[$i]['mnt_gar'];
			$mnt_int += $echeancier[$i]['mnt_int'];
		}
	}

	// }}}
	// {{{ Autre

	else
		signalErreur(__FILE__, __LINE__, __FUNCTION__, _("Mode de calcul des intérêts inconnu !"));

    //Si Report arrondi première échéance
    if($Produit["report_arrondi"] == 'f'){
    	//Echanger les montants entre la première échéance et la dernière
    	//Transfert du capital
    	$tmp_cap=$echeancier[$premiere_echeance]['mnt_cap'];
    	$echeancier[$premiere_echeance]['mnt_cap']=$echeancier[$nbr_ech_total]['mnt_cap'];
    	$echeancier[$nbr_ech_total]['mnt_cap']=$tmp_cap;
    	//Transfert des intérêts
    	$tmp_int=$echeancier[$premiere_echeance]['mnt_int'];
    	$echeancier[$premiere_echeance]['mnt_int']=$echeancier[$nbr_ech_total]['mnt_int'];
    	$echeancier[$nbr_ech_total]['mnt_int']=$tmp_int;
    	//Transfert des garanties
    	$tmp_gar=$echeancier[$premiere_echeance]['mnt_gar'];
    	$echeancier[$premiere_echeance]['mnt_gar']=$echeancier[$nbr_ech_total]['mnt_gar'];
    	$echeancier[$nbr_ech_total]['mnt_gar']=$tmp_gar;
    	//Transfert des soldes
    	//du capital
    	$tmp_solde_cap=$echeancier[$premiere_echeance]['solde_cap'];
    	$echeancier[$premiere_echeance]['solde_cap']=$echeancier[$nbr_ech_total]['solde_cap'];
    	$echeancier[$nbr_ech_total]['solde_cap']=$tmp_solde_cap;
    	//des intérêts
    	$tmp_solde_int=$echeancier[$premiere_echeance]['solde_int'];
    	$echeancier[$premiere_echeance]['solde_int']=$echeancier[$nbr_ech_total]['solde_int'];
    	$echeancier[$nbr_ech_total]['solde_int']=$tmp_solde_int;
    	//des garanties
    	$tmp_solde_gar=$echeancier[$premiere_echeance]['solde_gar'];
    	$echeancier[$premiere_echeance]['solde_gar']=$echeancier[$nbr_ech_total]['solde_gar'];
    	$echeancier[$nbr_ech_total]['solde_gar']=$tmp_solde_gar;
    	//des pénalités
    	$tmp_solde_pen=$echeancier[$premiere_echeance]['solde_pen'];
    	$echeancier[$premiere_echeance]['solde_pen']=$echeancier[$nbr_ech_total]['solde_pen'];
    	$echeancier[$nbr_ech_total]['solde_pen']=$tmp_solde_pen;


    }

    return $echeancier;
}

/**
 * Calcule un échéancier théorique de remboursement (càd un échéancier sans date) pour les raccourcissements à partir de données diverses.
 *
 * @param mixed $id_prod Produit de crédit
 * @param mixed $capital Montant en capital du crédit
 * @param mixed $nbr_echeances nouveau nombre echeances
 * @param mixed $differe_jours Différé en jours sur le premier remboursement
 * @param mixed $differe_ech Différé en échéances sur le premier remboursement
 * @param int $id_doss le numero du dossier 
 * @return array Tableau des échéances numérotées de 1 à n avec pour chacune d'elles mnt_cap, mnt_int et mnt_gar
 * @since 3.10
 * @author bd_maurice
 */
function calcul_echeancier_theorique_raccourci($id_prod, $capital, $nbr_echeances, $differe_jours, $differe_ech, $periodicite = NULL, $echeance_index = 1, $id_doss = NULL) 
{
	global $adsys, $global_id_agence;
	global $global_monnaie_courante, $global_monnaie_courante_prec;
		
	// {{{ Initialisations
	
	$retour = array ();
	
	// Récupération des infos via le produit de crédit
	$Produits = getProdInfo ( "WHERE id = $id_prod", $id_doss);
	$Produit = $Produits [0];
	if ($periodicite == NULL) {
		$periodicite = $Produit ["periodicite"];
	}
		
	$mode_calc_int = $Produit ["mode_calc_int"];
	$mode_perc_int = $Produit ["mode_perc_int"];
	$tx_interet = $Produit ["tx_interet"];
	$freq_paiement_cap = $Produit ["freq_paiement_cap"];
	$prc_gar_encours = $Produit ["prc_gar_encours"];
	$gar_encours = $prc_gar_encours * $capital;	
	
	// Récupération du nombre de jours par an
	$Agence = getAgenceDatas ( $global_id_agence );
	if ($Agence ["base_taux"] == 1) // 360 jours
		$nbre_jours_an = 360;
	elseif ($Agence ["base_taux"] == 2) // 365 jours
		$nbre_jours_an = 365;
	
	// Calcule la durée d'une période (échéance)
	// $period contient la durée d'une échéance exprimée en type de durée de crédit : mois ou semaine
	if ($periodicite != 6)
		$period = $adsys ["adsys_duree_periodicite"] [$periodicite];
	else
		$period = $duree; // Remboursement en une fois
    
	$duree = $nbr_echeances * $period;
		
	// Calcul du nombre d'échéances de remboursement (hors différé)
	$nbr_ech_remb = $duree / $period;
	$nbr_ech_total = $nbr_ech_remb + $differe_ech;
	
	// Calcul du prorata temporis pour le différé en jours
	if ($periodicite == 8) // Périodicité hebdomadaire		
		$nbr_jours_ech_courante = date ( "d", mktime ( 0, 0, 0, 0, date ( "d" ) + $period * 7, date ( "Y" ) ) );
	elseif ($Agence ["base_taux"] == 1)
		$nbr_jours_ech_courante = 30 * $period;
	else
		$nbr_jours_ech_courante = date ( "d", mktime ( 0, 0, 0, date ( "m" ) + $period, 0, date ( "Y" ) ) );
	
	if (($differe_jours > 0) || ($differe_jours < 0)) {
		$prorata_temp = $differe_jours / $nbr_jours_ech_courante;
		debug ( $prorata_temp, "temp" );
		/*
		 * Code à activer si on veut créer une échéance supplémentaire pour le différé en jours 
		 * $premiere_echeance = 2; $echeancier[1]['mnt_cap'] = 0; $echeancier[1]['mnt_int'] = 0; $echeancier[1]['mnt_gar'] = 0;
		 */
	} else {
		$prorata_temp = 1;
		// $premiere_echeance = 1;
	}
	$premiere_echeance = 1;
	
	// Taux d'intérêts ramené sur une période (pas utile pour le mode constant)
	if ($periodicite == 8)
		$tx_int_ech = $tx_interet * $period;
	else
		$tx_int_ech = $tx_interet * $period / 12;
		
		// Les montants cumulés, pour vérifier les arrondis sur la dernière échéance
	$mnt_cap = 0;
	$mnt_gar = 0;
	$mnt_int = 0;
	
	// Le montant total des interets a rembourser
	$int = 0;
	
	// Le flag pour la perception des intérêts au debut de l'échéancier
	$int_percus = false;

	// Calcul des interets proratisés pour le raccourcissement
	/*
	 *  @todo: Remove comments when working on #431 again
	$interets_proratise_data = getInteretsProrataForRaccourci($id_doss, $mode_calc_int, $periodicite, $tx_interet);
	$interets_proratise = $interets_proratise_data['interets_proratise'];
	$coeff_prorata = $interets_proratise_data['coeff_prorata'];
	*/
	
	/*
	 * Le coefficient de calcule des interets pro-ratisé pour un raccourcissement
	 * @TODO: Initialisé a zero pour le moment pour revenir en arriere sur le #431. 
	 * 	A completer		 
	 */
	$coeff_prorata = 0;
	$interets_proratise = 0;
	$int_pro_rata = 0;
	
	
	// }}}
	//	Montage de l'échéancier
	// {{{ Mode Constant
	
	if ($mode_calc_int == 1) {

		// Montant total des intérêts à payer
		if ($periodicite == 8)
			// Périodicité hebdomadaire
			$int = $capital * ($duree + $differe_ech * $period) * $tx_interet;
		else
			$int = $capital * ($duree + $differe_ech * $period) * $tx_interet / 12;


		for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
			// Pour chaque échéance
			$echeancier[$i]['mnt_int'] = 0;

			if ($i <= $differe_ech) {
				// On est dans le différé, on ne rembourse que les intérêts
				$echeancier[$i]['mnt_cap'] = 0;
				$echeancier[$i]['mnt_gar'] = 0;
				if ($mode_perc_int == 2) {
					// Intérêts inclus dans les remboursements
					if($Produit["calcul_interet_differe"] == 't'){
					  $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_total, $global_monnaie_courante_prec);
					}
				}
				if ($Produit['differe_epargne_nantie'] == 'f') {
					$echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
				}
			} elseif ($i < $nbr_ech_total) {
					// On rembourse les intérêts et le capital et on constitue la garantie
					if (($i - $differe_ech) % $freq_paiement_cap == 0)
						// L'échéance, hors différé, est un multiple de la fréquence de paiement
						$echeancier[$i]['mnt_cap'] = round($capital * $freq_paiement_cap / $nbr_ech_remb, $global_monnaie_courante_prec);
					else
						$echeancier[$i]['mnt_cap'] = 0;
					  if ($Produit['differe_epargne_nantie'] == 'f') {
					     $echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_total, $global_monnaie_courante_prec);
				    }else{
				    	$echeancier[$i]['mnt_gar'] = round($gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec);
				    }
					if ($mode_perc_int == 1 && !$int_percus) { // Perception des intérêts au début	
											
						if(!empty($coeff_prorata) && $i == 1) {
							$int_pro_rata = $coeff_prorata * $int; // calcul des interets prorata						
						}
												
						$echeancier[$i]['mnt_int'] = round($int, $global_monnaie_courante_prec);
						$int_percus = true;
					}
					if ($mode_perc_int == 2) {
						// Intérêts inclus dans les remboursements
						if($Produit["calcul_interet_differe"] == 't') {
							
							if(!empty($coeff_prorata) && $i == 1) {
								$int_pro_rata = ($int / $nbr_ech_total) * $coeff_prorata; // calcul des interets prorata		
							}
							
						   $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_total, $global_monnaie_courante_prec);
						}else{
							
							if(!empty($coeff_prorata) && $i == 1) {
								$int_pro_rata = ($int / $nbr_ech_remb) * $coeff_prorata; // calcul des interets prorata		
							}
							
						    $echeancier[$i]['mnt_int'] = round($int / $nbr_ech_remb, $global_monnaie_courante_prec);
						}
						
						if(!empty($int_pro_rata) && $i == 1) {
							$int += $int_pro_rata; // ajout pro rata dans l'interer de base 
						}					
					}

				} else {
					// On est à la dernière échéance
					// On reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
					$echeancier[$i]['mnt_cap'] = round($capital - $mnt_cap, $global_monnaie_courante_prec);
					$echeancier[$i]['mnt_gar'] = round($gar_encours - $mnt_gar, $global_monnaie_courante_prec);
					if ($mode_perc_int == 2) {
						// Intérêts inclus dans les remboursements
						$echeancier[$i]['mnt_int'] = round($int - $mnt_int, $global_monnaie_courante_prec);
					}
					if ($mode_perc_int == 3) {
						// Perception des intérêts à la fin
						$echeancier[$i]['mnt_int'] = round($int, $global_monnaie_courante_prec);
					}
				}

			// On calcule les sommes pour tomber juste à la fin
			$mnt_cap += $echeancier[$i]['mnt_cap'];
			$mnt_gar += $echeancier[$i]['mnt_gar'];
			$mnt_int += $echeancier[$i]['mnt_int'];
		}

//		if (($differe_jours > 0) || ($differe_jours < 0)) {
//			// On ajoute les intérêts sur le différé en jours à la première échéance
//			if($Produit["calcul_interet_differe"] == 't'){
//			     $mnt_int_diff_jour= round($int / $nbr_ech_total * $prorata_temp, $global_monnaie_courante_prec);
//
//			     /* On ajoute les intérêts sur le différé en jours à la première  ou a la derniere   échéance
//         			selon le report choisi .les montants entre la première échéance et la dernière seront échanger
//        		*/
//			     if ($Produit['report_arrondi'] =='t') {
//			     	 $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour; // ca sera la premiere écheance
//
//			     }
//			     else
//			     {
//                                 if($echeance_index!=1) {
//                                     $echeancier[$echeance_index]['mnt_int'] +=$mnt_int_diff_jour;
//                                 }
//                                 else {
//                                     $echeancier[$nbr_ech_total]['mnt_int'] +=$mnt_int_diff_jour;//sera la premiere
//                                 }
//			     }
//
//			}
//		}
	}	

	// }}}
	// {{{ Mode dégressif prédéfini ou mode dégressif variable
	// Ils sont identiques au niveau de l'échéancier théorique
	
	elseif ($mode_calc_int == 2 || $mode_calc_int == 3) {
		// Calcul de la somme à payer à chaque échéance
		$mnt_ech = $capital * $tx_int_ech / (1 - 1 / pow ( 1 + $tx_int_ech, $nbr_ech_remb / $freq_paiement_cap ));
		
		// Cas particuliers de perception des intérêts au début ou à la fin
		if ($mode_perc_int == 1) {
			// Perception des intérêts au début
			$ech_de_remboursement = $differe_ech + 1;
		} elseif ($mode_perc_int == 3) {
			// Perception des intérêts à la fin
			$ech_de_remboursement = $nbr_ech_total;
		}
		
		for($i = $premiere_echeance; $i <= $nbr_ech_total; $i ++) {
			// Pour chaque échéance	
						
			if($i == $premiere_echeance && $interets_proratise > 0) { // ajouter les intérêts proratisés s'il y en a
				$echeancier[$i]['mnt_int'] = $interets_proratise;				
			}
			else {
				$echeancier[$i]['mnt_int'] = 0;
			}			
			
			if ($i <= $differe_ech) {				
				// On est dans le différé, on ne rembourse que les intérêts
				$echeancier [$i] ['mnt_cap'] = 0;				
				$echeancier [$i] ['mnt_gar'] = 0;				
				
				if ($mode_perc_int == 1 || $mode_perc_int == 3) {				
					$sans_prorata =  round ( $capital * $tx_int_ech, $global_monnaie_courante_prec );			
					// Perception des intérêts au début ou à la fin
					$echeancier [$ech_de_remboursement] ['mnt_int'] += round ( $capital * $tx_int_ech, $global_monnaie_courante_prec );
				} else {
					// Intérêts inclus dans les remboursements					
					$sans_prorata =  round ( $capital * $tx_int_ech, $global_monnaie_courante_prec );					
					$echeancier [$i] ['mnt_int'] += round ( $capital * $tx_int_ech, $global_monnaie_courante_prec );
				}
				if ($Produit ['differe_epargne_nantie'] == 'f') {
					$echeancier [$i] ['mnt_gar'] = round ( $gar_encours / $nbr_ech_total, $global_monnaie_courante_prec );
				}
			} elseif ($i < $nbr_ech_total) {				
				// On rembourse les intérêts et le capital et on constitue la garantie
				if ($mode_perc_int == 1 || $mode_perc_int == 3) {					
					// Perception des intérêts au début ou à la fin
					$echeancier [$ech_de_remboursement] ['mnt_int'] += round ( ($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec );					
				} else {									
					// Intérêts inclus dans les remboursements
					$echeancier [$i] ['mnt_int'] += round ( ($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec );
				}
				if (($i - $differe_ech) % $freq_paiement_cap == 0)
					// L'échéance, hors différé, est un multiple de la fréquence de paiement
					$echeancier [$i] ['mnt_cap'] = round ( $mnt_ech - $echeancier [$i] ['mnt_int'], $global_monnaie_courante_prec );
				else
					$echeancier [$i] ['mnt_cap'] = 0;
				if ($Produit ['differe_epargne_nantie'] == 'f') {
					$echeancier [$i] ['mnt_gar'] = round ( $gar_encours / $nbr_ech_total, $global_monnaie_courante_prec );
				} else {
					$echeancier [$i] ['mnt_gar'] = round ( $gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec );
				}
			} else {
				// On est à la dernière échéance, on reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
				$echeancier [$i] ['mnt_cap'] = $capital - $mnt_cap;
				$echeancier [$i] ['mnt_gar'] = $gar_encours - $mnt_gar;
				
				if ($mode_perc_int == 1 || $mode_perc_int == 3) {
					// Perception des intérêts au début ou à la fin
					$echeancier [$ech_de_remboursement] ['mnt_int'] += round ( ($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec );
				} else {
					// Intérêts inclus dans les remboursements
					$echeancier [$i] ['mnt_int'] = round ( ($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec );
				}
			}			
			// On calcule les sommes pour tomber juste à la fin
			$mnt_cap += $echeancier [$i] ['mnt_cap'];
			$mnt_gar += $echeancier [$i] ['mnt_gar'];
			$mnt_int += $echeancier [$i] ['mnt_int'];
		}
		
//		if (($differe_jours > 0) || ($differe_jours < 0)) {
//			// On ajoute les intérêts sur le différé en jours à la première échéance
//			if ($Produit ["calcul_interet_differe"] == 't') { // si le calcule d'interêt diff'
//
//				$mnt_int_diff_jour = round ( $capital * $tx_int_ech * $prorata_temp, $global_monnaie_courante_prec );
//				/*
//				 * On ajoute les intérêts sur le différé en jours à la première ou a la derniere échéance selon le report choisi .les montants entre la première échéance et la dernière seront échanger
//				 */
//				if ($Produit ['report_arrondi'] == 't') {
//					$echeancier [$echeance_index] ['mnt_int'] += $mnt_int_diff_jour; // ca sera la pemiere écheance
//				} else {
//					if ($echeance_index != 1) {
//						$echeancier [$echeance_index] ['mnt_int'] += $mnt_int_diff_jour;
//					} else {
//						$echeancier [$nbr_ech_total] ['mnt_int'] += $mnt_int_diff_jour; // sera la premiere écheance
//					}
//				}
//			}
//		}
	}	

	// }}}
	// {{{ Mode dégressif capital constant
	
	elseif ($mode_calc_int == 4) {
		// Calcul de la somme à payer à chaque échéance
		$cap_ech = $capital * $freq_paiement_cap / $nbr_ech_remb;
		
		// Cas particuliers de perception des intérêts au début ou à la fin
		if ($mode_perc_int == 1) {
			// Perception des intérêts au début
			$ech_de_remboursement = $differe_ech + 1;
		} elseif ($mode_perc_int == 3) {
			// Perception des intérêts à la fin
			$ech_de_remboursement = $nbr_ech_total;
		}
		
		for($i = $premiere_echeance; $i <= $nbr_ech_total; $i ++) {
			// Pour chaque échéance					
			/* if($i == $premiere_echeance && $interets_proratise > 0) { // ajouter les intérêts proratisés s'il y en a
				$echeancier[$i]['mnt_int'] = $interets_proratise;				
			}
			else {
				$echeancier[$i]['mnt_int'] = 0;
			} */
			
			if ($i <= $differe_ech) {				
				// On est dans le différé, on ne rembourse que les intérêts
				$echeancier [$i] ['mnt_cap'] = 0;
				//$echeancier [$i] ['mnt_int'] = 0;
				$echeancier [$i] ['mnt_gar'] = 0;
				
				if ($mode_perc_int == 1 || $mode_perc_int == 3) {
					// Perception des intérêts au début ou à la fin
					$echeancier [$ech_de_remboursement] ['mnt_int'] += round ( $capital * $tx_int_ech, $global_monnaie_courante_prec );					
				} else {
					// Intérêts inclus dans les remboursements
					$echeancier [$i] ['mnt_int'] += round ( $capital * $tx_int_ech, $global_monnaie_courante_prec );
				}
				if ($Produit ['differe_epargne_nantie'] == 'f') {
					$echeancier [$i] ['mnt_gar'] = round ( $gar_encours / $nbr_ech_total, $global_monnaie_courante_prec );
				}
			} elseif ($i < $nbr_ech_total) {				
				// On rembourse les intérêts et le capital et on constitue la garantie
				if (($i - $differe_ech) % $freq_paiement_cap == 0)
					// L'échéance, hors différé, est un multiple de la fréquence de paiement
					$echeancier [$i] ['mnt_cap'] = round ( $cap_ech, $global_monnaie_courante_prec );
				else
					$echeancier [$i] ['mnt_cap'] = 0;
				if ($mode_perc_int == 1 || $mode_perc_int == 3) {
					// Perception des intérêts au début ou à la fin
					$echeancier [$ech_de_remboursement] ['mnt_int'] += round ( ($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec );
				} else {
					// Intérêts inclus dans les remboursements
					$echeancier [$i] ['mnt_int'] += round ( ($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec );
				}
				if ($Produit ['differe_epargne_nantie'] == 'f') {
					$echeancier [$i] ['mnt_gar'] = round ( $gar_encours / $nbr_ech_total, $global_monnaie_courante_prec );
				} else {
					$echeancier [$i] ['mnt_gar'] = round ( $gar_encours / $nbr_ech_remb, $global_monnaie_courante_prec );
				}
			} else {
				// On est à la dernière échéance, on reprend les arrondis en corrigeant le tir ! -> calcul à partir des montants cumulés
				$echeancier [$i] ['mnt_cap'] = $capital - $mnt_cap;
				$echeancier [$i] ['mnt_gar'] = $gar_encours - $mnt_gar;
				if ($mode_perc_int == 1 || $mode_perc_int == 3) {
					// Perception des intérêts au début ou à la fin
					$echeancier [$ech_de_remboursement] ['mnt_int'] += round ( ($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec );
				} else {
					// Intérêts inclus dans les remboursements
					$echeancier [$i] ['mnt_int'] = round ( ($capital - $mnt_cap) * $tx_int_ech, $global_monnaie_courante_prec );
				}
			}			
			// On calcule les sommes pour tomber juste à la fin
			$mnt_cap += $echeancier [$i] ['mnt_cap'];
			$mnt_gar += $echeancier [$i] ['mnt_gar'];
			$mnt_int += $echeancier [$i] ['mnt_int'];
		}
		
//		if (($differe_jours > 0) || ($differe_jours < 0)) {
//			// On ajoute les intérêts sur le différé en jours à la première échéance
//			if ($Produit ["calcul_interet_differe"] == 't') {
//				$mnt_int_diff_jour = round ( $capital * $tx_int_ech * $prorata_temp, $global_monnaie_courante_prec );
//				/*
//				 * On ajoute les intérêts sur le différé en jours à la première ou a la derniere échéance selon le report choisi .les montants entre la première échéance et la dernière seront échanger
//				 */
//				if ($Produit ['report_arrondi'] == 't') {
//					$echeancier [$echeance_index] ['mnt_int'] += $mnt_int_diff_jour; // sera la primiere écheance
//				} else {
//					if ($echeance_index != 1) {
//						$echeancier [$echeance_index] ['mnt_int'] += $mnt_int_diff_jour;
//					} else {
//						$echeancier [$nbr_ech_total] ['mnt_int'] += $mnt_int_diff_jour; // sera la premiere echeance
//					}
//				}
//			}
//		}
	} 	

	// }}}
	// {{{ Autre
	
	else
		signalErreur ( __FILE__, __LINE__, __FUNCTION__, _ ( "Mode de calcul des intérêts inconnu !" ) );
	
		// Si Report arrondi première échéance
	if ($Produit ["report_arrondi"] == 'f') {
		// Echanger les montants entre la première échéance et la dernière
		// Transfert du capital
		$tmp_cap = $echeancier [$premiere_echeance] ['mnt_cap'];
		$echeancier [$premiere_echeance] ['mnt_cap'] = $echeancier [$nbr_ech_total] ['mnt_cap'];
		$echeancier [$nbr_ech_total] ['mnt_cap'] = $tmp_cap;
		// Transfert des intérêts
		$tmp_int = $echeancier [$premiere_echeance] ['mnt_int'];
		$echeancier [$premiere_echeance] ['mnt_int'] = $echeancier [$nbr_ech_total] ['mnt_int'];
		$echeancier [$nbr_ech_total] ['mnt_int'] = $tmp_int;
		// Transfert des garanties
		$tmp_gar = $echeancier [$premiere_echeance] ['mnt_gar'];
		$echeancier [$premiere_echeance] ['mnt_gar'] = $echeancier [$nbr_ech_total] ['mnt_gar'];
		$echeancier [$nbr_ech_total] ['mnt_gar'] = $tmp_gar;
		// Transfert des soldes
		// du capital
		$tmp_solde_cap = $echeancier [$premiere_echeance] ['solde_cap'];
		$echeancier [$premiere_echeance] ['solde_cap'] = $echeancier [$nbr_ech_total] ['solde_cap'];
		$echeancier [$nbr_ech_total] ['solde_cap'] = $tmp_solde_cap;
		// des intérêts
		$tmp_solde_int = $echeancier [$premiere_echeance] ['solde_int'];
		$echeancier [$premiere_echeance] ['solde_int'] = $echeancier [$nbr_ech_total] ['solde_int'];
		$echeancier [$nbr_ech_total] ['solde_int'] = $tmp_solde_int;
		// des garanties
		$tmp_solde_gar = $echeancier [$premiere_echeance] ['solde_gar'];
		$echeancier [$premiere_echeance] ['solde_gar'] = $echeancier [$nbr_ech_total] ['solde_gar'];
		$echeancier [$nbr_ech_total] ['solde_gar'] = $tmp_solde_gar;
		// des pénalités
		$tmp_solde_pen = $echeancier [$premiere_echeance] ['solde_pen'];
		$echeancier [$premiere_echeance] ['solde_pen'] = $echeancier [$nbr_ech_total] ['solde_pen'];
		$echeancier [$nbr_ech_total] ['solde_pen'] = $tmp_solde_pen;
	}	
	return $echeancier;
}


/**
 * Calcul les interets en prorata lors d'un racourcissement
 * @param int $id_doss id_du dossier
 * @param int $mode_calc_int le mode de calcul d'interet du produit de credit
 * @param int $periodicite la periodicite du produit de credit
 * @param float $tx_interet le taux d'interet du produit de credit
 * @return number
 */
function getInteretsProrataForRaccourci($id_doss, $mode_calc_int, $periodicite, $tx_interet = NULL) 
{
	global $adsys;
	
	$interets_proratise_data = array();	
	
	if (! empty ( $id_doss ) && ! empty ( $mode_calc_int ))
	{
		// Proratisation des interets lors d'un racourcissement
		if ($mode_calc_int == 1 || $mode_calc_int == 2) { // constant, dégressif prédéfini			
			// recup date dernier echeance remboursé
			$dernier_ech_remb_data = getLastEchRembData ( $id_doss );
			// recup date premier echeance non-remboursé
			$firstEcheanceNonRembData = getFirstEcheanceNonRembData ( $id_doss );			
			
			if ($dernier_ech_remb_data && $firstEcheanceNonRembData) {
				$date_dernier_ech_remb = $dernier_ech_remb_data ['date_ech'];
				$date_raccourcissement = date ( "Y-m-d", mktime () );				
				$date_dernier_ech_remb = pg2phpDate ( $date_dernier_ech_remb );
				$date_raccourcissement = pg2phpDate ( $date_raccourcissement );
			
				// si racourcissement apres la date du premier echeance du, on proratise les interets
				$isAfter = isAfter ( $date_raccourcissement, $date_dernier_ech_remb );				
				
				if ($isAfter) {
					$nbr_jours_pro_rata = nbreDiffJours ( $date_raccourcissement, $date_dernier_ech_remb );					
					$duree_jours = $adsys ['adsys_duree_jours_periodicite'] [$periodicite];				
					
					if ($nbr_jours_pro_rata > 0) {
						$coeff_prorata = $nbr_jours_pro_rata / $duree_jours;
					}
					
					if ($coeff_prorata > 0) {
						$solde_interets_first_ech = $firstEcheanceNonRembData ['solde_int'];
						$interets_proratise_data['solde_interets_first_ech'] = $solde_interets_first_ech;
						$interets_proratise_data['coeff_prorata'] = $coeff_prorata;
						$interets_proratise_data['interets_proratise'] = $coeff_prorata * $solde_interets_first_ech;
						$interets_proratise_data['nbr_jours_pro_rata'] = $nbr_jours_pro_rata;
						$interets_proratise_data['duree_jours'] = $duree_jours;
					}
				}							
			}
		} elseif ($mode_calc_int == 3 || $mode_calc_int == 4)  // dégressif variable, dégressif capital constant
		{			
			$date_dernier_remb_data = getDateLastRemb ( $id_doss );			
									
			if(!empty($date_dernier_remb_data)) // si il y eu au moins une remboursement
			{
				$date_dernier_remb = $date_dernier_remb_data['date_remb'];				
				$date_raccourcissement = date ( "Y-m-d", mktime () );				
				$date_dernier_remb = pg2phpDate ( $date_dernier_remb );
				$date_raccourcissement = pg2phpDate ( $date_raccourcissement );			
				
				$isAfter = isAfter ( $date_raccourcissement, $date_dernier_remb );		
					
				if ($isAfter) {
					$nbr_jours_pro_rata = nbreDiffJours ($date_raccourcissement, $date_dernier_remb);				
					$duree_jours = $adsys ['adsys_duree_jours_periodicite'] [$periodicite];			
				
					if ($nbr_jours_pro_rata > 0 && !empty($tx_interet)) {
						$total_rembourse = getTotrestant ( $id_doss );					
						$total_capital_restant_du = $total_rembourse ['cap_rest'];							
						$interets_proratise_data['interets_proratise'] = $nbr_jours_pro_rata * $total_capital_restant_du * $tx_interet;						
						$interets_proratise_data['nbr_jours_pro_rata'] = $nbr_jours_pro_rata;					
						$interets_proratise_data['$total_capital_restant_du'] = $total_capital_restant_du;						
					}
				}
			}				
		}
	}		
	return $interets_proratise_data;
}
function getProrata($id_prod, $capital, $nbr_echeances, $differe_jours, $differe_ech, $periodicite = NULL, $id_doss = NULL)
{
    global $adsys, $global_id_agence;
    global $global_monnaie_courante, $global_monnaie_courante_prec;

    // {{{ Initialisations

    $retour = array ();

    // Récupération des infos via le produit de crédit
    $Produits = getProdInfo ( "WHERE id = $id_prod", $id_doss);
    $Produit = $Produits [0];
    if ($periodicite == NULL) {
        $periodicite = $Produit ["periodicite"];
    }

    $mode_calc_int = $Produit ["mode_calc_int"];
    $mode_perc_int = $Produit ["mode_perc_int"];
    $tx_interet = $Produit ["tx_interet"];
    $freq_paiement_cap = $Produit ["freq_paiement_cap"];
    $prc_gar_encours = $Produit ["prc_gar_encours"];
    $gar_encours = $prc_gar_encours * $capital;

    // Récupération du nombre de jours par an
    $Agence = getAgenceDatas ( $global_id_agence );
    if ($Agence ["base_taux"] == 1) // 360 jours
        $nbre_jours_an = 360;
    elseif ($Agence ["base_taux"] == 2) // 365 jours
        $nbre_jours_an = 365;

    // Calcule la durée d'une période (échéance)
    // $period contient la durée d'une échéance exprimée en type de durée de crédit : mois ou semaine
    if ($periodicite != 6)
        $period = $adsys ["adsys_duree_periodicite"] [$periodicite];
    else
        $period = $duree; // Remboursement en une fois

    $duree = $nbr_echeances * $period;

    // Calcul du nombre d'échéances de remboursement (hors différé)
    $nbr_ech_remb = $duree / $period;
    $nbr_ech_total = $nbr_ech_remb + $differe_ech;

    // Calcul du prorata temporis pour le différé en jours
    if ($periodicite == 8) // Périodicité hebdomadaire
        $nbr_jours_ech_courante = date ( "d", mktime ( 0, 0, 0, 0, date ( "d" ) + $period * 7, date ( "Y" ) ) );
    elseif ($Agence ["base_taux"] == 1)
        $nbr_jours_ech_courante = 30 * $period;
    else
        $nbr_jours_ech_courante = date ( "d", mktime ( 0, 0, 0, date ( "m" ) + $period, 0, date ( "Y" ) ) );

    if (($differe_jours > 0) || ($differe_jours < 0)) {
        $prorata_temp = $differe_jours / $nbr_jours_ech_courante;
        debug ( $prorata_temp, "temp" );
        /*
         * Code à activer si on veut créer une échéance supplémentaire pour le différé en jours
         * $premiere_echeance = 2; $echeancier[1]['mnt_cap'] = 0; $echeancier[1]['mnt_int'] = 0; $echeancier[1]['mnt_gar'] = 0;
         */
    } else {
        $prorata_temp = 1;
        // $premiere_echeance = 1;
    }
    $premiere_echeance = 1;

    // Taux d'intérêts ramené sur une période (pas utile pour le mode constant)
    if ($periodicite == 8)
        $tx_int_ech = $tx_interet * $period;
    else
        $tx_int_ech = $tx_interet * $period / 12;

    // Les montants cumulés, pour vérifier les arrondis sur la dernière échéance
    $mnt_cap = 0;
    $mnt_gar = 0;
    $mnt_int = 0;

    // Le montant total des interets a rembourser
    $int = 0;

    // Le flag pour la perception des intérêts au debut de l'échéancier
    $int_percus = false;

    // Calcul des interets proratisés pour le raccourcissement
    /*
     *  @todo: Remove comments when working on #431 again
    $interets_proratise_data = getInteretsProrataForRaccourci($id_doss, $mode_calc_int, $periodicite, $tx_interet);
    $interets_proratise = $interets_proratise_data['interets_proratise'];
    $coeff_prorata = $interets_proratise_data['coeff_prorata'];
    */

    /*
     * Le coefficient de calcule des interets pro-ratisé pour un raccourcissement
     * @TODO: Initialisé a zero pour le moment pour revenir en arriere sur le #431.
     * 	A completer
     */
    $coeff_prorata = 0;
    $interets_proratise = 0;
    $int_pro_rata = 0;


    // }}}
    //	Montage de l'échéancier
    // {{{ Mode Constant

//    if ($mode_calc_int == 1) {
//
//        // Montant total des intérêts à payer
//        if ($periodicite == 8) {
//            // Périodicité hebdomadaire
////            $int = $capital * ($duree + $differe_ech * $period) * $tx_interet;
//            $int = $capital * ($duree + $differe_ech * $period);
//        }
//        else {
////            $int = $capital * ($duree + $differe_ech * $period) * $tx_interet / 12;
//            $int = $capital * ($duree + $differe_ech * $period) / 12;
//        }
//
//
//        for ($i = $premiere_echeance; $i <= $nbr_ech_total; $i++) {
//            if ($mode_perc_int == 1 && !$int_percus) { // Perception des intérêts au début
//
//                if(!empty($coeff_prorata) && $i == 1) {
//                    $int_pro_rata = $coeff_prorata * $int; // calcul des interets prorata
//                }
//                $int_percus = true;
//            }
//            if ($mode_perc_int == 2) {
//                // Intérêts inclus dans les remboursements
//                if($Produit["calcul_interet_differe"] == 't') {
//                    if(!empty($coeff_prorata) && $i == 1) {
//                        $int_pro_rata = ($int / $nbr_ech_total) * $coeff_prorata; // calcul des interets prorata
//                    }
//                }else{
//                    if(!empty($coeff_prorata) && $i == 1) {
//                        $int_pro_rata = ($int / $nbr_ech_remb) * $coeff_prorata; // calcul des interets prorata
//                    }
//                }
//
//                if(!empty($int_pro_rata) && $i == 1) {
//                    $int += $int_pro_rata; // ajout pro rata dans l'interer de base
//                }
//            }
//        }
//
//        if (($differe_jours > 0) || ($differe_jours < 0)) {
//            // On ajoute les intérêts sur le différé en jours à la première échéance
//            if($Produit["calcul_interet_differe"] == 't'){
//                $mnt_int_diff_jour= round($int / $nbr_ech_total * $prorata_temp, $global_monnaie_courante_prec);
//            }
//        }
//    }
//
//    // }}}
//    // {{{ Mode dégressif prédéfini ou mode dégressif variable
//    // Ils sont identiques au niveau de l'échéancier théorique
//
//    elseif ($mode_calc_int == 2 || $mode_calc_int == 3) {
//        // Calcul de la somme à payer à chaque échéance
//        $mnt_ech = $capital * $tx_int_ech / (1 - 1 / pow ( 1 + $tx_int_ech, $nbr_ech_remb / $freq_paiement_cap ));
//
//        // Cas particuliers de perception des intérêts au début ou à la fin
//        if ($mode_perc_int == 1) {
//            // Perception des intérêts au début
//            $ech_de_remboursement = $differe_ech + 1;
//        } elseif ($mode_perc_int == 3) {
//            // Perception des intérêts à la fin
//            $ech_de_remboursement = $nbr_ech_total;
//        }
//
//        if (($differe_jours > 0) || ($differe_jours < 0)) {
//            if ($Produit ["calcul_interet_differe"] == 't') { // si le calcule d'interêt diff'
////                $mnt_int_diff_jour = round ( $capital * $tx_int_ech * $prorata_temp, $global_monnaie_courante_prec );
//                $mnt_int_diff_jour = round ( $capital * $prorata_temp, $global_monnaie_courante_prec );
//            }
//        }
//    }
//
//    // }}}
//    // {{{ Mode dégressif capital constant
//
//    else
        if (in_array($mode_calc_int, array(1, 2, 3, 4))) {
            // Calcul de la somme à payer à chaque échéance
            $cap_ech = $capital * $freq_paiement_cap / $nbr_ech_remb;

            // Cas particuliers de perception des intérêts au début ou à la fin
            if ($mode_perc_int == 1) {
                // Perception des intérêts au début
                $ech_de_remboursement = $differe_ech + 1;
            } elseif ($mode_perc_int == 3) {
                // Perception des intérêts à la fin
                $ech_de_remboursement = $nbr_ech_total;
            }

            if (($differe_jours > 0) || ($differe_jours < 0)) {
                // On ajoute les intérêts sur le différé en jours à la première échéance
                if ($Produit ["calcul_interet_differe"] == 't') {
    //                $mnt_int_diff_jour = round ( $capital * $tx_int_ech * $prorata_temp, $global_monnaie_courante_prec );
                    $mnt_int_diff_jour = round ( $capital * $prorata_temp, $global_monnaie_courante_prec );
                }
            }
        }

    // }}}
    // {{{ Autre

    else
        signalErreur ( __FILE__, __LINE__, __FUNCTION__, _ ( "Mode de calcul des intérêts inconnu !" ) );

    return $mnt_int_diff_jour;
}
?>