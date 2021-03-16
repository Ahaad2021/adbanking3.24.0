<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="inventaire_depot"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="informations_synthetiques.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="inventaire_depot">
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

            <xsl:for-each select="produit_epargne">
                <xsl:call-template name="titre_niv1">
                    <xsl:with-param name="titre"><xsl:value-of select="epargne"/></xsl:with-param>
                </xsl:call-template>
                <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">

                    <fo:table-column column-width="proportional-column-width(2)"/>
                    <fo:table-column column-width="proportional-column-width(8)"/>
                    <fo:table-column column-width="proportional-column-width(8)"/>
                    <fo:table-column column-width="proportional-column-width(2)"/>
                    <fo:table-column column-width="proportional-column-width(6)"/>
                    <fo:table-column column-width="proportional-column-width(6)"/>
                    <fo:table-column column-width="proportional-column-width(6)"/>
                    <fo:table-column column-width="proportional-column-width(6)"/>
                    <fo:table-column column-width="proportional-column-width(4)"/>
                    <fo:table-column column-width="proportional-column-width(4.5)"/>
                    <fo:table-column column-width="proportional-column-width(5)"/>
                    <!--  <fo:table-column column-width="proportional-column-width(2)"/>
                      <fo:table-column column-width="proportional-column-width(3)"/>-->
                    <fo:table-column column-width="proportional-column-width(5)"/>
                    <fo:table-column column-width="proportional-column-width(8)"/>
                    <fo:table-header>
                        <fo:table-row>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">N0</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Account No</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Client name</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">gender</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">balance start period</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">total deposit</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Total Withdrawal</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">balance end period</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">DOB</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">civil status</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Sector</fo:block>
                            </fo:table-cell>
                         <!--   <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Cell</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Village</fo:block>
                            </fo:table-cell>-->
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">Telephone</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1">
                                <fo:block text-align="center">IdNumber</fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-header>

                    <fo:table-body>
                        <xsl:apply-templates select="ligne_produit"/>
                        <xsl:apply-templates select="totals"/>
                    </fo:table-body>

                </fo:table>

            </xsl:for-each>
    </xsl:template>
    <xsl:template match="ligne_produit">
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="num"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="num_cpte"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">    <xsl:value-of select="nom_client"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="sexe"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="solde_debut_periode"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">    <xsl:value-of select="total_mouvement_depot"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="total_mouvement_retrait"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="solde_fin_periode"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">    <xsl:value-of select="date_naissance"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="etat_civile"/></fo:block>
            </fo:table-cell>
            <!--      <fo:table-cell display-align="center" border="0.1pt solid gray">
                      <fo:block text-align="center"><xsl:value-of select="sector"/></fo:block>
                  </fo:table-cell>
                  <fo:table-cell display-align="center" border="0.1pt solid gray">
                      <fo:block text-align="center">    <xsl:value-of select="cell"/></fo:block>
                  </fo:table-cell>-->
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="village"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center"><xsl:value-of select="tel"/></fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">    <xsl:value-of select="idnumber"/></fo:block>
            </fo:table-cell>

        </fo:table-row>
    </xsl:template>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="informations_synthetiques"/>
    </xsl:template>
    <xsl:template match="totals">
        <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.2pt solid black"  number-columns-spanned="4">
                <fo:block font-weight="bold" text-align="center"> TOTAUX </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
                <fo:block font-weight="bold" text-align="center">
                    <xsl:value-of select="tot_solde_debut"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
                <fo:block font-weight="bold" text-align="center">
                    <xsl:value-of select="tot_mouvement_depot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
                <fo:block font-weight="bold" text-align="center">
                    <xsl:value-of select="tot_mouvement_retrait"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
                <fo:block font-weight="bold" text-align="center">
                    <xsl:value-of select="tot_solde_fin"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>
