<?php

require_once "lib/html/HTML_GEN2.php";

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HTML_GEN2_REMOTE
 *
 * @author BD0513
 */
class HTML_GEN2_REMOTE extends HTML_GEN2 {

    public function __construct($title = "") {
        
        parent::HTML_GEN2($title);
    }

    public function addTableRemote(&$dbc, $nomTable, $operateur, $champs) {/* Paramètres entrants :
      - nom (court) de la table à insérer
      - opérateur qui va être appliqué aux champs suivants (cf. define)
      - champs qui vont être traités par l'opérateur

      Traitement :
      Insère les champs de la table en fin d'array en consultant d_tableliste
     */
        $rowset = Divers::getFieldsFromTable($dbc, $nomTable); // Récupère tous les champs de la table
        
        // var_dump($rowset);

        foreach($rowset as $row) {
            switch ($operateur) {
                case OPER_INCLUDE:
                    if (in_array($row["nchmpc"], $champs))
                        $this->insertField($row, $nomTable);
                    break;
                case OPER_EXCLUDE:
                    if (!in_array($row["nchmpc"], $champs))
                        $this->insertField($row, $nomTable);
                    break;
                case OPER_NONE:
                    $this->insertField($row, $nomTable);
                    break;
                default:
                    signalErreur(__FILE__, __LINE__, __FUNCTION__); // "L'opérateur $operateur n'est pas reconnu"
            }
        }
        return true;
    }

}

?>
