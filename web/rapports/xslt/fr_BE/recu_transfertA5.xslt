<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A5_portrait_no_region"/>
      <xsl:apply-templates select="recu"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="signature.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="recu">
    <xsl:apply-templates select="body"/>
  </xsl:template>
  <xsl:template match="body">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header" mode="no_region"/>
        <fo:block space-before.optimum="1cm"/>
        <fo:table>
          <fo:table-column column-width="4cm"/>
          <fo:table-column column-width="2cm"/>
          <fo:table-column column-width="4cm"/>
          <fo:table-column column-width="4cm"/>
          <fo:table-column column-width="4cm"/>
          <fo:table-header>    </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="donneur"/>
            <xsl:apply-templates select="transfert"/>
          </fo:table-body>
        </fo:table>
        <fo:block space-before.optimum="1cm"/>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="5pt" width="100%" table-layout="fixed">
          <fo:table-column column-width="proportional-column-width(4)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <fo:table-column column-width="proportional-column-width(2)"/>
          <xsl:if test="/recu/body/beneficaires/frais !=''">
            <fo:table-column column-width="proportional-column-width(2)"/>
          </xsl:if>
          <fo:table-header>
            <fo:table-row font-weight="bold">
              <fo:table-cell border="0.1pt solid gray">
                <fo:block text-align="center">Bénéficiaire</fo:block>
              </fo:table-cell>
              <fo:table-cell border="0.1pt solid gray">
                <fo:block text-align="center">Numéro de compte</fo:block>
              </fo:table-cell>
              <fo:table-cell border="0.1pt solid gray">
                <fo:block text-align="center">date de demande</fo:block>
              </fo:table-cell>
              <fo:table-cell border="0.1pt solid gray">
                <fo:block text-align="center">Montant prélevé</fo:block>
              </fo:table-cell>
              <fo:table-cell border="0.1pt solid gray">
                <fo:block text-align="center">Montant transféré</fo:block>
              </fo:table-cell>
              <xsl:if test="/recu/body/beneficaires/frais !=''">
                <fo:table-cell border="0.1pt solid gray">
                  <fo:block text-align="center">Frais</fo:block>
                </fo:table-cell>
              </xsl:if>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <xsl:apply-templates select="beneficaires"/>
          </fo:table-body>
        </fo:table>
        <fo:block space-before.optimum="0.5cm"/>
        <fo:block>Communication : <xsl:value-of select="communication"/></fo:block>
        <fo:block>Remarque      :<xsl:value-of select="remarque"/></fo:block>
        <fo:block space-before.optimum="2cm"/>
        <xsl:call-template name="signature"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="donneur">
    <fo:table-row>
      <fo:table-cell number-columns-spanned="2">
        <fo:block>Nom client </fo:block>
      </fo:table-cell>
      <fo:table-cell number-columns-spanned="3">
        <fo:block>: <xsl:value-of select="nomClient"/></fo:block>
      </fo:table-cell>
    </fo:table-row>
    <xsl:if  test="/recu/body/donneur/donneurOrdre !=''">
      <fo:table-row>
        <fo:table-cell number-columns-spanned="2">
          <fo:block>Donneur d'ordre </fo:block>
        </fo:table-cell>
        <fo:table-cell number-columns-spanned="3">
          <fo:block>: <xsl:value-of select="donneurOrdre"/></fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <fo:table-row>
      <fo:table-cell number-columns-spanned="2">
        <fo:block>Montant virement </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>: <xsl:value-of select="montant"/></fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>Numéro de compte</fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>: <xsl:value-of select="numCpte"/></fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="transfert">
    <fo:table-row>
      <fo:table-cell number-columns-spanned="2">
        <fo:block>Frais </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>: <xsl:value-of select="frais"/></fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block/>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block/>
      </fo:table-cell>
    </fo:table-row>
    <xsl:if test="/recu/body/transfert/frais_minimum_2retrait !=''">
      <fo:table-row>
        <fo:table-cell number-columns-spanned="2">
          <fo:block>Frais de non respect de la duree minimum entre deux retraits </fo:block>
        </fo:table-cell>
        <fo:table-cell display-align = "after">
          <fo:block>: <xsl:value-of select="frais_minimum_2retrait"/></fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <fo:table-row>
      <fo:table-cell number-columns-spanned="2">
        <fo:block>Date de transaction </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>: <xsl:value-of select="dateTransa"/></fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block> Numéro de transaction </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>: <xsl:value-of select="numTransa"/></fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
  <xsl:template match="beneficaires">
    <fo:table-row>
      <fo:table-cell border="0.1pt solid gray">
        <fo:block>
          <xsl:value-of select="nomBeneficaire"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.1pt solid gray">
        <fo:block>
          <xsl:value-of select="numCpteBeneficaire"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.1pt solid gray">
        <fo:block>
          <xsl:value-of select="dateDemandeVir"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="mntPreleve"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="mntBeneficaire"/>
        </fo:block>
      </fo:table-cell>
      <xsl:if test="/recu/body/beneficaires/frais !=''">
        <fo:table-cell border="0.1pt solid gray">
          <fo:block text-align="center">
            <xsl:value-of select="frais"/>
          </fo:block>
        </fo:table-cell>
      </xsl:if>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
