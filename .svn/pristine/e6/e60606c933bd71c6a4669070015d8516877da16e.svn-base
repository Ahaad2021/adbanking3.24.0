<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="credit_echeance"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="credit_echeance">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="ligne_credit"/>
        <xsl:apply-templates select="total_general"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="ligne_credit">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="echeance"/>
      </xsl:with-param>
    </xsl:call-template>
    <fo:table border="none" border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>File No.</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Client No.</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Client name</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">       Date ech.      </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">       Montant capital      </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">       Montant intérêt      </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">       Montant garantie      </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">       Montant réech      </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">       Capital balance      </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">       Total capital restant dû      </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="ligne"/>
        <xsl:apply-templates select="sous_total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="ligne">
    <fo:table-row>
      <xsl:choose>
        <xsl:when test="groupe_gs='groupe'">
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold">
              <xsl:value-of select="num_doss"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="left">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="date_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="mnt_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="mnt_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="mnt_gar"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="mnt_reech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="solde_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.2mm" border-style="solid">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:when test="membre_gs='membre'">
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500">
              <xsl:value-of select="num_doss"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500">
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="left">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="center">
              <xsl:value-of select="date_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="mnt_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="mnt_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="mnt_gar"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="mnt_reech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="solde_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.02mm" border-style="solid">
            <fo:block font-style="oblique" font-weight="500" text-align="right">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block>
              <xsl:value-of select="num_doss"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block>
              <xsl:value-of select="num_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="left">
              <xsl:value-of select="nom_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="date_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_gar"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_reech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="solde_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="capital_du"/>
            </fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="sous_total">
    <fo:table-row font-weight="bold">
      <fo:table-cell number-columns-spanned="4" border-top-width="0.5pt" border-top-style="solid">
        <fo:block>     Sous-total : <xsl:value-of select="nombre"/> credits    </fo:block>
      </fo:table-cell>
      <fo:table-cell border-top-width="0.5pt" border-top-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="montant"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-top-width="0.5pt" border-top-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="interet"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-top-width="0.5pt" border-top-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="garantie"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-top-width="0.5pt" border-top-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="reech"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-top-width="0.5pt" border-top-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-top-width="0.5pt" border-top-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="capital_du"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total_general">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'TOTAL'"/>
    </xsl:call-template>
    <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-body>
        <fo:table-row font-weight="bold">
          <fo:table-cell number-columns-spanned="4">
            <fo:block>        Total general : <xsl:value-of select="total_nombre"/> credits      </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_montant"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_interet"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_garantie"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_reech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_solde"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_capital"/>
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
