<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="etat_general_comptes_clients"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
    <xsl:apply-templates select="infos_synthetiques"/>
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
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre total de comptes : <xsl:value-of select="nbr_tot"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Solde total comptes: <xsl:value-of select="solde_tot"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
  <xsl:template match="etat_general_comptes_clients">
    <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
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
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(3)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black" text-align="center">N° client</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black" text-align="center">N° Compte</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black" text-align="center">Nom</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Solde</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">N° Client</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black"> N° Compte</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Nom</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">Solde</fo:block>
              </fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="ligne"/>
          </fo:table-body>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="ligne">
    <fo:table-row>
      <xsl:apply-templates select="client"/>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="client">
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="id"/>
      </fo:block>
    </fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="num_cpte"/>
      </fo:block>
    </fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="nom"/>
      </fo:block>
    </fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="solde"/>
      </fo:block>
    </fo:table-cell>
  </xsl:template>
</xsl:stylesheet>
