<?php

require_once 'lib/misc/tableSys.php';
require_once 'lib/misc/divers.php';

    /**
     * Calcul les intérêts pour un différé en jours à partir de données diverses.
     *
     * @param mixed $id_prod Produit de crédit
     * @param mixed $capital Montant en capital du crédit
     * @param mixed $duree Durée du crédit, en mois
     * @param mixed $differe_jours Différé en jours sur le premier remboursement
     * @param mixed $differe_ech Différé en échéances sur le premier remboursement
     * @return int intérêts encouru
     * @since 1.0
     * @author BD
     */
    function calcEchInterest($id_prod, $capital, $duree, $differe_jours, $differe_ech, $periodicite = NULL, $id_doss = NULL, $interet_actuelle = NULL)
    {

        global $adsys, $global_id_agence;
        global $global_monnaie_courante, $global_monnaie_courante_prec;

            $interets_attendus = 0;

            // Récupération des infos via le produit de crédit
            $Produits = getProdInfo("WHERE id = $id_prod", $id_doss);
            $Produit = $Produits[0];

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
        if ($Agence["base_taux"] == 1) { // 360 jours
                $nbre_jours_an = 360;
            } elseif ($Agence["base_taux"] == 2) { // 365 jours
                $nbre_jours_an = 365;
            }
        //get period for any periodicité from adsys_duree_periodicite
            if ($periodicite != 6) {
                $period = $adsys["adsys_duree_periodicite"][$periodicite];
            } else {
                $period = $duree; // Remboursement en une fois
            }

        // Calcul du nombre d'échéances de remboursement (hors différé)
        $nbr_ech_remb = $duree / $period;
        $nbr_ech_total = $nbr_ech_remb + $differe_ech;

            // Calcul du prorata temporis pour le différé en jours
        if ($periodicite == 8) {
                // Périodicité hebdomadaire
                $nbr_jours_ech_courante = date("d", mktime(0, 0, 0, 0, date("d") + $period * 7, date("Y")));
        } elseif ($Agence["base_taux"] == 1) {
                $nbr_jours_ech_courante = 30 * $period;
        } else {
            $nbr_jours_ech_courante = date("d", mktime(0, 0, 0, date("m") + $period, 0, date("Y")));
        }

        if ($differe_jours > 0) {
                $prorata_temp = $differe_jours / $nbr_jours_ech_courante;
        } else {
                $prorata_temp = 1;
        }

            // Mode Constant
        if (($mode_calc_int == 1) || ($mode_calc_int == 2)) {
                // Montant total des intérêts à payer
                if ($periodicite == 8) {
                    // Périodicité hebdomadaire

                    $int = $capital * ($duree + $differe_ech * $period) * $tx_interet;


                } else {

                    //tou cas
                    $int = $capital * ($duree + $differe_ech * $period) * $tx_interet / 12;
                }

                if ($differe_jours > 0) {
                    // On ajoute les intérêts sur le différé en jours à la première échéance
                    /*
                    if($Produit["calcul_interet_differe"] == 't'){

                         $mnt_int_diff_jour = round($int / $nbr_ech_total * $prorata_temp, $global_monnaie_courante_prec);

                         if ($Produit['report_arrondi'] =='t') {
                             $echeancier[$echeance_index]['mnt_int'] += $mnt_int_diff_jour; // ca sera la premiere écheance


                             $interets_attendus += $mnt_int_diff_jour;
                         }
                         else
                         {
                             if($echeance_index!=1) {
                                 $echeancier[$echeance_index]['mnt_int'] += $mnt_int_diff_jour;
                             }
                             else {
                                 $echeancier[$nbr_ech_total]['mnt_int'] += $mnt_int_diff_jour;//sera la premiere
                             }
                         }
                    }
                    */

//                    $interets_attendus = round($int / $nbr_ech_total * $prorata_temp, $global_monnaie_courante_prec);
                    $interets_attendus = round($interet_actuelle * $prorata_temp, $global_monnaie_courante_prec);
                }
            }

            // Dégressif variable  or Dégressif capital constant

            if ($mode_calc_int == 3 or $mode_calc_int == 4) {

                // Montant total des intérêts à payer
                if ($periodicite == 8 ) {
                    // Périodicité hebdomadaire
                    $int = $capital * ($duree + $differe_ech * $period) * $tx_interet;

                }else if ($periodicite == 5 ) {
                    // Périodicité annuelle
                    $int = $capital * ($duree + $differe_ech * $period) * $tx_interet/ $nbre_jours_an ;

                }else {
                    //tou cas
                    $int = $capital * ($duree + $differe_ech * $period) * $tx_interet / 12;
                }

            if ($differe_jours > 0) {
                $interets_attendus = round ( $interet_actuelle * $prorata_temp, $global_monnaie_courante_prec );
//                $interets_attendus = round ( $int / $nbr_ech_total * $prorata_temp, $global_monnaie_courante_prec );
            }
        }
		
        return $interets_attendus;
    }
