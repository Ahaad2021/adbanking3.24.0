<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="listecomptes"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="listecomptes">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:call-template name="titre_niv1">
          <xsl:with-param name="titre" select="'Détails'"/>
        </xsl:call-template>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(3)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Rank</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Client nbr</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Client name</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Account number</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Savings product</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Balance</fo:block>
              </fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="details"/>
            <xsl:apply-templates select="total"/>
          </fo:table-body>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>
  <xsl:template match="details">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="index"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="idclient"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="numcpt"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="libel"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
        <fo:block font-weight="bold">TOTAL</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="5">
        <fo:block text-align="right" font-weight="bold" wrap-option="no-wrap">
          <xsl:value-of select="total_solde"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
