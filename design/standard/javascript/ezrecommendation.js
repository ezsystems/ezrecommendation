function ezrecoClass() {
    this.originalSrc = '';
    this.userId = 10; // standard anonymous user id
}

ezrecoClass.prototype.get_ezreco_params = function() {
    var ezreco_params = 'userid=' + this.userId + '&';
    var x,y;

    if (navigator.cookieEnabled == true) {
        var c, c1, c2;
        var d = new Date();
        var usrCookie = new Array(3);
        var ezrecoCookieAvailable = false;
        var usrCookieAvailable = false;

        if (document.cookie) {
            c = document.cookie;
            var pos = c.indexOf("ezreco=");
            if ( pos > -1 )    {
                var pos2 = c.indexOf(";",pos+7);
                if ( pos2 > -1)
                    c1 = c.substring(pos+7, c.indexOf(";",pos));
                else
                    c1 = c.substring(pos+7);
                ezrecoCookieAvailable = true;
            }

            var pos = c.indexOf("ezreco_usr=");
            if ( pos > -1 )    {
                var pos2 = c.indexOf(";",pos+11);
                if ( pos2 > -1)
                    c2 = c.substring(pos+11, c.indexOf(";",pos));
                else
                    c2 = c.substring(pos+11);
                usrCookieAvailable = true;
            }
        }

        if (!ezrecoCookieAvailable) {
            c1 = d.getTime().toString() + Math.floor(100000*Math.random()).toString();
        }

        ezreco_params += "sid=" + encodeURIComponent(c1);

        if(!usrCookieAvailable  &&  this.userId != 10){
            var a = new Date(d.getTime() +1000*60*30);
            document.cookie = 'ezreco_usr=' + this.userId + '; expires=' + a.toGMTString() + '; path=/;';
            ezreco_params += "&map=1";
        }else if (this.userId != 10){
            ezreco_params += "&map=1";
        }

        var a = new Date(d.getTime() +1000*60*30);
        document.cookie = 'ezreco=' + c1 + '; expires=' + a.toGMTString() + '; path=/;';



    }

    return ezreco_params;
}


ezrecoClass.prototype.img = function(src, userid) {
    var params = '';

    this.userId = userid;
    params = ezreco.get_ezreco_params();

    if (src.indexOf('?') == -1){
        params = '?'+params;
    }else{
        params = '&'+params;
    }

    src = src + params;

    var e = document.getElementById('ezreco-image');
    if (e == null) {
        document.getElementById('ezrecommendation').innerHTML += '<img id="ezreco-image" src="' + src + '" alt="ezreco-image" />';
    }
    else {
        e.src = src;
    }
}

ezrecoClass.prototype.evt = function(src) {

    var params = ezreco.get_ezreco_params();

    if (src.indexOf('?') == -1){
        params = '?'+params;
    }else{
        params = '&'+params;
    }

    src = src + params;

    var e = document.getElementById('ezreco-image');
    if (e != null) {
        e.src = src;
    }
    return true;
}

ezrecoClass.prototype.consevt = function(src, elapsedtime) {

    var params = ezreco.get_ezreco_params();

    if (src.indexOf('?') == -1){
        params = '?'+params + '&elapsedtime=' + elapsedtime;
    }else{
        params = '&'+params + '&elapsedtime=' + elapsedtime;
    }

    src = src + params;

    var e = document.getElementById('ezreco-image');
    if (e != null) {
        e.src = src.replace(/&amp;/g, "&");
        setTimeout('sleep(1)', 1000); //the page should wait a bit of time until the module has sent the request
    }
    return true;
}

ezrecoClass.prototype.renderEvents = function (urls) {
    var params = ezreco.get_ezreco_params(),
        elt, r = document.getElementById('ezrecommendation');

    for(var i=0; i!=urls.length; i++) {
        r.innerHTML += '<img src="' + urls[i] + '&' + params + '" />';
    }
}


function sleep(milliseconds) {
      var start = new Date().getTime();
      for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds){
          break;
        }
      }
    }



var ezreco = new ezrecoClass();


 var startTime = new Date();
 var startTimePageVisit = startTime.getTime();
 window.onbeforeunload = function(){
     var endTime = new Date();
     var endTimePageVisit = endTime.getTime();
     var timeSpent=(endTimePageVisit - startTimePageVisit);
     var timeelapsed = Math.round(timeSpent/1000);
     var e = document.getElementById('ezreco-consume-event');

     if (e) {
         ezreco.consevt(e.innerHTML, timeelapsed);
     }

 };
