<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_portrait"/>
            <xsl:apply-templates select="visualisation_transaction_agent"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="visualisation_transaction_agent">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="transactions"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    <xsl:template match="transactions">
        <fo:table border-collapse="collapse" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(19)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(5)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>

            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Date</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Fonction</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Etat</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Login validateur</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">login initiateur</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Compte de flotte</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <fo:table-row>   </fo:table-row>
                <xsl:apply-templates select="his_data"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>
    <xsl:template match="his_data">
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="date"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="fonction"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="etat"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="login"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="login_initiateur"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="right">
                    <xsl:value-of select="montant"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="num_cpte_complet"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>