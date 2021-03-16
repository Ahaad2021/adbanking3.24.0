<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="liste_impot_mobilier_collecte"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="liste_impot_mobilier_collecte">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="header_contextuel"/>
        <!--<xsl:apply-templates select="totaux_devises"/>-->
        <xsl:apply-templates select="clients"/>
        <xsl:apply-templates select="total"/>
        <!--<xsl:if test="enreg_agence/is_siege='true'">-->
          <!--<xsl:call-template name="titre_niv1">-->
            <!--<xsl:with-param name="titre" select="'Liste des agences consolidées'"/>-->
          <!--</xsl:call-template>-->
          <!--<fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">-->
            <!--<fo:table-column column-width="proportional-column-width(3)"/>-->
            <!--<fo:table-column column-width="proportional-column-width(3)"/>-->
            <!--<fo:table-column column-width="proportional-column-width(3)"/>-->
            <!--<fo:table-header>-->
              <!--<fo:table-row font-weight="bold">-->
                <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
                  <!--<fo:block text-align="center">Identifiant agence </fo:block>-->
                <!--</fo:table-cell>-->
                <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
                  <!--<fo:block text-align="center"> Libellé agence  </fo:block>-->
                <!--</fo:table-cell>-->
                <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
                  <!--<fo:block text-align="center"> Date dernier mouvement </fo:block>-->
                <!--</fo:table-cell>-->
              <!--</fo:table-row>-->
            <!--</fo:table-header>-->
            <!--<fo:table-body>-->
              <!--<xsl:apply-templates select="enreg_agence"/>-->
            <!--</fo:table-body>-->
          <!--</fo:table>-->
        <!--</xsl:if>-->
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="clients">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre">
        <xsl:value-of select="lib_prod_ep"/>
      </xsl:with-param>
    </xsl:call-template>
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-header>
        <fo:table-row>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Date opération</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">N° de client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Nom client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Intérêts bruts reçus par le client</fo:block>
          </fo:table-cell>
          <fo:table-cell display-align="center" border="0.1pt solid gray">
            <fo:block text-align="center">Montant impôt mobilier collecté</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="comptes"/>
        <xsl:apply-templates select="sous_total"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>





  <xsl:template match="total">
    <fo:block font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0.5cm" font-weight="bold" border-bottom-width="0.5pt" border-bottom-style="none" border-bottom-color="black"/>
    <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-body>
        <!--<fo:table-row column-number="5">-->
          <!--<fo:table-cell display-align="center">-->
            <!--<fo:block text-align="left"> <fo:leader /> </fo:block>-->
          <!--</fo:table-cell>-->
        <!--</fo:table-row>-->
        <fo:table-row font-weight="bold">
          <fo:table-cell display-align="center" border="0.2pt solid black" number-columns-spanned="3">
            <fo:block font-weight="bold" text-align="center"> Total général</fo:block>
          </fo:table-cell>
          <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="total_interet_annuel"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
            <fo:block font-weight="bold" text-align="right">
              <xsl:value-of select="total_montant_impot"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <!--<fo:table-row column-number="5">-->
          <!--<fo:table-cell display-align="center">-->
            <!--<fo:block text-align="left"> <fo:leader /> </fo:block>-->
          <!--</fo:table-cell>-->
        <!--</fo:table-row>-->
      </fo:table-body>
    </fo:table>
  </xsl:template>



  <xsl:template match="header_contextuel">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Informations synthétiques'"/>
    </xsl:call-template>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date début : <xsl:value-of select="date_debut"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date fin : <xsl:value-of select="date_fin"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
    <fo:list-block>
      <fo:list-item>
        <fo:list-item-label>
          <fo:block/>
        </fo:list-item-label>
        <fo:list-item-body>
          <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Produit épargne : <xsl:value-of select="produit_epargne"/></fo:block>
        </fo:list-item-body>
      </fo:list-item>
    </fo:list-block>
  </xsl:template>

  <xsl:template match="comptes">
    <fo:table-row>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block text-align="right">
          <xsl:value-of select="date_operation"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray" padding-right="3pt">
        <fo:block text-align="right">
          <xsl:value-of select="num_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="center">
          <xsl:value-of select="nom_client"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="interet_annuel"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block text-align="right">
          <xsl:value-of select="montant_impot"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>

  <xsl:template match="sous_total">
    <fo:table-row font-weight="bold">
      <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="3">
        <fo:block font-weight="bold" text-align="center"> Sous Total</fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="sous_total_interet_annuel"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell display-align="center" border="0.1pt solid gray">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="sous_total_montant_impot"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>




  <!--<xsl:template match="total">-->
    <!--<fo:table-row font-weight="bold">-->
      <!--<fo:table-cell display-align="center" border="0.2pt solid black" number-columns-spanned="3">-->
        <!--<fo:block font-weight="bold" text-align="center"> Total général</fo:block>-->
      <!--</fo:table-cell>-->
      <!--<fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">-->
        <!--<fo:block font-weight="bold" text-align="right">-->
          <!--<xsl:value-of select="total_interet_annuel"/>-->
        <!--</fo:block>-->
      <!--</fo:table-cell>-->
      <!--<fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">-->
        <!--<fo:block font-weight="bold" text-align="right">-->
          <!--<xsl:value-of select="total_montant_impot"/>-->
        <!--</fo:block>-->
      <!--</fo:table-cell>-->
    <!--</fo:table-row>-->
  <!--</xsl:template>-->


  <!--<xsl:template match="enreg_agence">-->
    <!--<fo:table-row>-->
      <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
        <!--<fo:block text-align="center">-->
          <!--<xsl:value-of select="id_ag"/>-->
        <!--</fo:block>-->
      <!--</fo:table-cell>-->
      <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
        <!--<fo:block text-align="center">-->
          <!--<xsl:value-of select="libel_ag"/>-->
        <!--</fo:block>-->
      <!--</fo:table-cell>-->
      <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
        <!--<fo:block text-align="center">-->
          <!--<xsl:value-of select="date_max"/>-->
        <!--</fo:block>-->
      <!--</fo:table-cell>-->
    <!--</fo:table-row>-->
  <!--</xsl:template>-->
</xsl:stylesheet>
