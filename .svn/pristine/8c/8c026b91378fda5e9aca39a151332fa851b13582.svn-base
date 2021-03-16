<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait_no_region"/>
      <xsl:apply-templates select="recu"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="signature.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="recu">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="body"/>
        <fo:block space-before.optimum="2cm"/>
        <fo:block text-align="center"><xsl:value-of select="$ciseaux" disable-output-escaping="yes"/>--------------------------------------------------------------------------------------------------------------------------------------------------------------                        </fo:block>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="body"/>
        <fo:block space-before.optimum="2cm"/>
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
          <fo:block space-before.optimum="0.3cm">Client Number: <xsl:value-of select="num_client"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Name: <xsl:value-of select="nom_client"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Montant des frais d'adhésion: <xsl:value-of select="montant_frais_adh"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Montant frais d'adhésion versé: <xsl:value-of select="tranche_frais"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <xsl:choose>
        <xsl:when test="nbre_parts">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Number of shares subscribed: <xsl:value-of select="nbre_parts"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Number of liberated shares: <xsl:value-of select="nbre_parts_lib"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Nominal value of a share: <xsl:value-of select="prix_part"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Amount paid for liberated shares: <xsl:value-of select="total_ps"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:when>
        <xsl:otherwise>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Number of subscribed shares: 0 </fo:block>
            </fo:list-item-body>
          </fo:list-item>
            <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Nombre de parts sociales libérées: 0</fo:block>
            </fo:list-item-body>
          </fo:list-item>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Nominal value of a share: <xsl:value-of select="prix_part"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Amount paid for liberated shares:  0 </fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:otherwise>
      </xsl:choose>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Total amount deposited: <xsl:value-of select="total"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <xsl:if test="num_cpte">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Account Number: <xsl:value-of select="num_cpte"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Account Balance: <xsl:value-of select="solde_cpt_base"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Transaction Number: <xsl:value-of select="num_trans"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:block space-before.optimum="1cm"/>
    <xsl:call-template name="signature"/>
  </xsl:template>
</xsl:stylesheet>
