<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:include href="page_layout.xslt" />
	<xsl:include href="header.xslt" />
	<xsl:include href="criteres_recherche.xslt" />
	<xsl:include href="footer.xslt" />
	<xsl:include href="lib.xslt" />

	<xsl:template match="/">
		<fo:root>
			<xsl:call-template name="page_layout_A4_paysage" />
			<xsl:apply-templates select="operations_deplace_clients_interne" />
		</fo:root>
	</xsl:template>

	<xsl:template match="operations_deplace_clients_interne">
		<fo:page-sequence master-reference="main" font-size="6pt" font-family="Helvetica">
			<xsl:apply-templates select="header" />
			<xsl:call-template name="footer" />
			<fo:flow flow-name="xsl-region-body">
				<xsl:apply-templates select="header_contextuel" />
				<xsl:apply-templates select="transactions" />
				<xsl:apply-templates select="summary" />
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>

	<xsl:template match="transactions">
		<fo:table border-collapse="collapse" width="100%" table-layout="fixed">
			<fo:table-column column-width="proportional-column-width(1)" />
			<fo:table-column column-width="proportional-column-width(15)" />
			<fo:table-column column-width="proportional-column-width(10)" />

			<fo:table-header>
				<!-- Empty row -->				
				<fo:table-row column-number="3">
					<fo:table-cell display-align="center">
						<fo:block text-align="left"> <fo:leader /> </fo:block>
					</fo:table-cell>
				</fo:table-row>
			
				<fo:table-row font-weight="bold">
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">Date</fo:block>
					</fo:table-cell>
					<fo:table-cell display-align="center" border="0.1pt solid gray">						
						<fo:block text-align="center">
							Movements in agency <xsl:value-of select="infos_agences/nom_agence_locale" />
						</fo:block>
					</fo:table-cell>
					<fo:table-cell display-align="center" border="0.1pt solid gray">						
						<fo:block text-align="center">
							Movements in agency <xsl:value-of select="infos_agences/nom_agence_distante" />
						</fo:block>
					</fo:table-cell>
				</fo:table-row>

				<fo:table-row>
					<!-- Date (vide) -->
					<fo:table-cell display-align="center" border="0.1pt solid gray"
						font-weight="bold">
						<fo:block text-align="center"></fo:block>
					</fo:table-cell>

					<!-- Transactions distant titres -->
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:table border-collapse="collapse" width="100%" table-layout="fixed">

							<fo:table-column column-width="proportional-column-width(3)" />							
							<fo:table-column column-width="proportional-column-width(3)" />
							<fo:table-column column-width="proportional-column-width(4)" />
							<fo:table-column column-width="proportional-column-width(3)" />
							<fo:table-column column-width="proportional-column-width(24)" />

							<fo:table-body>
								<fo:table-row font-weight="bold">

									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:block text-align="center">Transaction No.</fo:block>
									</fo:table-cell>					

									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:block text-align="center">Operator</fo:block>
									</fo:table-cell>

									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:block text-align="center">Agency</fo:block>
									</fo:table-cell>

									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:block text-align="center">Client No.</fo:block>
									</fo:table-cell>

									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:table border="none" width="100%" table-layout="fixed">
																					
											<fo:table-column column-width="proportional-column-width(6)" />
											<fo:table-column column-width="proportional-column-width(3)" />
											<fo:table-column column-width="proportional-column-width(4)" />
											<fo:table-column column-width="proportional-column-width(3)" />
											<fo:table-column column-width="proportional-column-width(3)" />
															
											<fo:table-body>
												<fo:table-row>
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Designation</fo:block>
													</fo:table-cell>
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Account</fo:block>
													</fo:table-cell>
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Client account</fo:block>
													</fo:table-cell>
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Debit</fo:block>
													</fo:table-cell>
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Credit</fo:block>
													</fo:table-cell>
												</fo:table-row>
											</fo:table-body>
										</fo:table>
									</fo:table-cell>

								</fo:table-row>
							</fo:table-body>

						</fo:table>
					</fo:table-cell>

					<!-- Transactions local titres -->
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:table border-collapse="collapse" width="100%" table-layout="fixed">

							<fo:table-column column-width="proportional-column-width(3)" />							
							<fo:table-column column-width="proportional-column-width(2)" />
							<fo:table-column column-width="proportional-column-width(4)" />	
							<fo:table-column column-width="proportional-column-width(13)" />						

							<fo:table-body>
								<fo:table-row font-weight="bold">

									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:block text-align="center">Transaction No.</fo:block>
									</fo:table-cell>								

									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:block text-align="center">Operator</fo:block>
									</fo:table-cell>

									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:block text-align="center">External Agency</fo:block>
									</fo:table-cell>
																	
									<fo:table-cell display-align="center" border="0.1pt solid gray">
										<fo:table border="none" width="100%" table-layout="fixed">
											<fo:table-column column-width="proportional-column-width(4)" />
											<fo:table-column column-width="proportional-column-width(3)" />											
											<fo:table-column column-width="proportional-column-width(3)" />
											<fo:table-column column-width="proportional-column-width(3)" />
											<fo:table-body>
												<fo:table-row>
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Designation</fo:block>
													</fo:table-cell>
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Acccount</fo:block>
													</fo:table-cell>													
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Debit</fo:block>
													</fo:table-cell>
													<fo:table-cell display-align="center" border="0.1pt solid gray">
														<fo:block text-align="center">Credit</fo:block>
													</fo:table-cell>
												</fo:table-row>
											</fo:table-body>
										</fo:table>
									</fo:table-cell>

								</fo:table-row>
							</fo:table-body>
						</fo:table>

					</fo:table-cell>
				</fo:table-row>
				
				<!-- Empty row -->				
				<fo:table-row column-number="3">
					<fo:table-cell display-align="center">
						<fo:block text-align="left"> <fo:leader /> </fo:block>
					</fo:table-cell>
				</fo:table-row>
				
			</fo:table-header>

			<fo:table-body>		
					
				<xsl:for-each select="transaction">
					<fo:table-row>
					
						<!-- date -->
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:block text-align="center">
								<xsl:value-of select="date_transac" />
							</fo:block>
						</fo:table-cell>
						
						<!-- Infos transactions distant -->
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							<fo:table border="none" width="100%" table-layout="fixed">
							
								<fo:table-column column-width="proportional-column-width(3)" />								
								<fo:table-column column-width="proportional-column-width(3)" />
								<fo:table-column column-width="proportional-column-width(4)" />
								<fo:table-column column-width="proportional-column-width(3)" />
								<fo:table-column column-width="proportional-column-width(24)" />
									
								<fo:table-body>
									<fo:table-row>
										<fo:table-cell display-align="center" border="0.1pt solid gray">
											<fo:block text-align="center">
												<xsl:value-of select="his_data_local/trans_local" />
											</fo:block>
										</fo:table-cell>										
										<fo:table-cell display-align="center" border="0.1pt solid gray">
											<fo:block text-align="center">
												<xsl:value-of select="his_data_local/login_local" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell display-align="center" border="0.1pt solid gray">
											<fo:block text-align="center">
												<xsl:value-of select="his_data_local/agence_local" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell display-align="center" border="0.1pt solid gray">
											<fo:block text-align="center">
												<xsl:value-of select="his_data_local/client_local" />
											</fo:block>
										</fo:table-cell>
										
										<!-- Les ecritures  -->
										<fo:table-cell display-align="center" border="0.1pt solid gray">
																											
											<xsl:for-each select="his_data_local/ligne_ecritures_local/ecriture_local">
																				
													<fo:table border="none" width="100%" table-layout="fixed">
														<fo:table-column column-width="proportional-column-width(6)" />
														<fo:table-column column-width="proportional-column-width(13)" />
															
														<fo:table-body>
															<fo:table-row>
																<fo:table-cell display-align="center" border="0.1pt solid gray">
																	<fo:block text-align="right">
																		<xsl:value-of select="libel_ecriture_local" />
																	</fo:block>
																</fo:table-cell>		
																
																<!-- Les mouvements -->														
																<fo:table-cell display-align="center" border="0.1pt solid gray">
																	<fo:block text-align="right">
					
																		<xsl:for-each select="ligne_mouvements_local/mouvement_local">
																							
																			<fo:table border="none" width="100%" table-layout="fixed">
																				<fo:table-column column-width="proportional-column-width(3)" />
																				<fo:table-column column-width="proportional-column-width(4)" />
																				<fo:table-column column-width="proportional-column-width(3)" />
																				<fo:table-column column-width="proportional-column-width(3)" />
					
																				<fo:table-body>
																					<fo:table-row>
																						<fo:table-cell display-align="center" border="0.1pt solid gray">
																							<fo:block text-align="center">
																								<xsl:value-of select="compte_local" />
																							</fo:block>
																						</fo:table-cell>
																						<fo:table-cell display-align="center" border="0.1pt solid gray">
																							<fo:block text-align="center">
																								<xsl:value-of select="compte_client_local" />
																							</fo:block>
																						</fo:table-cell>
																						<fo:table-cell display-align="center" border="0.1pt solid gray">
																							<fo:block text-align="center">
																								<xsl:value-of select="montant_debit_local" />
																							</fo:block>
																						</fo:table-cell>
																						<fo:table-cell display-align="center" border="0.1pt solid gray">
																							<fo:block text-align="center">
																								<xsl:value-of select="montant_credit_local" />
																							</fo:block>
																						</fo:table-cell>
																					</fo:table-row>
																				</fo:table-body>
																			</fo:table>
					
																		</xsl:for-each>
					
																	</fo:block>
																</fo:table-cell>															
																<!-- end : mouvements -->		
																
															</fo:table-row>
														</fo:table-body>			
																
													</fo:table>			
											</xsl:for-each>
											
										</fo:table-cell>
										<!--  end: ecritures -->
																				
									</fo:table-row>
								</fo:table-body>
							</fo:table>
						</fo:table-cell>
						<!-- end : infos distant -->
						
						<!-- Infos transactions local -->
						<fo:table-cell display-align="center" border="0.1pt solid gray">
							
							<fo:table border="none" width="100%" table-layout="fixed">							
							
							<fo:table-column column-width="proportional-column-width(3)" />							
							<fo:table-column column-width="proportional-column-width(2)" />
							<fo:table-column column-width="proportional-column-width(4)" />	
							<fo:table-column column-width="proportional-column-width(13)" />				
								
								<fo:table-body>
									<fo:table-row>
										<fo:table-cell display-align="center" border="0.1pt solid gray">
											<fo:block text-align="center">
												<xsl:value-of select="his_data_distant/trans_distant" />
											</fo:block>
										</fo:table-cell>										
										<fo:table-cell display-align="center" border="0.1pt solid gray">
											<fo:block text-align="center">
												<xsl:value-of select="his_data_distant/login_distant" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell display-align="center" border="0.1pt solid gray">
											<fo:block text-align="center">
												<xsl:value-of select="his_data_distant/agence_distant" />
											</fo:block>
										</fo:table-cell>								
										
										<!-- Les ecritures  -->
										<fo:table-cell display-align="center" border="0.1pt solid gray">
																											
											<xsl:for-each select="his_data_distant/ligne_ecritures_distant/ecriture_distant">
																				
													<fo:table border="none" width="100%" table-layout="fixed">
														<fo:table-column column-width="proportional-column-width(4)" />
														<fo:table-column column-width="proportional-column-width(9)" />												
															
														<fo:table-body>
															<fo:table-row>
																<fo:table-cell display-align="center" border="0.1pt solid gray">
																	<fo:block text-align="right">
																		<xsl:value-of select="libel_ecriture_distant" />
																	</fo:block>
																</fo:table-cell>		
																
																<!-- Les mouvements -->														
																<fo:table-cell display-align="center" border="0.1pt solid gray">
																	<fo:block text-align="right">
					
																		<xsl:for-each select="ligne_mouvements_distant/mouvement_distant">
																							
																			<fo:table border="none" width="100%" table-layout="fixed">
																				<fo:table-column column-width="proportional-column-width(3)" />
																				<fo:table-column column-width="proportional-column-width(3)" />
																				<fo:table-column column-width="proportional-column-width(3)" />
					
																				<fo:table-body>
																					<fo:table-row>
																						<fo:table-cell display-align="center" border="0.1pt solid gray">
																							<fo:block text-align="center">
																								<xsl:value-of select="compte_distant" />
																							</fo:block>
																						</fo:table-cell>
																						<fo:table-cell display-align="center" border="0.1pt solid gray">
																							<fo:block text-align="center">
																								<xsl:value-of select="montant_debit_distant" />
																							</fo:block>
																						</fo:table-cell>
																						<fo:table-cell display-align="center" border="0.1pt solid gray">
																							<fo:block text-align="center">
																								<xsl:value-of select="montant_credit_distant" />
																							</fo:block>
																						</fo:table-cell>
																					</fo:table-row>
																				</fo:table-body>
																			</fo:table>
					
																		</xsl:for-each>
					
																	</fo:block>
																</fo:table-cell>															
																<!-- end : mouvements -->		
																
															</fo:table-row>
														</fo:table-body>			
																
													</fo:table>			
											</xsl:for-each>
											
										</fo:table-cell>
										<!--  end: ecritures -->
										
									</fo:table-row>
								</fo:table-body>								
							</fo:table>	

						</fo:table-cell>
						<!-- end : Infos transactions local -->
						
					</fo:table-row>	
					
					<!-- Empty row -->				
					<fo:table-row column-number="3">
						<fo:table-cell display-align="center">
							<fo:block text-align="left"> <fo:leader /> </fo:block>
						</fo:table-cell>
					</fo:table-row>					
									
				</xsl:for-each>		
								
			</fo:table-body>
		</fo:table>
	</xsl:template>
	
	<!-- end: transactions -->
	
	<!-- start : summary -->
	
	<xsl:template match="summary">
		<fo:table border-collapse="collapse" width="88%" table-layout="fixed">
			<fo:table-column column-width="proportional-column-width(10)" />
			<fo:table-column column-width="proportional-column-width(3)" />	
			<fo:table-column column-width="proportional-column-width(10)" />
			<fo:table-column column-width="proportional-column-width(3)" />	
			
			<fo:table-header>
				<!-- Empty row -->				
				<fo:table-row column-number="4">
					<fo:table-cell display-align="center">
						<fo:block text-align="left"> <fo:leader /> </fo:block>
					</fo:table-cell>
				</fo:table-row>
				
				<fo:table-row font-weight="bold">
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">
							Agency <xsl:value-of select="agence_locale" />
						</fo:block>
					</fo:table-cell>
					
					<fo:table-cell display-align="center" border="0.1pt solid gray">						
						<fo:block text-align="center">Amount</fo:block>
					</fo:table-cell>
					
					<fo:table-cell display-align="center" border="0.1pt solid gray">						
						<fo:block text-align="center">
							Agency <xsl:value-of select="agence_externe" />
						</fo:block>					
					</fo:table-cell>
					
					<fo:table-cell display-align="center" border="0.1pt solid gray">						
						<fo:block text-align="center">Amount</fo:block>
					</fo:table-cell>
				</fo:table-row>
				
			</fo:table-header>	
			
			<fo:table-body>	
				<fo:table-row>				
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">
							Total deposit in <xsl:value-of select="agence_externe" /> by <xsl:value-of select="agence_locale" />
						</fo:block>
					</fo:table-cell>
							
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center"><xsl:value-of select="total_depot" /></fo:block>
					</fo:table-cell>	
								
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">
							Total deposit by <xsl:value-of select="agence_locale" /> in <xsl:value-of select="agence_externe" />
						</fo:block>
					</fo:table-cell>	
								
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center"><xsl:value-of select="total_depot" /></fo:block>
					</fo:table-cell>								
				</fo:table-row>	
				
				<fo:table-row>				
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">							
							Total withdrawal in <xsl:value-of select="agence_externe" /> by <xsl:value-of select="agence_locale" />					
						</fo:block>
					</fo:table-cell>
							
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center"><xsl:value-of select="total_retrait" /></fo:block>
					</fo:table-cell>	
								
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center">							
							Total withdrawal by <xsl:value-of select="agence_locale" /> in <xsl:value-of select="agence_externe" />					
						</fo:block>
					</fo:table-cell>	
								
					<fo:table-cell display-align="center" border="0.1pt solid gray">
						<fo:block text-align="center"><xsl:value-of select="total_retrait" /></fo:block>
					</fo:table-cell>								
				</fo:table-row>	
					
			</fo:table-body>			
				
		</fo:table>	
				
	</xsl:template>
	
</xsl:stylesheet>
