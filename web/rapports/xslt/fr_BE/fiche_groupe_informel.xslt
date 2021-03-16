<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="fiche_groupe_informel"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="fiche_groupe_informel">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="fiche_gi"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="fiche_gi">
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Numéro client : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="num_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Etat du client : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="etat_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Statut juridique : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="stat_jur_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Qualité : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="qualite_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Ancien numéro : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="anc_num_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Date d'adhésion : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="date_adh_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Date création : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="date_cre_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Gestionnaire : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="gest_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Langue de correspondance : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="lang_corres_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Nom groupe : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="nom_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Nombre de membre : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="nbr_membre_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Nombre d'hommes : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="nbre_hommes_grp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Nombre de femmes : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="nbre_femmes_grp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Date d'agrément : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="date_agrement_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Adresse : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="adresse_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Localisation 1 : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="loc1_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Localisation 2 : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="loc2_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Code postal : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="code_postal_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Ville : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="ville_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Pays : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="pays_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Numéro téléphone : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="num_tel_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Fax : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="fax_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Email : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="email_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Secteur d'activité : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="sect_act_gi"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:table border="none" border-collapse="separate">
      <fo:table-column column-width="5cm"/>
      <fo:table-column/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell font-weight="bold">
            <fo:block>Zone : </fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>
              <xsl:value-of select="zone"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
    <fo:block>---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</fo:block>
  </xsl:template>
</xsl:stylesheet>
