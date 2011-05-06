//------------------------------ MULTI DOC MODEL ------------------------------//
$(function(){
	
	var bodyTemplate="<div id='tabs' class='tabs'></div><div id='doc_content' class='doc_content'></div>";
	
	//Modèle d'un document
 	Doc = Backbone.Model.extend({
 	
 		title: null,
 		
 		icon: null,
 		
 		id: null,
 		
 		url: null,
 			
 		erase: function() {
	      this.view.remove();
    	}
 	});
 	
 	//Modèle d'une liste de documents
 	DocList = Backbone.Collection.extend({
 	
 		model: Doc,
 		
   });
   
 	window.Docs = new DocList;
   
//------------------------------ MULTI DOC VIEW ------------------------------//

	DocView = Backbone.View.extend({
		
		template: _.template($('#doc-object').html()),
		
		events:
		{
			"click .img_new" : "newPage",
			"click .img_del" : "close",
			"click .tab" : "open",
			"mouseover .tabcontent" : "showOptions",
			"click .tabcontent" : "showOptions",
			"mouseout .tabcontent" : "hideOptions"
		},
		
		initialize: function() 
		{
			_.bindAll(this, 'render', 'close', 'open');
	      this.model.bind('change', this.render);
      	this.model.view = this;
	   },
	   
		render: function() {			
			var title = this.model.get('title'); //reçoit le title du document

	      $(this.el).html(this.template({
	      	content: title,
	      	image: this.model.get('icon'),
	      	id: this.model.get('id')
	      }));
	      return this;
    	},
    	
    	newPage: function()
    	{
    		//dernière url de l'iframe en cours
    		var path = document.getElementById('frame_' + this.model.get('id')).contentDocument.location.href; 
    		extract = window.open(path,this.model.get('title'),'width=600,height=600');
    		extract.moveTo((screen.width)/2-300,(screen.height)/2-300);
    		extract.focus();
    		this.close();
    	},
    	
    	close: function()
    	{
    		if((document.getElementById('frame_' + this.model.get('id')).style.display)=="block")
    		{
    			$('.content_frame').css('display','none');
    			if($('#frame_' + this.model.get('id')).next().length>0)
    			{
    				$('#frame_' + this.model.get('id')).next().css('display','block');
    				var idnew = $('#frame_' + this.model.get('id')).next().attr('id').substr(6,10);
    				$('#tab_' + idnew).removeClass().addClass('tab-active');   
    				$('#options_' + idnew).css('visibility','visible');		
    			}
    			else
    			{
    				if($('#frame_' + this.model.get('id')).prev().length>0)
    				{
    					$('#frame_' + this.model.get('id')).prev().css('display','block');
    					var idnew = $('#frame_' + this.model.get('id')).prev().attr('id').substr(6,10);
    					$('#tab_' + idnew).removeClass().addClass('tab-active');
    					$('#options_' + idnew).css('visibility','visible');
    				}
    			}
    			$('#frame_' + this.model.get('id')).remove();
    		}
    		else
    		{
    			$('#frame_' + this.model.get('id')).remove();
    		}
    		this.model.erase();
   	},
   	
   	open: function()
   	{
   		$('.content_frame').css('display','none');
   		$('.tab-active').removeClass().addClass('tab');
   		$('.options').css('visibility','hidden');
   		$('#tab_' + this.model.get('id')).removeClass().addClass('tab-active');
   		
   		if($('#frame_' + this.model.get('id')).length>0)
   		{
   			$('#frame_' + this.model.get('id')).css('display','block');
   			var existe=true;
   		}
   		else
   		{
	   		$('#doc_content').append("<iframe class='content_frame' id='frame_" + this.model.get('id') + "' style='display:block;' frameborder='no' name='document' src='" + this.model.get('url') +"' width='100%' height='100%'></iframe>");
	   	}

   	},
   
   	showOptions: function() {
   		$('#options_' + this.model.get('id')).css('visibility','visible');
   	},
   	
   	hideOptions: function() {
   		$('#options_' + this.model.get('id')).css('visibility','hidden');
   	},
   	
	   remove: function() {
	     	$(this.el).remove();
	   },
	});
	
	MultiDocument = Backbone.View.extend({
	
		el: $("#multidoc"),
		
		initialize: function() {
	      _.bindAll(this, 'newTab', 'render');
			Docs.bind('all', this.newTab);
	   },

   	newTab: function(_title, _icon, _id, _url) {
   		if($('#frame_' + _id).length<=0)
   		{
	   		doc = new Doc({title: _title, icon: _icon, id: _id, url: _url});
	   		
		      var view = new DocView({model: doc});
		 		
		      this.$("#tabs").append(view.render().el);
		      view.open();
	    	}
	    	else
	    	{
	    		$('.content_frame').css('display','none');
   			$('.tab-active').removeClass().addClass('tab');
   			$('#frame_' + _id).css('display','block');
   			$('#tab_' + _id).removeClass().addClass('tab-active');
   		}
    	},
    	
    	setInterfacePosition: function(position) {
    		if(position=="left")
    		{
    			$('#tabs').removeClass().addClass('tabs_horizontal');
    			$('#doc_content').removeClass().addClass('doc_content_horizontal');
    		}
			else
			{
				if(position=="top")
				{
					$('#tabs').removeClass().addClass('tabs');
    				$('#doc_content').removeClass().addClass('doc_content');
    			}
			}
    	},
    	
    	setLocation: function(div) {
    		$('#' + div).append(bodyTemplate);
    	}

   });
 });