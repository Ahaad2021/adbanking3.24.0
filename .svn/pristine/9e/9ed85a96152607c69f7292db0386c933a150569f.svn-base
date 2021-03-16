<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>
    <xsl:template match="atm_transaction">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="transaction"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="transaction">
        Id hist;Date transaction;No client;Compte;Numéro carte;Type operation;Montant;
        <xsl:apply-templates select="details"/>;
    </xsl:template>

    <xsl:template match="details">
        <xsl:value-of select="translate(id_his,';','')"/>;<xsl:value-of select="translate(date_transaction,';','')"/>;<xsl:value-of select="translate(no_client,';','')"/>;<xsl:value-of select="translate(compte,';','')"/>;<xsl:value-of select="translate(num_carte,';','')"/>;<xsl:value-of select="translate(type_operation,';','')"/>;<xsl:value-of select="translate(montant,';','')"/>;
    </xsl:template>


    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>


</xsl:stylesheet>
