<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:output method="text"/>

<xsl:template match="echeances_CAT">
	<xsl:apply-templates select="header"/>
	<xsl:apply-templates select="header_contextuel"/>
	<xsl:apply-templates select="ligne"/>			
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
	<xsl:apply-templates select="infos_synthetiques"/>
</xsl:template>

<xsl:template match="table_header_old">
	<xsl:for-each select="colonne">
		<xsl:value-of select="@libel"/>
		 <xsl:value-of select="','"/>	
	</xsl:for-each>		
</xsl:template>

<xsl:template match="ligne_old">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="@type_compte"/></xsl:call-template>;	
<xsl:apply-templates select="cellule"/>
</xsl:template>

<xsl:template match="celluless">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="@id"/></xsl:call-template>;
<xsl:for-each select="*">
   <xsl:value-of select="translate(.,';','')"/>
   <xsl:if test="position() != last()">
    <xsl:value-of select="';'"/>
   </xsl:if>
  </xsl:for-each>
<xsl:text disable-output-escaping="yes">
</xsl:text>
</xsl:template>

<xsl:template match="cellule_old">
<xsl:choose>
<xsl:when test="../@type_compte != 'Total'">
<xsl:value-of select="nombre"/>;
<xsl:value-of select="montant"/>;
</xsl:when>
</xsl:choose>
</xsl:template>

<xsl:template match="table_header">
	<xsl:for-each select="colonne">
	   <xsl:value-of select="@libel"/>;
	</xsl:for-each>
		Type de compte;
	<xsl:for-each select="colonne">;
				Nbre;
				Montant;
	</xsl:for-each>
</xsl:template>

<xsl:template match="ligne">
		<xsl:choose>
			<xsl:when test="@type_compte != 'Total'">
				<xsl:value-of select="@type_compte"/>;
			</xsl:when>
			
		</xsl:choose>	
		<xsl:apply-templates select="cellule"/>
</xsl:template>


<xsl:template match="cellule">
	Numéro du mois; Nombre;Montant;
	<xsl:value-of select="@id"/>;<xsl:value-of select="nombre"/>; <xsl:value-of select="montant"/>;
</xsl:template>

</xsl:stylesheet>
