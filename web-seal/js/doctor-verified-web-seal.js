
if (typeof(jQuery) == 'undefined') {
    document.write('<scr' + 'ipt type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></scr' + 'ipt>');
}
function getWebSeal(master_id, div_class_name) {

    jQuery.fn.textfill = function(options) {
        var fontSize = options.maxFontPixels;
        var ourText = jQuery('span:visible:first', this);
        var maxHeight = jQuery(this).height();
        var maxWidth = jQuery(this).width();
        var textHeight;
        var textWidth;
        do {
            ourText.css('font-size', fontSize);
            textHeight = ourText.height();
            textWidth = ourText.width();
            fontSize = fontSize - 1;
        } while ((textHeight > maxHeight || textWidth > maxWidth) && fontSize > 3);
        return this;
    };


    var seal_div_class_name = div_class_name;
    var master_id = master_id;
    var getSealObj;
    var seal_type;
    var seal_num;

    console.log(master_id);
    seal_num = jQuery("." + seal_div_class_name).length;
    if (seal_num > 0) {

        int_seal();
    }
    function int_seal() {
        if (document.getElementsByClassName(seal_div_class_name)[seal_num - 1]) {
            seal_type = document.getElementsByClassName(seal_div_class_name)[seal_num - 1].getAttribute("seal_type");
            console.log("seal_type " + seal_type);
            get_seal();
        } else {
            setTimeout(function () {
                console.log("int_seal calling again");
                int_seal();
            }, 1000);
        }
    }

    function get_seal() {
        if (window.XMLHttpRequest) {
            getSealObj = new XMLHttpRequest();
        }
        else {
            if (window.ActiveXObject) {
                try {
                    getSealObj = new ActiveXObject("Msxml2.XMLHTTP");
                }
                catch (e) {
                    try {
                        getSealObj = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    catch (e) { }
                }
            }
        }
        //if ('withCredentials' in getSealObj) {
            //getSealObj.open('GET', 'https://doctor-certified.com/web_seal/index.php?masterId=' + master_id + "&seal_type=" + seal_type, true);
            getSealObj.open('GET', 'http://www.nutritionist-verified.com/web-seal/doctor-verified-web-seal.php?master_id=' + master_id + "&seal_type=" + seal_type, true);            
            getSealObj.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            //getSealObj.setRequestHeader('Access-Control-Allow-Headers', '*');

            getSealObj.setRequestHeader("Access-Control-Allow-Origin", "*");
            getSealObj.setRequestHeader("Access-Control-Allow-Credentials", "true");
            getSealObj.setRequestHeader("Access-Control-Allow-Methods", "GET,HEAD,OPTIONS,POST,PUT");
            getSealObj.setRequestHeader("Access-Control-Allow-Headers", "Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");

            getSealObj.onreadystatechange = getSealResponse;
            getSealObj.send();
        //}
    }

    function getSealResponse() {
        if (getSealObj.readyState == 4) {
            var strResponse = getSealObj.responseText;
            console.log(getSealObj.responseText);
            paste_seal(seal_div_class_name, strResponse, seal_num);
            jQuery('.' + seal_div_class_name).bind('contextmenu', function (e) {
                return false;
            });
            seal_num--;
            if (seal_num > 0)
                int_seal();

        }
        jQuery('.doc_certi_domainName').each(function(){
            var jQuerydomain = jQuery(this);
            jQuerydomain.css({
                "white-space": 'nowrap',
                "font-family" : 'arial narrow',
                "font-size" : 14
            });
            jQuerydomain.html('<span>'+jQuerydomain.text()+'</span>').textfill({ maxFontPixels: 14 });
        });
    }
    function paste_seal(seal_div_class_name, strResponse, seal_div_num) {
        if (document.getElementsByClassName(seal_div_class_name)[seal_div_num - 1]) {
            document.getElementsByClassName(seal_div_class_name)[seal_div_num - 1].innerHTML = strResponse;
        } else {
            setTimeout(function () {
                console.log("calling again");
                paste_seal(seal_div_class_name, strResponse, seal_div_num);
            }, 1000);
        }

    }
}

//var WebSeal = new getWebSeal(WebSealParam.master_id, WebSealParam.div_class_name);

//alert(WebSealParam.master_id+' '+WebSealParam.div_class_name);
