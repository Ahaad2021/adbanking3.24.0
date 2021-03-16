<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait_no_region"/>
      <xsl:apply-templates select="operation_diverse_caisse"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="signature.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="operation_diverse_caisse">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="1cm"/>
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
          <fo:block space-before.optimum="0.2cm">Operation date: <xsl:value-of select="date_op"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.2cm">Operation designation: <xsl:value-of select="libelle_op"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-column column-width="proportional-column-width(3)"/>
      <fo:table-header>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.0pt gray">
            <fo:list-block>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Compte débit: <xsl:value-of select="compte_debit"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
            </fo:list-block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.0pt gray">
            <fo:list-block>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Compte crédit: <xsl:value-of select="compte_credit"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
            </fo:list-block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:list-block>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Devise débit: <xsl:value-of select="devise_debit"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Amount of debit:: <xsl:value-of select="montant_debit"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Montant tva: <xsl:value-of select="montant_tax_debit"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Montant ttc: <xsl:value-of select="montant_ttc_debit"/></fo:block>
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
                  <fo:block space-before.optimum="0.2cm">Devise crédit: <xsl:value-of select="devise_credit"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Loan amount: <xsl:value-of select="montant_credit"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Montant tva: <xsl:value-of select="montant_tax_credit"/></fo:block>
                </fo:list-item-body>
              </fo:list-item>
              <fo:list-item>
                <fo:list-item-label>
                  <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                  <fo:block space-before.optimum="0.2cm">Montant ttc: <xsl:value-of select="montant_ttc_credit"/></fo:block>
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
          <fo:block space-before.optimum="0.2cm">Type pièce: <xsl:value-of select="type_piece"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.2cm">Numéro pièce: <xsl:value-of select="numero_piece"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.2cm">Document date: <xsl:value-of select="date_piece"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.2cm">Communication / remark: <xsl:value-of select="communication"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.2cm">Remark: <xsl:value-of select="remarque"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.2cm">Transaction number: <xsl:value-of select="num_trans"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:block space-before.optimum="1cm"/>
    <xsl:call-template name="signature"/>
  </xsl:template>
</xsl:stylesheet>
