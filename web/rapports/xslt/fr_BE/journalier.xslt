<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="journaux"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="journaux">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="journalier"/>
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
  <xsl:template match="journalier">
    <fo:block break-before="page">
      <xsl:call-template name="titre_niv0">
        <xsl:with-param name="titre" select="concat('Journal du ', @jour)"/>
      </xsl:call-template>
      <xsl:apply-templates select="adhesions"/>
      <xsl:apply-templates select="defections"/>
      <xsl:apply-templates select="ouvertures"/>
      <xsl:apply-templates select="clotures"/>
      <xsl:apply-templates select="dat_prolonges"/>
      <xsl:apply-templates select="dat_non_prolonges"/>
      <xsl:apply-templates select="dossiers_credit"/>
      <xsl:apply-templates select="dcr_approuves"/>
      <xsl:apply-templates select="dcr_rejetes"/>
      <xsl:apply-templates select="dcr_annules"/>
      <xsl:apply-templates select="dcr_debourses"/>
      <xsl:apply-templates select="dcr_repris"/>
      <xsl:apply-templates select="ps_repris"/>
      <xsl:apply-templates select="comptes_ajustes"/>
      <xsl:apply-templates select="app_caisses"/>
      <xsl:apply-templates select="delest_caisses"/>
      <xsl:apply-templates select="situation_coffre"/>
      <xsl:apply-templates select="situation_dep"/>
    </fo:block>
  </xsl:template>
  <xsl:template match="adhesions">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Adhésions du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Statut juridique</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Secteur d'activité</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Gestionnaire</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_adhesion"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_adhesion">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="stat_jur"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="sect_act"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="gestionnaire"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="defections">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Défections du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
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
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Statut juridique</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Secteur d'activité</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Gestionnaire</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Date adhésion</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Raison défection</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_defection"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_defection">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="stat_jur"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="sect_act"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="gestionnaire"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_adh"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="raison_defection"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="ouvertures">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Ouvertures de comptes du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Numéro de compte</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit d'épargne</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Solde</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_ouverture"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_ouverture">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="produit_epargne"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="clotures">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Clôtures de comptes du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(3)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(3)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Numéo de compte</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit d'épargne</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Date ouverture</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Raison de la clôture</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Solde après arrêté</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_cloture"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_cloture">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="produit_epargne"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="date_ouverture"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="raison_cloture"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="dat_prolonges">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Comptes à terme prolongés dans la journée : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
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
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Numéro de compte</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit d'épargne</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Solde actuel</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Terme initial</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Intérêts prévus</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Prochain terme</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_dat_prolonge"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_dat_prolonge">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="produit_epargne"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="terme_initial"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="interets"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="prochain_terme"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="dat_non_prolonges">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Comptes à terme non-prolongés dans la journée : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
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
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Numéo de compte</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit d'épargne</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Solde actuel</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Terme initial</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Intérêts prévus</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Jours restants</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_dat_non_prolonge"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_dat_non_prolonge">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="num_cpte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="produit_epargne"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="terme"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="interets"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_jours"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="dossiers_credit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Demandes de crédit du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
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
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">N°</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit de crédit</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Durée (mois)</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Objet de la demande</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Gestionnaire</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_dossier_credit_sans_mnt_octr"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="dcr_approuves">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Crédits approuvés du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">N°</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit de crédit</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant dem.</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant octr.</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Durée (mois)</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Objet de la demande</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Gestionnaire</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_dossier_credit_avec_mnt_octr"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="dcr_rejetes">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Crédits rejetés du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">N°</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit de crédit</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Durée (mois)</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Objet de la demande</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Gestionnaire</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Motif rejet</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_dossier_credit_rejete"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="dcr_annules">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Crédits annulés du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">N°</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit de crédit</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant dem.</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Durée (mois)</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Objet de la demande</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Gestionnaire</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Motif annul.</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_dossier_credit_rejete"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="dcr_debourses">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Crédits déboursés du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">N°</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit de crédit</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant dem.</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant octr.</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Durée (mois)</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Objet de la demande</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Gestionnaire</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_dossier_credit_avec_mnt_octr"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_dossier_credit_sans_mnt_octr">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="produit_credit"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="montant_demande"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="duree"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="objet_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="gestionnaire"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="detail_dossier_credit_avec_mnt_octr">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="produit_credit"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="montant_demande"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="montant_octroye"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="duree"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="objet_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="gestionnaire"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="detail_dossier_credit_rejete">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="produit_credit"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="montant_demande"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="duree"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="objet_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="gestionnaire"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="motif"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="dcr_repris">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Crédits repris du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell number-columns-spanned="3">
              <fo:block text-align="center">Infos Clients</fo:block>
            </fo:table-cell>
            <fo:table-cell number-columns-spanned="3">
              <fo:block text-align="center">Infos Crédits</fo:block>
            </fo:table-cell>
            <fo:table-cell number-columns-spanned="3">
              <fo:block text-align="center">Remboursement</fo:block>
            </fo:table-cell>
            <fo:table-cell number-columns-spanned="3">
              <fo:block text-align="center">Impayés </fo:block>
            </fo:table-cell>
          </fo:table-row>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID dossier</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Etat</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Produit</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant Octr</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant Remb</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Interets Remb</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Penalités Remb</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant Restant</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Interets Restants</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Penalités restantes </fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_crd_repris"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_crd_repris">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="cre_etat"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="libel_prod"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="mnt_octr"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="cap_remb"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="int_remb"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="pen_remb"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="cap_restant"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="int_restant"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="pen_restant"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="ps_repris">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Parts sociales reprises du jour : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Secteur d'activité</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nombre de parts</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Gestionnaire</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_ps_repris"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_ps_repris">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="sect_act"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nbre_parts"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="gestionnaire"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="comptes_ajustes">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Ajustements de soldes de comptes clients : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(1)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID agence</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Code utilisateur</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Heure</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">N° client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom client</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Num. compte</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Ancien solde</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nouveau solde</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_compte_ajuste"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_compte_ajuste">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_agence"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="login"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="heure"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
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
          <xsl:value-of select="anc_solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nouv_solde"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="app_caisses">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Approvisionnement des caisses : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID guichet</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du guichet</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Devise</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_app_caisses"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_app_caisses">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_gui"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="libel_gui"/>
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
  <xsl:template match="delest_caisses">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Delestages des caisses : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">ID guichet</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Nom du guichet</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Devise</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_delest_caisses"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_delest_caisses">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="id_gui"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="libel_gui"/>
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
  <xsl:template match="situation_coffre">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Situation du coffre-fort : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Solde</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant débité</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant crédité</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Devise</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_situation_coffre"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_situation_coffre">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="montant_deb"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="montant_cred"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="devise"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="situation_dep">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="concat('Situation des dépenses : ', @nombre)"/>
    </xsl:call-template>
    <xsl:if test="@nombre &gt; 0">
      <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-column column-width="proportional-column-width(2)"/>
        <fo:table-header>
          <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Libellé</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Montant</fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
              <fo:block text-align="center">Devise</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="detail_situation_dep"/>
        </fo:table-body>
      </fo:table>
    </xsl:if>
  </xsl:template>
  <xsl:template match="detail_situation_dep">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="libel_ecriture"/>
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
