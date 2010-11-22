
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

FCKConfig.ToolbarSets["Simple"] = [
				   ['QuickSave','FontFormat','FontSize'],
				   ['Bold','Italic','Underline','StrikeThrough','-','OrderedList','UnorderedList','-','SpecialChar','Link','Unlink'],
				   '/',
				   ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],['OrderedList','UnorderedList','-','Outdent','Indent'],
				   ['TextColor','BGColor','Anchor','Image','Table','-','FitWindow','-','Source','SpellCheck','About']
] ;

FCKConfig.ToolbarSets["SimpleNoQS"] = [
				   ['FontFormat','FontSize'],
				   ['Bold','Italic','Underline','StrikeThrough','-','OrderedList','UnorderedList','-','SpecialChar','Link','Unlink'],
				   '/',
				   ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],['OrderedList','UnorderedList','-','Outdent','Indent'],
				   ['TextColor','BGColor','Anchor','Image','Table','-','FitWindow','-','Source','SpellCheck','About']
] ;

FCKConfig.ToolbarSets["Table"] = [
				   ['QuickSave'],
				   ['Bold','Italic','Underline','StrikeThrough','-','OrderedList','UnorderedList','-','SpecialChar','Link','Unlink'],				   
				   ['Table','-','FitWindow','-','Source','SpellCheck','About']
] ;

FCKConfig.ToolbarSets["DocAttr"] = [
				   ['QuickSave','DocAttr','FontFormat','FontSize'],
				   ['Bold','Italic','Underline','StrikeThrough','-','OrderedList','UnorderedList','-','SpecialChar','Link','Unlink'],
				   '/',
				   ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],['OrderedList','UnorderedList','-','Outdent','Indent'],
				   ['TextColor','BGColor','Anchor','Image','Table','-','FitWindow','-','Source','SpellCheck','About']
] ;

FCKConfig.Plugins.Add( 'quicksave', 'en,fr' ) ;

FCKConfig.HtmlEncodeOutput = true ; 

FCKConfig.SpellChecker = 'SpellerPages';