/*jslint browser: true, devel: true, sloppy: true */
/*global window*/
(function () {
    var jQuery,
        script_tag,
        main = function () {

            jQuery(function ($) {

                var $targets = $('.rebus-course-reading-list-widget'),
                    devEnv = window.location.search.indexOf('env=dev') !== -1,
                    baseUrl = devEnv ? '/widgets' : '//www.swan.ac.uk/widgets',
                    styleSheet = baseUrl + '/css/rebus/course-reading-list.css',
                    isRefresh = ($('.rebus-course-reading-list-widget').attr('data-refresh') !== undefined) ? true : false;

                if (false === isRefresh) {
                    var style = (document.createStyleSheet) ? document.createStyleSheet(styleSheet)
                        : $('head').append($('<link>', {
                            rel:  'stylesheet',
                            type: 'text/css',
                            href: styleSheet
                        }));
                }

                // Do we need html5shiv?
                if ($.browser.msie && $.browser.version < 8) {
                    script_tag = document.createElement('script');
                    script_tag.setAttribute("type", "text/javascript");
                    script_tag.setAttribute("src",  baseUrl + "widgets/vendor/html5shiv/html5shiv.js");
                    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
                }
                
                // Set Up List Functions
                function local(labels) {
                    var role = $('.rebus-course-reading-list-widget').attr('data-role'),
                        sortFunc = function (a, b) {
                            return a.strMod > b.strMod;
                        },
                        dedupe = function (ar) {
                            var obj = {}, temp = [], i, item;
                            for (i = 0; i < ar.length; i = i + 1) {
                                obj[ar[i].strMod] = ar[i];
                            }
                            for (item in obj) {
                                if (obj.hasOwnProperty(item)) {
                                    temp.push(obj[item]);
                                }
                            }
                            temp.sort(sortFunc);
                            return temp;
                        },
                        reStyleList = function (filter, $list) {
                            var filterClass = "." + filter;
                            if (filter === "*") {
                                $list.children().addClass("item");
                                $(".ui-icon-triangle-1-s", $list).each(function () {
                                    var catClass = "." + $(this).parent().parent().attr("data-category");
                                    $(catClass).show();
                                });
                            } else {
                                $list.children(":not(" + filterClass + ", .header, .options)").hide().removeClass("item");
                                $(filterClass, $list).addClass("item").show();

                                $(filterClass + ".item", $list).each(function () {
                                    var catClass = $(this).attr("data-incategory");
                                    $("div[data-category='" + catClass + "']").children("h4").children("span:not(.ui-icon-triangle-1-s)").addClass("ui-icon-triangle-1-s");
                                    $(".rebus-course-reading-list-widget .options." + catClass).show();
                                });
                                $(".header", $list).show();
                            }
                        };

                    $('.rebus-course-reading-list-widget .list').each(function () {
    
                        var $list = $(this), $listHeader = $(this).prev(), tags = [], uniqueTags, tagList,
                        $firstCat = $('.header:eq(0)', $list), firstCatClass = "." + $firstCat.attr("data-category");
    
                        $(".item", $list).each(function () {
                            var item = this;
                            $(".tag", this).each(function () {
                                var str = $(this).text().replace(/^\s+|\s+$/g, ''),
                                    strMod = str.replace(/[^\w\s]|_/g, "").replace(/\s+/g, "");
                                $(item).addClass(strMod);
                                tags.push({str: str, strMod: strMod});
                            });
                        });
    
                        $("h4", $list).css({cursor : "pointer"}).click(function () {
                            var cat = $(this).parent().attr("data-category");
                            $("span", this).toggleClass("ui-icon-triangle-1-s");
                            $('.item.' + cat, $list).animate({height : 'toggle'});
                            return false;
                        });
    
                        uniqueTags = dedupe(tags);
                        if (uniqueTags.length > 0) {
                            tagList = '<div class="tagList toggle"><dl>';
                            $(uniqueTags).each(function () {
                                tagList += '<dt><a href="" class="filter ' + this.strMod + '">' + this.str + '</a></dt>';
                            });
                            tagList += '</dl></div>';

                            $(".controls", $listHeader).prepend(
                                '<a href="#" class="btn toggle showAll">' + labels.label_Remove_Filters + '</a>' +
                                    '<a href="#" class="btn filterList"><img alt="Tag" src="' + baseUrl + '/images/rebus/icon_tags.png">&nbsp;' + labels.label_Filters + '</a>' +
                                    tagList
                            );
    
                            $(".header", $list).each(function () {
                                var $header = $(this);
                                $(this).after(
                                    '<div class="item options ' + $header.attr("data-category") + '">' +
                                        '<a href="#" class="toggle showAll">' + labels.label_Remove_Filters + '</a>' +
                                    '</div>'
                                );
                            });
    
                            $(".filter", $listHeader).click(function () {
                                var str = $(this).text();
                                str = str.replace(/[^\w\s]|_/g, "").replace(/\s+/g, "");
                                reStyleList(str, $list);
                                $(".showAll", $listHeader).css({opactity: '0', display : 'inline-block'}).animate({opacity: '1'});
                                $(".showAll", $list).css({opactity: '0', display : 'block'}).animate({opacity: '1'});
                                $(".tagList", $listHeader).hide();
                                return false;
                            });
                            
                            $(".tag", $list).click(function () {
                                var str = $(this).text();
                                str = str.replace(/[^\w\s]|_/g, "").replace(/\s+/g, "");
                                reStyleList(str, $list);
                                $(".showAll", $listHeader).css({opactity: '0', display : 'inline-block'}).animate({opacity: '1'});
                                $(".showAll", $list).css({opactity: '0', display : 'block'}).animate({opacity: '1'});
                                $(".tagList", $listHeader).hide();
                                return false;
                            });
    
                            $(".showAll", $list).click(function () {
                                reStyleList("*", $list);
                                $(".showAll", $list).animate({opacity: '0'}).hide();
                                $(".showAll", $listHeader).animate({opacity: '0'}).hide();
                                return false;
                            });
                            
                            $(".showAll", $listHeader).click(function () {
                                reStyleList("*", $list);
                                $(".showAll", $list).animate({opacity: '0'}).hide();
                                $(".showAll", $listHeader).animate({opacity: '0'}).hide();
                                return false;
                            });

                            $(".filterList", $listHeader).click(function () {
                                var $tagList =  $(this).parent().children('.tagList'),
                                    offset = $(this).offset(),
                                    left = (offset.left + $(this).outerWidth()) - $tagList.outerWidth(),
                                    top = (offset.top + $(this).outerHeight() - 4);
                                $tagList.css({left: left, top :  top}).animate({height: 'toggle'});
                                return false;
                            });
                        }
                        $firstCat.children("h4").children("span").addClass("ui-icon-triangle-1-s");
                        $list.children(":not(" + firstCatClass + ", .header)").animate({height : 'toggle'});
                    });
                    if (role === 'STUDENT') {
                        $('.rebus-course-reading-list-widget .controls .admin').remove();
                    }
                    $('.rebus-course-reading-list-widget .controls').append('<a class="btn refresh" href="#">' + labels.label_Refresh + '</a>');
                    
                    $('.rebus-course-reading-list-widget .controls .refresh').click(function (e) {
                        e.stopPropagation();
                        $('.rebus-course-reading-list-widget').html('<div class="loading"></div>').attr("data-refresh", "true");
                        setTimeout(function () { 
                            $.getScript($('.rebus-course-reading-list-widget').prev().attr('src'));
                        }, 2000);
                        return false;
                    });
                }

                $targets.each(function () {
                    var $this = $(this),
                        course = $this.attr('data-course'),
                        locale = (undefined !== $this.attr('data-locale')) ? $this.attr('data-locale') : "en-gb",
                        uri = baseUrl + '/controllers/rebus/course-reading-list.php?callback=?&course=' + course + '&locale=' + locale;
                    
                    $.getJSON(uri, function (data) {
                        $this.html(data.html);
                        local(data.labels);
                    });
                });
            });
        },
        scriptLoadHandler = function () {
            jQuery = window.jQuery.noConflict(true);
            main();
        };

    if (window.jQuery === undefined || window.jQuery.fn.jquery !== '1.5.2') {
        script_tag = document.createElement('script');
        script_tag.setAttribute("type", "text/javascript");
        script_tag.setAttribute("src", "//ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js");

        if (script_tag.readyState) { // old-IE
            script_tag.onreadystatechange = function () {
                if (this.readyState === 'complete' || this.readyState === 'loaded') {
                    scriptLoadHandler();
                }
            };
        } else {
            script_tag.onload = scriptLoadHandler;
        }

        (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
    } else {
        jQuery = window.jQuery;
        main();
    }
}());