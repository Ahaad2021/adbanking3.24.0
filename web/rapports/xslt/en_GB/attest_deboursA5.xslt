<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait_no_region"/>
      <xsl:apply-templates select="attest_debours"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="signature.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="attest_debours">
    <xsl:apply-templates select="infos_doss"/>
  </xsl:template>
  <xsl:template match="infos_doss">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="0.2cm"/>
        <xsl:apply-templates select="body"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="body">
    <fo:list-block>
      <xsl:choose>
        <xsl:when test="@gs_cat = 2">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block>Numéro groupe solidaire:<xsl:value-of select="num_gs"/><fo:leader leader-pattern="space" leader-length.optimum="5cm"/>Nom groupe solidaire: <xsl:value-of select="nom_gs"/> </fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:when>
        <xsl:otherwise>
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block>
                <xsl:value-of select="num_gs"/>
                <fo:leader leader-pattern="space" leader-length.optimum="5cm"/>
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
          <fo:block space-before.optimum="0.3cm">Client number: <xsl:value-of select="num_client"/></fo:block>
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
          <fo:block space-before.optimum="0.3cm">File number: <xsl:value-of select="id_doss"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Check number: <xsl:value-of select="num_cheque"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:list-block>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Amount disbursed: <xsl:value-of select="mnt_debours"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Commission amount: <xsl:value-of select="mnt_com"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Montant tva sur la commission: <xsl:value-of select="mnt_tax_com"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Amount of insurances: <xsl:value-of select="mnt_ass"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Amount of file fees : <xsl:value-of select="mnt_frais"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Montant tva sur les frais: <xsl:value-of select="mnt_tax_frais"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
            </fo:list-block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:list-block>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Garantie numéraire à bloquer: <xsl:value-of select="gar_num"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Garantie matérielle à bloquer: <xsl:value-of select="gar_mat"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Hard cash security drawn upon: <xsl:value-of select="gar_num_mob"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Material security drawn upon: <xsl:value-of select="gar_mat_mob"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Montant de la garantie en cours : <xsl:value-of select="mnt_gar_encours"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.3cm">Nouveau solde du compte lié: <xsl:value-of select="solde"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
            </fo:list-block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Transaction number: <xsl:value-of select="num_trans"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:block space-before.optimum="0.5cm"/>
    <xsl:call-template name="signature"/>
  </xsl:template>
</xsl:stylesheet>
