<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="extrait_compte">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="info"/>
        <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'EXTRAIT DE COMPTE'"/></xsl:call-template>;
        <xsl:apply-templates select="balance"/>
		<xsl:apply-templates select="extrait"/>
</xsl:template>


<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="info">
    <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations globales'"/></xsl:call-template>;
    ID Client ; <xsl:value-of select="id_client"/>;
    Nom du client  ; <xsl:value-of select="nom_client"/>;
    No compte ; <xsl:value-of select="num_cpte"/>;
</xsl:template>

<xsl:template match="balance">
    Solde reporté en date du  <xsl:value-of select="translate(eft_dern_date,';','')"/> = <xsl:value-of select="translate(eft_dern_solde,';','')"/>;
</xsl:template>

<xsl:template match="extrait">
    Date de valeur;N° transaction;Opération;Communication;Dépôt;Retrait;Solde;
    <xsl:apply-templates select="transaction"/>
    <xsl:apply-templates select="total"/>
</xsl:template>

<xsl:template match="transaction">
    <xsl:value-of select="translate(date_valeur,';','')"/>;<xsl:value-of select="translate(n_ref,';','')"/>;	<xsl:value-of select="translate(information,';','')"/>;	<xsl:value-of select="translate(communication,';','')"/>;	<xsl:value-of select="translate(depot,';','')"/>;	<xsl:value-of select="translate(retrait,';','')"/>;	<xsl:value-of select="translate(solde,';','')"/>;
</xsl:template>

<xsl:template match="total">
    ;;;	Total mouvements;	<xsl:value-of select="translate(total_depot,';','')"/>;	<xsl:value-of select="translate(total_retrait,';','')"/>; ;
</xsl:template>

</xsl:stylesheet>
