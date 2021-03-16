<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>
	

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="concentration_epargne">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
		<xsl:apply-templates select="niveau1"/>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
	Critères ; <xsl:value-of select="translate(critere,';','')"/>;
	Nombre total ; <xsl:value-of select="translate(nb1,';','')"/>;
	Montant total ; <xsl:value-of select="translate(substring(nb2,1,string-length(nb2)-3),';','')"/>;
	Devise; <xsl:value-of select="translate(substring(nb2,string-length(nb2)-3),';','')"/>;
</xsl:template>

<xsl:template match="niveau1">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_niveau1"/></xsl:call-template>;
  <xsl:apply-templates select="niveau2"/>
 </xsl:template>

<xsl:template match="niveau2">
	;Nombre compte;Pourcentage;Solde compte;Devise;Pourcentage;
	;<xsl:value-of select="translate(nb_compte,';','')"/>;<xsl:value-of select="translate(nb_prc,';','')"/>;<xsl:value-of select="translate(solde_compte,';','')"/>;<xsl:value-of select="translate(solde_prc,';','')"/>;
</xsl:template>

</xsl:stylesheet>
