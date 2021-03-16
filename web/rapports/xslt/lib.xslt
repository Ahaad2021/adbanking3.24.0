<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<!-- Pour l'affichage d'un block titre niveau 0 -->
<xsl:template name="titre_niv0">
	<xsl:param name="titre"/>
	<fo:block  border-color="silver" border="1pt solid" text-align="left" font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0cm" font-weight="bold"><xsl:value-of select="$titre"/></fo:block>
</xsl:template>

<!-- Pour l'affichage d'un block titre niveau 1 -->
<xsl:template name="titre_niv1">
	<xsl:param name="titre"/>
	<fo:block font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0.5cm" font-weight="bold" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black"><xsl:value-of select="$titre"/></fo:block>
</xsl:template>

<!-- Pour l'affichage d'un block titre niveau 1 en Anglais -->
<xsl:template name="titre_niv1_english">
	<xsl:param name="titre"/>
	<fo:block font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0.5cm" font-weight="bold" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black"><xsl:value-of select="$titre"/></fo:block>
</xsl:template>

<!-- Pour l'affichage d'un sous block niveau 2 -->
<xsl:template name="titre_niv2">
	<xsl:param name="titre"/>
	<fo:block font-size="10pt" space-after.optimum="0.2cm" space-before.optimum="0.2cm" border-bottom-width="0.3pt" border-bottom-style="solid" border-bottom-color="black"><xsl:value-of select="$titre"/></fo:block>
</xsl:template>

<xsl:template name="titre_niv3">
	<xsl:param name="titre"/>
	<fo:block text-align="left" font-size="14pt" font-weight="bold" border-top-style="solid" border-bottom-style="solid" space-before="0.5in"><xsl:value-of select="$titre"/></fo:block>
</xsl:template>

<!-- Caractère à afficher devant chaque élément d'une liste -->
<xsl:variable name="point_liste">&lt;fo:inline font-family=&quot;ZapfDingbats&quot;>&#x27A5;&lt;/fo:inline></xsl:variable>

<!-- Caractère ciseaux -->
<xsl:variable name="ciseaux">&lt;fo:inline font-family=&quot;ZapfDingbats&quot;>&#x2702;&lt;/fo:inline></xsl:variable>

<!-- Caractère crayon -->
<xsl:variable name="crayon">&lt;fo:inline font-family=&quot;ZapfDingbats&quot;>&#x270D;&lt;/fo:inline></xsl:variable>

<!-- Caractère telephone -->
<xsl:variable name="icone_tel">&lt;fo:inline font-family=&quot;ZapfDingbats&quot;>&#x2706;&lt;/fo:inline></xsl:variable>

<!-- Caractère flèche -->
<xsl:variable name="icone_fleche">&lt;fo:inline font-family=&quot;ZapfDingbats&quot;>&#x2794;&lt;/fo:inline></xsl:variable>

</xsl:stylesheet>

