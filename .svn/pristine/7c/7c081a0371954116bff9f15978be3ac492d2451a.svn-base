<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>
    <xsl:template match="engraischimiques_situation_paiement">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="list_paiement"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="list_paiement">
        <!-- <xsl:call-template name="titre1"><xsl:with-param name="titre" select="details_situation"/></xsl:call-template>; -->
        Province;Commune;Bureau/Coopec;Agriculteur;<xsl:for-each select="nbre_colonne/colonne"><xsl:value-of select="translate(text(),';','')"/>;</xsl:for-each>Montant;
        <xsl:for-each select="details_bureau">
            <xsl:value-of select="translate(province,';','')"/>;<xsl:value-of select="translate(commune,';','')"/>;<xsl:value-of select="translate(bureau,';','')"/>;<xsl:value-of select="translate(agriculteur,';','')"/>;<xsl:for-each select="detail_produit/qty_produit"><xsl:value-of select="translate(text(),';','')"/>;</xsl:for-each><xsl:value-of select="translate(total,';','')"/>;
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>


</xsl:stylesheet>
