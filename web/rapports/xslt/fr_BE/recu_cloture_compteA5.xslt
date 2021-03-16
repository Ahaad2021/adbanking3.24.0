<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait_no_region"/>
      <xsl:apply-templates select="recu_cloture_compte"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="signature.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="recu_cloture_compte">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="2cm"/>
        <xsl:apply-templates select="body"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="body">
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Numéro de client: <xsl:value-of select="num_client"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Nom: <xsl:value-of select="nom_client"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Numéro de compte: <xsl:value-of select="num_cpte"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Solde à la cloture: <xsl:value-of select="solde"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Frais de tenue de compte: <xsl:value-of select="frais_tenue_cpte"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Frais de fermeture: <xsl:value-of select="frais_fermeture"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Pénalitès: <xsl:value-of select="penalites"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Prélèvement impôt mobilier: <xsl:value-of select="impot_mobilier"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Destination des fonds: <xsl:value-of select="destination"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Numéro de transaction: <xsl:value-of select="historique"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:block space-before.optimum="1cm"/>
    <xsl:call-template name="signature"/>
  </xsl:template>
</xsl:stylesheet>
