<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:output method="text"/>
	
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="DAT_echeance">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="header_contextuel"/>
		<xsl:apply-templates select="ligne_DAT"/>
		<xsl:apply-templates select="total_general"/>	
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="ligne_DAT">
 Numéro compte;Nom client;Numéro client;Solde compte;Solde contre valeur;Date écheance;Taux intérets;Déja prolongé?;Décision;
 <xsl:apply-templates select="groupe"/>
 <xsl:apply-templates select="ligne"/>
 <xsl:apply-templates select="sous_total"/>
</xsl:template>

<xsl:template match="groupe">
DAT arrivant à échéance : <xsl:value-of select="translate(echeance,';','')"/>;
</xsl:template>

<xsl:template match="ligne">
<xsl:value-of select="num_compte"/>;<xsl:value-of select="nom_client"/>;<xsl:value-of select="num_client"/>;<xsl:value-of select="translate(solde_compte,';','')"/>;<xsl:value-of select="translate(solde_contre_valeur,';','')"/>;<xsl:value-of select="date_echeance"/>;<xsl:value-of select="taux_interet"/>;<xsl:value-of select="proroge"/>;<xsl:value-of select="decision"/>;
</xsl:template>

<xsl:template match="sous_total">
Sous-total- : <xsl:value-of select="translate(../groupe/echeance,';','')"/>;Compte : <xsl:value-of select="translate(nombre,';','')"/>; Total : <xsl:value-of select="translate(montant_total,';','')"/>;
</xsl:template>

<xsl:template match="total_general">
Total général ;Compte : <xsl:value-of select="translate(total_nombre,';','')"/>; Total : <xsl:value-of select="translate(total_montant,';','')"/>;
</xsl:template>


</xsl:stylesheet>
