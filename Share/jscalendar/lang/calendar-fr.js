// ** I18N
Calendar._DN = new Array
("Dimanche",
 "Lundi",
 "Mardi",
 "Mercredi",
 "Jeudi",
 "Vendredi",
 "Samedi",
 "Dimanche");
Calendar._MN = new Array
("Janvier",
 "Février",
 "Mars",
 "Avril",
 "Mai",
 "Juin",
 "Juillet",
 "Août",
 "Septembre",
 "Octobre",
 "Novembre",
 "Décembre");
Calendar._SDN = new Array
( "Dim",
 "Lun",
 "Mar",
 "Mer",
 "Jeu",
 "Ven",
 "Sam",
 "Dim");
Calendar._SMN = new Array
("Jan",
 "Fev",
 "Mar",
 "Avr",
 "Mai",
 "Juin",
 "Juil",
 "Aout",
 "Sep",
 "Oct",
 "Nov",
 "Dec");

// tooltips
Calendar._TT = {};

Calendar._TT["INFO"] = "A propos du calendrier";

Calendar._TT["ABOUT"] =
"DHTML Date/Heure Selecteur\n" +
"(c) dynarch.com 2002-2003\n" + // don't translate this this ;-)
"Pour la dernière version visitez: http://dynarch.com/mishoo/calendar.epl\n" +
"Distribué par GNU LGPL.  Voir http://gnu.org/licenses/lgpl.html pour les détails." +
"\n\n" +
"Sélection de la date :\n" +
"- Utiliser les boutons \xab, \xbb  pour sélectionner l\'année\n" +
"- Utiliser les boutons " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " pour sélectionner les mois\n" +
"- Garder la souris sur n'importe quel bouton pour une sélection plus rapide";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Sélection de l\'heure:\n" +
"- Cliquer sur heures ou minutes pour incrémenter\n" +
"- ou Maj-clic pour décrémenter\n" +
"- ou clic et glisser déplacer pour une sélection plus rapide";

Calendar._TT["TOGGLE"] = "Changer le premier jour de la semaine";
Calendar._TT["PREV_YEAR"] = "Année préc. (maintenir pour menu)";
Calendar._TT["PREV_MONTH"] = "Mois préc. (maintenir pour menu)";
Calendar._TT["GO_TODAY"] = "Atteindre date du jour";
Calendar._TT["NEXT_MONTH"] = "Mois suiv. (maintenir pour menu)";
Calendar._TT["NEXT_YEAR"] = "Année suiv. (maintenir pour menu)";
Calendar._TT["SEL_DATE"] = "Choisir une date";
Calendar._TT["DRAG_TO_MOVE"] = "Déplacer";
Calendar._TT["PART_TODAY"] = " (Aujourd'hui)";
Calendar._TT["MON_FIRST"] = "Commencer par lundi";
Calendar._TT["SUN_FIRST"] = "Commencer par dimanche";
Calendar._TT["CLOSE"] = "Fermer";
Calendar._TT["TODAY"] = "Aujourd'hui";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Afficher %s en premier";


// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "6,0";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d-%m-%y";
Calendar._TT["TT_DATE_FORMAT"] = " %A %e %B %Y";

Calendar._TT["WK"] = "sem";
