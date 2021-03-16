<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template name="page_layout_A4_paysage">
		<fo:layout-master-set>
			<fo:simple-page-master  margin-left="1cm" margin-right="1cm" margin-top="0.5cm" margin-bottom="0.5cm" master-name="main" page-width="29.7cm" page-height="21cm">
				<fo:region-body margin-top="1.5cm" margin-bottom="1cm" region-name="xsl-region-body"/>
				<fo:region-before extent="1.5cm" region-name="xsl-region-before"/>
				<fo:region-after extent="1cm" region-name="xsl-region-after"/>
			</fo:simple-page-master>
		</fo:layout-master-set>
</xsl:template>

<xsl:template name="page_layout_A5_paysage">
		<fo:layout-master-set>
			<fo:simple-page-master  margin-left="1cm" margin-right="1cm" margin-top="0.5cm" margin-bottom="0.5cm" master-name="main" page-width="14.8cm" page-height="21cm">
				<fo:region-body margin-top="1.5cm" margin-bottom="1cm" region-name="xsl-region-body"/>
				<fo:region-before extent="1.5cm" region-name="xsl-region-before"/>
				<fo:region-after extent="1cm" region-name="xsl-region-after"/>
			</fo:simple-page-master>
		</fo:layout-master-set>
</xsl:template>

<xsl:template name="page_layout_A5_portrait_no_region">
		<fo:layout-master-set>
			<fo:simple-page-master  margin-left="1cm" margin-right="1cm" margin-top="0.5cm" margin-bottom="0.5cm" master-name="main" page-width="21cm" page-height="14.8cm">
				<fo:region-body region-name="xsl-region-body"/>
			</fo:simple-page-master>
		</fo:layout-master-set>
</xsl:template>

<xsl:template name="page_layout_A4_portrait_no_region">
		<fo:layout-master-set>
			<fo:simple-page-master  margin-left="1cm" margin-right="1cm" margin-top="0.5cm" margin-bottom="0.5cm" master-name="main" page-width="21cm" page-height="29.7cm">
				<fo:region-body region-name="xsl-region-body"/>
			</fo:simple-page-master>
		</fo:layout-master-set>
</xsl:template>

<xsl:template name="page_layout_A4_portrait">
		<fo:layout-master-set>
			<fo:simple-page-master  margin-left="0.5cm" margin-right="0.5cm" margin-top="1cm" margin-bottom="1cm" master-name="main" page-width="21cm" page-height="29.7cm">
				<fo:region-body margin-top="1.5cm" margin-bottom="1cm" region-name="xsl-region-body"/>
				<fo:region-before extent="1.5cm" region-name="xsl-region-before"/>
				<fo:region-after extent="1cm" region-name="xsl-region-after"/>
			</fo:simple-page-master>
		</fo:layout-master-set>
</xsl:template>

</xsl:stylesheet>
