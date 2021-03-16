<?php
/* vim: set expandtab softtabstop=2 shiftwidth=2: */

/**
 * Génère le code XML pour les rapports client
 * @package Rapports
 */

require_once 'lib/misc/xml_lib.php';
require_once 'lib/multilingue/traductions.php';




function xml_clients($ids, $statut, $sect_act, $gest, $list_criteres) {
  /* Génère le code XML pour un rapport concernant tous les clients dont on transmet les ids;
     $statut, $qualite, $sect_act et $gest indiquent si un de ces éléments à fait partie des critères de recherche */
  global $adsys, $global_id_agence;
  $document = create_xml_doc("clients", "clients.dtd");
  //Element root
  $root = $document->root();
  if ($statut) $val="1";
  else $val="0";
  $root->set_attribute("exist_statut_juridique", $val);
  if ($sect_act) $val="1";
  else $val="0";
  $root->set_attribute("exist_sect_activite", $val);
  if ($gest) $val="1";
  else $val="0";
  $root->set_attribute("exist_gestionnaire", $val);

  //En-tête généraliste
  gen_header($root, 'CLI-GEN');

  $nbre_homme = 0;
  $nbre_femme = 0;
  $nbre_pm = 0;
  $nbre_gi = 0;
  $nbre_gs = 0;
  $total_mbre_gi = 0;
  $total_mbre_gs = 0;

  if (is_array($ids)) {
    reset($ids);
    while (list(,$value) = each($ids)) {
      $infos = get_info_rapport_client($value);
      switch ($infos['statut_juridique']) {
      case 1 :
        if ($infos['pp_sexe'] == 1) ++$nbre_homme;
        else ++$nbre_femme;
        break;
      case 2 :
        ++$nbre_pm;
        break;
      case 3 :
        {
          ++$nbre_gi;
          $total_mbre_gi += $infos['gi_nbre_membr'];
        }
        break;
      case 4 :
        {
        	++$nbre_gs;
        	$total_mbre_gs += $infos['gi_nbre_membr'];
        }
        break;
      default :
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Statut juridique inconnu !"
      }
    }
  }

  if (is_array($ids)) {
    reset($ids);
    $statut_courant = "";
    while (list(,$value) = each($ids)) { //Pour chaque client
      //Recup infos
      $infos = get_info_rapport_client($value);
      if ($statut_courant != $infos['statut_juridique']) {
        $statut_juridique = $root->new_child("statut_juridique", "");
        /*
        * FIXME : Est il necessaire de mettre l'en-tête ds cet element
        */
        gen_header($statut_juridique, 'CLI-GEN');
        $header_contextuel = $statut_juridique->new_child("header_contextuel", "");
        gen_criteres_recherche($header_contextuel, $list_criteres);
        $infos_synthetiques = $header_contextuel->new_child("infos_synthetiques", "");
        $stat_jur = $statut_juridique->new_child("stat_jur", htmlspecialchars($adsys["adsys_stat_jur"][$infos['statut_juridique']], ENT_QUOTES, "UTF-8"));
        $statut_courant = $infos['statut_juridique'];

        $infos_synthetiques->new_child("nbre_total", sizeof($ids));
        $infos_synthetiques->new_child("nbre_homme", $nbre_homme);
        $infos_synthetiques->new_child("nbre_femme", $nbre_femme);
        $infos_synthetiques->new_child("nbre_pm", $nbre_pm);
        $infos_synthetiques->new_child("nbre_gi", $nbre_gi);
        $infos_synthetiques->new_child("nbre_gs", $nbre_gs);
        $infos_synthetiques->new_child("total_mbre_gi",  $total_mbre_gi);
        $infos_synthetiques->new_child("total_mbre_gs",  $total_mbre_gs);
      }
      $client = $statut_juridique->new_child("client", "");
      //Num client
      $client->new_child("num_client", makeNumClient($value));
      //Nom client
      switch ($infos['statut_juridique']) {
      case 1 :
        $nom = $infos['pp_nom']." ".$infos['pp_prenom'];
        break;
      case 2 :
        $nom = $infos['pm_raison_sociale'];
        break;
      case 3 :
        $nom = $infos['gi_nom'];
        break;
      case 4 :
        $nom = $infos['gi_nom'];
        break;
      default :
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Statut juridique inconnu !"
      }
      $client->new_child("nom_client", $nom);
      //Sexe
      if ($infos['pp_sexe'] == 1) $val = "M";
      else if ($infos['pp_sexe'] == 2) $val = "F";
      else $val = "";
      $client->new_child("sexe", $val);
      //Date d'adhésion
      $client->new_child("date_adhesion", pg2phpDate($infos['date_adh']));
      //Statut juridique
      if ($statut)
        $client->new_child("statut_juridique", adb_gettext($adsys["adsys_stat_jur"][$infos['statut_juridique']]));
      //Secteur activite
      if ($sect_act)
        $client->new_child("sect_activite", getLibel("adsys_sect_activite", $infos['sect_act']));
      //Date de naissance
      $client->new_child("date_naissance", pg2phpDate($infos['pp_date_naissance']));
      //Gestionnaire
      if ($gest)
        $client->new_child("gestionnaire", getLibel("ad_uti", $infos['gestionnaire']));
      // Date de création
      if ($infos['date_crea'] != NULL) {
        $client->new_child("date_crea",pg2phpDate($infos['date_crea']));
        $statut_juridique->set_attribute("exist_date_crea", 1);

        // Nombre de membre si c'est un GI ou  GS
        if ($infos['gi_nbre_membr'] != NULL) {
          $client->new_child("nbr_membres",$infos['gi_nbre_membr']);
          $root->set_attribute("exist_nbr_membres", 1);

        }
        // etat
        $client->new_child("etat",$infos['etat']);
        $root->set_attribute("exist_etat", 1);
      }
    }
  }

  return $document->dump_mem(true);
}

function xmlConcentrationClients($DATACON, $list_criteres, $export_csv = false)
{
	global $adsys, $global_id_agence;
	$document = create_xml_doc("concentration_client", "concentration_client.dtd");
	//Element root
	$root = $document->root();
	//En-tête généraliste
	gen_header($root, 'CLI-CON');
	$header_contextuel = $root->new_child("header_contextuel", "");
	gen_criteres_recherche($header_contextuel, $list_criteres);

	//condition vérifiée dans le cas du monocritère
	if(count($DATACON[0]) > 0) {
		foreach($DATACON[0] as $key => $value) {
			$data = $root->new_child("monocritere", "");
			$data->new_child("libelle",$value['libel']);
			$data->new_child("nbre",$value['nbre']." -- ".$value['prc']);
  		}
	} 	// end of monocritere
		  
	// il s'agit ici du cas multi-critère
	elseif (count ( $DATACON [1] ) > 0) {
		
		foreach ( $DATACON [1] as $key_conc => $value_conc ) {
			
			// traitement pour liblocal par libel_stat
			
			$data = $root->new_child ( "tableau", "" );
			if (($value_conc ['libel_loc']) && (! $value_conc ['Loc_Sect_TransStat'])) {
				$data->new_child ( "liblocal", $value_conc ['libel_loc'] );
			} else if (($value_conc ['libel_stat_tableau']) && (! $value_conc ['libel_loc'])) {
				$data->new_child ( "liblocal", $value_conc ['libel_stat_tableau'] );
			} else if (($value_conc ['Lib_statutjuridique']) && (! $value_conc ['libel_loc'])) {
				$data->new_child ( "liblocal", $value_conc ['Lib_statutjuridique'] );
			} else if (($value_conc ['Loc_Sect_TransStat']) && ($value_conc ['libel_loc'])) {
				foreach ( $value_conc as $key2 => $value2 ) {
					$data->new_child ( "liblocal", $value2 ['libel_stat_tableau'] );
				}
			}
			
			if ((isset ( $value_conc ['libel_stat_loc_tranche'] ))) {
				for($i = 1; $i <= count ( $adsys ["adsys_tranche_age_client"] ); $i ++) {
					if ($i == 1)
						$lib = _ ( "Inférieur à 16 ans" );
					elseif ($i == count ( $adsys ["adsys_tranche_age_client"] ))
						$lib = _ ( "Supérieur à 95 ans" );
					else {
						$inf = substr ( $adsys ["adsys_tranche_age_client"] [$i], 0, 2 );
						$sup = substr ( $adsys ["adsys_tranche_age_client"] [$i], - 2 );
						$lib = "De $inf à $sup ans";
					}
					$data->new_child ( "libcolonne", $lib );
				}
			}
			// Gestion pour le rapport tranche d'age secteur et statut juridique(NEW )
			if (isset ( $value_conc ['libel_stat_tableau'] )) {
			  // gestion des tranches( tous tranches ou tranche individuel)
				if (($value_conc [0] ['tranche'] == 0) && (isset ( $value_conc [0] ['tranche'] ))) {
					
					$data->new_child ( "libcolonne", "toutes les tranches" ); // ici c'est sans tranche;
				} 				// tout les tranches
				elseif (($value_conc [0] ['tranche'] == 1) || (is_array ( $value_conc [0] ['tranche'] ))) {
					for($i = 1; $i <= count ( $adsys ["adsys_tranche_age_client"] ); $i ++) {
						if ($i == 1)
							$lib = _ ( "Inférieur à 16 ans" );
						elseif ($i == count ( $adsys ["adsys_tranche_age_client"] ))
							$lib = _ ( "Supérieur à 95 ans" );
						else {
							$inf = substr ( $adsys ["adsys_tranche_age_client"] [$i], 0, 2 );
							$sup = substr ( $adsys ["adsys_tranche_age_client"] [$i], - 2 );
							$lib = "De $inf à $sup ans";
						}
						$data->new_child ( "libcolonne", $lib );
					}
				}
				//PP personne homme et femme
			}
			else if ((isset ( $value_conc ['homme'] )) && (isset ( $value_conc ['femme'] ))) {
				for($i = 1; $i <= count ( $adsys ["adsys_tranche_age_client"] ); $i ++) {
					if ($i == 1)
						$lib = _ ( "Inférieur à 16 ans" );
					elseif ($i == count ( $adsys ["adsys_tranche_age_client"] ))
						$lib = _ ( "Supérieur à 95 ans" );
					else {
						$inf = substr ( $adsys ["adsys_tranche_age_client"] [$i], 0, 2 );
						$sup = substr ( $adsys ["adsys_tranche_age_client"] [$i], - 2 );
						$lib = "De $inf à $sup ans";
					}
					$data->new_child ( "libcolonne", $lib );
				}
			}
			else if ((! isset ( $value_conc ['stat_sect'] ))) {
				// gestion des tranches( tous tranches ou tranche individuel)(eX)
				if ((($value_conc ['tranche'] == 0) || ($value_conc ['tranche'] == 2)) && (isset ( $value_conc ['tranche'] ))) {
					$data->new_child ( "libcolonne", "toutes les tranches" ); // ici c'est sans tranche;
				} 				// tout les tranches
				else if (($value_conc ['tranche'] == 1) || (is_array ( $value_conc ['tranche'] ))) {
					for($i = 1; $i <= count ( $adsys ["adsys_tranche_age_client"] ); $i ++) {
						if ($i == 1)
							$lib = _ ( "Inférieur à 16 ans" );
						elseif ($i == count ( $adsys ["adsys_tranche_age_client"] ))
							$lib = _ ( "Supérieur à 95 ans" );
						else {
							$inf = substr ( $adsys ["adsys_tranche_age_client"] [$i], 0, 2 );
							$sup = substr ( $adsys ["adsys_tranche_age_client"] [$i], - 2 );
							$lib = "De $inf à $sup ans";
						}
						$data->new_child ( "libcolonne", $lib );
					}
				}
			} 

			else {
				// gestion des tranches( tous tranches ou tranche individuel)(eX)
				if ((($value_conc ['tranche'] == 0) || ($value_conc ['tranche'] == 2)) && (isset ( $value_conc ['tranche'] )) && (! isset ( $value_conc ['stat_sect'] ))) {
					$data->new_child ( "libcolonne", "toutes les tranches" ); // ici c'est sans tranche;
				} 				// tout les tranches
				else if (($value_conc ['tranche'] == 1) || (is_array ( $value_conc ['tranche'] ))) {
					for($i = 1; $i <= count ( $adsys ["adsys_tranche_age_client"] ); $i ++) {
						if ($i == 1)
							$lib = _ ( "Inférieur à 16 ans" );
						elseif ($i == count ( $adsys ["adsys_tranche_age_client"] ))
							$lib = _ ( "Supérieur à 95 ans" );
						else {
							$inf = substr ( $adsys ["adsys_tranche_age_client"] [$i], 0, 2 );
							$sup = substr ( $adsys ["adsys_tranche_age_client"] [$i], - 2 );
							$lib = "De $inf à $sup ans";
						}
						$data->new_child ( "libcolonne", $lib );
					}
				}
			}

	// Debut de generation xml parti donnees Commence ici
			// gestion des entete statut juridique
			if (($value_conc ['stat_sect']) == 1) {
				for($i = 1; $i <= count ( $adsys ["adsys_stat_jur"] ); $i ++) {

					if ($i == 1 && !isset($value_conc ['Loc_Sect_Stat'])) {
						$lib = adb_gettext ( $adsys ["adsys_stat_jur"] [$i] );
						$data->new_child ( "libcolonne", $lib.", Hommes" );
						$data->new_child ( "libcolonne", $lib.", Femmes" );
					} else {
						$lib = adb_gettext ( $adsys ["adsys_stat_jur"] [$i] );
						$data->new_child ( "libcolonne", $lib );
					}
				}
			}

			// absence de localisation, que SECTEUR & TRANCHE D'
			if (isset ( $value_conc ['lib_locX'] )) { // (lib_loc = toutes les localités)
				
				$dataligne = $data->new_child ( "ligne", "" );
				$dataligne->new_child ( "libligne", $value_conc ['libel_sect'] );
				foreach ( $value_conc ["tranche"] as $key => $value ) {
					$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
				}
			}
			
			// LOCALISATION ET TRANCHE D"AGE
			
			if (isset ( $value_conc ['libel_sectX'] )) {
				
				$dataligne = $data->new_child ( "ligne", "" );
				$dataligne->new_child ( "libligne", _ ( "tous les secteurs" ) );
				foreach ( $value_conc ["tranche"] as $key => $value ) {
					$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
				}
			}			

			// PART 3:Statut juridique(Personne physique only) et Tranche d'age (Retour Arnaud treated below)
			elseif (isset ( $value_conc ['femme'] ) && (isset ( $value_conc ['homme'] )) && (! isset ( $value_conc ['libel_stat_loc_tranche'] ))) {
				
				$dataligne = $data->new_child ( "ligne", "" );
				$dataligne->new_child ( "libligne", _ ( "Homme" ) );
				foreach ( $value_conc ["homme"] as $key => $value ) {
					$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
				}
				$dataligne = $data->new_child ( "ligne", "" );
				$dataligne->new_child ( "libligne", _ ( "Femme" ) );
				foreach ( $value_conc ["femme"] as $key => $value ) {
					$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
				}
			}			

			// PArt 2 : 
			// présence de localités et secteurs et eventuellement tranches d'âges
			elseif ((isset ( $value_conc ['libel_sect'] )) && (isset ( $value_conc ['lib_loc'] )) && (isset ( $value_conc ['tranche'] ))) {
				
				// parcours des secteurs ici
				foreach ( $value_conc as $key => $value ) {
					if (strlen ( $value ["libel_loc"] ) != 1)
						$dataligne = $data->new_child ( "ligne", "" );
					$dataligne->new_child ( "libligne", $value ['libel_sect'] );
					if ($value_conc ['tranche'] == 0)
						$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
					else {
						foreach ( $value ['tranche'] as $key => $value ) {
							$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
						}
					}
				}
			}
			
			// PART 4: SECTEUR & STATUT JURIDIQUE
			if ((isset ( $value_conc ['stat_sect'] )) && (! isset ( $value_conc ['Loc_Sect_Stat'] ))) {
				
				$dataligne = $data->new_child ( "ligne", "" );
				$dataligne->new_child ( "libligne", $value_conc ['libel_sect'] );
				foreach ( $value_conc as $key => $value ) {
					if (is_array ( $value )) {
						$dataligne->new_child ( "nbreparcellule", $value [0] . " -- " . $value [1] );
					}
				}
			}
			
			// part 7 localisation ,secteur,statut_juridique
			if ((isset ( $value_conc ['stat_sect'] )) && (isset ( $value_conc ['Loc_Sect_Stat'] ))) {
				
				if (is_array ( $value_conc )) {
					foreach ( $value_conc as $key => $value ) {
						$dataligne = $data->new_child ( "ligne", "" );
						if (is_array ( $value )) {
							$dataligne->new_child ( "libligne", $value ['libel_sect'] );
						}
						if (is_array ( $value )) {
							foreach ( $value as $key1 => $value1 ) {
								if (is_array ( $value1 )) {
									$dataligne->new_child ( "nbreparcellule", $value1 ['nbre'] . " -- " . $value1 ['prc'] );
								}
							}
						}
					}
				}
			}			

			// part 7
			elseif (isset ( $value_conc ['libel_stat_loc_tranche'] )) {
				
				$dataligne = $data->new_child ( "ligne", "" );
				$dataligne->new_child ( "libligne", _ ( "Personne Physique (Homme)" ) );
				foreach ( $value_conc ["homme"] as $key => $value ) {
					$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
				}
				$dataligne = $data->new_child ( "ligne", "" );
				$dataligne->new_child ( "libligne", _ ( "Personne Physique (Femme)" ) );
				foreach ( $value_conc ["femme"] as $key => $value ) {
					$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
				}
				
				// generation xml parti Statut juridique ,secteurs, et Tranche d'age
				// Part5 : Amelioration
				// Note: traitment de tranche : customiser en haut
			}
			elseif (isset ( $value_conc ['libel_stat_tableau'] )) {
				
				foreach ( $value_conc as $key2 => $value2 ) {
					if (is_array ( $value2 )) {
						
						if (strlen ( $value2 ["libel_loc"] ) != 1)
							$dataligne = $data->new_child ( "ligne", "" );
						$dataligne->new_child ( "libligne", $value2 ['libel_sect'] );
						foreach ( $value2 as $key3 => $value3 ) {
							
							if (is_array ( $value3 )) {
								foreach ( $value3 as $key4 => $value4 ) {
									
									$dataligne->new_child ( "nbreparcellule", $value4 ['nbre'] . " -- " . $value4 ['prc'] );
								}
							}
						}
					}
				}
			} 			

			// working for all partXX
			else if ((! isset ( $value_conc ['libel_sectX'] )) && (! isset ( $value_conc ['lib_locX'] )) && (! isset ( $value_conc ['stat_sect'] )) && (! isset ( $value_conc ['homme'] ))) {
				
				foreach ( $value_conc as $key => $value ) {
					if (strlen ( $value ["libel_loc"] ) != 1) {
						$dataligne = $data->new_child ( "ligne", "" );
					}
					if (isset ( $value ['libel_stat'] )) {
						$dataligne->new_child ( "libligne", $value ['libel_stat'] );
					}
					if (isset ( $value ['libel_sect'] )) {
						$dataligne->new_child ( "libligne", $value ['libel_sect'] );
					}
					if (($value_conc ['tranche'] == 0) || ($value_conc ['tranche'] == 2))
						$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
					else {
						foreach ( $value ['tranche'] as $key => $value ) {
							$dataligne->new_child ( "nbreparcellule", $value ['nbre'] . " -- " . $value ['prc'] );
						}
					}
				}
			}
		} // end of foreach
	}//end of multi critere
	
	return $document->dump_mem(true);
}

function xml_repartition_client($DATA, $nombre_client, $liste_criteres) {
  global $adsys,$global_id_agence;
  //reset($DATA);
  // Création racine
  $document = create_xml_doc("repartition_client", "repartition_client.dtd");

  //Element root
  $root = $document->root();

  //En-tête généraliste

  gen_header($root, 'CLI-ETA');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $liste_criteres);

  $nb_att_valid = 0;
  $nb_actif = 0;
  $nb_decede = 0;
  $nb_transfere = 0;
  $nb_demission = 0;
  $nb_radie = 0;
  $nb_enreg_dece = 0;
  $nb_non_ins = 0;
  $nb_att_sold_cre_gar = 0;

  $Where = array();
  $etaName = array(_("En attente de validation"), _("Actif"), _("Décédé"), _("Transféré"), _("Démissionnaire"), _("Radié"), _("En attente enregistrement décès"), _("Non-inscrit"), _("En attente solde crédit garanti"));
//  $nombre = countMatchedClients ($Where, "*");
//  $DATA = getMatchedClients($Where, "*");
//  //debug($DATA);

  //Pour chaque état on affiche les inforamtions sur les clients concernés

  // clients en attente de validation
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[0]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 1) {
      ++$nb_att_valid;
      $clients = $detail_etat->new_child("clients","");

      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }

  // clients actifs
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[1]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 2) {
      ++$nb_actif;
      $clients = $detail_etat->new_child("clients","");

      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }

  //clients décédés
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[2]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 3) {
      ++$nb_decede;
      $clients = $detail_etat->new_child("clients","");

      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }

  //clients tranférés
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[3]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 4) {
      ++$nb_transfere;
      $clients = $detail_etat->new_child("clients","");
      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }

  //clients démissionnaires
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[4]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 5) {
      ++$nb_demission;
      $clients = $detail_etat->new_child("clients","");
      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }

  //clients radiés
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[5]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 6) {
      ++$nb_radie;
      $clients = $detail_etat->new_child("clients","");

      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }

  //clients en attente enregistrement décés
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[6]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 7) {
      ++$nb_enreg_dece;
      $clients = $detail_etat->new_child("clients","");

      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }

  //clients non inscrits
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[7]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 8) {
      ++$nb_non_ins;
      $clients = $detail_etat->new_child("clients","");

      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }

  //clients en attente solde crédit garanti
  $detail_etat=$root->new_child("detail_etat","");
  $detail_etat->set_attribute("type",$etaName[8]);
  for ($i = 0; $i < $nombre_client; $i++) {
    if ($DATA[$i]["etat"] == 9) {
      ++$nb_att_sold_cre_gar;
      $clients = $detail_etat->new_child("clients","");

      $clients->new_child("id_client",makeNumClient($DATA[$i]["id_client"]));
      if ($DATA[$i]["statut_juridique"] == 1) {
        $clients->new_child("nom",$DATA[$i]["pp_nom"]." ".$DATA[$i]["pp_prenom"]);
        if ($DATA[$i]["pp_sexe"] == 1)
          $clients->new_child("statut","H");
        else if ($DATA[$i]["pp_sexe"] == 2)
          $clients->new_child("statut","F");
      } else if ($DATA[$i]["statut_juridique"] == 2) {
        $clients->new_child("nom",$DATA[$i]["pm_raison_sociale"]);
        $clients->new_child("statut","PM");
      } else if ($DATA[$i]["statut_juridique"] == 3) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GI");
      } else if ($DATA[$i]["statut_juridique"] == 4) {
        $clients->new_child("nom",$DATA[$i]["gi_nom"]);
        $clients->new_child("statut","GS");
      }
      $clients->new_child("date_adh",pg2phpDate($DATA[$i]["date_adh"]));
      $clients->new_child("date_etat",pg2phpDate($DATA[$i]["date_defection"]));
    }
  }
  $total = $root->new_child("total", "");
  $total->new_child("en_att_valid", $nb_att_valid);
  $total->new_child("prc_en_att_valid", affichePourcentage(($nb_att_valid/$nombre_client), 2, false));
  $total->new_child("nb_cli_actif", $nb_actif);
  $total->new_child("prc_nb_cli_actif", affichePourcentage(($nb_actif/$nombre_client), 2, false));
  $total->new_child("cli_deces", $nb_decede);
  $total->new_child("prc_cli_deces", affichePourcentage(($nb_decede/$nombre_client), 2, false));
  $total->new_child("cli_transfere", $nb_transfere);
  $total->new_child("prc_cli_transfere", affichePourcentage(($nb_transfere/$nombre_client), 2, false));
  $total->new_child("cli_demission", $nb_demission);
  $total->new_child("prc_cli_demission", affichePourcentage(($nb_demission/$nombre_client), 2, false));
  $total->new_child("cli_radie", $nb_radie);
  $total->new_child("prc_cli_radie", affichePourcentage(($nb_radie/$nombre_client), 2, false));
  $total->new_child("cli_enreg_deces", $nb_enreg_dece);
  $total->new_child("prc_cli_enreg_deces", affichePourcentage(($nb_enreg_dece/$nombre_client), 2, false));
  $total->new_child("cli_non_ins", $nb_non_ins);
  $total->new_child("prc_cli_non_ins", affichePourcentage(($nb_non_ins/$nombre_client), 2, false));
  $total->new_child("cli_att_sold_gar", $nb_att_sold_cre_gar);
  $total->new_child("prc_cli_att_sold_gar", affichePourcentage(($nb_att_sold_cre_gar/$nombre_client), 2, false));
  return $document->dump_mem(true);
}

function xml_sit_globale_clients($DATA, $export_csv = false) {
  /* Génère le code XML pour un rapport concernant la situation globale du client
     $DATA contient les informations sur le crédit et l'épargne */

  global $global_id_client,$global_id_agence;
  global $adsys;

  $document = create_xml_doc("situation_client", "situation_client.dtd");
  //  $document = create_xml_doc("situation_client");
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'CLI-SIT');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  $header_contextuel->set_attribute("stat_jur", $DATA['num_stat_jur']);
  $header_contextuel->new_child("num_client",makeNumClient(intval($DATA["num_client"])));
  $header_contextuel->new_child("nom_client",htmlspecialchars($DATA["nom_client"]));
  if ($DATA['num_stat_jur'] == 1) {
    $header_contextuel->new_child("pp_date_naissance",$DATA["pp_date_naissance"]);
    $header_contextuel->new_child("pp_lieu_naissance",$DATA["pp_lieu_naissance"]);
  }
  $header_contextuel->new_child("statut_juridique",$DATA["statut_juridique"]);
  $header_contextuel->new_child("qualite",$DATA["qualite"]);
  $header_contextuel->new_child("etat_client",$DATA["etat_client"]);
  $header_contextuel->new_child("date_adhesion",$DATA["date_adhesion"]);
  $header_contextuel->new_child("nbre_ps",$DATA["nbre_ps"]);
  $header_contextuel->new_child("gestionnaire",$DATA["gestionnaire"]);

  if (is_array($DATA["PS"])) {
    reset($DATA["PS"]); echo _("dans la boucle")." if";
    $ps = $root->new_child("ps");
    while (list($key,$part_soc) = each($DATA["PS"])) { //Pour chaque ps
      //Recup infos
      //  situation_ps(num_cpte, date_ouvert, lib_prod, solde_cpte, date_dern_mvt)
      $situation_ps = $ps->new_child("situation_ps","");
      $situation_ps->new_child("num_complet_cpte", $part_soc["num_complet_cpte"]);
      $situation_ps->new_child("intitule_compte", $part_soc["intitule_compte"]);
      $situation_ps->new_child("id_client", $part_soc["id_titulaire"]);
      $situation_ps->new_child("date_ouvert", $part_soc["date_ouvert"]);
      $situation_ps->new_child("prod_epargne",$part_soc["libel_prod"]);
      setMonnaieCourante($part_soc["devise"]);
      $situation_ps->new_child("solde_cpte",afficheMontant(recupMontant($part_soc["solde_cpte"]),false, $export_csv));
      $situation_ps->new_child("mnt_bloq",afficheMontant(recupMontant($part_soc["mnt_bloq"]),false, $export_csv));
      $situation_ps->new_child("mnt_disp",afficheMontant(recupMontant($part_soc["mnt_disp"]),false, $export_csv));
    }
  }
  if (is_array($DATA["EPARGNE"])) {
    // afficheMontant  setMonnaieCourante($epargne["devise"]);

    reset($DATA["EPARGNE"]);
    $epargnes = $root->new_child("epargnes");
    while (list($key,$epargne) = each($DATA["EPARGNE"])) { //Pour chaque crédit
      //Recup infos
      //  situation_epargne(num_cpte, date_ouvert, lib_prod, solde_cpte, date_dern_mvt)
      $situation_epargne = $epargnes->new_child("situation_epargne","");
      $situation_epargne->new_child("num_complet_cpte", $epargne["num_complet_cpte"]);
      $situation_epargne->new_child("intitule_compte", $epargne["intitule_compte"]);
      $situation_epargne->new_child("id_client", $epargne["id_titulaire"]);
      $situation_epargne->new_child("date_ouvert", $epargne["date_ouvert"]);
      $situation_epargne->new_child("prod_epargne",$epargne["libel_prod"]);
      $situation_epargne->new_child("date_dern_mvt", $epargne["date_dernier_mvt"]);
      setMonnaieCourante($epargne["devise"]);
      $situation_epargne->new_child("solde_cpte",afficheMontant(recupMontant($epargne["solde_cpte"]),false, $export_csv));
      setMonnaieCourante($epargne["devise"]);
      $situation_epargne->new_child("mnt_bloq",afficheMontant(recupMontant($epargne["mnt_bloq"]),false, $export_csv));
      setMonnaieCourante($epargne["devise"]);
      $situation_epargne->new_child("mnt_disp",afficheMontant(recupMontant($epargne["mnt_disp"]),false, $export_csv));
      setMonnaieCourante($epargne["devise"]);
      $situation_epargne->new_child("solde_calcul_interets", afficheMontant(recupMontant($epargne["solde_calcul_interets"]),false, $export_csv));

    }
  }
  if (is_array($DATA["ORD"])) {
    reset($DATA["ORD"]);
    $ord = $root->new_child("ord");
    while (list($key,$ordInfo) = each($DATA["ORD"])) { //Pour chaque ordre permanents
      //Recup infos
      $situation_ord = $ord->new_child("situation_ord","");
      $situation_ord->new_child("num_cpte_ord", $ordInfo["cpte_destination"]);
      $situation_ord->new_child("prod", $ordInfo["prod_libel"]);
      $situation_ord->new_child("date_ouverture", pg2phpDate($ordInfo["date_ouverture"]));
      $situation_ord->new_child("montant",afficheMontant(recupMontant($ordInfo["montant_virement"]),false, $export_csv));
      $situation_ord->new_child("periodicite",adb_gettext($adsys["adsys_periodicite_ordre_perm"][$ordInfo['periodicite']]));
      $situation_ord->new_child("date_fin", pg2phpDate($ordInfo["date_fin"]));
      $situation_ord->new_child("mnt_solde", afficheMontant(recupMontant($ordInfo["solde"]),false, $export_csv));

    }
  }
  if (is_array($DATA['GAR'])) {
    reset($DATA["GAR"]);
    $garanties = $root->new_child("garanties");
    while (list($key,$gar) = each($DATA["GAR"])) //Pour chaque crédit dont le client est garant
      // situation_garant(id_doss, num_client, nom_client, mnt_garanties, mnt_credit, etat)
    {
      $situation_garant = $garanties->new_child("situation_garant", "");
      $situation_garant->new_child("id_doss", $gar['id_doss']);
      $situation_garant->new_child("id_client", makeNumClient($gar['id_client']));
      $situation_garant->new_child("nom_client", $gar['nomClient']);
      $situation_garant->new_child("num_cpte", $gar['num_cpte']);
      setMonnaieCourante($gar["devise"]);
      $situation_garant->new_child("gar_num", afficheMontant(recupMontant($gar["gar_num"]),false, $export_csv));
      setMonnaieCourante($gar["devise"]);
      $situation_garant->new_child("mnt", ($gar['etat'] >= 5 ? afficheMontant(recupMontant($gar["cre_mnt_octr"]),false, $export_csv) : afficheMontant(recupMontant($gar["mnt_dem"]),false, $export_csv))); // Montant = montant octroyé si le crédit a déjà été déboursé et montant demandé sinon.
      $ET = getTousEtatCredit();
      // Etat = etat du dossier + etat du crédit si fonds déboursés, uniquement état du dossier dans les autre cas.
      $situation_garant->new_child("etat", ($gar['etat'] != 5 ? adb_gettext($adsys['adsys_etat_dossier_credit'][$gar['etat']]) : adb_gettext($adsys['adsys_etat_dossier_credit'][$gar['etat']]).", ".$ET[$gar['cre_etat']]["libel"]));
    }
  }

  if (is_array($DATA["CREDIT"])) {
    reset($DATA["CREDIT"]);
    $credits = $root->new_child("credits");
    $total = $root->new_child("total", "");
    while (list($key,$credit) = each($DATA["CREDIT"])) { //Pour chaque crédit
      //Recup infos
      $situation_credit= $credits->new_child("situation_credit","");

      if ($credit["cre_date_approb"]!="") $val="1";
      else $val="0";
      $situation_credit->set_attribute("exist_date_approb", $val);
      if ($credit["cre_date_debourse"]!="") $val="1";
      else $val="0";
      $situation_credit->set_attribute("exist_date_debourse", $val);
      if ($credit["cre_mnt_octr"]>0) $val="1";
      else $val="0";
      $situation_credit->set_attribute("exist_mnt_octr", $val);

      $situation_credit->new_child("id_doss", $credit["id_doss"]);
      $situation_credit->new_child("libel_prod",$credit["libel_prod"]);
      $situation_credit->new_child("id_client",$credit["id_client"]);
      $situation_credit->new_child("date_dem", $credit["date_dem"]);
      $situation_credit->new_child("cre_date_approb", $credit["cre_date_approb"]);
      $situation_credit->new_child("cre_date_debourse", $credit["cre_date_debourse"]);
      setMonnaieCourante( $credit["devise"]);
      $situation_credit->new_child("mnt_dem", afficheMontant($credit["mnt_dem"],false, $export_csv));
      setMonnaieCourante( $credit["devise"]);
      // Montant = montant octroyé si le cr"dit a déjà été déboursé et montant demandé sinon.
      $situation_credit->new_child("cre_mnt_octr", afficheMontant($credit["cre_mnt_octr"],false,$export_csv));

      $ET = getTousEtatCredit();
      $situation_credit->new_child("etat", ($credit['etat'] != 5 ? adb_gettext($adsys['adsys_etat_dossier_credit'][$credit['etat']]) : adb_gettext($adsys['adsys_etat_dossier_credit'][$credit['etat']]).", ".$ET[$credit['cre_etat']]["libel"]));
      $situation_credit->new_child("cre_etat", $credit["cre_etat"]);
      $situation_credit->new_child("nbre_ech", $credit["nbre_ech"]);
      $situation_credit->new_child("nbre_ech_remb", $credit["nbre_ech_remb"]);
    }
  }
  return $document->dump_mem(true);
}
/**
 * Création du fichier XML contenant la liste des clients et leurs comptes
 */
function xml_liste_clients_comptes($DATA) {

  reset($DATA);

  // Création racine
  $document = create_xml_doc("liste_client_compte", "liste_clients_comptes.dtd");

  //Element root
  $root = $document->root();
  set_time_limit(0);
  //En-tête généraliste

  gen_header($root, 'CLI-EXP');
  //En-tête contextuel

  $detail_client=$root->new_child("detail_client_compte","");
  reset($DATA->param);
  $i=0;
  foreach($DATA->param as $cle => $valeur) {
    $client=$detail_client->new_child("client","");
    $client->new_child("id_client",makeNumClient($valeur["id_client"]));
    $client->new_child("nom",$valeur["pp_nom"]." ".$valeur["pp_prenom"]);
    $client->new_child("date_naiss",$valeur["pp_date_naissance"]);
    $client->new_child("sexe",$valeur["pp_sexe"]);
    $trad_chaine_type_piece=new Trad($valeur["libel"]);
    $client->new_child("type_piece",$trad_chaine_type_piece->traduction());
    $client->new_child("numero_piece",$valeur["pp_nm_piece_id"]);
    $client->new_child("adresse",$valeur["adresse"]);
    $client->new_child("telephone",$valeur["num_tel"]);
    $client->new_child("telecopie",$valeur["num_fax"]);
    $client->new_child("portable",$valeur["num_port"]);
    $client->new_child("email",$valeur["email"]);
    $client->new_child("compte",$valeur["num_complet_cpte"]);
    $client->new_child("pays",$valeur["libel_pays"]);
    $client->new_child("ville",$valeur["ville"]);

  }
  return $document->dump_mem(true);
}
/**
 * Generateur XML pour le Rapport « liste des sociétaires de l'institution " partie 2 du rapports
 * @author Kheshan A.G
 * @return Array $DATAS Tableau de données à afficher sur la rapport liste de societaires
 */
function xml_liste_societaires($DATA,$DATA_comp,$critere=NULL,$export_csv = false) {
  reset($DATA);
  // Création racine
  $document = create_xml_doc("liste_societaires", "liste_societaires.dtd");

  //Element root
  $root = $document->root();
 
  //En-tête généraliste
  gen_header($root, 'CLI-SOC');
  $liste_societaires_init = $root->new_child("liste_societaires_init", "");

  // En-tête contextuel
  $header_contextuel = $liste_societaires_init->new_child("header_contextuel", "");
  gen_criteres_recherche($header_contextuel, $critere);
  // Totaux
  $total = $header_contextuel->new_child("total", "");
  $total->new_child("nbre_soc", $DATA["nbre_societaires"]);
  $total->new_child("nbre_ps", $DATA["total_ps"]);
  $total->new_child("nbre_ps_lib", $DATA["total_ps_lib"]);
  $total->new_child("capital_social", afficheMontant($DATA["capital_social"],true));
  $total->new_child("capital_social_souscrites", afficheMontant($DATA["capital_social_souscrites"],true));
  $total->new_child("capital_social_lib", afficheMontant($DATA["capital_social_lib"],true));
  $total->new_child("capital_social_restant", afficheMontant($DATA["capital_social_restant"],true));
  $total->new_child("valeurnominale", afficheMontant($DATA["valnomps"],true));
  
  while (list($cle,$valeur)=each($DATA["details"])) 
  {
  	if (!empty($DATA["details"][$cle])){
    $detail_stat_jur=$liste_societaires_init->new_child("detail_stat_jur","");
    
    if ($cle == "pp")
      $attName = "Personnes physiques";
    else if ($cle == "pm")
      $attName = "Personnes morales";
    else if ($cle == "gi")
      $attName = "Groupes informels";
    else if ($cle == "gs")
      $attName = "Groupes solidaires";

    $detail_stat_jur->set_attribute("type",$attName);
    $detail_stat_jur->new_child("nbre_ps",$DATA["total_stat_jur"][$cle]["total_ps"]);
    $detail_stat_jur->new_child("nbre_ps_lib",$DATA["total_stat_jur"][$cle]["total_ps_lib"]);
    $detail_stat_jur->new_child("nbre_soc",$DATA["total_stat_jur"][$cle]["total_soc"]);
    $detail_stat_jur->new_child("tot_solde_sous",$DATA["total_stat_jur"][$cle]["total_soldePS_sous"]);
    $detail_stat_jur->new_child("tot_solde_lib",$DATA["total_stat_jur"][$cle]["total_soldePS_lib"]);  
    $detail_stat_jur->new_child("tot_solde_restant",$DATA["total_stat_jur"][$cle]["total_soldePS_restant"]);
    
    while (list(,$infoclient)=each($DATA["details"][$cle])) {
      $client=$detail_stat_jur->new_child("client","");

      $client->new_child("id_client",makeNumClient($infoclient["id_client"]));
      $client->new_child("nom",$infoclient["nom"]);
      $client->new_child("nbre_ps",$infoclient["nbre_parts"]);
      $client->new_child("nbre_ps_lib",$infoclient["nbre_parts_lib"]);
      $client->new_child("solde_ps_sous",afficheMontant($infoclient["soldePSSouscrites"],false, $export_csv));
      $client->new_child("solde_ps_lib",afficheMontant($infoclient["soldePSLib"],false, $export_csv));
      $client->new_child("solde_ps_restant",afficheMontant($infoclient["soldePSRestant"],false, $export_csv));

    }
  }
  }
  //Le rapport complémentaire de la liste de societaires
  reset($DATA_comp);
  $liste_societaires_comp = $root->new_child("liste_societaires_comp", "");
  $header_contextuel_comp = $liste_societaires_comp->new_child("header_contextuel_comp", "");
  // En-tête contextuel
  gen_criteres_recherche($header_contextuel_comp, $critere);
  // Totaux
  $total_comp = $header_contextuel_comp->new_child("total_comp", "");
  $total_comp->new_child("nbre_soc_comp", $DATA_comp["nbre_societaires"]);
  $total_comp->new_child("nbre_ps_comp", $DATA_comp["total_ps"]);
  $total_comp->new_child("nbre_ps_lib_comp", $DATA_comp["total_ps_lib"]);
  $total_comp->new_child("capital_social_comp", afficheMontant($DATA_comp["capital_social"],true));
  $total_comp->new_child("capital_social_souscrites_comp", afficheMontant($DATA_comp["capital_social_souscrites"],true));
  $total_comp->new_child("capital_social_lib_comp", afficheMontant($DATA_comp["capital_social_lib"],true));
  $total_comp->new_child("capital_social_restant_comp", afficheMontant($DATA_comp["capital_social_restant"],true));
  $total_comp->new_child("valeurnominale_comp", afficheMontant($DATA_comp["valnomps"],true));
  
  while (list($cle,$valeur)=each($DATA_comp["details"]))
  {
  	if (!empty($DATA_comp["details"][$cle])){
  		$detail_stat_jur_comp=$liste_societaires_comp->new_child("detail_stat_jur_comp","");
  
  		if ($cle == "pp")
  			$attName = "Personnes physiques";
  		else if ($cle == "pm")
  			$attName = "Personnes morales";
  		else if ($cle == "gi")
  			$attName = "Groupes informels";
  		else if ($cle == "gs")
  			$attName = "Groupes solidaires";
  
  		$detail_stat_jur_comp->set_attribute("type_comp",$attName);
  		$detail_stat_jur_comp->new_child("nbre_ps_comp",$DATA_comp["total_stat_jur"][$cle]["total_ps"]);
  		$detail_stat_jur_comp->new_child("nbre_ps_lib_comp",$DATA_comp["total_stat_jur"][$cle]["total_ps_lib"]);
  		$detail_stat_jur_comp->new_child("nbre_soc_comp",$DATA_comp["total_stat_jur"][$cle]["total_soc"]);
  		$detail_stat_jur_comp->new_child("tot_solde_sous_comp",$DATA_comp["total_stat_jur"][$cle]["total_soldePS_sous"]);
  		$detail_stat_jur_comp->new_child("tot_solde_lib_comp",$DATA_comp["total_stat_jur"][$cle]["total_soldePS_lib"]);
  		$detail_stat_jur_comp->new_child("tot_solde_restant_comp",$DATA_comp["total_stat_jur"][$cle]["total_soldePS_restant"]);
  
  		while (list(,$infoclient)=each($DATA_comp["details"][$cle])) {
  			$client_comp=$detail_stat_jur_comp->new_child("client_comp","");
  
  			$client_comp->new_child("id_client_comp",makeNumClient($infoclient["id_client"]));
  			$client_comp->new_child("nom_comp",$infoclient["nom"]);
  			$client_comp->new_child("nbre_ps_comp",$infoclient["nbre_parts"]);
  			$client_comp->new_child("nbre_ps_lib_comp",$infoclient["nbre_parts_lib"]);
  			$client_comp->new_child("solde_ps_sous_comp",afficheMontant($infoclient["soldePSSouscrites"],false, $export_csv));
  			$client_comp->new_child("solde_ps_lib_comp",afficheMontant($infoclient["soldePSLib"],false, $export_csv));
  			$client_comp->new_child("solde_ps_restant_comp",afficheMontant($infoclient["soldePSRestant"],false, $export_csv));
  
  		}
  	}
  }


  return $document->dump_mem(true);
}

function xml_fiche_personne_physique($DATA) {
  //Génération de code XML pour la fiche d'une personne physique
  //DATA contient la liste des informatgions du cleint
  //la liste des critères est un tableau associatif contient les critères de sélection : numéro du client

  global $adsys;
  global $doc_prefix;
  global $global_id_client;

  // Récupération de la photo
  $photo = getPathPhotoClient($global_id_client);

  $document = create_xml_doc("fiche_personne_physique", "fiche_personne_physique.dtd");

  //définition de la racine
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'FIC-CLI');

  //body
  $fiche_pp = $root->new_child("fiche_pp","");
  if ($photo != NULL) {
    $fiche_pp->new_child("photo_pp", $photo);
  }

  if(!empty($DATA["client_zone"])){
      $libelle_zone = current(getZone(" id = ".$DATA["client_zone"]));
      $critere_zone = _($libelle_zone["libelle_zone"]);
  }else{
      $critere_zone = 'Non renseigné';
  }


  $fiche_pp->new_child("num_pp", makeNumClient($DATA["id_client"]));
  $fiche_pp->new_child("etat_pp", adb_gettext($adsys["adsys_etat_client"][$DATA["etat"]]));
  $fiche_pp->new_child("stat_jur_pp", adb_gettext($adsys["adsys_stat_jur"][$DATA["statut_juridique"]]));
  $fiche_pp->new_child("qualite_pp", adb_gettext($adsys["adsys_qualite_client"][$DATA["qualite"]]));
  $fiche_pp->new_child("anc_num_pp", sprintf("%06d",$DATA["anc_id_client"]));
  $fiche_pp->new_child("date_adh_pp", pg2phpDate($DATA["date_adh"]));
  $fiche_pp->new_child("date_cre_pp", pg2phpDate($DATA["date_crea"]));
  $fiche_pp->new_child("gest_pp", getLibel("ad_uti",$DATA["gestionnaire"]));
  $fiche_pp->new_child("lang_corres_pp", getLibel("adsys_langues_systeme",$DATA["langue_correspondance"]));
  $fiche_pp->new_child("nom_pp", htmlspecialchars($DATA["pp_nom"]));
  $fiche_pp->new_child("prenom_pp", htmlspecialchars($DATA["pp_prenom"]));
  $fiche_pp->new_child("sexe_pp", adb_gettext($adsys["adsys_sexe"][$DATA["pp_sexe"]]));
  $fiche_pp->new_child("etat_civil_pp", $adsys["adsys_etat_civil"][$DATA["pp_etat_civil"]]);
  $fiche_pp->new_child("date_nais_pp", pg2phpDate($DATA["pp_date_naissance"]));
  $fiche_pp->new_child("lieu_nais_pp", $DATA["pp_lieu_naissance"]);
  $fiche_pp->new_child("pays_nais_pp", getLibel("adsys_pays",$DATA["pp_pays_naiss"]));
  $fiche_pp->new_child("nationalite_pp", getLibel("adsys_pays",$DATA["pp_nationalite"]));
  $fiche_pp->new_child("type_piece_pp", getLibel("adsys_type_piece_identite",$DATA["pp_type_piece_id"]));
  $fiche_pp->new_child("num_piece_pp", $DATA["pp_nm_piece_id"]);
  $fiche_pp->new_child("date_expir_pp", pg2phpDate($DATA["pp_date_exp_id"]));
  $fiche_pp->new_child("adresse_pp", $DATA["adresse"]);
  $fiche_pp->new_child("loc1_pp", getLibel("adsys_localisation",$DATA["id_loc1"]));
  $fiche_pp->new_child("loc2_pp", getLibel("adsys_localisation",$DATA["id_loc2"]));
  $fiche_pp->new_child("code_postal_pp", $DATA["code_postal"]);
  $fiche_pp->new_child("ville_pp", $DATA["ville"]);
  $fiche_pp->new_child("pays_pp", getLibel("adsys_pays", $DATA["pays"]));
  $fiche_pp->new_child("num_tel_pp", $DATA["num_tel"]);
  $fiche_pp->new_child("fax_pp", $DATA["num_fax"]);
  $fiche_pp->new_child("email_pp", $DATA["email"]);
  $fiche_pp->new_child("sect_act_pp", getLibel("adsys_sect_activite", $DATA["sect_act"]));
  $fiche_pp->new_child("activ_prof_pp", $DATA["pp_pm_activite_prof"]);
  $fiche_pp->new_child("zone", $critere_zone);

  $listeGroupSol=getGroupSol($DATA["id_client"]);
  $liste_groupe_sol = $root->new_child("liste_groupe", "");
  foreach ($listeGroupSol->param as $cle => $valeur) {
    $id_groupe=$valeur["id_grp_sol"];
    $enreg_nom=getNomGroup($id_groupe);
    $groupe = $liste_groupe_sol->new_child("groupe","");
    $groupe->new_child("num_groupe",$id_groupe);
    $groupe->new_child("nom_groupe",htmlspecialchars($enreg_nom->param[0]["gi_nom"]));

  }
  if (sizeof($listeGroupSol->param) == 0) {
    $groupe = $liste_groupe_sol->new_child("groupe","");
    $groupe->new_child("num_groupe","");
    $groupe->new_child("nom_groupe","");
  }


  return $document->dump_mem(true);
}

function xml_fiche_personne_morale($DATA) {
  //Génération de code XML pour la fiche d'une personne morale
  //DATA contient la liste des informatgions du client

  global $adsys;

  $document = create_xml_doc("fiche_personne_morale", "fiche_personne_morale.dtd");

  //définition de la racine
  $root = $document->root();

    if(!empty($DATA["client_zone"])){
        $libelle_zone = current(getZone(" id = ".$DATA["client_zone"]));
        $critere_zone = _($libelle_zone["libelle_zone"]);
    }else{
        $critere_zone = 'Non renseigné';
    }


  //En-tête généraliste
  gen_header($root, 'FIC-CLI');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");

  //body
  $fiche_pp = $root->new_child("fiche_pm","");

  $fiche_pp->new_child("num_pm", makeNumClient($DATA["id_client"]));
  $fiche_pp->new_child("etat_pm", adb_gettext($adsys["adsys_etat_client"][$DATA["etat"]]));
  $fiche_pp->new_child("stat_jur_pm", adb_gettext($adsys["adsys_stat_jur"][$DATA["statut_juridique"]]));
  $fiche_pp->new_child("qualite_pm", adb_gettext($adsys["adsys_qualite_client"][$DATA["qualite"]]));
  $fiche_pp->new_child("anc_num_pm", sprintf("%06d",$DATA["anc_id_client"]));
  $fiche_pp->new_child("date_adh_pm", pg2phpDate($DATA["date_adh"]));
  $fiche_pp->new_child("date_cre_pm", pg2phpDate($DATA["date_crea"]));
  $fiche_pp->new_child("gest_pm", getLibel("ad_uti",$DATA["gestionnaire"]));
  $fiche_pp->new_child("lang_corres_pm", getLibel("adsys_langues_systeme",$DATA["langue_correspondance"]));
  $fiche_pp->new_child("raison_soc_pm", htmlspecialchars($DATA["pm_raison_sociale"]));
  $fiche_pp->new_child("abreviation_pm", htmlspecialchars($DATA["pm_abreviation"]));
  $fiche_pp->new_child("nature_jur_pm", htmlspecialchars($DATA["pm_nature_juridique"]));
  $fiche_pp->new_child("nbre_hommes_grp", $DATA["nbre_hommes_grp"]);
  $fiche_pp->new_child("nbre_femmes_grp", $DATA["nbre_femmes_grp"]);
  $fiche_pp->new_child("categorie_pm", adb_gettext($adsys["adsys_categorie_pm"][$DATA["pm_categorie"]]));
  $fiche_pp->new_child("date_notaire_pm", pg2phpDate($DATA["pm_date_notaire"]));
  $fiche_pp->new_child("date_greffe_pm", pg2phpDate($DATA["pm_date_depot_greffe"]));
  $fiche_pp->new_child("lieu_greffe_pm", $DATA["pm_lieu_depot_greffe"]);
  $fiche_pp->new_child("num_registre_pm", $DATA["pm_numero_nric"]);
  $fiche_pp->new_child("patrimoine_pm", $DATA["pp_pm_patrimoine"]);
  $fiche_pp->new_child("adresse_pm", $DATA["adresse"]);
  $fiche_pp->new_child("loc1_pm", getLibel("adsys_localisation",$DATA["id_loc1"]));
  $fiche_pp->new_child("loc2_pm", getLibel("adsys_localisation",$DATA["id_loc2"]));
  $fiche_pp->new_child("code_postal_pm", $DATA["code_postal"]);
  $fiche_pp->new_child("ville_pm", $DATA["ville"]);
  $fiche_pp->new_child("pays_pm", getLibel("adsys_pays",$DATA["pays"]));
  $fiche_pp->new_child("num_tel_pm", $DATA["num_tel"]);
  $fiche_pp->new_child("fax_pm", $DATA["num_fax"]);
  $fiche_pp->new_child("email_pm", $DATA["email"]);
  $fiche_pp->new_child("sect_act_pm", getLibel("adsys_sect_activite", $DATA["sect_act"]));
  $fiche_pp->new_child("activ_prof_pm", $DATA["pp_pm_activite_prof"]);
  $fiche_pp->new_child("zone", $critere_zone);

  return $document->dump_mem(true);

}

function xml_fiche_groupe_informel($DATA) {
  //Génération de code XML pour la fiche d'un groupe informel
  //DATA contient la liste des informatgions du client

  global $adsys;

  $document = create_xml_doc("fiche_groupe_informel", "fiche_groupe_informel.dtd");

  //définition de la racine
  $root = $document->root();

    if(!empty($DATA["client_zone"])){
        $libelle_zone = current(getZone(" id = ".$DATA["client_zone"]));
        $critere_zone = _($libelle_zone["libelle_zone"]);
    }else{
        $critere_zone = 'Non renseigné';
    }


    //En-tête généraliste
  gen_header($root, 'FIC-CLI');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");

  //body
  $fiche_pp = $root->new_child("fiche_gi","");

  $fiche_pp->new_child("num_gi", makeNumClient($DATA["id_client"]));
  $fiche_pp->new_child("etat_gi", adb_gettext($adsys["adsys_etat_client"][$DATA["etat"]]));
  $fiche_pp->new_child("stat_jur_gi", adb_gettext($adsys["adsys_stat_jur"][$DATA["statut_juridique"]]));
  $fiche_pp->new_child("qualite_gi", adb_gettext($adsys["adsys_qualite_client"][$DATA["qualite"]]));
  $fiche_pp->new_child("anc_num_gi", sprintf("%06d",$DATA["anc_id_client"]));
  $fiche_pp->new_child("date_adh_gi", pg2phpDate($DATA["date_adh"]));
  $fiche_pp->new_child("date_cre_gi", pg2phpDate($DATA["date_crea"]));
  $fiche_pp->new_child("nom_gi", htmlspecialchars($DATA["gi_nom"]));
  $fiche_pp->new_child("gest_gi", getLibel("ad_uti",$DATA["gestionnaire"]));
  $fiche_pp->new_child("lang_corres_gi", getLibel("adsys_langues_systeme",$DATA["langue_correspondance"]));
  $fiche_pp->new_child("nbr_membre_gi", $DATA["gi_nbre_membr"]);
  $fiche_pp->new_child("nbre_hommes_grp", $DATA["nbre_hommes_grp"]);
  $fiche_pp->new_child("nbre_femmes_grp", $DATA["nbre_femmes_grp"]);
  $fiche_pp->new_child("date_agrement_gi", pg2phpDate($DATA["gi_date_agre"]));
  $fiche_pp->new_child("adresse_gi", $DATA["adresse"]);
  $fiche_pp->new_child("loc1_gi", getLibel("adsys_localisation",$DATA["id_loc1"]));
  $fiche_pp->new_child("loc2_gi", getLibel("adsys_localisation",$DATA["id_loc2"]));
  $fiche_pp->new_child("code_postal_gi", $DATA["code_postal"]);
  $fiche_pp->new_child("ville_gi", $DATA["ville"]);
  $fiche_pp->new_child("pays_gi", getLibel("adsys_pays",$DATA["pays"]));
  $fiche_pp->new_child("num_tel_gi", $DATA["num_tel"]);
  $fiche_pp->new_child("fax_gi", $DATA["num_fax"]);
  $fiche_pp->new_child("email_gi", $DATA["email"]);
  $fiche_pp->new_child("sect_act_gi", getLibel("adsys_sect_activite", $DATA["sect_act"]));
  $fiche_pp->new_child("zone", $critere_zone);

  return $document->dump_mem(true);

}

function xml_fiche_groupe_solidaire($DATA) {
  //Génération de code XML pour la fiche d'un groupe solidaire
  //DATA contient la liste des informations du client

  global $adsys;

  // Création racine
  $document = create_xml_doc("fiche_groupe_solidaire", "fiche_groupe_solidaire.dtd");

  //Element root
  $root = $document->root();

    if(!empty($DATA["client_zone"])){
        $libelle_zone = current(getZone(" id = ".$DATA["client_zone"]));
        $critere_zone = _($libelle_zone["libelle_zone"]);
    }else{
        $critere_zone = 'Non renseigné';
    }


  //En-tête généraliste
  gen_header($root, 'FIC-CLI');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");

  $fiche_client = $root->new_child("fiche_gs", "");
  $fiche_client->new_child("num_gi", makeNumClient($DATA["id_client"]));
  $fiche_client->new_child("etat_gi", adb_gettext($adsys["adsys_etat_client"][$DATA["etat"]]));
  $fiche_client->new_child("stat_jur_gi", adb_gettext($adsys["adsys_stat_jur"][$DATA["statut_juridique"]]));
  $fiche_client->new_child("qualite_gi", adb_gettext($adsys["adsys_qualite_client"][$DATA["qualite"]]));
  $fiche_client->new_child("anc_num_gi", sprintf("%06d",$DATA["anc_id_client"]));
  $fiche_client->new_child("date_adh_gi", pg2phpDate($DATA["date_adh"]));
  $fiche_client->new_child("date_cre_gi", pg2phpDate($DATA["date_crea"]));
  $fiche_client->new_child("nom_gi", htmlspecialchars($DATA["gi_nom"]));
  $fiche_client->new_child("gest_gi", getLibel("ad_uti",$DATA["gestionnaire"]));
  $fiche_client->new_child("lang_corres_gi", getLibel("adsys_langues_systeme",$DATA["langue_correspondance"]));
  $fiche_client->new_child("nbr_membre_gi", $DATA["gi_nbre_membr"]);
  $fiche_client->new_child("date_agrement_gi", pg2phpDate($DATA["gi_date_agre"]));
  $fiche_client->new_child("adresse_gi", $DATA["adresse"]);
  $fiche_client->new_child("loc1_gi", getLibel("adsys_localisation",$DATA["id_loc1"]));
  $fiche_client->new_child("loc2_gi", getLibel("adsys_localisation",$DATA["id_loc2"]));
  $fiche_client->new_child("code_postal_gi", $DATA["code_postal"]);
  $fiche_client->new_child("ville_gi", $DATA["ville"]);
  $fiche_client->new_child("pays_gi", getLibel("adsys_pays",$DATA["pays"]));
  $fiche_client->new_child("num_tel_gi", $DATA["num_tel"]);
  $fiche_client->new_child("fax_gi", $DATA["num_fax"]);
  $fiche_client->new_child("email_gi", $DATA["email"]);
  $fiche_client->new_child("sect_act_gi", getLibel("adsys_sect_activite", $DATA["sect_act"]));
  $fiche_client->new_child("zone", $critere_zone);

  $membres=getListeMembresGrpSol($DATA["id_client"]);
  $listemembre = $root->new_child("liste_membres", "");

  foreach ($membres->param as $cle => $id_membre) {
    $enreg_client = getClientDatas($id_membre);
    $membre = $listemembre->new_child("membre","");
    $membre->new_child("num_membre",makeNumClient($id_membre));
    $membre->new_child("nom_membre",htmlspecialchars(getClientName($id_membre)));
    $membre->new_child("date_adh_membre",pg2phpDate($enreg_client["date_adh"]));
  }
  return $document->dump_mem(true);
}

/**
 * Génère le code XML pour le rapports des parts sociales reprises
 *
 * @param array $DATA Les données des parts sociales reprises
 * @param array $list_criteres Les critères de sélection des parts sociales reprises
 * @param boolean $export_csv Flag disant si le XML est pour générer un PDF ou un CSV.
 * @return string Le code XML généré.
 */
function xml_ps_reprise($DATA, $list_criteres, $export_csv = false) {
	global $global_monnaie;
	global $global_multidevise;
	$document = create_xml_doc("ps_reprises", "ps_reprises.dtd");

	//définition de la racine
	$root = $document->root();

	//En-tête généraliste
	gen_header($root, 'CLI-PSR');

	//En-tête contextuel
	$header_contextuel = $root->new_child("header_contextuel", "");
	gen_criteres_recherche($header_contextuel, $list_criteres);

  $infos_synthetiques= $header_contextuel->new_child("infos_synthetiques", "");
  $nbre=count($DATA);
  $nbre_total= $infos_synthetiques->new_child("nbre_total",$nbre);
	$liste_credit=$root->new_child("liste_ps_reprise", "");
	foreach ($DATA as $value) {
	  $ps_reprise=$liste_credit->new_child("ps_reprise", "");

		$ps_reprise->new_child("num_client", $value["id_client"]);
		$ps_reprise->new_child("anc_num_client", $value["anc_id_client"]);
		$ps_reprise->new_child("nom_client", getClientNameByArray($value));
		// FIXME :le nbre de parts sociale n'est pas stocké lors de la reprise,si on a changé la valeur nominale de PS (VNPS)=>mnt_repris/VNPS sera erroné
		$ps_reprise->new_child("nbre_ps", 0);
		$ps_reprise->new_child("mnt_ps_repris", afficheMontant($value["mnt_repris"],null,$export_csv));
		$ps_reprise->new_child("date_reprise", pg2phpDate($value["date_reprise"]));
	}

return $document->dump_mem(true);
}

function xml_situation_analytique_client($DATA, $list_criteres) {
  //Génération de code XML pour la fiche d'une personne physique
  //DATA contient la liste des informatgions du cleint
  //la liste des critères est un tableau associatif contient les critères de sélection : numéro du client

  global $adsys;
  global $doc_prefix;
  global $global_id_client, $global_id_profil;

  $document = create_xml_doc("situation_analytique_client", "situation_analytique_client.dtd");
  $root = $document->root();
  //définition de la racine
  $root = $document->root();

  //En-tête généraliste
  gen_header($root, 'SIT-CLI');

  //En-tête contextuel
  $header_contextuel = $root->new_child("header_contextuel", "");
  $header_contextuel->new_child("num_client",$global_id_client);
  $header_contextuel->new_child("nom_client", getNomClient($global_id_client));

  //body
  $credits = $root->new_child("credits", "");
  foreach ($DATA['credit'] as $key => $value) {
	  $dossier = $credits->new_child("dossier", "");
	  $dossier->new_child("id_doss", $value["id_doss"]);
		$dossier->new_child("cap_du", $value["cap_du"]);
		$dossier->new_child("int_du", $value["int_du"]);
		$dossier->new_child("gar_du", $value["gar_du"]);
		$dossier->new_child("cap_rest", $value["cap_rest"]);
		$dossier->new_child("int_rest", $value["int_rest"]);
		$dossier->new_child("gar_rest", $value["gar_rest"]);
		$dossier->new_child("cre_retard_etat_max",$value["cre_retard_etat_max"]);
	}
	$epargne = $root->new_child("epargne", "");
	foreach ($DATA['epargne'] as $key => $value) {
    $compte = $epargne->new_child("compte", "");
		$compte->new_child("num_cpte", $value["num_complet_cpte"]);
		$compte->new_child("prod_ep", $value["libel"]);
		$compte->new_child("date_ouvert", pg2phpDate($value["date_ouvert"]));
		$compte->new_child("etat_cpte", adb_gettext($adsys['adsys_etat_cpt_epargne'][$value["etat_cpte"]]));
		$access_solde = get_profil_acces_solde($global_id_profil, $value["id_prod"]);
		$access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);
		if(manage_display_solde_access($access_solde, $access_solde_vip))
			$compte->new_child("solde", afficheMontant($value["solde"]));
		$compte->new_child("devise", $value["devise"]);
		$compte->new_child("mnt_bloq", afficheMontant($value["mnt_bloq"] + $value["mnt_bloq_cre"]));
		if(manage_display_solde_access($access_solde, $access_solde_vip))
			$compte->new_child("mnt_disponible",afficheMontant(getSoldeDisponible($value["id_cpte"])));
  }
  $ord_permanent = $root->new_child("ord_permanent", "");
  foreach ($DATA['ord_permanent'] as $key => $value) {
    $compte_ord = $ord_permanent->new_child("compte_ord", "");
    $compte_ord->new_child("num_cpte_ord", $value["cpte_destination"]);
    $compte_ord->new_child("prod", $value["prod_libel"]);
    $compte_ord->new_child("date_ouverture", pg2phpDate($value["date_ouverture"]));
    $compte_ord->new_child("montant", afficheMontant($value["montant_virement"]));
    $compte_ord->new_child("periodicite", adb_gettext($adsys['adsys_periodicite_ordre_perm'][$value["periodicite"]]));
    $compte_ord->new_child("date_fin", pg2phpDate($value["date_fin"]));
    $access_solde = get_profil_acces_solde($global_id_profil, $value["id_prod"]);
    $access_solde_vip = get_profil_acces_solde_vip($global_id_profil, $global_id_client);
    if(manage_display_solde_access($access_solde, $access_solde_vip))
      $compte_ord->new_child("mnt_solde", afficheMontant($value["solde"]));
  }
  return $document->dump_mem(true);
}
?>