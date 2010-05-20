--
-- PostgreSQL database dump
--

\connect - anakeen

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 39 (OID 1827953)
-- Name: doc; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 40 (OID 1827966)
-- Name: docfrom; Type: TABLE DATA; Schema: public; Owner: anakeen
--

INSERT INTO docfrom (id, fromid) VALUES (503, 21);
INSERT INTO docfrom (id, fromid) VALUES (507, 21);
INSERT INTO docfrom (id, fromid) VALUES (502, 21);
INSERT INTO docfrom (id, fromid) VALUES (506, 21);
INSERT INTO docfrom (id, fromid) VALUES (501, 28);
INSERT INTO docfrom (id, fromid) VALUES (508, 28);
INSERT INTO docfrom (id, fromid) VALUES (504, 3);
INSERT INTO docfrom (id, fromid) VALUES (509, 3);
INSERT INTO docfrom (id, fromid) VALUES (505, 4);
INSERT INTO docfrom (id, fromid) VALUES (512, 6);
INSERT INTO docfrom (id, fromid) VALUES (510, 23);
INSERT INTO docfrom (id, fromid) VALUES (511, 23);


--
-- Data for TOC entry 41 (OID 1827972)
-- Name: docperm; Type: TABLE DATA; Schema: public; Owner: anakeen
--

INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (501, 2, 8194, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (501, 4, 4484, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (504, 2, 2, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (504, 4, 4, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (505, 2, 162, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (505, 4, 356, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (512, 2, 162, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (512, 4, 356, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (508, 2, 2, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (508, 4, 4096, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (509, 2, 146, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (509, 4, 270, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (510, 2, 130, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (510, 4, 292, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (511, 2, 162, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (511, 4, 260, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (508, 1000000, 8192, 0, 0);
INSERT INTO docperm (docid, userid, upacl, unacl, cacl) VALUES (509, 1000000, 6, 0, 0);


--
-- Data for TOC entry 42 (OID 1827976)
-- Name: vgroup; Type: TABLE DATA; Schema: public; Owner: anakeen
--

INSERT INTO vgroup (id, num) VALUES ('us_meid', 1000000);
INSERT INTO vgroup (id, num) VALUES ('us_idsociety', 1000001);
INSERT INTO vgroup (id, num) VALUES ('us_idservice', 1000002);
INSERT INTO vgroup (id, num) VALUES ('us_idservice', 1000002);
select setval('seq_id_docvgroup',1000004);


--
-- Data for TOC entry 43 (OID 1827983)
-- Name: docfam; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 44 (OID 1828004)
-- Name: doc1; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 45 (OID 1828020)
-- Name: doc2; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 46 (OID 1828035)
-- Name: fld; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 47 (OID 1828043)
-- Name: doc17; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 48 (OID 1828059)
-- Name: doc26; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 49 (OID 1828075)
-- Name: doc20; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 50 (OID 1828091)
-- Name: doc21; Type: TABLE DATA; Schema: public; Owner: anakeen
--

INSERT INTO doc21 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, msk_famid, msk_fam, msk_attrids, msk_visibilities, msk_needeeds) VALUES (503, 1, 'membres', 0, 503, 21, 'F', 0, 'masque.gif', 'Y', 504, 'N', 1077728433, '25/02/2004 18:00 [What Master] modification
25/02/2004 18:00 [What Master] création', NULL, NULL, NULL, '£membres££127£groupe intranet£grp_fr_ident
FIELD_HIDDENS
fr_basic
grp_fr_intranet
fld_fr_rest
fld_fr_prof
grp_fr
ba_title
grp_name
ba_desc
grp_role
grp_mail
grp_type
us_login
us_whatid
us_meid
us_iddomain
us_domain
grp_users
grp_iduser
grp_user
grp_groups
grp_idgroup
grp_group
grp_rusers
grp_idruser
grp_ruser
grp_parent
grp_pgroup
grp_idpgroup
fld_allbut
fld_tfam
fld_fam
fld_famids
fld_pdoc
fld_pdocid
fld_pdir
fld_pdirid£-
-
-
-
-
-
R
-
-
-
-
-
-
-
-
-
-
-
R
-
-
R
-
-
R
-
-
-
-
-
-
-
-
-
-
-
-
-£-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-£', '£ba_title££msk_famid£msk_fam£msk_attrids£msk_visibilities£msk_needeeds£', NULL, NULL, NULL, NULL, 'membres', '127', 'groupe intranet', 'grp_fr_ident
FIELD_HIDDENS
fr_basic
grp_fr_intranet
fld_fr_rest
fld_fr_prof
grp_fr
ba_title
grp_name
ba_desc
grp_role
grp_mail
grp_type
us_login
us_whatid
us_meid
us_iddomain
us_domain
grp_users
grp_iduser
grp_user
grp_groups
grp_idgroup
grp_group
grp_rusers
grp_idruser
grp_ruser
grp_parent
grp_pgroup
grp_idpgroup
fld_allbut
fld_tfam
fld_fam
fld_famids
fld_pdoc
fld_pdocid
fld_pdir
fld_pdirid', '-
-
-
-
-
-
R
-
-
-
-
-
-
-
-
-
-
-
R
-
-
R
-
-
R
-
-
-
-
-
-
-
-
-
-
-
-
-', '-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-');
INSERT INTO doc21 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, msk_famid, msk_fam, msk_attrids, msk_visibilities, msk_needeeds) VALUES (507, 1, 'mon compte personne intranet', 0, 507, 21, 'F', 0, 'masque.gif', 'Y', 504, 'N', 1077879163, '27/02/2004 11:52 [What Master] modification
26/02/2004 10:03 [What Master] modification
26/02/2004 10:02 [What Master] création', NULL, NULL, NULL, '£mon compte££128£personne intranet£us_fr_soc
us_fr_intranet
us_fr_coord
us_fr_ident
FIELD_HIDDENS
us_lname
us_fname
us_photo
us_initials
us_mail
us_phone
us_pphone
us_intphone
us_pfax
us_fax
us_mobile
us_meid
us_login
us_passwd
us_passwd1
us_passwd2
us_whatid
us_groups
us_group
us_idgroup
us_status
us_expires
us_daydelay
us_expiresd
us_expirest
us_passdelay
us_iddomain
us_domain
us_idsociety
us_socaddr
us_society
us_type
us_idservice
us_service
us_job
us_privcard
us_role
us_workaddr
us_workpostalcode
us_worktown
us_country
us_workcedex
us_workweb
us_scatg£R
W
R
W
-
W
W
R
R
-
-
-
-
-
-
-
-
S
-
W
W
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-£-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-£', '£ba_title££msk_famid£msk_fam£msk_attrids£msk_visibilities£msk_needeeds£', NULL, NULL, NULL, NULL, 'mon compte', '128', 'personne intranet', 'us_fr_soc
us_fr_intranet
us_fr_coord
us_fr_ident
FIELD_HIDDENS
us_lname
us_fname
us_photo
us_initials
us_mail
us_phone
us_pphone
us_intphone
us_pfax
us_fax
us_mobile
us_meid
us_login
us_passwd
us_passwd1
us_passwd2
us_whatid
us_groups
us_group
us_idgroup
us_status
us_expires
us_daydelay
us_expiresd
us_expirest
us_passdelay
us_iddomain
us_domain
us_idsociety
us_socaddr
us_society
us_type
us_idservice
us_service
us_job
us_privcard
us_role
us_workaddr
us_workpostalcode
us_worktown
us_country
us_workcedex
us_workweb
us_scatg', 'R
W
R
W
-
W
W
R
R
-
-
-
-
-
-
-
-
S
-
W
W
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-', '-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-');


INSERT INTO doc21 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, msk_famid, msk_fam, msk_attrids, msk_visibilities, msk_needeeds) VALUES (502, 1, 'Administration groupe intranet', 0, 502, 21, 'F', 0, 'masque.gif', 'Y', 504, 'N', 1077728382, '26/02/2004 10:23 [What Master] modification
25/02/2004 17:59 [What Master] modification
25/02/2004 17:59 [What Master] création', NULL, NULL, NULL, '£Administration££127£groupe intranet£grp_fr_ident
FIELD_HIDDENS
fr_basic
grp_fr_intranet
fld_fr_rest
fld_fr_prof
grp_fr
ba_title
grp_name
ba_desc
grp_role
grp_mail
grp_type
us_login
us_whatid
us_meid
us_iddomain
us_domain
grp_users
grp_iduser
grp_user
grp_groups
grp_idgroup
grp_group
grp_rusers
grp_idruser
grp_ruser
grp_parent
grp_pgroup
grp_idpgroup
fld_allbut
fld_tfam
fld_fam
fld_famids
fld_pdoc
fld_pdocid
fld_pdir
fld_pdirid£-
-
-
W
-
-
-
-
-
-
-
-
-
W
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-£-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-£', '£ba_title££msk_famid£msk_fam£msk_attrids£msk_visibilities£msk_needeeds£', NULL, NULL, NULL, NULL, 'Administration', '127', 'groupe intranet', 'grp_fr_ident
FIELD_HIDDENS
fr_basic
grp_fr_intranet
fld_fr_rest
fld_fr_prof
grp_fr
ba_title
grp_name
ba_desc
grp_role
grp_mail
grp_type
us_login
us_whatid
us_meid
us_iddomain
us_domain
grp_users
grp_iduser
grp_user
grp_groups
grp_idgroup
grp_group
grp_rusers
grp_idruser
grp_ruser
grp_parent
grp_pgroup
grp_idpgroup
fld_allbut
fld_tfam
fld_fam
fld_famids
fld_pdoc
fld_pdocid
fld_pdir
fld_pdirid', '-
-
-
W
-
-
-
-
-
-
-
-
-
W
-
-
-
W
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-', '-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-');
INSERT INTO doc21 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, msk_famid, msk_fam, msk_attrids, msk_visibilities, msk_needeeds) VALUES (506, 1, 'Administration personne intranet', 0, 506, 21, 'F', 0, 'masque.gif', 'Y', 504, 'N', 1077787696, '26/02/2004 10:28 [What Master] modification
26/02/2004 10:25 [What Master] modification
26/02/2004 10:23 [What Master] modification
26/02/2004 10:04 [What Master] modification
26/02/2004 10:02 [What Master] modification
26/02/2004 09:59 [What Master] création', NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, 'Administration', '128', 'personne intranet', 'us_fr_soc
us_fr_intranet
us_fr_coord
us_fr_ident
FIELD_HIDDENS
us_lname
us_fname
us_photo
us_initials
us_mail
us_phone
us_pphone
us_intphone
us_pfax
us_fax
us_mobile
us_meid
us_login
us_passwd
us_passwd1
us_passwd2
us_whatid
us_groups
us_group
us_idgroup
us_status
us_expires
us_daydelay
us_expiresd
us_expirest
us_passdelay
us_iddomain
us_domain
us_idsociety
us_socaddr
us_society
us_type
us_idservice
us_service
us_job
us_privcard
us_role
us_workaddr
us_workpostalcode
us_worktown
us_country
us_workcedex
us_workweb
us_scatg', 'R
W
R
W
-
W
W
R
R
-
-
-
-
-
-
-
-
W
-
-
-
-
-
-
-
W
-
W
W
W
-
-
W
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-', '-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-
-');


--
-- Data for TOC entry 51 (OID 1828107)
-- Name: doc28; Type: TABLE DATA; Schema: public; Owner: anakeen
--

INSERT INTO doc28 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, cv_desc, cv_famid, cv_fam, cv_idview, cv_idcview, cv_lview, cv_lcview, cv_kview, cv_zview, cv_mskid, cv_msk, dpdoc_famid, dpdoc_fam) VALUES (501, 1, 'Administration groupe intranet', 0, 501, 28, 'P', 0, 'cview.gif', 'Y', 501, 'N', 1077728451, '25/02/2004 18:00 [What Master] modification
25/02/2004 17:57 [What Master] création', NULL, NULL, NULL, '£Administration££127£groupe intranet£ADMIN
MEMBERS£ADMIN£administration
détail membres£administration£VEDIT
VCONS£FDL:EDITBODYCARD
FDL:VIEWBODYCARD£502
503£Administration
membres£', '£ba_title££cv_famid£cv_fam£cv_idview£cv_idcview£cv_lview£cv_lcview£cv_kview£cv_zview£cv_mskid£cv_msk£', NULL, NULL, NULL, NULL, 'Administration', NULL, '127', 'groupe intranet', 'ADMIN
MEMBERS', 'ADMIN', 'administration
détail membres', 'administration', 'VEDIT
VCONS', 'FDL:EDITBODYCARD
FDL:VIEWBODYCARD', '502
503', 'Administration
membres', NULL, NULL);
INSERT INTO doc28 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, cv_desc, cv_famid, cv_fam, cv_idview, cv_idcview, cv_lview, cv_lcview, cv_kview, cv_zview, cv_mskid, cv_msk, dpdoc_famid, dpdoc_fam) VALUES (508, 1, 'Confidentiel personne intranet', 0, 508, 28, 'P', 0, 'cview.gif', 'Y', 508, 'N', 1077786422, '26/02/2004 10:07 [What Master] création', NULL, NULL, NULL, '£Confidentiel££128£personne intranet£ADMIN
MYACCOUNT£ADMIN£Administration
Mon compte£Administration£VEDIT
VEDIT£FDL:EDITBODYCARD
FDL:EDITBODYCARD£506
507£Administration
mon compte£128£personne intranet£', '£ba_title££cv_famid£cv_fam£cv_idview£cv_idcview£cv_lview£cv_lcview£cv_kview£cv_zview£cv_mskid£cv_msk£dpdoc_famid£dpdoc_fam£', NULL, 0, NULL, NULL, 'Confidentiel', NULL, '128', 'personne intranet', 'ADMIN
MYACCOUNT', 'ADMIN', 'Administration
Mon compte', 'Administration', 'VEDIT
VEDIT', 'FDL:EDITBODYCARD
FDL:EDITBODYCARD', '506
507', 'Administration
mon compte', '128', 'personne intranet');




INSERT INTO doc3 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, prf_desc, dpdoc_famid, dpdoc_fam) VALUES (504, 1, 'Administration', 0, 504, 3, 'P', 0, 'profil.gif', 'Y', 504, 'P', 1077730225, '25/02/2004 18:30 [What Master] création', NULL, NULL, NULL, '£Administration££lecture seule sauf pour groupe admin£', '£ba_title££prf_desc£', NULL, 0, NULL, NULL, 'Administration', 'lecture seule sauf pour groupe admin', NULL, NULL);
INSERT INTO doc3 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, prf_desc, dpdoc_famid, dpdoc_fam) VALUES (509, 1, 'Confidentiel', 0, 509, 3, 'P', 0, 'profil.gif', 'Y', 509, 'P', 1077787270, '26/02/2004 10:21 [What Master] modification
26/02/2004 10:10 [What Master] création', NULL, NULL, NULL, '£Confidentiel££Droit à l''utilisateur de modifier sa propre fiche
sinon seul les membres du groupe administration peuvent modifier les autres fiches£128£personne intranet£', '£ba_title££prf_desc£dpdoc_famid£dpdoc_fam£', NULL, 0, NULL, NULL, 'Confidentiel', 'Droit à l''utilisateur de modifier sa propre fiche
sinon seul les membres du groupe administration peuvent modifier les autres fiches', '128', 'personne intranet');


--
-- Data for TOC entry 59 (OID 1828235)
-- Name: doc4; Type: TABLE DATA; Schema: public; Owner: anakeen
--

INSERT INTO doc4 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, prf_desc, dpdoc_famid, dpdoc_fam) VALUES (505, 1, 'Administration dossier', 0, 505, 4, 'P', 0, 'profil_dossier.gif', 'Y', 505, 'P', 1077730372, '25/02/2004 18:32 [What Master] création', NULL, NULL, NULL, '£Administration dossier£££', '£ba_title£££', NULL, 0, NULL, NULL, 'Administration dossier', NULL, NULL, NULL);


INSERT INTO doc6 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, prf_desc, dpdoc_famid, dpdoc_fam) VALUES (512, 1, 'Administration recherche', 0, 512, 6, 'P', 0, 'profil_recherche.gif', 'Y', 512, 'P', 1077730372, '25/02/2004 18:32 [What Master] création', NULL, NULL, NULL, '£Administration recherche£££', '£ba_title£££', NULL, 0, NULL, NULL, 'Administration recherche', NULL, NULL, NULL);

--
-- Data for TOC entry 60 (OID 1828251)
-- Name: doc6; Type: TABLE DATA; Schema: public; Owner: anakeen
--



--
-- Data for TOC entry 61 (OID 1828267)
-- Name: doc23; Type: TABLE DATA; Schema: public; Owner: anakeen
--

INSERT INTO doc23 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, prf_desc, dpdoc_famid, dpdoc_fam) VALUES (510, 1, 'Création administrateur', 0, 510, 23, 'P', 0, 'profil_family.gif', 'Y', 510, 'P', 1077788192, '26/02/2004 10:36 [What Master] modification
26/02/2004 10:35 [What Master] création', NULL, NULL, NULL, '£Création administrateur£££Seul les administrateurs peuvent créer ce type de famille£', '£ba_title£££prf_desc£', NULL, 0, NULL, NULL, 'Création administrateur', 'Seul les administrateurs peuvent créer ce type de famille', NULL, NULL);
INSERT INTO doc23 (id, "owner", title, revision, initid, fromid, doctype, locked, icon, lmodify, profid, usefor, revdate, "comment", classname, state, wid, "values", attrids, postitid, cvid, name, dprofid, ba_title, prf_desc, dpdoc_famid, dpdoc_fam) VALUES (511, 1, 'Défaut', 0, 511, 23, 'P', 0, 'profil_family.gif', 'Y', 511, 'P', 1077788293, '26/02/2004 10:38 [What Master] création', NULL, NULL, NULL, '£Défaut£££protection des familles en lecture seule avec possibilité de création£', '£ba_title£££prf_desc£', NULL, 0, NULL, NULL, 'Défaut', 'protection des familles en lecture seule avec possibilité de création', NULL, NULL);







UPDATE docfam set profid=511 where profid=0;
update doc5 set profid=512 where id < 30;
-- iuser profile
UPDATE docfam set cprofid=509, ccvid=508, profid=510 where id=128;
-- igroup profile
UPDATE docfam set cprofid=505, ccvid=501, profid=510 where id=127;
