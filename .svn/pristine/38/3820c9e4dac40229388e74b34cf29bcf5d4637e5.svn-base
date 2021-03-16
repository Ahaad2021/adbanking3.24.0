<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="situation_compensation">
	<xsl:apply-templates select="header"/>
	<xsl:apply-templates select="compensations_par_agence"/>
</xsl:template>
<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>
<!-- Start : compensations_par_agence -->
<xsl:template match="compensations_par_agence">
<xsl:for-each select="situation_agence">
Situation <xsl:value-of select="situation_local/donnees_agence/agence_local" />;<xsl:value-of select="situation_local/donnees_agence/code_devise_local" />;;Situation <xsl:value-of select="situation_distant/donnees_agence/agence_distant" />;<xsl:value-of select="situation_distant/donnees_agence/code_devise_distant" />
Solde début de période compte de liaison <xsl:value-of select="situation_local/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_distant" />;<xsl:value-of select="situation_local/donnees_agence/solde_deb" />;;Solde début de période compte de liaison <xsl:value-of select="situation_distant/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_distant/donnees_agence/agence_local" />;<xsl:value-of select="situation_distant/donnees_agence/solde_deb" />
Total des dépôts dans <xsl:value-of select="situation_local/donnees_agence/agence_distant" />;<xsl:value-of select="situation_local/donnees_agence/total_depot" />;;Total des dépôts dans <xsl:value-of select="situation_distant/donnees_agence/agence_local" />;<xsl:value-of select="situation_distant/donnees_agence/total_depot" />
Total des retraits dans <xsl:value-of select="situation_local/donnees_agence/agence_distant" />;<xsl:value-of select="situation_local/donnees_agence/total_retrait" />;;Total des retraits dans <xsl:value-of select="situation_distant/donnees_agence/agence_local" />;<xsl:value-of select="situation_distant/donnees_agence/total_retrait" />
Autres mouvements débiteurs <xsl:value-of select="situation_local/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_distant" />;<xsl:value-of select="situation_local/donnees_agence/mvmts_deb" />;;Autres mouvements débiteurs <xsl:value-of select="situation_distant/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_local" />;<xsl:value-of select="situation_distant/donnees_agence/mvmts_deb" />
Autres mouvements créditeurs <xsl:value-of select="situation_local/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_distant" />;<xsl:value-of select="situation_local/donnees_agence/mvmts_cred" />;;Autres mouvements créditeurs <xsl:value-of select="situation_distant/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_local" />;<xsl:value-of select="situation_distant/donnees_agence/mvmts_cred" />
Solde fin de période compte de liaison <xsl:value-of select="situation_local/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_local/donnees_agence/agence_distant" />;<xsl:value-of select="situation_local/donnees_agence/solde_fin" />;;Solde fin de période compte de liaison <xsl:value-of select="situation_distant/donnees_agence/cpte_liaison" />&#160;<xsl:value-of select="situation_distant/donnees_agence/agence_local" />;<xsl:value-of select="situation_distant/donnees_agence/solde_fin" />;
<xsl:value-of select="synthese" />
;;;;;
</xsl:for-each>
<xsl:if test="summary != ''"><xsl:for-each select="summary"><xsl:value-of select="summary_info"/>;;;;
</xsl:for-each></xsl:if>
</xsl:template>
</xsl:stylesheet>
