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
    <xsl:apply-templates select="infos_doss"/>
  </xsl:template>
  <xsl:template match="infos_doss">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="1cm"/>
        <xsl:apply-templates select="body"/>
        <fo:block space-before.optimum="2cm"/>
        <fo:block text-align="center"><xsl:value-of select="$ciseaux" disable-output-escaping="yes"/>--------------------------------------------------------------------------------------------------------------------------------------------------------------                        </fo:block>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="0.5cm"/>
        <xsl:apply-templates select="body"/>
        <fo:block space-before.optimum="0.5cm"/>
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
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Numéro dossier de crédit : <xsl:value-of select="iddossier"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date of reimbursement : <xsl:value-of select="date_remb"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <xsl:choose>
        <xsl:when test="@gs_cat = 2">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Numéro groupe solidaire : <xsl:value-of select="num_gs"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nom groupe solidaire: <xsl:value-of select="nom_gs"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:when>
        <xsl:otherwise>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">
                <xsl:value-of select="num_gs"/>
              </fo:block>
            </fo:list-item-body>
          </fo:list-item>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">
                <xsl:value-of select="nom_gs"/>
              </fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:otherwise>
      </xsl:choose>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Client number : <xsl:value-of select="num_client"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Name : <xsl:value-of select="nom_client"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Amount reimbursed : <xsl:value-of select="mnt_rbt"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Capital remaining due : <xsl:value-of select="encours"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Interests remaining due: <xsl:value-of select="interet"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Charges remaining due: <xsl:value-of select="frais"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Garantie restant due: <xsl:value-of select="garantie"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm"><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Penalties due : <xsl:value-of select="penalite"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:block space-before.optimum="0.5cm"/>
    <xsl:call-template name="signature"/>
  </xsl:template>
</xsl:stylesheet>
