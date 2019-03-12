// JavaScript Document
function checkMobile(){  
    var isiPad = navigator.userAgent.match(/iPad/i) != null;  
    if(isiPad){  
        return false;  
    }  
    var isMobile=navigator.userAgent.match(/iphone|android|phone|mobile|wap|netfront|x11|java|opera mobi|opera mini|ucweb|windows ce|symbian|symbianos|series|webos|sony|blackberry|dopod|nokia|samsung|palmsource|xda|pieplus|meizu|midp|cldc|motorola|foma|docomo|up.browser|up.link|blazer|helio|hosin|huawei|novarra|coolpad|webos|techfaith|palmsource|alcatel|amoi|ktouch|nexian|ericsson|philips|sagem|wellcom|bunjalloo|maui|smartphone|iemobile|spice|bird|zte-|longcos|pantech|gionee|portalmmm|jig browser|hiptop|benq|haier|^lct|320x320|240x320|176x220/i)!= null;  
    if(isMobile){  
        return true;  
    }  
    return false;  
}  
function _getCookie(cname){  
    var cookieStr = document.cookie.match("(?:^|;)\\s*" + cname + "=([^;]*)");  
    return cookieStr ? unescape(cookieStr[1]) : "";  
}  
var URL_MAP = [  
    ["shenghuo/", "wap/shenghuo/"],  
    ["jiankang/", "wap/jiankang/"],  
    ["yangsheng/", "wap/yangsheng/"],  
    ["diannao/", "wap/diannao/"],  
    ["yinshi/", "wap/yinshi/"],  
    ["baike/", "wap/baike/"]  
];  
function _getCookie(cname){  
  var cookieStr = document.cookie.match("(?:^|;)\\s*" + cname + "=([^;]*)");  
  return cookieStr ? unescape(cookieStr[1]) : "";  
}  
(function(){  
    if(checkMobile()){  
        if( location.hostname=="www.yldagong.com" ){ 
		   window.location.href="http://"+location.hostname+"/wechat/web";  
		}
    }  
})(); 