/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * config : documentId, attributeId, index
 */
var widgetFile=function (config) {
	for (var name in config) {
		this[name]=config[name];
	}
	this['rootUrl']=window.location.href.substr(0,window.location.href.lastIndexOf('/')+1);
}

widgetFile.prototype = {
	toString : function() {
		return 'widgetFile';
 	},
 	show : function (config) {
 		
 		for (var name in config) {
 			this[name]=config[name];
 		}
 		//?app=FDL&action=EXPORTFILE&inline=yes&cache=no&vid=1366&docid=3602&attrid=sfi_file&index=-1&width=200&type=png&page=1
 		var hpagerbegin='';
			var hpagerend='';
			if (this.showPager) {
				hpagerbegin='<table width="100%"><thead><tr><td>';

				hpagerbegin+='<img class="mime\" needresize=1  src="Images/'+this.mimeIcon+'"> ';
				hpagerbegin+='<a href="'+this.fileLink+'">'+this.fileTitle+'</a>';
				if (this.type=='png') {
					hpagerbegin+='<span style="margin-left:30px"><img alt="Prev" style="cursor:pointer" src="Images/prev16.png"></span><span><img alt="Next"  style="cursor:pointer" src="Images/next16.png"></span>';
					hpagerbegin+='<input type="text" size="2" >';
				}
				hpagerbegin+='</td></tr></thead><tbody><tr><td>';
				hpagerend='</td></tr></tbody></table>';
			}
 		if (this.type=='png') {
 			var url=this.rootUrl+'?app=FDL&action=EXPORTFILE&inline=yes&cache=no&type=png';
 			if (this.width) url+='&width='+parseInt(this.width);
 			if (this.documentId) url+='&docid='+(this.documentId);
 			if (this.attributeId) url+='&attrid='+(this.attributeId);
 			if (this.page) url+='&page='+parseInt(this.page);
 			if (this.index) url+='&index='+parseInt(this.index);
 			
 			var himg='<div class="fileimage" style="';
 			var divstyle='';
 			if (this.height) divstyle+=' height:'+this.height+';';
 			if (this.width) divstyle+=' width:'+this.width+';';
 			//console.log("widget",this);
 			 himg+=divstyle+'"><img src="'+url+'"';
 			//if (this.height) himg+=' style="height:'+this.height+'"';
 			if (this.width) himg+=' style="width:'+this.width+'px"';
 			himg+='></div>';
 			if (this.target) {
 				this.target.innerHTML=hpagerbegin+himg+hpagerend; 			
 				if (this.showPager) {
 					var tds=this.target.getElementsByTagName('span');
 					if (tds.length > 1) {
 						tds[0].wf=this;
 						tds[1].wf=this;
 						addEvent(tds[0],"click",function (event) {this.wf.prevPage();});
 						addEvent(tds[1],"click",function (event) {this.wf.nextPage();});
 					}
 					var tds=this.target.getElementsByTagName('input');
 					if (tds.length > 0) {
 						tds[0].wf=this;
 						tds[0].value=this.page+1;
 						addEvent(tds[0],"keyup",function (event) {this.wf.showPage(event, this);});
 						
 					}
 				}

 			}
 			//console.log(himg);
 		} else if (this.type='embed') {
 			var hembed='<iframe src="'+this.fileLink+'&inline=yes" style="';
 			hembed+='width:100%;';
 			if (this.height) hembed+=' height:'+this.height+';';
 			hembed+='" src="'+this.fileLink+'&inline=yes">';
 			if (this.target) {
 				this.target.innerHTML=hpagerbegin+hembed+hpagerend; 		
 			}
 			console.log('embed',hembed);
 		}
 	},
 	nextPage : function () {
 		this.page++;
 		this.show();
 	},
 	prevPage : function () {
 		if (this.page > 0) {
 		  this.page--;
 		  this.show();
 		}
 	},
 	showPage : function (event,io) {
 		
 		if (io.value) {
 		  this.page=io.value - 1;
 		  if (this.page < 0 ) this.page=0;
 		  this.show();
 		}
 	}
 	
}
function addEvent(o,e,f){
	if (o.addEventListener){ o.addEventListener(e,f,true); return true; }
	else if (o.attachEvent){ return o.attachEvent("on"+e,f); }
	else { return false; }
}
