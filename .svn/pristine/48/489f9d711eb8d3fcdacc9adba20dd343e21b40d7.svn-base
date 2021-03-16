<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<xsl:template match="/">
	<fo:root>
		<xsl:call-template name="page_layout_A4_portrait"/>
		<xsl:apply-templates select="fiche_personne_morale"/>
	</fo:root>
</xsl:template>

<xsl:include href="page_layout.xslt"/>
<xsl:include href="header.xslt"/>
<xsl:include href="footer.xslt"/>
<xsl:include href="lib.xslt"/>

<xsl:template match="fiche_personne_morale">
	<fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
		<xsl:apply-templates select="header"/>
		<xsl:call-template name="footer"/>
		<fo:flow flow-name="xsl-region-body">			
			<xsl:apply-templates select="fiche_pm"/>
		</fo:flow>
	</fo:page-sequence>
</xsl:template>

<xsl:template match="fiche_pm">
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Numéro client : </fo:block> </fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="num_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Etat client : </fo:block> </fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="etat_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	 <fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Statut juridique : </fo:block> </fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="stat_jur_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

        <fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Qualité : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="qualite_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Ancien numéro : </fo:block> </fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="anc_num_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Date d'adhésion : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="date_adh_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Date création : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="date_cre_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
	
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Gestionnaire : </fo:block> </fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="gest_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Langue de correspondance : </fo:block> </fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="lang_corres_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Raison sociale : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="raison_soc_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Abréviation : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="abreviation_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
        
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Nature Juridique : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="nature_jur_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Nombre d'hommes du groupe : </fo:block></fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="nbre_hommes_grp"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Nombre de femmes du groupe : </fo:block></fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="nbre_femmes_grp"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
	
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Catégorie : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="categorie_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Date notaire : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="date_notaire_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Date dépôt greffe : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="date_greffe_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Lieu dépôt greffe : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="lieu_greffe_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Numéro registre : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="num_registre_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Patrimoine : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="patrimoine_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>	

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Adresse : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="adresse_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Localisation 1 : </fo:block> </fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="loc1_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Localisation 2 : </fo:block> </fo:table-cell>
			<fo:table-cell><fo:block><xsl:value-of select="loc2_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Code postal : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="code_postal_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
	
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Ville : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="ville_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Pays : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="pays_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
	
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Numéro téléphone : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="num_tel_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
	
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Fax : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="fax_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
		
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Email : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="email_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>		

	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Secteur d'activité : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="sect_act_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
	
	<fo:table border="none" border-collapse="separate">
		<fo:table-column column-width="5cm"/>
		<fo:table-column/>
		<fo:table-body>	<fo:table-row>
			<fo:table-cell font-weight="bold"><fo:block>Activité professionnelle : </fo:block></fo:table-cell>
        		<fo:table-cell><fo:block><xsl:value-of select="activ_prof_pm"/></fo:block></fo:table-cell>
		</fo:table-row></fo:table-body>
	</fo:table>
		
	<fo:block>---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</fo:block>
</xsl:template>

</xsl:stylesheet>
