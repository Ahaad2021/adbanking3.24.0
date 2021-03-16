<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>


<xsl:template match="ecritures_libres">
    <xsl:apply-templates select="header"/>
	<xsl:apply-templates select="ecritures_devise"/>
	<xsl:apply-templates select="infos_globales"/>
	<xsl:apply-templates select="detail"/>
</xsl:template>

<xsl:template match="infos_globales">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Informations globales ', ../@devise)"/></xsl:call-template>
    	       Nom du guichet ;<xsl:value-of select="translate(libel_gui,';','')"/>;
		       Agent ;<xsl:value-of select="translate(nom_uti,';','')"/>;
		       Login ;<xsl:value-of select="translate(login,';','')"/>;
		       Encaisse début de journée ;<xsl:value-of select="translate(substring(encaisse_deb,1,string-length(encaisse_deb)-3),';','')"/>;
		       Encaisse fin de journée ;<xsl:value-of select="translate(substring(encaisse_fin,1,string-length(encaisse_fin)-3),';','')"/>;
		       Devise ; ;<xsl:value-of select="translate(substring(encaisse_fin,string-length(encaisse_fin)-3),';','')"/>;
		     <xsl:apply-templates select="resume_transactions111"/>
</xsl:template>

<xsl:template match="resume_transactions">
	<xsl:apply-templates select="ligne_resume_transactions"/>
</xsl:template>

<xsl:template match="ligne_resume_transactions">
	<xsl:if test="@total = '0'">
	       ; libellé opération; Nombre;Montant;
	       ;<xsl:value-of select="translate(libel_operation,';','')"/>;<xsl:value-of select="translate(nombre,';','')"/>;<xsl:value-of select="translate(montant_debit,';','')"/>;
	;
	</xsl:if>
	<xsl:if test="@total = '1'">
	        ; Opération; Nombre;Montant;
	        ;<xsl:value-of select="translate(libel_operation,';','')"/>;<xsl:value-of select="translate(nombre,';','')"/>;<xsl:value-of select="translate(montant_debit,';','')"/>;
	</xsl:if>
</xsl:template>

<xsl:template match="detail">
	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="concat('Détail des transactions ', ../@devise)"/></xsl:call-template>
		;Numéro transaction; Client; Date/heure ; libellé opération; Cpte débit; Cpte crédit; Montant débité; Montant crédité; Encaisse;
		<xsl:apply-templates select="ligne_detail"/>
</xsl:template>

<xsl:template match="ligne_detail">
	     ;<xsl:value-of select="translate(num_trans,';','')"/>;<xsl:value-of select="translate(client,';','')"/>;<xsl:value-of select="translate(heure,';','')"/>;<xsl:value-of select="translate(libel_operation,';','')"/>;<xsl:value-of select="translate(compte_debit,';','')"/>;<xsl:value-of select="translate(compte_credit,';','')"/>;<xsl:value-of select="translate(montant_debit,';','')"/>;<xsl:value-of select="translate(montant_credit,';','')"/>;<xsl:value-of select="translate(encaisse,';','')"/>;
;
</xsl:template>

</xsl:stylesheet>
