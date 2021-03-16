<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template name="titre1">
	<xsl:param name="titre"/>
	<xsl:value-of select="$titre"/>
</xsl:template>

<xsl:template name="echeance">
<xsl:param name="echean"/>
<xsl:value-of select="$echean"/>
</xsl:template>

<xsl:template name="titre2">
<xsl:param name="titre"/>
<xsl:value-of select="$titre"/>
</xsl:template>

</xsl:stylesheet>
