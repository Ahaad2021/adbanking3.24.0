<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:include href="lib.xslt"/>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="resultat_trimestriel"/>
    </fo:root>
  </xsl:template>
  <xsl:template match="resultat_trimestriel">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="infos_globales"/>
        <xsl:call-template name="titre_niv1"/>
        <fo:table width="100%" table-layout="fixed" font-size="8pt">
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">  </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">  </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">  </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 1er trimestre </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 2ème trimestre </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 3ème trimestre</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">4ème trimestre </fo:block>
              </fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="usagers"/>
            <xsl:apply-templates select="epargnants"/>
            <xsl:apply-templates select="credits_accordes"/>
            <xsl:apply-templates select="encoursepargnes"/>
            <xsl:apply-templates select="montant_credits_accordes"/>
          </fo:table-body>
        </fo:table>
        <xsl:call-template name="titre_niv1"/>
        <fo:table width="100%" table-layout="fixed" font-size="8pt">
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-header> </fo:table-header>
          <fo:table-body>
            <fo:table-row font-weight="bold">
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> Produits d exploitation </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 1er trimestre </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 2ème trimestre </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 3ème trimestre</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">4ème trimestre </fo:block>
              </fo:table-cell>
            </fo:table-row>
            <xsl:apply-templates select="produit_exploitation"/>
            <fo:table-row font-weight="bold">
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">  Charges d exploitation </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 1er trimestre </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 2ème trimestre </fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"> 3ème trimestre</fo:block>
              </fo:table-cell>
              <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">4ème trimestre </fo:block>
              </fo:table-cell>
            </fo:table-row>
            <xsl:apply-templates select="charge_exploitation"/>
          </fo:table-body>
        </fo:table>
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
                  <fo:block text-align="center"> Date of latest transaction </fo:block>
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
  <xsl:template match="infos_globales">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Administrative informations'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block>Beginning period : </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block>
              <xsl:value-of select="debut_periode"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block>End period : </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block>
              <xsl:value-of select="fin_periode"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="usagers">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-rows-spanned="18">
        <fo:block text-align="center"> Number  </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-rows-spanned="6">
        <fo:block text-align="center"> Usagers (tous les membres et auxiliaires)  </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Women</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Mixed groupings</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">TOTAL</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="epargnants">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-rows-spanned="6">
        <fo:block text-align="center"> Epargnants (membres ayant au moins un compte épargne)  </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Women</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Mixed groupings</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">TOTAL</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="credits_accordes">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-rows-spanned="6">
        <fo:block text-align="center"> Crédits accordés (cumul du 1er Janvier précédent à la date retenue)  </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Women</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Mixed groupings</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_g_mixte_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">TOTAL</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="TOTAL_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="encoursepargnes">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-rows-spanned="12">
        <fo:block text-align="center"> Amount  </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-rows-spanned="6">
        <fo:block text-align="center"> Encours Epargne (compilation des fiches d épargne)  </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Women</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Mixed groupings</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_mixte_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_mixte_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_mixte_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_mixte_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">TOTAL</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="TOTAL_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="TOTAL_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="TOTAL_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="TOTAL_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="montant_credits_accordes">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-rows-spanned="6">
        <fo:block text-align="center"> Crédits  accordés (dans l année)  </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Women</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_homme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_homme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_homme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_homme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Groupings of men</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_femme_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_femme_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_femme_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_femme_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">Mixed groupings</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_mixte_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_mixte_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_mixte_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_g_mixte_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">TOTAL</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="TOTAL_t1"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="TOTAL_t2"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="TOTAL_t3"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="TOTAL_t4"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="charge_exploitation">
    <xsl:apply-templates select="compteCharge"/>
  </xsl:template>
  <xsl:template match="compteCharge">
    <xsl:if test="@total = '1'">
      <fo:table-row font-weight="bold">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="left" space-before.optimum="0.2cm">
            <xsl:value-of select="libel_charge"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_charge_t1"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_charge_t2"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_charge_t3"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_charge_t4"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <xsl:if test="@total = '0'">
      <fo:table-row>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="libel_charge"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_charge_t1"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_charge_t2"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_charge_t3"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_charge_t4"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
  </xsl:template>
  <xsl:template match="produit_exploitation">
    <xsl:apply-templates select="compteProduit"/>
  </xsl:template>
  <xsl:template match="compteProduit">
    <xsl:if test="@total = '1'">
      <fo:table-row font-weight="bold">
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="left" space-before.optimum="0.2cm">
            <xsl:value-of select="libel_produit"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_produit_t1"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_produit_t2"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_produit_t3"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_produit_t4"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <xsl:if test="@total = '0'">
      <fo:table-row>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="libel_produit"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_produit_t1"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_produit_t2"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_produit_t3"/>
          </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align="center" border="0.1pt solid gray">
          <fo:block text-align="right">
            <xsl:value-of select="solde_produit_t4"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>
