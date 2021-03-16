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
                    <xsl:with-param name="titre" select="'Information synthétique'"/>
                </xsl:call-template>
                <xsl:apply-templates select="infos_syn"/>
                <xsl:apply-templates select="agent"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    <xsl:template match="infos_syn">
        <fo:list-block>
            <fo:list-item>
                <fo:list-item-label>
                    <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                    <fo:block text-align="left"><fo:inline font-family="ZapfDingbats">➥</fo:inline>Devise: <xsl:value-of select="devise"/></fo:block>
                    <fo:block text-align="left"><fo:inline font-family="ZapfDingbats">➥</fo:inline>Total montant dépôts: <xsl:value-of select="tot_depot"/></fo:block>
                    <fo:block text-align="left"><fo:inline font-family="ZapfDingbats">➥</fo:inline>Total montant retraits: <xsl:value-of select="tot_retrait"/></fo:block>
                    <fo:block text-align="left"><fo:inline font-family="ZapfDingbats">➥</fo:inline>Total commissions pour agent: <xsl:value-of select="tot_comm_agent"/></fo:block>
                    <xsl:if test="tot_comm_inst">
                        <fo:block text-align="left"><fo:inline font-family="ZapfDingbats">➥</fo:inline>Total commissions pour institution: <xsl:value-of select="tot_comm_inst"/></fo:block>
                    </xsl:if>
                    <xsl:if test="tot_commission">
                        <fo:block text-align="left"><fo:inline font-family="ZapfDingbats">➥</fo:inline>Total commissions: <xsl:value-of select="tot_commission"/></fo:block>
                    </xsl:if>
                    <fo:block text-align="left"><fo:inline font-family="ZapfDingbats">➥</fo:inline>Total clients créés: <xsl:value-of select="tot_client"/></fo:block>
                </fo:list-item-body>
            </fo:list-item>
        </fo:list-block>
    </xsl:template>
    <xsl:template match="agent">
        <xsl:if test="depot | retrait | client">
            <xsl:call-template name="titre_niv1"></xsl:call-template>
            <xsl:call-template name="titre_niv0">
                <xsl:with-param name="titre" select="concat('Agent ', nom_agent)"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="depot">
            <xsl:call-template name="titre_niv1">
                <xsl:with-param name="titre" select="'Type de transaction : Dépôt'"/>
            </xsl:call-template>
            <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                <fo:table-column column-width="proportional-column-width(0.5)"/>
                <fo:table-column column-width="proportional-column-width(1)"/>
                <fo:table-column column-width="proportional-column-width(1.5)"/>
                <fo:table-column column-width="proportional-column-width(2.5)"/>
                <fo:table-column column-width="proportional-column-width(1)"/>
                <xsl:if test="depot/his/mnt_comm_inst">
                    <fo:table-column column-width="proportional-column-width(1.3)"/>
                </xsl:if>
                <xsl:if test="depot/his/mnt_comm_agent_inst">
                    <fo:table-column column-width="proportional-column-width(1.3)"/>
                </xsl:if>
                <!--            <xsl:if test="his/mnt_comm_client">-->
                <!--                <fo:table-column column-width="proportional-column-width(1.3)"/>-->
                <!--            </xsl:if>-->
                <fo:table-column column-width="proportional-column-width(1.3)"/>
                <fo:table-header>
                    <fo:table-row font-weight="bold">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">N°</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Date</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Compte client</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Nom client</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Montant</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Commission pour l’agent</fo:block>
                        </fo:table-cell>
                        <xsl:if test="depot/his/mnt_comm_inst">
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Commission pour l’institution</fo:block>
                            </fo:table-cell>
                        </xsl:if>
<!--                        <xsl:if test="depot/his/mnt_comm_client">-->
<!--                            <fo:table-cell display-align="center" border="0.1pt solid gray">-->
<!--                                <fo:block text-align="center">Commission sur nouveaux client</fo:block>-->
<!--                            </fo:table-cell>-->
<!--                        </xsl:if>-->
                        <xsl:if test="depot/his/mnt_comm_agent_inst">
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Commission total</fo:block>
                            </fo:table-cell>
                        </xsl:if>
                    </fo:table-row>
                </fo:table-header>
                <fo:table-body>    </fo:table-body>
            </fo:table>
            <xsl:apply-templates select="depot"/>
        </xsl:if>
        <xsl:if test="retrait">
            <xsl:call-template name="titre_niv1">
                <xsl:with-param name="titre" select="'Type de transaction : Retrait'"/>
            </xsl:call-template>
            <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                <fo:table-column column-width="proportional-column-width(0.5)"/>
                <fo:table-column column-width="proportional-column-width(1)"/>
                <fo:table-column column-width="proportional-column-width(1.5)"/>
                <fo:table-column column-width="proportional-column-width(2.5)"/>
                <fo:table-column column-width="proportional-column-width(1)"/>
                <xsl:if test="retrait/his/mnt_comm_inst">
                    <fo:table-column column-width="proportional-column-width(1.3)"/>
                </xsl:if>
                <xsl:if test="retrait/his/mnt_comm_agent_inst">
                    <fo:table-column column-width="proportional-column-width(1.3)"/>
                </xsl:if>
<!--                <xsl:if test="retrait/his/mnt_comm_client">-->
<!--                    <fo:table-column column-width="proportional-column-width(1.3)"/>-->
<!--                </xsl:if>-->
                <fo:table-column column-width="proportional-column-width(1.3)"/>
                <fo:table-header>
                    <fo:table-row font-weight="bold">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">N°</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Date</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Compte client</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Nom client</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Montant</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Commission pour l’agent</fo:block>
                        </fo:table-cell>
                        <xsl:if test="retrait/his/mnt_comm_inst">
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Commission pour l’institution</fo:block>
                            </fo:table-cell>
                        </xsl:if>
<!--                        <xsl:if test="retrait/his/mnt_comm_client">-->
<!--                            <fo:table-cell display-align="center" border="0.1pt solid gray">-->
<!--                                <fo:block text-align="center">Commission sur nouveaux client</fo:block>-->
<!--                            </fo:table-cell>-->
<!--                        </xsl:if>-->
                        <xsl:if test="retrait/his/mnt_comm_agent_inst">
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Commission total</fo:block>
                            </fo:table-cell>
                        </xsl:if>
                    </fo:table-row>
                </fo:table-header>
                <fo:table-body>    </fo:table-body>
            </fo:table>
            <xsl:apply-templates select="retrait"/>
        </xsl:if>
        <xsl:if test="client">
            <xsl:call-template name="titre_niv1">
                <xsl:with-param name="titre" select="'Type de transaction : Création client'"/>
            </xsl:call-template>
            <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                <fo:table-column column-width="proportional-column-width(0.5)"/>
                <fo:table-column column-width="proportional-column-width(1)"/>
                <fo:table-column column-width="proportional-column-width(1.5)"/>
                <fo:table-column column-width="proportional-column-width(2.5)"/>
                <fo:table-column column-width="proportional-column-width(1)"/>
                <xsl:if test="client/his/mnt_comm_inst">
                    <fo:table-column column-width="proportional-column-width(1.3)"/>
                </xsl:if>
                <xsl:if test="client/his/mnt_comm_agent_inst">
                    <fo:table-column column-width="proportional-column-width(1.3)"/>
                </xsl:if>
<!--                <xsl:if test="client/his/mnt_comm_client">-->
<!--                    <fo:table-column column-width="proportional-column-width(1.3)"/>-->
<!--                </xsl:if>-->
                <fo:table-column column-width="proportional-column-width(1.3)"/>
                <fo:table-header>
                    <fo:table-row font-weight="bold">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">N°</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Date</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Compte client</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Nom client</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Montant initial</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Commission pour l’agent</fo:block>
                        </fo:table-cell>
                        <xsl:if test="client/his/mnt_comm_inst">
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Commission pour l’institution</fo:block>
                            </fo:table-cell>
                        </xsl:if>
<!--                        <xsl:if test="client/his/mnt_comm_client">-->
<!--                            <fo:table-cell display-align="center" border="0.1pt solid gray">-->
<!--                                <fo:block text-align="center">Commission sur nouveaux client</fo:block>-->
<!--                            </fo:table-cell>-->
<!--                        </xsl:if>-->
                        <xsl:if test="client/his/mnt_comm_agent_inst">
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="center">Commission total</fo:block>
                            </fo:table-cell>
                        </xsl:if>
                    </fo:table-row>
                </fo:table-header>
                <fo:table-body>    </fo:table-body>
            </fo:table>
            <xsl:apply-templates select="client"/>
        </xsl:if>
    </xsl:template>
    <xsl:template match="depot | retrait | client">
        <fo:table border-collapse="separate" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <fo:table-column column-width="proportional-column-width(2.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>

            <fo:table-column column-width="proportional-column-width(1.3)"/>
            <xsl:if test="his/mnt_comm_inst">
                <fo:table-column column-width="proportional-column-width(1.3)"/>
            </xsl:if>
<!--            <xsl:if test="his/mnt_comm_client">-->
<!--                <fo:table-column column-width="proportional-column-width(1.3)"/>-->
<!--            </xsl:if>-->
            <xsl:if test="his/mnt_comm_agent_inst">
                <fo:table-column column-width="proportional-column-width(1.3)"/>
            </xsl:if>
            <!-- Affichage des infos -->
            <fo:table-body>
                <xsl:apply-templates select="his"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>
    <xsl:template match="his">
        <xsl:choose>
            <xsl:when test="num_transac='Totaux'">
                <fo:table-row>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned = '4'>
                        <fo:block text-align="center" font-weight="bold">
                            <xsl:value-of select="num_transac"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center" font-weight="bold">
                            <xsl:value-of select="mnt_transac"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center" font-weight="bold">
                            <xsl:value-of select="mnt_comm_agent"/>
                        </fo:block>
                    </fo:table-cell>
                    <xsl:if test="mnt_comm_inst">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center" font-weight="bold">
                                <xsl:value-of select="mnt_comm_inst"/>
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
<!--                    <xsl:if test="mnt_comm_client">-->
<!--                        <fo:table-cell display-align="center" border="0.1pt solid gray">-->
<!--                            <fo:block text-align="center" font-weight="bold">-->
<!--                                <xsl:value-of select="mnt_comm_client"/>-->
<!--                            </fo:block>-->
<!--                        </fo:table-cell>-->
<!--                    </xsl:if>-->
                    <xsl:if test="mnt_comm_agent_inst">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center" font-weight="bold">
                                <xsl:value-of select="mnt_comm_agent_inst"/>
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                </fo:table-row>
            </xsl:when>
            <xsl:otherwise>
                <fo:table-row>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="num_transac"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="date_mod"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="num_complet_cpte"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="nom_client"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="mnt_transac"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="mnt_comm_agent"/>
                        </fo:block>
                    </fo:table-cell>
                    <xsl:if test="mnt_comm_inst">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="mnt_comm_inst"/>
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
<!--                    <xsl:if test="mnt_comm_client">-->
<!--                        <fo:table-cell display-align="center" border="0.1pt solid gray">-->
<!--                            <fo:block text-align="center">-->
<!--                                <xsl:value-of select="mnt_comm_client"/>-->
<!--                            </fo:block>-->
<!--                        </fo:table-cell>-->
<!--                    </xsl:if>-->
                    <xsl:if test="mnt_comm_agent_inst">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">
                                <xsl:value-of select="mnt_comm_agent_inst"/>
                            </fo:block>
                        </fo:table-cell>
                    </xsl:if>
                </fo:table-row>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
