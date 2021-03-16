<?Php

/**
 * Gestion du code HTML relatif à un échéancier.
 *
 * @package Credit
 */

require_once 'lib/misc/tableSys.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/credit.php';
require_once 'lib/dbProcedures/mobile_lending.php';
require_once 'lib/misc/divers.php';

/**
 * Complète l'échéancier théorique.
 *
 * Ajoute la date d'échéance, les soldes de capitaux, intérêts et pénalités, le numéro de l'échéance et le booléen (remboursée ou non).
 *
 * La fonction explode est propre à PHP {@link PHP_MANUAL#explode}
 *
 * @param array $echeancier tableau associatif renvoyé par la fonction calcul_echeancier_theorique
 * @param array $parametre : les paramètres de l'échéancier suivants (c'est le même tableau que celui passé à HTML_echeancier) :<ul>
 *              <li><b>index</b> index de début des numéros d'échéances
 *              <li><b>id_prod</b> l'identifiant du produit de crédit
 *              <li><b>id_doss</b> l'identifiant du dossier de crédit
 *              <li><b>nbre_jours_mois</b> durée théorique du mois en nombre de jours
 *              <li><b>differe_jours</b> le différé du crédit en jours
 *              <li><b>date</b> la date à afficher
 *		 </ul>
 * @return array vecteur contenant pour chaque échéance un tableau associatif avec les infos sur les échéances :<ul>
 *             <li>$DATAECH["id_doss"] =  Numéro du dossier (-1 si pas de dossier)
 *             <li>$DATAECH["id_ech"] = Numéro de l'échéance
 *             <li>$DATAECH["mnt_cap"] = Montant en capital
 *             <li>$DATAECH["mnt_int"] = Montant en intérêts
 *             <li>$DATAECH["mnt_gar"] = Montant en garantie
 *             <li>$DATAECH["remb"] = Echéance remboursée (toujours à false)
 *             <li>$DATAECH["solde_cap"] = Solde en capital
 *             <li>$DATAECH["solde_int"] = Solde en intérêts
 *             <li>$DATAECH["solde_gar"] = Solde en garantie
 *             <li>$DATAECH["solde_pen"] = Solde en pénalités (toujours 0)
 *             <li>$DATAECH["mnt_reech"] = Montant rééchelonné (toujours 0)
 *             <li>$DATAECH["date_ech"] = Date de l'échéance
 *		 </ul>
 */

function completeEcheancier($echeancier,$parametre) {

  global $adsys, $global_id_agence;
  $Produit = getProdInfo(" where id =".$parametre["id_prod"], $parametre["id_doss"]);  //Info du produit de crédit
  $index=$Produit[0]["periodicite"];

  if($parametre["perio"]){
    $Produit[0]["periodicite"] = $parametre["perio"];
    $index = $parametre["perio"];
  }

  // Récupération de la base pour le calcul des intérpets
  // 1 => 360 jours => Mois de 30 jours
  // 2 => 365 jours => Mois correspondent au calendrier
  $AG = getAgenceDatas($global_id_agence);
  $base_taux = $AG["base_taux"];

  // Echéancier non stocké dans la base de données
  // D'où la nécessité de le générer entièrement
  $total_cap = 0;
  $total_int = 0;
  $period = $adsys["adsys_duree_periodicite"][$index];
  reset($echeancier); // Réinitialise le pointeur de tableau des écheances

  // Calcul de la périodicité en jour
  if ($Produit[0]["periodicite"] == 6) // Si on doit tout rembourser en une fois
    $duree_Periode = $parametre["duree"] * $parametre["nbre_jour_mois"];
  elseif ($adsys["adsys_type_periodicite"][$index]=="Hebdomadaire")
  $duree_Periode= $adsys["adsys_duree_periodicite"][$Produit[0]["periodicite"]]*7;
  else
    //FIXME nbre_jours_mois est toujours 30 et ne sert à rien en fait. A supprimer
    $duree_Periode = $adsys["adsys_duree_periodicite"][$Produit[0]["periodicite"]]*$parametre["nbre_jour_mois"];

  $diff = $parametre["differe_jours"];
  $date = $parametre["date"]; //Date de déboursement ou rééchelonnement
  $retour = array();
  $dern_jour = false; // Variable utilisée pour le cas particulier du dernier jour du mois
  $jj_save = $mm_save = 0;
    
  while (list($key,$echanc) = each($echeancier)) 
  {
    $i = $key + $parametre["index"];
    
    $DATAECH = array();
    // Remplissage de $DATAECH avec les données retournées par l'échéancier.
    $DATAECH["id_doss"] =  $parametre["id_doss"];
    $DATAECH["id_ech"] = $i;
    $DATAECH["mnt_cap"] = $echanc["mnt_cap"].'';
    $DATAECH["mnt_int"] = $echanc["mnt_int"].'';
    $DATAECH["mnt_gar"] = $echanc["mnt_gar"].'';
    $DATAECH["remb"] ='f';
    $DATAECH["solde_cap"] = $echanc["mnt_cap"].'';
    $DATAECH["solde_gar"] = $echanc["mnt_gar"].'';

    // Dans le cas d'un mode de calcul des intérêts de type 'dégressif KAANI',
    // les intérêts seront comptabilisés dynamiquement au jour le jour
    if ($Produit[0]["mode_calc_int"] == 3) // Type dégressif KAANI
      $DATAECH["solde_int"] = 0;
    else
      $DATAECH["solde_int"] = $echanc["mnt_int"].'';

    $DATAECH["solde_pen"] ='0';
    $DATAECH["mnt_reech"] ='0';

    // Calcul des dates d'échéance la date doit être au format jj/mm/aaaa
    $periodicite = $Produit[0]["periodicite"];

    if ($date != "") { // Rappel : $date = Date du déboursement / rééchelonnement
      $r = explode("/", $date);
      $jj = (int) 1*$r[0];
      $mm = (int) 1*$r[1];
      $aa = (int) 1*$r[2];
      
      // Init :
      if(empty($jj_save)) $jj_save = $jj;
      if(empty($mm_save)) $mm_save = $mm;
      if(empty($aa_savex)) $aa_savex = $aa;    

      if ($base_taux == 1) // 360 jours
        $date = date("d/m/Y",mktime(0,0,0,$mm,$jj + $duree_Periode + $diff,$aa,0));
      else if ($base_taux == 2) {
        // Périodicité hebdomadaire
        if (in_array($periodicite, array(8))) $date=date("d/m/Y",mktime(0,0,0,$mm,$jj + $duree_Periode + $diff,$aa,0));

        // Périodes de mois entiers
        else if (in_array($periodicite, array(1,3,4,5,7))) {
          $nbre_mois_periode = $adsys["adsys_duree_periodicite"][$periodicite];
          if ($dern_jour)
            $date = date("d/m/Y", mktime(0,0,0,$mm+$nbre_mois_periode+1,0,$aa));
          else
            $date = date("d/m/Y", mktime(0,0,0,$mm+$nbre_mois_periode,$jj+$diff,$aa));
        }

        // Périodicité 2 fois par mois
        else if ($periodicite == 2) 
        {       	
          if ($i%2 == 1) { // Impair ==> d(j) = d(j-1) + 15 jours.
            if ($dern_jour)
              $date = date("d/m/Y", mktime(0,0,0,$mm+1,15,$aa));
            else {
              $date = date("d/m/Y", mktime(0,0,0,$mm,$jj+$diff+15,$aa));
              /*echo 'ech='.$i.' - $mm='.$mm.', $jj='.$jj.' + $diff='.$diff.' + 15 , $aa='.$aa.'<br />';
              echo '$date='.$date.'<br /><br />';*/
            }
            // On enregistre le jour et le mois de d(j-1)
            $aa_savex = $aa; // Year fix
            $mm_save = $mm;
            $jj_save = $jj+$diff;           
          } 
          else // Pair ==> d(j) = d(j-2) + 1 mois.
          {          		
          		// Year fix
	      		if($mm_save==12 && ($jj_save+15)>31) {
	            	if ($dern_jour) {
	                  $date = date("d/m/Y", mktime(0,0,0,$mm_save+2,0,$aa_savex));
	                } else {
	                  $date = date("d/m/Y", mktime(0,0,0,$mm_save+1,$jj_save,$aa_savex));	
	                  /*echo 'ech='.$i.' - $mm_save='.$mm_save.' + 1, $jj_save='.$jj_save.' , $aa='.$aa_savex.'<br />';
	                  echo '$date='.$date.'<br /><br />';*/
	                }
	            }
	            else {
	                if ($dern_jour) {
	                  $date = date("d/m/Y", mktime(0,0,0,$mm_save+2,0,$aa));
	                } else {
	                  $date = date("d/m/Y", mktime(0,0,0,$mm_save+1,$jj_save,$aa));	
	                  /*echo 'ech='.$i.' - $mm_save='.$mm_save.' + 1, $jj_save='.$jj_save.' , $aa='.$aa.'<br />';
	                  echo '$date='.$date.'<br /><br />';*/
	                }
	            }          
          }
        } // end :  Périodicité 2 fois par mois

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
        signalErreur(__FILE__,__LINE__,__FUNCTION__); // "Type de base de calcul inconnue: [$base_taux]"

      $diff=0;
      $DATAECH["date_ech"] = $date;
    }
    //if($parametre["id_doss"]>=0) $SESSION_VARS["etr"][$key] = $DATAECH;
    $retour[$key] = $DATAECH;
  }
    
  return $retour;
}

/**
 * Renvoie le code html pour l'échéancier
 * @author ADbanking
 * @since unknown
 * @param array $parametre : les paramètres de l'échéancier, ils sont principalement utilisés pour construire le tableau d'entête de l'affichage de l'échéancier, ce tableau peut contenir les éléments suivants :<ul>
 *              <li><b>id_doss</b> l'identifiant du dossier de crédit
 *              <li><b>id_prod</b> l'identifiant du produit de crédit
 *              <li><b>index</b> index de début des numéros d'échéances
 *              <li><b>differe_jours</b> le différé du crédit en jours
 *              <li><b>differe_ech</b> le différé du crédit en échéances
 *              <li><b>titre</b> le titre de l'échéancier
 *              <li><b>nbre_jours_mois</b> durée théorique du mois en nombre de jours
 *              <li><b>montant</b> le montant du crédit
 *              <li><b>mnt_reech</b> le montant du rééchelonnement
 *              <li><b>mnt_octr</b> le montant effectivement octroyé
 *              <li><b>lib_date</b> le libellé à utiliser devant l'affichage de la date du dossier de crédit
 *              <li><b>garantie</b> le montant de la garantie
 *              <li><b>date</b> la date à afficher
 *              <li><b>durée</b> la durée du crédit en mois
 *              <li><b>EXIST</b> flag indiquant si l'échéancier est stocké dans la BD (1) ou non (0)
 *		 </ul>
 * @param array $echeancier : l'échéancier à afficher s'il est déjà existant
 * @return string le code HTML de l'échéancier
 */
function HTML_echeancier($parametre, $echeancier, $id_doss = NULL, $produits_credit = NULL) {
  global $tableau_border;
  global $tableau_cellspacing;
  global $tableau_cellpadding;
  global $adsys;
  global $SESSION_VARS;
  global $global_monnaie;
  global $global_monnaie_prec;


  $echeancier_genere = false;

  // Retourne les informations sur le produit sélectionné dans le crédit
  $id_doss = (($parametre["id_doss"] == -1) ? $id_doss : $parametre["id_doss"]);

  if ($produits_credit != NULL && is_array($produits_credit)) {
    $Produit[0] = $produits_credit;
  } else {
    $Produit = getProdInfo(" where id =" . $parametre["id_prod"], $id_doss);
  }

  if (isMobileLending()) {
      $premier_credit = isPremierCreditMobileLending($parametre['id_client']);
      if ($premier_credit['premier_credit'] == 't') {
          $Produit[0]['tx_interet'] = 0;
      }
  }

  $diff = $parametre["differe_jours"];
  $diff_ech = $parametre["differe_ech"];

  // Tableau des détails du produit
  $table1 = new HTML_TABLE_table(4, TABLE_STYLE_CLASSIC);
  $table1->set_property("title",$parametre["titre"]);
  $table1->add_cell(new TABLE_cell(_("N° client:")));
  $table1->set_cell_property("width","15%");
  $table1->add_cell(new TABLE_cell($parametre["id_client"]));
  $table1->add_cell(new TABLE_cell(_("Nom client:")));
  $table1->add_cell(new TABLE_cell(_(getClientName($parametre["id_client"]))));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell(_("Produit:")));
  $table1->set_cell_property("width","15%");
  $table1->add_cell(new TABLE_cell($Produit[0]["libel"]));
  $table1->set_cell_property("width","35%");
  $table1->add_cell(new TABLE_cell(_("Montant octroyé:")));
  $table1->set_cell_property("width","30%");
  if(($parametre['prelev_commission'] == 't'  && $parametre['prelev_frais_doss']== 2 && $parametre['debours'] == "true")||($parametre['prelev_commission'] == 't' && $parametre['prelev_frais_doss']== 1 && $parametre['debours'] != "true")){
  	 $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["mnt_octr"]-$parametre["mnt_des_frais"],true)));
  }else{
     $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["mnt_octr"],true)));
  }
  $table1->set_cell_property("width","20%");
  $table1->set_row_childs_property("align","left");
  $table1->set_row_property("class","");

  $table1->add_cell(new TABLE_cell(_("Durée:")));
  $table1->add_cell(new TABLE_cell(
                      $parametre["duree"]." ".$adsys["adsys_type_duree_credit"][$Produit[0]["type_duree_credit"]]
                    ));
  $table1->add_cell(new TABLE_cell(_("Montant rééchel.:")));
  $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["mnt_reech"],true)));
  $table1->set_row_childs_property("align","left");

  $tx=100*$Produit[0]["tx_interet"];
  $table1->add_cell(new TABLE_cell(_("Différé:")));
  $table1->add_cell(new TABLE_cell(str_affichage_diff($diff, $diff_ech)));
  $table1->add_cell(new TABLE_cell(_("Taux d'intérêt:")));
  $table1->add_cell(new TABLE_cell("$tx%"));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell(_("Périodicité:")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_type_periodicite"][$Produit[0]["periodicite"]])));
  $table1->add_cell(new TABLE_cell(_("Garantie totale:")));
  $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["garantie"],true)));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell(_("Fréq. remb. capital:")));
  (($Produit[0]["freq_paiement_cap"] == 1)? $frequence_paiement_capital = _("Chaque échéance") :$frequence_paiement_capital = sprintf(_("Toutes les %d échéances"), $Produit[0]["freq_paiement_cap"]));
  $table1->add_cell(new TABLE_cell($frequence_paiement_capital));
  $table1->add_cell(new TABLE_cell(_("Mode de calcul des intérêts:")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_mode_calc_int_credit"][$Produit[0]["mode_calc_int"]])));
  $table1->set_row_childs_property("align","left");

  if(isset($parametre["lib_date"])) {
    $table1->add_cell(new TABLE_cell($parametre["lib_date"]));
    $table1->add_cell(new TABLE_cell($parametre["date"]));
  }
  elseif(isset($parametre["lib_curr_date"])) {
    $table1->add_cell(new TABLE_cell($parametre["lib_curr_date"]));
    $table1->add_cell(new TABLE_cell(date("d/m/Y")));
  }
  $table1->set_row_childs_property("align","left");

  // Tableau des échéances
  // Affichage entête
  $table2 = new HTML_TABLE_table(7, TABLE_STYLE_ALTERN);
  $table2->add_cell(new TABLE_cell(_("N°")));
  $table2->add_cell(new TABLE_cell(_("Date")));
  $table2->add_cell(new TABLE_cell(_("Montant du capital")));
  $table2->add_cell(new TABLE_cell(_("Montant des intérêts")));
  $table2->add_cell(new TABLE_cell(_("Montant de la garantie")));
  $table2->add_cell(new TABLE_cell(_("Total de l'échéance")));
  $table2->add_cell(new TABLE_cell(_("Solde restant dû")));

  if ($parametre["EXIST"]==0) {
    // Echéancier non stocké dans la base de données
    // D'où la nécessité de le générer entièrement

    $echeancier=completeEcheancier($echeancier,$parametre);
    if ($parametre["id_doss"]>=0) {
      $SESSION_VARS["etr"] = $echeancier;
      $SESSION_VARS['infos_doss'][$parametre["id_doss"]]['etr'] = $echeancier;
    }
    $echeancier_genere = true;
  }

  if (($parametre["EXIST"]==1) || ($echeancier_genere)) {
    // L'échéancier existe (stocké dans la BD ou vient d'être généré)
    $total_cap = 0;
    $total_int = 0;
    $total_gar = 0;

    foreach ($echeancier AS $key=>$echanc) {
      $total_cap = $total_cap + $echanc["mnt_cap"];
      $total_int = $total_int + $echanc["mnt_int"];
      $total_gar = $total_gar + $echanc["mnt_gar"];

      if ($echeancier_genere) {
        $date = $echanc["date_ech"];
      }
      else {
        $date = pg2phpDate($echanc["date_ech"]);
      }

      $som=$echanc["mnt_cap"] + $echanc["mnt_int"] + $echanc["mnt_gar"];
      $rest=max(0,$parametre["montant"] - $total_cap);

      // Affichage échéances
      $table2->add_cell(new TABLE_cell($echanc["id_ech"]));
      $table2->set_cell_property("align","center");
      $table2->add_cell(new TABLE_cell_date($date));
      $table2->set_cell_property("align","left");
      $table2->add_cell(new TABLE_cell(afficheMontant ($echanc["mnt_cap"], false)));
      $table2->add_cell(new TABLE_cell(afficheMontant ($echanc["mnt_int"], false)));
      $table2->add_cell(new TABLE_cell(afficheMontant ($echanc["mnt_gar"], false)));
      $table2->add_cell(new TABLE_cell(afficheMontant ($som,false)));
      $table2->add_cell(new TABLE_cell(afficheMontant ($rest,false)));
      $table2->set_row_childs_property("align","right");
    }
    $total=$total_cap+$total_int+$total_gar;

    $table2->add_cell(new TABLE_cell(_("Total"),2));
    $table2->set_row_childs_property("align","center");
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_cap,false)));
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_int,false)));
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_gar,false)));
    $table2->add_cell(new TABLE_cell(afficheMontant ($total,false)));
    $table2->add_cell(new TABLE_cell(""));
    $table2->set_row_childs_property("align","right");
    $table2->set_row_childs_property("bold");
  }
  return $table1->gen_HTML().$table2->gen_HTML();

}

/**
 * Renvoie le code html pour l'échéancier de raccourcissement de credit
 * @author ADbanking
 * @since unknown
 * @param array $parametre : les paramètres de l'échéancier, ils sont principalement utilisés pour construire le tableau d'entête de l'affichage de l'échéancier, ce tableau peut contenir les éléments suivants :<ul>
 *              <li><b>id_doss</b> l'identifiant du dossier de crédit
 *              <li><b>id_prod</b> l'identifiant du produit de crédit
 *              <li><b>index</b> index de début des numéros d'échéances
 *              <li><b>differe_jours</b> le différé du crédit en jours
 *              <li><b>differe_ech</b> le différé du crédit en échéances
 *              <li><b>titre</b> le titre de l'échéancier
 *              <li><b>nbre_jours_mois</b> durée théorique du mois en nombre de jours
 *              <li><b>montant</b> le montant du crédit
 *              <li><b>mnt_reech</b> le montant du rééchelonnement
 *              <li><b>mnt_octr</b> le montant effectivement octroyé
 *              <li><b>lib_date</b> le libellé à utiliser devant l'affichage de la date du dossier de crédit
 *              <li><b>garantie</b> le montant de la garantie
 *              <li><b>date</b> la date à afficher
 *              <li><b>durée</b> la durée du crédit en mois
 *              <li><b>EXIST</b> flag indiquant si l'échéancier est stocké dans la BD (1) ou non (0)
 *		 </ul>
 * @param array $echeancier : l'échéancier à afficher s'il est déjà existant
 * @return string le code HTML de l'échéancier
 */
function HTML_echeancier_raccourci($parametre, $echeancier, $id_doss = NULL)
{
	global $tableau_border;
	global $tableau_cellspacing;
	global $tableau_cellpadding;
	global $adsys;
	global $SESSION_VARS;
	global $global_monnaie;
	global $global_monnaie_prec;
	global $global_id_client;
	
	$echeancier_genere = false;

	// Retourne les informations sur le produit sélectionné dans le crédit
    $id_doss = (($parametre["id_doss"] == -1) ? $id_doss : $parametre["id_doss"]);
	$Produit = getProdInfo ( " where id =" . $parametre ["id_prod"] , $id_doss);
	if(isMobileLending()) {
        $premier_credit = isPremierCreditMobileLending($global_id_client);
        if ($premier_credit['premier_credit'] == 't') {
            $Produit[0]['tx_interet'] = 0;
        }
    }

	$diff = $parametre ["differe_jours"];
	$diff_ech = $parametre ["differe_ech"];
	
	// Tableau des détails du produit
	$table1 = new HTML_TABLE_table ( 4, TABLE_STYLE_CLASSIC );
	$table1->set_property ( "title", $parametre ["titre"] );
	$table1->add_cell ( new TABLE_cell ( _ ( "N° client:" ) ) );
	$table1->set_cell_property ( "width", "15%" );
	$table1->add_cell ( new TABLE_cell ( $parametre ["id_client"] ) );
	$table1->add_cell ( new TABLE_cell ( _ ( "Nom client:" ) ) );
	$table1->add_cell ( new TABLE_cell ( _ ( getClientName ( $parametre ["id_client"] ) ) ) );
	$table1->set_row_childs_property ( "align", "left" );
	
	$table1->add_cell ( new TABLE_cell ( _ ( "Produit:" ) ) );
	$table1->set_cell_property ( "width", "15%" );
	$table1->add_cell ( new TABLE_cell ( $Produit [0] ["libel"] ) );
	$table1->set_cell_property ( "width", "35%" );
	$table1->add_cell ( new TABLE_cell ( _ ( "Montant octroyé:" ) ) );
	$table1->set_cell_property ( "width", "30%" );
	
	if (($parametre ['prelev_commission'] == 't' && $parametre ['prelev_frais_doss'] == 2 && $parametre ['debours'] == "true") || ($parametre ['prelev_commission'] == 't' && $parametre ['prelev_frais_doss'] == 1 && $parametre ['debours'] != "true")) {
		$table1->add_cell ( new TABLE_cell ( afficheMontant ( $parametre ["mnt_octr"] - $parametre ["mnt_des_frais"], true ) ) );
	} else {
		$table1->add_cell ( new TABLE_cell ( afficheMontant ( $parametre ["mnt_octr"], true ) ) );
	}
	$table1->set_cell_property ( "width", "20%" );
	$table1->set_row_childs_property ( "align", "left" );
	$table1->set_row_property ( "class", "" );	
	
	$table1->add_cell ( new TABLE_cell ( _ ( "Nbr. d'échéances restantes:" ) ) );
	$table1->set_cell_property ( "width", "25%" );
	
	$table1->add_cell ( new TABLE_cell ( $parametre ["nbr_echeances_restant"]) );
	$table1->set_cell_property ( "width", "20%" );
	$table1->set_row_childs_property ( "align", "left" );
	$table1->set_row_property ( "class", "" );
	
	$table1->add_cell ( new TABLE_cell ( _ ( "Nbr. d'échéances souhaitées:" ) ) );
	$table1->add_cell ( new TABLE_cell ( $parametre ["nbr_echeances_souhaite"]) );
	$table1->set_cell_property ( "width", "20%" );
	$table1->set_row_childs_property ( "align", "left" );
	$table1->set_row_property ( "class", "" );	
	
	$table1->add_cell ( new TABLE_cell ( _ ( "Montant rééchel.:" ) ) );
	$table1->add_cell ( new TABLE_cell ( afficheMontant ( $parametre ["mnt_reech"], true ) ) );
	$table1->set_row_childs_property ( "align", "left" );
	
	$tx = 100 * $Produit [0] ["tx_interet"];
	$table1->add_cell ( new TABLE_cell ( _ ( "Différé:" ) ) );
	$table1->add_cell ( new TABLE_cell ( str_affichage_diff ( $diff, $diff_ech ) ) );
	$table1->add_cell ( new TABLE_cell ( _ ( "Taux d'intérêt:" ) ) );
	$table1->add_cell ( new TABLE_cell ( "$tx%" ) );
	$table1->set_row_childs_property ( "align", "left" );
	
	$table1->add_cell ( new TABLE_cell ( _ ( "Périodicité:" ) ) );
	$table1->add_cell ( new TABLE_cell ( adb_gettext ( $adsys ["adsys_type_periodicite"] [$Produit [0] ["periodicite"]] ) ) );
	$table1->add_cell ( new TABLE_cell ( _ ( "Garantie totale:" ) ) );
	$table1->add_cell ( new TABLE_cell ( afficheMontant ( $parametre ["garantie"], true ) ) );
	$table1->set_row_childs_property ( "align", "left" );
	
	$table1->add_cell ( new TABLE_cell ( _ ( "Fréq. remb. capital:" ) ) );
	(($Produit [0] ["freq_paiement_cap"] == 1) ? $frequence_paiement_capital = _ ( "Chaque échéance" ) : $frequence_paiement_capital = sprintf ( _ ( "Toutes les %d échéances" ), $Produit [0] ["freq_paiement_cap"] ));
	$table1->add_cell ( new TABLE_cell ( $frequence_paiement_capital ) );
	$table1->add_cell ( new TABLE_cell ( _ ( "Mode de calcul des intérêts:" ) ) );
	$table1->add_cell ( new TABLE_cell ( adb_gettext ( $adsys ["adsys_mode_calc_int_credit"] [$Produit [0] ["mode_calc_int"]] ) ) );
	$table1->set_row_childs_property ( "align", "left" );
	
	if (isset ( $parametre ["lib_date"] )) {
		$table1->add_cell ( new TABLE_cell ( $parametre ["lib_date"] ) );
		$table1->add_cell ( new TABLE_cell ( $parametre ["date"] ) );
	} elseif (isset ( $parametre ["lib_curr_date"] )) {
		$table1->add_cell ( new TABLE_cell ( $parametre ["lib_curr_date"] ) );
		$table1->add_cell ( new TABLE_cell ( date ( "d/m/Y" ) ) );
	}
	$table1->set_row_childs_property ( "align", "left" );
	
	// Tableau des échéances
	// Affichage entête
	$table2 = new HTML_TABLE_table ( 7, TABLE_STYLE_ALTERN );
	$table2->add_cell ( new TABLE_cell ( _ ( "N°" ) ) );
	$table2->add_cell ( new TABLE_cell ( _ ( "Date" ) ) );
	$table2->add_cell ( new TABLE_cell ( _ ( "Montant du capital" ) ) );
	$table2->add_cell ( new TABLE_cell ( _ ( "Montant des intérêts" ) ) );
	$table2->add_cell ( new TABLE_cell ( _ ( "Montant de la garantie" ) ) );
	$table2->add_cell ( new TABLE_cell ( _ ( "Total de l'échéance" ) ) );
	$table2->add_cell ( new TABLE_cell ( _ ( "Solde restant dû" ) ) );
	
	if ($parametre ["EXIST"] == 0) {
		// Echéancier non stocké dans la base de données
		// D'où la nécessité de le générer entièrement
		
		$echeancier = completeEcheancier ( $echeancier, $parametre );
		if ($parametre ["id_doss"] >= 0) {
			$SESSION_VARS ["etr"] = $echeancier;
			$SESSION_VARS ['infos_doss'] [$parametre ["id_doss"]] ['etr'] = $echeancier;
		}
		$echeancier_genere = true;
	}
	
	if (($parametre ["EXIST"] == 1) || ($echeancier_genere)) {
		// L'échéancier existe (stocké dans la BD ou vient d'être généré)
		$total_cap = 0;
		$total_int = 0;
		$total_gar = 0;
		
		foreach ( $echeancier as $key => $echanc ) {
			$total_cap = $total_cap + $echanc ["mnt_cap"];
			$total_int = $total_int + $echanc ["mnt_int"];
			$total_gar = $total_gar + $echanc ["mnt_gar"];
			
			if ($echeancier_genere)
				$date = $echanc ["date_ech"];
			else
				$date = pg2phpDate ( $echanc ["date_ech"] );
			
			$som = $echanc ["mnt_cap"] + $echanc ["mnt_int"] + $echanc ["mnt_gar"];
			$rest = max ( 0, $parametre ["montant"] - $total_cap );
			
			// Affichage échéances
			$table2->add_cell ( new TABLE_cell ( $echanc ["id_ech"] ) );
			$table2->set_cell_property ( "align", "center" );
			$table2->add_cell ( new TABLE_cell_date ( $date ) );
			$table2->set_cell_property ( "align", "left" );
			$table2->add_cell ( new TABLE_cell ( afficheMontant ( $echanc ["mnt_cap"], false ) ) );
			$table2->add_cell ( new TABLE_cell ( afficheMontant ( $echanc ["mnt_int"], false ) ) );
			$table2->add_cell ( new TABLE_cell ( afficheMontant ( $echanc ["mnt_gar"], false ) ) );
			$table2->add_cell ( new TABLE_cell ( afficheMontant ( $som, false ) ) );
			$table2->add_cell ( new TABLE_cell ( afficheMontant ( $rest, false ) ) );
			$table2->set_row_childs_property ( "align", "right" );
		}
		$total = $total_cap + $total_int + $total_gar;
		
		$table2->add_cell ( new TABLE_cell ( _ ( "Total" ), 2 ) );
		$table2->set_row_childs_property ( "align", "center" );
		$table2->add_cell ( new TABLE_cell ( afficheMontant ( $total_cap, false ) ) );
		$table2->add_cell ( new TABLE_cell ( afficheMontant ( $total_int, false ) ) );
		$table2->add_cell ( new TABLE_cell ( afficheMontant ( $total_gar, false ) ) );
		$table2->add_cell ( new TABLE_cell ( afficheMontant ( $total, false ) ) );
		$table2->add_cell ( new TABLE_cell ( "" ) );
		$table2->set_row_childs_property ( "align", "right" );
		$table2->set_row_childs_property ( "bold" );
	}
	return $table1->gen_HTML () . $table2->gen_HTML ();
}


/**
 * Renvoie le code html pour l'échéancier
 * @author ADbanking
 * @since unknown
 * @param array $parametre : les paramètres de l'échéancier, ils sont principalement utilisés pour construire le tableau d'entête de l'affichage de l'échéancier, ce tableau peut contenir les éléments suivants :<ul>
 *              <li><b>id_doss</b> l'identifiant du dossier de crédit
 *              <li><b>id_prod</b> l'identifiant du produit de crédit
 *              <li><b>index</b> index de début des numéros d'échéances
 *              <li><b>differe_jours</b> le différé du crédit en jours
 *              <li><b>differe_ech</b> le différé du crédit en échéances
 *              <li><b>titre</b> le titre de l'échéancier
 *              <li><b>nbre_jours_mois</b> durée théorique du mois en nombre de jours
 *              <li><b>montant</b> le montant du crédit
 *              <li><b>mnt_reech</b> le montant du rééchelonnement
 *              <li><b>mnt_octr</b> le montant effectivement octroyé
 *              <li><b>lib_date</b> le libellé à utiliser devant l'affichage de la date du dossier de crédit
 *              <li><b>garantie</b> le montant de la garantie
 *              <li><b>date</b> la date à afficher
 *              <li><b>durée</b> la durée du crédit en mois
 *              <li><b>EXIST</b> flag indiquant si l'échéancier est stocké dans la BD (1) ou non (0)
 *		 </ul>
 * @param array $echeancier : l'échéancier à afficher s'il est déjà existant
 * @return string le code HTML de l'échéancier
 */
function HTML_new_echeancier($parametre, $echeancier) {
  global $tableau_border;
  global $tableau_cellspacing;
  global $tableau_cellpadding;
  global $adsys;
  global $SESSION_VARS;
  global $global_monnaie;
  global $global_monnaie_prec;

  // Retourne les informations sur le produit sélectionné dans le crédit
  $Produit = getProdInfo(" where id =".$parametre["id_prod"], $parametre["id_doss"]);

  $diff = $parametre["differe_jours"];
  $diff_ech = $parametre["differe_ech"];

  // Tableau des détails du produit
  $table1 = new HTML_TABLE_table(4, TABLE_STYLE_CLASSIC);
  $table1->set_property("title",$parametre["titre"]);
  $table1->add_cell(new TABLE_cell(_("N° client:")));
  $table1->set_cell_property("width","15%");
  $table1->add_cell(new TABLE_cell($parametre["id_client"]));
  $table1->add_cell(new TABLE_cell(_("Nom client:")));
  $table1->add_cell(new TABLE_cell(_(getClientName($parametre["id_client"]))));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell(_("Produit:")));
  $table1->set_cell_property("width","15%");
  $table1->add_cell(new TABLE_cell($Produit[0]["libel"]));
  $table1->set_cell_property("width","35%");
  $table1->add_cell(new TABLE_cell(_("Montant octroyé:")));
  $table1->set_cell_property("width","30%");
  if(($parametre['prelev_commission'] == 't'  && $parametre['prelev_frais_doss']== 2 && $parametre['debours'] == "true")||($parametre['prelev_commission'] == 't' && $parametre['prelev_frais_doss']== 1 && $parametre['debours'] != "true")){
  	 $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["mnt_octr"]-$parametre["mnt_des_frais"],true)));
  }else{
     $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["mnt_octr"],true)));
  }
  $table1->set_cell_property("width","20%");
  $table1->set_row_childs_property("align","left");
  $table1->set_row_property("class","");

  $table1->add_cell(new TABLE_cell(_("Durée:")));
  $table1->add_cell(new TABLE_cell(
                      $parametre["duree"]." ".$adsys["adsys_type_duree_credit"][$Produit[0]["type_duree_credit"]]
                    ));
  $table1->add_cell(new TABLE_cell(_("Montant rééchel.:")));
  $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["mnt_reech"],true)));
  $table1->set_row_childs_property("align","left");

  $tx=100*$Produit[0]["tx_interet"];
  $table1->add_cell(new TABLE_cell(_("Différé:")));
  $table1->add_cell(new TABLE_cell(str_affichage_diff($diff, $diff_ech)));
  $table1->add_cell(new TABLE_cell(_("Taux d'intérêt:")));
  $table1->add_cell(new TABLE_cell("$tx%"));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell(_("Périodicité:")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_type_periodicite"][$Produit[0]["periodicite"]])));
  $table1->add_cell(new TABLE_cell(_("Garantie totale:")));
  $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["garantie"],true)));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell(_("Fréq. remb. capital:")));
  (($Produit[0]["freq_paiement_cap"] == 1)? $frequence_paiement_capital = _("Chaque échéance") :$frequence_paiement_capital = sprintf(_("Toutes les %d échéances"), $Produit[0]["freq_paiement_cap"]));
  $table1->add_cell(new TABLE_cell($frequence_paiement_capital));
  $table1->add_cell(new TABLE_cell(_("Mode de calcul des intérêts:")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_mode_calc_int_credit"][$Produit[0]["mode_calc_int"]])));
  $table1->set_row_childs_property("align","left");

  if(isset($parametre["lib_date"])) {
    $table1->add_cell(new TABLE_cell($parametre["lib_date"]));
    $table1->add_cell(new TABLE_cell($parametre["date"]));
  }
  elseif(isset($parametre["lib_curr_date"])) {
    $table1->add_cell(new TABLE_cell($parametre["lib_curr_date"]));
    $table1->add_cell(new TABLE_cell(date("d/m/Y")));
  }
  $table1->set_row_childs_property("align","left");

  // Tableau des échéances
  // Affichage entête
  $table2 = new HTML_TABLE_table(7, TABLE_STYLE_ALTERN);
  $table2->add_cell(new TABLE_cell(_("N°")));
  $table2->add_cell(new TABLE_cell(_("Date")));
  $table2->add_cell(new TABLE_cell(_("Montant du capital")));
  $table2->add_cell(new TABLE_cell(_("Montant des intérêts")));
  $table2->add_cell(new TABLE_cell(_("Montant de la garantie")));
  $table2->add_cell(new TABLE_cell(_("Total de l'échéance")));
  $table2->add_cell(new TABLE_cell(_("Solde restant dû")));

  if (($parametre["EXIST"]==1)) {
    // L'échéancier existe (stocké dans la BD ou vient d'être généré)
    $total_cap = 0;
    $total_int = 0;
    $total_gar = 0;

    foreach ($echeancier AS $key=>$echanc) {
      $total_cap = $total_cap + $echanc["mnt_cap"];
      $total_int = $total_int + $echanc["mnt_int"];
      $total_gar = $total_gar + $echanc["mnt_gar"];

      if ($echeancier_genere)
        $date = $echanc["date_ech"];
      else
        $date = pg2phpDate($echanc["date_ech"]);

      $som=$echanc["mnt_cap"] + $echanc["mnt_int"] + $echanc["mnt_gar"];
      $rest=max(0,$parametre["montant"] - $total_cap);

      // Affichage échéances
      $table2->add_cell(new TABLE_cell($echanc["id_ech"]));
      $table2->set_cell_property("align","center");
      $table2->add_cell(new TABLE_cell_date($date));
      $table2->set_cell_property("align","left");
      $table2->add_cell(new TABLE_cell(afficheMontant ($echanc["mnt_cap"], false)));
      $table2->add_cell(new TABLE_cell(afficheMontant ($echanc["mnt_int"], false)));
      $table2->add_cell(new TABLE_cell(afficheMontant ($echanc["mnt_gar"], false)));
      $table2->add_cell(new TABLE_cell(afficheMontant ($som,false)));
      $table2->add_cell(new TABLE_cell(afficheMontant ($rest,false)));
      $table2->set_row_childs_property("align","right");
    }
    $total=$total_cap+$total_int+$total_gar;

    $table2->add_cell(new TABLE_cell(_("Total restant dû"),2));
    $table2->set_row_childs_property("align","center");
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_cap,false)));
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_int,false)));
    $table2->add_cell(new TABLE_cell(afficheMontant ($total_gar,false)));
    $table2->add_cell(new TABLE_cell(afficheMontant ($total,false)));
    $table2->add_cell(new TABLE_cell(""));
    $table2->set_row_childs_property("align","right");
    $table2->set_row_childs_property("bold");
  }
  return $table1->gen_HTML().$table2->gen_HTML();

}

/**
 * HTML_echeancier_remboursement : Renvoie le code html pour un échéancier de remboursement éventuellement modifiable
 * @author Antoine Delvaux
 * @since 2.1
 * @param array $parametre : les paramètres de l'échéancier de remboursement, ils sont principalement utilisés pour construire le tableau d'entête de l'affichage de l'échéancier, ce tableau peut contenir les éléments suivants :<ul>
 *              <li><b>diff</b> le différé en jours du crédit
 *              <li><b>diff_ech</b> le différé en échéances du crédit
 *              <li><b>titre</b> le titre de l'échéancier
 *              <li><b>mnt_reech</b> le montant du rééchelonnement
 *              <li><b>lib_date</b> le libellé à utiliser devant l'affichage de la date du dossier de crédit
 *              <li><b>date</b> la date à afficher
  *		 </ul>
 * @param array $echeancier : l'échéancier à afficher s'il est déjà existant
 * @param array $modifiables : une liste des champs éditables de l'échéancier, peut contenir les éléments suivants :<ul>
 *              <li><b>cap</b> pour le capital
 *              <li><b>int</b> pour les intérêts
 *              <li><b>pen</b> pour les pénalités
 *              <li><b>gar</b> pour la garantie
 *              </ul>
 * @param array $Dossier : une liste de certains champs du dossier de crédit :<ul>
 *              <li><b>differe_jours</b> pour le nombre de jour de différé
 *              <li><b>cre_mnt_octr</b> pour le montant octroyé
 *              <li><b>duree_mois</b> pour la durée en mois du crédites
 *              <li><b>garantie_num</b> pour les garanties numéraires
 *              <li><b>garantie_mat</b> pour les garanties matérielles
 *              <li><b>garantie_encours</b> pour les garanties numéraires encours
 *              </ul>
 * @param array $Produit : une liste de certains champs du produit de crédit :<ul>
 *              <li><b>libel</b> pour le libellé du produit
 *              <li><b>type_duree_credit</b> pour le type de durée du crédit
 *              <li><b>tx_interet</b> pour le taux d'intyérêt du produit de crédit
 *              <li><b>periodicite</b> pour la périodicité du produit de crédit
 *              <li><b>mode_calc_int</b> pour le mode de calcul des intérêts
 *              </ul>
 * @return string le code HTML de l'échéancier
 */
function HTML_echeancier_remboursement($parametre, $echeancier, $modifiables, $Dossier, $Produit) {
  global $tableau_border;
  global $tableau_cellspacing;
  global $tableau_cellpadding;
  global $adsys;
  global $SESSION_VARS;
  global $global_monnaie;
  global $global_monnaie_prec;

  //identifiant du dossier
  $id_doss=$parametre["id_doss"];
  // Tableau des détails du produit
  $table1 = new HTML_TABLE_table(4, TABLE_STYLE_CLASSIC);
  $table1->set_property("title",$parametre["titre"]);
  $table1->add_cell(new TABLE_cell(_("Produit:")));
  $table1->set_cell_property("width","15%");
  $table1->add_cell(new TABLE_cell($Produit[0]["libel"]));
  $table1->set_cell_property("width","35%");
  $table1->add_cell(new TABLE_cell(_("Montant octroyé:")));
  $table1->set_cell_property("width","30%");
  if(($parametre['prelev_commission'] == 't' && $parametre['prelev_frais_doss']== 2 && $parametre['debours'] == "true")||($parametre['prelev_commission'] == 't' && $parametre['prelev_frais_doss']== 1 && $parametre['debours'] != "true")){
  	  $table1->add_cell(new TABLE_cell(afficheMontant ($Dossier["cre_mnt_octr"]-$parametre["mnt_des_frais"],true)));
  }else{
      $table1->add_cell(new TABLE_cell(afficheMontant ($Dossier["cre_mnt_octr"],true)));
  }
  $table1->set_cell_property("width","20%");
  
  
  $table1->add_cell(new TABLE_cell(_("Durée:")));
  $table1->add_cell(new TABLE_cell($Dossier["duree_mois"]." ".adb_gettext($adsys["adsys_type_duree_credit"][$Produit[0]["type_duree_credit"]])));
  $table1->add_cell(new TABLE_cell(_("Montant rééchel.:")));
  $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["mnt_reech"],true)));
  $table1->set_row_childs_property("align","left");

  $tx=100*$Produit[0]["tx_interet"];
  $table1->add_cell(new TABLE_cell(_("Différé:")));
  $table1->add_cell(new TABLE_cell(str_affichage_diff($Dossier["differe_jours"], $Dossier["differe_ech"])));
  $table1->add_cell(new TABLE_cell(_("Taux d'intérêt:")));
  $table1->add_cell(new TABLE_cell("$tx%"));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell(_("Périodicité:")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_type_periodicite"][$Produit[0]["periodicite"]])));
  $table1->add_cell(new TABLE_cell(_("Garantie totale:")));
  //debug($Dossier);
  $table1->add_cell(new TABLE_cell(afficheMontant ($Dossier["gar_num"] + $Dossier["gar_num_encours"],true)));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell($parametre["lib_date"]));
  $table1->add_cell(new TABLE_cell($parametre["date"]));
  $table1->add_cell(new TABLE_cell(_("Mode de calcul des intérêts:")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_mode_calc_int_credit"][$Produit[0]["mode_calc_int"]])));
  $table1->set_row_childs_property("align","left");

  // Tableau des échéances
  // Affichage entête
  $table2 = new HTML_TABLE_table(10, TABLE_STYLE_ALTERN);
  $table2->add_cell(new TABLE_cell(_("N°"),1,2));
  $table2->add_cell(new TABLE_cell(_("Date"),1,2));
  $table2->add_cell(new TABLE_cell(_("Montants théoriques"),3));
  $table2->add_cell(new TABLE_cell(_("Montants restant dus"),5));

  $table2->add_cell(new TABLE_cell(_("Capital")));
  $table2->add_cell(new TABLE_cell(_("Intérêts")));
  $table2->add_cell(new TABLE_cell(_("Garantie")));
  $table2->add_cell(new TABLE_cell(_("Capital")));
  $table2->add_cell(new TABLE_cell(_("Intérêts")));
  $table2->add_cell(new TABLE_cell(_("Garantie")));
  $table2->add_cell(new TABLE_cell(_("Pénalités")));
  $table2->add_cell(new TABLE_cell(_("Total de l'échéance")));

  $total_cap_th = 0;
  $total_int_th = 0;
  $total_gar_th = 0;

  $total_cap = 0;
  $total_int = 0;
  $total_pen = 0;
  $total_gar = 0;

  foreach ($echeancier AS $key=>$echeanc) {

    $total_cap_th = $total_cap_th + $echeanc["mnt_cap"];
    $total_int_th = $total_int_th + $echeanc["mnt_int"];
    $total_gar_th = $total_gar_th + $echeanc["mnt_gar"];


    $total_cap = $total_cap + $echeanc["solde_cap"];
    $total_int = $total_int + $echeanc["solde_int"];
    $total_gar = $total_gar + $echeanc["solde_gar"];
    $total_pen = $total_pen + $echeanc["solde_pen"];

    $date = pg2phpDate($echeanc["date_ech"]);

    $som = $echeanc["solde_cap"] + $echeanc["solde_int"] + $echeanc["solde_gar"] + $echeanc["solde_pen"];

    // Affichage échéances
    $table2->add_cell(new TABLE_cell($echeanc["id_ech"]));
    $table2->set_cell_property("align","center");

    if ($modifiables['date_ech'])
      $table2->add_cell(new TABLE_cell_input_text("date".$id_doss.$key, 12, $date, "", "right"));
    else
      $table2->add_cell(new TABLE_cell_date($date));

    $table2->set_cell_property("align","left");

    $table2->add_cell(new TABLE_cell(afficheMontant($echeanc["mnt_cap"])));
    $table2->set_cell_property("align","center");

    $table2->add_cell(new TABLE_cell(afficheMontant($echeanc["mnt_int"])));
    $table2->set_cell_property("align","center");

    $table2->add_cell(new TABLE_cell(afficheMontant($echeanc["mnt_gar"])));
    $table2->set_cell_property("align","center");


    foreach (array ("cap", "int", "gar", "pen") as $cellule) {
      if ($modifiables[$cellule]) {
        $table2->add_cell(new TABLE_cell_input_text("solde_".$cellule.$id_doss.$key, 12, afficheMontant($echeanc["solde_".$cellule]), "document.ADForm.solde_".$cellule.$id_doss.$key.".value = formateMontant(document.ADForm.solde_".$cellule.$id_doss.$key.".value);checkSumLessThan(".$cellule."Cells".$id_doss.", document.ADForm.total_".$cellule.$id_doss.", total_".$cellule."_original$id_doss, '');\n", "right"));
      } else {
        $table2->add_cell(new TABLE_cell(afficheMontant($echeanc["solde_".$cellule], false)));
      }
    }
    $table2->add_cell(new TABLE_cell(afficheMontant($som,false)));
    $table2->set_row_childs_property("align","right");
  }
  $total=$total_cap+$total_int+$total_gar+$total_pen;

  $table2->add_cell(new TABLE_cell(_("Total"),2));
  $table2->set_row_childs_property("align","center");

  foreach (array ("cap", "int", "gar") as $cellule) {
    $table2->add_cell(new TABLE_cell(afficheMontant(eval("return \$total_".$cellule."_th;"),false)));
  }

  foreach (array ("cap", "int", "gar", "pen") as $cellule) {
    if ($modifiables[$cellule]) {
      $table2->add_cell(new TABLE_cell_input_text("total_".$cellule.$id_doss, 12, afficheMontant(eval("return \$total_".$cellule.";"),false), "", "right", true));
    } else {
      $table2->add_cell(new TABLE_cell(afficheMontant(eval("return \$total_".$cellule.";"),false)));
    }
  }
  $table2->add_cell(new TABLE_cell(afficheMontant($total,false)));
  $table2->set_row_childs_property("align","right");
  $table2->set_row_childs_property("bold");
  $SESSION_VARS["total_int"] = $total_int;
  $SESSION_VARS["total_pen"] = $total_pen;

  return $table1->gen_HTML().$table2->gen_HTML();
}

/**
 * str_affichage_diff Construction de la string pour l'affichage du différé
 *
 * @param mixed $diff_jours Le différé en jours
 * @param mixed $diff_ech Le différé en échéances
 * @access public
 * @return string La chaîne pour l'affichage du différé
 */
function str_affichage_diff($diff_jours, $diff_ech) {
  if ($diff_jours > 1) {
    $affichage_differe = sprintf(_("%d jours"), $diff_jours);
  } else if ($diff_jours == 1) {
    $affichage_differe = _("un jour");
  } else {
    $affichage_differe = "";
  }
  if ($diff_ech > 0 && $diff_jours > 0) {
    $affichage_differe .= " "._("et")." ";
  }
  if ($diff_ech > 1) {
    $affichage_differe .= sprintf(_("%d échéances"), $diff_ech);
  } else if ($diff_ech == 1) {
    $affichage_differe .= _("une échéance");
  } else if ($diff_jours < 1) {
    $affichage_differe = _("Aucun");
  }
  return $affichage_differe;
}



/**
 * HTML_echeancier_remboursement : Renvoie le code html pour un échéancier de remboursement éventuellement modifiable
 * @author Antoine Delvaux
 * @since 2.1
 * @param array $parametre : les paramètres de l'échéancier de remboursement, ils sont principalement utilisés pour construire le tableau d'entête de l'affichage de l'échéancier, ce tableau peut contenir les éléments suivants :<ul>
 *              <li><b>diff</b> le différé en jours du crédit
 *              <li><b>diff_ech</b> le différé en échéances du crédit
 *              <li><b>titre</b> le titre de l'échéancier
 *              <li><b>mnt_reech</b> le montant du rééchelonnement
 *              <li><b>lib_date</b> le libellé à utiliser devant l'affichage de la date du dossier de crédit
 *              <li><b>date</b> la date à afficher
 *		 </ul>
 * @param array $echeancier : l'échéancier à afficher s'il est déjà existant
 * @param array $modifiables : une liste des champs éditables de l'échéancier, peut contenir les éléments suivants :<ul>
 *              <li><b>cap</b> pour le capital
 *              <li><b>int</b> pour les intérêts
 *              <li><b>pen</b> pour les pénalités
 *              <li><b>gar</b> pour la garantie
 *              </ul>
 * @param array $Dossier : une liste de certains champs du dossier de crédit :<ul>
 *              <li><b>differe_jours</b> pour le nombre de jour de différé
 *              <li><b>cre_mnt_octr</b> pour le montant octroyé
 *              <li><b>duree_mois</b> pour la durée en mois du crédites
 *              <li><b>garantie_num</b> pour les garanties numéraires
 *              <li><b>garantie_mat</b> pour les garanties matérielles
 *              <li><b>garantie_encours</b> pour les garanties numéraires encours
 *              </ul>
 * @param array $Produit : une liste de certains champs du produit de crédit :<ul>
 *              <li><b>libel</b> pour le libellé du produit
 *              <li><b>type_duree_credit</b> pour le type de durée du crédit
 *              <li><b>tx_interet</b> pour le taux d'intyérêt du produit de crédit
 *              <li><b>periodicite</b> pour la périodicité du produit de crédit
 *              <li><b>mode_calc_int</b> pour le mode de calcul des intérêts
 *              </ul>
 * @return string le code HTML de l'échéancier
 */
function HTML_echeancier_remboursement_anticipe($parametre, $echeancier, $modifiables, $Dossier, $Produit) {
  global $tableau_border;
  global $tableau_cellspacing;
  global $tableau_cellpadding;
  global $adsys;
  global $SESSION_VARS;
  global $global_monnaie;
  global $global_monnaie_prec;

  //identifiant du dossier
  $id_doss=$parametre["id_doss"];
  // Tableau des détails du produit
  $table1 = new HTML_TABLE_table(4, TABLE_STYLE_CLASSIC);
  $table1->set_property("title",$parametre["titre"]);
  $table1->add_cell(new TABLE_cell(_("Produit:")));
  $table1->set_cell_property("width","15%");
  $table1->add_cell(new TABLE_cell($Produit[0]["libel"]));
  $table1->set_cell_property("width","35%");
  $table1->add_cell(new TABLE_cell(_("Montant octroyé:")));
  $table1->set_cell_property("width","30%");
  if(($parametre['prelev_commission'] == 't' && $parametre['prelev_frais_doss']== 2 && $parametre['debours'] == "true")||($parametre['prelev_commission'] == 't' && $parametre['prelev_frais_doss']== 1 && $parametre['debours'] != "true")){
    $table1->add_cell(new TABLE_cell(afficheMontant ($Dossier["cre_mnt_octr"]-$parametre["mnt_des_frais"],true)));
  }else{
    $table1->add_cell(new TABLE_cell(afficheMontant ($Dossier["cre_mnt_octr"],true)));
  }
  $table1->set_cell_property("width","20%");


  $table1->add_cell(new TABLE_cell(_("Durée:")));
  $table1->add_cell(new TABLE_cell($Dossier["duree_mois"]." ".adb_gettext($adsys["adsys_type_duree_credit"][$Produit[0]["type_duree_credit"]])));
  $table1->add_cell(new TABLE_cell(_("Montant rééchel.:")));
  $table1->add_cell(new TABLE_cell(afficheMontant ($parametre["mnt_reech"],true)));
  $table1->set_row_childs_property("align","left");

  $tx=100*$Produit[0]["tx_interet"];
  $table1->add_cell(new TABLE_cell(_("Différé:")));
  $table1->add_cell(new TABLE_cell(str_affichage_diff($Dossier["differe_jours"], $Dossier["differe_ech"])));
  $table1->add_cell(new TABLE_cell(_("Taux d'intérêt:")));
  $table1->add_cell(new TABLE_cell("$tx%"));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell(_("Périodicité:")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_type_periodicite"][$Produit[0]["periodicite"]])));
  $table1->add_cell(new TABLE_cell(_("Garantie totale:")));
  //debug($Dossier);
  $table1->add_cell(new TABLE_cell(afficheMontant ($Dossier["gar_num"] + $Dossier["gar_num_encours"],true)));
  $table1->set_row_childs_property("align","left");

  $table1->add_cell(new TABLE_cell($parametre["lib_date"]));
  $table1->add_cell(new TABLE_cell($parametre["date"]));
  $table1->add_cell(new TABLE_cell(_("Mode de calcul des intérêts:")));
  $table1->add_cell(new TABLE_cell(adb_gettext($adsys["adsys_mode_calc_int_credit"][$Produit[0]["mode_calc_int"]])));
  $table1->set_row_childs_property("align","left");

  // Tableau des échéances
  // Affichage entête
  $table2 = new HTML_TABLE_table(10, TABLE_STYLE_ALTERN);
  $table2->add_cell(new TABLE_cell(_("N°"),1,2));
  $table2->add_cell(new TABLE_cell(_("Date"),1,2));
  $table2->add_cell(new TABLE_cell(_("Montants théoriques"),3));
  $table2->add_cell(new TABLE_cell(_("Montants restant dus"),5));

  $table2->add_cell(new TABLE_cell(_("Capital")));
  $table2->add_cell(new TABLE_cell(_("Intérêts")));
  $table2->add_cell(new TABLE_cell(_("Garantie")));
  $table2->add_cell(new TABLE_cell(_("Capital")));
  $table2->add_cell(new TABLE_cell(_("Intérêts")));
  $table2->add_cell(new TABLE_cell(_("Garantie")));
  $table2->add_cell(new TABLE_cell(_("Pénalités")));
  $table2->add_cell(new TABLE_cell(_("Total de l'échéance")));

  $total_cap_th = 0;
  $total_int_th = 0;
  $total_gar_th = 0;

  $total_cap = 0;
  $total_int = 0;
  $total_pen = 0;
  $total_gar = 0;

  // datre du jour
  $date_jour = date("d");
  $date_mois = date("m");
  $date_annee = date("Y");
  $date_total = $date_jour."/".$date_mois."/".$date_annee;
  $date_test = $date_annee."-".$date_mois."-".$date_jour;
  $date_debut_mois = date("Y-m-01", strtotime($date_test));
  $date_fin_mois =date("Y-m-t", strtotime($date_test));

  foreach ($echeancier AS $key=>$echeanc) {

    $total_cap_th = $total_cap_th + $echeanc["mnt_cap"];
    $total_int_th = $total_int_th + $echeanc["mnt_int"];
    $total_gar_th = $total_gar_th + $echeanc["mnt_gar"];


    $total_cap = $total_cap + $echeanc["solde_cap"];
    $date_ech = explode('/',pg2phpDate($echeanc['date_ech']));
    $date_ech_final = $date_ech[2]."-".$date_ech[1]."-".$date_ech[0];
    if($date_ech_final <= $date_fin_mois) {
      $total_int = $total_int + $echeanc["solde_int"];
    }else{
      $total_int = $total_int + 0;
    }
    $total_gar = $total_gar + $echeanc["solde_gar"];
    $total_pen = $total_pen + $echeanc["solde_pen"];

    $date = pg2phpDate($echeanc["date_ech"]);

    if($date_ech_final <= $date_fin_mois) {
      $som = $echeanc["solde_cap"] + $echeanc["solde_int"] + $echeanc["solde_gar"] + $echeanc["solde_pen"];
    }else{
      $som = $echeanc["solde_cap"] + 0+ $echeanc["solde_gar"] + $echeanc["solde_pen"];
    }


    // Affichage échéances
    $table2->add_cell(new TABLE_cell($echeanc["id_ech"]));
    $table2->set_cell_property("align","center");

    if ($modifiables['date_ech'])
      $table2->add_cell(new TABLE_cell_input_text("date".$id_doss.$key, 12, $date, "", "right"));
    else
      $table2->add_cell(new TABLE_cell_date($date));

    $table2->set_cell_property("align","left");

    $table2->add_cell(new TABLE_cell(afficheMontant($echeanc["mnt_cap"])));
    $table2->set_cell_property("align","center");

    $table2->add_cell(new TABLE_cell(afficheMontant($echeanc["mnt_int"])));
    $table2->set_cell_property("align","center");

    $table2->add_cell(new TABLE_cell(afficheMontant($echeanc["mnt_gar"])));
    $table2->set_cell_property("align","center");


    foreach (array ("cap", "int", "gar", "pen") as $cellule) {
      if ($modifiables[$cellule]) {
        if ($cellule == "int") {
          $date_ech = explode('/',pg2phpDate($echeanc['date_ech']));
          $date_ech_final = $date_ech[2]."-".$date_ech[1]."-".$date_ech[0];
          if ($date_ech_final <= $date_fin_mois) {
            $table2->add_cell(new TABLE_cell_input_text("solde_" . $cellule . $id_doss . $key, 12, afficheMontant($echeanc["solde_" . $cellule]), "document.ADForm.solde_" . $cellule . $id_doss . $key . ".value = formateMontant(document.ADForm.solde_" . $cellule . $id_doss . $key . ".value);checkSumLessThan(" . $cellule . "Cells" . $id_doss . ", document.ADForm.total_" . $cellule . $id_doss . ", total_" . $cellule . "_original$id_doss, '');\n", "right"));
          } else {
            //$table2->add_cell(new TABLE_cell_input_text("solde_" . $cellule . $id_doss . $key, 12, '0', "document.ADForm.solde_" . $cellule . $id_doss . $key . ".value = formateMontant(document.ADForm.solde_" . $cellule . $id_doss . $key . ".value);checkSumLessThan(" . $cellule . "Cells" . $id_doss . ", document.ADForm.total_" . $cellule . $id_doss . ", total_" . $cellule . "_original$id_doss, '');\n", "right"));
            //$table2->add_cell(new TABLE_cell_input_text("solde_" . $cellule . $id_doss . $key,12,'0',"","left","true"));
            $table2->add_cell(new TABLE_cell(afficheMontant('0', false)));
          }
        }else {
          $table2->add_cell(new TABLE_cell_input_text("solde_".$cellule.$id_doss.$key, 12, afficheMontant($echeanc["solde_".$cellule]), "document.ADForm.solde_".$cellule.$id_doss.$key.".value = formateMontant(document.ADForm.solde_".$cellule.$id_doss.$key.".value);checkSumLessThan(".$cellule."Cells".$id_doss.", document.ADForm.total_".$cellule.$id_doss.", total_".$cellule."_original$id_doss, '');\n", "right"));
        }
      } else {
        $table2->add_cell(new TABLE_cell(afficheMontant($echeanc["solde_".$cellule], false)));
      }
    }
    $table2->add_cell(new TABLE_cell(afficheMontant($som,false)));
    $table2->set_row_childs_property("align","right");
  }
  $total=$total_cap+$total_int+$total_gar+$total_pen;

  $table2->add_cell(new TABLE_cell(_("Total"),2));
  $table2->set_row_childs_property("align","center");

  foreach (array ("cap", "int", "gar") as $cellule) {
    $table2->add_cell(new TABLE_cell(afficheMontant(eval("return \$total_".$cellule."_th;"),false)));
  }

  foreach (array ("cap", "int", "gar", "pen") as $cellule) {
    if ($modifiables[$cellule]) {
      $table2->add_cell(new TABLE_cell_input_text("total_".$cellule.$id_doss, 12, afficheMontant(eval("return \$total_".$cellule.";"),false), "", "right", true));
    } else {
      $table2->add_cell(new TABLE_cell(afficheMontant(eval("return \$total_".$cellule.";"),false)));
    }
  }
  $table2->add_cell(new TABLE_cell(afficheMontant($total,false)));
  $table2->set_row_childs_property("align","right");
  $table2->set_row_childs_property("bold");
  $SESSION_VARS["total_int"] = $total_int;
  $SESSION_VARS["total_pen"] = $total_pen;

  return $table1->gen_HTML().$table2->gen_HTML();
}



?>