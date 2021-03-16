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
        <fo:block space-before.optimum="1cm"/>
        <xsl:apply-templates select="body"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="body">
    <fo:list-block>
      <xsl:if test="nom_client">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Client name : <xsl:value-of select="nom_client"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <xsl:if test="donneur_ordre">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Originator : <xsl:value-of select="donneur_ordre"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <xsl:if test="num_cpte">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Account number : <xsl:value-of select="num_cpte"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <xsl:if test="num_carte_ferlo">
        <fo:list-item>
          <fo:list-item-label>
            <fo:block/>
          </fo:list-item-label>
          <fo:list-item-body>
            <fo:block space-before.optimum="0.3cm">Ferlo card number : <xsl:value-of select="num_carte_ferlo"/></fo:block>
          </fo:list-item-body>
        </fo:list-item>
      </xsl:if>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Amount : <xsl:value-of select="montant"/><xsl:if test="/recu/@type='7'"><fo:inline font-weight="bold">  (sous réserve d'encaissement)</fo:inline></xsl:if></fo:block>
        </fo:list-item-body>
      </fo:list-item>
      <xsl:if test="/recu/@type='6' or /recu/@type='8' or /recu/@type='40'">
        <xsl:if test="frais">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Deposit fee : <xsl:value-of select="frais"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
        <xsl:if test="fraisDureeMin">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Charge of Minimum Duration between two withdrawals not achieved : <xsl:value-of select="fraisDureeMin"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
        <xsl:if test="frais_attente">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Fees on hold : <xsl:value-of select="frais_attente"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
        <xsl:if test="solde">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">New balance : <xsl:value-of select="solde"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
        <xsl:if test="remarque">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Remark : <xsl:value-of select="remarque"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
        <xsl:if test="communication">
          <fo:list-item>
            <fo:list-item-label>
              <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body>
              <fo:block space-before.optimum="0.3cm">Communication / remark : <xsl:value-of select="communication"/></fo:block>
            </fo:list-item-body>
          </fo:list-item>
        </xsl:if>
      </xsl:if>
      <xsl:if test="/recu/@type='7' or /recu/@type='40'">
        <xsl:apply-templates select="info_cheque"/>
      </xsl:if>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block space-before.optimum="0.3cm">Transaction number : <xsl:value-of select="num_trans"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:block space-before.optimum="2cm"/>
    <xsl:call-template name="signature"/>
  </xsl:template>
  <xsl:template match="solde">
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">New balance : <xsl:value-of select="solde"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
  </xsl:template>
  <xsl:template match="info_cheque">
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">Numéro chèque : <xsl:value-of select="num_cheque"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">Bank : <xsl:value-of select="banque_cheque"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">Date chèque : <xsl:value-of select="date_cheque"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
    <fo:list-item>
      <fo:list-item-label>
        <fo:block/>
      </fo:list-item-label>
      <fo:list-item-body>
        <fo:block space-before.optimum="0.3cm">Beneficiary : <xsl:value-of select="beneficiaire"/></fo:block>
      </fo:list-item-body>
    </fo:list-item>
  </xsl:template>
</xsl:stylesheet>
