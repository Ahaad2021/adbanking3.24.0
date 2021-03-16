<?php

require_once 'lib/misc/divers.php';
require_once 'lib/dbProcedures/compta.php';

require 'lib/html/HtmlHeader.php';

echo "<h1 align=\"center\">"._("Détail clôture périodique")."</h1><br/><br/>";

if (isset($id_clot)) {

  // Entête du formulaire
  echo "<FORM name=\"ADForm\" action=\"$PHP_SELF\" method=\"post\">\n";

  // Tableau pour les infos de la clôture
  echo "<table align=\"center\"
  bgcolor=$colb_tableau
  border=$tableau_border
  cellspacing=$tableau_cellspacing
  cellpadding=$tableau_cellpadding>\n";

  // Entête tableau
  echo "<tr bgcolor=$colb_tableau align=\"center\">
  <td><b>"._("Numéro clôture")."</b></td>
  <td><b>"._("Date clôture")."</b></td>
  <td><b>"._("Exercice")."</b></td>
  </tr>\n";

  // Infos de la clôture
  $param = array();
  $param["id_clot_per"] = $id_clot;
  $cloture = getCloturesPeriodiques($param);
  $cloture = $cloture['id_ag'];

  $color = $colb_tableau_altern;
  $html = "";
  $html .= "<tr bgcolor=$color align=\"center\">\n";
  $html .= "<td <SPAN> nowrap>".$cloture[$id_clot]["id_clot_per"]."</td>";
  $html .= "<td <SPAN> nowrap>".pg2phpDate($cloture[$id_clot]["date_clot_per"])."</td>";
  $html .= "<td <SPAN> nowrap>".$cloture[$id_clot]["id_exo"]."</td>";
  $html .= "</tr>";
  $html = str_replace('<SPAN>', "rowspan=$count", $html);

  echo $html;
  echo "</table><br />";

  // Tableau pour les détails de la clôture
  echo "<tale align=\"center\"
  bgcolor=$colb_tableau
  border=$tableau_border
  cellspacing=$tableau_cellspacing
  cellpadding=$tableau_cellpadding>\n";

  // Entête tableau
  echo "<tr bgcolor=$colb_tableau align=\"center\">
  <td><b>"._("Compte comptable")."</b></td>
  <td><b>"._("Libellé compte comptable")."</b></td>
  <td><b>"._("Solde débiteur")."</b></td>
  <td><b>"._("Solde créditeur")."</b></td>
  </tr>\n";

  // Détail tableau
  $html = "";

  $total_debit = 0;
  $total_credit = 0;
  global $global_monnaie, $global_id_agence;
  setMonnaieCourante($global_monnaie);

  // Soldes des comptes comptables à cette clôture
  $param= array();
  $param["id_cloture"] = $id_clot;
  $details = getDetailClotPer($param);

  /* Soldes des comptes à cette clôture périodique */
  foreach ($details as $key=>$value) {
    //On alterne la couleur de fond a chaque écriture
    $html = str_replace('<SPAN>', "rowspan=$count", $html);
    $a = !$a;

    if (!$a)
      $color = $colb_tableau;
    else
      $color = $colb_tableau_altern;

    $html .= "<tr bgcolor=$color>\n";

    // Numéro compte comptable
    $html .= "<td <SPAN> nowrap>".$value['num_cpte_comptable_solde']."</td>";

    // Libellé compte comptable
    $temp = array();
    $temp["num_cpte_comptable"] = $value["num_cpte_comptable_solde"];
    $compte= getComptesComptables($temp);
    $html .= "<td <SPAN> nowrap>".$compte[$value["num_cpte_comptable_solde"]]["libel_cpte_comptable"]."</td>";

    // Solde du compte comptable
    if ($value['solde_cloture'] < 0) { // solde est débiteur
      $solde = calculeCV($compte[$value["num_cpte_comptable_solde"]]["devise"], $global_monnaie, $value['solde_cloture']);
      $total_debit += abs($solde);
      $html .= "<td <SPAN> nowrap>".afficheMontant(abs($solde),true)."</td>";
      $html .= "<td <SPAN> nowrap></td>";
    }
    elseif($value['solde_cloture'] > 0) { // solde créditeur
      $solde = calculeCV($compte[$value["num_cpte_comptable_solde"]]["devise"], $global_monnaie, $value['solde_cloture']);
      $total_credit += $solde;
      $html .= "<td <SPAN> nowrap></td>";
      $html .= "<td <SPAN> nowrap>".afficheMontant($solde,true)."</td>";
    }
    else { // solde = 0
      $html .= "<td <SPAN> nowrap></td>";
      $html .= "<td <SPAN> nowrap></td>";
    }

    $html .= "</tr>";
    $html = str_replace('<SPAN>', "rowspan=$count", $html);

  }

  // Ajout des totaux
  $html .= "<tr bgcolor=$color>\n";
  $html .= "<td <SPAN> nowrap align=\"left\"><b>Totaux</b></td>";
  $html .= "<td <SPAN> nowrap></td>";
  $html .= "<td <SPAN> nowrap><b>".afficheMontant($total_debit,true)."</b></td>";
  $html .= "<td <SPAN> nowrap><b>".afficheMontant($total_credit,true)."</b></td>";
  $html .= "</tr>";
  $html = str_replace('<SPAN>', "rowspan=$count", $html);

  echo $html;
  echo "</table>";

  echo "<br /> <br /> <p align=\"center\"> <input type=\"submit\" value=\""._("Fermer")."\" onclick=\"window.close();\" /> </p>";

}

require 'lib/html/HtmlFooter.php';

?>