<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root>
			<xsl:call-template name="page_layout_A4_portrait_no_region" />
			<xsl:apply-templates select="recu_tps" />
		</fo:root>
	</xsl:template>
	<xsl:include href="page_layout.xslt" />
	<xsl:include href="header.xslt" />
	<xsl:include href="signature.xslt" />
	<xsl:include href="footer.xslt" />
	<xsl:include href="lib.xslt" />
	<xsl:template match="recu_tps">
		<fo:page-sequence master-reference="main" font-size="10pt"
			font-family="Helvetica">
			<fo:flow flow-name="xsl-region-body">
				<xsl:apply-templates select="header" mode="no_region" />
				<fo:block space-before.optimum="0.5cm" />
				<xsl:apply-templates select="body" />
				<fo:block space-before.optimum="2cm" />
				<fo:block text-align="center">
					<xsl:value-of select="$ciseaux"
						disable-output-escaping="yes" />
					--------------------------------------------------------------------------------------------------------------------------------------------------------------
				</fo:block>
				<fo:block space-before.optimum="0.5cm" />
				<xsl:apply-templates select="header" mode="no_region" />
				<fo:block space-before.optimum="0.5cm" />
				<xsl:apply-templates select="body" />
				<fo:block space-before.optimum="2cm" />
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
	<xsl:template match="body">
		<fo:table border-collapse="collapse"
			border-separation.inline-progression-direction="10pt" width="100%"
			table-layout="fixed">
			<fo:table-column column-width="proportional-column-width(1)" />
			<fo:table-column column-width="proportional-column-width(1)" />
			<fo:table-header align="center">
				<fo:table-row font-weight="bold" align="center">
					<xsl:if test="type_transfer_1">
						<fo:table-cell display-align="center"
							number-columns-spanned="2" border="0.1pt solid gray">
							<fo:block text-align="center">Transfer to another shares account</fo:block>
						</fo:table-cell>
					</xsl:if>
					<xsl:if test="type_transfer_2">
						<fo:table-cell display-align="center"
							number-columns-spanned="2" border="0.1pt solid gray">
							<fo:block text-align="center">Transfer to current account
							</fo:block>
						</fo:table-cell>
					</xsl:if>
				</fo:table-row>
			</fo:table-header>
			<fo:table-body>

				<fo:table-row>

					<fo:table-cell display-align="left">

						<fo:list-block>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block></fo:block>
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm" font-weight="bold"
										text-decoration="underline">Source account information </fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Client number:
										<xsl:value-of select="num_client" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Name:
										<xsl:value-of select="nom_client" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Shares account Number:
										<xsl:value-of select="num_cpte_ps" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Nominal value of a share:
										<xsl:value-of select="prix_part" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Number of shares transfered:
										<xsl:value-of select="nbre_parts" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Value of shares transfered:
										<xsl:value-of select="total_ps" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>

							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										New account balance:
										<xsl:value-of select="total_ps_restant" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Number of subscribed shares remaining:
										<xsl:value-of select="nbre_total_ps_sous" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block />
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Number of liberated shares remaining:
										<xsl:value-of select="nbre_total_ps_lib" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>

							<fo:list-item>
								<fo:list-item-label>
									<fo:block></fo:block>
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm">
										Transaction number:
										<xsl:value-of select="num_trans" />
									</fo:block>
								</fo:list-item-body>
							</fo:list-item>
						</fo:list-block>
					</fo:table-cell>
					<fo:table-cell display-align="left">
						<fo:list-block>
							<fo:list-item>
								<fo:list-item-label>
									<fo:block></fo:block>
								</fo:list-item-label>
								<fo:list-item-body>
									<fo:block space-before.optimum="0.3cm" font-weight="bold"
										text-decoration="underline">Recipient account information  </fo:block>
								</fo:list-item-body>
							</fo:list-item>
							<xsl:if test="type_transfer_1">
								<fo:list-item>
									<fo:list-item-label>
										<fo:block></fo:block>
									</fo:list-item-label>
									<fo:list-item-body>
										<fo:block space-before.optimum="0.3cm">
											Recipient client number:
											<xsl:value-of select="num_cli_dest" />
										</fo:block>
									</fo:list-item-body>
								</fo:list-item>
							</xsl:if>
							<xsl:if test="type_transfer_1">
								<fo:list-item>
									<fo:list-item-label>
										<fo:block></fo:block>
									</fo:list-item-label>
									<fo:list-item-body>
										<fo:block space-before.optimum="0.3cm">
											Name of client:
											<xsl:value-of select="nom_cli_dest" />
										</fo:block>
									</fo:list-item-body>
								</fo:list-item>
							</xsl:if>
							<xsl:if test="type_transfer_1">
								<fo:list-item>
									<fo:list-item-label>
										<fo:block></fo:block>
									</fo:list-item-label>
									<fo:list-item-body>
										<fo:block space-before.optimum="0.3cm">
											Account name:
											<xsl:value-of select="libelle_ps" />
										</fo:block>
									</fo:list-item-body>
								</fo:list-item>
								<fo:list-item>
									<fo:list-item-label>
										<fo:block></fo:block>
									</fo:list-item-label>
									<fo:list-item-body>
										<fo:block space-before.optimum="0.3cm">
											Account number:
											<xsl:value-of select="num_compte_dest" />
										</fo:block>
									</fo:list-item-body>
								</fo:list-item>
							</xsl:if>
							<xsl:if test="type_transfer_2">
								<fo:list-item>
									<fo:list-item-label>
										<fo:block></fo:block>
									</fo:list-item-label>
									<fo:list-item-body>
										<fo:block space-before.optimum="0.3cm">
											Account name:
											<xsl:value-of select="libelle_courant" />
										</fo:block>
									</fo:list-item-body>
								</fo:list-item>
								<fo:list-item>
									<fo:list-item-label>
										<fo:block></fo:block>
									</fo:list-item-label>
									<fo:list-item-body>
										<fo:block space-before.optimum="0.3cm">
											Current account number:
											<xsl:value-of select="num_compte_dest" />
										</fo:block>
									</fo:list-item-body>
								</fo:list-item>
								<fo:list-item>
									<fo:list-item-label>
										<fo:block></fo:block>
									</fo:list-item-label>
									<fo:list-item-body>
										<fo:block space-before.optimum="0.3cm">
											Account balance:
											<xsl:value-of select="solde_courant" />
										</fo:block>
									</fo:list-item-body>
								</fo:list-item>
							</xsl:if>
						</fo:list-block>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-body>
		</fo:table>
		<fo:block space-before.optimum="1cm" />
		<xsl:call-template name="signature" />
	</xsl:template>
</xsl:stylesheet>
