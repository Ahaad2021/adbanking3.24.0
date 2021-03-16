<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="risque_credit_activite">
	<xsl:apply-templates select="header"/>
	<xsl:apply-templates select="header_contextuel"/>
	<xsl:apply-templates select="globals"/>
	<xsl:apply-templates select="details"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="research_criteria.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="research_criteria"/>
</xsl:template>

<xsl:template match="globals">
;;;;;;"Amounts in <xsl:value-of select="translate(devise,';','')"/>"
</xsl:template>

<xsl:template match="details">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Detailed of Risks Situation by Sector'"/></xsl:call-template>;
;
Line Code;Branch of Activity;Total amount of loans by each  Sector or Branch of activity;Number of Individuals Debtors;Number of debtors legal entities and groups;Number of Loans Benef. in Legal Entities or Groups;Total number of loans beneficiaries;
<xsl:apply-templates select="activ"/>
<xsl:apply-templates select="total"/>
</xsl:template>

<xsl:template match="activ">
	<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(libel_act,';','')"/>;<xsl:value-of select="translate(mnt_cred,';','')"/>;<xsl:value-of select="translate(nbr_ind_deb,';','')"/>;<xsl:value-of select="translate(nbr_grp_deb,';','')"/>;<xsl:value-of select="translate(nbr_grp_benef_pret,';','')"/>;<xsl:value-of select="translate(nbr_benef_act,';','')"/>;
</xsl:template>

<xsl:template match="total">
;TOTAL;<xsl:value-of select="translate(tot_mnt_cred,';','')"/>;<xsl:value-of select="translate(tot_ind_deb,';','')"/>;<xsl:value-of select="translate(tot_grp_deb,';','')"/>;<xsl:value-of select="translate(tot_grp_benef_pret,';','')"/>;<xsl:value-of select="translate(tot__benef_pret,';','')"/>;
</xsl:template>

</xsl:stylesheet>
