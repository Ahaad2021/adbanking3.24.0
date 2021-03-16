-- Modèle de plan comptable PARMEC

-- Disable triggers
UPDATE "pg_class" SET "reltriggers" = 0 WHERE "relname" = 'ad_cpt_comptable';
UPDATE "pg_class" SET "reltriggers" = 0 WHERE "relname" = 'ad_classes_compta';


-- Data for TOC entry 320 (OID 625905)
-- Name: ad_classes_compta; Type: TABLE DATA; Schema: public; Owner: adbanking
--

COPY ad_classes_compta (id_classe,numero_classe,id_ag,libel_classe) FROM stdin;
1	1	0	OPERATIONS DE TRESORERIE ET AVEC LES INSTITUTIONS FINANCIERES
2	2	0	OPERATIONS AVEC LES MEMBRES, BENEFICIAIRES OU CLIENTS
3	3	0	OPERATIONS SUR TITRES ET OPERATIONS DIVERSES
4	4	0	VALEURS IMMOBILISEES
5	5	0	PROVISIONS, FONDS PROPRES ET ASSIMILES
6	6	0	CHARGES
7	7	0	PRODUITS
9	9	0	ENGAGEMENTS HORS BILAN
\.

--
-- Data for TOC entry 8 (OID 210764)
-- Name: ad_cpt_comptable; Type: TABLE DATA; Schema: public; Owner: adbanking
--

COPY ad_cpt_comptable (num_cpte_comptable,id_ag, libel_cpte_comptable, sens_cpte, classe_compta, compart_cpte, etat_cpte, date_ouvert, cpte_centralise, cpte_princ_jou, solde, is_hors_bilan, devise) FROM stdin;
1.0	0	Valeurs en caisse	3	1	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.1	0	Comptes ordinaires chez les institutions financières	1	1	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.1.1	0	Organe financier / Caisse centrale	1	1	1	1	2008-01-01 00:00:00	1.0	f	0.000000	f	XOF
1.1.4	0	Banques et correspondants	3	1	1	1	2008-01-01 00:00:00	1.1	f	0.000000	f	XOF
1.2	0	Autres comptes de dépôts chez les institutions financières	1	1	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.3	0	Comptes de prêts aux institutions financières 	1	1	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.3.1	0	Prêts à moins d’1 an 	1	1	1	1	2008-01-01 00:00:00	1.3	f	0.000000	f	XOF
1.3.3	0	Prêts à terme 	1	1	1	1	2008-01-01 00:00:00	1.3	f	0.000000	f	XOF
1.5	0	Comptes ordinaires des institutions financières	1	1	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.6	0	Autres comptes de dépôt des institutions financières	1	1	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.7	0	Comptes d’emprunts et autres sommes dues aux institutions financières	2	1	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.7.5	0	Emprunts à mois d’un an	2	1	2	1	2008-01-01 00:00:00	1.7	f	0.000000	f	XOF
1.7.8	0	Emprunt à terme	2	1	2	1	2008-01-01 00:00:00	1.7	f	0.000000	f	XOF
1.7.9	0	Autres sommes dues aux institutions financières	2	1	2	1	2008-01-01 00:00:00	1.7	f	0.000000	f	XOF
1.8	0	Ressources affectées 	1	1	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.9	0	Comptes de prêts en souffrance	1	1	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
1.9.9	0	Provisions sur prêts en souffrance	2	1	1	1	2008-01-01 00:00:00	1.9	f	0.000000	f	XOF
2.0	0	Crédits aux membres, bénéficiaires ou clients	1	2	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
2.0.2	0	Crédits à court terme	1	2	1	1	2008-01-01 00:00:00	2.0	f	0.000000	f	XOF
2.0.3	0	Crédits à moyen terme	1	2	1	1	2008-01-01 00:00:00	2.0	f	0.000000	f	XOF
2.0.4	0	Crédit à long terme	1	2	1	1	2008-01-01 00:00:00	2.0	f	0.000000	f	XOF
2.5	0	Comptes des membres, bénéficiaires ou clients	3	2	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
2.5.1	0	Comptes Ordinaires	3	2	2	1	2008-01-01 00:00:00	2.5	f	0.000000	f	XOF
2.5.2	0	Dépôts à terme reçus	3	2	2	1	2008-01-01 00:00:00	2.5	f	0.000000	f	XOF
2.5.2.1	0	Dépôts à terme reçus	3	2	2	1	2008-01-01 00:00:00	2.5.2	f	0.000000	f	XOF
2.5.2.1.1	0	Dépôts à terme reçus de 0 à 6 mois au plus	3	2	2	1	2008-01-01 00:00:00	2.5.2.1	f	0.000000	f	XOF
2.5.2.1.2	0	Dépôts à terme reçus de plus de 6 mois à 12 mois au plus	3	2	2	1	2008-01-01 00:00:00	2.5.2.1	f	0.000000	f	XOF
2.5.2.1.3	0	Dépôts à terme reçus de plus d’1 an à 2ans au plus	3	2	2	1	2008-01-01 00:00:00	2.5.2.1	f	0.000000	f	XOF
2.5.2.1.4	0	Dépôts à terme reçus de plus de 2 ans à 3 ans au plus	3	2	2	1	2008-01-01 00:00:00	2.5.2.1	f	0.000000	f	XOF
2.5.2.1.5	0	Dépôts à terme reçus de plus de 3 ans à 10 ans au plus	3	2	2	1	2008-01-01 00:00:00	2.5.2.1	f	0.000000	f	XOF
2.5.2.1.6	0	Dépôts à terme reçus de plus de 10 ans	3	2	2	1	2008-01-01 00:00:00	2.5.2.1	f	0.000000	f	XOF
2.5.3	0	Comptes d’épargne à régime spécial	3	2	2	1	2008-01-01 00:00:00	2.5	f	0.000000	f	XOF
2.5.4	0	Dépôts de garantie reçus	3	2	2	1	2008-01-01 00:00:00	2.5	f	0.000000	f	XOF
2.9	0	Comptes de crédits en souffrance	1	2	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
2.9.1	0	Crédits en souffrance de 6 mois au plus	1	2	1	1	2008-01-01 00:00:00	2.9	f	0.000000	f	XOF
2.9.2	0	Crédits en souffrance de plus de 6 mois à 12 mois au plus	1	2	1	1	2008-01-01 00:00:00	2.9	f	0.000000	f	XOF
2.9.3	0	Crédits en Souffrance de plus de 12 mois à 24 mois au plus	1	2	1	1	2008-01-01 00:00:00	2.9	f	0.000000	f	XOF
2.9.9	0	Prévisions sur crédits en souffrance	1	2	1	1	2008-01-01 00:00:00	2.9	f	0.000000	f	XOF
3.0	0	Titres de placement	1	3	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
3.0.1	0	Obligations	1	3	1	1	2008-01-01 00:00:00	3.0	f	0.000000	f	XOF
3.0.2	0	Autres titres à revenu fixe	1	3	1	1	2008-01-01 00:00:00	3.0	f	0.000000	f	XOF
3.0.3	0	Actions	1	3	1	1	2008-01-01 00:00:00	3.0	f	0.000000	f	XOF
3.0.5	0	Versements restant à effectuer	1	3	1	1	2008-01-01 00:00:00	3.0	f	0.000000	f	XOF
3.0.7	0	Créances rattachées	1	3	1	1	2008-01-01 00:00:00	3.0	f	0.000000	f	XOF
3.0.9	0	Provisions pour dépréciation	1	3	1	1	2008-01-01 00:00:00	3.0	f	0.000000	f	XOF
3.2	0	Comptes de stocks et emplois divers	1	3	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
3.3	0	Débiteurs et créditeurs divers	2	3	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
3.7	0	Comptes transitoires et d’attente	2	3	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
3.8	0	Comptes de régularisation	3	3	5	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
3.8.1	0	Compte de régularisation – actif	1	3	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
3.8.2	0	Compte de régularisation – passif	2	3	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
3.9	0	Compte de liaison	3	3	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
4.1	0	Immobilisations financières	1	4	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
4.2	0	Dépôts et cautionnements	1	4	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
4.3	0	Immobilisations en cours	1	4	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
4.3.1	0	Immobilisations incorporelles en cours	1	4	1	1	2008-01-01 00:00:00	4.3	f	0.000000	f	XOF
4.3.2	0	Immobilisations corporelles en cours	1	4	1	1	2008-01-01 00:00:00	4.3	f	0.000000	f	XOF
4.4	0	Immobilisations d'exploitation	1	4	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
4.5	0	Immobilisations hors exploitation	1	4	1	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
4.5.1	0	Immobilisations incorporelles	1	4	1	1	2008-01-01 00:00:00	4.5	f	0.000000	f	XOF
4.5.1.1	0	Fonds commercial	1	4	1	1	2008-01-01 00:00:00	4.5.1	f	0.000000	f	XOF
4.5.1.3	0	Autres immobilisations incorporelles	1	4	1	1	2008-01-01 00:00:00	4.5.1	f	0.000000	f	XOF
4.5.1.8	0	Amortissements	1	4	1	1	2008-01-01 00:00:00	4.5.1	f	0.000000	f	XOF
4.5.1.9	0	Provisions pour dépréciation	1	4	1	1	2008-01-01 00:00:00	4.5.1	f	0.000000	f	XOF
4.5.2	0	Immobilisations corporelles	1	4	1	1	2008-01-01 00:00:00	4.5	f	0.000000	f	XOF
4.5.2.1	0	Immobilisations incorporelles	1	4	1	1	2008-01-01 00:00:00	4.5.2	f	0.000000	f	XOF
4.5.2.8	0	Amortissements	1	4	1	1	2008-01-01 00:00:00	4.5.2	f	0.000000	f	XOF
4.5.2.9	0	Provisions pour dépréciation	1	4	1	1	2008-01-01 00:00:00	4.5.2	f	0.000000	f	XOF
4.5.3	0	Immobilisation incorporelles acquises par réalisation de garantie	1	4	1	1	2008-01-01 00:00:00	4.5	f	0.000000	f	XOF
4.5.3.1	0	Immobilisation incorporelles acquises par réalisation de garantie	1	4	1	1	2008-01-01 00:00:00	4.5.3	f	0.000000	f	XOF
4.5.3.8	0	Amortissements	1	4	1	1	2008-01-01 00:00:00	4.5.3	f	0.000000	f	XOF
4.5.3.9	0	Provisions pour dépréciation	1	4	1	1	2008-01-01 00:00:00	4.5.3	f	0.000000	f	XOF
4.5.4	0	Immobilisation corporelles acquises par réalisation de garantie	1	4	1	1	2008-01-01 00:00:00	4.5	f	0.000000	f	XOF
4.5.4.1	0	Immobilisation corporelles acquises par réalisation de garantie	1	4	1	1	2008-01-01 00:00:00	4.5.4	f	0.000000	f	XOF
4.5.4.8	0	Amortissements	1	4	1	1	2008-01-01 00:00:00	4.5.4	f	0.000000	f	XOF
4.5.4.9	0	Provisions pour dépréciation	1	4	1	1	2008-01-01 00:00:00	4.5.4	f	0.000000	f	XOF
5.0	0	Subventions et autres fonds reçus	2	5	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
5.0.1	0	Subventions d’investissement	2	5	2	1	2008-01-01 00:00:00	5.0	f	0.000000	f	XOF
5.0.2	0	Fonds affectés	2	5	2	1	2008-01-01 00:00:00	5.0	f	0.000000	f	XOF
5.0.3	0	Fonds de crédit	2	5	2	1	2008-01-01 00:00:00	5.0	f	0.000000	f	XOF
5.5	0	Primes liées au capital et réserves	2	5	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
5.5.2	0	Réserves	2	5	2	1	2008-01-01 00:00:00	5.5	f	0.000000	f	XOF
5.5.2.1	0	Réserve générale	2	5	2	1	2008-01-01 00:00:00	5.5.2	f	0.000000	f	XOF
5.5.2.2	0	Réserves facultatives	2	5	2	1	2008-01-01 00:00:00	5.5.2	f	0.000000	f	XOF
5.5.2.3	0	Autres réserves	2	5	2	1	2008-01-01 00:00:00	5.5.2	f	0.000000	f	XOF
5.5.3	0	Ecart de réévaluation des immobilisations	2	5	2	1	2008-01-01 00:00:00	5.5	f	0.000000	f	XOF
5.6	0	Fonds de dotation	2	5	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
5.7	0	Capital social	2	5	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
5.8	0	Report à  nouveau	3	5	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
5.9	0	Résultat	3	5	2	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
5.9.1	0	Excédent ou déficit en instance d’approbation	3	5	2	1	2008-01-01 00:00:00	5.9	f	0.000000	f	XOF
5.9.2	0	Excédent ou déficit de l’exercice	3	5	2	1	2008-01-01 00:00:00	5.9	f	0.000000	f	XOF
5.9.3	0	Marge	3	5	2	1	2008-01-01 00:00:00	5.9	f	0.000000	f	XOF
5.9.3.1	0	Marge d’intérêts	3	5	2	1	2008-01-01 00:00:00	5.9.3	f	0.000000	f	XOF
5.9.3.2	0	Marge commerciale	3	5	2	1	2008-01-01 00:00:00	5.9.3	f	0.000000	f	XOF
5.9.4	0	Produit financier net où charge financière nette	3	5	2	1	2008-01-01 00:00:00	5.9	f	0.000000	f	XOF
5.9.5	0	Excédent ou déficit d’exploitation	3	5	2	1	2008-01-01 00:00:00	5.9	f	0.000000	f	XOF
5.9.6	0	Excédent ou déficit exceptionnel	3	5	2	1	2008-01-01 00:00:00	5.9	f	0.000000	f	XOF
6.0	0	Charges d’exploitation financière	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
6.0.1	0	Charges sur opérations avec les institutions financières	1	6	3	1	2008-01-01 00:00:00	6.0	f	0.000000	f	XOF
6.0.1.1	0	Intérêts sur compte ordinaires les institutions financières	1	6	3	1	2008-01-01 00:00:00	6.0.1	f	0.000000	f	XOF
6.0.1.5	0	Intérêts sur compte ordinaires  des institutions financières	1	6	3	1	2008-01-01 00:00:00	6.0.1	f	0.000000	f	XOF
6.0.1.6	0	Intérêts sur autres comptes de dépôts des institutions financières	1	6	3	1	2008-01-01 00:00:00	6.0.1	f	0.000000	f	XOF
6.0.1.6.1	0	Intérêts sur dépôts à terme reçus	1	6	3	1	2008-01-01 00:00:00	6.0.1.6	f	0.000000	f	XOF
6.0.1.6.2	0	Intérêts sur dépôts de garantie reçus	1	6	3	1	2008-01-01 00:00:00	6.0.1.6	f	0.000000	f	XOF
6.0.1.6.5	0	Intérêts sur autres dépôts reçus	1	6	3	1	2008-01-01 00:00:00	6.0.1.6	f	0.000000	f	XOF
6.0.1.7	0	Intérêts sur compte d’emprunts à moins d’un an	1	6	3	1	2008-01-01 00:00:00	6.0.1	f	0.000000	f	XOF
6.0.1.7.5	0	Intérêts sur emprunts à moins d’un an	1	6	3	1	2008-01-01 00:00:00	6.0.1.7	f	0.000000	f	XOF
6.0.1.7.8	0	Intérêts sur emprunts à terme	1	6	3	1	2008-01-01 00:00:00	6.0.1.7	f	0.000000	f	XOF
6.0.1.8	0	Autres intérêts	1	6	3	1	2008-01-01 00:00:00	6.0.1	f	0.000000	f	XOF
6.0.1.9	0	Commissions	1	6	3	1	2008-01-01 00:00:00	6.0.1	f	0.000000	f	XOF
6.1	0	Achats et variations de stocks	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
6.2	0	Autres charges externes et  charges diverses d’exploitation	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
6.2.1	0	Services extérieurs	1	6	3	1	2008-01-01 00:00:00	6.2	f	0.000000	f	XOF
6.2.2	0	Autres services extérieurs	1	6	3	1	2008-01-01 00:00:00	6.2	f	0.000000	f	XOF
6.2.3	0	Charges diverses d’exploitation	1	6	3	1	2008-01-01 00:00:00	6.2	f	0.000000	f	XOF
6.3	0	Impôts, taxes et versements assimiles	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
6.4	0	Charges de personnel	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
6.4.1	0	Salaires et traitements	1	6	3	1	2008-01-01 00:00:00	6.4	f	0.000000	f	XOF
6.4.2	0	Charges sociales	1	6	3	1	2008-01-01 00:00:00	6.4	f	0.000000	f	XOF
6.5	0	Dotation au fonds pour risques financiers généraux	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
6.6	0	Dotation aux amortissements, aux provisions et pertes sur creances irrécouvrables	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
6.7	0	Charges exceptionnelles et pertes sur exercices antérieurs	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
6.7.1	0	Charges exceptionnelles	1	6	3	1	2008-01-01 00:00:00	6.7	f	0.000000	f	XOF
6.7.2	0	Pertes sur exercices antérieurs	1	6	3	1	2008-01-01 00:00:00	6.7	f	0.000000	f	XOF
6.7.2.1	0	Pertes d’exploitation financière	1	6	3	1	2008-01-01 00:00:00	6.7.2	f	0.000000	f	XOF
6.7.2.2	0	Pertes d’exploitation non financière	1	6	3	1	2008-01-01 00:00:00	6.7.2	f	0.000000	f	XOF
6.7.2.3	0	Pertes exceptionnelles	1	6	3	1	2008-01-01 00:00:00	6.7.2	f	0.000000	f	XOF
6.9	0	Impôt sur les excédents	1	6	3	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
7.0	0	Produits d’exploitation financière	2	7	4	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
7.0.1	0	Produits sur opérations avec les institutions financières	2	7	4	1	2008-01-01 00:00:00	7.0	f	0.000000	f	XOF
7.0.2	0	Produits sur opérations avec les membres, bénéficiaires ou clients	2	7	4	1	2008-01-01 00:00:00	7.0	f	0.000000	f	XOF
7.0.2.1	0	Intérêts sur crédits aux  membres, bénéficiaires ou clients	2	7	4	1	2008-01-01 00:00:00	7.0.2	f	0.000000	f	XOF
7.0.2.1.2	0	Intérêts sur crédits à court terme	2	7	4	1	2008-01-01 00:00:00	7.0.2.1	f	0.000000	f	XOF
7.0.2.1.3	0	Intérêts sur crédits à moyen terme	2	7	4	1	2008-01-01 00:00:00	7.0.2.1	f	0.000000	f	XOF
7.0.2.1.4	0	Intérêts sur crédits à long terme	2	7	4	1	2008-01-01 00:00:00	7.0.2.1	f	0.000000	f	XOF
7.0.2.9	0	Commissions	2	7	4	1	2008-01-01 00:00:00	7.0.2	f	0.000000	f	XOF
7.0.2.9.1	0	Cotisations et droits d’adhésion	2	7	4	1	2008-01-01 00:00:00	7.0.2.9	f	0.000000	f	XOF
7.0.2.9.2	0	Commissions sur transfert d’argent	2	7	4	1	2008-01-01 00:00:00	7.0.2.9	f	0.000000	f	XOF
7.0.2.9.3	0	Autres commissions	2	7	4	1	2008-01-01 00:00:00	7.0.2.9	f	0.000000	f	XOF
7.1	0	Ventes et variations de stocks 	2	7	4	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
7.1.1	0	Ventes 	2	7	4	1	2008-01-01 00:00:00	7.1	f	0.000000	f	XOF
7.1.2	0	Variations de stocks négatives 	2	7	4	1	2008-01-01 00:00:00	7.1	f	0.000000	f	XOF
7.6	0	Reprises d’amortissements, de provisions et récupérations sur créances irrécouvrables	2	7	4	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
7.7	0	Produits exceptionnels et  profits sur exercices antérieurs	2	7	4	1	2008-01-01 00:00:00	\N	f	0.000000	f	XOF
7.7.1	0	Produits exceptionnels	2	7	4	1	2008-01-01 00:00:00	7.7	f	0.000000	f	XOF
7.7.2	0	Profits sur exercices antérieurs	2	7	4	1	2008-01-01 00:00:00	7.7	f	0.000000	f	XOF
7.7.2.1	0	Profits d’exploitation financière	2	7	4	1	2004-11-15 00:00:00	7.7.2	f	0.000000	f	XOF
7.7.2.2	0	Profits d’exploitation non financière	2	7	4	1	2004-11-15 00:00:00	7.7.2	f	0.000000	f	XOF
7.7.2.3	0	Profits exceptionnels	2	7	4	1	2004-11-15 00:00:00	7.7.2	f	0.000000	f	XOF
9.0	0	Engagement de financement	3	9	5	1	2008-01-01 00:00:00	\N	f	0.000000	t	XOF
9.1	0	Engagements de garantie	3	9	5	1	2008-01-01 00:00:00	\N	f	0.000000	t	XOF
9.2	0	Engagements sur titres	3	9	5	1	2008-01-01 00:00:00	\N	f	0.000000	t	XOF
9.3	0	Engagements sur opérations en devises	3	9	5	1	2008-01-01 00:00:00	\N	f	0.000000	t	XOF
9.5	0	Autres engagements	3	9	5	1	2008-01-01 00:00:00	\N	f	0.000000	t	XOF
9.6	0	Opérations effectuées pour le compte de tiers	3	9	5	1	2008-01-01 00:00:00	\N	f	0.000000	t	XOF
9.9	0	Engagements douteux	3	9	5	1	2008-01-01 00:00:00	\N	f	0.000000	t	XOF
\.

-- Mise à jour des comptes
UPDATE ad_cpt_comptable SET id_ag= :agence; 
UPDATE ad_classes_compta SET id_ag= :agence; 
-- Mise à jour des séquance
select setval('ad_classes_comptables_id_seq',9);

-- Enable triggers
UPDATE pg_class SET reltriggers = (SELECT count(*) FROM pg_trigger where pg_class.oid = tgrelid) WHERE relname = 'ad_cpt_comptable';
