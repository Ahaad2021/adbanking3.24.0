<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="bilan_bnr">
	    <xsl:apply-templates select="header"/>
			<xsl:apply-templates select="compartiment"/>
		</xsl:template>

<xsl:include href="lib.xslt"/>
<xsl:include href="header.xslt"/>

<xsl:template match="compartiment">;
 <xsl:choose>
		<xsl:when test="@numero = '1'" >
				<xsl:value-of select="entete/entete_1"/>;<xsl:value-of select="entete/entete_2"/>; <xsl:value-of select="entete/entete_3"/>;<xsl:value-of select="entete/entete_4"/>; <xsl:value-of select="entete/entete_5"/>;
		</xsl:when>
		<xsl:otherwise>
				<xsl:value-of select="entete/entete_1"/>;<xsl:value-of select="entete/entete_2"/>; <xsl:value-of select="entete/entete_3"/>;
	</xsl:otherwise>
 </xsl:choose>
			<xsl:apply-templates select="poste"/>
</xsl:template>
<xsl:template match="poste">
<xsl:choose>
	<xsl:when test="../@numero = '1'" >
     <xsl:value-of select="code"/>;<xsl:value-of select="libel"/>;<xsl:value-of select="solde"/>;<xsl:value-of select="amort_actif"/>;<xsl:value-of select="net_actif"/>;
		</xsl:when>
		<xsl:otherwise>
     <xsl:value-of select="code"/>;<xsl:value-of select="libel"/>;<xsl:value-of select="solde"/>;
	</xsl:otherwise>
 </xsl:choose>

	</xsl:template>
</xsl:stylesheet>
