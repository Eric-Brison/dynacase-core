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
};

widgetFile.prototype = {
		showPager:false,
		mimeIcon:'',
		fileLink:'',
		fileTitle:'',
		pages:0,
		waiting:false,
		height:'300px',
		width:'300px',
		target:null,
		rootUrl:'',
		documentId:0,
		attributeId:'',
		vid:0,
		index:-1,
		interval:null,
		resizeEnabled:false,
		scrollBarWidth:0,
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
				if (this.type=='png' && this.pages > 1) {
					hpagerbegin+='<div style="float:right">';
					hpagerbegin+='<span style="margin-left:30px"><img alt="Prev" style="cursor:pointer" src="Images/prev16.png"></span><span><img alt="Next"  style="cursor:pointer" src="Images/next16.png"></span>';
					hpagerbegin+='<input type="text" size="2" >';
					hpagerbegin+='/'+this.pages;
					hpagerbegin+='</div>';
				}
				hpagerbegin+='</td></tr></thead><tbody><tr><td>';
				hpagerend='</td></tr></tbody></table>';
			}
			if (this.error) {
				var herr='<div';
				if (this.height) herr+=' style="height:'+this.height+';"';
				herr+='><p>'+this.error+'</p>';
 				herr+='<img src="Images/loading.gif">';
				herr+='</div>';
 	 			if (this.target) {
 	 				this.target.innerHTML=herr;
 	 				
 	 			}
			} else if (this.waiting) {
				var hwaiting='<div';

 				if (this.height) hwaiting+=' style="height:'+this.height+';"';
				hwaiting+='><p>In progress</p>';
 				hwaiting+='<img src="Images/loading.gif">';
				hwaiting+='</div>';
 	 			if (this.target) {
 	 				this.target.innerHTML=hwaiting;
 	 				var wft=this;
 	 				this.interval=setInterval(function() {wft.verifyWaiting();},2000);
 	 			}
			} else if (this.type=='png') {
				var url='';
				if (this.width=='100%') {
					if (this.target) {
						
							
							this.target.innerHTML='<p>Loading</p>';
							this.absoluteWidth=this.getInnerWidth(this.target);
							if (! this.resizeEnabled) {
								var wftr=this;
								addEvent(window,"resize",function() {wftr.resize();});
							}
							this.absoluteWidth-=this.scrollBarWidth;
						
					}
				} else {
					this.absoluteWidth=parseInt(this.width);
				}
				url=this.rootUrl+'?app=FDL&action=EXPORTFILE&inline=yes&cache=no&type=png';
				if (this.waiting) url=this.rootUrl+'Images/loading.gif';
				if (this.absoluteWidth) url+='&width='+parseInt(this.absoluteWidth);
				
				if (this.documentId) url+='&docid='+(this.documentId);
				if (this.attributeId) url+='&attrid='+(this.attributeId);
				if (this.page) url+='&page='+parseInt(this.page);
				if (this.index) url+='&index='+parseInt(this.index);

				var himg='<div class="fileimage" style="';
				var divstyle='';
				if (this.height) divstyle+=' height:'+this.height+';';
				if (this.width) divstyle+=' width:'+this.width+';';
				himg+=divstyle+'"><img class="imgfile" src="'+url+'"';
				//if (this.height) himg+=' style="height:'+this.height+'"';
				if (this.absoluteWidth) himg+=' style="width:'+this.absoluteWidth+'px"';
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
						tds=this.target.getElementsByTagName('input');
						if (tds.length > 0) {
							tds[0].wf=this;
							tds[0].value=this.page+1;
							addEvent(tds[0],"keyup",function (event) {this.wf.showPage(event, this);});
						}
						/*
						tds=this.target.getElementsByTagName('img');
						if (tds.length > 0) {
							tds[0].wf=this;
							tds[0].value=this.page+1;
							addEvent(tds[0],"load",function (event) {this.wf.resize();});
						}*/
					}
					this.resize();

				}
 		} else if (this.type='embed') {
 			var hembed='<iframe src="'+this.fileLink+'&inline=yes" style="';
 			hembed+='border:none;width:100%;';
 			if (this.height) hembed+=' height:'+this.height+';';
 			hembed+='" src="'+this.fileLink+'&inline=yes">';
 			if (this.target) {
 				this.target.innerHTML=hpagerbegin+hembed+hpagerend; 		
 			}
 		}
 	},
 	nextPage : function () {
 		if (this.page < (this.pages -1)) {
 		this.page++;
 		this.show();
 		}
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
 		  if (this.page >= this.pages ) this.page=this.pages-1;
 		  this.show();
 		}
 	},
 	resize : function (event) {
 		if (this.type=='png' && this.width=='100%' && this.target) {
 			var imgs=this.target.getElementsByTagName('img');
 			var imgfile=null;
 			if (imgs.length > 1) {
 				for (var i=0;i<imgs.length;i++) {
 					if (imgs[i].className=='imgfile') imgfile=imgs[i];
 				}
 			}
 			if (imgfile ) {
 				imgfile.style.width='10px';
 				//imgfile.style.border='solid red 10px';
 				//this.target.innerHTML='<p>Resizing</p>';
 				this.absoluteWidth=this.getInnerWidth(this.target);
 				imgfile.style.width=this.absoluteWidth+'px';
 				/*
 				this.absoluteWidth-=this.scrollBarWidth;
 				//if (this.absoluteWidth > 30 ) this.absoluteWidth-=25; // scrollbar
 				//this.target.innerHTML=saveimg;
 				imgfile.style.width=this.absoluteWidth+'px';
 				console.log("new width",this.absoluteWidth,imgfile);
 				if (document.documentElement) {
 					var inw=document.documentElement.offsetWidth;
 					var ouw=document.documentElement.scrollWidth;
 					console.log("resize width",inw,ouw);
 					if (ouw > inw) {
 						this.scrollBarWidth=(ouw-inw);
 						this.absoluteWidth-=(ouw-inw);
 		 				imgfile.style.width=this.absoluteWidth+'px';
 	 					console.log("rescale width",inw,ouw);
 					} else {
 						this.scrollBarWidth=0;
 					}
 				}
 				*/
 			}
 		}
 	},
 	getInnerWidth : function (o) {
 		if (o) {
 			var iw=0;
 			if (o.clientWidth) iw=o.clientWidth;
 			else if (o.offsetWidth) iw=o.offsetWidth;
 			else if (o.clip && o.clip.width) iw=o.clip.width;
 			if (iw > 0) {
 				if (iw > 100) {
 					iw-=50;
 				}
 			}
 			return iw;
 		}
 		return 0;
 	},
 	verifyWaiting : function (event) {
 		if (XHT_FILES) {
 			 // from verifycomputedfiles.js
 			var status=parseInt(XHT_FILES.files[this.vid]);
 			if (status > 1) {
 	 			
 			} else {
 			  if (this.interval) clearInterval(this.interval);
 			  if (status==1) {
 				  this.waiting=false;
 				  this.show();
 			  } else {
 				  this.error='no rendering';
 				  this.waiting=false;
 				  this.show();
 			  }
 			}
 		}
 		
 	}
 	
};

