<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="rapport_creation_client_agent">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        No d'ordre;Numéro de compte;Nom du client;Nom agent;Utilisateur(Validation);Date creation
        <xsl:apply-templates select="client"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>

    <xsl:template match="client">
        <xsl:value-of select="translate(ordre,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(nom_agent,';','')"/>;<xsl:value-of select="translate(login_validation,';','')"/>;<xsl:value-of select="translate(date_creation,';','')"/>;
    </xsl:template>

</xsl:stylesheet>
