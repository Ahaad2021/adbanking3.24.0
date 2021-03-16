<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="ratio_liq"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="ratio_liq">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <fo:block space-after.optimum="0.2cm" space-before.optimum="0.5cm" border-bottom-width="0.5pt"/>
        <xsl:apply-templates select="compartiment"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="research_criteria"/>
  </xsl:template>
  <xsl:template match="compartiment">
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="5pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(0.5)" border-width="0.5pt" border-style="solid" border-left-color="gray"/>
      <fo:table-column column-width="proportional-column-width(4)" border-width="0.3pt" border-style="solid" border-left-color="gray"/>
      <fo:table-column column-width="proportional-column-width(1)" border-width="0.3pt" border-style="solid" border-left-color="gray"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
            <fo:block>
              <xsl:value-of select="entete/entete_1"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
            <fo:block text-align="center">
              <xsl:value-of select="entete/entete_2"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
            <fo:block text-align="center">
              <xsl:value-of select="entete/entete_3"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="poste"/>
      </fo:table-body>
    </fo:table>
    <fo:block space-after.optimum="0.2cm" space-before.optimum="0.5cm" font-weight="bold" border-bottom-width="0.5pt"> </fo:block>
  </xsl:template>
  <xsl:template match="poste">
    <xsl:if test="total = '1'">
      <fo:table-row>
        <fo:table-cell border-width="0.02mm" border-style="solid">
          <fo:block>
            <xsl:value-of select="code"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell border-width="0.02mm" border-style="solid" font-weight="bold">
          <fo:block>
            <xsl:value-of select="libel"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell border-width="0.02mm" border-style="solid" font-weight="bold">
          <fo:block text-align="right">
            <xsl:value-of select="solde"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <xsl:if test="total= '0'">
      <fo:table-row>
        <fo:table-cell border-width="0.02mm" border-style="solid">
          <fo:block>
            <xsl:value-of select="code"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell border-width="0.02mm" border-style="solid">
          <fo:block>
            <xsl:value-of select="libel"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell border-width="0.02mm" border-style="solid">
          <fo:block text-align="right">
            <xsl:value-of select="solde"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>
