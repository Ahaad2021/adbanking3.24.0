<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="cloture_periodique"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="cloture_periodique">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt">
          <fo:table-column column-width="3cm"/>
          <fo:table-column column-width="17cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
          <fo:table-column column-width="3cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
          <fo:table-column column-width="3cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell>
                <fo:block>Account no.</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block>Account designation</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="center">Debit balance</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="center">Credit balance</fo:block>
              </fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="compte"/>
          </fo:table-body>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Summary information'"/>
    </xsl:call-template>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Clôture : <xsl:value-of select="id_cloture"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Closing date : <xsl:value-of select="date_cloture"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Year: <xsl:value-of select="id_exo"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
  <xsl:template match="compte">
    <xsl:if test="@total = '1'">
      <fo:table-row font-weight="bold">
        <fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
          <fo:block text-align="left" space-before.optimum="0.2cm">
            <xsl:value-of select="num"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
          <fo:block text-align="left" space-before.optimum="0.2cm">
            <xsl:value-of select="libel"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
          <fo:block text-align="right" space-before.optimum="0.2cm">
            <xsl:value-of select="solde_debit"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
          <fo:block text-align="right" space-before.optimum="0.2cm">
            <xsl:value-of select="solde_credit"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <xsl:if test="@total = '0'">
      <fo:table-row>
        <fo:table-cell>
          <fo:block text-align="left" space-before.optimum="0.2cm">
            <xsl:value-of select="num"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell>
          <fo:block text-align="left" space-before.optimum="0.2cm">
            <xsl:value-of select="libel"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell>
          <fo:block text-align="right" space-before.optimum="0.2cm">
            <xsl:value-of select="solde_debit"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell>
          <fo:block text-align="right" space-before.optimum="0.2cm">
            <xsl:value-of select="solde_credit"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>
