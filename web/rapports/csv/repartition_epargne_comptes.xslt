<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>


    <xsl:include href="header.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="concentration_epargne">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="niveau1"/>
    </xsl:template>

    <xsl:template match="header_contextuel">
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
        Critères ; <xsl:value-of select="translate(critere,';','')"/>;
        Nombre total de comptes ; <xsl:value-of select="translate(nb1,';','')"/>;
        Solde total comptes ; <xsl:value-of select="translate(nb2,';','')"/>;
    </xsl:template>

    <xsl:template match="niveau1">
        ;
        <xsl:value-of select="translate(lib_niveau1,';','')"/>;
        Libellé;Nbre de comptes;% nbre cptes;Solde Cptes;% solde cptes
        <xsl:apply-templates select="niveau2"/>
        <xsl:apply-templates select="total"/>
    </xsl:template>

    <xsl:template match="niveau2">
        <xsl:value-of select="translate(lib_niveau2,';','')"/>;<xsl:value-of select="nb_compte"/>;<xsl:value-of select="nb_prc"/>;<xsl:value-of select="solde_compte"/>;<xsl:value-of select="solde_prc"/>;
    </xsl:template>

    <xsl:template match="total">
        Total général;<xsl:value-of select="tot_nb_compte"/>;<xsl:value-of select="tot_nb_prc"/>;<xsl:value-of select="tot_solde_compte"/>;<xsl:value-of select="tot_solde_prc"/>
    </xsl:template>

</xsl:stylesheet>