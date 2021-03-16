<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="provisioncredit"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="provisioncredit">
    <fo:page-sequence master-reference="main" font-size="9pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="produit"/>
        <xsl:apply-templates select="total"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="produit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="libel_prod"/>
      </xsl:with-param>
    </xsl:call-template>
    <fo:table border-collapse="separate" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block text-align="left">Client</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Name</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">File</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Status</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Civil status</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Oustanding balance of loan principal</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Hard cash securities</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Provision date</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Provision amount</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="client"/>
        <xsl:apply-templates select="client_credit_gs"/>
        <fo:table-row>
          <fo:table-cell border-bottom-width="0.3pt" border-bottom-style="solid" border-bottom-color="black" number-columns-spanned="7">
            <fo:block/>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row font-weight="bold">
          <fo:table-cell number-columns-spanned="2">
            <fo:block text-align="left">Number of loans : <xsl:value-of select="prod_nombre"/></fo:block>
          </fo:table-cell>
          <fo:table-cell number-columns-spanned="3">
            <fo:block/>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="prod_capital_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="prod_gar_num"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell number-columns-spanned="2">
            <fo:block text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="prod_total_provision"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="client">
    <fo:table-row border-bottom-width="0.1pt" border-bottom-style="solid" border-bottom-color="black">
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="left">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="num_dossier"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="cre_etat"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="cre_etat_date"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="capital_du"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="gar_num"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="prov_date"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.1mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="prov_mnt"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="client_credit_gs">
    <fo:table-row border-bottom-width="0.1pt" border-bottom-style="solid" border-bottom-color="black">
      <xsl:choose>
        <xsl:when test="membre_gs='true'">
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="left">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="num_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="cre_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="cre_etat_date"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="right">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="right">
              <xsl:value-of select="gar_num"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="prov_date"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="italic" font-weight="500" text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="prov_mnt"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="left">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="num_dossier"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="cre_etat"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="cre_etat_date"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="gar_num"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="prov_date"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right" wrap-option="no-wrap">
              <xsl:value-of select="prov_mnt"/>
            </fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
    </fo:table-row>
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
            <fo:block text-align="left" font-weight="bold">Oustanding balance of loan principal</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right" font-weight="bold">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Provision amount</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_provision"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block text-align="left" font-weight="bold">Hard cash securities</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_gar_num"/>
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
