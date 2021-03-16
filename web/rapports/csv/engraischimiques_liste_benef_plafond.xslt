<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>
    <xsl:template match="comptes_inactifs">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="saison"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="saison">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Liste des bénéficiaires'"/></xsl:call-template>;
        Numero beneficiaire;Nom Prénom;Numéro commande;Dépassement engrais (qtite);Montant dépassement engrais;Dépassement amendement (qtite);Montant dépassement amendement;Montant dépassement total;
        <xsl:apply-templates select="commande"/>;
        <xsl:apply-templates select="total_montant"/>
    </xsl:template>

    <xsl:template match="commande">
        <xsl:value-of select="translate(id_benef,';','')"/>;<xsl:value-of select="translate(nom_prenom,';','')"/>;<xsl:value-of select="translate(id_commande,';','')"/>;<xsl:value-of select="translate(nbre_engrais,';','')"/>;<xsl:value-of select="translate(total_engrais,';','')"/>;<xsl:value-of select="translate(nbre_amendement,';','')"/>;<xsl:value-of select="translate(total_amendement,';','')"/>;<xsl:value-of select="translate(total_depassement,';','')"/>;
    </xsl:template>

    <xsl:template match="total_montant">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Sous Total;'"/></xsl:call-template>
        <xsl:value-of select="translate('',';','')"/>;<xsl:value-of select="translate('',';','')"/>;<xsl:value-of select="translate('',';','')"/>;<xsl:value-of select="translate(sous_total_engrais,';','')"/>; <xsl:value-of select="translate('',';','')"/>; <xsl:value-of select="translate(sous_total_amendement,';','')"/>;<xsl:value-of select="translate(sous_total_montant,';','')"/>;
    </xsl:template>

    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>


</xsl:stylesheet>
