<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="suivi_ligne_credit">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:call-template name="titre1">
            <xsl:with-param name="titre" select="'Détails'"/>
        </xsl:call-template>
        Num prêt;Numéro client;Nom client;Produit de crédit;Gestionnaire;Montant octroyé;Devise;Date octroi;Durée;Etat;Montant en attente déblocage;Capital restant dû;Intérêts restant dû;Intérêts payés;Frais restant dû;Frais payés;Date dernier déboursement;Date dernier remboursement;Date fin échéance;
        <xsl:apply-templates select="ligneCredit"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>

    <xsl:template match="ligneCredit">
        <xsl:apply-templates select="infosCredit"/>
        <xsl:apply-templates select="xml_total"/>
    </xsl:template>

    <xsl:template match="infosCredit">
        <xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of
            select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of
            select="translate(libel_prod,';','')"/>;<xsl:value-of
            select="translate(libel_gestionnaire,';','')"/>;<xsl:value-of
            select="translate(montant_octroye,';','')"/>;<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of
            select="translate(date_octroi,';','')"/>;<xsl:value-of select="translate(duree,';','')"/>;<xsl:value-of
            select="translate(etat,';','')"/>;<xsl:value-of select="translate(montant_dispo,';','')"/>;<xsl:value-of
            select="translate(capital_restant_du,';','')"/>;<xsl:value-of
            select="translate(interets_restant_du,';','')"/>;<xsl:value-of
            select="translate(interets_payes,';','')"/>;<xsl:value-of
            select="translate(frais_restant_du,';','')"/>;<xsl:value-of
            select="translate(frais_payes,';','')"/>;<xsl:value-of
            select="translate(date_dernier_deb,';','')"/>;<xsl:value-of
            select="translate(date_dernier_remb,';','')"/>;<xsl:value-of select="translate(date_fin_echeance,';','')"/>;
    </xsl:template>

    <xsl:template match="xml_total">
        ;;;;Total;<xsl:value-of
            select="translate(tot_mnt_octr,';','')"/>;;;;;;;;;;;;;;;
    </xsl:template>

</xsl:stylesheet>
