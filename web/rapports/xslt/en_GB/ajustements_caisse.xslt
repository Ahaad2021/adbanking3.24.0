<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="ajustements_caisse"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="ajustements_caisse">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:call-template name="titre_niv1">
          <xsl:with-param name="titre" select="'Cash adjustments'"/>
        </xsl:call-template>
        <xsl:call-template name="ajustements"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>
  <xsl:template name="ajustements">
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block font-weight="bold" text-align="center">User</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block font-weight="bold" text-align="center">Adjustment date</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block font-weight="bold" text-align="center">Excess</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block font-weight="bold" text-align="center">Missing</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block font-weight="bold" text-align="center">Total</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="ajustement"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="ajustement">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="utilisateur"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_ajustement"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="excedent"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="manquant"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="total"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>