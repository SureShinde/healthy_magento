(function ( $ ){
    $.perficient = {


        // =============================================
        // Primary Break Points
        // =============================================

        // These should be used with the bp (max-width, xx) mixin
        // where a min-width is used, remember to +1 to break correctly
        // If these are changed, they must also be updated in _var.scss and app.js <-- Important

        bp : {
            xsmall: 479,
            small: 599,
            medium: 767,
            large: 1024,
            xlarge: 1096
        },

        init : function() {
            if($('body.cms-home').length){
                this.homeActions.init();
            }
            if($('.mobile-accordion').length){
                this.mobileaccordions.init();
            }
            if($('.tablet-accordion').length){
                this.tabletaccordions.init();
            }
            if($('body.catalog-category-view').length || $('.catalogsearch-result-index'.length)){
                this.categoryActions.init();
            }
            if($('.cart').length)
                this.cartBpMatch();

            this.toggleDisplays();
            this.selectArrow();
            this.moveMiniCartBpMatch();
            this.overlay.init();
            this.colorSwatch();

            if($('.bundle').length){
                this.bundleBpMatch();
                this.radioButtonGrid.init();
            }
        },
        homeActions : {
            init: function () {
                // Slide Show
                $('.flexslider').flexslider({
                    animation: "fade",
                    slideshowSpeed: 4000,
                    animationSpeed: 600,
                    prevText: '',
                    nextText: '',
                    controlsContainer: $(".custom-controls-container"),
                    customDirectionNav: $(".custom-navigation a"),

                });

                // Caring Connections Toggle
                var toggle = $('a.see-all-toggle');

                toggle.on('click', function (e) {
                    e.preventDefault();
                    var icon = $(this).children('span.icon').children('img').attr('src');
                    if ($(this).is('.open')) {
                        $(this).removeClass('open');
                        $(this).children('span.icon').children('img').attr('src', icon.replace('minus', 'plus'));
                        $('.caring-partners').removeClass('expanded');
                    } else {
                        $(this).addClass('open');
                        $(this).children('span.icon').children('img').attr('src', icon.replace('plus', 'minus'));
                        $('.caring-partners').addClass('expanded');
                    }
                });

                this.setNavContainerSize();
                this.clickableTipBox();

                $(window).resize(function() {
                    $.perficient.homeActions.setNavContainerSize();
                });
            },
            setNavContainerSize : function() {
                $('.custom-navigation').width($('.flex-active-slide .slide-content').width());
            },
            clickableTipBox : function() {
                enquire.register('screen and (max-width: ' + $.perficient.bp.large + 'px)', {
                    match: function () {
                        $('body.cms-home #tips li').each(function () {
                            if ($(this).find('.learn-more').length) {
                                var target = $(this).find('.learn-more').parent().attr('href');

                                $(this).addClass('clickable');
                                $(this).bind("click", function () {
                                    window.location.href = target;
                                });
                            }
                        });
                    }, unmatch : function() {
                        $('body.cms-home #tips li.clickable').unbind();
                        $('body.cms-home #tips li.clickable').removeClass("clickable");
                    }
                });
            }
        },
        mobileaccordions : {

            accordions : null,
            headings : null,
            lists : null,

            init : function(){
                this.accordions = $('.mobile-accordion');
                this.headings = this.accordions.find('.accordion-heading');
                this.lists = this.accordions.find('.accordion-content');
                this.bpMatch();
            },
            bpMatch : function(){
                enquire.register('screen and (max-width: ' + $.perficient.bp.medium + 'px)', {
                    match: function () {

                        var directionalIcons = '<span class="gri-icon"><img src="/skin/frontend/perficient/default/images/close.png" alt="Close" /></span>';

                        $.perficient.mobileaccordions.accordions.addClass('mobile-view');
                        $.perficient.mobileaccordions.lists.hide();
                        $.perficient.mobileaccordions.headings.append(directionalIcons);

                        $.perficient.mobileaccordions.headings.click(function(e){
                            e.preventDefault();
                            if(!$(this).is('.open')){
                                $.perficient.mobileaccordions.headings.removeClass('open');
                                $.perficient.mobileaccordions.lists.hide();
                                $(this).addClass('open');
                                $(this).siblings('.accordion-content').slideDown();
                            } else {
                                $(this).removeClass('open');
                                $(this).siblings('.accordion-content').slideUp();
                            }

                        });

                    },

                    unmatch: function () {
                        $.perficient.mobileaccordions.headings.unbind('click');
                        $.perficient.mobileaccordions.headings.find('span.gri-icon').remove();
                        $.perficient.mobileaccordions.accordions.removeClass('mobile-view');
                        $.perficient.mobileaccordions.lists.show();
                    }
                });

            }

        },
        tabletaccordions : {

            accordions : null,
            headings : null,
            lists : null,

            init : function(){
                this.accordions = $('.tablet-accordion');
                this.headings = this.accordions.find('.accordion-heading');
                this.lists = this.accordions.find('.accordion-content');
                this.bpMatch();
            },
            bpMatch : function(){

                enquire.register('screen and (max-width: ' + $.perficient.bp.large + 'px)', {
                    match: function () {

                        var directionalIcons = '<span class="gri-icon"><img src="/skin/frontend/perficient/default/images/close.png" alt="Close" /></span>';

                        $.perficient.tabletaccordions.accordions.addClass('tablet-view');
                        $.perficient.tabletaccordions.lists.hide();
                        $.perficient.tabletaccordions.headings.append(directionalIcons);

                        $.perficient.tabletaccordions.headings.click(function(e){
                            e.preventDefault();
                            if(!$(this).is('.open')){
                                $.perficient.tabletaccordions.headings.removeClass('open');
                                $.perficient.tabletaccordions.lists.hide();
                                $(this).addClass('open');
                                $(this).siblings('.accordion-content').slideDown();
                            } else {
                                $(this).removeClass('open');
                                $(this).siblings('.accordion-content').slideUp();
                            }

                        });
                    },
                    unmatch: function () {
                        $.perficient.tabletaccordions.headings.unbind('click');
                        $.perficient.tabletaccordions.headings.find('span.gri-icon').remove();
                        $.perficient.tabletaccordions.accordions.removeClass('tablet-view');
                        $.perficient.tabletaccordions.lists.show();
                    }
                });

            }

        },
        toggleDisplays : function() {
            $('.flag-icon').click(function(e){
                $(this).next('.toggle-trigger').trigger('click');
            });
            $('.toggle-trigger').on('click', function(e){
                e.preventDefault();

                var target = $(this).attr('href');

                var oldTrigger = $('.toggle-trigger.opened');
                var oldTarget = oldTrigger.attr('href');

                if($(this).is('.opened')){
                    $(this).removeClass('opened');
                    $(target).hide();
                } else {
                    $(this).addClass('opened');
                    $(target).show();

                    $(oldTrigger).removeClass('opened');
                    $(oldTarget).hide();
                }
            });

            // Close Search
            $('a.close-search').on('click', function(e){
                e.preventDefault();
                $('#header-search').hide();
                $('li.search-toggle a').removeClass('opened');
                $('#search').val('');
            });

        },
        moveMiniCartBpMatch : function(){

            enquire.register('screen and (max-width: ' + $.perficient.bp.medium + 'px)', {
                match: function () {

                    $('#header-cart').addClass('header-minicart');
                    $('.page-header-container').append($('#header-cart'));

                },
                unmatch: function () {

                    $('#header-cart').removeClass('header-minicart');
                    $('.account-cart-wrapper .header-minicart').append($('#header-cart'));
                }
            });

        },

        cartBpMatch: function(){
            enquire.register('screen and (max-width: ' + $j.perficient.bp.large + 'px)', {
                match: function () {
                    //cart

                    $j('.cart tbody tr').each(function(){
                        var cartMobileELements = $j(this).children().slice(2, 4);
                        $j(this).find('.links').after(cartMobileELements);
                    })
                },
                unmatch: function () {
                    //cart

                    $j('.cart tbody tr').each(function(){
                        var cartMobileElements = $j(this).children('.description').children('td');
                        $j(this).children('.subtotal').before(cartMobileElements);
                    })
                }
            })
        },

        checkoutBpMatch: function () {
            enquire.register('screen and (max-width: ' + $j.perficient.bp.medium + 'px)', {
                match: function () {
                    //checkout
                    var qty = '';
                    var price = '';
                    var subtotal = '';
                    var mobileEle = '';

                    $j('#checkout-review-table tbody tr').each(function () {
                        qty = $j(this).find('td').eq(2).text();
                        price = $j(this).find('td').eq(1).text();
                        subtotal = $j(this).find('td').eq(3).text();

                        mobileEle = '<dl class="mobile-element">';
                        mobileEle += '<dt>Price:</dt><dd>' + price + '(' + qty + ')</dd>'; //price
                        mobileEle += '<dt class="subtotal">Subtotal:</dt><dd class="subtotal">' + subtotal + '(' + qty + ')</dd></div>'; //subtotal
                        $j(this).find('td').eq(0).find('.product-name').after(mobileEle);
                        $j(this).find('td').eq(0).attr('colspan','4');
                    })
                },
                unmatch: function () {
                    //checkout
                    $j('#checkout-review-table .mobile-element').remove();
                    $j('#checkout-review-table tbody tr td').removeAttr('colspan');
                }
            })
        },

        bundleBpMatch : function(){
            //mobile
            enquire.register('screen and (max-width: ' + $.perficient.bp.medium + 'px)', {
                match: function () {
                    $('.product-shop-before').find('.left .product-name').after($('.product-shop-before').find('.right'));
                    $('.right .add-to-cart').before($('.product-img-box').clone().addClass('cloned'));
                    $(document).ready(function(){
                        $('.engraving-right').each(function() {
                            $(this).prev().before($(this));
                        })
                    })
                },

                unmatch : function(){
                    $(document).ready(function() {
                        $('.engraving-right').each(function () {
                            $(this).next().after($(this));
                        })
                    })
                    $j('.product-img-box.cloned').remove();
                    $('.product-shop-before').append($('.product-shop-before .right'));
                }
            });
        },

        radioButtonGrid : {

            grids : null,

            init : function(){
                this.grids = $('#product-options-wrapper > dl > dd:not(.myihr_option_container) .options-list');
                this.visibleChildren();
                this.bpMatch();
            },

            visibleChildren : function(){
                this.grids.children().filter(':visible').addClass('visible');
            },

            adjustGridLayout : function(container){
                container -= 50;
                this.grids.each(function(){
                    var childWidth = $(this).children().filter(':visible').width();
                    var gap = 10;
                    if($(this).children().filter(':visible').length < container/(childWidth + gap)){
                        $(this).addClass('center');
                    }else{
                        $(this).removeClass('center');
                    }
                })
            },

            bpMatch : function(){

                enquire.register('screen and (min-width: 330px) and (max-width: 469px)', {
                    match: function () {
                        $.perficient.radioButtonGrid.adjustGridLayout(330);
                    }
                })

                enquire.register('screen and (min-width: 470px) and (max-width: 609px)', {
                    match: function () {
                        $.perficient.radioButtonGrid.adjustGridLayout(470);
                    }
                })

                enquire.register('screen and (min-width: 610px) and (max-width: 749px)', {
                    match: function () {
                        $.perficient.radioButtonGrid.adjustGridLayout(610);
                    }
                })

                enquire.register('screen and (min-width: 750px) and (max-width: '+ $.perficient.bp.medium +')', {
                    match: function () {
                        $.perficient.radioButtonGrid.adjustGridLayout(750);
                    }
                })

                enquire.register('screen and (min-width: '+ ($.perficient.bp.medium + 1) +'px)', {
                    match: function () {
                        $.perficient.radioButtonGrid.grids.removeClass('center');
                    }
                })
            }
        },

        categoryActions : {

            topAttr : null,
            closeFiltersElm : null,
            filters : null,

            init : function(){
                this.topAttr = $('#narrow-by-list').find('dt');
                this.closeFiltersElm = $('#close-filters');
                this.filters = $('#filters');
                this.moveCount();

                if(this.filters.find('div.currently').length){
                    this.moveFilterStates();
                }

                this.closeFiltersElm.on('click', function(e){
                    e.preventDefault();
                    $.perficient.categoryActions.closeFilters();
                });
                this.topAttr.on('click', function(e){
                    e.preventDefault();
                    if($(this).is('.open')) {
                        $.perficient.categoryActions.closeFilterList($(this));
                    } else {
                        $.perficient.categoryActions.openFilterList($(this));
                    }
                });
            },
            moveCount : function(){
                $('#count').appendTo('#count-holder');
            },
            moveFilterStates : function(){
                $('#filters div.actions').appendTo('#state-holder');
                $('#filters div.currently').appendTo('#state-holder');
                $('#state-holder').addClass('is-active');

                //adjust the position for the pop-up sort menu
                $('#sorter').addClass('up');
            },
            openFilterList : function(dtElm){
                dtElm.addClass('open');
                dtElm.next('dd').slideDown();


            },
            closeFilterList : function(dtElm){
                dtElm.removeClass('open');
                dtElm.next('dd').slideUp();
            },
            closeFilters : function(){
                this.filters.slideUp();
                $('a[href="#filters"]').removeClass('opened');
            }
        },
        overlay : {

            mask : null,
            overlayElm : null,
            trigger : null,
            timer : null,

            init : function(){
                this.mask = $('#mask');
                this.overlayElm = $('#overlay');
                this.trigger = $('.overlay-trigger');
                this.timer = 500;
                this.trigger.on('click', function(e){
                    e.preventDefault();
                    $.perficient.overlay.openOverlay($(this).attr('href'));
                });
                this.mask.on('click', function(e){
                    e.preventDefault();
                    $.perficient.overlay.closeOverlay();
                });
                this.overlayElm.children('#close-overlay').on('click', function(e){
                    e.preventDefault();
                    $.perficient.overlay.closeOverlay();
                });
                $(document).on('click','.close-overlay',function(){
                    $.perficient.overlay.closeOverlay();
                })
            },
            openOverlay : function(elmToOverlay){
                $(elmToOverlay).addClass('cloned-elm').clone(true).appendTo(this.overlayElm.children('#overlay-content'));
                this.mask.fadeIn(this.timer);
                this.overlayElm.fadeIn(this.timer);
            },
            closeOverlay : function(){
                this.mask.fadeOut(this.timer);
                this.overlayElm.fadeOut(this.timer);
                setTimeout(function(){
                    $.perficient.overlay.overlayElm.children('#overlay-content').empty();
                    $.perficient.overlay.overlayElm.children('.buttons-set').remove();
                }, $.perficient.overlay.timer + 100);
            }
        },
        colorSwatch : function(){
            if($('.product-info ul.configurable-swatch-list').hasClass('configurable-swatch-color')) {
                $('ul.configurable-swatch-list').parents('li.item').addClass('swatch-color-prod');
            }
            if($('ul.configurable-swatch-list.configurable-swatch-color li').hasClass('more')){
                $('ul.configurable-swatch-list.configurable-swatch-color').addClass('more-link');
            }
            enquire.register('screen and (max-width: '+ ($.perficient.bp.large + 1) +'px)', {
                    match: function () {
                    $('li.item.swatch-color-prod').each(function(){
                        var prod_href = $(this).find('a.product-image').attr('href');
                        var liCount = $(this).find('.configurable-swatch-color').children('li').length;
                    if(liCount > 3 && liCount <= 5){
                        $('<li class="more" style="display: block"><a href="' +prod_href + '">More</a></li>').insertAfter('.configurable-swatch-color li:last-child');
                    }
                    })
                },
                unmatch : function(){
                    $('li.item.swatch-color-prod').each(function(){
                        var liCount = $(this).find('.configurable-swatch-color').children('li').length;
                        if(liCount > 3 && liCount <= 6){
                            $('.configurable-swatch-color li.more').remove();
                        }
                    })
                }
            })

        },
        selectArrow : function(){
            jQuery('<span class="select-arrow"></span>').insertAfter('.webforms-hear-about-us .form-list .type-select .input-box select');
        }

        };

    $(document).ready(function() {
        $.perficient.init();
    });

})(jQuery);

(function($) {
    $.fn.changeElementType = function(newType) {
        var attrs = {};

        $.each(this[0].attributes, function(idx, attr) {
            attrs[attr.nodeName] = attr.nodeValue;
        });

        this.replaceWith(function() {
            return $("<" + newType + "/>", attrs).append($(this).contents());
        });
    }
})(jQuery);