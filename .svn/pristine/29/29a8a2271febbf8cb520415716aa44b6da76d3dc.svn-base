<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>

<xsl:template match="research_criteria">
 <xsl:apply-templates select="critere"/>
</xsl:template>
<xsl:template match="critere">
<xsl:value-of select="champs"/>;<xsl:value-of select="valeur" />
<xsl:text disable-output-escaping="yes">
</xsl:text>
</xsl:template>
</xsl:stylesheet>