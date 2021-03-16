<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:fo="http://www.w3.org/1999/XSL/Format"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage">
            </xsl:call-template>
            <xsl:apply-templates select="budget_revisionhistorique"/>
        </fo:root>
    </xsl:template>

    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="budget_revisionhistorique">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"></xsl:call-template>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="list_revision"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="list_revision">
        <xsl:for-each select="list_budget">
            <fo:block text-align="left" font-size="14pt" font-weight="bold" border-top-style="" border-bottom-style="solid" space-before="0.3in"><xsl:value-of select="type_budget"/></fo:block>
            <xsl:for-each select="list_period">
                <fo:block text-align="left" font-size="11pt" font-weight="bold" font-style="italic" border-top-style="" border-bottom-style="" space-before="0.1in">  <xsl:value-of select="period"/></fo:block>
                <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.1in" >
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-column column-width="proportional-column-width(1)"/>
                    <fo:table-header>
                        <fo:table-row font-weight="bold" font-size="9pt" border="0.1pt solid gray">
                            <fo:table-cell border="0.1pt solid gray">
                                <fo:block text-align="center">Revision Date</fo:block>
                            </fo:table-cell>
                            <fo:table-cell border="0.1pt solid gray">
                                <fo:block text-align="center">Budget Line</fo:block>
                            </fo:table-cell>
                            <fo:table-cell border="0.1pt solid gray">
                                <fo:block text-align="center">User who revised the budget</fo:block>
                            </fo:table-cell>
                            <fo:table-cell border="0.1pt solid gray">
                                <fo:block text-align="center">User who validated the budget</fo:block>
                            </fo:table-cell>
                            <fo:table-cell border="0.1pt solid gray">
                                <fo:block text-align="center">Old Budget Amount</fo:block>
                            </fo:table-cell>
                            <fo:table-cell border="0.1pt solid gray">
                                <fo:block text-align="center">New Budget Amount</fo:block>
                            </fo:table-cell>
                            <fo:table-cell border="0.1pt solid gray">
                                <fo:block text-align="center">Variation</fo:block>
                            </fo:table-cell>
                        </fo:table-row>
                    </fo:table-header>

                    <fo:table-body>
                        <xsl:for-each select="ligne_revision">
                            <fo:table-row font-size="9pt" border="0.1pt solid gray">
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="center"><xsl:value-of select="date_revision"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="center"><xsl:value-of select="ligne_budget"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="center"><xsl:value-of select="login_revise"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="center"><xsl:value-of select="login_valide"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="right"><xsl:value-of select="anc_montant"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="right"><xsl:value-of select="nouv_montant"/></fo:block>
                                </fo:table-cell>
                                <fo:table-cell border="0.1pt solid gray">
                                    <fo:block text-align="right"><xsl:value-of select="variation"/></fo:block>
                                </fo:table-cell>
                            </fo:table-row>
                        </xsl:for-each>
                    </fo:table-body>
                </fo:table>
            </xsl:for-each>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
