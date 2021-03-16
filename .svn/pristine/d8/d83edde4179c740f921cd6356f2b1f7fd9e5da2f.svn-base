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
            <xsl:call-template name="page_layout_A4_paysage" />
            <xsl:apply-templates select="calc_int_recevoir" />
        </fo:root>
    </xsl:template>

    <xsl:template match="calc_int_recevoir">
        <fo:page-sequence master-reference="main" font-size="6pt" font-family="Helvetica">
            <xsl:apply-templates select="header" />
            <xsl:call-template name="footer" />
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel" />
                <xsl:apply-templates select="infos_synthetique"/>
                <xsl:apply-templates select="calc_int_recevoir_data" />
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <!-- Infos synthetique -->
    <xsl:template match="infos_synthetique">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Informations synthétiques'"/>
        </xsl:call-template>

        <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="35%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>

            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell>
                        <fo:block text-align="left">Total intérêts à recevoir :</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="left"><xsl:value-of select="total_int_recevoir"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>

            <fo:table-body>
                <fo:table-row>
                    <fo:table-cell><fo:block text-align="left"></fo:block></fo:table-cell>
                    <fo:table-cell><fo:block text-align="left"></fo:block></fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>

    </xsl:template>


    <!-- Body -->
    <xsl:template match="calc_int_recevoir_data">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre">Détails</xsl:with-param>
        </xsl:call-template>

        <!-- Loop each product -->
        <xsl:for-each select="prod">
            <xsl:call-template name="titre_niv2">
                <xsl:with-param name="titre"><xsl:value-of select="libel"/></xsl:with-param>
            </xsl:call-template>

            <fo:table border-collapse="collapse" width="100%" table-layout="fixed">
                <fo:table-column column-width="proportional-column-width(1)" />
                <fo:table-column column-width="proportional-column-width(1)" />
                <fo:table-column column-width="proportional-column-width(1.5)" />
                <fo:table-column column-width="proportional-column-width(1.25)" />
                <fo:table-column column-width="proportional-column-width(1.5)" />
                <fo:table-column column-width="proportional-column-width(1.5)" />
                <fo:table-column column-width="proportional-column-width(1)" />
                <fo:table-column column-width="proportional-column-width(1)" />
                <fo:table-column column-width="proportional-column-width(1)" />
                <fo:table-column column-width="proportional-column-width(1)" />
                <fo:table-column column-width="proportional-column-width(1)" />

                <fo:table-header>
                    <!-- Empty row -->
                    <fo:table-row column-number="8">
                        <fo:table-cell display-align="center">
                            <fo:block text-align="left"> <fo:leader /> </fo:block>
                        </fo:table-cell>
                    </fo:table-row>

                    <fo:table-row font-weight="bold">
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">N° du dossier</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">N° du client</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Nom du client</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Date de déblocage</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Capital restant du</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Intérêts attendus sur l'échéance</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Date début théorique</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Nombre de jours échus des intérêts à recevoir </fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Intérêts à recevoir sur l'échéance</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Intérêts à recevoir non  payer sur les échéances précédentes </fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="center">Intérêts à recevoir cumulés</fo:block>
                        </fo:table-cell>

                    </fo:table-row>
                </fo:table-header>

                <fo:table-body>
                    <xsl:for-each select="ligne_int_recevoir">
                        <fo:table-row>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="left"><xsl:value-of select="num_dossier" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="left"><xsl:value-of select="num_client" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="left"><xsl:value-of select="nom_client" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="left"><xsl:value-of select="date_debloc" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="right"><xsl:value-of select="capital" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="right"><xsl:value-of select="int_att_ech" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="right"><xsl:value-of select="date_th" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="right"><xsl:value-of select="nb_jours" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="right"><xsl:value-of select="montant_int" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="right"><xsl:value-of select="int_non_paye" /></fo:block>
                            </fo:table-cell>
                            <fo:table-cell display-align="center" border="0.1pt solid gray">
                                <fo:block text-align="right"><xsl:value-of select="iar_cumule" /></fo:block>
                            </fo:table-cell>

                        </fo:table-row>
                    </xsl:for-each>

                    <fo:table-row font-weight="bold">
                        <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned = "3">
                            <fo:block text-align="center"  font-weight = "bold">Totals</fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left"></fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="totals/total_cap_restant_du" /></fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="totals/total_int_attendu_ech" /></fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left"></fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="left"></fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="totals/total_iar_echeance" /></fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="totals/total_int_non_paye" /></fo:block>
                        </fo:table-cell>
                        <fo:table-cell display-align="center" border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="totals/total_int_recevoir" /></fo:block>
                        </fo:table-cell>
                    </fo:table-row>


                </fo:table-body>

            </fo:table>
        </xsl:for-each>



    </xsl:template>

</xsl:stylesheet>
