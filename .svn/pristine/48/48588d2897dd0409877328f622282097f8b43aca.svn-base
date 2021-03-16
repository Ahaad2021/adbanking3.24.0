<?php

/**
 * Description de la classe Epargne
 *
 * @author danilo
 */
require_once 'ad_ma/app/models/BaseModel.php';
require_once 'ad_ma/app/models/Agence.php';
require_once 'ad_ma/app/models/Traduction.php';
require_once 'ad_ma/app/models/Parametrage.php';
require_once "lib/misc/divers.php";

class Rapport extends BaseModel {

    /** Properties */
    private $_id_produit;
    private $_info_cpte;

    public function __construct(&$dbc, $id_agence = NULL) {
        parent::__construct($dbc, $id_agence);
    }

    public function __destruct() {
        parent::__destruct();
    }


    public function xml_epargne($DATA, $list_criteres, $export_csv = false) {
        //Génération de code XML pour les rapports sur les mouvements de ccmptes d'épargne
        //DATA contient la liste des mouvements de comptes sélectionés suivant les critères
        //la liste des critères est un tableau associatif : champs=>valeur

        $agence = new Agence($this->getDbConn(), $this->getIdAgence());
        $parametrage = new Parametrage($this->getDbConn(), $this->getIdAgence());
        $epargne = new Epargne($this->getDbConn(), $this->getIdAgence());
        basculer_langue_rpt();


        setMonnaieCourante($DATA["devise"]);
        global $adsys, $global_id_profil, $global_id_client;

        $document = create_xml_doc("compte_epargne", "compte_epargne.dtd");

        //définition de la racine
        $root = $document->root();

        //En-tête généraliste
        gen_header($root, 'EPA-EXT');
        $AGC = $agence->getAgenceDatas($this->getIdAgence());
        $prodPS = $AGC["id_prod_cpte_parts_sociales"];
        //control table column & header in xslt
        if($DATA["id_produit"] == $prodPS ){
            $root->new_child("isset_ps", $prodPS);
        }

        //En-tête contextuel
        $header_contextuel = $root->new_child("header_contextuel", "");
        //control csv
        if($DATA["id_produit"] == $prodPS ){
            $header_contextuel->new_child("isset_ps_csv", $prodPS);
        }
        gen_criteres_recherche($header_contextuel, $list_criteres);

        $infos_synthetiques = $header_contextuel->new_child("infos_synthetiques", "");
        //Contôle sur l'affichage des soldes
        $access_solde = $parametrage->getProfilAccesSolde($global_id_profil, $DATA['id_produit']);
        $access_solde_vip = $parametrage->get_profil_acces_solde_vip($global_id_profil, $global_id_client);
        if($parametrage->manage_display_solde_access($access_solde, $access_solde_vip))
            $infos_synthetiques->new_child("solde", afficheMontant($DATA["solde"], true));
        $infos_synthetiques->new_child("mnt_bloq", afficheMontant($DATA["mnt_bloq"], true));
        $infos_synthetiques->new_child("date_ouverture", localiser_date_rpt($DATA["date_ouverture"]));
        $infos_synthetiques->new_child("produit", $DATA["produit"]);
        $infos_synthetiques->new_child("solde_min", $DATA["taux_int"] > 0 ? afficheMontant($DATA["solde_min"], true) : _("Compte non rémunéré"));
        $infos_synthetiques->new_child("mnt_min", afficheMontant($DATA["mnt_min"], true));
        if($parametrage->manage_display_solde_access($access_solde, $access_solde_vip))
            $infos_synthetiques->new_child("solde_disp", afficheMontant($DATA["solde_disp"], true));
        $infos_synthetiques->new_child("taux_int", $DATA["taux_int"] == 0 ? _("Non rémunéré") : affichePourcentage($DATA["taux_int"], 2, true));
//xml pour les champs PS
        if($DATA["id_produit"] == $prodPS ){//info a afficher dans le pdf/csv pour ps
            $infos_synthetiques->new_child("ps_souscrites", $DATA["souscrites"]);
            $infos_synthetiques->new_child("ps_lib", $DATA["lib"]);

        }

        $infos_synthetiques->new_child("devise", $DATA["devise"]);
        if ($DATA ["id_produit"] == $prodPS) {
            //body pour le cas PS
            if (is_array ( $DATA ["InfoMvts"] )) {
                foreach ( $DATA ["InfoMvts"] as $value ) {
                    $mouvement = $root->new_child ( "mouvement", "" );
                    $tmp_dte1 = pg2phpDatebis ( $value ["date"] );
                    // On ne doit localiser que la partie date et laisser la partie :hhmm
                    $tmp_dte2 = localiser_date_rpt ( $tmp_dte1 [1] . "/" . $tmp_dte1 [0] . "/" . $tmp_dte1 [2] ) . " " . $tmp_dte1 [3] . ":" . $tmp_dte1 [4];
                    $mouvement->new_child ( "date_mouv", $tmp_dte2 );
                    $mouvement->new_child ( "num_trans", $value ["id_his"] );
                    // FIXME il y a actuellement un pb avec le support XML pour l'encodage des caractères spéciaux
                    $libel_ecriture = new Traduction($this->getDbConn(), $this->getIdAgence());
                    $libel_ecriture->setTraduction($value ["libel_ecriture"] );
                    $libel_ecriture = $libel_ecriture->traduire();
                    $operation = ereg_replace ( "é|è|ê", "e", $libel_ecriture );
                    $operation = ereg_replace ( "ô", "o", $operation );
                    $operation = ereg_replace ( "à", "a", $operation );
                    $operation = ereg_replace ( "ù", "u", $operation );

                    $mouvement->new_child ( "libel_ope", $operation );
                    if ($value ["sens"] == "d")
                        $mouvement->new_child ( "mnt_retrait", afficheMontant ( $value ["montant"], false, $export_csv ) );
                    else if ($value ["sens"] == "c")
                        $mouvement->new_child ( "mnt_depot", afficheMontant ( $value ["montant"], false, $export_csv ) );
                    $mouvement->new_child ( "nbre_jour_inactivite", $value ["nbre_jours_inactivite"] );
                    if ($parametrage->manage_display_solde_access ( $access_solde, $access_solde_vip ))
                        $mouvement->new_child ( "solde", afficheMontant ( $value ["solde"], false, $export_csv ) );

                    // gestion de nombre ps mouvementer pour le cas de produit ps en se basant sur les fonctions qui concerne
                    $fonction_ps = array (
                        28,
                        20,
                        23
                    );
                    if (in_array ( $value ["type_fonction"], $fonction_ps )) {
                        if (isset ( $value ["infos"] )) {
                            $mouvement->new_child ( "nbre_ps_mouvementer", $value ["infos"] );
                        }
                    }


                }
            }
        } else {

            // body pour le cas autre compte
            if (is_array ( $DATA ["InfoMvts"] )) {
                foreach ( $DATA ["InfoMvts"] as $value ) {
                    $mouvement = $root->new_child ( "mouvement", "" );
                    $tmp_dte1 = pg2phpDatebis ( $value ["date"] );
                    // On ne doit localiser que la partie date et laisser la partie :hhmm
                    $tmp_dte2 = localiser_date_rpt ( $tmp_dte1 [1] . "/" . $tmp_dte1 [0] . "/" . $tmp_dte1 [2] ) . " " . $tmp_dte1 [3] . ":" . $tmp_dte1 [4];
                    $mouvement->new_child ( "date_mouv", $tmp_dte2 );
                    $mouvement->new_child ( "num_trans", $value ["id_his"] );
                    // FIXME il y a actuellement un pb avec le support XML pour l'encodage des caractères spéciaux
                    $libel_ecriture = new Traduction($this->getDbConn(), $this->getIdAgence());
                    $libel_ecriture->setTraduction($value ["libel_ecriture"] );
                    $libel_ecriture = $libel_ecriture->traduire();
                    $operation = ereg_replace ( "é|è|ê", "e", $libel_ecriture );
                    $operation = ereg_replace ( "ô", "o", $operation );
                    $operation = ereg_replace ( "à", "a", $operation );
                    $operation = ereg_replace ( "ù", "u", $operation );

                    if ($value["type_fonction"] == '76' && $value["type_operation"] == '120') {

                        if (isset($value["info_ecriture"])) {
                            $numcpts = explode('|', $value["info_ecriture"]);

                            if (count($numcpts) == 2) {

                                $operation .= ":\n";
                                $operation .= "Compte source: " . $numcpts[0];
                                $operation .= "\nCompte destination: " . $numcpts[1];
                            }
                        }
                    }

                    // Vérifier liste opération à modifier.
                    if (in_array($value['type_operation'], $adsys["adsys_operation_cheque_infos"])) {
                        $operation = $epargne->getChequeno($value['id_his'], $operation, $value['info_ecriture']);
                    }

                    $mouvement->new_child ( "libel_ope", $operation );
                    if ($value ["sens"] == "d")
                        $mouvement->new_child ( "mnt_retrait", afficheMontant ( $value ["montant"], false, $export_csv ) );
                    else if ($value ["sens"] == "c")
                        $mouvement->new_child ( "mnt_depot", afficheMontant ( $value ["montant"], false, $export_csv ) );
                    $mouvement->new_child ( "nbre_jour_inactivite", $value ["nbre_jours_inactivite"] );
                    if ($parametrage->manage_display_solde_access ( $access_solde, $access_solde_vip ))
                        $mouvement->new_child ( "solde", afficheMontant ( $value ["solde"], false, $export_csv ) );

                }
            }
        }
        reset_langue();

        return $document->dump_mem(true);

    }

}
