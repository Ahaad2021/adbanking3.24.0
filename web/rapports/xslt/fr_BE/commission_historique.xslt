<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_portrait"/>
            <xsl:apply-templates select="commission_hist"/>
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

    <xsl:template match="commission_hist">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="depot | retrait | client"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="depot | retrait  | client">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="concat('Commission sur ', his/type_comm)"/>
        </xsl:call-template>
        <xsl:apply-templates select="his"/>
    </xsl:template>

    <xsl:template match="his">
        <xsl:choose>
            <xsl:when test="type_comm='nouveaux client'">
                <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-header>
                        <fo:table-row font-weight="bold">
                            <fo:table-cell display-align="after" border="0.1pt solid gray">
                                <fo:block text-align="center">Date de modification</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="after" border="0.1pt solid gray">
                                <fo:block text-align="center">Commission pour agent</fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-header>
                    <fo:table-body>    </fo:table-body>
                </fo:table>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="titre_niv1">
                    <xsl:with-param name="titre" select="concat('Date modification: ', date_mod)"/>
                </xsl:call-template>
                <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(4)"/>
                    <fo:table-column column-width="proportional-column-width(3)"/>
                    <fo:table-column column-width="proportional-column-width(3)"/>
                    <fo:table-column column-width="proportional-column-width(3)"/>
                    <fo:table-header>
                        <fo:table-row font-weight="bold">
                            <fo:table-cell display-align="after" border-top="0.1pt solid gray" border-left="0.1pt solid gray" border-right="0.1pt solid gray">
                                <fo:block text-align="center">ID PALIER</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">PALIER</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">COMMISSION POUR AGENT</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">COMMISSION POUR L'INSTITUTION</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">COMMISSION TOTAL</fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-header>
                    <fo:table-body>    </fo:table-body>
                </fo:table>
                <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(2)"/>
                    <fo:table-column column-width="proportional-column-width(2)"/>
                    <fo:table-column column-width="proportional-column-width(1.5)"/>
                    <fo:table-column column-width="proportional-column-width(1.5)"/>
                    <fo:table-column column-width="proportional-column-width(1.5)"/>
                    <fo:table-column column-width="proportional-column-width(1.5)"/>
                    <fo:table-column column-width="proportional-column-width(1.5)"/>
                    <fo:table-column column-width="proportional-column-width(1.5)"/>
                    <fo:table-header>
                        <fo:table-row font-weight="bold">
                            <fo:table-cell display-align="center" border-left="0.1pt solid gray" border-right="0.1pt solid gray" border-bottom="0.1pt solid gray">
                                <fo:block text-align="center"></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Montant minimum</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Montant maximum</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">En pourcentage</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Montant fixe</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">En pourcentage</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Montant fixe</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">En pourcentage</fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Montant fixe</fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-header>
                    <fo:table-body>    </fo:table-body>
                </fo:table>
            </xsl:otherwise>
        </xsl:choose>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
            <xsl:if test="palier/id_palier">
                <fo:table-column column-width="proportional-column-width(1)"/>
                <fo:table-column column-width="proportional-column-width(2)"/>
                <fo:table-column column-width="proportional-column-width(2)"/>
                <fo:table-column column-width="proportional-column-width(1.5)"/>
                <fo:table-column column-width="proportional-column-width(1.5)"/>
                <fo:table-column column-width="proportional-column-width(1.5)"/>
                <fo:table-column column-width="proportional-column-width(1.5)"/>
            </xsl:if>
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <fo:table-body>
                <xsl:apply-templates select="palier"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>

    <xsl:template match="palier">
        <fo:table-row>
            <xsl:if test="date_creation">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="date_creation"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="id_palier">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="id_palier"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="mnt_min">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="right">
                        <xsl:value-of select="mnt_min"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="mnt_max">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="right">
                        <xsl:value-of select="mnt_max"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="comm_agent_prc">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="comm_agent_prc"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="comm_agent_mnt">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="comm_agent_mnt"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="comm_inst_prc">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="comm_inst_prc"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="comm_inst_mnt">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="comm_inst_mnt"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="comm_tot_prc">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="comm_tot_prc"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
            <xsl:if test="comm_tot_mnt">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="comm_tot_mnt"/>
                    </fo:block>
                </fo:table-cell>
            </xsl:if>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>
