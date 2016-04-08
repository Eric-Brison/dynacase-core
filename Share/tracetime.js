
/**
 * @author Anakeen
 */

var TRACETIME=true;
var BEGINTIME;
var ENDTIME;

var TRACEBUTTON=document.createElement("button");
TRACEBUTTON.style.position='absolute';
TRACEBUTTON.style.top='0px';
TRACEBUTTON.style.left='0px';
TRACEBUTTON.style.zIndex=100000;
TRACEBUTTON.innerHTML=TTRACE['server all'];
TRACEBUTTON.onclick=function ztrace() {displayPropertyNames(TTRACE);};
TRACEBUTTON.oncontextmenu=function ztraceun() {this.style.display='none';return false;};
//TRACEBUTTON.style.display='none';
function trace_enddate() {
  var d=new Date();
  ENDTIME=d.getTime();
  TTRACE['navigator']=(ENDTIME-BEGINTIME)/1000+'s';
  // displayPropertyNames(TTRACE);

  document.body.appendChild(TRACEBUTTON); 
}
if (TRACETIME) {
  var d=new Date();
  BEGINTIME=d.getTime();
  addEvent(window,"load",trace_enddate);
 }
