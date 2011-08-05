
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * Currently Unused. Kept for later reference still.
 * @author Clément Laballe
 */

/**
 * CustomView
 * Class to handle custom view rendering from multiple documents.
 * @param {Object} config
 */
Fdl.CustomView = function(){

    this.documentArray = new Array();
    this.documentForm;
    
};

Fdl.CustomView.prototype = {

    /**
     * Add a document from which to render.
     * Document will be referenced by its index in layout.
     * @param {Object} document
     * @param {Object} index
     */
    addDocument: function(document, index){
        this.documentArray[index] = document;
    },
    
    addForm: function(form){
        this.documentForm = form;
    },
    
    /**
     * Generate from template and documents.
     * @param {Object} layout
     * @param {Object} renderTo
     */
    renderLayout: function(layout, renderTo){
    
        var reg = new RegExp("", "ig");
        
        // Handle the [IF][ENDIF] structure
        // Algo : while (no more [IF] found (
        // - Search for [IF condition] and catch condition
        // - Replace [IF condition] ... [ENDIF condition] by ... if condition is true or by nothing if condition is false
        reg.compile("\\[IF (\\w*)\\]", "ig");
        while ((result = reg.exec(layout)) != null) {
            if (eval(result[1])) {
                reg.compile("\\[(END)?IF " + result[1] + "\\]", "ig");
                layout = layout.replace(reg, "");
            }
            else {
                reg.compile("\\[IF " + result[1] + "\\].*\\[ENDIF " + result[1] + "\\]", "ig");
                layout = layout.replace(reg, "");
            }
        }
        
        reg.compile("\\[CORE:(.*)\\]", "ig");
        
        reg.compile("\\[TEXT:(.*)\\]", "ig");
        
        reg.compile("\\[(?:DOC:([0-9]*))? *DATA:([0-9]*)\\]", "ig"); // Example : match [DOC:3 DATA:1200] and capture 3 and 1200, so result is [ '[DOC:3 DATA:1200]', '3', '1200' ]
        var result;
        var doc; // Document index
        var attr; // Attribute id
        while ((result = reg.exec(layout)) != null) {
        
            // Result[1] holds document index and is not defined if there is no document index, in this case, assign default 0
            if (result[1]) {
                doc = result[1];
            }
            else {
                doc = 0;
            }
            
            // result[2] contains the result for our first match of sub reg exp (inside parenthesis). Here, it is catching the attribute id.
            var attr = this.documentArray[doc].getAttribute(result[2]);
            
            var _widget = Fdl.DocumentView.getExtInput(attr.id);
            
            this.documentForm.add(_widget);
            
        }
        
        this.documentForm.doLayout();
        
    },
    
    /**
     * Generate from template and documents.
     * @param {Object} layout
     * @param {Object} renderTo
     */
    renderLayoutDeprecated: function(layout, renderTo){
    
        var reg = new RegExp("", "ig");
        
        // Handle the [IF][ENDIF] structure
        // Algo : while (no more [IF] found (
        // - Search for [IF condition] and catch condition
        // - Replace [IF condition] ... [ENDIF condition] by ... if condition is true or by nothing if condition is false
        reg.compile("\\[IF (\\w*)\\]", "ig");
        while ((result = reg.exec(layout)) != null) {
            if (eval(result[1])) {
                reg.compile("\\[(END)?IF " + result[1] + "\\]", "ig");
                layout = layout.replace(reg, "");
            }
            else {
                reg.compile("\\[IF " + result[1] + "\\].*\\[ENDIF " + result[1] + "\\]", "ig");
                layout = layout.replace(reg, "");
            }
        }
        
        reg.compile("\\[ID\\]", "ig");
        layout = layout.replace(reg, this.documentArray[0].id);
        
        reg.compile("\\[TITLE\\]", "ig");
        layout = layout.replace(reg, this.documentArray[0].getTitle());
        
        reg.compile("\\[(?:DOC:([0-9]*))? *(A|L|V)_([0-9]*)\\]", "ig"); // Example : match [DOC:3 L_1200] and capture 3 and 1200, so result is [ '[DOC:3 L_1200]', '3', 'L', '1200' ]
        var result;
        var doc; // Document index
        var attr; // Attribute id
        var widget = [];
        
        while ((result = reg.exec(layout)) != null) {
        
            // Result[1] holds document index and is not defined if there is no document index, in this case, assign default 0
            if (result[1]) {
                doc = result[1];
            }
            else {
                doc = 0;
            }
            
            // result[1] contains the result for our first match of sub reg exp (inside parenthesis). Here, it is catching the attribute id.
            var attr = this.documentArray[doc].getAttribute(result[3]);
            
            if (result[2] == 'A') {
                // Create the div to hold the widget.
                var _reg = new RegExp("\\[(DOC:(.*))? *A_" + attr.id + "\\]", "ig");
                layout = layout.replace(_reg, "<div id='attribute" + attr.id + "'></div>");
                
                var _widget = new Ext.fdl.Text({
                    id: 'Toto',
                    fieldLabel: attr.getLabel(),
                    renderToLater: 'attribute' + attr.id
                });
                
                // Stock the widget for later rendering.
                widget.push(_widget);
                
                this.documentForm.add(_widget);
                this.documentForm.doLayout();
            }
            
            if (result[2] == 'L') {
                var _reg = new RegExp("\\[(DOC:(.*))? *L_" + attr.id + "\\]", "ig");
                layout = layout.replace(_reg, attr.getLabel());
            }
            
        }
        
        Ext.get(renderTo).update(layout); // Here there should be a distinction whether Ext.get() returns a div or an ext component.
        l = widget.length;
        for (var i = 0; i < l; i++) {
            widget[i].render(widget[i].renderToLater);
        }
        
    }
    
};