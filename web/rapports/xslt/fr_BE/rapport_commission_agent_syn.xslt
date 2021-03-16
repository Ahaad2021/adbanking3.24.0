<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_portrait"/>
            <xsl:apply-templates select="commission_agent"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
    </xsl:template>

    <xsl:template match="commission_agent">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:call-template name="titre_niv1">
                    <xsl:with-param name="titre" select="'Rapport synthétique'"/>
                </xsl:call-template>
                <xsl:call-template name="titre_niv1">
                    <xsl:with-param name="titre" select="concat('Devise: ', agent/devise)"/>
                </xsl:call-template>
                <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <!--<fo:table-column column-width="proportional-column-width(1)"/>-->
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <!--<fo:table-column column-width="proportional-column-width(1)"/>-->
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>

                    <fo:table-header>
                        <fo:table-row font-weight="bold">
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Nom agent</fo:block>
                            </fo:table-cell>
                            <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
                                <!--<fo:block text-align="center">Devise</fo:block>-->
                            <!--</fo:table-cell>-->
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Total montant dépôt</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Total montant retraits</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Total commissions pour agent</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Total commissions pour institution</fo:block>
                            </fo:table-cell>
                            <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
                                <!--<fo:block text-align="center">Total commissions sur nouveaux client</fo:block>-->
                            <!--</fo:table-cell>-->
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Total commissions</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Total clients créés</fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-header>
                    <fo:table-body>    </fo:table-body>
                </fo:table>
                <fo:table border-collapse="separate" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <!--<fo:table-column column-width="proportional-column-width(1)"/>-->
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <!--<fo:table-column column-width="proportional-column-width(1)"/>-->
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <!-- Affichage des infos -->
                    <fo:table-body>
                        <xsl:apply-templates select="agent"/>
                    </fo:table-body>
                </fo:table>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    <xsl:template match="agent">
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="nom_agent"/>
                </fo:block>
            </fo:table-cell>
            <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
                <!--<fo:block text-align="center">-->
                    <!--<xsl:value-of select="devise"/>-->
                <!--</fo:block>-->
            <!--</fo:table-cell>-->
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="total_mnt_depot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="total_mnt_retrait"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="total_mnt_comm_agent"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="total_mnt_comm_inst"/>
                </fo:block>
            </fo:table-cell>
            <!--<fo:table-cell display-align="center" border="0.1pt solid gray">-->
                <!--<fo:block text-align="center">-->
                    <!--<xsl:value-of select="total_mnt_comm_client"/>-->
                <!--</fo:block>-->
            <!--</fo:table-cell>-->
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="total_mnt_comm_agent_inst"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="total_client"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>
