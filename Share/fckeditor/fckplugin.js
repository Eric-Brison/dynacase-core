
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/* 
FCKCommands.RegisterCommand(commandName, command)
       commandName - Command name, referenced by the Toolbar, etc...
       command - Command object (must provide an Execute() function).
*/
var oQuickSave = new Object() ;
oQuickSave.Name = 'QuickSave' ;

// This is the standard function used to execute the command (called when clicking in the context menu item).
oQuickSave.Execute = function()
{
  window.parent.quicksave();
}
// This is the standard function used to retrieve the command state (it could be disabled for some reason).
oQuickSave.GetState = function()
{
	// Let's make it always enabled.
	return FCK_TRISTATE_OFF ;
}


// Register the related commands.
FCKCommands.RegisterCommand('QuickSave',oQuickSave );

// Create the "Find" toolbar button.
var oQuickSaveItem = new FCKToolbarButton('QuickSave', FCKLang['DlgQuickSaveTitle']);
oQuickSaveItem.IconPath = FCKConfig.PluginsPath + 'quicksave/floppy.png' ;

// 'QuickSave' is the name used in the Toolbar config.
FCKToolbarItems.RegisterItem( 'QuickSave', oQuickSaveItem ) ;

//-----

var oDocattr = new Object() ;
oDocattr.Name = 'DocAttr' ;

// This is the standard function used to execute the command (called when clicking in the context menu item).
oDocattr.Execute = function(a) { 
  FCK.InsertHtml(a);
}
// This is the standard function used to retrieve the command state (it could be disabled for some reason).
oDocattr.GetState = function()
{
	// Let's make it always enabled.
	return FCK_TRISTATE_OFF ;
}


// Register the related commands.
FCKCommands.RegisterCommand('Docattr',oDocattr );
var FCKToolbarDocattrCombo = function( tooltip, style )
{
  this.CommandName	=  'Docattr';
	this.Label		= this.GetLabel() ;
	this.Tooltip	= tooltip ? tooltip : this.Label ;
	this.Style		= style ? style : FCK_TOOLBARITEM_ICONTEXT ;
}

// Inherit from FCKToolbarSpecialCombo.
FCKToolbarDocattrCombo.prototype = new FCKToolbarSpecialCombo ;


FCKToolbarDocattrCombo.prototype.GetLabel = function()
{
	return FCKLang.Docattr ;
}

FCKToolbarDocattrCombo.prototype.CreateItems = function( targetSpecialCombo )
{
  targetSpecialCombo.FieldWidth = 70 ;

  if (parent.FCK_DocAttr) {
    var aSizes = parent.FCK_DocAttr.split(';') ;
    for ( var i = 0 ; i < aSizes.length ; i++ )
      {
	var aSizeParts = aSizes[i].split('|') ;
	if (aSizeParts[0]) this._Combo.AddItem( aSizeParts[0], aSizeParts[1]+'<pre> '+aSizeParts[0].replace('<','&lt;').replace('>','&gt;')  +  '</pre>', aSizeParts[1] ) ;
      }
  }
}
  
var FCKDocattrCommand = function()
{
	this.Name = 'Docattr' ;
}

FCKDocattrCommand.prototype.Execute = function( docattr )
{
	if ( typeof( docattr ) == 'string' ) docattr = parseInt(docattr, 10) ;

	if ( docattr == null || docattr == '' )
	{
		// TODO: Remove font size attribute (Now it works with size 3. Will it work forever?)
		FCK.ExecuteNamedCommand( 'Docattr', 3 ) ;
	}
	else
		FCK.ExecuteNamedCommand( 'Docattr', docattr ) ;
}

FCKDocattrCommand.prototype.GetState = function()
{
	return FCK.GetNamedCommandValue( 'Docattr' ) ;
}


// Create the "Find" toolbar button.
var oDocattrItem = new FCKToolbarDocattrCombo('DocAttr');
//oDocattrItem.IconPath = FCKConfig.PluginsPath + 'docattr/floppy.png' ;

// 'Docattr' is the name used in the Toolbar config.
FCKToolbarItems.RegisterItem( 'DocAttr', oDocattrItem ) ;

