<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:include href="lib.xslt"/>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>

<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"></xsl:call-template>
		<xsl:apply-templates select="stat_agence"/>
	</fo:root>
</xsl:template>

<xsl:template match="stat_agence">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"></xsl:call-template>
		<fo:flow flow-name="xsl-region-body">
			<xsl:apply-templates select="infos_globales"/>
			<xsl:apply-templates select="ratio_prud"/>
			<xsl:apply-templates select="indic_perf"/>
			<xsl:apply-templates select="couverture"/>
			<xsl:apply-templates select="indic_productivite"/>
			<xsl:apply-templates select="indic_impact"/>
			<xsl:if test="enreg_agence/is_siege='true'">
			<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Liste des agences consolidées'"/></xsl:call-template>
	 <fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(3)"/>
		<fo:table-column column-width="proportional-column-width(3)"/>
		<fo:table-column column-width="proportional-column-width(3)"/>
  <fo:table-header>
		    <fo:table-row font-weight="bold">
			     <fo:table-cell display-align="center" border="0.1pt solid gray">
				      <fo:block text-align="center">Identifiant agence </fo:block>
			    </fo:table-cell>
			    <fo:table-cell display-align="center" border="0.1pt solid gray">
				      <fo:block text-align="center"> Libellé agence  </fo:block>
			    </fo:table-cell>
			    <fo:table-cell display-align="center" border="0.1pt solid gray">
				     <fo:block text-align="center"> Date dernier mouvement </fo:block>
			    </fo:table-cell>
	      </fo:table-row>
	    </fo:table-header>
	    <fo:table-body>
	        <xsl:apply-templates select="enreg_agence"/>
	    </fo:table-body>
     </fo:table>
     </xsl:if>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="infos_globales">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Informations administratives'"/></xsl:call-template>
	<fo:table border-collapse="collapse" border-separation.inline-progression-direction="10pt" width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-body>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Début de période : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="debut_periode"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Fin de période : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="fin_periode"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Responsable : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="responsable"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Type structure : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="type_structure"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Date agrément : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="date_agrement"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">N° agrément : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="num_agrement"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="ratio_prud">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Ratios prudentiels (%)'"/></xsl:call-template>
	<fo:table width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-body>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Limitation des prêts aux dirigeants : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="limit_prets_dirig"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Limitation du risque pris sur un seul membre : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="limit_risk_membre"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Taux de transformation de l'épargne en crédit : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="tx_transform"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="indic_perf">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Qualité du portefeuille (%)'"/></xsl:call-template>
	<fo:table width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-body>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Portefeuille à risque sur 1 échéance : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="risk_1_ech"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Portefeuille à risque sur 2 échéances : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="risk_2_ech"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Portefeuille à risque sur 3 échéances : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="risk_3_ech"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Portefeuille à risque à 30 jours : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="risk_30j_ech"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Provisions pour créances douteuses : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="tx_provisions"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Taux de rééchelonnement des prêts : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="tx_reech"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Taux d'abandon de créances : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="tx_perte"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="couverture">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Couverture'"/></xsl:call-template>
	<fo:table width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-body>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Nombre de crédits en cours : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="nbr_credits"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">En-cours total de crédit : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="portefeuille"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Nombre de comptes d'épargne actifs : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="nbre_cpt_epargne"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">En-cours total de l'épargne : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="total_epargne"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Taux de renouvellement de crédits (%) : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="tx_renouvellement_credits"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Montant moyen des premiers crédits : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="first_credit_moyen"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Montant médian des premiers crédits : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="first_credit_median"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Montant moyen des crédits : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="credit_moyen"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Montant médian des crédits : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="credit_median"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Solde moyen des comptes d'épargne : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargne_moyen_cpte"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Solde médian des comtpes d'épargne : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargne_median_cpte"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Volume moyen d'épargne des clients : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargne_moyen_client"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Volume médian d'épargne des clients : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargne_median_client"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		</fo:table-body>
	</fo:table>
</xsl:template>


<xsl:template match="indic_productivite">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Productivité'"/></xsl:call-template>
	<fo:table width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-body>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Rendement du portefeuille (%) : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="rendement_portefeuille"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Rendement théorique du portefeuille (%) : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="rendement_theorique"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Ecart de rendement (%) : </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="ecart_rendement"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		</fo:table-body>
	</fo:table>
</xsl:template>


<xsl:template match="indic_impact">
	<xsl:call-template name="titre_niv1"><xsl:with-param name="titre" select="'Impact'"/></xsl:call-template>
	<fo:table width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(2)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
	<fo:table-header>
		<fo:table-row font-weight="bold">
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Statut juridique </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Epargnants</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Emprunteurs</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Total des clients</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</fo:table-header>
	<fo:table-body>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Personnes Physiques </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargnants/nbre_individus/nbre_pp"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="emprunteurs/nbre_individus/nbre_pp"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="clients/nbre_individus/nbre_pp"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell padding-start="10pt">
				<fo:block text-align="center">Hommes (%) </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargnants/nbre_individus/prc_homme"/> %</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="emprunteurs/nbre_individus/prc_homme"/> %</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="clients/nbre_individus/prc_homme"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell padding-start="10pt">
				<fo:block text-align="center">Femmes (%) </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargnants/nbre_individus/prc_femme"/> %</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="emprunteurs/nbre_individus/prc_femme"/> %</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="clients/nbre_individus/prc_femme"/> %</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Personnes Morales</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargnants/nbre_individus/nbre_pm"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="emprunteurs/nbre_individus/nbre_pm"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="clients/nbre_individus/nbre_pm"/></fo:block>
			</fo:table-cell>
		</fo:table-row>

		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Groupes Informels</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargnants/nbre_individus/nbre_gi"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="emprunteurs/nbre_individus/nbre_gi"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="clients/nbre_individus/nbre_gi"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Groupes solidaires</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargnants/nbre_individus/nbre_gs"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="emprunteurs/nbre_individus/nbre_gs"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="clients/nbre_individus/nbre_gs"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row font-weight="bold">
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center">Total</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="epargnants/nbre_individus/nbre_total"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="emprunteurs/nbre_individus/nbre_total"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="clients/nbre_individus/nbre_total"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
	</fo:table-body>
	</fo:table>
	<fo:table width="100%" table-layout="fixed">
		<fo:table-column column-width="proportional-column-width(3)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-column column-width="proportional-column-width(1)"/>
		<fo:table-body>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block font-weight="bold" text-align="center">Nombre moyen de membres emprunteurs</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"> par groupe informel : <xsl:value-of select="nbr_moyen_gi"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"> par groupe solidaire : <xsl:value-of select="nbr_moyen_gs"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block font-weight="bold" text-align="center">Nombre total de membres emprunteurs</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"> par groupe informel : <xsl:value-of select="total_membres_empr_gi"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"> par groupe solidaire : <xsl:value-of select="total_membres_empr_gs"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		<fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block  text-align="center" font-weight="bold">Répartition par groupe solidaire  </fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="total_hommes_gs"/> hommes</fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="total_femmes_gs"/> femmes</fo:block>
			</fo:table-cell>
		</fo:table-row>
		</fo:table-body>
	</fo:table>
</xsl:template>

<xsl:template match="enreg_agence">

 <fo:table-row>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="id_ag"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="libel_ag"/></fo:block>
			</fo:table-cell>
			<fo:table-cell display-align="center" border="0.1pt solid gray">
				<fo:block text-align="center"><xsl:value-of select="date_max"/></fo:block>
			</fo:table-cell>
		</fo:table-row>
		</xsl:template>

</xsl:stylesheet>
