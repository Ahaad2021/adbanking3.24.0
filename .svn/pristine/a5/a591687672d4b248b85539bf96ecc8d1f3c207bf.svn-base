<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:fo="http://www.w3.org/1999/XSL/Format"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage">
            </xsl:call-template>
            <xsl:apply-templates select="engraisChimiques_listbenefpayant"/>
        </fo:root>
    </xsl:template>

    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="engraisChimiques_listbenefpayant">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"></xsl:call-template>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="list_beneficiaires"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="list_beneficiaires">
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.1in" >
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <xsl:for-each select="nbre_colonne/colonne">
                <fo:table-column column-width="proportional-column-width(0.5)"/>
            </xsl:for-each>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold" font-size="10pt" border="0.1pt solid gray">
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">Province</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">Commune</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">Bureau/coopec</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">Zone</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">Colline</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">ID carte</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">Nom Bénéficiaire</fo:block>
                    </fo:table-cell>
                    <xsl:for-each select="nbre_colonne/colonne" >
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="text()"/><!--<xsl:value-of select="concat(text(),' (quantité - montant)')"/>--></fo:block>
                        </fo:table-cell>
                    </xsl:for-each>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">Montant</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
        <!--<xsl:for-each select="details_beneficiaires/zone">-->
            <!--<fo:block text-align="left" font-size="14pt" font-weight="bold" border-top-style="solid" border-bottom-style="solid" space-before="0.3in"><xsl:value-of select="nom_zone"/></fo:block>-->
            <!--<xsl:for-each select="colline">-->
                <!--<fo:block text-align="left" font-size="11pt" font-weight="bold" border-top-style="" border-bottom-style="" space-before="0.1in"> > <xsl:value-of select="nom_colline"/></fo:block> -->


                    <fo:table-body>
                        <xsl:for-each select="province">
                        <xsl:for-each select="commune">
                        <xsl:for-each select="coopec">
                        <xsl:for-each select="zone">
                        <xsl:for-each select="colline">
                        <xsl:for-each select="details_benef">
                            <fo:table-row font-size="9pt" border="0.1pt solid gray">
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="left"><xsl:value-of select="../../../../../nom_province"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="left"><xsl:value-of select="../../../../nom_commune"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="left"><xsl:value-of select="../../../nom_coopec"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="left"><xsl:value-of select="nom_zone1"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="left"><xsl:value-of select="nom_colline1"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="left"><xsl:value-of select="id_card"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="left"><xsl:value-of select="nom_benef"/></fo:block>
                                </fo:table-cell>
                                <xsl:for-each select="detail_produit/qty_produit">
                                    <fo:table-cell border="0.1pt solid gray">
                                        <fo:block text-align="right"><xsl:value-of select="text()"/></fo:block>
                                    </fo:table-cell>
                                </xsl:for-each>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="right"><xsl:value-of select="montant"/></fo:block>
                                </fo:table-cell>
                            </fo:table-row>
                        </xsl:for-each>
                        </xsl:for-each>
                        </xsl:for-each>
                        </xsl:for-each>
                        </xsl:for-each>
                        </xsl:for-each>
                    </fo:table-body>

      </fo:table>
    </xsl:template>
</xsl:stylesheet>
