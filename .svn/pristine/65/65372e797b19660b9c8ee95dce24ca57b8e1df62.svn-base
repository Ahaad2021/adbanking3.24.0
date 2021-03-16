<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="liste_plus_grds_emp">
	<xsl:apply-templates select="header"/>
	<xsl:apply-templates select="header_contextuel"/>
	<xsl:apply-templates select="details"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="globals">
"Fréquency";"Quarterly";;;;;;;"Amounts in <xsl:value-of select="translate(devise,';','')"/>"
</xsl:template>

<xsl:template match="details">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Detailed information'"/></xsl:call-template>;
;
No.;Names;Original Date;Original Amount;Terms;Balance;Overdue;Guarantees;Provision;
<xsl:apply-templates select="client"/>
<xsl:apply-templates select="total"/>
</xsl:template>

<xsl:template match="client">
	<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(date_pret,';','')"/>;<xsl:value-of select="translate(mnt_pret,';','')"/>;<xsl:value-of select="translate(echeances,';','')"/>;<xsl:value-of select="translate(solde,';','')"/>;<xsl:value-of select="translate(mnt_retard,';','')"/>;<xsl:value-of select="translate(garanties,';','')"/>;<xsl:value-of select="translate(mnt_prov,';','')"/>;
</xsl:template>

<xsl:template match="total">
;TOTAL;;<xsl:value-of select="translate(tot_mnt_pret,';','')"/>;;<xsl:value-of select="translate(tot_solde,';','')"/>;<xsl:value-of select="translate(tot_mnt_retard,';','')"/>;;<xsl:value-of select="translate(tot_mnt_prov,';','')"/>;
</xsl:template>

</xsl:stylesheet>
