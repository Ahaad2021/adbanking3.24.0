<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="budget_etatbudgetaire"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="budget_etatbudgetaire">
        <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="infos_synthetiques"/>
                <xsl:apply-templates select="infos_etat"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="type_budget/details">
        <fo:table-body>
            <fo:table-row>
                <xsl:if test="niveau='0'">
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="13pt" background-color = "#a6a6a6" font-weight="bold">
                        <fo:block text-align="center">
                            <xsl:value-of select="poste"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="13pt" background-color = "#a6a6a6" font-weight="bold">
                        <fo:block text-align="left">
                            <xsl:value-of select="description"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="13pt" background-color = "#a6a6a6" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="budget_annuel"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="13pt" background-color = "#a6a6a6" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="budget_periode"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="13pt" background-color = "#a6a6a6" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="realisation_period"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="13pt" background-color = "#a6a6a6" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="performance_period"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="13pt" background-color = "#a6a6a6" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="performance_annuelle"/>
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>

                <xsl:if test="niveau='1'">
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="12pt" background-color = "#bfbfbf" font-weight="bold">
                        <fo:block text-align="center">
                            <xsl:value-of select="poste"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="12pt" background-color = "#bfbfbf" font-weight="bold">
                        <fo:block text-align="left">
                            <xsl:value-of select="description"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="12pt" background-color = "#bfbfbf" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="budget_annuel"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="12pt" background-color = "#bfbfbf" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="budget_periode"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="12pt" background-color = "#bfbfbf" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="realisation_period"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="12pt" background-color = "#bfbfbf" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="performance_period"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="12pt" background-color = "#bfbfbf" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="performance_annuelle"/>
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>

                <xsl:if test="niveau='2'">
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="11pt" font-weight="bold">
                        <fo:block text-align="center">
                            <xsl:value-of select="poste"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="11pt" font-weight="bold">
                        <fo:block text-align="left">
                            <xsl:value-of select="description"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="11pt" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="budget_annuel"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="11pt" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="budget_periode"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="11pt" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="realisation_period"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="11pt" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="performance_period"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="11pt" font-weight="bold">
                        <fo:block text-align="right">
                            <xsl:value-of select="performance_annuelle"/>
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>

                <xsl:if test="niveau='3'">
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt">
                        <fo:block text-align="center">
                            <xsl:value-of select="poste"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt">
                        <fo:block text-align="left">
                            <xsl:value-of select="description"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt">
                        <fo:block text-align="right">
                            <xsl:value-of select="budget_annuel"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt">
                        <fo:block text-align="right">
                            <xsl:value-of select="budget_periode"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt">
                        <fo:block text-align="right">
                            <xsl:value-of select="realisation_period"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt">
                        <fo:block text-align="right">
                            <xsl:value-of select="performance_period"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt">
                        <fo:block text-align="right">
                            <xsl:value-of select="performance_annuelle"/>
                        </fo:block>
                    </fo:table-cell>
                </xsl:if>


            </fo:table-row>
        </fo:table-body>
    </xsl:template>
    <xsl:template match="infos_etat">

        <fo:block text-align="left" border-bottom-style ="solid" space-before="0.1in" space-after = "0.1in">Devise : <xsl:value-of select="../infos_synthetiques"/> </fo:block>
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre">Etat d'execution budgetaire</xsl:with-param>
        </xsl:call-template>

        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.2in">
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Poste</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Description</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Budget annuel</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Budget de la p??riode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">R??alisation de la p??riode</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Performance de la p??riode (en %)</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Performance par rapport au budget annuel(en %)</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <xsl:apply-templates select="type_budget/details"/>
        </fo:table>
    </xsl:template>
</xsl:stylesheet>