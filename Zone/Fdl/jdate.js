
/**
 * @author Anakeen
 */


var string_constant_zero  = "0000000000000000000000000000000000";
function zeros(w, number)
{
  var s, ww;
  s=number.toString();
  ww = s.length;
  if (ww < w) {
    s = string_constant_zero.substring(0, w-ww)+s;
  }
  return s;
}

// from http://aa.usno.navy.mil/data/docs/JulianDate.html
//-----------------------------------------------------------------------------
// convert calendar to Julian date
// (Julian day number algorithm adopted from Press et al.)
//-----------------------------------------------------------------------------
function cal_to_jd( era, y, m, d, h, mn, s )
{
	var jy, ja, jm;			//scratch

	if( y == 0 ) {
		alert("There is no year 0 in the Julian system!");
        return "invalid";
    }
    if( y == 1582 && m == 10 && d > 4 && d < 15 && era == "CE" ) {
		alert("The dates 5 through 14 October, 1582, do not exist in the Gregorian system!");
        return "invalid";
    }

//	if( y < 0 )  ++y;
    if( era == "BCE" ) y = -y + 1;
	if( m > 2 ) {
		jy = y;
		jm = m + 1;
	} else {
		jy = y - 1;
		jm = m + 13;
	}

	var intgr = Math.floor( Math.floor(365.25*jy) + Math.floor(30.6001*jm) + d + 1720995 );

	//check for switch to Gregorian calendar
    var gregcal = 15 + 31*( 10 + 12*1582 );
	if( d + 31*(m + 12*y) >= gregcal ) {
		ja = Math.floor(0.01*jy);
		intgr += 2 - ja + Math.floor(0.25*ja);
	}

	//correct for half-day offset
	var dayfrac = h/24.0 - 0.5;
	if( dayfrac < 0.0 ) {
		dayfrac += 1.0;
		--intgr;
	}

	//now set the fraction of a day
	var frac = dayfrac + (mn + s/60.0)/60.0/24.0;

    //round to nearest second
    var jd0 = (intgr + frac)*100000;
    var jd  = Math.floor(jd0);
    if( jd0 - jd > 0.5 ) ++jd;
    return jd/100000;
}
function jdToWeekNumber(JD) {
  var J=parseFloat(JD) + 0.5;
    var D4=(J+31741-(J %7))% 146097 % 36524 % 1461;
    var L=Math.floor(D4/1460);
    var D1=((D4-L) % 365)+L;
    var wn=Math.floor(D1/7)+1;
    return(wn);
}
//-----------------------------------------------------------------------------
// convert Julian date to calendar date
// (algorithm adapted from Hatcher, D.A., 1984, QJRAS 25, 53)
// (algorithm adopted from Press et al.)
// with minor corrections to properly catch Gregorian gap in October 1582
//-----------------------------------------------------------------------------
function jd_to_cal( jd, dformat )
{
  var	j1, j2, j3, j4, j5;			//scratch

  //
  // get the date from the Julian day number
  //
  var intgr   = Math.floor(jd);
  var frac    = jd - intgr;
  var gregjd  = 2299160.5;
  if( jd >= gregjd ) {				//Gregorian calendar correction
    var tmp = Math.floor( ( (intgr - 1867216.0) - 0.25 ) / 36524.25 );
    j1 = intgr + 1 + tmp - Math.floor(0.25*tmp);
  } else
    j1 = intgr;

  //correction for half day offset
  var df = frac + 0.5;
  if( df >= 1.0 ) {
    df -= 1.0;
    ++j1;
  }

  j2 = j1 + 1524.0;
  j3 = Math.floor( 6680.0 + ( (j2 - 2439870.0) - 122.1 )/365.25 );
  j4 = Math.floor(j3*365.25);
  j5 = Math.floor( (j2 - j4)/30.6001 );

  var d = Math.floor(j2 - j4 - Math.floor(j5*30.6001));
  var m = Math.floor(j5 - 1.0);
  if( m > 12 ) m -= 12;
  var y = Math.floor(j3 - 4715.0);
  if( m > 2 )   --y;
  if( y <= 0 )  --y;

  //
  // get time of day from day fraction
  //
  var hr  = Math.floor(df * 24.0);
  var mn  = Math.floor((df*24.0 - hr)*60.0);
  f  = ((df*24.0 - hr)*60.0 - mn)*60.0;
  var sc  = Math.floor(f);
  f -= sc;
  if( f > 0.5 ) ++sc;
  if( sc == 60 ) {
    sc = 0;
    ++mn;
  }
  if( mn == 60 )  {
    mn = 0;
    ++hr;
  }
  if( hr == 24 )  {
    hr = 0;
    ++d;            //this could cause a bug, but probably will never happen in practice
  }

  if( y < 0 ) {
    y = -y;
    ce=' BCE';
    // form.era[1].checked = true;
  } else {
    ce='';
    //   form.era[0].checked = true;
  }

  switch (dformat) {
  case 'M':
    retiso8601=m;
    break;
  case 'Y':
    retiso8601=y;
    break;
  case 'd':
    retiso8601=d;
    break;
  default:
    retiso8601=y+'-'+zeros(2,m)+'-'+zeros(2,d)+' '+zeros(2,hr)+':'+zeros(2,mn)+ce;
  }
  return retiso8601;
  //    form.year.value          = y;
  //     form.month[m-1].selected = true;
  //     form.day[d-1].selected   = d;
  //     form.hour.value          = hr;
  //     form.minute.value        = mn;
  //    form.second.value        = sc;
}
function weekDay(jd) {
    //weekday
    var	weekday = new Array("[TEXT:Monday]","[TEXT:Tuesday]","[TEXT:Wednesday]","[TEXT:Thursday]","[TEXT:Friday]","[TEXT:Saturday]","[TEXT:Sunday]");
    var t  = parseFloat(jd) + 0.5;
    var wd = Math.floor( (t/7 - Math.floor(t/7))*7 + 0.000000000317 );   //add 0.01 sec for truncation error correction
    return weekday[wd];
}
//-----------------------------------------------------------------------------
// calculate Julian date from calendar date or calendar date from Julian date
//-----------------------------------------------------------------------------
function JDcalc( form ) {
    var era;
    for( k=0; k < form.era.length; ++k ) {
        if( form.era[k].checked ) {
            era = form.era[k].value;
            break;
        }
    }
    var calctype;
    for( k=0; k < form.calctype.length; ++k ) {
        if( form.calctype[k].checked ) {
            calctype = form.calctype[k].value;
            break;
        }
    }
    if( calctype == "JD" ) {
        var m;
        for( var k=0; k < form.month.length; ++k ) {    //Netscape 4.7 is stoopid
            if( form.month[k].selected ) {
                m = k+1;
                break;
            }
        }
        var d;
        for( var k=1; k <= form.day.length; ++k ) {    //Netscape 4.7 is stoopid
            if( form.day[k-1].selected ) {
                d = k;
                break;
            }
        }
        var y  = parseFloat(form.year.value);
//      var m  = parseFloat(form.month.value);
//      var d  = parseFloat(form.day.value);
        var h  = parseFloat(form.hour.value);
        var mn = parseFloat(form.minute.value);
        var s  = parseFloat(form.second.value);
        if( y < 0 ) {
	    	y   = -y;
            era = "BCE";
            form.year.value = y;
            form.era[1].checked = true;
        }
        form.JDedit.value = cal_to_jd(era,y,m,d,h,mn,s);
    } else {
        jd_to_cal(form.JDedit.value,form);
    }
    //weekday
    var	weekday = new Array("[TEXT:Monday]","[TEXT:Tuesday]","[TEXT:Wednesday]","[TEXT:Thursday]","[TEXT:Friday]","[TEXT:Saturday]","[TEXT:Sunday]");
    var t  = parseFloat(form.JDedit.value) + 0.5;
    var wd = Math.floor( (t/7 - Math.floor(t/7))*7 + 0.000000000317 );   //add 0.01 sec for truncation error correction
    form.wkday.value = weekday[wd];
}

var month = new Array("[TEXT:January]","[TEXT:February]","[TEXT:March]","[TEXT:April]","[TEXT:May]","[TEXT:June]","[TEXT:July]",
					  "[TEXT:August]","[TEXT:September]","[TEXT:October]","[TEXT:November]","[TEXT:December]");
var numdays = new Array(31,28,31,30,31,30,31,31,30,31,30,31);



