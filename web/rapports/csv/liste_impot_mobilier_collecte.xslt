<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>


<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="liste_epargne">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
		<xsl:apply-templates select="clients"/>
		<xsl:apply-templates select="total"/>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
	Critères ; <xsl:value-of select="translate(critere,';','')"/>;
	Date début ; <xsl:value-of select="translate(date_debut,';','')"/>;
	Date fin ; <xsl:value-of select="translate(date_fin,';','')"/>;
	Produit épargne ; <xsl:value-of select="translate(produit_epargne,';','')"/>;
</xsl:template>

<xsl:template match="clients">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_prod_ep"/></xsl:call-template>;
	;Date opération;N° du client;Nom client;Intérêts bruts reçus par le client;Montant impôt mobilier collecté;
  <xsl:apply-templates select="comptes"/>;
  <xsl:apply-templates select="sous_total"/>;
 </xsl:template>

<xsl:template match="comptes">
	;<xsl:value-of select="translate(date_operation,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(interet_annuel,';','')"/>;<xsl:value-of select="translate(montant_impot,';','')"/>;
</xsl:template>

<xsl:template match="sous_total">
       <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Sous Total;;;;'"/></xsl:call-template>
       <xsl:value-of select="translate(sous_total_interet_annuel,';','')"/>;<xsl:value-of select="translate(sous_total_montant_impot,';','')"/>;
</xsl:template>

<xsl:template match="total">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Total général;;;;'"/></xsl:call-template>
	<xsl:value-of select="translate(total_interet_annuel,';','')"/>;<xsl:value-of select="translate(total_montant_impot,';','')"/>;
</xsl:template>

</xsl:stylesheet>
