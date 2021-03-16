<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_portrait"/>
            <xsl:apply-templates select="impression_echeancier"/>
        </fo:root>
    </xsl:template>

    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="impression_echeancier">
        <xsl:apply-templates select="infos_doss"/>
    </xsl:template>

    <xsl:template match="infos_doss">
        <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header" mode="no_region"/>
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:call-template name="titre_niv2"/>
                <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"/>
                    <fo:table-header>
                        <fo:table-row font-weight="bold">
                            <fo:table-cell>
                                <fo:block text-align="center">N°</fo:block>
                            </fo:table-cell>
                            <fo:table-cell>
                                <fo:block text-align="center">Date</fo:block>
                            </fo:table-cell>
                            <fo:table-cell>
                                <fo:block text-align="center">Montant du capital</fo:block>
                            </fo:table-cell>
                            <fo:table-cell>
                                <fo:block text-align="center">Montant des intérêts</fo:block>
                            </fo:table-cell>
                            <fo:table-cell>
                                <fo:block text-align="center">Montant de</fo:block>
                                <fo:block text-align="center">la garantie</fo:block>
                            </fo:table-cell>
                            <fo:table-cell>
                                <fo:block text-align="center">Total de l'échéance</fo:block>
                            </fo:table-cell>
                            <fo:table-cell>
                                <fo:block text-align="center">Solde restant dû</fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-header>
                    <!-- Ajout de la ligne : Création d'un autre tableau -->
                    <fo:table-body></fo:table-body>
                </fo:table>
                <xsl:call-template name="titre_niv2"/>
                <fo:table border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
                    <fo:table-column column-width="proportional-column-width(1)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"   border-right-width="0.3pt" border-right-style="solid" border-right-color="gray"/>
                    <fo:table-column column-width="proportional-column-width(2)"/>
                    <!-- Affichage des infos -->
                    <fo:table-body>
                        <xsl:apply-templates select="ech"/>
                    </fo:table-body>
                </fo:table>
                <xsl:call-template name="titre_niv2"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    <xsl:template match="ech">
        <xsl:choose>
            <xsl:when test="date_s='Total'">
                <fo:table-row font-weight="bold">
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="eid"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="center"><xsl:value-of select="date_s"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="montant_capital"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="montant_interets"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="montant_garantie"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="total_echeance"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="solde_restant"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </xsl:when>
            <xsl:otherwise>
                <fo:table-row>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="center"><xsl:value-of select="eid"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="center"><xsl:value-of select="date_s"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="montant_capital"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="montant_interets"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="montant_garantie"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="total_echeance"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell padding-before="3pt">
                        <fo:block text-align="right"><xsl:value-of select="solde_restant"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>