<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="simulation_echeancier"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="simulation_echeancier">
    <xsl:apply-templates select="infos_doss"/>
  </xsl:template>
  <xsl:template match="infos_doss">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:call-template name="titre_niv2"/>
        <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell number-columns-spanned="2">
                <fo:block/>
              </fo:table-cell>
              <fo:table-cell number-columns-spanned="4">
                <fo:block text-align="center">Due date</fo:block>
              </fo:table-cell>
              <fo:table-cell number-columns-spanned="4">
                <fo:block text-align="center">Balance</fo:block>
              </fo:table-cell>
            </fo:table-row>
            <fo:table-row font-weight="bold">
              <fo:table-cell>
                <fo:block text-align="center">No.</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="center">Date</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="right">Expected capital</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="right">Interests</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="right">Security</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="right">Total</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="right">Expected capital</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="right">Interests</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="right">Security</fo:block>
              </fo:table-cell>
              <fo:table-cell>
                <fo:block text-align="right">Total</fo:block>
              </fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>    </fo:table-body>
        </fo:table>
        <xsl:call-template name="titre_niv2"/>
        <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-body>
            <xsl:apply-templates select="ech"/>
          </fo:table-body>
        </fo:table>
        <xsl:call-template name="titre_niv2"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="teste">
    <xsl:choose>
      <xsl:when test="date_ech='Total'">  </xsl:when>
      <xsl:otherwise>
        <fo:table-row>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="num_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="date_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="cap_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="int_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="gar_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="solde_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="solde_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="solde_gar"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="solde_total"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template match="ech">
    <xsl:choose>
      <xsl:when test="date_ech='Total'">
        <fo:table-row font-weight="bold">
          <fo:table-cell padding-before="3pt">
            <fo:block>
              <xsl:value-of select="num_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block>
              <xsl:value-of select="date_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="cap_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="int_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="gar_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="total_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="solde_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="solde_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="solde_gar"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="solde_total"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </xsl:when>
      <xsl:otherwise>
        <fo:table-row>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="num_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="date_ech"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="cap_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="int_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="gar_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_du"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="solde_cap"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="solde_int"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell padding-before="3pt">
            <fo:block text-align="right">
              <xsl:value-of select="solde_gar"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="solde_total"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>
