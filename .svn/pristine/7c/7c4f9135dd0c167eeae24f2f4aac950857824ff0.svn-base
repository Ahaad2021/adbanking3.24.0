<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="concentration_epargne"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="concentration_epargne">
    <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="totaux_devises"/>
        <xsl:apply-templates select="niveau1"/>
        <xsl:if test="enreg_agence/is_siege='true'">
          <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Liste des agences consolidées'"/>
          </xsl:call-template>
          <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-header>
              <fo:table-row font-weight="bold">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center">Identifiant agence </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center"> Libellé agence  </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                  <fo:block text-align="center"> Date dernier mouvement </fo:block>
                </fo:table-cell>
              </fo:table-row>
            </fo:table-header>
            <fo:table-body>
              <xsl:apply-templates select="enreg_agence"/>
            </fo:table-body>
          </fo:table>
        </xsl:if>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="niveau1">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="lib_niveau1"/>
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
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-header>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray" number-rows-spanned="2">
            <fo:block text-align="center">Libellé</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="4">
            <fo:block text-align="center">Nombre</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="4">
            <fo:block text-align="center">Montant</fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Nbre de comptes</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Nbre de clients</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">% nbre cptes</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">% nbre clients</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Solde Cptes</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Solde clients</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">% solde cptes</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">% solde clients</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="niveau2"/>
        <xsl:apply-templates select="sous_total"/>
        <xsl:apply-templates select="total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Informations synthétiques'"/>
    </xsl:call-template>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Critères : <xsl:value-of select="critere"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre total de comptes : <xsl:value-of select="nb1"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre total de clients : <xsl:value-of select="nbc1"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Solde total comptes: <xsl:value-of select="nb2"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Solde total clients: <xsl:value-of select="nbc2"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
  <xsl:template match="totaux_devises">
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Montant total (comptes) en devise: <xsl:value-of select="@nb"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Montant total (clients) en devise: <xsl:value-of select="@nbc"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>
  <xsl:template match="niveau2">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="lib_niveau2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block text-align="center">
          <xsl:value-of select="nb_compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block text-align="center">
          <xsl:value-of select="nb_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nb_prc"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nb_prc_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block text-align="right">
          <xsl:value-of select="solde_compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block text-align="center">
          <xsl:value-of select="solde_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="solde_prc"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="solde_prc_client"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="sous_total">
    <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block font-weight="bold" text-align="center"> Sous Total</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="nb_tot"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="nb_tot_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="nb_prc_tot"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="nb_prc_tot_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="mnt_tot"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="mnt_tot_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="mnt_prc_tot"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="mnt_prc_tot_client"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total">
    <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.2pt solid black">
        <fo:block font-weight="bold" text-align="center"> Total général</fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt" padding-right="3pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_nb_compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt" padding-right="3pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_nb_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="tot_nb_prc"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="tot_nb_prc_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt" padding-right="3pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_solde_compte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt" padding-right="3pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_solde_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="tot_solde_prc"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="tot_solde_prc_client"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="enreg_agence">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_ag"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="libel_ag"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_max"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
