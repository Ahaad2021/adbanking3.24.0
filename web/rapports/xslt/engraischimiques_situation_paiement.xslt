<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="engraischimiques_situation_paiement"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="engraischimiques_situation_paiement">
        <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="details_produits"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    <xsl:template match="liste_prod">
        <fo:table-body>
            <fo:table-row>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block>
                        <xsl:value-of select="nom_prod"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="montant_depot"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="montant_paye"/>
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="center" border="0.1pt solid gray">
                    <fo:block text-align="center">
                        <xsl:value-of select="montant_total_paye"/>
                    </fo:block>
                </fo:table-cell>
            </fo:table-row>
        </fo:table-body>
    </xsl:template>
    <xsl:template match="details_produits">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre">
                <xsl:value-of select="choix_periode"/>
            </xsl:with-param>
        </xsl:call-template>
        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Nom produit</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant avance paye</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant paye</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant total paye</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <xsl:apply-templates select="liste_prod"/>
        </fo:table>
    </xsl:template>

    <xsl:template match="header_contextuel">
        <xsl:apply-templates select="criteres_recherche"/>
        <xsl:apply-templates select="infos_synthetiques"/>
    </xsl:template>
    <xsl:template match="infos_synthetiques">
        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre" select="'Informations synthétiques'"/>
        </xsl:call-template>
        <fo:list-block>
            <fo:list-item>
                <fo:list-item-label>
                    <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                    <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date debut:<xsl:value-of select="date_debut"/></fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label>
                    <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                    <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Date fin: <xsl:value-of select="date_fin"/></fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label>
                    <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                    <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Nombre agriculteur: <xsl:value-of select="nb_agri"/></fo:block>
                </fo:list-item-body>
            </fo:list-item>
            <fo:list-item>
                <fo:list-item-label>
                    <fo:block/>
                </fo:list-item-label>
                <fo:list-item-body>
                    <fo:block><xsl:value-of select="$point_liste" disable-output-escaping="yes"/>Montant encaisse: <xsl:value-of select="mnt_encaisse"/></fo:block>
                </fo:list-item-body>
            </fo:list-item>
        </fo:list-block>
    </xsl:template>
</xsl:stylesheet>
