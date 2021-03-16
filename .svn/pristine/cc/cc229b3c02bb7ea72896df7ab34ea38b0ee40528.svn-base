<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>
	
<xsl:template match="liste_credits_emp_dir">
		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="epargne"/>
		<xsl:apply-templates select="details_dir"/>
		<xsl:apply-templates select="details_emp"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="total">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations globales'"/></xsl:call-template>;
<xsl:for-each select="*">
   <xsl:value-of select="translate(.,';','')"/>
   <xsl:if test="position() != last()">
    <xsl:value-of select="';'"/>
   </xsl:if>
  </xsl:for-each>
<xsl:text disable-output-escaping="yes">
</xsl:text>	
</xsl:template>

<xsl:template match="epargne">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Ratios épargne'"/></xsl:call-template>
	Ratios epargne des dirigeants ; <xsl:value-of select="ratio_epar_dir"/>;
	Ratios epargne des employes ; <xsl:value-of select="ratio_epar_emp"/>;
	Ratios epargne  employes et dirigeants ;<xsl:value-of select="ratio_epar_emp_dir"/>;
</xsl:template>

<xsl:template match="details_dir">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations détaillées dirigeants'"/></xsl:call-template>;
<xsl:apply-templates select="client"/>
</xsl:template>

<xsl:template match="details_emp">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations détaillées employés'"/></xsl:call-template>;
;
Index;Numéro client;Numéro dossier;Nom client;Encours de crédit;C/V encours de crédit;Etat crédit;Pénalités attendues;
<xsl:apply-templates select="client"/>
</xsl:template>

<xsl:template match="client">
	<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(encours_client,';','')"/>;<xsl:value-of select="translate(cv_encours_client,';','')"/>;<xsl:value-of select="translate(cre_etat,';','')"/>;<xsl:value-of select="translate(mnt_pen,';','')"/>;
</xsl:template>

</xsl:stylesheet>
