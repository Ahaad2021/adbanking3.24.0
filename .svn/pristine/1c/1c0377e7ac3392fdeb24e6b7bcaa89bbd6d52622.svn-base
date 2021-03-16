<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="comptes_inactifs"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="comptes_inactifs">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="groupe_comptes"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="ligne_compte">
    <fo:table-body>
      <fo:table-row>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block>
            <xsl:value-of select="num_compte"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="num_client"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block>
            <xsl:value-of select="nom_client"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_compte"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="cv"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="date_derniere_operation"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="nbre_jours_inactifs"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </fo:table-body>
  </xsl:template>

  <xsl:template match="total_general">
    <fo:table-body>
      <fo:table-row font-weight="bold" font-style="italic" border-collapse="separate" border-separation.block-progression-direction="25pt">
        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="2">
          <fo:block>Total général</fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center"><xsl:value-of select="total_nombre"/> comptes</fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="2">
          <fo:block text-align="right">
            <xsl:value-of select="total_montant"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </fo:table-body>
  </xsl:template>

  <xsl:template match="sous_total">
    <fo:table-body>
      <fo:table-row font-weight="bold" font-style="italic" border-collapse="separate" border-separation.block-progression-direction="25pt">
        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="2">
          <fo:block>Sous total</fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center"><xsl:value-of select="sous_tot_compte"/> comptes</fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="2">
          <fo:block text-align="right">
            <xsl:value-of select="sous_tot_solde"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </fo:table-body>
  </xsl:template>

  <xsl:template match="groupe_comptes">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="lib_prod_ep"/>
      </xsl:with-param>
    </xsl:call-template>
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block>Numéro compte</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Numéro client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left">Nom client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">Solde</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">Contre Valeur</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Date dernière opération</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Nombre jours inactif</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <xsl:apply-templates select="ligne_compte"/>
      <xsl:apply-templates select="sous_total"/>
      <xsl:apply-templates select="total_general"/>
    </fo:table>
  </xsl:template>

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
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/><xsl:value-of select="etat_compte_definition"/> :<xsl:value-of select="total_general_cptes"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Comptes actifs: <xsl:value-of select="comptes_existant"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre total de comptes: <xsl:value-of select="total_comptes"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Pourcentage par rapport aux comptes actifs: <xsl:value-of select="total_prc_comptes"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Pourcentage par rapport aux nombre total de comptes: <xsl:value-of select="total_nbre_comptes"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
</xsl:stylesheet>
