<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template name="footer">
    <fo:static-content flow-name="xsl-region-after">
      <fo:table width="100%" table-layout="fixed">
        <fo:table-column column-width="proportional-column-width(15)"/>
        <fo:table-body font-size="8pt">
          <fo:table-row>
            <fo:table-cell>
              <fo:block text-align="center">@: <xsl:value-of select="//adresse"/>                  - <xsl:value-of select="$icone_tel" disable-output-escaping="yes"/> <xsl:value-of select="//telephone"/>                  - Fax: <xsl:value-of select="//fax"/>                  - Email: <xsl:value-of select="//email"/>                  - N° Agrément: <xsl:value-of select="//num_agrement"/>                  - Code Swift: <xsl:value-of select="//code_swift_banque"/>                  - N° TVA: <xsl:value-of select="//num_tva"/>                    -  Page <fo:page-number/> -                   © ADbanking                                  </fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-body>
      </fo:table>
    </fo:static-content>
  </xsl:template>
</xsl:stylesheet>
