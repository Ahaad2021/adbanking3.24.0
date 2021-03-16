<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template match="prevision_liquidite">

		<xsl:apply-templates select="header"/>
		<xsl:apply-templates select="body"/>
		
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="body">
	<xsl:apply-templates select="credit"/>
	<xsl:apply-templates select="epargne"/>
</xsl:template>


<xsl:template name="debut_table">
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/j,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/s1,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/s2,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/s3,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/m1,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/m2,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/m3,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/m6,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/m9,';','')"/><xsl:value-of select="';'"/>
	<xsl:value-of select="translate(/prevision_liquidite/body/dates/previsions/m12,';','')"/>
</xsl:template>

<xsl:template match="credit">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Crédit'"/></xsl:call-template>&#160;
	
	<xsl:apply-templates select="cap_attendu/previsions"><xsl:with-param name="titre" select="'Capital attendu'"/></xsl:apply-templates>&#160;
	<xsl:apply-templates select="int_attendu/previsions"><xsl:with-param name="titre" select="'Intérêts attendus'"/></xsl:apply-templates>&#160;
        
</xsl:template>

<xsl:template match="epargne">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Epargne'"/></xsl:call-template>&#160;
	<xsl:apply-templates select="ep_nantie/previsions"><xsl:with-param name="titre" select="'Epargne nantie'"/></xsl:apply-templates>&#160;
	<xsl:apply-templates select="ep_terme/previsions"><xsl:with-param name="titre" select="'Epargne à terme'"/></xsl:apply-templates>&#160;
	<xsl:apply-templates select="ep_libre/previsions"><xsl:with-param name="titre" select="'Epargne libre'"/></xsl:apply-templates>&#160;
        
</xsl:template>

<xsl:template match="previsions">
	<xsl:param name="titre"/><xsl:value-of select="translate($titre,';','')"/>;
      ;Aujourd'hui;Semaine+1;Semaine+2;Semaine+3;Mois+1;Mois+2;Mois+3;Mois+6;Mois+9;Mois+12;
      ;<xsl:value-of select="translate(j,';','')"/><xsl:value-of select="';'"/><xsl:value-of select="translate(s1,';','')"/><xsl:value-of select="';'"/><xsl:value-of select="translate(s2,';','')"/><xsl:value-of select="';'"/><xsl:value-of select="translate(s3,';','')"/><xsl:value-of select="';'"/><xsl:value-of select="translate(m1,';','')"/><xsl:value-of select="';'"/> <xsl:value-of select="translate(m2,';','')"/><xsl:value-of select="';'"/> <xsl:value-of select="translate(m3,';','')"/><xsl:value-of select="';'"/> <xsl:value-of select="translate(m6,';','')"/><xsl:value-of select="';'"/> <xsl:value-of select="translate(m9,';','')"/><xsl:value-of select="';'"/> <xsl:value-of select="translate(m12,';','')"/><xsl:value-of select="';'"/>
;
</xsl:template>

</xsl:stylesheet>
