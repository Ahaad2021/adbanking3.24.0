<?php

require_once 'lib/misc/tableSys.php';
require_once 'lib/misc/VariablesGlobales.php';

// Multi agence includes
require_once 'ad_ma/app/controllers/misc/VariablesSessionRemote.php';
require_once 'ad_ma/app/models/AgenceRemote.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/Audit.php';
require_once 'ad_ma/app/models/Client.php';
require_once 'ad_ma/app/models/Compta.php';
require_once 'ad_ma/app/models/Compte.php';
require_once 'ad_ma/app/models/Credit.php';
require_once 'ad_ma/app/models/Devise.php';
require_once 'ad_ma/app/models/Divers.php';
require_once 'ad_ma/app/models/Epargne.php';
require_once 'ad_ma/app/models/Guichet.php';
require_once 'ad_ma/app/models/Historique.php';
require_once 'ad_ma/app/models/Parametrage.php';
require_once 'ad_ma/app/models/TireurBenef.php';


function HTML_suiviCreditMA($parametre,$echeancier=null) {
  global $colb_tableau;
  global $colb_tableau_altern;
  global $tableau_border;
  global $tableau_cellspacing;
  global $tableau_cellpadding;
  global $adsys;
  global $SESSION_VARS;
  global $global_monnaie;
  global $global_monnaie_prec;
  global $pdo_conn;
  global $global_remote_id_agence;

  // Renvoie le code html du suivi du crédit
  // INPUT: $Parametre et $echeancier

  // Init class
  $CreditObj = new Credit($pdo_conn, $global_remote_id_agence);
  
  // Recherche des échéances en retard
  $id_doss = $parametre["id_doss"];
  $echeancier_retard = $CreditObj->getEcheancier("WHERE id_doss = $id_doss AND remb = 'f' AND date_ech < NOW()");
  $nb_echeances_retard = count($echeancier_retard);
  $date_dernier_remb = $CreditObj->getDateLastRemb($parametre["id_doss"]);
  
  if($echeancier==null) {
    $echeancier = $CreditObj->getEcheancier("WHERE id_doss = $id_doss");
  }

  unset($CreditObj);

  // Récupération du montant des pénalités pour ce crédit
  $solde_pen_du = 0;
  $solde_pen_rest = 0;
  reset($echeancier);// Réinitialise le pointeur de tableau des écheances
  while (list($key,$echanc) = each($echeancier)) {
    $total_pen_du += $echanc["pen_att"];
    $total_pen_remb += $echanc["pen_remb"];
  }
  $total_pen_rest = $total_pen_du - $total_pen_remb;

  $retour="";
  $retour .="<h1 align=\"center\">".$parametre["titre"]."</h1>\n";

  //Tableau principal
  $retour .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=\"0\" border=\"1\">\n";
  $retour .= "<TR bgcolor=$colb_tableau>\n";
  $retour .= "<TD>";

  //Tableau entête
  $retour .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\" cellspacing=\"0\" border=\"0\">\n";
  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2 width=\"23%\">"._("Montant capital octroyé").":</TD>\n";// Cap dû
  $retour .= "<TD width=\"25%\">".afficheMontant ($parametre["cap_du"],true)."</TD>\n";
  $retour .= "<TD colspan=2 width=\"29%\">"._("Montant capital restant").":</TD>\n"; //Cap. restant
  $retour .= "<TD>".afficheMontant ($parametre["cap_rest"],true)."</TD>\n";
  $retour .= "</TR>\n";

  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2 >"._("Montant intérêts dûs").":</TD>\n"; // Int dû
  $retour .= "<TD>".afficheMontant ($parametre["int_du"],true)."</TD>\n";
  $retour .= "<TD colspan=2 >"._("Montant intérêts restants").":</TD>\n"; // int restant
  $retour .= "<TD>".afficheMontant ($parametre["int_rest"],true)."</TD>\n";
  $retour .= "</TR>\n";


  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2 >"._("Montant garantie dûe en cours").":</TD>\n"; // Gar dûe
  $retour .= "<TD>".afficheMontant ($parametre["gar_du"],true)."</TD>\n";
  $retour .= "<TD colspan=2 >"._("Montant garantie restante en cours").":</TD>\n"; // Gar restante
  $retour .= "<TD>".afficheMontant ($parametre["gar_rest"],true)."</TD>\n";
  $retour .= "</TR>\n";

  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2 >"._("Montant pénalités dûes").":</TD>\n"; // Gar dûe
  $retour .= "<TD>".afficheMontant($total_pen_du, true)."</TD>\n";
  $retour .= "<TD colspan=2 >"._("Montant pénalités restantes dûes")." :</TD>\n"; // Gar restante
  $retour .= "<TD>".afficheMontant($total_pen_rest, true)."</TD>\n";
  $retour .= "</TR>\n";

  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2>"._("Nbre d'échéances").":</TD>\n";
  $retour .= "<TD>".$parametre["Nbre_Ech"]."</TD>\n";
  $retour .= "<TD colspan=2 width=\"15%\">"._("Nbre d'échéances restants").":</TD>\n";
  $retour .= "<TD width=\"35%\">".$parametre["Nbre_rest"]."</TD>\n";
  $retour .= "</TR>\n";

  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2>"._("Etat le plus avancé").":</TD>\n";
  $retour .= "<TD>".$parametre["cre_retard_etat_max"]."</TD>\n";
  $retour .= "<TD colspan=2 width=\"15%\">"._("Plus grand nombre jour de retard observé")." :</TD>\n";
  $retour .= "<TD width=\"35%\">".$parametre["cre_retard_etat_max_jour"]."</TD>\n";

  $retour .= "</TR>\n";

  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2>"._("Date du dernier remboursement").":</TD>\n";
  $retour .= "<TD>".$date_dernier_remb["date_remb"]."</TD>\n";
  $retour .= "<TD colspan=2 width=\"15%\">"._("Nbre d'échéances en retard").":</TD>\n";
  $retour .= "<TD width=\"35%\">".$nb_echeances_retard."</TD>\n";

  $retour .= "</TR>\n";

  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD colspan=2>"._("Montant provisionné").":</TD>\n";
  $retour .= "<TD>".afficheMontant($total_pen_rest, true)."</TD>\n";
  if (!isset( $parametre["cre_mnt_deb"])) {
  	$retour .= "<TD colspan=2></TD>\n";
   $retour .= "<TD ></TD>\n";
  } else {
  	$retour .= "<TD colspan=2>"._("Montant déboursé")."</TD>\n";
    $retour .= "<TD >".afficheMontant ($parametre["cre_mnt_deb"],true)."</TD>\n";
  }
   $retour .= "</TR>\n";


  $retour .= "</TABLE>\n";

  //Fin Tableau
  $retour .= "</TD>\n";
  $retour .= "</TR>\n";

  $retour .= "<TR bgcolor=$colb_tableau>\n";
  $retour .= "<TD>";
  //Tableau des echéances
  $retour .= "<TABLE width=\"100%\" align=\"center\" valign=\"middle\"cellspacing=$tableau_cellspacing cellpadding=$tableau_cellpadding  border=$tableau_border>\n";
  $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
  $retour .= "<TD align=\"center\">"._("N°")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Date théorique")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Capital attendu")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Intérêts attendus")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Pénalités attendues")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Garantie attendue")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Capital remb.")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Intérêts remb.")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Pénalités remb.")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Garantie remb.")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Montant total remb.")."</TD>\n";
  $retour .= "<TD align=\"center\">"._("Echéance clôturée")."</TD>\n";
  $retour .= "</TR>\n";

  // Echéancier non stocké dans la base de données
  // D'où la nécessité de le générer entièrement
  reset($echeancier); // Réinitialise le pointeur de tableau des écheances
  while (list($key,$echanc) = each($echeancier)) {
      
    // Init class
    $CreditObj = new Credit($pdo_conn, $global_remote_id_agence);  
    
    // Affichage
    if ($echanc["id"]=="RM") {// Rééchélonnement moratoire
      $retour .= "<TR bgcolor=\"$colb_tableau\">\n";
      $retour .= "<TD colspan=11 align=\"center\">".$echanc["titre"]."</TD>\n";
      $retour .= "</TR>\n";
    } else {
      // Formatage des valeurs
      $date_ech = pg2phpDate($echanc["date_ech"]);
      $cap_att = afficheMontant ($echanc["mnt_cap"], false);
      $int_att = afficheMontant ($echanc["mnt_int"], false);
      $pen_att = afficheMontant ($echanc["solde_pen"], false);
      $gar_att = afficheMontant ($echanc["mnt_gar"], false);
      $REMB = $CreditObj->getRemboursement("WHERE id_doss = ".$parametre["id_doss"]." AND id_ech = ".$echanc["id_ech"]);
      $i = 0;
      $pen_remb='0'; //Pénalité remboursé
      $cap_remb='0'; //Capital remboursé pour l'échéance i
      $int_remb='0'; //Intérêt remboursé pour l'échéance i
      $gar_remb='0'; //Garantie remboursée pour l'échéance i
      while (list($num_remb, $remb) = each($REMB)) {
         $cap_remb += $remb["mnt_remb_cap"]; //Capital remboursé pour l'échéance i
         $int_remb += $remb["mnt_remb_int"]; //Intérêts remboursés pour l'échéance i
         $pen_remb += $remb["mnt_remb_pen"]; //Pénalités remboursées pour l'échéance i
         $gar_remb += $remb["mnt_remb_gar"]; //Intérêts remboursés pour l'échéance i
      }
      //Le montal total remboursé pour chaque échéance
      $mnt_total_remb= $cap_remb + $int_remb + $pen_remb;

      // Formatage des valeurs
      $cap_remb = afficheMontant ($cap_remb, false);
      $int_remb = afficheMontant ($int_remb, false);
      $pen_remb = afficheMontant ($pen_remb, false);
      $gar_remb = afficheMontant ($gar_remb, false);
      $mnt_total_remb= afficheMontant($mnt_total_remb,false);

      (($echanc["id_ech"] % 2==0) ? $retour .= "<TR bgcolor=\"$colb_tableau\">\n" : $retour .= "<TR bgcolor=\"$colb_tableau_altern\">\n");
      if (sizeof($REMB) > 0)
        $retour .= "<TD align=\"center\"><a href=\"javascript:open_remb(".$parametre["id_doss"].",".$echanc["id_ech"].")\">".$echanc["id_ech"]."</a></TD>\n";
      else
        $retour .= "<TD align=\"center\">".$echanc["id_ech"]."</TD>\n";
      $retour .= "<TD align=\"center\">".$date_ech."</TD>\n";
      $retour .= "<TD align=\"right\">".$cap_att."</TD>\n";
      $retour .= "<TD align=\"right\">".$int_att."</TD>\n";
      $retour .= "<TD align=\"right\">".$pen_att."</TD>\n";
      $retour .= "<TD align=\"right\">".$gar_att."</TD>\n";
      $retour .= "<TD align=\"right\">".$cap_remb."</TD>\n";
      $retour .= "<TD align=\"right\">".$int_remb."</TD>\n";
      $retour .= "<TD align=\"right\">".$pen_remb."</TD>\n";
      $retour .= "<TD align=\"right\">".$gar_remb."</TD>\n";
      $retour .= "<TD align=\"right\">".$mnt_total_remb."</TD>\n";
      if ($echanc["remb"] == TRUE)
        $retour .= "<TD align=\"center\">"._("Oui")."</TD>\n";
      else
        $retour .= "<TD align=\"center\">"._("Non")."</TD>\n";
      $retour .= "</TR>\n";
    }
    ++$i;
    
    unset($CreditObj);
  }
  $retour .= "</TABLE>\n";

  //Fin Tableau des échéances
  $retour .= "</TD>\n";
  $retour .= "</TR>\n";

  //Tableau principal
  $retour .= "</TABLE>\n";
  return $retour;
}
?>
