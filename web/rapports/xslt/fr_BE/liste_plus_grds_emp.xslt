<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="liste_plus_grds_emp"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="research_criteria.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="liste_plus_grds_emp">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="globals"/>
        <xsl:apply-templates select="details"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="details">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Details of List of 10 Biggest Borrowers'"/>
    </xsl:call-template>
    <fo:table border-collapse="separate" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(2.5)"/>
      <fo:table-column column-width="proportional-column-width(7)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(3.5)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block text-align="right">No.</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Names</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Original Date</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Original Amount</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Terms</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Balance</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Overdue</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Guarantees</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">Provision</fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center" font-style="italic" font-size="8pt" color="blue"> MV.TB.NO </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center" font-style="italic" font-size="8pt" color="blue"> MV.TB.NAME </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> MV.TB.DATE </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> MV.TB.AMOUNT </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> MV.TB.TERMS </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> MV.TB.BALANCE </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center" font-style="italic" font-size="8pt" color="blue"> MV.TB.OVERDUE </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> MV.TB.GUAR </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> MV.TB.PROV </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="client"/>
        <xsl:apply-templates select="total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="research_criteria"/>
  </xsl:template>
  <xsl:template match="client">
    <fo:table-row>
      <xsl:choose>
        <xsl:when test="groupe_gs='groupe'">
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="right">
            <fo:block text-align="center">
              <xsl:value-of select="index"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
            <fo:block text-align="left">
              <xsl:value-of select="nom"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="date_pret"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_pret"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="echeances"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
            <fo:block text-align="right">
              <xsl:value-of select="solde"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_retard"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="garanties"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_prov"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:when test="membre_gs='membre'">
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="right">
            <fo:block text-align="center">
              <xsl:value-of select="index"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
            <fo:block text-align="left">
              <xsl:value-of select="nom"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="date_pret"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_pret"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="echeances"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
            <fo:block text-align="right">
              <xsl:value-of select="solde"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_retard"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="garanties"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_prov"/>
            </fo:block>
          </fo:table-cell>
        </xsl:when>
        <xsl:otherwise>
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="right">
            <fo:block text-align="center">
              <xsl:value-of select="index"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
            <fo:block text-align="left">
              <xsl:value-of select="nom"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="date_pret"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_pret"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center">
              <xsl:value-of select="echeances"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid" text-align="center">
            <fo:block text-align="right">
              <xsl:value-of select="solde"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_retard"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="garanties"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right">
              <xsl:value-of select="mnt_prov"/>
            </fo:block>
          </fo:table-cell>
        </xsl:otherwise>
      </xsl:choose>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total">
    <fo:table-row>
      <fo:table-cell border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold"> </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold" text-align="center">TOTAL</fo:block>
      </fo:table-cell>
      <fo:table-cell background-color="#C4C2C1" border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold"/>
      </fo:table-cell>
      <fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_mnt_pret"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell background-color="#C4C2C1" border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold"> </fo:block>
      </fo:table-cell>
      <fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_solde"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold">
          <xsl:value-of select="tot_mnt_retard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell background-color="#C4C2C1" border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold" text-align="right"/>
      </fo:table-cell>
      <fo:table-cell background-color="#CCFFFF" border-width="0.2mm" border-style="solid" text-align="right">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="tot_mnt_prov"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
