<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>
<xsl:template match="concentration_client">
	    <xsl:apply-templates select="header"/>
		<xsl:apply-templates select="monocritere"/>;
		<xsl:apply-templates select="tableau"/>;
		<xsl:apply-templates select="ligne"/>;
</xsl:template>

<xsl:include href="lib.xslt"/>
<xsl:include href="header.xslt"/>

<xsl:template match="monocritere">
	Libelle :<xsl:value-of select="libelle"/>; Nombre:<xsl:value-of select="nbre"/>;
</xsl:template>

<xsl:template match="tableau">
titre : <xsl:value-of select="liblocal"/>;
Libelle :<xsl:for-each select="libcolonne"><xsl:value-of select="."/>;</xsl:for-each>
<xsl:apply-templates select="ligne"/>
</xsl:template>

<xsl:template match="ligne">
<xsl:value-of select="libligne"/> :
<xsl:for-each select="nbreparcellule">;<xsl:value-of select="."/>;</xsl:for-each>
</xsl:template>
</xsl:stylesheet>