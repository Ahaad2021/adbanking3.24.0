<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="rapport_op_div"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="criteres_recherche.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="header_contextuel">
    <xsl:apply-templates select="criteres_recherche"/>
  </xsl:template>

  <xsl:template match="rapport_op_div">
    <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:call-template name="titre_niv1">
          <xsl:with-param name="titre" select="'Détails des opérations'"/>
        </xsl:call-template>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          <fo:table-column column-width="proportional-column-width(1)"/>
          
          <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block text-align="center">Transaction Number</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Login</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Date</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Operation type</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Client Number</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="center">Amount</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="ligne"/>
             <fo:table-row>
      <fo:table-cell padding-before="8pt">
        <fo:block/>
      </fo:table-cell>
     <fo:table-cell padding-before="8pt">
        <fo:block/>
      </fo:table-cell>
     <fo:table-cell padding-before="8pt">
        <fo:block/>
      </fo:table-cell>
     <fo:table-cell padding-before="8pt">
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="center"> Total for the period :</fo:block>
      </fo:table-cell>
      <fo:table-cell padding-before="8pt">
        <fo:block font-weight="bold" text-align="center">
          <xsl:value-of select="total"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>         
          </fo:table-body>
        </fo:table>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="ligne">
    <fo:table-row>
      <xsl:apply-templates select="details"/>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="details">
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="num_transaction"/>
      </fo:block>
    </fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="login"/>
      </fo:block>
    </fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="date"/>
      </fo:block>
    </fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="libel_ecriture"/>
      </fo:block>
    </fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="num_client"/>
      </fo:block>
    </fo:table-cell>
    <fo:table-cell display-align="center" border="0.1pt solid gray">
      <fo:block text-align="center">
        <xsl:value-of select="montant"/>
      </fo:block>
    </fo:table-cell>
  </xsl:template>
</xsl:stylesheet>
