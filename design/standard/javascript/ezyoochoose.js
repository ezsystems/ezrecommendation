
function ezycClass() {
	this.originalSrc = '';
}

ezycClass.prototype.get_ezyc_params = function(usrId) {
	var ezyc_params = '';
	var x,y;

	if (navigator.cookieEnabled == true) {
		var c, c1, c2;
		var d = new Date();
		var usrCookie = new Array(3);
		var ezycCookieAvailable = false;
		var usrCookieAvailable = false;
	
		if (document.cookie) {
			c = document.cookie;
			var pos = c.indexOf("ezyc=");
			if ( pos > -1 )	{
				var pos2 = c.indexOf(";",pos+5);
				if ( pos2 > -1)
					c1 = c.substring(pos+5, c.indexOf(";",pos));
				else
					c1 = c.substring(pos+5);
				ezycCookieAvailable = true;
			}

			var pos = c.indexOf("ezyc_usr=");
			if ( pos > -1 )	{
				var pos2 = c.indexOf(";",pos+9);
				if ( pos2 > -1)
					c2 = c.substring(pos+9, c.indexOf(";",pos));
				else
					c2 = c.substring(pos+9);
				usrCookieAvailable = true;
			}
		}
		
		if (!ezycCookieAvailable) {
			c1 = d.getTime().toString() + Math.floor(100000*Math.random()).toString();
		}
		
		ezyc_params += "sid=" + encodeURIComponent(c1);
		
		if(!usrCookieAvailable  &&  usrId != 0){
			
			var a = new Date(d.getTime() +1000*60*30);			
			document.cookie = 'ezyc_usr=' + usrId + '; expires=' + a.toGMTString() + '; path=/;';
			ezyc_params += "&map=1";
		}
		
		var a = new Date(d.getTime() +1000*60*30);			
		document.cookie = 'ezyc=' + c1 + '; expires=' + a.toGMTString() + '; path=/;';	
		
		
		
	}

	return ezyc_params;
}


ezycClass.prototype.img = function(src, userid) {
	
	var params = ezyc.get_ezyc_params(userid);
	
	if (src.indexOf('?') == -1){
		params = '?'+params;
	}else{
		params = '&'+params;
	}
	
	src = src + params;
	
	var e = document.getElementById('ezyc-image');
	if (e == null) {
		document.write('<div id="ezyoochoose"><img id="ezyc-image" src="' + src + '" alt="ezyc-image" /></div>');
	}
	else {
		e.src = src;
	}	
}

ezycClass.prototype.evt = function(src, userid) {
	
	var params = ezyc.get_ezyc_params(userid);
	
	if (src.indexOf('?') == -1){
		params = '?'+params;
	}else{
		params = '&'+params;
	}
	
	src = src + params;
	
	var e = document.getElementById('ezyc-image');
	if (e != null) {
		e.src = src;
	}
	return true;
}

var ezyc = new ezycClass(); 