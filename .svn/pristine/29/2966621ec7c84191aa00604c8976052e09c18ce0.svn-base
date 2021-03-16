<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="etat_general_comptes_clients">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/> 
		Numéro client;Numéro compte;Nom client;Solde
		<xsl:apply-templates select="ligne"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>
<xsl:template match="header_contextuel">
		<xsl:apply-templates select="criteres_recherche"/>
		<xsl:apply-templates select="infos_synthetiques"/>
</xsl:template>
<xsl:template match="infos_synthetiques">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>;
Nombre total de comptes ;<xsl:value-of select="nbr_tot"/>;
Solde total comptes ;<xsl:value-of select="solde_tot"/>;
</xsl:template>
<xsl:template match="ligne">
		<xsl:apply-templates select="client"/>
</xsl:template>

<xsl:template match="client">
	<xsl:value-of select="translate(id,';','')"/>;<xsl:value-of select="translate(num_cpte,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>;
</xsl:template>

</xsl:stylesheet>
