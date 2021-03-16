<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
                version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="rapport_compte_dormant"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="rapport_compte_dormant">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="infos_synthetiques"/>
                <xsl:apply-templates select="ligneCompteDormant"/>
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
                        Nombre total de comptes dormants : <xsl:value-of select="nombre_comptes_dormants_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>

            <fo:list-item>
                <fo:list-item-label><fo:block></fo:block></fo:list-item-label>
                <fo:list-item-body>
                    <fo:block>
                        <xsl:value-of select="$point_liste" disable-output-escaping="yes"/>
                        Solde total comptes dormants : <xsl:value-of select="solde_comptes_dormants_total"/>
                    </fo:block>
                </fo:list-item-body>
            </fo:list-item>
        </fo:list-block>

    </xsl:template>
    <!-- End : infos_synthetique -->

    <xsl:template match="ligneCompteDormant">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre">
                <xsl:value-of select="lib_prod"/>
            </xsl:with-param>
        </xsl:call-template>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1" padding-before="2pt" padding-after="1pt">
                        <fo:block text-align="center">N° client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1" padding-before="2pt" padding-after="1pt">
                        <fo:block text-align="center">N° compte</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1" padding-before="2pt" padding-after="1pt">
                        <fo:block text-align="center">Nom du client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1" padding-before="2pt" padding-after="1pt">
                        <fo:block text-align="center">Solde</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" number-columns-spanned="1" padding-before="2pt" padding-after="1pt">
                        <fo:block text-align="center">Date blocage</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:apply-templates select="infosCompteDormant"/>
                <xsl:apply-templates select="xml_total"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>

    <xsl:template match="infosCompteDormant">
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray" padding-before="1pt" padding-after="1pt">
                <fo:block text-align="center">
                    <xsl:value-of select="num_client"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" padding-before="1pt" padding-after="1pt">
                <fo:block text-align="center">
                    <xsl:value-of select="num_compte"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" padding-before="1pt" padding-after="1pt">
                <fo:block text-align="center">
                    <xsl:value-of select="nom_client"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" padding-before="1pt" padding-after="1pt">
                <fo:block text-align="center">
                    <xsl:value-of select="solde_compte"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" padding-before="1pt" padding-after="1pt">
                <fo:block text-align="center">
                    <xsl:value-of select="date_blocage"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>

    </xsl:template>

    <xsl:template match="xml_total">
        <fo:table-row font-weight="bold">
            <fo:table-cell padding-before="8pt">
                <fo:block/>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block/>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" padding-after="2pt">
                <fo:block font-weight="bold" text-align="center" font-size="8pt">Sous Total</fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.1pt solid black" padding-before="2pt" padding-after="2pt">
                <fo:block font-weight="bold" text-align="center" font-size="8pt">
                    <xsl:value-of select="tot_solde_cpte"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-before="8pt">
                <fo:block font-weight="bold" text-align="right"/>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>
