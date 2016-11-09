<?php
namespace Dcp\Family {
	/** de base  */
	class Base extends Document { const familyName="BASE";}
	/** Dossier  */
	class Dir extends \Dir { const familyName="DIR";}
	/** Profil de document  */
	class Pdoc extends \PDoc { const familyName="PDOC";}
	/** Profil de dossier  */
	class Pdir extends \PDir { const familyName="PDIR";}
	/** Profil de recherche  */
	class Psearch extends \PDocSearch { const familyName="PSEARCH";}
	/** Profil de famille  */
	class Pfam extends \PFam { const familyName="PFAM";}
	/** Porte-documents  */
	class Basket extends Dir { const familyName="BASKET";}
	/** Aide en ligne  */
	class Helppage extends \Dcp\Core\HelpPage { const familyName="HELPPAGE";}
	/** Mail  */
	class Mail extends \Dcp\Core\MailEdit { const familyName="MAIL";}
	/** Modèle de mail  */
	class Mailtemplate extends \Dcp\Core\MailTemplate { const familyName="MAILTEMPLATE";}
	/** Message envoyé  */
	class Sentmessage extends \Dcp\Core\SentEmail { const familyName="SENTMESSAGE";}
	/** Archive  */
	class Archiving extends \Dcp\Core\Archiving { const familyName="ARCHIVING";}
	/** Post-it  */
	class Postit extends \Dcp\Core\PostitView { const familyName="POSTIT";}
	/** Groupe de personnes  */
	class Group extends \Dcp\Core\AccountCollection { const familyName="GROUP";}
	/** Rôle  */
	class Role extends \Dcp\Core\RoleAccount { const familyName="ROLE";}
	/** Groupe d'utilisateurs  */
	class Igroup extends \Dcp\Core\GroupAccount { const familyName="IGROUP";}
	/** utilisateur  */
	class Iuser extends \Dcp\Core\UserAccount { const familyName="IUSER";}
	/** Recherche  */
	class Search extends \DocSearch { const familyName="SEARCH";}
	/** Recherche groupée  */
	class Msearch extends \Dcp\Core\GroupedSearch { const familyName="MSEARCH";}
	/** Recherche détaillée  */
	class Dsearch extends \Dcp\Core\DetailSearch { const familyName="DSEARCH";}
	/** Recherche spécialisée  */
	class Ssearch extends \Dcp\Core\SpecialSearch { const familyName="SSEARCH";}
	/** Rapport  */
	class Report extends \Dcp\Core\Report { const familyName="REPORT";}
	/** Cycle de vie  */
	class Wdoc extends \WDoc { const familyName="WDOC";}
	/** Masque de saisie  */
	class Mask extends \Dcp\Core\Mask { const familyName="MASK";}
	/**   */
	class Cvdoc extends \Dcp\Core\Cvdoc { const familyName="CVDOC";}
	/** État libre  */
	class Freestate extends Document { const familyName="FREESTATE";}
	/** Accord  */
	class Wask extends \Dcp\Core\Wask { const familyName="WASK";}
	/** Minuteur  */
	class Timer extends \Dcp\Core\Timer { const familyName="TIMER";}
	/** fichiers  */
	class File extends \Dcp\Core\File { const familyName="FILE";}
	/** image  */
	class Image extends \Dcp\Core\Image { const familyName="IMAGE";}
	/** texte  */
	class Text extends Base { const familyName="TEXT";}
	/** chemise  */
	class Portfolio extends \Dcp\Core\PortFolio { const familyName="PORTFOLIO";}
	/** intercalaire  */
	class Guidecard extends Dir { const familyName="GUIDECARD";}
	/** intercalaire dynamique  */
	class Sguidecard extends Dsearch { const familyName="SGUIDECARD";}
	/** Traitement  */
	class Batch extends \Dcp\Core\BatchDocument { const familyName="BATCH";}
	/** Processus  */
	class Exec extends \Dcp\Core\ExecProcessus { const familyName="EXEC";}
	/** Publipostage  */
	class Publimail extends \Dcp\Core\Emailing { const familyName="PUBLIMAIL";}
	/** Action basique  */
	class Basicbatch extends Batch { const familyName="BASICBATCH";}
}
namespace Dcp\AttributeIdentifiers {
	/** de base  */
	class Base {
		/** [frame] Basique */
		const fr_basic='fr_basic';
		/** [text] Titre */
		const ba_title='ba_title';
	}
	/** Dossier  */
	class Dir extends Base {
		/** [longtext] Description */
		const ba_desc='ba_desc';
		/** [color] Couleur intercalaire */
		const gui_color='gui_color';
		/** [enum] Utilisable comme flux RSS */
		const gui_isrss='gui_isrss';
		/** [enum] Flux RSS système */
		const gui_sysrss='gui_sysrss';
		/** [frame] Restrictions */
		const fld_fr_rest='fld_fr_rest';
		/** [enum] Tout ou rien */
		const fld_allbut='fld_allbut';
		/** [array] Familles filtrées */
		const fld_tfam='fld_tfam';
		/** [text] Familles (titre) */
		const fld_fam='fld_fam';
		/** [docid('-1')] Familles */
		const fld_famids='fld_famids';
		/** [enum] Restriction sous famille */
		const fld_subfam='fld_subfam';
		/** [frame] Profils par défaut */
		const fld_fr_prof='fld_fr_prof';
		/** [text] Profil par défaut de document (titre) */
		const fld_pdoc='fld_pdoc';
		/** [docid('PDOC')] Profil par défaut de document */
		const fld_pdocid='fld_pdocid';
		/** [text] Profil par défaut de dossier (titre) */
		const fld_pdir='fld_pdir';
		/** [docid('PDIR')] Profil par défaut de dossier */
		const fld_pdirid='fld_pdirid';
		/** [menu] Ouvrir */
		const fld_open='fld_open';
		/** [menu] Insérer le porte-document */
		const fld_copybasket='fld_copybasket';
		/** [menu] Ouvrir comme une chemise */
		const fld_openfolio='fld_openfolio';
		/** [menu] Insérer des documents */
		const fld_insertdoc='fld_insertdoc';
		/** [menu] RSS visible/masquée aux utilisateurs */
		const fld_setsysrss='fld_setsysrss';
	}
	/** Profil de document  */
	class Pdoc extends Base {
		/** [longtext] Description */
		const prf_desc='prf_desc';
		/** [frame] Dynamique */
		const dpdoc_fr_dyn='dpdoc_fr_dyn';
		/** [docid] Famille (id) */
		const dpdoc_famid='dpdoc_famid';
		/** [text] Famille */
		const dpdoc_fam='dpdoc_fam';
		/** [action] Accessibilités */
		const prf_access='prf_access';
		/** [action] Activer */
		const prf_activate='prf_activate';
		/** [action] Désactiver */
		const prf_desactivate='prf_desactivate';
		/** [menu] Forcer la propagation du profil */
		const prf_forcecomputing='prf_forcecomputing';
	}
	/** Profil de dossier  */
	class Pdir extends Pdoc {
	}
	/** Profil de recherche  */
	class Psearch extends Pdoc {
	}
	/** Profil de famille  */
	class Pfam extends Pdoc {
	}
	/** Porte-documents  */
	class Basket extends Dir {
		/** [menu] Insérer le porte-document */
		const fld_copybasket='fld_copybasket';
		/** [menu] Vider */
		const fld_clear='fld_clear';
	}
	/** Aide en ligne  */
	class Helppage {
		/** [frame] Aide */
		const help_fr_identification='help_fr_identification';
		/** [docid("x")] Famille */
		const help_family='help_family';
		/** [array] Description */
		const help_t_help='help_t_help';
		/** [text] Libellé */
		const help_name='help_name';
		/** [enum] Langue du libellé */
		const help_lang='help_lang';
		/** [longtext] Description */
		const help_description='help_description';
		/** [array] Rubriques */
		const help_t_sections='help_t_sections';
		/** [int] Ordre de la rubrique */
		const help_sec_order='help_sec_order';
		/** [text] Nom de la rubrique */
		const help_sec_name='help_sec_name';
		/** [enum] Langue */
		const help_sec_lang='help_sec_lang';
		/** [text] Clé de la rubrique */
		const help_sec_key='help_sec_key';
		/** [htmltext] Texte */
		const help_sec_text='help_sec_text';
		/** [frame] Paramètres de famille */
		const help_fr_family='help_fr_family';
		/** [array] Langues */
		const help_t_family='help_t_family';
		/** [text] Libellé de la langue */
		const help_p_lang_name='help_p_lang_name';
		/** [text] Langue */
		const help_p_lang_key='help_p_lang_key';
	}
	/** Mail  */
	class Mail {
		/** [frame] Adresses */
		const mail_fr='mail_fr';
		/** [text] De */
		const mail_from='mail_from';
		/** [array] Destinataires */
		const mail_dest='mail_dest';
		/** [enum]  */
		const mail_copymode='mail_copymode';
		/** [docid] Id destinataire */
		const mail_recipid='mail_recipid';
		/** [text] Destinataire */
		const mail_recip='mail_recip';
		/** [enum] Notif. */
		const mail_sendformat='mail_sendformat';
		/** [text] Sujet */
		const mail_subject='mail_subject';
		/** [enum] Enregistrer une copie */
		const mail_savecopy='mail_savecopy';
		/** [frame] Commentaire */
		const mail_fr_cm='mail_fr_cm';
		/** [longtext] Commentaire */
		const mail_cm='mail_cm';
		/** [enum] Format */
		const mail_format='mail_format';
	}
	/** Modèle de mail  */
	class Mailtemplate {
		/** [frame] Entête */
		const tmail_fr='tmail_fr';
		/** [text] Titre */
		const tmail_title='tmail_title';
		/** [docid("x")] Famille */
		const tmail_family='tmail_family';
		/** [docid("x")] Famille cycle */
		const tmail_workflow='tmail_workflow';
		/** [array] Émetteur */
		const tmail_t_from='tmail_t_from';
		/** [enum] type */
		const tmail_fromtype='tmail_fromtype';
		/** [text] De */
		const tmail_from='tmail_from';
		/** [array] Destinataires */
		const tmail_dest='tmail_dest';
		/** [enum] - */
		const tmail_copymode='tmail_copymode';
		/** [enum] Type */
		const tmail_desttype='tmail_desttype';
		/** [text] Destinataire */
		const tmail_recip='tmail_recip';
		/** [text] Sujet */
		const tmail_subject='tmail_subject';
		/** [frame] Contenu */
		const tmail_fr_content='tmail_fr_content';
		/** [enum] Enregistrer une copie */
		const tmail_savecopy='tmail_savecopy';
		/** [enum] Avec liens */
		const tmail_ulink='tmail_ulink';
		/** [htmltext] Corps */
		const tmail_body='tmail_body';
		/** [array] Attachements */
		const tmail_t_attach='tmail_t_attach';
		/** [text] Attachement */
		const tmail_attach='tmail_attach';
		/** [enum] Format */
		const tmail_format='tmail_format';
	}
	/** Message envoyé  */
	class Sentmessage {
		/** [frame] Identification */
		const emsg_fr_ident='emsg_fr_ident';
		/** [docid("x")] Document référence */
		const emsg_refid='emsg_refid';
		/** [text] De */
		const emsg_from='emsg_from';
		/** [array] Destinataires */
		const emsg_t_recipient='emsg_t_recipient';
		/** [enum] Type */
		const emsg_sendtype='emsg_sendtype';
		/** [text] Destinataire */
		const emsg_recipient='emsg_recipient';
		/** [text] Sujet */
		const emsg_subject='emsg_subject';
		/** [timestamp("%d %B %Y %H:%S")] Date */
		const emsg_date='emsg_date';
		/** [int] Taille */
		const emsg_size='emsg_size';
		/** [frame] Corps de messages */
		const emsg_fr_bodies='emsg_fr_bodies';
		/** [longtext] Texte */
		const emsg_textbody='emsg_textbody';
		/** [ifile] Texte formaté */
		const emsg_htmlbody='emsg_htmlbody';
		/** [array] Attachements */
		const emsg_t_attach='emsg_t_attach';
		/** [file] Fichier */
		const emsg_attach='emsg_attach';
		/** [frame] Paramètres */
		const emsg_fr_parameters='emsg_fr_parameters';
		/** [enum] Force la lecture seule */
		const emsg_editcontrol='emsg_editcontrol';
	}
	/** Archive  */
	class Archiving extends Dir {
		/** [enum] Statut */
		const arc_status='arc_status';
		/** [date] Date de clôture */
		const arc_clotdate='arc_clotdate';
		/** [docid("PDIR")] Profil applicable */
		const arc_profil='arc_profil';
		/** [tab] Purge */
		const arc_tab_purge='arc_tab_purge';
		/** [frame] Purge */
		const arc_fr_purge='arc_fr_purge';
		/** [date] Date de destruction */
		const arc_purgedate='arc_purgedate';
		/** [htmltext] Documents détruits */
		const arc_purgemanif='arc_purgemanif';
		/** [menu] Voir les documents */
		const arc_list='arc_list';
		/** [menu] Voir les documents archivés */
		const arc_listc='arc_listc';
		/** [menu] Archiver les documents */
		const arc_close='arc_close';
		/** [menu] Gérer les droits des documents archivés */
		const arc_modprof='arc_modprof';
		/** [menu] Désarchiver les documents archivés */
		const arc_reopen='arc_reopen';
		/** [menu] Détruire les documents archivé */
		const arc_purge='arc_purge';
		/** [menu] Vider l'archive de son contenu */
		const arc_clear='arc_clear';
	}
	/** Post-it  */
	class Postit {
		/** [frame] Texte */
		const pit_fr_text='pit_fr_text';
		/** [text] Titre */
		const pit_title='pit_title';
		/** [array] Commentaires */
		const pit_tcom='pit_tcom';
		/** [longtext] Commentaire */
		const pit_com='pit_com';
		/** [date] Date */
		const pit_date='pit_date';
		/** [docid] Id utilisateur */
		const pit_iduser='pit_iduser';
		/** [text] Utilisateur */
		const pit_user='pit_user';
		/** [color] Couleur */
		const pit_color='pit_color';
		/** [frame] Attachement */
		const pit_fr_doc='pit_fr_doc';
		/** [docid] Id doc attaché */
		const pit_idadoc='pit_idadoc';
		/** [text] Doc attaché */
		const pit_adoc='pit_adoc';
		/** [menu] Voir le document associé */
		const pit_viewdoc='pit_viewdoc';
		/** [frame] Édition */
		const pit_fr_edit='pit_fr_edit';
		/** [longtext] Nouveau commentaire */
		const pit_ncom='pit_ncom';
		/** [color] Nouvelle couleur */
		const pit_ncolor='pit_ncolor';
		/** [menu] Modifier le contenu */
		const pit_mod='pit_mod';
	}
	/** Groupe de personnes  */
	class Group extends Dir {
		/** [frame] Identification */
		const grp_fr_ident='grp_fr_ident';
		/** [text] nom */
		const grp_name='grp_name';
		/** [text] mail */
		const grp_mail='grp_mail';
		/** [enum] sans adresse mail de groupe */
		const grp_hasmail='grp_hasmail';
		/** [frame] Groupes */
		const grp_fr='grp_fr';
		/** [account] sous groupes */
		const grp_idgroup='grp_idgroup';
		/** [account] groupes parents */
		const grp_idpgroup='grp_idpgroup';
		/** [enum] est rafraîchi */
		const grp_isrefreshed='grp_isrefreshed';
		/** [menu] Gérer les membres */
		const grp_adduser='grp_adduser';
		/** [menu] Rafraîchir */
		const grp_refresh='grp_refresh';
		/** [frame] basique */
		const fr_basic='fr_basic';
		/** [text] titre */
		const ba_title='ba_title';
		/** [frame] Restrictions */
		const fld_fr_rest='fld_fr_rest';
		/** [frame] Profils par défaut */
		const fld_fr_prof='fld_fr_prof';
	}
	/** Rôle  */
	class Role {
		/** [frame] Identification */
		const role_fr_ident='role_fr_ident';
		/** [text] Référence */
		const role_login='role_login';
		/** [text] Libellé */
		const role_name='role_name';
		/** [int] Identifiant système */
		const us_whatid='us_whatid';
	}
	/** Groupe d'utilisateurs  */
	class Igroup extends Group {
		/** [frame] Système */
		const grp_fr_intranet='grp_fr_intranet';
		/** [text] identifiant */
		const us_login='us_login';
		/** [int] identifiant système */
		const us_whatid='us_whatid';
		/** [account] groupe id */
		const us_meid='us_meid';
		/** [docid("ROLE")] Rôles associés */
		const grp_roles='grp_roles';
		/** [menu] Modifier la hiérarchie */
		const grp_choosegroup='grp_choosegroup';
	}
	/** utilisateur  */
	class Iuser {
		/** [frame] État civil */
		const us_fr_ident='us_fr_ident';
		/** [text] nom */
		const us_lname='us_lname';
		/** [text] prénom */
		const us_fname='us_fname';
		/** [text] mail */
		const us_mail='us_mail';
		/** [text] mail principal */
		const us_extmail='us_extmail';
		/** [tab] Système */
		const us_tab_system='us_tab_system';
		/** [frame] Identification intranet */
		const us_fr_intranet='us_fr_intranet';
		/** [account] utilisateur id */
		const us_meid='us_meid';
		/** [text] login */
		const us_login='us_login';
		/** [text] identifiant */
		const us_whatid='us_whatid';
		/** [array] Rôles */
		const us_t_roles='us_t_roles';
		/** [account] Rôle */
		const us_roles='us_roles';
		/** [enum] Origine */
		const us_rolesorigin='us_rolesorigin';
		/** [account] Groupe */
		const us_rolegorigin='us_rolegorigin';
		/** [array] groupes d'appartenance */
		const us_groups='us_groups';
		/** [text] groupe (titre) */
		const us_group='us_group';
		/** [account] Groupe */
		const us_idgroup='us_idgroup';
		/** [int] date d'expiration epoch */
		const us_expires='us_expires';
		/** [int] délai d'expiration en jours */
		const us_daydelay='us_daydelay';
		/** [date] date d'expiration */
		const us_expiresd='us_expiresd';
		/** [time] heure d'expiration */
		const us_expirest='us_expirest';
		/** [int] délai d'expiration epoch */
		const us_passdelay='us_passdelay';
		/** [text] login LDAP */
		const us_ldapdn='us_ldapdn';
		/** [frame] Suppléants */
		const us_fr_substitute='us_fr_substitute';
		/** [account] Suppléant */
		const us_substitute='us_substitute';
		/** [account] Titulaires */
		const us_incumbents='us_incumbents';
		/** [frame] Mot de passe */
		const us_fr_userchange='us_fr_userchange';
		/** [password] nouveau mot de passe */
		const us_passwd1='us_passwd1';
		/** [password] confirmation mot de passe */
		const us_passwd2='us_passwd2';
		/** [frame] Paramètre */
		const us_fr_default='us_fr_default';
		/** [account] Groupe par défaut */
		const us_defaultgroup='us_defaultgroup';
		/** [frame] Sécurité */
		const us_fr_security='us_fr_security';
		/** [enum] état du compte */
		const us_status='us_status';
		/** [int] échecs de connexion */
		const us_loginfailure='us_loginfailure';
		/** [date] Date d'expiration du compte */
		const us_accexpiredate='us_accexpiredate';
		/** [menu] Réinitialiser échecs de connexion */
		const us_menuresetlogfails='us_menuresetlogfails';
		/** [menu] Activer le compte */
		const us_activateaccount='us_activateaccount';
		/** [menu] Désactiver le compte */
		const us_desactivateaccount='us_desactivateaccount';
		/** [frame] confidentialité */
		const us_fr_privacy='us_fr_privacy';
		/** [menu] Actualiser les utilisateurs */
		const us_inituser='us_inituser';
	}
	/** Recherche  */
	class Search extends Base {
		/** [account] Auteur */
		const se_author='se_author';
		/** [color] Couleur intercalaire */
		const gui_color='gui_color';
		/** [enum] Utilisable comme flux RSS */
		const gui_isrss='gui_isrss';
		/** [enum] Flux RSS système */
		const gui_sysrss='gui_sysrss';
		/** [enum] À utiliser dans les menus */
		const se_memo='se_memo';
		/** [frame] Critère */
		const se_crit='se_crit';
		/** [text] Mot-clef */
		const se_key='se_key';
		/** [enum] Révision */
		const se_latest='se_latest';
		/** [enum] Mode  */
		const se_case='se_case';
		/** [text] Famille */
		const se_fam='se_fam';
		/** [docid] Famille (id) */
		const se_famid='se_famid';
		/** [enum] Inclure les documents système */
		const se_sysfam='se_sysfam';
		/** [docid] Dossier racine */
		const se_idfld='se_idfld';
		/** [text] À partir du dossier */
		const se_cfld='se_cfld';
		/** [enum] Dans la poubelle */
		const se_trash='se_trash';
		/** [docid("ARCHIVING")] Dans l'archive */
		const se_archive='se_archive';
		/** [int] Profondeur de recherche */
		const se_sublevel='se_sublevel';
		/** [text] Requête sql */
		const se_sqlselect='se_sqlselect';
		/** [docid] Id dossier père courant */
		const se_idcfld='se_idcfld';
		/** [text] Dossier père courant */
		const se_ccfld='se_ccfld';
		/** [text] Trié par */
		const se_orderby='se_orderby';
		/** [enum] Sans sous famille */
		const se_famonly='se_famonly';
		/** [enum] Document */
		const se_acl='se_acl';
		/** [text] Requête statique */
		const se_static='se_static';
		/** [menu] Ouvrir */
		const se_open='se_open';
		/** [menu] Ouvrir comme une chemise */
		const se_openfolio='se_openfolio';
		/** [menu] RSS visible/masquée aux utilisateurs */
		const se_setsysrss='se_setsysrss';
	}
	/** Recherche groupée  */
	class Msearch extends Search {
		/** [frame] Critère */
		const se_crit='se_crit';
		/** [frame] Les recherches */
		const se_fr_searches='se_fr_searches';
		/** [array] Ensemble de recherche */
		const seg_t_cond='seg_t_cond';
		/** [docid("SEG_IDCOND")] Recherche */
		const seg_idcond='seg_idcond';
		/** [text] Recherche (titre) */
		const seg_cond='seg_cond';
	}
	/** Recherche détaillée  */
	class Dsearch extends Search {
		/** [frame] Conditions */
		const se_fr_detail='se_fr_detail';
		/** [enum] Condition */
		const se_ol='se_ol';
		/** [array] Conditions */
		const se_t_detail='se_t_detail';
		/** [enum] Opérateur */
		const se_ols='se_ols';
		/** [enum] Parenthèse gauche */
		const se_leftp='se_leftp';
		/** [text] Attributs */
		const se_attrids='se_attrids';
		/** [text] Fonctions */
		const se_funcs='se_funcs';
		/** [text] Mot-clefs */
		const se_keys='se_keys';
		/** [enum] Parenthèse droite */
		const se_rightp='se_rightp';
		/** [array] Filtres */
		const se_t_filters='se_t_filters';
		/** [xml] Filtre */
		const se_filter='se_filter';
		/** [enum] Type */
		const se_typefilter='se_typefilter';
	}
	/** Recherche spécialisée  */
	class Ssearch extends Search {
		/** [frame] Fonction */
		const se_fr_function='se_fr_function';
		/** [text] Fichier PHP */
		const se_phpfile='se_phpfile';
		/** [text] Fonction PHP */
		const se_phpfunc='se_phpfunc';
		/** [text] Argument PHP */
		const se_phparg='se_phparg';
	}
	/** Rapport  */
	class Report extends Dsearch {
		/** [tab] Présentation */
		const rep_tab_presentation='rep_tab_presentation';
		/** [frame] Présentation */
		const rep_fr_presentation='rep_fr_presentation';
		/** [longtext] Description */
		const rep_caption='rep_caption';
		/** [text] Tri */
		const rep_sort='rep_sort';
		/** [text] Id tri */
		const rep_idsort='rep_idsort';
		/** [enum] Ordre */
		const rep_ordersort='rep_ordersort';
		/** [int] Limite */
		const rep_limit='rep_limit';
		/** [array] Colonnes */
		const rep_tcols='rep_tcols';
		/** [text] Label */
		const rep_lcols='rep_lcols';
		/** [text] Id colonnes */
		const rep_idcols='rep_idcols';
		/** [text] Option de présentation */
		const rep_displayoption='rep_displayoption';
		/** [color] Couleur */
		const rep_colors='rep_colors';
		/** [enum] Pied de tableau */
		const rep_foots='rep_foots';
		/** [enum] Style */
		const rep_style='rep_style';
		/** [color] Couleur entête */
		const rep_colorhf='rep_colorhf';
		/** [color] Couleur impaire */
		const rep_colorodd='rep_colorodd';
		/** [color] Couleur paire */
		const rep_coloreven='rep_coloreven';
		/** [menu] Export CSV */
		const rep_csv='rep_csv';
		/** [menu] Version imprimable */
		const rep_imp='rep_imp';
		/** [frame] Paramètres */
		const rep_fr_param='rep_fr_param';
		/** [htmltext] Texte à afficher pour les valeurs protégées */
		const rep_noaccesstext='rep_noaccesstext';
		/** [int] Limite d'affichage pour le nombre de rangées */
		const rep_maxdisplaylimit='rep_maxdisplaylimit';
	}
	/** Cycle de vie  */
	class Wdoc extends Base {
		/** [longtext] description */
		const wf_desc='wf_desc';
		/** [menu] Initialisation */
		const wf_init='wf_init';
		/** [docid("-1")] Famille */
		const wf_famid='wf_famid';
		/** [text] Famille (titre) */
		const wf_fam='wf_fam';
		/** [frame] Profil dynamique */
		const dpdoc_fr_dyn='dpdoc_fr_dyn';
		/** [docid("-1")] Famille */
		const dpdoc_famid='dpdoc_famid';
		/** [text] Famille (titre) */
		const dpdoc_fam='dpdoc_fam';
		/** [action] Voir le graphe */
		const wf_graph='wf_graph';
		/** [tab] Étapes */
		const wf_tab_states='wf_tab_states';
		/** [tab] Transitions */
		const wf_tab_transitions='wf_tab_transitions';
		/** [action] Voir le graphe complet */
		const wf_graphc='wf_graphc';
	}
	/** Masque de saisie  */
	class Mask extends Base {
		/** [frame] Famille */
		const msk_fr_rest='msk_fr_rest';
		/** [docid("FAMILIES")] Famille */
		const msk_famid='msk_famid';
		/** [text] Famille (titre) */
		const msk_fam='msk_fam';
		/** [array] Contenu */
		const msk_t_contain='msk_t_contain';
		/** [text] Attrid */
		const msk_attrids='msk_attrids';
		/** [text] Visibilité */
		const msk_visibilities='msk_visibilities';
		/** [text] Obligatoire */
		const msk_needeeds='msk_needeeds';
	}
	/**   */
	class Cvdoc extends Base {
		/** [longtext] Description */
		const cv_desc='cv_desc';
		/** [docid] Famille (id) */
		const cv_famid='cv_famid';
		/** [text] Famille */
		const cv_fam='cv_fam';
		/** [array] Vues */
		const cv_t_views='cv_t_views';
		/** [text] Identifiant de la vue */
		const cv_idview='cv_idview';
		/** [text] Label */
		const cv_lview='cv_lview';
		/** [enum] Type */
		const cv_kview='cv_kview';
		/** [text] Zone (Layout) */
		const cv_zview='cv_zview';
		/** [docid("MASK")] Masque */
		const cv_mskid='cv_mskid';
		/** [text] Masque(titre) */
		const cv_msk='cv_msk';
		/** [int] Ordre de sélection */
		const cv_order='cv_order';
		/** [enum] Affichable */
		const cv_displayed='cv_displayed';
		/** [text] Menu */
		const cv_menu='cv_menu';
		/** [frame] Vues par défauts */
		const cv_fr_default='cv_fr_default';
		/** [text] Id création vues par défaut */
		const cv_idcview='cv_idcview';
		/** [text] Création vue */
		const cv_lcview='cv_lcview';
		/** [frame] Profil dynamique */
		const dpdoc_fr_dyn='dpdoc_fr_dyn';
		/** [docid("-1")] Famille pour le profil */
		const dpdoc_famid='dpdoc_famid';
		/** [text] Famille pour le profil (titre) */
		const dpdoc_fam='dpdoc_fam';
	}
	/** État libre  */
	class Freestate {
		/** [frame] Identification */
		const frst_fr_ident='frst_fr_ident';
		/** [text] Nom */
		const frst_name='frst_name';
		/** [longtext] Description */
		const frst_desc='frst_desc';
		/** [docid] Famille (id) */
		const frst_famid='frst_famid';
		/** [text] Famille */
		const frst_fam='frst_fam';
		/** [color] Couleur */
		const frst_color='frst_color';
	}
	/** Accord  */
	class Wask {
		/** [frame] Identification */
		const was_fr_ident='was_fr_ident';
		/** [text] Référence */
		const was_ref='was_ref';
		/** [longtext] Question */
		const was_ask='was_ask';
		/** [array] Réponses possibles */
		const was_t_answer='was_t_answer';
		/** [text] Clef */
		const was_keys='was_keys';
		/** [text] Libellé */
		const was_labels='was_labels';
		/** [frame] Profil dynamique */
		const dpdoc_fr_dyn='dpdoc_fr_dyn';
		/** [docid] Famille (id) */
		const dpdoc_famid='dpdoc_famid';
		/** [text] Famille */
		const dpdoc_fam='dpdoc_fam';
	}
	/** Minuteur  */
	class Timer {
		/** [frame] Identification */
		const tm_fr_ident='tm_fr_ident';
		/** [text] Titre */
		const tm_title='tm_title';
		/** [docid("x")] Famille */
		const tm_family='tm_family';
		/** [docid("x")] Famille cycle */
		const tm_workflow='tm_workflow';
		/** [text] Date de référence */
		const tm_dyndate='tm_dyndate';
		/** [double] Décalage (en jours) */
		const tm_refdaydelta='tm_refdaydelta';
		/** [double] Décalage (en heures) */
		const tm_refhourdelta='tm_refhourdelta';
		/** [array] Configuration */
		const tm_t_config='tm_t_config';
		/** [double] Délai (en jours) */
		const tm_delay='tm_delay';
		/** [double] Délai (en heures) */
		const tm_hdelay='tm_hdelay';
		/** [int] Nombre d'itérations */
		const tm_iteration='tm_iteration';
		/** [docid("MAILTEMPLATE")] Modèle de mail */
		const tm_tmail='tm_tmail';
		/** [text] Nouvel état */
		const tm_state='tm_state';
		/** [text] Méthode */
		const tm_method='tm_method';
	}
	/** fichiers  */
	class File {
		/** [frame] description */
		const fi_frdesc='fi_frdesc';
		/** [text] titre */
		const fi_title='fi_title';
		/** [text] titre */
		const fi_titlew='fi_titlew';
		/** [text] sujet */
		const fi_subject='fi_subject';
		/** [text] mots-clés */
		const fi_keyword='fi_keyword';
		/** [longtext] résumé */
		const fi_description='fi_description';
		/** [frame] Fichiers */
		const fi_fr_oformat='fi_fr_oformat';
		/** [file] principal */
		const fi_file='fi_file';
		/** [array] autres */
		const ft_t_oformat='ft_t_oformat';
		/** [file] fichier */
		const fi_ofile='fi_ofile';
	}
	/** image  */
	class Image {
		/** [frame] image */
		const img_frfile='img_frfile';
		/** [text] titre */
		const img_title='img_title';
		/** [image] image */
		const img_file='img_file';
		/** [longtext] description */
		const img_description='img_description';
		/** [frame] caractéristiques */
		const img_fr_char='img_fr_char';
		/** [enum] catégorie */
		const img_catg='img_catg';
	}
	/** texte  */
	class Text extends Base {
		/** [text] titre */
		const ba_title='ba_title';
		/** [htmltext] texte */
		const txt_text='txt_text';
		/** [frame] annexes */
		const txt_fr_anx='txt_fr_anx';
		/** [array] images */
		const txt_t_img='txt_t_img';
		/** [image] images */
		const txt_img='txt_img';
		/** [file] source */
		const txt_img_ori='txt_img_ori';
	}
	/** chemise  */
	class Portfolio extends Dir {
		/** [menu] ouvrir */
		const pfl_open='pfl_open';
		/** [docid] id chemise defaut  */
		const pfl_iddef='pfl_iddef';
		/** [text] chemise defaut */
		const pfl_def='pfl_def';
		/** [menu] ouvrir */
		const fld_open='fld_open';
		/** [menu] ouvrir comme une chemise */
		const fld_openfolio='fld_openfolio';
		/** [frame] Affichage de la chemise */
		const pfl_fr_init='pfl_fr_init';
		/** [enum] style de la liste */
		const pfl_liststyle='pfl_liststyle';
		/** [enum] affichage dernier onglet consulté */
		const pfl_savetab='pfl_savetab';
		/** [enum] affichage dernière disposition */
		const pfl_savedispo='pfl_savedispo';
		/** [array] Onglets à lier (dynamique) */
		const pfl_t_linktab='pfl_t_linktab';
		/** [docid("DIR")] id onglet à lier */
		const pfl_idlinktab='pfl_idlinktab';
		/** [text] onglet à lier */
		const pfl_linktab='pfl_linktab';
		/** [array] Onglets à copier pour les nouveaux documents */
		const pfl_t_copytab='pfl_t_copytab';
		/** [docid("DIR")] id onglet à copier */
		const pfl_idcopytab='pfl_idcopytab';
		/** [text] onglet à copier */
		const pfl_copytab='pfl_copytab';
	}
	/** intercalaire  */
	class Guidecard extends Dir {
	}
	/** intercalaire dynamique  */
	class Sguidecard extends Dsearch {
	}
	/** Traitement  */
	class Batch extends Portfolio {
		/** [menu] Planification */
		const batch_plan='batch_plan';
		/** [frame] Identification */
		const batch_fr_ident='batch_fr_ident';
		/** [text] Titre */
		const batch_title='batch_title';
		/** [longtext] Description */
		const batch_desc='batch_desc';
		/** [docid("FAMILY")] Id famille */
		const batch_idfam='batch_idfam';
		/** [text] Famille */
		const batch_fam='batch_fam';
	}
	/** Processus  */
	class Exec {
		/** [frame] Identification */
		const exec_fr_ident='exec_fr_ident';
		/** [docid("IUSER")] Exécutant */
		const exec_iduser='exec_iduser';
		/** [text] Exécutant (titre) */
		const exec_user='exec_user';
		/** [docid("BATCH")] Issue de */
		const exec_idref='exec_idref';
		/** [text] Référent (titre) */
		const exec_ref='exec_ref';
		/** [text] Titre */
		const exec_title='exec_title';
		/** [enum] Exécution */
		const exec_status='exec_status';
		/** [timestamp] Exécution depuis */
		const exec_statusdate='exec_statusdate';
		/** [frame] Traitement */
		const exec_fr_batch='exec_fr_batch';
		/** [text] Application */
		const exec_application='exec_application';
		/** [text] Action */
		const exec_action='exec_action';
		/** [text] Api */
		const exec_api='exec_api';
		/** [array] Paramètres */
		const exec_t_parameters='exec_t_parameters';
		/** [text] Variable */
		const exec_idvar='exec_idvar';
		/** [text] Valeur */
		const exec_valuevar='exec_valuevar';
		/** [frame] Dates */
		const exec_fr_date='exec_fr_date';
		/** [timestamp] Précédente date d'exécution */
		const exec_prevdate='exec_prevdate';
		/** [timestamp] Prochaine date d'exécution */
		const exec_nextdate='exec_nextdate';
		/** [timestamp] À exécuter le */
		const exec_handnextdate='exec_handnextdate';
		/** [int] Période en jours */
		const exec_periodday='exec_periodday';
		/** [int] Période en heures */
		const exec_periodhour='exec_periodhour';
		/** [int] Période en minutes */
		const exec_periodmin='exec_periodmin';
		/** [timestamp] Jusqu'au */
		const exec_periodenddate='exec_periodenddate';
		/** [enum] Jour de la semaine */
		const exec_perioddaynumber='exec_perioddaynumber';
		/** [frame] Compte-rendu */
		const exec_fr_cr='exec_fr_cr';
		/** [timestamp("%A %d %B %Y %X")] Date d'exécution */
		const exec_date='exec_date';
		/** [time("%H:%M:%S")] Durée d'exécution */
		const exec_elapsed='exec_elapsed';
		/** [text] Statut */
		const exec_state='exec_state';
		/** [ifile] Détail */
		const exec_detail='exec_detail';
		/** [longtext] Log */
		const exec_detaillog='exec_detaillog';
		/** [menu] Exécuter maintenant */
		const exec_bgexec='exec_bgexec';
		/** [menu] Abandonner l'exécution en cours */
		const exec_reset='exec_reset';
		/** [frame] Paramètre */
		const exec_fr_param='exec_fr_param';
		/** [docid("IUSER")] Administrateur */
		const exec_idadmin='exec_idadmin';
	}
	/** Publipostage  */
	class Publimail extends Batch {
		/** [frame] Basique */
		const fr_basic='fr_basic';
		/** [text] Titre */
		const ba_title='ba_title';
		/** [frame] Identification */
		const pubm_fr_ident='pubm_fr_ident';
		/** [text] Sujet */
		const pubm_title='pubm_title';
		/** [frame] Corps */
		const pubm_fr_body='pubm_fr_body';
		/** [htmltext] Corps du message */
		const pubm_body='pubm_body';
		/** [image] Image de fond */
		const pubm_bgimg='pubm_bgimg';
		/** [frame] Attachements */
		const pubm_fr_att='pubm_fr_att';
		/** [array] Attachements */
		const pubm_t_att='pubm_t_att';
		/** [text] Description */
		const pubm_adesc='pubm_adesc';
		/** [file] Fichier */
		const pubm_fdesc='pubm_fdesc';
		/** [action] Envoyer */
		const pubm_send='pubm_send';
		/** [action] Prévisualisation */
		const pubm_preview='pubm_preview';
		/** [action] Afficher */
		const pubm_display='pubm_display';
		/** [action] Imprimer */
		const pubm_print='pubm_print';
		/** [frame] Configuration */
		const pubm_fr_config='pubm_fr_config';
		/** [docid] Id famille */
		const pubm_idfam='pubm_idfam';
		/** [text] Famille du lot */
		const pubm_fam='pubm_fam';
		/** [text] Attribut mail */
		const pubm_mailatt='pubm_mailatt';
	}
	/** Action basique  */
	class Basicbatch extends Batch {
		/** [action] Verrouiller */
		const bbatch_lock='bbatch_lock';
		/** [action] Déverrouiller */
		const bbatch_unlock='bbatch_unlock';
		/** [action] Supprimer */
		const bbatch_delete='bbatch_delete';
		/** [action] Dupliquer */
		const bbatch_copy='bbatch_copy';
	}
}
