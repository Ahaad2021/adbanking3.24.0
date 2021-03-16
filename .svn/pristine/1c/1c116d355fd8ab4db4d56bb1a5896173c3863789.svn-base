<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text"/>

    <xsl:template match="situation_compensation_siege">
        <xsl:apply-templates select="header"/>
        <xsl:apply-templates select="situation_agence"/>
    </xsl:template>
    <xsl:include href="header.xslt"/>
    <xsl:include href="lib.xslt"/>
    <!-- Start : compensations_par_agence -->
    <xsl:template match="situation_agence">
        SOLDE DEBUT PERIODE COMPTE DE LIAISON <xsl:value-of select="compensations_par_agence/situation_local/cpte_liaison"/>        <xsl:value-of select="compensations_par_agence/nom_agence"/>: , <xsl:value-of select="../solde_deb"/>;;
        <xsl:for-each select="compensations_par_agence">;;

            <xsl:value-of select="title"/>

            Opérations distantes à <xsl:value-of select="situation_local/nom_agence_distant" />
            Total Depots à Agence <xsl:value-of select="situation_distant/nom_agence_distant" />&#160; ,<xsl:value-of select="situation_distant/total_depot" />
            Total Rétraits à Agence <xsl:value-of select="situation_distant/nom_agence_distant" /> ,<xsl:value-of select="situation_distant/total_retrait" />
            Solde Opérations distantes à <xsl:value-of select="situation_distant/nom_agence_distant" /> ,<xsl:value-of select="situation_distant/solde_operation_distant" />
            Opérations locales pour <xsl:value-of select="situation_local/nom_agence_distant" />
            Total Depots pour Agence <xsl:value-of select="situation_local/nom_agence_distant" />&#160; ,<xsl:value-of select="situation_local/total_depot" />
            Total Rétraits pour Agence <xsl:value-of select="situation_local/nom_agence_distant" /> ,<xsl:value-of select="situation_local/total_retrait" />
            Solde Opérations locales pour <xsl:value-of select="situation_local/nom_agence_distant" /> ,<xsl:value-of select="situation_local/solde_operation_local" />
            Solde Compensation avec Agence <xsl:value-of select="situation_local/nom_agence_distant" /> ,<xsl:value-of select="situation_local/solde_compensation_local" />;;
            <xsl:value-of select="synthese" />;;
        </xsl:for-each>

        Solde Compensation Globale ( <xsl:value-of select="../devise" /> ) ,<xsl:value-of select="../solde_compensation_global" />

        Autres mouvements débiteurs <xsl:value-of select="compensations_par_agence/situation_local/cpte_liaison" /> ( <xsl:value-of select="../devise" /> ) <xsl:value-of select="compensations_par_agence/nom_agence" /> ,<xsl:value-of select="../mvmts_deb" />
        Autres mouvements créditeurs <xsl:value-of select="compensations_par_agence/situation_local/cpte_liaison" /> ( <xsl:value-of select="../devise" /> ) <xsl:value-of select="compensations_par_agence/nom_agence" /> ,<xsl:value-of select="../mvmts_cred" />


        SOLDE FIN PERIODE COMPTE DE LIAISON <xsl:value-of select="compensations_par_agence/situation_local/cpte_liaison"/> <xsl:value-of select="compensations_par_agence/nom_agence"/>: ,<xsl:value-of select="../solde_fin"/>
    </xsl:template>
</xsl:stylesheet>
