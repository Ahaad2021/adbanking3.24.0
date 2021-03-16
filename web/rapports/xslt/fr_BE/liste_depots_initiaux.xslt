<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="listedepots"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="listedepots">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="details"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="details">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="ladevise"/>
    </xsl:call-template>
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">N°Client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Nom client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">N°Compte</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Date Ouverture</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Produit d'épargne</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Etat compte</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Depot initial</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Devise</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="devise"/>
        <xsl:apply-templates select="total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>
  <xsl:template match="devise">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_ouvert"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="libel_prod_ep"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="etat_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="montant"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="devise"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total">
    <fo:table-row>
      <fo:table-cell>
        <fo:block> </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block> </fo:block>
      </fo:table-cell>
      <fo:table-cell>  </fo:table-cell>
      <fo:table-cell>
        <fo:block> </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block> </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block font-weight="bold">TOTAL</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center" font-weight="bold" wrap-option="no-wrap">
          <xsl:value-of select="total_solde"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
