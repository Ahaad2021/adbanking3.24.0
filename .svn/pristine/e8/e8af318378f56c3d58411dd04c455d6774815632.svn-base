<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:include href="lib.xslt"/>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="activite_agence"/>
    </fo:root>
  </xsl:template>
  <xsl:template match="activite_agence">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="infos_globales"/>
        <xsl:apply-templates select="clients"/>
        <xsl:apply-templates select="credits"/>
        <xsl:apply-templates select="epargnes"/>
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
  <xsl:template match="clients">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Evolution des membres'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">  </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Men </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Women </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Legal entities</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Total </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="date_deb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="nbre_hom_deb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="nbre_fem_deb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="nbre_pm_deb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="total_client_deb"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="date_fin"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="nbre_hom_fin"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="nbre_fem_fin"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="nbre_pm_fin"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="total_client_fin"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Accroissement </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="diff_hom"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="diff_fem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="diff_pm"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="diff_tot"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Pourcentage </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="prc_hom"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="prc_fem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="prc_pm"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="prc_tot"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="credits">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Evolution du crédit'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Month </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Number  </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Amount </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> January </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_jan"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_jan"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> February </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_fev"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_fev"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> March </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_mars"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_mars"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> April </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_av"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_av"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> May </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_mai"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_mai"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> June </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_juin"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_juin"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> July </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_jui"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_jui"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> Aout </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_aout"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_aout"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> September </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_sept"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_sept"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> October </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_oc"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_oc"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> November </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_nov"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_nov"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> December </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">
              <xsl:value-of select="cred_dec"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_dec"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center" font-weight="bold"> Total </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center" font-weight="bold">
              <xsl:value-of select="cred_tot"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="mnt_tot"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="epargnes">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Evolution de epargne'"/>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Month </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Epargne collectée  </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center"> Encours de l'épargne </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> January </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_jan"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_jan"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> February </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_fev"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_fev"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> March </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_mars"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_mars"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> April </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_av"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_av"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> May </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_mai"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_mai"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> June </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_juin"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_juin"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> July </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_jui"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_jui"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> Aout </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_aout"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_aout"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> September </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_sept"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_sept"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> October </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_oc"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_oc"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> November </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_nov"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_nov"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="left"> December </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="epargne_dec"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right">
              <xsl:value-of select="encour_dec"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center" font-weight="bold"> Total </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="epargne_tot"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="encours_tot"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
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
