<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="detail_transactions"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="detail_transactions">
    <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="transactions"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="transactions">
    <fo:table border-collapse="collapse" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(6)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(25)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Transaction No.</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Date</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Function</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Login</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Client No.</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:table border="none" width="100%" table-layout="fixed">
              <fo:table-column column-width="proportional-column-width(5)"/>
              <fo:table-column column-width="proportional-column-width(6)"/>
              <fo:table-column column-width="proportional-column-width(3)"/>
              <fo:table-column column-width="proportional-column-width(5)"/>
              <fo:table-column column-width="proportional-column-width(3)"/>
              <fo:table-column column-width="proportional-column-width(3)"/>
              <fo:table-body>
                <fo:table-row>
                  <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">Entry No.</fo:block>
                  </fo:table-cell>
                  <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">Designation</fo:block>
                  </fo:table-cell>
                  <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">Account</fo:block>
                  </fo:table-cell>
                  <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">Client account</fo:block>
                  </fo:table-cell>
                  <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">Debit</fo:block>
                  </fo:table-cell>
                  <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">Credit menu</fo:block>
                  </fo:table-cell>
                </fo:table-row>
              </fo:table-body>
            </fo:table>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <fo:table-row>   </fo:table-row>
        <xsl:apply-templates select="his_data"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="his_data">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="left">
          <xsl:value-of select="num_trans"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="fonction"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="login"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <xsl:apply-templates select="ligne_ecritures"/>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="ligne_ecritures">
    <fo:table border="none" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(5)"/>
      <fo:table-column column-width="proportional-column-width(6)"/>
      <fo:table-column column-width="proportional-column-width(14)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left">
              <xsl:value-of select="num_ecriture"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="libel_ecriture"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <xsl:apply-templates select="ligne_mouvements"/>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="ligne_mouvements">
    <fo:table border="none" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(5)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="compte"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="compte_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="montant_debit"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="montant_credit"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
</xsl:stylesheet>
