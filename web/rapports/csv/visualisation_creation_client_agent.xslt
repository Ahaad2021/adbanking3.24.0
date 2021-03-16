<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="creation_client">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        Id Transaction;N° client;Nom client;Statut juridique;Etat;Login createur;Date creation
        <xsl:apply-templates select="client"/>

    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>

    <xsl:template match="client">
        <xsl:value-of select="translate(id_trans,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(statut_juridique,';','')"/>;<xsl:value-of select="translate(etat,';','')"/>;<xsl:value-of select="translate(nom_agent,';','')"/>;<xsl:value-of select="translate(date_creation,';','')"/>;
    </xsl:template>

</xsl:stylesheet>
