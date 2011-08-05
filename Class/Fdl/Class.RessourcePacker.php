<?php
/**
 * JS scripts and CSS stylesheets merger/packer
 *
 * @author Anakeen 2011
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

class RessourcePacker {

	/**
	 * Pack JS scripts
	 * 
	 * @param Action $action The action on which the resulting JS will be added
	 * @param array $static_js List of static JS file to pack
	 * @param array $dynamic_js List of dynamic JS file to pack
	 * 
	 *  @return boolean true
	 */
	static function pack_js(Action &$action, array &$static_js = array(), array &$dynamic_js = array()) {
		foreach ($static_js as $jsfile) {
			if (is_file($jsfile)) {
				print sprintf("// --- <static file='%s'> ---\n", $jsfile);
				print str_replace("include_js(","//include_js(",file_get_contents($jsfile))."\n";
				print sprintf("// -- </static file='%s'> ---\n", $jsfile);
				print "\n";
			} else {
				print ("\nalert(\"$jsfile not found\");\n");
			}
		}
		foreach ($dynamic_js as $jsfile) {
			if (is_file($jsfile)) {
				$action->lay->template .= sprintf("// --- <dynamic file='%s'> ---\n", $jsfile);
				$action->lay->template .= str_replace("include_js(","//include_js(",file_get_contents($jsfile))."\n";
				$action->lay->template .= sprintf("// --- </dynamic file='%s'> ---\n", $jsfile);
			} else {
				$action->lay->template.=("\nalert(\"$jsfile not found\");\n");
			}
		}

		$action->lay->template=str_replace("\ninclude_js(","\n//include_js(",$action->lay->template);
		return true;
	}

	/**
	 * Pack CSS stylesheets
	 *
	 * @param Action $action The action on which the resulting CSS will be added
	 * @param array $static_css List of static CSS stylesheets to pack
	 * @param array $dynamic_css List of dynamic CSS stylesheets to pack
	 */
	static function pack_css(Action &$action, array $static_css = array(), array $dynamic_css = array()) {
		foreach( $static_css as $cssfile ) {
			if( is_file($cssfile) ) {
				print sprintf("/* <static file='%s'> */\n", $cssfile);
				print file_get_contents($jsfile)."\n";
				print sprintf("/* </static file='%s'> */\n", $cssfile);
				print "\n";
			} else {
				print sprintf("/* CSS file '%s' not found */\n", $cssfile);
			}
		}
		foreach( $dynamic_css as $cssfile ) {
			if( is_file($cssfile ) ) {
				$action->lay->template .= sprintf("/* <dynamic file='%s'> */\n", $cssfile);
				$action->lay->template .= file_get_contents($cssfile)."\n";
				$action->lay->template .= sprintf("/* </dynamic file='%s'> */\n", $cssfile);
			} else {
				$action->lay->template .= sprintf("/* CSS file '%s' not found */\n", $cssfile);
			}
		}
		return true;
	}

}

?>