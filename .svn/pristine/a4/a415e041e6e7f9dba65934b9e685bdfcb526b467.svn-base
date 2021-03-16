<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"  xmlns:fo="http://www.w3.org/1999/XSL/Format"  xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage">
            </xsl:call-template>
            <xsl:apply-templates select="mobile_lending_nouveaux_clients"/>
        </fo:root>
    </xsl:template>

    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>

    <xsl:template match="mobile_lending_nouveaux_clients">
        <fo:page-sequence master-reference="main" font-size="8pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"></xsl:call-template>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="new_client"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="new_client">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Crédits en cours'"/>
        </xsl:call-template>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.1in" >
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold" font-size="10pt" border="0.1pt solid gray">
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">N° client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Nom client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Numéro de téléphone</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">N° dossier</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Montant crédit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Durée crédit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Etat crédit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Montant échéance</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Date échéance</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:for-each select="credit_en_cours">
                    <fo:table-row font-size="9pt" border="0.1pt solid gray">
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="num_client"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="nom_client"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="telephone1"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="dossier"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="mnt_credit"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="duree"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="etat"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="mnt_eche"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="date_eche"/></fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                </xsl:for-each>
            </fo:table-body>
        </fo:table>



        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Crédits soldés'"/>
        </xsl:call-template>

        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.1in" >
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold" font-size="10pt" border="0.1pt solid gray">
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">N° client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Nom client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Numéro de téléphone</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">N° dossier</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Montant crédit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Durée crédit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Etat crédit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Montant échéance</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border="0.1pt solid gray">
                        <fo:block text-align="center">Date échéance</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:for-each select="credit_solde">
                    <fo:table-row font-size="9pt" border="0.1pt solid gray">
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="num_client_solde"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="nom_client_solde"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="telephone_solde"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="dossier_solde"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="mnt_credit_solde"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="duree_solde"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="etat_solde"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="right"><xsl:value-of select="mnt_eche_solde"/></fo:block>
                        </fo:table-cell>
                        <fo:table-cell border="0.1pt solid gray">
                            <fo:block text-align="left"><xsl:value-of select="date_eche_solde"/></fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                </xsl:for-each>
            </fo:table-body>
        </fo:table>







    </xsl:template>
</xsl:stylesheet>