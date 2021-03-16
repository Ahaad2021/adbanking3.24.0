<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="liste_credits_emps">
		<xsl:apply-templates select="details_dir"/>
</xsl:template>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>

<xsl:template match="details_dir">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Employees Loans'"/></xsl:call-template>
  
No;Names;Original date;Original Amount;Terms;Balance;Overdue;Guarantees;Provision
<xsl:apply-templates select="client"/>
<xsl:apply-templates select="total"/>
</xsl:template>

<xsl:template match="client">

<xsl:value-of select="translate(index,';','')"/>; <xsl:value-of select="translate(nom,';','')"/>; <xsl:value-of select="translate(date_dem,';','')"/>;	<xsl:value-of select="translate(cre_mnt_octr,';','')"/>;	<xsl:value-of select="translate(nbre_ech,';','')"/>;	<xsl:value-of select="translate(solde_cap,';','')"/>;	<xsl:value-of select="translate(cre_retard_etat_max_jour,';','')"/>; <xsl:value-of select="translate(gar_tot,';','')"/>; 
</xsl:template>

<xsl:template match="total">
	Total ; ; ; <xsl:value-of select="translate(total_mnt_octr,';','')"/>; ;<xsl:value-of select="translate(total_solde_cap,';','')"/>; <xsl:value-of select="translate(total_retard,';','')"/>;
</xsl:template>

</xsl:stylesheet>
