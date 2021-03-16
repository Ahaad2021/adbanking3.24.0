<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
                version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="suivi_ligne_credit"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="suivi_ligne_credit">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
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
                        Total Montant octroyé : <xsl:value-of select="montant_octoye_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Capital déboursé : <xsl:value-of select="cap_debourse_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Capital restant dû : <xsl:value-of select="cap_restant_du_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total capital en attente de déblocage : <xsl:value-of select="montant_dispo_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Intérêts restant dû : <xsl:value-of select="interets_restant_du_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Intérêts payés : <xsl:value-of select="interets_payes_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Frais restant dû : <xsl:value-of select="frais_restant_du_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Total Frais payés : <xsl:value-of select="frais_payes_total"/>
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
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(2.5)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <fo:table-column column-width="proportional-column-width(2.5)"/>
            <fo:table-column column-width="proportional-column-width(2.5)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell>
                        <fo:block text-align="center">Num prêt</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Num client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Nom client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Gestionnaire</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Montant octroyé</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Devise</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Date d'octroi</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Durée</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Etat</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Montant en attente déblocage</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Capital restant dû</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Intérêts restant dû</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Intérêts payés</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Frais restant dû</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Frais payés</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Date dernier débours.</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Date dernier rembours.</fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block text-align="center">Date fin échéance</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <fo:table-row>
                    <fo:table-cell number-columns-spanned="19">
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
                    <xsl:value-of select="id_doss"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="num_client"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="left">
                    <xsl:value-of select="nom_client"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="libel_gestionnaire"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="right">
                    <xsl:value-of select="montant_octroye"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="devise"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="date_octroi"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="duree"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="etat"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="montant_dispo"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="capital_restant_du"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="interets_restant_du"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="interets_payes"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="frais_restant_du"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="frais_payes"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="date_dernier_deb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="date_dernier_remb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block text-align="center">
                    <xsl:value-of select="date_fin_echeance"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
            <fo:table-cell number-columns-spanned="19">
                <fo:block text-align="center" wrap-option="no-wrap">
                    -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>

    <xsl:template match="xml_total">
        <fo:table-row>
            <fo:table-cell padding-before="8pt">
                <fo:block/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right" font-size="8pt">Total</fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right" font-size="8pt">
                    <xsl:value-of select="tot_mnt_octr"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>
