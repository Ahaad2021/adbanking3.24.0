<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>


<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="liste_epargne">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
		<xsl:apply-templates select="clients"/>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations synthétiques'"/></xsl:call-template>
	Critères ; <xsl:value-of select="translate(critere,';','')"/>;
	Nombre total de comptes ; <xsl:value-of select="translate(header_tot_compte,';','')"/>;
	Solde total des comptes ; <xsl:value-of select="translate(substring(header_tot_solde,1,string-length(header_tot_solde)-3),';','')"/>;
	Devise; <xsl:value-of select="translate(substring(header_tot_solde,string-length(header_tot_solde)-3),';','')"/>;
</xsl:template>

<xsl:template match="clients">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_prod_ep"/></xsl:call-template>;
	;N° du client;Nom et Prénoms;N° de compte;solde du compte;
  <xsl:apply-templates select="comptes"/>;
  <xsl:apply-templates select="sous_total"/>;
 </xsl:template>

<xsl:template match="comptes">
	;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>
	<xsl:apply-templates select="compte_csv"/>
</xsl:template>
<xsl:template match="compte_csv">
;;;<xsl:value-of select="translate(num_compte,';','')"/>;<xsl:value-of select="translate(solde_compte,';','')"/>
</xsl:template>

<xsl:template match="sous_total">
       <xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Sous Total;;'"/></xsl:call-template>
       <xsl:value-of select="translate(nb_tot_tit,';','')"/>;<xsl:value-of select="translate(sous_tot_compte,';','')"/>;<xsl:value-of select="translate(mnt_tot,';',';')"/>;
</xsl:template>
</xsl:stylesheet>
