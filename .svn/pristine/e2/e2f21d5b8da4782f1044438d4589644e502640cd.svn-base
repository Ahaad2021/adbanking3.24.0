<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="informations_synthetiques">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Summary information'"/>
        </xsl:call-template>
        <xsl:apply-templates select="critere"/>
    </xsl:template>
    <xsl:template match="research_criteria">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Research criteria'"/>
        </xsl:call-template>
        <xsl:apply-templates select="critere"/>
    </xsl:template>
    <xsl:template match="critere">
        <fo:list-block>
            <fo:list-item>
                <fo:list-item-label>
                    <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                    <fo:block><fo:inline font-family="ZapfDingbats">➥</fo:inline><xsl:value-of select="champs"/>: <xsl:value-of select="valeur"/></fo:block>
                </fo:list-item-body>
            </fo:list-item>
        </fo:list-block>
    </xsl:template>
</xsl:stylesheet>
