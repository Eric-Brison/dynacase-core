$(function(){

	//DECLARATION DE L'OBJET MULTIDOC !!!
	window.myMultiDoc = new MultiDocument();

	 //*************************************
	 //*************************************
	 myMultiDoc.setLocation("multidoc"); // On le place grâce à l'id du div que l'on veut
	 //*************************************
	 //*************************************
		
	var cpt=1010;
	 $(document.documentElement).keypress( function(event) 
	 {
		   if (event.keyCode == 13) 
		   {
		  		cpt++; 
		  		  
		  		//*************************************
		  		//*************************************
		 		myMultiDoc.newTab("Document" + cpt, "doc2", cpt, "http://localhost/dynacase/?app=FDL&action=FDL_CARD&latest=Y&id=" + cpt);
		 		//*************************************
		 		//*************************************
		 		
			} 	
	 });
	
	 $('.left').click( function() 
	 {
	 	
	 		 //*************************************
	 		 //*************************************
	  		 myMultiDoc.setInterfacePosition("left");
			 //*************************************
			 //*************************************
			 
	 });
	
	$('.top').click( function() {
	  
	  		 //*************************************
	  		 //*************************************
			 myMultiDoc.setInterfacePosition("top");
			 //*************************************
			 //*************************************
			 
	 });
 });