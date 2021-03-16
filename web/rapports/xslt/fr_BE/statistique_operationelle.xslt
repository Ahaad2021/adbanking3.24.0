<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root>
            <xsl:call-template name="page_layout_A4_paysage"/>
            <xsl:apply-templates select="statistique_operationelle"/>
        </fo:root>
    </xsl:template>
    <xsl:include href="page_layout.xslt"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="footer.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="statistique_operationelle">
        <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
            <xsl:apply-templates select="header"/>
            <xsl:call-template name="footer"/>
            <fo:flow flow-name="xsl-region-body">
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="infos_rapport"/>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>

    <xsl:template match="adhesion/data_ad">
        <!-- <fo:table-body>-->
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="center">
                    <xsl:value-of select="employeur"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="nbre_cible"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="nombre"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt"  >
                <fo:block text-align="right">
                    <xsl:value-of select="actif"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="prc_nbre"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="prc_actif"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
        <!-- </fo:table-body>-->
    </xsl:template>


    <!-- Concernant le rapport credit -->

    <xsl:template match="credit/data_cr">
        <!--<fo:table-body> -->
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="center">
                    <xsl:value-of select="employeur_credit"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="nbre_octroi"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="mnt_octroi"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt"  >
                <fo:block text-align="right">
                    <xsl:value-of select="nbre_remb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="mnt_remb"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="nbre_encours"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="mnt_encours"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
        <!--</fo:table-body>-->
    </xsl:template>

    <!-- Concernant le rapport epargne -->

    <xsl:template match="epargne/data_ep">
        <!--<fo:table-body> -->
        <fo:table-row>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="center">
                    <xsl:value-of select="employeur_epargne"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="nbre_depot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="mnt_depot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt"  >
                <fo:block text-align="right">
                    <xsl:value-of select="nbre_retrait"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="mnt_retrait"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="nbre_encours_epargne"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell display-align="center" border="0.1pt solid gray" font-size="10pt" >
                <fo:block text-align="right">
                    <xsl:value-of select="mnt_encours_epargne"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
        <!--</fo:table-body>-->
    </xsl:template>
    <xsl:template match="infos_rapport">

        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre">Adhesion</xsl:with-param>
        </xsl:call-template>

        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.2in">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Employeur</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Cible</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Nombre</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Actifs</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">% Nombre</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">% Actifs</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:apply-templates select="adhesion/data_ad"/>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Total</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="adhesion/total_adhesion/tot_cible"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="adhesion/total_adhesion/tot_nbre"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="adhesion/total_adhesion/tot_actif"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="adhesion/total_adhesion/tot_prc_nbre"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="adhesion/total_adhesion/total_prc_actif"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>



        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre">Credit</xsl:with-param>
        </xsl:call-template>

        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.2in">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Employeur</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Nombre Octroie</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant Octroie</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Nombre rembourse</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant rembourse</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Nombre en cours</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant en cours</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:apply-templates select="credit/data_cr"/>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Total</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="credit/total_credit/tot_nbre_octroi"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="credit/total_credit/tot_mnt_octroi"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="credit/total_credit/tot_nbre_remb"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="credit/total_credit/tot_mnt_remb"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="credit/total_credit/tot_nbre_encours"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="credit/total_credit/tot_mnt_encours"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>

        <xsl:call-template name="titre_niv1">
            <xsl:with-param name="titre">Epargne</xsl:with-param>
        </xsl:call-template>

        <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed" space-before="0.2in">
            <fo:table-column column-width="proportional-column-width(2)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-column column-width="proportional-column-width(0.5)"/>
            <fo:table-column column-width="proportional-column-width(1)"/>
            <fo:table-header>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Employeur</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Nombre depot</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant depot</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Nombre retrait</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant retrait</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Nombre en cours</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Montant en cours</fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-header>
            <fo:table-body>
                <xsl:apply-templates select="epargne/data_ep"/>
                <fo:table-row font-weight="bold">
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="center">Total</fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="epargne/total_epargne/tot_nbre_depot"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="epargne/total_epargne/tot_mnt_depot"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="epargne/total_epargne/tot_nbre_retrait"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="epargne/total_epargne/tot_mnt_retrait"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="epargne/total_epargne/tot_nbre_encours_epargne"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell display-align="center" font-size="12pt" border="0.1pt solid gray">
                        <fo:block text-align="right"><xsl:value-of select="epargne/total_epargne/tot_mnt_encours_epargne"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </fo:table-body>
        </fo:table>
    </xsl:template>
</xsl:stylesheet>