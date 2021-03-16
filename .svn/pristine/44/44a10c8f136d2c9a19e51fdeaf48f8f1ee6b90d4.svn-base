<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">

<xsl:output method="text"/>

<xsl:template match="comptes_epargne_repris">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="header_contextuel"/>
<xsl:apply-templates select="produit"/>
</xsl:template>

  <xsl:include href="header.xslt"/>
  <xsl:include href="lib.xslt"/>
 	<xsl:template match="produit">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="libel"/></xsl:call-template>
	Numéro client;Ancien N° client;Nom client;Numero compte;Solde repris;Date reprise;
	<xsl:apply-templates select="compte_repris"/>
	</xsl:template>


  <xsl:template match="header_contextuel">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
</xsl:template>

  <xsl:template match="compte_repris">
  <xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(ancien_num_client,';','')"/>;	<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(ancien_num_cpte,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>;<xsl:value-of select="translate(date_reprise,';','')"/>;
  </xsl:template>
</xsl:stylesheet>
