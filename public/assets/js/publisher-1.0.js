(function(window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

$(function () {
    $('#side-menu').metisMenu();
});

$(function() {
    $('select[value]').each(function() {
        $(this).val(this.getAttribute("value"));
    });
});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function () {
    $(window).bind("load resize", function () {
        topOffset = 50;
        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1)
            height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    var element = $('ul.nav a').filter(function () {
        return this.href == url || url.href.indexOf(this.href) == 0;
    }).addClass('active').parent().parent().addClass('in').parent();
    if (element.is('li')) {
        element.addClass('active');
    }
});

$(document).ready(function() {

    // find all the selectors 
    var types = $('#addOptions select');
    types.on("change", function () { // bind the change function
        var value = $(this).val();

        // if text box is selected then show it and hide the file upload or vice-versa
        if (value === "text") {
            $("#type").find("input[type='text']").toggleClass("hide").attr("required", "");
            $("#type").find("input[type='file']").toggleClass("hide");
        } else if (value === "image") {
            $("#type").find("input[type='file']").toggleClass("hide");
            $("#type").find("input[type='text']").toggleClass("hide").removeAttr("required");
        }
    });

    tinymce.init({
        selector: ".editor",
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu"
        ],
        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
        autosave_ask_before_unload: false,
        max_height: 200,
        min_height: 160,
        height: 180
    });

    //initialize beautiful datetime picker
    $("input[type=date]").datepicker();
    $("input[type=date]").datepicker("option", "dateFormat", "yy-mm-dd");

    $('#getstats').submit(function(e) {
        $('#stats').html('<p class="text-center"><i class="fa fa-spinner fa-spin fa-5x"></i></p>');
        e.preventDefault();
        var data = $(this).serializeArray();
        request.read({
            action: "publisher/stats",
            data: data,
            callback: function(data) {
                $('#stats').html('');
                if (data.data) {
                    Morris.Bar({
                        element: 'stats',
                        data: toArray(data.data),
                        xkey: 'y',
                        ykeys: ['a'],
                        labels: ['Total']
                    });
                }
            }
        });
    });

    $(".shortenURL").click(function(e) {
        e.preventDefault();
        var btn = $(this),
            hash = btn.data('hash'),
            title = btn.data('title'),
            description = btn.data('description'),
            item = btn.data('item'),
            domain = btn.closest('div').find('.domain option:selected').text();

        request.read({
            action: "publisher/shortenURL",
            data: {
                hash: hash,
                item: item,
                domain: domain
            },
            callback: function(data) {
                btn.closest('div').find('.shorturl').val(data.shortURL);
                btn.closest('div').find('.shorturl').focus();
                $('#link_data').val(title+"\n"+description+"\n"+data.shortURL);
                $('#link_modal').modal('show');
                document.execCommand('SelectAll');
                document.execCommand("Copy", false, null);
            }
        });
    });

    $('#link_data').mouseup(function() {
        $(this)[0].select();
    });

    // find all the selectors 
    var types = $('#addOptions select');
    types.on("change", function() { // bind the change function
        var value = $(this).val();

        // if text box is selected then show it and hide the file upload or vice-versa
        if (value === "text") {
            $("#type").find("input[type='text']").toggleClass("hide").attr("required", "");
            $("#type").find("input[type='file']").toggleClass("hide");
        } else if (value === "image") {
            $("#type").find("input[type='file']").toggleClass("hide");
            $("#type").find("input[type='text']").toggleClass("hide").removeAttr("required");
        }
    });

    $(".googl").click(function(e) {
        e.preventDefault();
        var item = $(this),
            shortURL = item.data('url'),
            time = item.data('time'),
            property = item.data('property');
        item.html('<i class="fa fa-spinner fa-pulse"></i>');
        request.read({
            action: "analytics/link",
            data: {shortURL: shortURL},
            callback: function(data) {
                item.html('RPM : <i class="fa fa-inr"></i> '+ data.rpm +', Click : '+ data.click +', Earning : <i class="fa fa-inr"></i> '+ data.earning);
            }
        });

    });

});

function today () {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();

    if(dd<10) {
        dd='0'+dd
    } 

    if(mm<10) {
        mm='0'+mm
    } 

    today = yyyy+'-'+mm+'-'+dd;
    return today;
}

function stats() {
    request.read({
        action: "analytics/stats/" + today(),
        callback: function(data) {
            $('#today_click').html(data.stats.click);
            $('#today_rpm').html('<i class="fa fa-inr"></i> '+ data.stats.rpm);
            $('#today_earning').html('<i class="fa fa-inr"></i> '+ data.stats.earning);

            var gdpData = data.stats.analytics;
            $('#world-map').vectorMap({
                map: 'world_mill_en',
                series: {
                    regions: [{
                        values: gdpData,
                        scale: ['#C8EEFF', '#0071A4'],
                        normalizeFunction: 'polynomial'
                    }]
                },
                onRegionTipShow: function(e, el, code) {
                    if (gdpData.hasOwnProperty(code)) {
                        el.html(el.html() + ' (Clicks - ' + gdpData[code] + ')');
                    } else{
                        el.html(el.html() + ' (Clicks - 0)');
                    };
                }
            });
        }
    });
}


function toArray(object) {
    var array = $.map(object, function(value, index) {
        return [value];
    });
    return array;
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function copy() {
    var copyDiv = $(".shorturl");
    copyDiv.focus();
    document.execCommand('SelectAll');
    document.execCommand("Copy", false, null);
}

window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
$.src="//v2.zopim.com/?3PC3XA60lWz8HPyy7BkzGZoo5L1PUKUw";z.t=+new Date;$.
type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");