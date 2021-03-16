<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="creditactif"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="creditactif">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="gestionnaire"/>
        <xsl:apply-templates select="total"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="gestionnaire">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="nom_gestionnaire"/>
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
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-header>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid black" number-rows-spanned="2" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Client No.</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Name</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Locations</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" number-columns-spanned="7" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Address</fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">File No.</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Product</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Status</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Amount requested</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Amount granted</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Oustanding balance of loan principal</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Interest remaining due</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Grant date</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-left-width="1px" border-top-width="1px" border-right-width="1px" border-bottom-width="1px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">Date of last due date</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="client"/>
      </fo:table-body>
    </fo:table>
    <xsl:apply-templates select="sous_total"/>
  </xsl:template>
  <xsl:template match="client">
    <fo:table-row>
      <xsl:choose>
        <xsl:when test="groupe_gs='groupe'">
          <fo:table-cell display-align="center" border="0.1pt solid black" number-rows-spanned="2" border-left-width="0.8px" border-top-width="0.8px" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-top-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="left">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-top-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="left">
              <xsl:value-of select="localite"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" number-columns-spanned="7" border-top-width="0.8px" border-right-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="left">
              <xsl:value-of select="adresse"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:when test="membre_gs='membre'">
          <fo:table-cell display-align="center" border="0.1pt solid black" number-rows-spanned="2" border-left-width="0.8px" border-top-width="0.8px" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="center">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-top-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="left">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-top-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="left">
              <xsl:value-of select="localite"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" number-columns-spanned="7" border-top-width="0.8px" border-right-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="left">
              <xsl:value-of select="adresse"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell display-align="center" border="0.1pt solid black" number-rows-spanned="2" border-left-width="0.8px" border-top-width="0.8px" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-top-width="0.8px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-top-width="0.8px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="localite"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" number-columns-spanned="7" border-top-width="0.8px" border-right-width="0.8px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="adresse"/>
            </fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
    </fo:table-row>
    <fo:table-row>
      <xsl:choose>
        <xsl:when test="groupe_gs='groupe'">
          <fo:table-cell display-align="center" border="0.1pt solid black" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="num_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="left">
              <xsl:value-of select="libel_prod"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="cre_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="mnt_dem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="cre_mnt_octr"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="solde_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="cre_date_approb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-bottom-width="0.8px" border-right-width="0.8px" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="delai"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:when test="membre_gs='membre'">
          <fo:table-cell display-align="center" border="0.1pt solid black" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="center">
              <xsl:value-of select="num_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="left">
              <xsl:value-of select="libel_prod"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="center">
              <xsl:value-of select="cre_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="mnt_dem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="cre_mnt_octr"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="solde_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="center">
              <xsl:value-of select="cre_date_approb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-bottom-width="0.8px" border-right-width="0.8px" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="center">
              <xsl:value-of select="delai"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="num_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-left="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="libel_prod"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="cre_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_dem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="cre_mnt_octr"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="solde_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt" border-bottom-width="0.8px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="cre_date_approb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" border-bottom-width="0.8px" border-right-width="0.8px" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="delai"/>
            </fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="sous_total">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Sous Total'"/>
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
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.pt solid black">
            <fo:block font-weight="bold" text-align="center">Number loans</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">Amount granted</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">Currency</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">Capital reimbursed</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">Reimbursed interests</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">Reimbursed securities</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">Penalties reimbursed</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">Total reimbursed</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">Oustanding balance of loan principal</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.1pt solid black">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="prod_nombre"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prod_montant"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prod_devise"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prod_capital"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prod_interet"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prod_garantie"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prod_penalite"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prod_total_remb"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid black" padding-right="3pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prod_capital_du"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="total">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Total'"/>
    </xsl:call-template>
    <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="40%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Number of loans</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="nombre"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Amount granted</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="montant"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Capital reimbursed</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="capital"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Reimbursed interests</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="interet"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Reimbursed securities</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="garantie"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Penalties reimbursed</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="penalite"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Total reimbursed</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="total_remb"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Oustanding balance of loan principal</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>
</xsl:stylesheet>
