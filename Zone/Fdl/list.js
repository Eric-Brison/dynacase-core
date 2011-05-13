$(function(){

	//If click on the arrow
	$("#arrow_down").click(function(){
	   if($('#tabs_plus').css("display")=="none")
		{
	   	$('#tabs_plus').css("display","block");
	   }
	   else
	   {
	   	$('#tabs_plus').css("display","none");
	   }
	});
	
	//If your mouse leave the list 
	$("#tabs_plus").mouseleave(function(){
	   	$('#tabs_plus').css("display","none");
	});
	
	//If you click on one tab on the list
	$('li','ul#tabs_plus').live('click', function(){
		var width_tabs = $("#tabs").width();
   	var count_tabs = Math.floor((width_tabs-20)/160);   	   
   	var idtab = $(this).attr("id");
		
		$("ul.tabs > li:nth-child(" + (count_tabs) + " )").attr("class","tabs_plus_li");
		$("#tabs_plus").prepend($("ul.tabs > li:nth-child(" + (count_tabs) + " )"));

	   $("#" + idtab).attr("class","tabs_li");
	   $("#tabs").prepend($("#" + idtab).css("display","block"));
	   
	   $('.content_frame').css('display','none');
   	$('.tab-active').removeClass().addClass('tab');
   	$('.options').css('visibility','hidden');
   	$('#tab_' + idtab).removeClass().addClass('tab-active');
		$("#doc_content").prepend($('#frame_' + idtab).css('display','block'));
		$('#tabs_plus').css("display","none");
	});

	//We have to manipulate tabs during the resize
	$(window).resize(function() {
		var width_tabs = $("#tabs").width();
  		var count_tabs = parseInt(Math.floor((width_tabs-20)/160),10);
  		var nb_tabs = parseInt(document.getElementsByClassName("tabs_li").length,10);
  		var nb_tabs_plus = parseInt(document.getElementsByClassName("tabs_plus_li").length,10);
  		var itt = nb_tabs - count_tabs;
  		if(itt>0)
  		{
  			for(var i=0;i<itt;i++)
  			{
  				$("ul.tabs > li:nth-child(" + (count_tabs+1) + ")").attr("class","tabs_plus_li");
  				$("#tabs_plus").prepend($("ul.tabs > li:nth-child(" + (count_tabs+1) + ")"));
  			}
  			
  			if(document.getElementsByClassName("tabs_plus_li").length > 0)
    		{
    			$('#arrow_down').css("display","block");
    			$('#tab_plus').css("display","block");
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
    			$('#tab_plus').css("display","none");
    		}
  		}
  	});
});

//TEST get id and title with the iframe only
  	function frameloaded(id) {
				src = document.getElementById("frame_" + id).src
				if (src == "")
				{
					console.log("iframe was loaded with no content");
				}
				else
				{
					var title = document.getElementById("frame_" + id).contentWindow.document.title;
					var idpage = (document.getElementById("frame_" + id).src).substr(-4,4);
					console.log(title);
					console.log(idpage);
				}
			}

