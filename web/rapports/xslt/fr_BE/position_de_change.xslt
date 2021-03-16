<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="position_de_change"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="position_de_change">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
          <fo:table-column column-width="1.5cm"/>
          <fo:table-column column-width="3cm"/>
          <fo:table-column column-width="3cm"/>
          <fo:table-column column-width="3cm"/>
          <fo:table-column column-width="3cm"/>
          <fo:table-column column-width="3cm"/>
          <fo:table-column column-width="3cm"/>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell>
                <fo:block>Code</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block>Libelé</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block>Pos.Nette</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block>Taux jour</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block> C/V</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block>Var Tx</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block>Taux moyen</fo:block>
              </fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="devise"/>
          </fo:table-body>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="devise">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="code"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="libel"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="pos_net"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="taux_jour"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="cv"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="var_taux"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="taux_moyen"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
