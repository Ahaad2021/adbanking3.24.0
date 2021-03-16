<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="histo_credit"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="histo_credit">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="ligne_produit"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Global informations'"/>
    </xsl:call-template>
    <xsl:apply-templates select="total"/>
  </xsl:template>
  <xsl:template match="ligne_produit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="lib_prod"/>
      </xsl:with-param>
    </xsl:call-template>
    <fo:table width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>Number</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="left">Name</fo:block>
          </fo:table-cell>
          <fo:table-cell number-columns-spanned="5" border-bottom-width="0.1pt" border-bottom-style="solid" border-bottom-color="black">
            <fo:block text-align="center">Prior loan</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Days</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="left">Product</fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block padding-after="10pt">client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="left">client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Amount</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Date of disbursement</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Date paid</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">On time (%)</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Status</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">without a loan</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="left">from loan</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="ligne_histo"/>
        <xsl:apply-templates select="ligne_histo_credit_gs"/>
        <xsl:apply-templates select="prod_total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="ligne_histo">
    <fo:table-row>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block>
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block>
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="mnt_credit"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="cre_date_debloc"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="date_reglt"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="taux_retard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="etat_credit"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="jours_sans_pret"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="prd_credit"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="ligne_histo_credit_gs">
    <fo:table-row>
      <xsl:choose>
        <xsl:when test="membre_gs='true'">
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="mnt_credit"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="cre_date_debloc"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="date_reglt"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="taux_retard"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="etat_credit"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="jours_sans_pret"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="left">
              <xsl:value-of select="prd_credit"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="mnt_credit"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="cre_date_debloc"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="date_reglt"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="taux_retard"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="etat_credit"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="jours_sans_pret"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.3mm" border-style="outset">
            <fo:block font-weight="bold" text-align="left">
              <xsl:value-of select="prd_credit"/>
            </fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="prod_total">
    <fo:table-row>
      <fo:table-cell padding-before="8pt">
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold"> Total en devise</fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_mnt_octr"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total">
    <fo:table border-collapse="separate" width="50%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-body>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>Montant total en (<xsl:value-of select="devise"/>) :</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="mnt_credit"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
</xsl:stylesheet>
