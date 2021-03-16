<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="cptes_epargne_cloture">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="header_contextuel"/>
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails'"/></xsl:call-template>;

Numéro compte;N°Client;Nom client;Solde clôture;solde clôture C/V(<xsl:value-of select="translate(total/devise,';','')"/> );date clôture;Raison;Produit;
<xsl:apply-templates select="ligne_cpte"/>;
Information globale :;
<xsl:apply-templates select="total"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="ligne_cpte">
	<xsl:value-of select="translate(num_cpte,';','')"/>;<xsl:value-of select="translate(num_client,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(solde_clot,';','')"/>;<xsl:value-of select="translate(solde_clot_cv,';','')"/>;<xsl:value-of select="translate(date_clot,';','')"/>;<xsl:value-of select="translate(raison_clot,';','')"/>;<xsl:value-of select="translate(produit,';','')"/>;
</xsl:template>
<xsl:template match="total">
	Nombre total;<xsl:value-of select="translate(total_nombre,';','')"/>;
	Solde Total à la clôture :;<xsl:value-of select="translate(total_montant,';','')"/>(<xsl:value-of select="translate(devise,';','')"/> );
</xsl:template>

</xsl:stylesheet>
