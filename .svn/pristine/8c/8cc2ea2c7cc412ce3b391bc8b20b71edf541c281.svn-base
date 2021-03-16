<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="repartition_credit"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="repartition_credit">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>

        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
          <fo:table-column column-width="proportional-column-width(3)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell>
                <fo:block/>
              </fo:table-cell>
              <fo:table-cell border-width="0.1mm" border-style="solid" number-columns-spanned="2">
                <fo:block text-align="center">Crédits</fo:block>
              </fo:table-cell>
              <fo:table-cell border-width="0.1mm" border-style="solid" number-columns-spanned="2">
                <fo:block text-align="center">Portefeuille</fo:block>
              </fo:table-cell>
              <fo:table-cell border-width="0.1mm" border-style="solid" number-columns-spanned="2">
                <fo:block text-align="center">Portefeuille en retard</fo:block>
              </fo:table-cell>
            </fo:table-row>
            <fo:table-row font-weight="bold">
              <fo:table-cell>
                <fo:block/>
              </fo:table-cell>
              <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="center">Nombre</fo:block>
              </fo:table-cell>
              <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="center">%</fo:block>
              </fo:table-cell>
              <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="center">Montant</fo:block>
              </fo:table-cell>
              <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="center">%</fo:block>
              </fo:table-cell>
              <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="center">Montant</fo:block>
              </fo:table-cell>
              <fo:table-cell border-width="0.05mm" border-style="solid">
                <fo:block text-align="center">%</fo:block>
              </fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="tranche"/>
            <xsl:apply-templates select="total"/>
          </fo:table-body>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
    <xsl:apply-templates select="infos_synthetiques"/>
    <xsl:call-template name="titre_niv1">
    	<xsl:with-param name="titre" select="concat('Concentration par ', concentration_par)"/>
    </xsl:call-template>
  </xsl:template>
  <xsl:template match="infos_synthetiques">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Informations synthétiques'"/>
    </xsl:call-template>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Critère: <xsl:value-of select="critere"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
  <xsl:template match="tranche">
    <fo:table-row>
      <fo:table-cell border-width="0.05mm" border-style="dashed">
        <fo:block>
          <xsl:value-of select="lib_tranche"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="dashed">
        <fo:block text-align="right">
          <xsl:value-of select="nbre"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="dashed">
        <fo:block text-align="right">
          <xsl:value-of select="nbre_prc"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="dashed">
        <fo:block text-align="right">
          <xsl:value-of select="mnt"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="dashed">
        <fo:block text-align="right">
          <xsl:value-of select="mnt_prc"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="dashed">
        <fo:block text-align="right">
          <xsl:value-of select="retard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="dashed">
        <fo:block text-align="right">
          <xsl:value-of select="retard_prc"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total">
    <fo:table-row font-weight="bold">
      <fo:table-cell>
        <fo:block>Total</fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="nbre"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="mnt"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="right">
          <xsl:value-of select="retard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block/>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
