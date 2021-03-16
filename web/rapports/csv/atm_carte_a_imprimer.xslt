<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>
    <xsl:template match="atm_carte_a_imprimer">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="carte_imprimer"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="carte_imprimer">
        No client;Compte;Nom;Date commande;
        <xsl:apply-templates select="details"/>;
    </xsl:template>

    <xsl:template match="details">
        <xsl:value-of select="translate(no_client,';','')"/>;<xsl:value-of select="translate(compte,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(date_demande,';','')"/>;
    </xsl:template>


    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>


</xsl:stylesheet>
