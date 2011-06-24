//*********************************************************
//WIDGET MultiDoc with Backbone using Underscore and Jquery  
//ANAKEEN (c) 2011
//*********************************************************

$(function(){
	//DECLARATION OF SOME EVENTS WITH JQUERY FOR THE DOC LIST

	//If click on the arrow to see more tabs
	$("#arrow_down").live('click', function(){
	   if($('#tabs_plus').css("display")=="none")
		{
	   	$('#tabs_plus').show(500);
	   }
	   else
	   {
	   	$('#tabs_plus').hide(500);
	   }
	});
	
	//If click on a tab on the ul tabs plus
	$('li','ul#tabs_plus').live('click', function(){
 		if($('#arrow_down').attr('class')=='vmode')
  		{
	  		var width_tabs = $("#tabs").outerHeight();
    		var width_tab = $(".tab-active").outerHeight();
   		var count_tabs = Math.floor((width_tabs)/width_tab);  	   
   		var count_li = document.getElementsByClassName("tabs_li").length;
   	}
   	else
   	{
		 	var width_tabs = $("#tabs").outerWidth();
		 	var width_options = $(".options").outerWidth();
		   var width_tab = $(".tab-active").outerWidth();
		   var count_tabs = Math.floor((width_tabs-width_options)/width_tab);  	   
		   var count_li = document.getElementsByClassName("tabs_li").length;
		}	   
   	var idtab = $(this).attr("id");
		
		$("ul#tabs > li:nth-child(" + (count_tabs) + " )").attr("class","tabs_plus_li");
		$("#tabs_plus").prepend($("ul#tabs > li:nth-child(" + (count_tabs) + " )"));
	   $("#" + idtab).attr("class","tabs_li");
	   $("#tabs").prepend($("#" + idtab).css("display","block"));
	   
	   $('.content_frame').css('display','none');
   	$('.tab-active').removeClass().addClass('tab');
   	$('.options').css('visibility','hidden');
   	$('#tab_' + idtab).removeClass().addClass('tab-active');
		$('#frame_' + idtab).css('display','block');
		$('#tabs_plus').hide(500);
	});
	
	//We have to manipulate tabs during the resize
	$(window).resize(function() {
			if($('#arrow_down').attr('class')=='vmode')
	  		{
	  			var wwindow = $(".multidoc").width();
		  	 	var wwidth = wwindow - 200;
		   	$(".doc_content_vertical").css('width', wwidth + 'px');
		  		var width_tabs = $("#tabs").outerHeight();
	    		var width_tab = $(".tab-active").outerHeight();
	    		var count_tabs = parseInt(Math.floor((width_tabs)/width_tab),10);
	   	}
	   	else
	   	{
			 	var width_tabs = $("#tabs").outerWidth();
				var width_options = $(".options").outerWidth(); 
				var width_tab = $(".tab").outerWidth();
		
				if(width_tab==0)
				{
					var width_content = parseInt($(".content").css("width").substr(0,3),10);
					var width_opt = parseInt($(".options").css("width").substr(0,2),10);
					width_tab=width_content + width_opt;
				}
				var count_tabs = parseInt(Math.floor((width_tabs-width_options)/width_tab),10);
			}
  		var nb_tabs = parseInt(document.getElementsByClassName("tabs_li").length,10);
  		var nb_tabs_plus = parseInt(document.getElementsByClassName("tabs_plus_li").length,10);
  		var itt = nb_tabs - count_tabs;
  		
  		if(itt>0)
  		{
  			for(var i=0;i<itt;i++)
  			{
  				$("ul#tabs > li:nth-child(" + (count_tabs+1) + ")").attr("class","tabs_plus_li");
  				$("#tabs_plus").prepend($("ul#tabs > li:nth-child(" + (count_tabs+1) + ")"));
  			}
  			
  			if(document.getElementsByClassName("tabs_plus_li").length > 0)
    		{
    			$('#arrow_down').css("display","block");
    			$('#tabs_plus').css("display","block");
    		}
  		}
  		else
  		{
  			itt_add = Math.abs(itt);
  			for(var i=0;i<itt_add;i++)
  			{
  				$("ul.tabs_plus > li:nth-child(1)").attr("class","tabs_li");
  				$("#tabs").append($("ul.tabs_plus > li:nth-child(1)"));
  			}
  			
  			if(document.getElementsByClassName("tabs_plus_li").length <= 0)
    		{
    			$('#arrow_down').css("display","none");
    			$('#tabs_plus').css("display","none");
    		}
  		}
  	});
  	
	//Two variables to stock templates
	var bodyTemplate="<div class='multidoc'><ul id='tabs' class='tabs'></ul><div id='arrow_down' title='View more...'></div><ul id='tabs_plus' class='tabs_plus'></ul><div id='doc_content' class='doc_content'></div></div>";
	var backboneTemplate = "<div class='tabcontent'><div class='tab' id='tab_<%= id %>' title='<%= content %>'><div class='icon_doc'><img class='img_icon' id='img_<%= id %>' src='ONEFAM/Images/loader5.gif' /></div><div class='content'>Loading...<%= content %></div></div><div class='options' id='options_<%= id %>'><div class='img_del' title='Delete'></div><div class='img_new' title='Extract'></div></div></div>";
	
	var systemcss = '<link href="?app=CORE&action=CORE_CSS&layout=FDL:multidoc_system.css" rel="stylesheet" type="text/css" />';
	var usercss = '<link id="usercss" href="?app=CORE&action=CORE_CSS&layout=FDL:multidoc_user.css" rel="stylesheet" type="text/css" />';

	$('head').append(systemcss);
	$('head').append(usercss);
	
	//Create the Backbone template for DOC TAB with Javascript	
	var Script = document.createElement('script');
	Script.type = "text/template";
	Script.id = 'doc-object';
   Script.innerHTML = backboneTemplate;
   
   //And append to the body element
   $("body").append(Script);
	
	
	//BACKBONE MODEL 
	//Definition of my model for one Document
 	Doc = Backbone.Model.extend({
 	
 		title: null,
 		
 		icon: null,
 		
 		id: null,
 		
 		url: null,
 			
 		erase: function() {
	      this.view.remove();
    	}
 	});

	//Definition of the collection
 	DocList = Backbone.Collection.extend({
 	
 		model: Doc,
 		
   });
   
 	window.Docs = new DocList;
   
//----MULTI DOC VIEW

	DocView = Backbone.View.extend({
		
		tagName: "li",
		
		template: _.template($('#doc-object').html()),
		
		events:
		{
			"click .img_new" : "newPage",
			"click .img_del" : "close",
			"click ul#tabs .tab" : "open",
			"mouseover .tabcontent" : "showOptions",
			"click .tabcontent" : "showOptions",
			"mouseleave .tabcontent" : "hideOptions",
			"contextmenu .tabcontent" : "goFirst"
		},
		
		initialize: function() 
		{
			_.bindAll(this, 'render', 'close', 'open');
	      this.model.bind('change', this.render);
      	this.model.view = this;
	   },
	   
		render: function() {			
			var title = this.model.get('title');

	      $(this.el).html(this.template({
	      	content: "",
	      	id: this.model.get('id')
	      }));
	      return this;
    	},
    	
    	//This fonction is called when the user click on the extract button to open a new window of the select tab
    	newPage: function()
    	{	
    		//We have to control the widht of tabs
    		if($('#arrow_down').attr('class')=='vmode')
	  		{
		  		var width_tabs = $("#tabs").outerHeight();
	    		var width_tab = $(".tab-active").outerHeight();
	   		var count_tabs = Math.floor((width_tabs)/width_tab);  	   
	   		var count_li = document.getElementsByClassName("tabs_li").length;
	   	}
	   	else
	   	{
			 	var width_tabs = $("#tabs").outerWidth();
			 	var width_options = $(".options").outerWidth();
			   var width_tab = $(".tab-active").outerWidth();
			   var count_tabs = Math.floor((width_tabs-width_options)/width_tab);  	   
			   var count_li = document.getElementsByClassName("tabs_li").length;
			}
   		
    		//We retrieve the last url of the iframe
    		var path = document.getElementById('frame_' + this.model.get('id')).contentDocument.location.href; 
    		extract = window.open(path,this.model.get('title'),'width=600,height=600');
    		extract.moveTo((screen.width)/2-300,(screen.height)/2-300);
    		extract.focus();
    		//We close the tab calling the close() function
    		this.close();
    		
			if(document.getElementsByClassName("tabs_li").length < count_tabs)
    		{
    			$("#tabs_plus").children("li:first").attr('class', 'tabs_li');
    			$("#tabs").append($("#tabs_plus").children("li:first"));
    		}
    		
    		if(document.getElementsByClassName("tabs_plus_li").length <= 0)
    		{
    			$('#arrow_down').css("display","none");
    			$('#tab_plus').css("display","none");
    		}
    	},
    	
    	close: function(id)
    	{
			id = this.model.get('id');
	    	if($('#arrow_down').attr('class')=='vmode')
	  		{
		  		var width_tabs = $("#tabs").outerHeight();
	    		var width_tab = $(".tab-active").outerHeight();
	   		var count_tabs = Math.floor((width_tabs)/width_tab);  	   
	   		var count_li = document.getElementsByClassName("tabs_li").length;
	   	}
	   	else
	   	{
			 	var width_tabs = $("#tabs").outerWidth();
			 	var width_options = $(".options").outerWidth();
			   var width_tab = $(".tab-active").outerWidth();
			   var count_tabs = Math.floor((width_tabs-width_options)/width_tab);  	   
			   var count_li = document.getElementsByClassName("tabs_li").length;
			}   
   	
    		if((document.getElementById('frame_' + id).style.display)=="block")
    		{
    			var idnext = $('#tab_' + id).parent().parent().next().attr('id');
    			var idprev = $('#tab_' + id).parent().parent().prev().attr('id');
    			$('.content_frame').css('display','none');
    			
    			if(idnext)
    			{
	    			$('#frame_' + idnext).css('display','block');
	    			$('#tab_' + idnext).removeClass().addClass('tab-active');   
    				$('#options_' + idnext).css('visibility','visible');
	    		}
				else
				{
					$('#frame_' + idprev).css('display','block');
	    			$('#tab_' + idprev).removeClass().addClass('tab-active');   
    				$('#options_' + idprev).css('visibility','visible');
				}
    		}
    		$('#frame_' + id).remove();
    		this.model.erase();
    		
    		if(document.getElementsByClassName("tabs_li").length < count_tabs)
    		{
    			$("#tabs_plus").children("li:first").attr('class', 'tabs_li');
    			$("#tabs").append($("#tabs_plus").children("li:first"));
    		}
    		
    		if(document.getElementsByClassName("tabs_plus_li").length <= 0)
    		{
    			$('#arrow_down').css("display","none");
    			$('#tabs_plus').hide(500);
    		}
   	},
   	
   	open: function(id)
   	{
   		id = this.model.get('id');
   		$('.content_frame').css('display','none');
   		$('.tab-active').removeClass().addClass('tab');
   		$('.options').css('visibility','hidden');
   		$('#tab_' + id).removeClass().addClass('tab-active');
   		
   		if($('#frame_' + id).length>0)
   		{
   			$('#frame_' + id).css('display','block');
   			var existe=true;
   		}
   		else
   		{ 			
	   		$('#doc_content').append("<iframe class='content_frame' id='frame_" + id + "' style='display:block;' frameborder='no' name='document' src='" + this.model.get('url') +"' width='100%' height='400px'></iframe>");	   		
	   		$('ul#tabs > li:nth-child(1)').attr('id', id);
   			$('ul#tabs > li:nth-child(1)').attr('class', 'tabs_li');
   			
   			//FRAME LOADING
   			$("#frame_" + id).load(function() {
   				//HREF
   				try
  				  	{
   					src = this.contentWindow.location.href;
				  	}
				  	catch (e)
				  	{
				  		src = "false";
				 	}
   				
	   			if(src!="false")
	   			{	
	   				//RELATIONS
	   				//With class relation
	   				var relations = this.contentWindow.document.getElementsByClassName('relation');
	   				//Whitout class relation, but link on other document
	   				var _relations = this.contentWindow.document.getElementsByTagName("a");
   				
						//CLASS RELATION
						if(relations.length>0)
						{
			   			for(var i=0;i<relations.length;i++)
			   			{
			   				var relhref = relations[i].getAttribute("href");
			   				var relid = relations[i].getAttribute("documentId");
			   				var hroot = window.location.href.substr(0,window.location.href.indexOf('?'));
		   					relations[i].setAttribute('onclick','window.parent.MultiDocument.newDoc(\''+ relid + '\',"' + hroot + relhref + '")');
		   					relations[i].removeAttribute('href');
		   				}
		   			}
		   			
		   			//DIRECT LINK ON OTHER DOCUMENT
		   			if(_relations.length>0)
		   			{
		   				for(var i=0;i<_relations.length;i++)
			   			{
			   				if(_relations[i].href!="" && _relations[i].class!="relation" && _relations[i].target=="")
			   				{
			   					var relhref = _relations[i].getAttribute("href");
		   						_relations[i].setAttribute('onclick','window.open("' + relhref + '","' + relhref + '", "width=600,height=600,scrollbars=yes").moveTo((screen.width)/2-300,(screen.height)/2-300)');
		   						_relations[i].removeAttribute('href');
		   					}
		   				}
		   			}
	   			}
	   			
					if(src != "" && src != "false")
					{
						var title = this.contentWindow.document.title;
						//TITLE
						if(title != "")
						{
							$("#tab_" + id).children(".content").html(title);
							$("#tab_" + id).attr("title",title);
						}
						else
						{
							$("#tab_" + id).children(".content").html("Error Document");
							$("#tab_" + id).attr("title","Error Document");
							$("#img_" + id).attr("src","ONEFAM/Images/erreur.png");
							var error = true;
						}
						
						//IF THE PAGE EXISTS
						if(error!=true)
						{
							//ID PAGE LOAD
							var metaid = this.contentWindow.document.getElementsByName("document-id");
							if(metaid.length>0)
							{
								var idpage = metaid[0].content;
							}
							
							//ICONE PAGE LOAD
							var metaicone = this.contentWindow.document.getElementsByName("document-icon");
							if(metaicone.length>0)
							{
								var icone = metaicone[0].content;
								$("#img_" + id).attr("src","../../dynacase/" + icone); /*MODIF*/
							}
							
							//IF DURING LOAD this frame already exist 	
							if($("#" + idpage).length<=0 && idpage!=null && idpage!="0")
							{
								var change = "true";
								$("#" + id).attr('id',idpage);
								$("#tab_" + id).attr('id','tab_' + idpage);
								$("#img_" + id).attr('id','img_' + idpage);
								$("#frame_" + id).attr('id','frame_' + idpage);
								$("#options_" + id).attr('id','options_' + idpage);
							}						
						}
					}	
					else
					{
						$("#tab_" + id).children(".content").html("Extern URL");
						$("#tab_" + id).attr("title","Extern URL");
						$("#img_" + id).attr("src","ONEFAM/Images/extern.png");
					}
					
					if(change=="true")
		 			{
		 				window.parent.doc.set({id:idpage});
		 				var title = this.contentWindow.document.title;
		 				$("#tab_" + idpage).children(".content").html(title);
						$("#tab_" + idpage).attr("title",title);
						
						var metaicone = this.contentWindow.document.getElementsByName("document-icon");
						if(metaicone.length>0)
						{
							var icone = metaicone[0].content;
							$("#img_" + idpage).attr("src","../../dynacase/" + icone);
						}
						$("#tab_" + idpage).attr("class","tab-active");
		 			}
   			});   			
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
	   
	   goFirst: function() {
		   if($("#" + this.model.get('id')).attr("class") == 'tabs_li')
		   {
		   	$('.options').css('visibility','hidden');
	   		$('#options_' + this.model.get('id')).css('visibility','visible');
		   	$("#"+this.model.get('id')).prependTo($("#tabs"));
		   	return false;
		   }
	   }
	   
	});
	
	MultiDocument = Backbone.View.extend({
		
		initialize: function() {
	      _.bindAll(this, 'newDoc', 'render');
			Docs.bind('all', this.newDoc);
	   },

   	newDoc: function(_id, _url) {
	   	//TABS GESTION
	  		if($('#arrow_down').attr('class')=='vmode')
	  		{
		  		var width_tabs = $("#tabs").outerHeight();
	    		var width_tab = $(".tab-active").outerHeight();
	   		var count_tabs = Math.floor((width_tabs)/width_tab);  	   
	   		var count_li = document.getElementsByClassName("tabs_li").length;
	   	}
	   	else
	   	{
			 	var width_tabs = $("#tabs").outerWidth();
			 	var width_options = $(".options").outerWidth();
			   var width_tab = $(".tab-active").outerWidth();
			   var count_tabs = Math.floor((width_tabs-width_options)/width_tab);  	   
			   var count_li = document.getElementsByClassName("tabs_li").length;
			}
			
   		if($('#frame_' + _id).length<=0)
   		{
	   		doc = new Doc({id: _id, url: _url});
	   		
		      var view = new DocView({model: doc});
				
   	   	if(count_li<count_tabs)
   	   	{
		      	this.$("#tabs").prepend(view.render().el);
		     		view.open();
		     	}
		     	else
		     	{
		     		$('#arrow_down').css("display","block");
		     		$("ul#tabs > li:nth-child(" + count_tabs + " )").attr('class', 'tabs_plus_li');
		     		$("#tabs_plus").prepend($("li:nth-child(" + count_tabs + " )"));
		     		this.$("#tabs").prepend(view.render().el);
		     		view.open();
		     	}
	    	}
	    	else
	    	{
   			$('.options').css('visibility','hidden');
   			$('#options_' + _id).css('visibility','visible');
	    		$('.content_frame').css('display','none');
	   		$('.tab-active').removeClass().addClass('tab');
	   		$('#frame_' + _id).css('display','block');
	   		$('#tab_' + _id).removeClass().addClass('tab-active');
	   		$("#" + _id).attr("class","tabs_li");
	   		$("#tabs").prepend($("#" + _id).css("display","block"));
	   		$("ul#tabs > li:nth-child(" + (count_tabs+1) + " )").attr("class","tabs_plus_li");
	   		$("#tabs_plus").prepend($("li:nth-child(" + (count_tabs+1) + " )"));
   		}
    	},
    	
    	setAnchor: function(id) {
    			$(id).append(bodyTemplate);
    			this.el = $(".multidoc");
    	},
    	
    	setCSS: function(css) {
    		$("#usercss").attr("href",css);
    	},
    	
    	removeDoc: function(id) {
    		if($("#frame_"+id).length>0)
	    	{
			   if($('#arrow_down').attr('class')=='vmode')
		  		{
			  		var width_tabs = $("#tabs").outerHeight();
		    		var width_tab = $(".tab-active").outerHeight();
		   		var count_tabs = Math.floor((width_tabs)/width_tab);  	   
		   		var count_li = document.getElementsByClassName("tabs_li").length;
		   	}
		   	else
		   	{
				 	var width_tabs = $("#tabs").outerWidth();
				 	var width_options = $(".options").outerWidth();
				   var width_tab = $(".tab-active").outerWidth();
				   var count_tabs = Math.floor((width_tabs-width_options)/width_tab);  	   
				   var count_li = document.getElementsByClassName("tabs_li").length;
				}  
	
	    		if((document.getElementById('frame_' + id).style.display)=="block")
	    		{
	    			var idnext = $('#tab_' + id).parent().parent().next().attr('id');
	    			var idprev = $('#tab_' + id).parent().parent().prev().attr('id');
	    			$('.content_frame').css('display','none');
	    			
	    			if(idnext)
	    			{
		    			$('#frame_' + idnext).css('display','block');
		    			$('#tab_' + idnext).removeClass().addClass('tab-active');   
	    				$('#options_' + idnext).css('visibility','visible');
		    		}
					else
					{
						$('#frame_' + idprev).css('display','block');
		    			$('#tab_' + idprev).removeClass().addClass('tab-active');   
	    				$('#options_' + idprev).css('visibility','visible');
					}
	    		}
	    		$('#frame_' + id).remove();
	    		$('#' + id).remove();
	    		
	    		if(document.getElementsByClassName("tabs_li").length < count_tabs)
	    		{
	    			$("#tabs_plus").children("li:first").attr('class', 'tabs_li');
	    			$("#tabs").append($("#tabs_plus").children("li:first"));
	    		}
	    		
	    		if(document.getElementsByClassName("tabs_plus_li").length <= 0)
	    		{
	    			$('#arrow_down').css("display","none");
	    			$('#tabs_plus').hide(500);
	    		}
	    	}
	   	else
    		{
    			return false;
    		}
    	},
    	
    	showInANewWindow: function(id) {
	    	if($("#frame_"+id).length>0)
	    	{
	    	   //We have to control the widht of tabs
		    	if($('#arrow_down').attr('class')=='vmode')
		  		{
			  		var width_tabs = $("#tabs").outerHeight();
		    		var width_tab = $(".tab-active").outerHeight();
		   		var count_tabs = Math.floor((width_tabs)/width_tab);  	   
		   		var count_li = document.getElementsByClassName("tabs_li").length;
		   	}
		   	else
		   	{
				 	var width_tabs = $("#tabs").outerWidth();
				 	var width_options = $(".options").outerWidth();
				   var width_tab = $(".tab-active").outerWidth();
				   var count_tabs = Math.floor((width_tabs-width_options)/width_tab);  	   
				   var count_li = document.getElementsByClassName("tabs_li").length;
				}
	   		
	    		//We retrieve the last url of the iframe
	    		var path = document.getElementById('frame_' + id).contentDocument.location.href; 
	    		extract = window.open(path,"test",'width=600,height=600');
	    		extract.moveTo((screen.width)/2-300,(screen.height)/2-300);
	    		extract.focus();
	    		//We close the tab calling the close() function
	    		this.removeDoc(id);
	    		
				if(document.getElementsByClassName("tabs_li").length < count_tabs)
	    		{
	    			$("#tabs_plus").children("li:first").attr('class', 'tabs_li');
	    			$("#tabs").append($("#tabs_plus").children("li:first"));
	    		}
	    		
	    		if(document.getElementsByClassName("tabs_plus_li").length <= 0)
	    		{
	    			$('#arrow_down').css("display","none");
	    			$('#tab_plus').css("display","none");
	    		}
	    	}
	    	else
    		{
    			return false;
    		}
    	},
    	
    	setDocOnTop: function(id) {
    		if($("#frame_"+id).length>0)
	    	{
    			$("#"+id).prependTo($("#tabs"));
    		}
    		else
    		{
    			return false;
    		}
    	},
    	
    	resetMultiDoc: function() {
    		$("#doc_content").html("");
    		$("#tabs").html("");
    		$("#tabs_plus").html("");
    		$('#arrow_down').css("display","none");
	    	$('#tab_plus').css("display","none");
    	},
    	
    	setDisplayMode: function(mode) {    		
	    	if(mode == 'V')
	    	{
	    		var wwindow = $(".multidoc").width();
	    		var wwidth = wwindow - 200;
	    		$("#arrow_down").attr('class','vmode');
	    		$("#tabs").removeClass().addClass('tabs_vertical');
	    		$("#doc_content").removeClass().addClass('doc_content_vertical');
	    		$(".doc_content_vertical").css('width', wwidth + 'px');
	    		$("#arrow_down").css('float','left');
	    		$("#arrow_down").css('margin-left','-120px');
				$("#tabs_plus").removeClass().addClass('tabs_plus_vertical');
				
				//Readjustement
				var count_li = document.getElementsByClassName("tabs_li").length;
				var count_li_plus = document.getElementsByClassName("tabs_plus_li").length;
									
				if(count_li>0)
				{
					var width_tabs = $("#tabs").outerHeight();
					var width_tab = $(".tab-active").outerHeight();
					if(!width_tab)
					{
						width_tab = $(".tab").outerHeight();
					}
					var count_tabs = Math.floor(width_tabs/width_tab);  	  

					var transf = count_li - count_tabs;
					
					if(count_tabs<count_li)
					{
						for(var i=0;i<transf;i++)
						{
							$("ul#tabs > li:last-child").attr('class', 'tabs_plus_li');
		     				$("#tabs_plus").prepend($("ul#tabs > li:last-child"));
		     			}
		     			$('#arrow_down').css("display","block");
				    	$('#tab_plus').css("display","block");
					}
					else
					{
						if( (count_tabs>count_li) && count_li_plus>0 )
						{
							for(var i=0;i<Math.abs(transf);i++)
							{
								$("ul#tabs_plus > li:nth-child(1)").attr('class', 'tabs_li');
			     				$("#tabs").append($("ul#tabs_plus > li:nth-child(1)"));
			     			}			     			
						}
					}	
				}	
				
				if(document.getElementsByClassName("tabs_plus_li").length <= 0)
				{
					$('#arrow_down').css("display","none");
				   $('#tab_plus').css("display","none");
		 		}	
    		}
	    	else
	    	{
		    	if(mode == 'H')
		    	{
		    		$("#arrow_down").attr('class','hmode');
		    		$("#tabs").removeClass().addClass('tabs');
	    			$("#doc_content").removeClass().addClass('doc_content');
	    			$(".doc_content").css('width', '100%');
	    			$("#arrow_down").css('float','right');
	    			$("#arrow_down").css('margin-left','0px');
	    			$("#tabs_plus").removeClass().addClass('tabs_plus');
				
					//Readjustement
					var count_li = document.getElementsByClassName("tabs_li").length;
					var count_li_plus = document.getElementsByClassName("tabs_plus_li").length;
										
					if(count_li>0)
					{
						var width_tabs = $("#tabs").outerWidth();
						var width_tab = $(".tab-active").outerWidth();
						if(!width_tab)
						{
							width_tab = $(".tab").outerHeight();
						}
						var width_options = $(".options").outerWidth();
						var count_tabs = Math.floor((width_tabs-width_options)/width_tab);  	  
						
						var transf = count_li - count_tabs;
						
						if(count_tabs<count_li)
						{
							for(var i=0;i<transf;i++)
							{
								$("ul#tabs > li:last-child").attr('class', 'tabs_plus_li');
			     				$("#tabs_plus").prepend($("ul#tabs > li:last-child"));
			     			}
						}
						else
						{
							if( (count_tabs>count_li) && count_li_plus>0 )
							{
								for(var i=0;i<Math.abs(transf);i++)
								{
									$("ul#tabs_plus > li:nth-child(1)").attr('class', 'tabs_li');
				     				$("#tabs").append($("ul#tabs_plus > li:nth-child(1)"));
				     			}
	
					    		if(document.getElementsByClassName("tabs_plus_li").length <= 0)
					    		{
					    			$('#arrow_down').css("display","none");
					    			$('#tab_plus').css("display","none");
					    		}
							}
						}
					}
				}
	    		else
	    		{
	    			return false;
	    		}
	    	}
   	}
   });	
});
