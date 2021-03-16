<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="echeances_CAT"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="echeances_CAT">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:call-template name="titre_niv1">
          <xsl:with-param name="titre" select="'Prévisions'"/>
        </xsl:call-template>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
          <xsl:apply-templates select="table_header"/>
          <fo:table-body>
            <xsl:apply-templates select="ligne"/>
          </fo:table-body>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
    <xsl:apply-templates select="infos_synthetiques"/>
  </xsl:template>
  <xsl:template match="table_header">
    <fo:table-column column-width="4cm"/>
    <xsl:for-each select="colonne">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
    </xsl:for-each>
    <fo:table-header border-separation.block-progression-direction="10pt">
      <fo:table-row font-weight="bold">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center"/>
        </fo:table-cell>
        <xsl:for-each select="colonne">
          <fo:table-cell number-columns-spanned="2">
            <fo:block text-align="center">
              <xsl:value-of select="@libel"/>
            </fo:block>
          </fo:table-cell>
        </xsl:for-each>
      </fo:table-row>
      <fo:table-row font-weight="bold">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block>Type de compte</fo:block>
        </fo:table-cell>
        <xsl:for-each select="colonne">
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Nbre</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Montant</fo:block>
          </fo:table-cell>
        </xsl:for-each>
      </fo:table-row>
    </fo:table-header>
  </xsl:template>
  <xsl:template match="ligne">
    <fo:table-row border-left-width="0.1pt" border-left-style="solid" border-left-color="black">
      <xsl:choose>
        <xsl:when test="@type_compte = 'Total'">
          <fo:table-cell padding-before="10pt" border-top-width="0.1pt" border-top-style="solid" border-top-color="black">
            <fo:block text-align="center">
              <xsl:value-of select="@type_compte"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="@type_compte"/>
            </fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="cellule"/>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="cellule">
    <xsl:choose>
      <xsl:when test="../@type_compte = 'Total'">
        <fo:table-cell padding-before="10pt" border-top-width="0.1pt" border-top-style="solid" border-top-color="black">
          <fo:block text-align="center">
            <xsl:value-of select="nombre"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell padding-before="10pt" border-top-width="0.1pt" border-top-style="solid" border-top-color="black">
          <fo:block text-align="center">
            <xsl:value-of select="montant"/>
          </fo:block>
        </fo:table-cell>
      </xsl:when>
      <xsl:otherwise>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="nombre"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="montant"/>
          </fo:block>
        </fo:table-cell>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>
