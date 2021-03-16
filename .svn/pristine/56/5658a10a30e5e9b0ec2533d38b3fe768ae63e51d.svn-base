<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="inventaire_credit"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="informations_synthetiques.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="inventaire_credit">
        <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <!-- <xsl:apply-templates select="header" mode="no_region"/> -->
                <fo:block space-before.optimum="1cm"/>
                <xsl:apply-templates select="header_contextuel"/>
                <fo:block space-before.optimum="1cm"/>
                <xsl:apply-templates select="body"/>
                <fo:block space-before.optimum="1cm"/>
                <!-- <xsl:call-template name="footer"></xsl:call-template> -->
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="body">

        <xsl:for-each select="produit_credit">
            <xsl:call-template name="titre_niv1">
                <xsl:with-param name="titre"><xsl:value-of select="credit"/></xsl:with-param>
            </xsl:call-template>
            <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">

                <fo:table-column column-width="proportional-column-width(3)"/>
                <fo:table-column column-width="proportional-column-width(8)"/>
                <fo:table-column column-width="proportional-column-width(8)"/>
                <fo:table-column column-width="proportional-column-width(6)"/>
                <fo:table-column column-width="proportional-column-width(6)"/>
                <fo:table-column column-width="proportional-column-width(6)"/>
                <fo:table-column column-width="proportional-column-width(6)"/>
                <fo:table-column column-width="proportional-column-width(6)"/>
                <fo:table-column column-width="proportional-column-width(4)"/>
                <fo:table-column column-width="proportional-column-width(4.5)"/>
                <xsl:if test="etat_tous = 'true'">
                    <fo:table-column column-width="proportional-column-width(3)"/>
                </xsl:if>

                <fo:table-header>
                    <fo:table-row>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">Client Number</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">File Number</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">Client name</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">Capital start period</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">Capital disbursed during period</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">Capital paid during period</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">Ordinary interest paid during period</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">Delayed interest paid during period</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                            <fo:block text-align="center">Total amount paid during period</fo:block>
                        </fo:table-cell>
                        <xsl:if test="etat_radie = 'false'">
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Remaining Capital at term</fo:block>
                            </fo:table-cell>
                        </xsl:if>
                        <xsl:if test="etat_radie = 'true'">
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Capital Lost</fo:block>
                            </fo:table-cell>
                        </xsl:if>
                        <xsl:if test="etat_tous = 'true'">
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Status</fo:block>
                            </fo:table-cell>
                        </xsl:if>
                    </fo:table-row>
                </fo:table-header>

                <fo:table-body>
                    <xsl:apply-templates select="ligne_credit"/>
                    <xsl:apply-templates select="totals"/>
                </fo:table-body>

            </fo:table>

        </xsl:for-each>
    </xsl:template>
    <xsl:template match="ligne_produit">
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="num_client"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center" font-size="9pt"><xsl:value-of select="num_dossier"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center" font-size="9pt">    <xsl:value-of select="nom_client"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="right" border="0.1pt solid gray">
                <fo:block text-align="right" font-size="9pt"><xsl:value-of select="cap_deb_prd"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="right" border="0.1pt solid gray">
                <fo:block text-align="right" font-size="9pt"><xsl:value-of select="cap_deb"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="right" border="0.1pt solid gray">
                <fo:block text-align="right" font-size="9pt">    <xsl:value-of select="cap_remb_en_cours_period"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="right" border="0.1pt solid gray">
                <fo:block text-align="right" font-size="9pt"><xsl:value-of select="interet_ord_remb_en_cours_period"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="right" border="0.1pt solid gray">
                <fo:block text-align="right" font-size="9pt"><xsl:value-of select="interet_ret_remb_en_cours_period"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="right" border="0.1pt solid gray">
                <fo:block text-align="right" font-size="9pt">    <xsl:value-of select="mnt_total_remb_en_cours_period"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="right" border="0.1pt solid gray">
                <fo:block text-align="right" font-size="9pt"><xsl:value-of select="cap_rest_du_fin_period"/></fo:block>
            </fo:table-cell>
            <xsl:if test="../etat_tous = 'true'">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center" font-size="9pt"><xsl:value-of select="etat_dossier"/></fo:block>
                </fo:table-cell>
            </xsl:if>



        </fo:table-row>
    </xsl:template>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="informations_synthetiques"/>
    </xsl:template>
    <xsl:template match="totals">
        <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.1pt solid black"  number-columns-spanned="3">
                <fo:block font-weight="bold" text-align="right"> TOTAUX </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" display-align="right" padding-after="2pt">
                <fo:block font-weight="bold" text-align="right" font-size="9pt">
                    <xsl:value-of select="tot_cap_deb_prd"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" display-align="right" padding-after="2pt">
                <fo:block font-weight="bold" text-align="right" font-size="9pt">
                    <xsl:value-of select="tot_cap_deb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" display-align="right" padding-after="2pt">
                <fo:block font-weight="bold" text-align="right" font-size="9pt">
                    <xsl:value-of select="tot_cap_remb_en_cours_period"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" display-align="right" padding-after="2pt">
                <fo:block font-weight="bold" text-align="right" font-size="9pt">
                    <xsl:value-of select="tot_interet_ord_remb_en_cours_period"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" display-align="right" padding-after="2pt">
                <fo:block font-weight="bold" text-align="right" font-size="9pt">
                    <xsl:value-of select="tot_interet_ret_remb_en_cours_period"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" display-align="right" padding-after="2pt">
                <fo:block font-weight="bold" text-align="right" font-size="9pt">
                    <xsl:value-of select="tot_mnt_total_remb_en_cours_period"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" display-align="right" padding-after="2pt">
                <fo:block font-weight="bold" text-align="right" font-size="9pt">
                    <xsl:value-of select="tot_cap_rest_du_fin_period"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>
