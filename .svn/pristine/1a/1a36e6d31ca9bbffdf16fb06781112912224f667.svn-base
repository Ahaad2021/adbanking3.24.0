<?php

// TODO
// Ce fichier contient toutes les assertions qui sont censées être vérifiées à tout moment (sauf à l'intérieur d'une transaction)
// Pour le moment, cette fonction ne sera active qu'en mode DEBUG,
// mais il sera intéressant de l'utiliser plus souvent afin de détecter très tôt des erreurs dans le traitement logiciel


/**
 * Effectue la vérification des diverses assertions dans le logiciel
 * Si une ou plusieurs assertiosn ne se vérifient pas, affiche à l'écran un message d'alerte
 * @author homas Fastenakel
 * @since 2.0.2
 */
function check_assert() {

  $RESULT = array(); // Un tableau qui va contenir le résultat de chaque assertion

  // Vérifie l'assertion concernant la position de change
  check_assert_position_change($RESULT);

  // Affichage des messages si nécessaire
  foreach ($RESULT as $assert_name => $RES) {
    if ($RES["result"] == false)
      echo "<P><FONT color=red> "._("Erreur dans l'assertion")." $assert_name : ".$RES["msg"]."</FONT></P>";
  }
  return true;
}

/**
 * Vérfication de l'assertion concernant la position de change
 * On vérifie que pour tous les comptes de position de change, les comptes de C/V position de change contiennent bien la C/V de celle-ci
 * @author Thomas Fastenakel
 * @since 2.0.2
 */
function check_assert_position_change(&$LAST_RES) {
  global $dbHandler;

  global $global_id_agence;
  global $global_monnaie;

  $DEVS = get_table_devises();
  // Pour chaque devise
  foreach ($DEVS as $devise => $value) {

    // On skippe la devise de référence
    if ($devise != $global_monnaie) {
      $CPTS = getCptesLies($devise);

      // Recherche des infos sur la position de change de cette devise
      $ACC_PCHS = getComptesComptables(array("num_cpte_comptable" => $CPTS["position"]));
      $ACC_PCH = $ACC_PCHS[$CPTS["position"]];

      // Recherche des infos sur la C/V position de change de cette devise
      $ACC_CVPCHS = getComptesComptables(array("num_cpte_comptable" => $CPTS["cvPosition"]));
      $ACC_CVPCH = $ACC_CVPCHS[$CPTS["cvPosition"]];
      // Vérification que les ssoldes sont bien équivalents
      if (!estEquivalent($ACC_PCH["solde"], $devise, -$ACC_CVPCH["solde"], $global_monnaie)) {
        $msg = sprintf(_("En %s, la position de change ne correspond pas à la C/V"), $devise);
        $RES = array("result" => false, "msg" => $msg);
        break;
      } else
        $RES = array("result" => true);
    } else
      $RES = array("result" => true);
  }

  $LAST_RES["assert_pos_ch"] = $RES;

  return true;
}

?>