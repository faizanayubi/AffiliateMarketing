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
                $('#link_data').val(title + "\n" + description + "\n" + data.shortURL);
                $('#link_modal').modal('show');
                document.execCommand('SelectAll');
                document.execCommand("Copy", false, null);
            }
        });
    });

    $('#link_data').mouseup(function() {
        $(this)[0].select();
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
            data: {
                shortURL: shortURL
            },
            callback: function(data) {
                item.html('RPM : <i class="fa fa-inr"></i> ' + data.rpm + ', Click : ' + data.click + ', Earning : <i class="fa fa-inr"></i> ' + data.earning);
            }
        });

    });

});

function today() {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    var yyyy = today.getFullYear();

    if (dd < 10) {
        dd = '0' + dd
    }

    if (mm < 10) {
        mm = '0' + mm
    }

    today = yyyy + '-' + mm + '-' + dd;
    return today;
}

function stats() {
    request.read({
        action: "analytics/stats/" + today(),
        callback: function(data) {
            $('#today_click').html(data.stats.click);
            $('#today_rpm').html(data.stats.rpm);
            $('#today_earning').html(data.stats.earning);

            /*var gdpData = data.stats.analytics;
            $('#world-map').vectorMap({
                map: 'world_mill_en',
                backgroundColor: null,
                color: '#fff',
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
            });*/
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
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
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

(function(jQuery) {

    // Variable
    var $ = jQuery;
    $.fn.ripple = function() {
        $(this).click(function(e) {
            var rippler = $(this),
                ink = rippler.find(".ink");

            if (rippler.find(".ink").length === 0) {
                rippler.append("<span class='ink'></span>");
            }


            ink.removeClass("animate");
            if (!ink.height() && !ink.width()) {
                var d = Math.max(rippler.outerWidth(), rippler.outerHeight());
                ink.css({
                    height: d,
                    width: d
                });
            }

            var x = e.pageX - rippler.offset().left - ink.width() / 2;
            var y = e.pageY - rippler.offset().top - ink.height() / 2;
            ink.css({
                top: y + 'px',
                left: x + 'px'
            }).addClass("animate");
        });
    };

    $.fn.carouselAnimate = function() {
        function doAnimations(elems) {
            var animEndEv = 'webkitAnimationEnd animationend';

            elems.each(function() {
                var $this = $(this),
                    $animationType = $this.data('animation');
                $this.addClass($animationType).one(animEndEv, function() {
                    $this.removeClass($animationType);
                });
            });
        }

        var $myCarousel = this;
        var $firstAnimatingElems = $myCarousel.find('.item:first')
            .find('[data-animation ^= "animated"]');

        doAnimations($firstAnimatingElems);
        $myCarousel.carousel('pause');
        $myCarousel.on('slide.bs.carousel', function(e) {
            var $animatingElems = $(e.relatedTarget)
                .find("[data-animation ^= 'animated']");
            doAnimations($animatingElems);
        });
    };


    this.hide = function() {
        $(".tree").hide();
        $(".sub-tree").hide();
    };


    this.treeMenu = function() {

        $('.tree-toggle').click(function(e) {
            e.preventDefault();
            var $this = $(this).parent().children('ul.tree');
            $(".tree").not($this).slideUp(600);
            $this.toggle(700);

            $(".tree").not($this).parent("li").find(".tree-toggle .right-arrow").removeClass("fa-angle-down").addClass("fa-angle-right");
            $this.parent("li").find(".tree-toggle .right-arrow").toggleClass("fa-angle-right fa-angle-down");
        });

        $('.sub-tree-toggle').click(function(e) {
            e.preventDefault();
            var $this = $(this).parent().children('ul.sub-tree');
            $(".sub-tree").not($this).slideUp(600);
            $this.toggle(700);

            $(".sub-tree").not($this).parent("li").find(".sub-tree-toggle .right-arrow").removeClass("fa-angle-down").addClass("fa-angle-right");
            $this.parent("li").find(".sub-tree-toggle .right-arrow").toggleClass("fa-angle-right fa-angle-down");
        });
    };



    this.leftMenu = function() {

        $('.opener-left-menu').on('click', function() {
            $(".line-chart").width("100%");
            $(".mejs-video").height("auto").width("100%");
            if ($('#right-menu').is(":visible")) {
                $('#right-menu').animate({
                    'width': '0px'
                }, 'slow', function() {
                    $('#right-menu').hide();
                });
            }
            if ($('#left-menu .sub-left-menu').is(':visible')) {
                $('#content').animate({
                    'padding-left': '0px'
                }, 'slow');
                $('#left-menu .sub-left-menu').animate({
                    'width': '0px'
                }, 'slow', function() {
                    $('.overlay').show();
                    $('.opener-left-menu').removeClass('is-open');
                    $('.opener-left-menu').addClass('is-closed');
                    $('#left-menu .sub-left-menu').hide();
                });

            } else {
                $('#left-menu .sub-left-menu').show();
                $('#left-menu .sub-left-menu').animate({
                    'width': '230px'
                }, 'slow');
                $('#content').animate({
                    'padding-left': '230px',
                    'padding-right': '0px'
                }, 'slow');
                $('.overlay').hide();
                $('.opener-left-menu').removeClass('is-closed');
                $('.opener-left-menu').addClass('is-open');
            }
        });
    };

    $(".box-v6-content-bg").each(function() {
        $(this).attr("style", "width:" + $(this).attr("data-progress") + ";");
    });

    $('.carousel-thumb').on('slid.bs.carousel', function() {
        if ($(this).find($(".item")).is(".active")) {
            var Current = $(this).find($(".item.active")).attr("data-slide");
            $(".carousel-thumb-img li img").removeClass("animated rubberBand");
            $(".carousel-thumb-img li").removeClass("active");

            $($(".carousel-thumb-img").children()[Current]).addClass("active");
            $($(".carousel-thumb-img li").children()[Current]).addClass("animated rubberBand");
        }
    });



    $(".carousel-thumb-img li").on("click", function() {
        $(".carousel-thumb-img li img").removeClass("animated rubberBand");
        $(".carousel-thumb-img li").removeClass("active");
        $(this).addClass("active");
    });

    $("#mimin-mobile-menu-opener").on("click", function(e) {
        $("#mimin-mobile").toggleClass("reverse");
        var rippler = $("#mimin-mobile");
        if (!rippler.hasClass("reverse")) {
            if (rippler.find(".ink").length == 0) {
                rippler.append("<div class='ink'></div>");
            }
            var ink = rippler.find(".ink");
            ink.removeClass("animate");
            if (!ink.height() && !ink.width()) {
                var d = Math.max(rippler.outerWidth(), rippler.outerHeight());
                ink.css({
                    height: d,
                    width: d
                });

            }
            var x = e.pageX - rippler.offset().left - ink.width() / 2;
            var y = e.pageY - rippler.offset().top - ink.height() / 2;
            ink.css({
                top: y + 'px',
                left: x + 'px',
            }).addClass("animate");

            rippler.css({
                'z-index': 9999
            });
            rippler.animate({
                backgroundColor: "#FF6656",
                width: '100%'
            }, 750);

            $("#mimin-mobile .ink").on("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd",
                function(e) {
                    $(".sub-mimin-mobile-menu-list").show();
                    $("#mimin-mobile-menu-opener span").removeClass("fa-bars").addClass("fa-close").css({
                        "font-size": "2em"
                    });
                });
        } else {

            if (rippler.find(".ink").length == 0) {
                rippler.append("<div class='ink'></div>");
            }
            var ink = rippler.find(".ink");
            ink.removeClass("animate");
            if (!ink.height() && !ink.width()) {
                var d = Math.max(rippler.outerWidth(), rippler.outerHeight());
                ink.css({
                    height: d,
                    width: d
                });

            }
            var x = e.pageX - rippler.offset().left - ink.width() / 2;
            var y = e.pageY - rippler.offset().top - ink.height() / 2;
            ink.css({
                top: y + 'px',
                left: x + 'px',
            }).addClass("animate");
            rippler.animate({
                backgroundColor: "transparent",
                'z-index': '-1'
            }, 750);

            $("#mimin-mobile .ink").on("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd",
                function(e) {
                    $("#mimin-mobile-menu-opener span").removeClass("fa-close").addClass("fa-bars").css({
                        "font-size": "1em"
                    });
                    $(".sub-mimin-mobile-menu-list").hide();
                });
        }
    });



    $(".form-text").on("click", function() {
        $(this).before("<div class='ripple-form'></div>");
        $(".ripple-form").on("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd",
            function(e) {
                // do something here
                $(this).remove();
            });
    });

    $('.mail-wrapper').find('.mail-left').css('height', $('.mail-wrapper').innerHeight());
    $("#left-menu ul li a").ripple();
    $(".ripple div").ripple();
    $("#left-menu .sub-left-menu").niceScroll();

    $(".fileupload-v1-btn").on("click", function() {
        var wrapper = $(this).parent("span").parent("div");
        var path = wrapper.find($(".fileupload-v1-path"));
        $(".fileupload-v1-file").click();
        $(".fileupload-v1-file").on("change", function() {
            path.attr("placeholder", $(this).val());
            console.log(wrapper);
            console.log(path);
        });
    });

    $("body").tooltip({
        selector: '[data-toggle=tooltip]'
    });
    leftMenu();
    treeMenu();
    hide();
})(jQuery);

window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
$.src="//v2.zopim.com/?3PC3XA60lWz8HPyy7BkzGZoo5L1PUKUw";z.t=+new Date;$.
type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");