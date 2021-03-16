<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>
<xsl:template match="comptes_inactifs">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
	 	<xsl:apply-templates select="groupe_comptes"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="groupe_comptes">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="lib_prod_ep"/></xsl:call-template>;
	Numero compte;Numéro client;Nom client;Solde;Contre valeur;Derniere modification;Nombre de jours inactifs;
	<xsl:apply-templates select="ligne_compte"/>;
	<xsl:apply-templates select="sous_total"/>;
	<xsl:apply-templates select="total_general"/>
</xsl:template>

<xsl:template match="ligne_compte">
	<xsl:value-of select="translate(num_compte,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(solde_compte,';','')"/>;<xsl:value-of select="translate(cv,';','')"/>;<xsl:value-of select="translate(date_derniere_operation,';','')"/>;<xsl:value-of select="translate(nbre_jours_inactifs,';','')"/>;
</xsl:template>

<xsl:template match="sous_total">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Sous Total;'"/></xsl:call-template>
	<xsl:value-of select="translate(sous_tot_compte,';','')"/> comptes;<xsl:value-of select="translate(sous_tot_solde,';','')"/>;
</xsl:template>

<xsl:template match="total_general">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Total général;'"/></xsl:call-template>
	<xsl:value-of select="translate(total_nombre,';','')"/> comptes;<xsl:value-of select="translate(total_montant,';','')"/>;
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>


</xsl:stylesheet>
