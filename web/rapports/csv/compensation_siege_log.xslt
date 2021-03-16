<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="compensation_siege_log">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="header_contextuel"/>
        <xsl:apply-templates select="compensation_etat_log"/>
    </xsl:template>

    <xsl:include href="header.xslt"/>
    <xsl:include href="criteres_recherche.xslt"/>
    <xsl:include href="lib.xslt"/>

    <!-- list compensation siege log-->
    <xsl:template match="compensation_etat_log">;
                ID Agence;Nom Agence;Etat de la Compensation;Date dernier compensation;Date derniere compensation reussi;
                <xsl:for-each select="details_log">
                    <xsl:value-of select="translate(id_agence,';','')"/>;<xsl:value-of select="translate(agence,';','')"/>;<xsl:value-of select="translate(etat_compensation,';','')"/>;<xsl:value-of select="translate(date_derniere_compensation,';','')"/>;<xsl:value-of select="translate(date_derniere_compensation_reussi,';','')"/>;
                </xsl:for-each>;
            ;;
    </xsl:template>
</xsl:stylesheet>
