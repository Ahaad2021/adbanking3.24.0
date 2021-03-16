<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="DAT_echeance"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="DAT_echeance">
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
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Numéro compte</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="left">Nom client</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Numéro client</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="right">Solde compte</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="right">Contre valeur</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Date échéance</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Taux d'intérêt (%)</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Déjà prolongé ?</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">Décision client</fo:block>
              </fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <xsl:apply-templates select="ligne_DAT"/>
          <xsl:apply-templates select="total_general"/>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="ligne_DAT">
    <fo:table-body>
      <xsl:apply-templates select="groupe"/>
    </fo:table-body>
    <fo:table-body>
      <xsl:apply-templates select="ligne"/>
    </fo:table-body>
    <fo:table-body>
      <xsl:apply-templates select="sous_total"/>
    </fo:table-body>
  </xsl:template>
  <xsl:template match="groupe">
    <fo:table-row font-style="italic" border-collapse="separate" border-separation.block-progression-direction="5pt">
      <fo:table-cell number-columns-spanned="3">
        <fo:block font-weight="bold">DAT arrivant à échéance : dans <xsl:value-of select="echeance"/> </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="ligne">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="solde_compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="solde_contre_valeur"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_echeance"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="taux_interet"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="proroge"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="decision"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="sous_total">
    <fo:table-row font-weight="bold" font-style="italic" border-collapse="separate" border-separation.block-progression-direction="25pt">
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="2">
        <fo:block text-align="center">Sous-total-<xsl:value-of select="../groupe/echeance"/></fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block text-align="center"><xsl:value-of select="nombre"/> comptes</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="2">
        <fo:block text-align="center">
          <xsl:value-of select="montant_total"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total_general">
    <fo:table-body>
      <fo:table-row font-weight="bold" font-style="italic" border-collapse="separate" border-separation.block-progression-direction="25pt">
        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="2">
          <fo:block text-align="center">Total général</fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center"><xsl:value-of select="total_nombre"/> comptes</fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="2">
          <fo:block text-align="center">
            <xsl:value-of select="total_montant"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </fo:table-body>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>
</xsl:stylesheet>
