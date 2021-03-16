<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template name="titre_niv0">
    <xsl:param name="titre"/>
    <fo:block border-color="silver" border="1pt solid" text-align="left" font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0cm" font-weight="bold">
      <xsl:value-of select="$titre"/>
    </fo:block>
  </xsl:template>
  <xsl:template name="titre_niv1">
    <xsl:param name="titre"/>
    <fo:block font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0.5cm" font-weight="bold" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
      <xsl:value-of select="$titre"/>
    </fo:block>
  </xsl:template>
  <xsl:template name="titre_niv1_english">
    <xsl:param name="titre"/>
    <fo:block font-size="12pt" space-after.optimum="0.2cm" space-before.optimum="0.5cm" font-weight="bold" border-bottom-width="0.5pt" border-bottom-style="solid" border-bottom-color="black">
      <xsl:value-of select="$titre"/>
    </fo:block>
  </xsl:template>
  <xsl:template name="titre_niv2">
    <xsl:param name="titre"/>
    <fo:block font-size="10pt" space-after.optimum="0.2cm" space-before.optimum="0.2cm" border-bottom-width="0.3pt" border-bottom-style="solid" border-bottom-color="black">
      <xsl:value-of select="$titre"/>
    </fo:block>
  </xsl:template>
  <xsl:template name="titre_niv3">
    <xsl:param name="titre"/>
    <fo:block text-align="left" font-size="14pt" font-weight="bold" border-top-style="solid" border-bottom-style="solid" space-before="0.5in">
      <xsl:value-of select="$titre"/>
    </fo:block>
  </xsl:template>
  <xsl:variable name="point_liste">&lt;fo:inline font-family="ZapfDingbats"&gt;➥&lt;/fo:inline&gt;</xsl:variable>
  <xsl:variable name="ciseaux">&lt;fo:inline font-family="ZapfDingbats"&gt;✂&lt;/fo:inline&gt;</xsl:variable>
  <xsl:variable name="crayon">&lt;fo:inline font-family="ZapfDingbats"&gt;✍&lt;/fo:inline&gt;</xsl:variable>
  <xsl:variable name="icone_tel">&lt;fo:inline font-family="ZapfDingbats"&gt;✆&lt;/fo:inline&gt;</xsl:variable>
  <xsl:variable name="icone_fleche">&lt;fo:inline font-family="ZapfDingbats"&gt;➔&lt;/fo:inline&gt;</xsl:variable>
</xsl:stylesheet>
