<?xml version="1.0" encoding="UTF-8"?>
 	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
 	<xsl:output method="text"/>
 	
 	<xsl:template match="liste_clients_deb">
 	  <xsl:apply-templates select="header"/>
 	  <xsl:apply-templates select="details"/>
 	</xsl:template>
 	
 	<xsl:include href="header.xslt"/>
 	<xsl:include href="lib.xslt"/>
 	
 	<xsl:template match="details">
 	<xsl:call-template name="titre1"><xsl:with-param name="titre" select="'Informations détaillées'"/></xsl:call-template>;
 	;
 	Index;Numéro client;Numéro dossier;Nom client;Numéro compte;Découvert;
 	<xsl:apply-templates select="client"/>
 	</xsl:template>
 	
 	<xsl:template match="client">
 	  <xsl:value-of select="translate(index,';','')"/>;<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(id_doss,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(num_cpte,';','')"/>;<xsl:value-of select="translate(encours_client,';','')"/>;
 	</xsl:template>
 	
</xsl:stylesheet>