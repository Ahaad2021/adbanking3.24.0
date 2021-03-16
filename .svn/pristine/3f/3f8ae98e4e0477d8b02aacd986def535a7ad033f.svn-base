<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template name="header_body">
    <fo:table width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(18)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" number-rows-spanned="2">
            <fo:block text-align="center">
              <xsl:if test="./logo_existe='true'">
                <fo:external-graphic heigth="150px" width="50px" src="{logo_ag}"/>
              </xsl:if>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="left">
            <fo:block text-align="left"><xsl:value-of select="institution"/> (agence: <xsl:value-of select="agence"/>) <xsl:value-of select="$icone_tel" disable-output-escaping="yes"/> <xsl:value-of select="telephone"/></fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="right">
            <fo:block text-align="right"><xsl:value-of select="date"/>  <xsl:value-of select="heure"/></fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="left">
            <fo:block text-align="left">Operator: <xsl:value-of select="utilisateur"/></fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="right">
            <fo:block text-align="right">Réf: <xsl:value-of select="idrapport"/></fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:block background-color="silver" text-align="center" font-size="11pt" font-weight="bold">
      <xsl:value-of select="titre"/>
    </fo:block>
  </xsl:template>
  <xsl:template match="header">
    <fo:static-content flow-name="xsl-region-before">
      <xsl:call-template name="header_body"/>
    </fo:static-content>
  </xsl:template>
  <xsl:template match="header" mode="no_region">
    <xsl:call-template name="header_body"/>
  </xsl:template>
</xsl:stylesheet>
