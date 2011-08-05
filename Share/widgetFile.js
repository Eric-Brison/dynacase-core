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
		pdfLink:'',
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
		staticHeight:0,
		fullScreenActivated:false,
		fullType:'width',
	toString : function() {
		return 'widgetFile';
 	},
 	canViewPdf : function() {
 		var can=(window.PluginDetect.getVersion('adobereader') ||
			(window.PluginDetect.isMinVersion('PDFReader') == 0));
 		if (! can) {
 			// special for safari on macosx
 			if ((navigator.platform.indexOf('Mac') != -1) && (navigator.vendor.indexOf('Apple') != -1) ) can=true;
 		}
 		return can;
 	},
 	show : function (config) {
 		for (var name in config) {
 			this[name]=config[name];
 		}
 		if (this.pdfLink && this.type=='embed' && (!this.canViewPdf())) {
 			this.type='png';
 		}

 		if (config && config.height) {
 			if (config.height != '100%') this.staticHeight=parseInt(config.height);
 		}
 		//?app=FDL&action=EXPORTFILE&inline=yes&cache=no&vid=1366&docid=3602&attrid=sfi_file&index=-1&width=200&type=png&page=1
 		var hpagerbegin='';
			var hpagerend='';
			if (this.showPager) {
				hpagerbegin='<table width="100%"><thead><tr><td>';

				hpagerbegin+='<img class="mime\" needresize=1  src="Images/'+this.mimeIcon+'"> ';
				hpagerbegin+='<a href="'+this.fileLink+'" title="Download file">'+this.fileTitle+'</a>';
				if (this.type=='png' && this.pages > 1) {
					hpagerbegin+='<div style="float:right">';
					hpagerbegin+='<span style="margin-left:30px"><img alt="Prev" style="cursor:pointer" src="Images/prev16.png"></span><span><img alt="Next"  style="cursor:pointer" src="Images/next16.png"></span>';
					hpagerbegin+='<input type="text" size="2" >';
					hpagerbegin+='/'+this.pages;
					hpagerbegin+='</div>';
				}
				if (this.type=='png') {
			 	    hpagerbegin+=' <a class="fullheight" title="Plain Heigth">&nbsp;&nbsp;</a>';
				    hpagerbegin+=' <a class="fullwidth" title="Plain Width">&nbsp;&nbsp;</a>';
				}
				hpagerbegin+=' <a class="fullscreen" title="fullscreen">&nbsp;&nbsp;</a>';
				hpagerbegin+='</td></tr></thead><tbody><tr><td>';
				hpagerend='</td></tr></tbody></table>';
			}
			if (this.error) {
				var herr='<div style="';
				if (this.height) herr+=' height:'+this.height+';';
				herr+='text-align:center;';
				herr+='"><p>'+this.error+'</p>';
 				//herr+='<img src="Images/loading.gif">';
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
						
							
							//this.target.innerHTML='<p>Loading</p>';
							this.absoluteWidth=this.getInnerWidth(this.target);
							if (! this.resizeEnabled) {
								var wftr=this;
								this.resizeEnabled=true; // one shot 
								addEvent(window,"resize",function() {wftr.onWindowResize();});
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

				var imgfile=this.getImgFile();
				if (imgfile) {
					var tdi=this.target.getElementsByTagName('input');
					if (tdi.length > 0) {					
						tdi[0].value=this.page+1;
					}
					// already set : just update url
					imgfile.style.width=this.width+'px';
					imgfile.style.height=''; 
					imgfile.src=url;
					return;
				}
				var himg='<div class="fileimage" style="';
				var divstyle='';
				if (this.height) divstyle+=' height:'+this.height+';';
				if (this.width) divstyle+=' width:'+this.width+';';
				if (this.staticHeight) divstyle+=' overflow-y:scroll;';
				divstyle+='text-align:center;';
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
							addEvent(tds[0],"click",function (event) {if (! this.wf) this.wf=window.event.srcElement.wf;this.wf.prevPage();});
							addEvent(tds[1],"click",function (event) {if (! this.wf) this.wf=window.event.srcElement.wf;this.wf.nextPage();});
						}
						//addEvent(this.target,"keyup",function (event) {console.log('key', event);});
						tds=this.target.getElementsByTagName('input');
						if (tds.length > 0) {
							tds[0].wf=this;
							tds[0].value=this.page+1;
							addEvent(tds[0],"keyup",function (event) {if (! this.wf) this.wf=window.event.srcElement.wf;this.wf.showPage(event, this);});
						}
						tds=this.target.getElementsByTagName('a');
						for (var i=0;i<tds.length;i++) {
							if (tds[i].className=='fullheight') {
								tds[i].wf=this;
								addEvent(tds[i],"click",function (event) {if (! this.wf) this.wf=window.event.srcElement.wf;this.wf.fullHeight(event, this);});
							}
							if (tds[i].className=='fullwidth') {
								tds[i].wf=this;
								addEvent(tds[i],"click",function (event) {if (! this.wf) this.wf=window.event.srcElement.wf;this.wf.fullWidth(event, this);});
							}
							if (tds[i].className=='fullscreen') {
								tds[i].wf=this;
								addEvent(tds[i],"click",function (event) {if (! this.wf) this.wf=window.event.srcElement.wf;this.wf.fullScreen(event, this);});
							}
						}
						/*
						tds=this.target.getElementsByTagName('img');
						if (tds.length > 0) {
							tds[0].wf=this;
							tds[0].value=this.page+1;
							addEvent(tds[0],"load",function (event) {console.log(this.wf);});
						}*/
					}
					this.fixWidth();

				}
 		} else if (this.type=='embed') {
 			
 			var hembed='<iframe id="fembed" src="';
 			if (this.pdfLink) hembed+=this.pdfLink;
 			else hembed+=this.fileLink;
 			
 			hembed+='&inline=yes" style="';
 			hembed+='border:none;width:100%;';
 			if (this.staticHeight) hembed+=' height:'+this.staticHeight+'px;';
 			else {
 				var yt=AnchorPosition_getPageOffsetTop(this.target);
 				var dh=getFrameHeight();//this.getInnerHeight(document.body);
 				var ih=this.getInnerHeight(this.target);
 				var absHeight=dh-yt-ih-54;
 				if (absHeight < 100) absHeight=100;
 				hembed+=' height:'+absHeight+'px;';
 				if (! this.resizeEnabled) {
					var wftr2=this;
					this.resizeEnabled=true; // one shot 
					addEvent(window,"resize",function() {wftr2.onWindowResize();});
				}
 			}
 			hembed+='">';
 			if (this.target) {
 				
 				this.target.innerHTML=hpagerbegin+hembed+hpagerend; 		
 				var als=this.target.getElementsByTagName('a');
 				for (var i=0;i<als.length;i++) {

 					if (als[i].className=='fullscreen') {
 						als[i].setAttribute('wf',this);
 						als[i].wf=this;
 						this.wf=this;
 						addEvent(als[i],"click",function (event) {if (! this.wf) this.wf=window.event.srcElement.wf;this.wf.fullScreen(event, this);});
 					}
 				}
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
 			if (isNaN(io.value)) {
 				io.value=this.page+1;
 				return false;
 			}
 		  this.page=io.value - 1;
 		  if (this.page < 0 ) this.page=0;
 		  if (this.page >= this.pages ) this.page=this.pages-1;
 		  this.show();
 		}
 		return true;
 	},
 	
 	/**
 	 * view height page in screen 
 	 */
 	fullHeight : function (event) {
 		if (this.staticHeight && (! this.fullScreenActivated)) {
				this.fitResize(event);
				return;
			}
 		var imgs=this.target.getElementsByTagName('img');
 		var imgfile=null;
 		if (imgs.length > 1) {
 			for (var i=0;i<imgs.length;i++) {
 				if (imgs[i].className=='imgfile') imgfile=imgs[i];
 			}
 		}
 		if (imgfile ) {
 			
 			var wi=getObjectWidth(imgfile);
 			var hi=getObjectHeight(imgfile);
 			var yt=AnchorPosition_getPageOffsetTop(this.target);
 			var yi=AnchorPosition_getPageOffsetTop(imgfile);
 			var sh=document.body.scrollHeight;
 			var dh=getFrameHeight();//this.getInnerHeight(document.body);
 			var hhead=yi-yt+10;
 			var oriwidth=getObjectWidth(imgfile);
 			if (hi > dh) {
 				// the image est greater than document page => decrease image
 				imgfile.style.width='';
 				imgfile.style.border='none';
 				imgfile.style.height=(dh - hhead)+'px';
 				wi=getObjectWidth(imgfile);
 				this.width=wi;
 			} else if ((! this.fullScreenActivated) &&(sh < dh)) {
 				// the image est lesser than document page=> increase image
 				imgfile.style.width='';
 				imgfile.style.border='none';
 				imgfile.style.height=(hi - minus)+'px';
 				wi=getObjectWidth(imgfile);
 				var dw=getFrameWidth();
 				var maxwidth=dw-50;
 				if (wi > maxwidth) {
 					wi=maxwidth;
 					imgfile.style.width=wi+'px';
 	 				imgfile.style.height='';
 				}
 				this.width=wi;
 			}

 			this.fullType='height';
 			if (oriwidth < wi) this.show();

 			}
 	},

 	fullScreen: function (event) {
 		if (this.fullScreenActivated) {
 			this.unFullScreen();
 			return;
 		}
 		var imgs=this.target.getElementsByTagName('img');
 		var imgfile=null;
 		if (imgs.length > 1) {
 			for (var i=0;i<imgs.length;i++) {
 				if (imgs[i].className=='imgfile') imgfile=imgs[i];
 			}
 		}
 		if (imgfile ) {

 			var wi=getObjectWidth(imgfile);
 			var hi=getObjectHeight(imgfile);
 			var yt=AnchorPosition_getPageOffsetTop(this.target);
 			var yi=AnchorPosition_getPageOffsetTop(imgfile);
 			var sh=document.body.scrollHeight;
 			var dh=getFrameHeight();//this.getInnerHeight(document.body);
 			var hhead=yi-yt+10;
 			var oriwidth=getObjectWidth(imgfile);

 			//document.body.style.overflow='hidden';
 			//document.body.style.width='0px';
 			//document.body.style.height='0px';
 			window.scrollTo(0, 0);
 			this.target.style.height=dh+'px';
 			imgfile.parentNode.style.height='';
 			imgfile.style.width='';
 			imgfile.style.height=(dh - hhead)+'px';

 			wi=getObjectWidth(imgfile);
 			//this.target.style.width=(wi+10)+'px';
 			this.target.className='fullscreen';
			if (this.staticHeight) imgfile.parentNode.style.overflowY='auto';
 			this.height=(dh - hhead);
 			var dw=getFrameWidth();
 			var maxwidth=dw-50;
 			if (wi > maxwidth) {
 					wi=maxwidth;
 					imgfile.style.width=wi+'px';
 	 				imgfile.style.height='';
 				}
 			this.width=wi;
 			this.fullScreenActivated=true;
 			if (oriwidth < wi) this.show();
 		}else if (this.type=='embed') {
 			this.target.className='fullscreen';
 			var fe=document.getElementById('fembed');
 			var sh=document.body.scrollHeight;
 			var yt=AnchorPosition_getPageOffsetTop(fe);
 			var dh=getFrameHeight();//
 			var nh=(dh-yt-10);
 			if (nh <100) nh=100;
			fe.style.height=nh+'px';
 			this.fullScreenActivated=true;
 		}
 	},	
 	unFullScreen: function (event) {

 		this.fullScreenActivated=false;
 		var imgs=this.target.getElementsByTagName('img');
 		var imgfile=null;
 		if (imgs.length > 1) {
 			for (var i=0;i<imgs.length;i++) {
 				if (imgs[i].className=='imgfile') imgfile=imgs[i];
 			}
 		}
 		if (imgfile ) {

 			var wi=getObjectWidth(imgfile);
 			var hi=getObjectHeight(imgfile);
 			var yt=AnchorPosition_getPageOffsetTop(this.target);
 			var yi=AnchorPosition_getPageOffsetTop(imgfile);
 			var sh=document.body.scrollHeight;
 			var dh=getFrameHeight();//this.getInnerHeight(document.body);
 			var hhead=yi-yt+10;
 			var oriwidth=getObjectWidth(imgfile);

 			document.body.style.overflow='';
 			this.target.className='';
 			this.target.style.height='';
 			if (this.staticHeight) {

 				imgfile.parentNode.style.overflowY='scroll';
 				this.target.style.height='';
 				imgfile.parentNode.style.height=this.staticHeight+'px';;
 				imgfile.style.width='';
 				imgfile.style.height=(this.staticHeight - hhead)+'px';
 				this.height=this.staticHeight+'px';
 			}

 			wi=getObjectWidth(imgfile);	 	 		
 			this.width=wi;
 			this.fullScreenActivated=false;

 		}else if (this.type=='embed') {
 			var fe=document.getElementById('fembed');
 			var sh=document.body.scrollHeight;
 			var nh;

 			this.target.className='';
 			if (this.staticHeight) {
 				nh=this.staticHeight;
 				fe.style.height=nh+'px';
 			} else {
 				this.onWindowResize();
 			}
 			
			
 		}
 	},
 	fullWidth : function (event) {
 		var imgs=this.target.getElementsByTagName('img');
 		var imgfile=null;
 		var wi=0;
 		if (imgs.length > 1) {
 			for (var i=0;i<imgs.length;i++) {
 				if (imgs[i].className=='imgfile') imgfile=imgs[i];
 			}
 		}
 		if (imgfile ) {
 			var oriwidth=getObjectWidth(imgfile);
 			if (this.fullScreenActivated) {

 				 wi=this.getInnerWidth(imgfile.parentNode);
 				this.width=wi;
 				//this.show();
 			} else {
 				var dw=getObjectWidth(imgfile.parentNode);
 				imgfile.style.height='';
 				var scrollbarWidth=20;
 				imgfile.style.width=(dw-4-scrollbarWidth)+'px';
 				wi=getObjectWidth(imgfile);
 				this.width=wi;
 			}
 			var dw=getFrameWidth();
 			var maxwidth=dw-50;
 			if (wi > maxwidth) {
 				wi=maxwidth;
 				imgfile.style.width=wi+'px';
 				imgfile.style.height='';
 			}
 			this.fullType='width';
 			if (oriwidth < wi) this.show();

 		}
 	},
/**
 * view height page in screen 
 */
 	fitResize : function (event) {
 		var imgs=this.target.getElementsByTagName('img');
 		var imgfile=null;
 		if (imgs.length > 1) {
 			for (var i=0;i<imgs.length;i++) {
 				if (imgs[i].className=='imgfile') imgfile=imgs[i];
 			}
 		}
 		if (imgfile ) {
 			var hi=getObjectHeight(imgfile);
 			
 			var dh=getObjectHeight(imgfile.parentNode);
 			if (hi > dh) {

 	 			imgfile.style.width='';
 	 			
 	 			imgfile.style.height=(dh-8)+'px';
 	 			wi=getObjectWidth(imgfile);
 	 			this.width=wi;
 			} 
 			
 		}
 	},
 	getImgFile : function() {
 		var imgfile=null;
 		var imgs=this.target.getElementsByTagName('img');
 		if (imgs.length > 1) {
 			for (var i=0;i<imgs.length;i++) {
 				if (imgs[i].className=='imgfile') imgfile=imgs[i];
 			}
 		}
 		return imgfile;
 	},
 	/**
 	 * view height page in screen 
 	 */
 	fixWidth : function (event) {
 		if (this.type=='png' && this.width=='100%' && this.target) {

 			var imgfile=this.getImgFile();
 			if (imgfile ) {
 				imgfile.style.width='10px';
 				//imgfile.style.border='solid red 10px';
 				//this.target.innerHTML='<p>Resizing</p>';
 				this.absoluteWidth=this.getInnerWidth(this.target);
 				imgfile.style.width=this.absoluteWidth+'px';
 			}	
 		}
 	},
 	/**
 	 * view height page in screen 
 	 */
 	onWindowResize : function (event) {
 			var imgfile=this.getImgFile();
 			if (imgfile ) {
 				imgfile.style.width='10px';
 				//imgfile.style.border='solid red 10px';
 				//this.target.innerHTML='<p>Resizing</p>';
 				this.absoluteWidth=this.getInnerWidth(this.target);
 				imgfile.style.width=this.absoluteWidth+'px';

 				this.fullType='width';
 				if (this.fullType == 'width') {
 					this.fullWidth(event);
 				} else {
 					this.fullHeigth(event);
 				}
 				return;
 				
 			
 		} else if (this.type=='embed') {
 			
 			var fe=document.getElementById('fembed');
 			var sh=document.body.scrollHeight;
 			var dh=getFrameHeight();//
 			var bodyHeight=this.getInnerHeight(document.body);
 			var feHeight=parseInt(fe.style.height);

 			
 			if (sh > dh) {
 				feHeight-=(sh-dh);
 				if (feHeight < 100) feHeight=100;
 			} else {
 				feHeight+=(dh-bodyHeight);
 			}

				fe.style.height=feHeight+'px';
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
 	getInnerHeight : function (o) {
 		if (o) {
 			var iw=0;
 			if (o.clientHeight) iw=o.clientHeight;
 			else if (o.offsetHeight) iw=o.offsetHeight;
 			else if (o.clip && o.clip.Height) iw=o.clip.Height;
 			if (iw > 0) {
 				if (iw > 100) {
 					//iw-=50;
 				}
 			}
 			return iw;
 		}
 		return 0;
 	},
 	verifyWaiting : function (event) {
 		if (XHT_FILES) {
 			 // from verifycomputedfiles.js
 			var status=-1;
 			if (XHT_FILES.files) {
 			   status=parseInt(XHT_FILES.files[this.vid]);
 			}
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

