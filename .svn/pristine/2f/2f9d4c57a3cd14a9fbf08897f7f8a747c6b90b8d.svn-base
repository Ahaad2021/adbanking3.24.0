<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="text"/>
    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>
    <xsl:template match="inventaire_depot">
            <xsl:apply-templates select="header"/>
                <xsl:apply-templates select="header_contextuel"/>
                <xsl:apply-templates select="body"/>
    </xsl:template>

    <xsl:template match="body">
        <xsl:apply-templates select="produit_epargne"/>
    </xsl:template>
    <xsl:template match="produit_epargne">
                <xsl:call-template name="titre1">
                    <xsl:with-param name="titre">
                        <xsl:value-of select="epargne"/>
                    </xsl:with-param>
                </xsl:call-template>
    <xsl:value-of select="';'"/>
N0;Numéro de compte;Nom client;sexe;solde début période;Total mvts. dépôts;Total mvts. retrait;Solde fin période;Date de naissance;Etat Civile;Sector;Telephone;IdNumber;
<xsl:apply-templates select="ligne_produit"/>
Total Solde debut periode;Total mouvement depot;Total mouvement retrait;Solde fin periode
<xsl:apply-templates select="totals"/>
</xsl:template>
<xsl:template match="ligne_produit">
<xsl:value-of select="translate(num,';','')"/>;<xsl:value-of select="translate(num_cpte,';','')"/>;<xsl:value-of select="translate(nom_client,';','')"/>;<xsl:value-of select="translate(sexe,';','')"/>;<xsl:value-of select="translate(solde_debut_periode,';','')"/>;<xsl:value-of select="translate(total_mouvement_depot,';','')"/>;<xsl:value-of select="translate(total_mouvement_retrait,';','')"/>;<xsl:value-of select="translate(solde_fin_periode,';','')"/>;<xsl:value-of select="translate(date_naissance,';','')"/>;<xsl:value-of select="translate(etat_civile,';','')"/>;<xsl:value-of select="translate(sector,';','')"/>;<xsl:value-of select="translate(tel,';','')"/>;<xsl:value-of select="translate(idnumber,';','')"/>;
</xsl:template>
<xsl:template match="header_contextuel">
<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>
<xsl:template match="totals">
<xsl:value-of select="translate(tot_solde_debut,';','')"/>;<xsl:value-of select="translate(tot_mouvement_depot,';','')"/>;<xsl:value-of select="translate(tot_mouvement_retrait,';','')"/>;<xsl:value-of select="translate(tot_solde_fin,';','')"/>;
</xsl:template>
    <!--<xsl:template match="totals">
        <fo:table-row font-weight="bold">
            <fo:table-cell display-align="center" border="0.2pt solid black"  number-columns-spanned="4">
                <fo:block font-weight="bold" text-align="center"> TOTAUX </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
            <fo:block font-weight="bold" text-align="center">
                <xsl:value-of select="tot_solde_debut"/>
            </fo:block>
        </fo:table-cell>
            <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
                <fo:block font-weight="bold" text-align="center">
                    <xsl:value-of select="tot_mouvement_depot"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
                <fo:block font-weight="bold" text-align="center">
                    <xsl:value-of select="tot_mouvement_retrait"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell border="0.2pt solid black" padding-before="8pt" padding-after="8pt">
                <fo:block font-weight="bold" text-align="center">
                    <xsl:value-of select="tot_solde_fin"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>-->
</xsl:stylesheet>
