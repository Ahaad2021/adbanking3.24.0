<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

    <xsl:include href="page_layout.xslt" />
    <xsl:include href="header.xslt" />
    <xsl:include href="criteres_recherche.xslt" />
    <xsl:include href="footer.xslt" />
    <xsl:include href="lib.xslt" />

    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_portrait" />
            <xsl:apply-templates select="situation_compensation_siege" />
        </fo:root>
    </xsl:template>

    <xsl:template match="situation_compensation_siege">
        <fo:page-sequence master-reference="main" font-size="6pt" font-family="Helvetica">
            <xsl:apply-templates select="header" />
            <xsl:call-template name="footer" />
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel" />
                <xsl:apply-templates select="situation_agence" />
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>


    <xsl:template match="situation_agence">

        <fo:block text-align="left" font-size="9pt" space-before="0.3in">
            SOLDE DEBUT PERIODE COMPTE DE LIAISON
            <fo:inline font-weight="bold">
                <xsl:value-of select="compensations_par_agence/situation_local/cpte_liaison"/>
            </fo:inline> <xsl:value-of select="compensations_par_agence/nom_agence"/>:
            <fo:inline font-weight="bold" text-decoration="underline">
                <xsl:value-of select="../solde_deb"/>
            </fo:inline>
        </fo:block>

        <xsl:for-each select="compensations_par_agence">

            <fo:block text-align="left" font-size="11pt" font-weight="bold" text-decoration="underline" width="100%" space-before="0.3in">
                <xsl:value-of select="title"/>
            </fo:block>

            <fo:table border-collapse="collapse" width="100%" table-layout="fixed" space-before="0.1in">
                <fo:table-column column-width="proportional-column-width(5)" />
                <fo:table-column column-width="proportional-column-width(2)" />

                <fo:table-header>
                    <fo:table-row font-weight="bold" font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray" background-color="lightgrey" number-columns-spanned="2">
                            <fo:block text-align="center">
                                Opérations distantes à
                                <xsl:value-of select="situation_local/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                </fo:table-header>

                <fo:table-body>
                    <!-- total depots distants-->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                Total Depots à Agence
                                <xsl:value-of select="situation_distant/nom_agence_distant" />&#160;
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right">
                                <xsl:value-of select="situation_distant/total_depot" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- total retraits distantes-->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                Total Rétraits à Agence
                                <xsl:value-of select="situation_distant/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right">
                                <xsl:value-of select="situation_distant/total_retrait" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- Ajout des montants OD pour retraits/depots -->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                Total Commissions sur depot à Agence
                                <xsl:value-of select="situation_distant/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right">
                                <xsl:value-of select="situation_distant/comm_od_depot_distant" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                Total Commissions sur retrait à Agence
                                <xsl:value-of select="situation_distant/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right">
                                <xsl:value-of select="situation_distant/comm_od_retrait_distant" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- -->


                    <!-- solde operations distantes-->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left" font-weight="bold">
                                Solde Opérations distantes à
                                <xsl:value-of select="situation_distant/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right" font-weight="bold">
                                <xsl:value-of select="situation_distant/solde_operation_distant" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- header local -->
                    <fo:table-row font-weight="bold" font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray" background-color="lightgrey" number-columns-spanned="2">
                            <fo:block text-align="center">
                                Opérations locales pour
                                <xsl:value-of select="situation_local/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- total depots locales-->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                Total Depots pour Agence
                                <xsl:value-of select="situation_local/nom_agence_distant" />&#160;
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right">
                                <xsl:value-of select="situation_local/total_depot" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- total retraits locales-->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                Total Rétraits pour Agence
                                <xsl:value-of select="situation_local/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right">
                                <xsl:value-of select="situation_local/total_retrait" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- Ajout des montants OD pour retraits/depots locale -->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                Total Commissions sur depot à Agence
                                <xsl:value-of select="situation_local/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right">
                                <xsl:value-of select="situation_local/comm_od_depot_local" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left">
                                Total Commissions sur retrait à Agence
                                <xsl:value-of select="situation_local/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right">
                                <xsl:value-of select="situation_local/comm_od_retrait_local" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- -->

                    <!-- solde operations locales-->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left" font-weight="bold">
                                Solde Opérations locales pour
                                <xsl:value-of select="situation_local/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right" font-weight="bold">
                                <xsl:value-of select="situation_local/solde_operation_local" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                    <!-- solde compensation-->
                    <fo:table-row font-size="9pt">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left" font-weight="bold">
                                Solde Compensation avec Agence
                                <xsl:value-of select="situation_local/nom_agence_distant" />
                            </fo:block>
                        </fo:table-cell>

                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right" font-weight="bold">
                                <xsl:value-of select="situation_local/solde_compensation_local" />
                            </fo:block>
                        </fo:table-cell>

                    </fo:table-row>

                </fo:table-body>

            </fo:table>

            <fo:table border-collapse="collapse" font-size="9pt" width="100%" table-layout="fixed">
                <fo:table-column column-width="proportional-column-width(1)" />

                <fo:table-body>
                    <fo:table-row  font-weight="bold">
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left">
                                <xsl:value-of select="synthese" />
                            </fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                </fo:table-body>
            </fo:table>

        </xsl:for-each>

        <!--solde compensation globale, autres mouvements debit et autres mouvements credit -->

        <fo:table border-collapse="collapse" width="100%" table-layout="fixed" space-before="0.2in">
            <fo:table-column column-width="proportional-column-width(5)" />
            <fo:table-column column-width="proportional-column-width(2)" />

            <fo:table-body>
                <!--solde compensation globale -->
                <fo:table-row  font-weight="bold" font-size="9pt">
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">
                            Solde Compensation Globale (
                            <xsl:value-of select="../devise" />
                             )
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="right">
                            <xsl:value-of select="../solde_compensation_global" />
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>

        <fo:table border-collapse="collapse" width="100%" table-layout="fixed" space-before="0.1in">
            <fo:table-column column-width="proportional-column-width(5)" />
            <fo:table-column column-width="proportional-column-width(2)" />

            <fo:table-body>

                <!--Autres mouvements debit -->
                <fo:table-row font-size="9pt">
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">
                            Autres mouvements débiteurs
                            <xsl:value-of select="compensations_par_agence/situation_local/cpte_liaison" />
                             ( <xsl:value-of select="../devise" /> )
                            <xsl:value-of select="compensations_par_agence/nom_agence" />
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="right">
                            <xsl:value-of select="../mvmts_deb" />
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>

                <!--Autres mouvements credit -->
                <fo:table-row font-size="9pt">
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="left">
                            Autres mouvements créditeurs
                            <xsl:value-of select="compensations_par_agence/situation_local/cpte_liaison" />
                            ( <xsl:value-of select="../devise" /> )
                            <xsl:value-of select="compensations_par_agence/nom_agence" />
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="right">
                            <xsl:value-of select="../mvmts_cred" />
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>

        <fo:block text-align="left" font-size="9pt" space-before="0.3in">
            SOLDE FIN PERIODE COMPTE DE LIAISON
            <fo:inline font-weight="bold">
                <xsl:value-of select="compensations_par_agence/situation_local/cpte_liaison"/>
            </fo:inline> <xsl:value-of select="compensations_par_agence/nom_agence"/>:
            <fo:inline font-weight="bold" text-decoration="underline">
                <xsl:value-of select="../solde_fin"/>
            </fo:inline>
        </fo:block>

    </xsl:template>
</xsl:stylesheet>
