<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:include href="lib.xslt"/>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="concentration_client"/>
    </fo:root>
  </xsl:template>
  <xsl:template match="concentration_client">
    <fo:page-sequence master-reference="main" font-size="9pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="monocritere"/>
        <xsl:apply-templates select="tableau"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="tableau">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="liblocal"/>
    </xsl:call-template>
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <xsl:for-each select="libcolonne">
        <fo:table-column column-width="proportional-column-width(1)"/>
      </xsl:for-each>
      <fo:table-header>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block font-weight="bold" text-align="center">Designation</fo:block>
          </fo:table-cell>
          <xsl:for-each select="libcolonne">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block font-weight="bold" text-align="center">
                <xsl:value-of select="."/>
              </fo:block>
            </fo:table-cell>
          </xsl:for-each>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="ligne"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="ligne">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="libligne"/>
        </fo:block>
      </fo:table-cell>
      <xsl:for-each select="nbreparcellule">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="."/>
          </fo:block>
        </fo:table-cell>
      </xsl:for-each>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>
  <xsl:template match="monocritere">
    <fo:table width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="libelle"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="nbre"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
</xsl:stylesheet>
