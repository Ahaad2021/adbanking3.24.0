<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_paysage"></xsl:call-template>
		<xsl:apply-templates select="journal_annulations"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="criteres_recherche.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="journal_annulations">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="header_contextuel"/> 
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Détails'"/></xsl:call-template>
			<fo:table>
		
				<fo:table-column column-width="2cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="1.5cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="2.9cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="3.5cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="6cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="2cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="4.2cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="2.8cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>
				<fo:table-column column-width="2.8cm" border-left-width="0.3pt" border-left-style="solid" border-left-color="gray"/>                            

				<fo:table-header>
					<fo:table-row font-weight="bold">
						<fo:table-cell>
							<fo:block text-align="center" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">Date</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">N° Pièce</fo:block>
						</fo:table-cell>	
						<fo:table-cell>
							<fo:block text-align="center" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">Fonction</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">Réf écriture</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">Opération</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">Compte</fo:block>
						</fo:table-cell> 
						<fo:table-cell>
							<fo:block text-align="center" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">Intitulé Compte</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">Débit</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="center" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">Crédit</fo:block>
						</fo:table-cell>
<fo:table-cell>
							 <fo:block text-align="left" border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black">.</fo:block>
						</fo:table-cell>	                           

					</fo:table-row>
				</fo:table-header>
				<fo:table-body>

					<xsl:apply-templates select="ligne"/>
                                        <xsl:apply-templates select="ligne_totaux"/>

	<fo:table-row font-weight="bold" font-style="italic" >




<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

                        <fo:block font-weight="bold" text-align="right"> </fo:block>
                       

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

		<fo:block font-weight="bold" text-align="center"> </fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>


<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

	</fo:table-row>	

 
                          </fo:table-body>
				 
			</fo:table>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>


<xsl:template match="header_contextuel">
	<xsl:apply-templates select="criteres_recherche"/>
</xsl:template>


<xsl:template match="ligne">



	


	<fo:table-row  font-weight="bold" color="gray">
		<fo:table-cell  space-before.optimum="0.2cm">
			<fo:block text-align="center" space-before.optimum="0.2cm"><xsl:value-of select="date_comptable"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center" space-before.optimum="0.2cm"><xsl:value-of select="num_piece"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block space-before.optimum="0.2cm"><xsl:value-of select="fonction"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center" space-before.optimum="0.2cm"><xsl:value-of select="ref_ecriture"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block space-before.optimum="0.2cm"><xsl:value-of select="operation"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center" space-before.optimum="0.2cm"><xsl:value-of select="compte"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block space-before.optimum="0.2cm"><xsl:value-of select="libel_cpte"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center" space-before.optimum="0.2cm"><xsl:value-of select="montant_debit"/></fo:block>
		</fo:table-cell>
		<fo:table-cell>
			<fo:block text-align="center" space-before.optimum="0.2cm"><xsl:value-of select="montant_credit"/></fo:block>
		</fo:table-cell>
	</fo:table-row>	
</xsl:template>

<xsl:template match="ligne_totaux">

	<fo:table-row font-weight="bold" font-style="italic" >

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

                        <fo:block font-weight="bold" text-align="right">.....</fo:block>
                        <fo:block font-weight="bold" text-align="right"> <xsl:value-of select="totaux"/> </fo:block>
                        <fo:block font-weight="bold" text-align="right">.....</fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

		<fo:block font-weight="bold" text-align="center"> </fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

			<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

				<fo:block font-weight="bold" text-align="center"></fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">

	<fo:block font-weight="bold" text-align="right">.</fo:block>
	<fo:block font-weight="bold" text-align="center"><xsl:value-of select="total_debit"/></fo:block>
	<fo:block font-weight="bold" text-align="right">.</fo:block>

</fo:table-cell>

<fo:table-cell space-before.optimum="0.5cm" border-top-width="0.5pt" border-top-style="solid" border-top-color="black">
                         
	<fo:block font-weight="bold" text-align="right">.</fo:block> 
        <fo:block font-weight="bold" text-align="center"><xsl:value-of select="total_credit"/></fo:block>
	<fo:block font-weight="bold" text-align="right">.</fo:block>

</fo:table-cell>

	</fo:table-row>	


</xsl:template>

</xsl:stylesheet>
