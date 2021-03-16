<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_portrait"/>
            <xsl:apply-templates select="brouillard_caisse"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="brouillard_caisse">
        <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="brouillard_devise"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    <xsl:template match="brouillard_devise">
        <xsl:apply-templates select="infos_globales"/>
        <xsl:apply-templates select="detail"/>
    </xsl:template>
    <xsl:template match="infos_globales">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="concat('Guichet N°', ../@guichet)"/>
        </xsl:call-template>
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="concat('Informations globales en ', ../@devise)"/>
        </xsl:call-template>
        <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-body>
                <fo:table-row>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Numero de compte de flotte : </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="libel_gui"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Agent : </fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="nom_uti"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Solde du compte de début de la journée</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="encaisse_deb"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Solde du compte de fin de la journée</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">
                            <xsl:value-of select="encaisse_fin"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>
        <xsl:apply-templates select="resume_transactions"/>
    </xsl:template>
    <xsl:template match="resume_transactions">
        <fo:table border="none" border-collapse="separate" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Opération</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Nombre</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant débit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant crédit</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:apply-templates select="ligne_resume_transactions"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>
    <xsl:template match="ligne_resume_transactions">
        <xsl:if test="@total = '0'">
            <fo:table-row>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="libel_operation"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="nombre"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="montant_debit"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="montant_credit"/>
                    </fo:block>
                </fo:table-cell>
            </fo:table-row>
        </xsl:if>
        <xsl:if test="@total = '1'">
            <fo:table-row font-weight="bold">
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="libel_operation"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="nombre"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="montant_debit"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="montant_credit"/>
                    </fo:block>
                </fo:table-cell>
            </fo:table-row>
        </xsl:if>
    </xsl:template>
    <xsl:template match="detail">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="concat('Détail des transactions en ', ../@devise)"/>
        </xsl:call-template>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(4)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-column column-width="proportional-column-width(3)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="left">Num trans</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="right">Num pièce</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Heure</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Opération</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Num Client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Nom client</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Mnt débit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Mnt crédit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Solde</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:apply-templates select="ligne_detail"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>
    <xsl:template match="ligne_detail">
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="left">
                    <xsl:value-of select="num_trans"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="right">
                    <xsl:value-of select="num_piece"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="heure"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="libel_operation"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="id_client"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="nom_client"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="montant_debit"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="montant_credit"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray">
                <fo:block text-align="center">
                    <xsl:value-of select="encaisse"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
</xsl:stylesheet>
