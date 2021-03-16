<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
  <xsl:template match="/">
    <fo:root>
      <xsl:call-template name="page_layout_A4_portrait"/>
      <xsl:apply-templates select="fiche_personne_physique"/>
    </fo:root>
  </xsl:template>
  <xsl:include href="page_layout.xslt"/>
  <xsl:include href="header.xslt"/>
  <xsl:include href="footer.xslt"/>
  <xsl:include href="lib.xslt"/>
  <xsl:template match="fiche_personne_physique">
    <fo:page-sequence master-reference="main" font-size="10pt" font-family="Helvetica">
      <xsl:apply-templates select="header"/>
      <xsl:call-template name="footer"/>
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates select="fiche_pp"/>
        <xsl:apply-templates select="liste_groupe"/>
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>
  <xsl:template match="fiche_pp">
    <fo:table border="none" border-collapse="separate" width="100%">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell>
            <fo:table border="none" border-collapse="separate" width="100%">
              <fo:table-column column-width="proportional-column-width(1)"/>
              <fo:table-column column-width="proportional-column-width(1)"/>
              <fo:table-body>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>Client number:</fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="num_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>Ancien numéro : </fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="anc_num_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>Name : </fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="nom_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>First name : </fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="prenom_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>Genre : </fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="sexe_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>Etat civil : </fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="etat_civil_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>Birth date:</fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="date_nais_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>Birth place:</fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="lieu_nais_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
                <fo:table-row>
                  <fo:table-cell border-top-width="0.5pt" font-weight="bold">
                    <fo:block>Pays de naissance:</fo:block>
                  </fo:table-cell>
                  <fo:table-cell border-top-width="0.5pt">
                    <fo:block>
                      <xsl:value-of select="pays_nais_pp"/>
                    </fo:block>
                  </fo:table-cell>
                </fo:table-row>
              </fo:table-body>
            </fo:table>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block text-align="right">
              <fo:external-graphic width="70px" src="{photo_pp}"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Pays de nationalité : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="nationalite_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Type de pièce d'identité: </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="type_piece_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Identity document number: </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="num_piece_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Date expiration pièce d'identité: </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="date_expir_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Address : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="adresse_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Localization 1 : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="loc1_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Localization 2 : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="loc2_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Code postal : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="code_postal_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>City : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="ville_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Countries : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="pays_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Numéro téléphone : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="num_tel_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Fax : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="fax_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Email : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="email_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Activity sector : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="sect_act_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Activité professionnelle: </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="activ_prof_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Etat client : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="etat_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Legal status : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="stat_jur_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Quality : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="qualite_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Subscription date : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="date_adh_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Creation date : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="date_cre_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Manager : </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="gest_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
        <fo:table-row>
          <fo:table-cell border-top-width="0.5pt" font-weight="bold">
            <fo:block>Langue de correspondance: </fo:block>
          </fo:table-cell>
          <fo:table-cell border-top-width="0.5pt">
            <fo:block>
              <xsl:value-of select="lang_corres_pp"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="liste_groupe">
    <xsl:call-template name="titre_niv1">
      <xsl:with-param name="titre" select="'Liste des groupes solidaires'"/>
    </xsl:call-template>
    <fo:table border-collapse="separate" width="100%" table-layout="fixed" space-before="0.2cm">
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-column column-width="proportional-column-width(2)"/>
      <fo:table-column column-width="proportional-column-width(1)"/>
      <fo:table-header>
        <fo:table-row font-weight="bold">
          <fo:table-cell>
            <fo:block>Group nbr</fo:block>
          </fo:table-cell>
          <fo:table-cell>
            <fo:block>Group name</fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-header>
      <fo:table-body>
        <xsl:apply-templates select="groupe"/>
      </fo:table-body>
    </fo:table>
  </xsl:template>
  <xsl:template match="groupe">
    <fo:table-row>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="num_groupe"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell>
        <fo:block>
          <xsl:value-of select="nom_groupe"/>
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
