<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_portrait"/>
            <xsl:apply-templates select="creation_client"/>
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

    <xsl:template match="creation_client">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:call-template name="titre_niv1"></xsl:call-template>
                <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(0.5)"/>
                    <fo:table-column column-width="proportional-column-width(2)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>

                    <fo:table-header>
                        <fo:table-row font-weight="bold">
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Id Transaction</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">N° client</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Nom client</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Statut juridique</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Etat</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Login agent</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Date creation</fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-header>
                    <fo:table-body>    </fo:table-body>
                </fo:table>
                <fo:table border-collapse="separate" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(0.5)"/>
                    <fo:table-column column-width="proportional-column-width(2)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <!-- Affichage des infos -->
                    <fo:table-body>
                        <xsl:apply-templates select="client"/>
                    </fo:table-body>
                </fo:table>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    <xsl:template match="client">
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="id_trans"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="id_client"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="nom_client"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="statut_juridique"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="etat"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="nom_agent"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="date_creation"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>
