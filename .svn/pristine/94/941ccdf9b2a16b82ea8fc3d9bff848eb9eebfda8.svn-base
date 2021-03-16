<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>


<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="concentration_epargne1">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
		<xsl:apply-templates select="produit"/>
		<xsl:apply-templates select="total"/>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
	Critères ; <xsl:value-of select="translate(critere,';','')"/>;
	Nombre total ; <xsl:value-of select="translate(nb1,';','')"/>;
	Montant total ; <xsl:value-of select="translate(substring(nb2,1,string-length(nb2)-3),';','')"/>;
	Devise; <xsl:value-of select="translate(substring(nb2,string-length(nb2)-3),';','')"/>;
</xsl:template>

<xsl:template match="produit">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="libel"/></xsl:call-template>;
;Libellé;Nbre de comptes;% nbre cptes;Solde Cptes;% solde cptes;
 <xsl:apply-templates select="tranche"/>;
  <xsl:apply-templates select="sous_total"/>;
 </xsl:template>

<xsl:template match="tranche">
	;<xsl:value-of select="translate(statut_juridique,';','')"/>;<xsl:value-of select="nbre"/>;<xsl:value-of select="nbre_prc"/>;<xsl:value-of select="solde"/>;<xsl:value-of select="solde_prc"/>;
</xsl:template>

<xsl:template match="sous_total">
	;<xsl:value-of select="libel"/>;<xsl:value-of select="total_cpte"/>;<xsl:value-of select="total_cpte_prc"/>;<xsl:value-of select="total_solde"/>;<xsl:value-of select="total_solde_prc"/>;
</xsl:template>

<xsl:template match="total">
;Libellé;Nbre de comptes;% nbre cptes;Solde Cptes;% solde cptes;
;Total général	;<xsl:value-of select="cpte_total"/>;<xsl:value-of select="cpte_total_prc"/>;<xsl:value-of select="solde_total"/>;<xsl:value-of select="solde_total_prc"/>;
</xsl:template>

</xsl:stylesheet>