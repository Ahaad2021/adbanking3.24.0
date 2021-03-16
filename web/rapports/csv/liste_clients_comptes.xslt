<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text"/>
<xsl:template match="liste_client_compte">
  <xsl:apply-templates select="header"/> 
   <xsl:apply-templates select="detail_client_compte"/>
</xsl:template>

<xsl:include href="header.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="detail_client_compte">
<xsl:call-template name="titre1"><xsl:with-param name="titre" select="@type"/></xsl:call-template>;<xsl:value-of select="';'"/>
Num client;Nom client;Date Naissance;Sexe;Pièce d'identité;Numéro Pièce;Adresse;Téléphone;Télécopie;Portable;Email;Compte;Pays;Ville
<xsl:apply-templates select="client"/>
</xsl:template>

<xsl:template match="client">
	<xsl:value-of select="translate(id_client,';','')"/>;<xsl:value-of select="translate(nom,';','')"/>;<xsl:value-of select="translate(date_naiss,';','')"/>;<xsl:value-of select="translate(sexe,';','')"/>;<xsl:value-of select="translate(type_piece,';','')"/>;<xsl:value-of select="translate(numero_piece,';','')"/>;<xsl:value-of select="translate(adresse,';','')"/>;<xsl:value-of select="translate(telephone,';','')"/>;<xsl:value-of select="translate(telecopie,';','')"/>;<xsl:value-of select="translate(portable,';','')"/>;<xsl:value-of select="translate(email,';','')"/>;<xsl:value-of select="translate(compte,';','')"/>;<xsl:value-of select="translate(pays,';','')"/>;<xsl:value-of select="translate(ville,';','')"/>;
</xsl:template>

</xsl:stylesheet>
