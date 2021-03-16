<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="credit_reech">
<xsl:apply-templates select="header"/>
<xsl:apply-templates select="header_contextuel"/>;
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations globales'"/>
</xsl:call-template>
<xsl:apply-templates select="globalInfos"/>;
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Détails'"/>
</xsl:call-template>
Dossier;Client;Nom;Montant Octroyé;Capital attendu;Capital restant;Devise;Etat crédit;
<xsl:apply-templates select="ligneCredit"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="globalInfos">
Montant total Crédits rééchelonnés : <xsl:value-of select="translate(mnt_tot_crd_reech,';','')"/>
Encours crédits rééchelonnés : <xsl:value-of select="translate(encours_crd_reech,';','')"/>;
</xsl:template>

<xsl:template match="ligneCredit">
<xsl:apply-templates select="detailCredit"/>
</xsl:template>

<xsl:template match="detailCredit">;
	<xsl:value-of select="translate(no_dossier,';','')"/>;	<xsl:value-of select="translate(num_client,';','')"/>;	<xsl:value-of select="translate(nom_client,';','')"/>;	<xsl:value-of select="translate(mnt_octr,';','')"/>;	<xsl:value-of select="translate(cap_att,';','')"/>;	<xsl:value-of select="translate(cap_rest,';','')"/>;	<xsl:value-of select="translate(devise,';','')"/>;<xsl:value-of select="translate(lib_etat,';','')"/>
</xsl:template>

</xsl:stylesheet>
