<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="compensation_siege_log"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="compensation_siege_log">
        <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="compensation_etat_log"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="compensation_etat_log/details_log">
        <fo:table-body>
            <fo:table-row>
                <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="9pt" >
                    <fo:block text-align="left">
                        <xsl:value-of select="id_agence"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="9pt" >
                    <fo:block text-align="left">
                        <xsl:value-of select="agence"/>
                    </fo:block>
                </fo:table-cell>
                <!--<fo:table-cell display-align="center" border="0.1pt solid gray" font-size="9pt" >
                    <fo:block text-align="left">
                        <xsl:value-of select="date_rapport"/>
                    </fo:block>
                </fo:table-cell>-->
                <xsl:choose>
                    <xsl:when test="etat = 't'">
                        <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="9pt" >
                            <fo:block text-align="left">
                                <xsl:value-of select="etat_compensation"/>
                            </fo:block>
                        </fo:table-cell>
                    </xsl:when>
                    <xsl:otherwise>
                        <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="9pt" color = "white" background-color = "#FF0000" >
                            <fo:block text-align="left">
                                <xsl:value-of select="etat_compensation"/>
                            </fo:block>
                        </fo:table-cell>
                    </xsl:otherwise>
                </xsl:choose>
                <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="9pt" >
                    <fo:block text-align="center">
                        <xsl:value-of select="date_derniere_compensation"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="9pt" >
                    <fo:block text-align="center">
                        <xsl:value-of select="date_derniere_compensation_reussi"/>
                    </fo:block>
                </fo:table-cell>
            </fo:table-row>
        </fo:table-body>
    </xsl:template>

    <xsl:template match="compensation_etat_log">
        <fo:block text-align="center" border-bottom-style ="solid" font-weight="bold" space-before="0.1in">Liste Etat Compensation pour le(s) agence(s) </fo:block>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.2in">
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1.5)"/>
            <!--<fo:table-column column-width="proportional-column-width(1.0)"/>-->
            <fo:table-column column-width="proportional-column-width(1.0)"/>
            <fo:table-column column-width="proportional-column-width(1.0)"/>
            <fo:table-column column-width="proportional-column-width(1.0)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" border="0.1pt solid gray" >
                        <fo:block text-align="center">Branch ID</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Branch Name</fo:block>
                    </fo:table-cell>
                    <!--<fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Repport Date</fo:block>
                    </fo:table-cell>-->
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Compensation Status</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Last date of Compensation</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Last date of success</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <xsl:apply-templates select="details_log"/>
        </fo:table>
    </xsl:template>
</xsl:stylesheet>