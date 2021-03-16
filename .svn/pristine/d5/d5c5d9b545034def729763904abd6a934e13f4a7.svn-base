<?php

/**
 * [163] Traitement des chèques reçus en compensation
 *  *
 * @package Chèques Internes
 */

require_once 'lib/html/HTML_GEN2.php';
require_once 'lib/html/FILL_HTML_GEN2.php';
require_once 'lib/html/HTML_erreur.php';
require_once 'lib/misc/VariablesGlobales.php';
require_once 'lib/dbProcedures/cheque_interne.php';
require_once 'lib/misc/divers.php';

require_once "lib/html/HTML_menu_gen.php";

/*{{{ Tcc-1 : Traitement des chèques reçus en compensation */
if ($global_nom_ecran == "Tcc-1") {

    $MyPage = new HTML_GEN2("Traitement des chèques reçus en compensation");

    $nb_cheques_certifies_enregistre = ChequeCertifie::getNbChequeCompensationCertifie(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_ENREGISTRE);
    $nb_cheques_certifies_valide = ChequeCertifie::getNbChequeCompensationCertifie(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_VALIDE);

    $nb_cheques_ordinaires_enregistre = ChequeCertifie::getNbChequeCompensationOrdinaire(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_ENREGISTRE);
    $nb_cheques_ordinaires_valide = ChequeCertifie::getNbChequeCompensationOrdinaire(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_VALIDE);

    $nb_cheques_ordinaires_mise_en_attente = ChequeCertifie::getNbChequeCompensationOrdinaire(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_MIS_EN_ATTENTE);
    $nb_cheques_ordinaires_rejete = ChequeCertifie::getNbChequeCompensationOrdinaire(ChequeCertifie::ETAT_CHEQUE_COMPENSATION_REJETE);

    $MyPage->addField("nb_cheques_certifies_enregistre", "Nombre de chèques certifiés non-traité", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_certifies_enregistre", FIELDP_DEFAULT, $nb_cheques_certifies_enregistre);
    $MyPage->setFieldProperties("nb_cheques_certifies_enregistre", FIELDP_IS_LABEL, true);

    $MyPage->addField("nb_cheques_certifies_valide", "Nombre de chèques certifiés traité", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_certifies_valide", FIELDP_DEFAULT, $nb_cheques_certifies_valide);
    $MyPage->setFieldProperties("nb_cheques_certifies_valide", FIELDP_IS_LABEL, true);

    $MyPage->addField("nb_cheques_ordinaires_enregistre", "Nombre de chèques ordinaires non-traité", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_ordinaires_enregistre", FIELDP_DEFAULT, $nb_cheques_ordinaires_enregistre);
    $MyPage->setFieldProperties("nb_cheques_ordinaires_enregistre", FIELDP_IS_LABEL, true);

    $MyPage->addField("nb_cheques_ordinaires_valide", "Nombre de chèques ordinaires traité", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_ordinaires_valide", FIELDP_DEFAULT, $nb_cheques_ordinaires_valide);
    $MyPage->setFieldProperties("nb_cheques_ordinaires_valide", FIELDP_IS_LABEL, true);

    $MyPage->addField("nb_cheques_ordinaires_mise_en_attente", "Nombre de chèques ordinaires mis en attente", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_ordinaires_mise_en_attente", FIELDP_DEFAULT, $nb_cheques_ordinaires_mise_en_attente);
    $MyPage->setFieldProperties("nb_cheques_ordinaires_mise_en_attente", FIELDP_IS_LABEL, true);

    $MyPage->addField("nb_cheques_ordinaires_rejete", "Nombre de chèques ordinaires rejeté", TYPC_TXT);
    $MyPage->setFieldProperties("nb_cheques_ordinaires_rejete", FIELDP_DEFAULT, $nb_cheques_ordinaires_rejete);
    $MyPage->setFieldProperties("nb_cheques_ordinaires_rejete", FIELDP_IS_LABEL, true);

    $MyPage->buildHTML();
    echo $MyPage->getHTML();

    $MyMenu = new HTML_menu_gen("");

    $MyMenu->addItem(_("Enregistrement des chèques"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Ecc-1", 164, "$http_prefix/images/menu_systeme.gif", "1");

    $MyMenu->addItem(_("Traitement des chèques certifiés"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pcc-1", 165, "$http_prefix/images/traitement_chq.gif", "2");

    $MyMenu->addItem(_("Traitement des chèques ordinaires"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pco-1", 166, "$http_prefix/images/traitement_chq.gif", "3");

    $MyMenu->addItem(_("Traitement des chèques ordinaires mis en attente"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Pom-1", 167, "$http_prefix/images/traitement_chq.gif", "4");

    $MyMenu->addItem(_("Retour Menu Guichet"), "$PHP_SELF?m_agc=".$_REQUEST['m_agc']."&prochain_ecran=Gen-6", 0, "$http_prefix/images/back.gif", "0");
    $MyMenu->buildHTML();

    echo $MyMenu->HTMLCode;
 
}
