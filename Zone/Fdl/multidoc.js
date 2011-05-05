//------------------------------ MULTI DOC MODEL ------------------------------//
$(function(){

	//Modèle d'un document
 	Doc = Backbone.Model.extend({
 	
 		title: null,
 		
 		icon: null,
 		
 		id: null,
 		
 		url: null,
 			
 		effacer: function() {
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
			"click .img_suppr" : "close",
			"click .clickable" : "open"
		},
		
		initialize: function() 
		{
			_.bindAll(this, 'render', 'close', 'open');
	      this.model.bind('change', this.render);
      	this.model.view = this;
	   },
	   
		render: function() {			
			var title = this.model.get('title'); //reçoit le title du document
			if(title.length>20)
			{
				title = title.substr(0,20) + "..";
			}

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
    		//$('#onglet_' + this.model.get('id')).next('.onglet').css('background-color','#838787');
    		if((document.getElementById('frame_' + this.model.get('id')).style.display)=="block")
    		{
    			$('.content_frame').css('display','none');
    			if($('#frame_' + this.model.get('id')).next().length>0)
    			{
    				$('#frame_' + this.model.get('id')).next().css('display','block');
    				var idnew = $('#frame_' + this.model.get('id')).next().attr('id').substr(6,10);
    				$('#onglet_' + idnew).removeClass().addClass('onglet-active');
    			}
    			else
    			{
    				if($('#frame_' + this.model.get('id')).prev().length>0)
    				{
    					$('#frame_' + this.model.get('id')).prev().css('display','block');
    					var idnew = $('#frame_' + this.model.get('id')).prev().attr('id').substr(6,10);
    					$('#onglet_' + idnew).removeClass().addClass('onglet-active');
    				}
    			}
    			$('#frame_' + this.model.get('id')).remove();
    		}
    		else
    		{
    			$('#frame_' + this.model.get('id')).remove();
    		}
    		this.model.effacer();
   	},
   	
   	open: function()
   	{
   		$('.content_frame').css('display','none');
   		$('.onglet-active').removeClass().addClass('onglet');
   		$('#onglet_' + this.model.get('id')).removeClass().addClass('onglet-active');
   		
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
   
	   remove: function() {
	     	$(this.el).remove();
	   },
	});
	
	DocListView = Backbone.View.extend({
	
		el: $("#multidoc"),
		
		initialize: function() {
	      _.bindAll(this, 'newOnglet', 'render');
			Docs.bind('all', this.newOnglet);
	   },

   	newOnglet: function(_title, _icon, _id, _url) {
   		if($('#frame_' + _id).length<=0)
   		{
	   		doc = new Doc({title: _title, icon: _icon, id: _id, url: _url}); // on instantie notre modèle avec les bons paramètres !
	   		
		      var view = new DocView({model: doc}); // on crée la vue affiliée au modèle précédemment créé
		 		
		      this.$("#onglets").append(view.render().el);
		      view.open();
	    	}
	    	else
	    	{
	    		$('.content_frame').css('display','none');
   			$('.onglet-active').removeClass().addClass('onglet');
   			$('#frame_' + _id).css('display','block');
   			$('#onglet_' + _id).removeClass().addClass('onglet-active');
   		}
    	},
    	
    	setInterface: function(position) {
    		if(position=="left")
    		{
    			$('#onglets').removeClass().addClass('onglets_horizontal');
    			$('#doc_content').removeClass().addClass('doc_content_horizontal');
    		}
			else
			{
				if(position=="top")
				{
					$('#onglets').removeClass().addClass('onglets');
    				$('#doc_content').removeClass().addClass('doc_content');
    			}
			}
    	}

   });
   window.MultiDoc = new DocListView({model:Docs});
 

 // TESTS
 // URL TYPE : http://localhost/dynacase/?app=FDL&action=FDL_CARD&latest=Y&id=1017
 $('#idopen').keypress( function(event) {
   if (event.keyCode == 13) {
  		 var cpt = document.getElementById('idopen').value;  
 		MultiDoc.newOnglet("Document" + cpt, "doc2", cpt, "http://localhost/dynacase/?app=FDL&action=FDL_CARD&latest=Y&id=" + cpt);
 		$("#left").append("<br/>Nouvel onglet : <br/><b>MultiDoc.newOnglet('Document" + cpt + "', 'doc2', '" + cpt + "','http://...id=" + cpt + "');</b>")
	} 	
 	});
 	
 	$('.left').click( function() {
  		 MultiDoc.setInterface("left");
       $("#left").append("<br/><FONT color='red'>mode <b>LEFT</b> : </FONT> MultiDoc.setInterface('left');")
 	});
 	
  $('.top').click( function() {
		 MultiDoc.setInterface("top");
 		 $("#left").append("<br/><FONT color='red'>mode <b>TOP</b> : </FONT> MultiDoc.setInterface('top');")
 	});
 });