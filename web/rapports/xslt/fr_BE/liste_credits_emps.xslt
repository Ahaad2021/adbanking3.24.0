<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="liste_credits_emps"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="research_criteria.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="liste_credits_emps">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="details_dir"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="research_criteria"/>
  </xsl:template>
  <xsl:template match="details_dir">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Details of Employees Loans'"/>
    </xsl:call-template>
    <fo:table border-collapse="separate" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(4)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1.5)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center"> No </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center"> Names </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right"> Original date </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right"> Original Amount </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right"> Terms </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right"> Balance </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center"> Overdue </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right"> Guarantees </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right"> Provision </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center" font-style="italic" font-size="8pt" color="blue"> M.EML.11 </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center" font-style="italic" font-size="8pt" color="blue"> M.EML.12 </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> M.EML.13 </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> M.EML.14 </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> M.EML.15 </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> M.EML.16 </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="center" font-style="italic" font-size="8pt" color="blue"> M.EML.17 </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> M.EML.18 </fo:block>
          </fo:table-cell>
          <fo:table-cell border-width="0.1mm" border-style="solid">
            <fo:block text-align="right" font-style="italic" font-size="8pt" color="blue"> M.EML.19 </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="client"/>
        <xsl:apply-templates select="total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="client">
    <fo:table-row>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="index"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="nom"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="center">
          <xsl:value-of select="date_dem"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="cre_mnt_octr"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="nbre_ech"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="solde_cap"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="cre_retard_etat_max_jour"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="right">
          <xsl:value-of select="gar_tot"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block text-align="right"> </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="total">
    <fo:table-row>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block font-weight="bold" text-align="center"> Total </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block> </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block> </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="total_mnt_octr"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block> </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="total_solde_cap"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="total_retard"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block> </fo:block>
      </fo:table-cell>
      <fo:table-cell border-width="0.2mm" border-style="solid">
        <fo:block> </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
