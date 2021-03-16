<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="rapport_agent">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        Login agent;Nom agent;Date creation;Compte de flotte;Compte de base
        <xsl:apply-templates select="agent"/>

    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>

    <xsl:template match="agent">
        <xsl:value-of select="translate(login_agent,';','')"/>;<xsl:value-of select="translate(nom_agent,';','')"/>;<xsl:value-of select="translate(date_creation,';','')"/>;<xsl:value-of select="translate(cpte_flotte,';','')"/>;<xsl:value-of select="translate(cpte_base,';','')"/>;
    </xsl:template>

</xsl:stylesheet>
