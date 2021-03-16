<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_paysage"/>
      <xsl:apply-templates select="credits_perte"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="credits_perte">
    <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="total"/>
        <xsl:apply-templates select="details"/>
      </fo:flow>
      <xsl:if test="@exist_gestionnaire='1'">
        <fo:table-cell>
          <fo:block>Manager</fo:block>
        </fo:table-cell>
      </xsl:if>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>
  <xsl:template match="total">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Global informations'"/>
    </xsl:call-template>
    <fo:table border-collapse="separate" width="50%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell>
            <fo:block>Montant total passé en perte : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_perte"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block>Montant total recouvert : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_perte_rec"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>

        <fo:table-row>
          <fo:table-cell>
            <fo:block>Total Amount of Capital Recovered : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_cap_recupere"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block>Total Amount of Interest Recovered : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_int_recupere"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell>
            <fo:block>Total Amount of Penalities Recovered : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_pen_recupere"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>

        <fo:table-row>
          <fo:table-cell>
            <fo:block>Montant total provisionné : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <xsl:value-of select="total_prov_mnt"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="details">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Informations détaillées'"/>
    </xsl:call-template>
    <fo:table border-collapse="separate" width="100%" table-layout="fixed">
     <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(5)"/>
      <fo:table-column column-width="proportional-column-width(5)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2.1)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(2.1)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">Rank</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">File No.</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">Client No.</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block>Client name</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block>Product</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block>Purpose of request</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">Amount of loss</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">Capital recovered</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right"> Interest recovered</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right"> Penalty recovered</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">Total amount written-off</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">Provision</fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">Provision date</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="credit"/>
        <xsl:apply-templates select="credit_gs"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="credit">
    <fo:table-row>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="index"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="id_doss"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="id_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block>
          <xsl:value-of select="nom"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block>
          <xsl:value-of select="produit"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block>
          <xsl:value-of select="obj_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="mnt_perte"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="mnt_rec"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="int_rec"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="date"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="prov_mnt"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.05mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="prov_date"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="credit_gs">
    <fo:table-row>
      <xsl:choose>
        <xsl:when test="membre_gs='true'">
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="right">
              <xsl:value-of select="index"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="id_doss"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="id_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500">
              <xsl:value-of select="nom"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500">
              <xsl:value-of select="produit"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500">
              <xsl:value-of select="obj_dem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="right">
              <xsl:value-of select="mnt_perte"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="right">
              <xsl:value-of select="mnt_rec"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="right">
              <xsl:value-of select="int_rec"/>
            </fo:block>
          </fo:table-cell>
           <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="right">
              <xsl:value-of select="pen_rec"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="dashed">
            <fo:block font-style="italic" font-weight="500" text-align="center">
              <xsl:value-of select="date"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prov_mnt"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="prov_date"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="index"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="id_doss"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="id_client"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold">
              <xsl:value-of select="nom"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold">
              <xsl:value-of select="produit"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold">
              <xsl:value-of select="obj_dem"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="mnt_perte"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="mnt_rec"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="int_rec"/>
            </fo:block>
          </fo:table-cell>
             <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="pen_rec"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="date"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="prov_mnt"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="outset">
            <fo:block font-weight="bold" text-align="center">
              <xsl:value-of select="prov_date"/>
            </fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
