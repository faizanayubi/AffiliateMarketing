(function(window, Model) {
    window.request = Model.initialize();
    window.opts = {};
}(window, window.Model));

$(function() {
    $('select[value]').each(function() {
        $(this).val(this.getAttribute("value"));
    });
});

$(document).ready(function() {

    //initialize beautiful datetime picker
    $("input[type=date]").datepicker();
    $("input[type=date]").datepicker("option", "dateFormat", "yy-mm-dd");

    $('#getstats').submit(function(e) {
        $('#stats').html('<p class="text-center"><i class="fa fa-spinner fa-spin fa-5x"></i></p>');
        e.preventDefault();
        var data = $(this).serializeArray();
        request.read({
            action: "member/stats",
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
            longURL = btn.data('longurl'),
            description = btn.data('description'),
            title = btn.data('title'),
            item = btn.data('item');
        request.read({
            action: "member/shortenURL",
            data: {
                longURL: longURL,
                item: item
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

    $('button[name=message]').click(function(e) {
        var self = this;
        window.opts.subject = $(this).data("subject");
        window.opts.email = $(this).data("from");
        $('#message_modal').modal('show');
    });

    $('#messageform').submit(function(e) {
        e.preventDefault();
        var body = $('#body').val();
        request.create({
            action: "employer/messages",
            data: {
                action: 'support',
                subject: window.opts.subject,
                email: window.opts.email,
                body: body
            },
            callback: function(data) {
                $('#status').html('Message Sent Successfully!!!');
                $('#message_modal').modal('hide');
            }
        });
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
            action: "content/rpm",
            data: {shortURL: shortURL},
            callback: function(data) {
                item.html('RPM : ₹ '+ data.rpm +', Click : '+ data.click +', Earning : ₹ '+ data.earning);
            }
        });

    });

});

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

function clickToday () {
    var track = getCookie('clickToday');
    if (track != "") {
        //cookie exists
        $('#clickToday').html(getCookie('clickToday'));
        $('#unverifiedEarning').html(getCookie('unverifiedEarning'));
    } else {
        request.read({
            action: "member/clicksToday",
            data: {},
            callback: function(data) {
                $('#clickToday').html(data.click);
                setCookie('clickToday', data.click, 1/24);

                $('#unverifiedEarning').html(data.earning);
                setCookie('unverifiedEarning', data.earning, 1/24);
            }
        });
    }
}

function realtime () {
    $('#realtime_avgrpm').html('<i class="fa fa-spinner fa-pulse"></i>');
    $('#realtime_earnings').html('<i class="fa fa-spinner fa-pulse"></i>');
    $('#realtime_clicks').html('<i class="fa fa-spinner fa-pulse"></i>');
    
    request.read({
        action: "analytics/realtime",
        data: {},
        callback: function(data) {
            $('#realtime_avgrpm').html(data.avgrpm);
            $('#realtime_earnings').html(data.earnings);
            $('#realtime_clicks').html(data.clicks);
        }
    });
}

function getRPM (item_id) {
    var track = getCookie('rpm_'+item_id);
    if (track != "") {
        //cookie exists
        return JSON.parse(track);
    } else {
        request.read({
            action: "content/rpm",
            data: {item_id: item_id},
            callback: function(data) {
                var json_str = JSON.stringify(arr);
                setCookie('rpm_'+item_id, json_str, 1);
                return data;
            }
        });
    }
}