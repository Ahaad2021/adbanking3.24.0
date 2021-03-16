<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="recouvrement_creance_bnr">
	<xsl:apply-templates select="header_contextuel"/>
	<xsl:apply-templates select="globals"/>
	"Line Code";"Days Overdue";"Total Risks on 31st December of Previous /Last Year";"Recovery for the Current Period";;;;
;;;"1st  Quarter";"2nd Quarter ";"3rd  Quarter";"4th Quarter";"TOTAL"
;;"Principal";"Principal";"Principal";"Principal";"Principal";"Principal"
	<xsl:apply-templates select="creance"/>
	<xsl:apply-templates select="total"/>
</xsl:template>

<xsl:include href="research_criteria.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="research_criteria"/>
</xsl:template>

<xsl:template match="globals">
;;;;;;;"Amounts in <xsl:value-of select="translate(devise,';','')"/>"
</xsl:template>
<xsl:template match="creance">
M.DLCI.<xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(libel_etat,';','')"/>;<xsl:value-of select="translate(annee_ecoulee,';','')"/>;<xsl:value-of select="translate(trim1,';','')"/>;<xsl:value-of select="translate(trim2,';','')"/>;<xsl:value-of select="translate(trim3,';','')"/>;<xsl:value-of select="translate(trim4,';','')"/>;<xsl:value-of select="translate(total_creance,';','')"/>
</xsl:template>
<xsl:template match="total">
"";"TOTAL";<xsl:value-of select="translate(tot_annee_ecoulee,';','')"/>;<xsl:value-of select="translate(tot_trim1,';','')"/>;<xsl:value-of select="translate(tot_trim2,';','')"/>;<xsl:value-of select="translate(tot_trim3,';','')"/>;<xsl:value-of select="translate(tot_trim4,';','')"/>;<xsl:value-of select="translate(total_trim,';','')"/>
</xsl:template>
</xsl:stylesheet>

