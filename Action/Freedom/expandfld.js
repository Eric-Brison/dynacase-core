
/**
 * @author Anakeen
 */


  // --------------------------------------------------
function addbranch(fldtopnode, BeginnEntries,mode) {
  // --------------------------------------------------


  
    // --------------------------------------------------
    // first part : compose the logical tree, set html object in current frame
    // --------------------------------------------------
    // find the leftside

  var images = fldtopnode.navObj.getElementsByTagName('img');   
  var leftSide="";
  var ffolder = window.parent;//window.open('','ffolder','');


  ffolder.doc=document;
  ffolder.nEntries = BeginnEntries;
  if (mode=='navigator') {
   for (i=0 ; i < images.length-2; i++)    { 
      leftSide  += '<img src="'+images[i].src+'" width=16 height=22>';
   }
   if (fldtopnode.isLastNode) {
     leftSide  += '<img src="FREEDOM/Images/ftv2blank.gif" width=16 height=22>';
   } else {
     leftSide  += '<img src="FREEDOM/Images/ftv2vertline.gif" width=16 height=22>';
   }
  // init the logical tree in ffolder :: add a branch in fldtopnode node

    var level=1;
    for (i=0 ; i < fldtopnode.nChildren; i++)    { 
      if (i == fldtopnode.nChildren-1) 
        fldtopnode.children[i].initialize(level, 1, leftSide) 
      else 
        fldtopnode.children[i].initialize(level, 0, leftSide) 
    } 
  } else {
    ffolder.doc=ffolder.document;
    ffolder.deleteallfolder();
    ffolder.doc=document;
     leftSide  += '<img src="FREEDOM/Images/ftv2blank.gif" width=16 height=22>';

     f2=fldtopnode.father;
     while (f2 != 0) {
        f2.drawFather();
       f2=f2.father;
     }

     fldtopnode.initialize(0, 1, "");

    
  }   



  // restore parameters
  ffolder.doc=ffolder.document;

  // --------------------------------------------------
  // second part : copy html object in the initial frame
  // --------------------------------------------------
	
  copydivfolder(ffolder, BeginnEntries);
  ffolder.restoreImg();

  fldtopnode.isLoaded=true;
  fldtopnode.setState(false);
  fldtopnode.setState(true);
   ffolder.resetOnNode(fldtopnode.id);
}


function copydivfolder(fto, BeginnEntries) {  
  var divs = document.getElementsByTagName("div");
  var ifld=1;
  var ndiv=divs.length;
  var ne;
  var h;
  if (ndiv > 1) {


    var divtoinsert = null;
    var divtoinserth = fto.document.getElementById('thefolderh');
    var flddiv = fto.document.getElementById('folder'+fto.fldidtoexpand);
    
    if (flddiv)
      divtoinsert=flddiv.nextSibling;


    //   alert('nch1:'+fto.indexOfEntries[fto.fldidtoexpand].nChildren);
    for (var i=1; i < ndiv; i++)  {
      
      //   alert(fto.fldidtoexpand);
      
      h=  fto.document.createElement("div");
      h.innerHTML= divs[i].innerHTML;
      h.id= divs[i].id;
      h.className= divs[i].className;
      divs[i].style.backgroundColor='yellow';

      if (h.className=='fldh')  {
	divtoinserth.appendChild(h); 
      } else {
	fto.document.getElementById('bodyid').insertBefore(h,divtoinsert); 
      }
//       if (divs[i].className=='folder') {      
// 	ne = BeginnEntries+ifld-1;


// 	if (fto.indexOfEntries && fto.indexOfEntries[ne]) {
// 	  fto.indexOfEntries[ne].navObj=h;  
// 	  fto.indexOfEntries[ne].iconImg=fto.document.getElementById('folderIcon'+ne);  
// 	  fto.indexOfEntries[ne].nodeImg=fto.document.getElementById('nodeIcon'+ne);  
// 	  ifld++;
// 	}
//       }            
    }           
  }  
}
// --------------------------------------------------
function copypopup( tdivpopup, BeginnEntries ) {

  var ffolder = window.parent;
  for (var i=1; i< tdivpopup['popfld'].length; i++) {
      ffolder.tdiv['popfld'][i-1+BeginnEntries]=tdivpopup['popfld'][i];
      ffolder.tdiv['poppaste'][i-1+BeginnEntries]=tdivpopup['poppaste'][i];
    }
}

     
     
