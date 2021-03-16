<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
                version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="his_ligne_credit"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="his_ligne_credit">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="infos_synthetiques"/>
                <xsl:apply-templates select="ligneCredit"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <!-- Start : infos_synthetique -->
    <xsl:template match="infos_synthetiques">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Informations synthétiques'"/>
        </xsl:call-template>

        <fo:list-block>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Client number : <xsl:value-of select="num_client"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Client name : <xsl:value-of select="nom_client"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Credit number : <xsl:value-of select="num_doss"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        State : <xsl:value-of select="etat"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Date request : <xsl:value-of select="date_dem"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Date approval : <xsl:value-of select="date_approb"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Credit product : <xsl:value-of select="libel_prod"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Amount granted : <xsl:value-of select="montant_octroye"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Currency : <xsl:value-of select="devise"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Interest rate : <xsl:value-of select="taux_interet"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Fee rate : <xsl:value-of select="taux_frais"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Deadline date : <xsl:value-of select="date_fin_ech"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

        </fo:list-block>

    </xsl:template>
    <!-- End : infos_synthetique -->

    <xsl:template match="ligneCredit">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre">
                <xsl:value-of select="lib_prod"/>
            </xsl:with-param>
        </xsl:call-template>
        <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%"
                  table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell>
                        <fo:block text-align="center">Date</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">Amount disbursed</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">Principal repaid</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">Interest paid</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">Fees reimbursed</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">Penalties paid</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="right">Remaining capital</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <fo:table-row>
                    <fo:table-cell number-columns-spanned="7">
                        <fo:block font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0.2cm" font-weight="bold" border-bottom-width="0.2pt" border-bottom-style="solid" border-bottom-color="black"></fo:block>
                    </fo:table-cell>
                </fo:table-row>
                <xsl:apply-templates select="infosCredit"/>
                <xsl:apply-templates select="xml_total"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>

    <xsl:template match="infosCredit">
        <fo:table-row>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="date_evnt"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="right">
                    <xsl:value-of select="mnt_deb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="right">
                    <xsl:value-of select="cap_remb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="right">
                    <xsl:value-of select="int_remb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="right">
                    <xsl:value-of select="frais_remb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="right">
                    <xsl:value-of select="pen_remb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="right">
                    <xsl:value-of select="cap_restant_du"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
            <fo:table-cell number-columns-spanned="7">
                <fo:block text-align="center" wrap-option="no-wrap">
                    -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>

    <xsl:template match="xml_total">
        <fo:table-row>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right" font-size="8pt">TOTAL</fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right" font-size="8pt">
                    <xsl:value-of select="mnt_deb_tot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right" font-size="8pt">
                    <xsl:value-of select="cap_remb_tot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right" font-size="8pt">
                    <xsl:value-of select="int_remb_tot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right" font-size="8pt">
                    <xsl:value-of select="frais_remb_tot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right" font-size="8pt">
                    <xsl:value-of select="pen_remb_tot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>

</xsl:stylesheet>
