var amid = {

    init: function () {
        this.browserDetection();

        if ( $j( "#product-options-wrapper" ).length ) {
            this.productOptionRadios.init();

            if($j('.product-view.bundle').length){
                this.stickyHeader.init();
                this.cartButton.init();
            }

            if ($j('.engraving_group_options').length) {
                this.engraving.init();
            }

            if($j('.myihr_option_container').length){
                this.myihr.init();
                this.learnmore.init();
            }
        }
        if ($j('#sorter').length) {
            this.sort.init();
        }
        if ($j('.toggle-area').length) {
            this.toggle.init();
        }
        if('.tab-area'){
            this.tab.init();
        }
        if ($j('.product-videos').length) {
            this.youtubeEmbed.init();
        }
        if ($j('.price-box').length) {
            this.pricebox.init();
        }
        if( $j('.product-view.bundle').length){
            this.bundleQty.init();
        }
        if ($j('.toggle-all').length) {
            this.collapse.init();
        }
        if ($j('.q-mark').length) {
            this.qmark.init();
        }
        if ($j('.customer-account').length) {
            this.account.init();
        }
        if ($j('#checkout-progress-wrapper').length) {
            this.checkoutProcess.init();
        }
        if ($j('#shipping-method-estimated-arrival-date-container').length) {
            this.arrival.init();
        }
        if ($j('header').length) {
            this.header.init();
        }
        if ($j('#engraving-controls').length) {
            this.engravingSuggestions();
        }
        if($j('.flexslider').length) {
            this.slider.init();
        }
        if($j('.product-options .configurable-swatch-list li.selected').length) {
            this.engravingPlates.init();
        }
    },
    account : {

        init : function(){
            this.bpMatch();
        },

        bpMatch : function() {
            enquire.register('screen and (max-width: ' + $j.perficient.bp.large + 'px)', {
                match: function () {
                    $j('.block-title').on('click',function () {
                        //$j('.block-content').toggle();
                        $j(this).toggleClass("up-arrow");
                    });
                },
                unmatch: function () {
                    $j('.block-account .block-title').off('click');
                    //$j('.block-account .block-content').show();
                }
            });
        }
    },

    browserDetection: function () {
        if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Mac') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
            $j('body').addClass('safari-mac');
        }

        var ms_ie = false;
        var ua = window.navigator.userAgent;
        var old_ie = ua.indexOf('MSIE ');
        var new_ie = ua.indexOf('Trident/');

        if ((old_ie > -1) || (new_ie > -1)) {
            ms_ie = true;
        }

        if (ms_ie) {
            $j('body').addClass('ie');
        }
    },

    header : {
        nav : null,

        init : function(){
            this.nav = $j('#nav');

            $j('#mobile-contact .email-signup').bind('click',function(){
                $j('.subscribe-container .block-title').removeClass('open');
                $j('.subscribe-container .block-title').trigger('click');
            });

            this.bpMatch();
        },

        bpMatch : function() {
            enquire.register('screen and (max-width: ' + $j.perficient.bp.medium + 'px)', {
                match: function () {
                    $j('#header-search').css('display','');
                },

                unmatch: function () {
                    $j("#mobile-account-link").remove();
                }
            });
        }
    },

    tips : {

        blocks : null,

        init : function(){
            this.blocks = $j('#tips').find('ul').children('li');
        }
    },

    engraving : {
        engravingContainer : null,
        engravingText : [],
        engravingDataFields : null,
        textFields : null,
        engravingRadios : null,
        engravingRadio : null,
        plates : null,
        groupTitles : null,
        engravingTextFields : null,
        activePlateSize : null,
        addToCartForm : null,

        init : function(){
            this.addToCartForm = $j('#product_addtocart_form');
            this.engravingContainer = this.addToCartForm.find('.engraving_group_options');
            this.engravingDataFields = this.engravingContainer.find('input.engraving-data');
            this.textFields = this.engravingContainer.find('input.engraving-field');
            this.engravingTextFields = this.textFields;
            this.engravingRadios = this.engravingContainer.find('input[data-option="engrave_this_item"]');
            this.engravingRadio = this.engravingRadios.filter('input[data-option-value="yes"]');
            this.plates = this.engravingContainer.find('input[data-option*="_size"]');
            this.groupTitles = $j('dt.front_group_title, dt.back_group_title');

            this.engravingRadios.change(function(e) {
                amid.engraving.setHasEngraving($j(this));
                amid.engraving.toggleEngravingValidation();
                amid.engraving.toggleSpellcheckBtn(e);
            });

            this.plates.change(function(e) {
                if (amid.engraving.getHasEngraving()) {
                    var plateSize = $j(this).attr('data-option-value');
                    if (plateSize && amid.engraving.activePlateSize !== plateSize) {
                        amid.engraving.toggleEngravingFields(plateSize);
                    }
                }
            });

            this.engravingRadio.change(function(e) {
                if (amid.engraving.plates.length) {
                    var plateSize = amid.engraving.getPlateSize();
                    if (plateSize && amid.engraving.activePlateSize !== plateSize) {
                        amid.engraving.toggleEngravingFields(plateSize);
                    }
                }else{
                    amid.engraving.activateEngravingFields(amid.engraving.engravingTextFields);
                }
            });

            if (this.engravingRadio.length && this.engravingRadio[0].checked) {
                amid.engraving.setHasEngraving(this.engravingRadio);
                this.engravingRadio.trigger('change');
            }

            if(this.textFields.length){

                if (this.plates.length) {
                    // default to small selection
                    var plateSize = this.getPlateSize('small');
                    if (plateSize && amid.engraving.activePlateSize !== plateSize) {
                        this.toggleEngravingFields(plateSize);
                    }
                }

                // Check each field for initial text
                $j.each( this.textFields, function( key, value ) {
                    "use strict";

                    amid.engraving.characterCountInitial( value );
                });
                this.simulation.generateEngravingDisplay();
                this.textFields.on('keyup blur', function(){
                    amid.engraving.characterCount(this);
                    amid.engraving.simulation.displayEngravingText(this);
                });

            }

            this.toggleEngravingValidation();
            this.repopulateEngraving();

            $j(document).on('click','.display-suggestion',function(){
                amid.engraving.displaySuggestion($j(this));
            });

            $j(document).on('click','#overlay .btn-cart',function(){
                productAddToCartForm.submit(this,'',true);
            });

            $j(document).on('click','.show-valid-characters',function(e){
                e.preventDefault();
                if($j(this).html() == 'Show Valid Characters'){
                    $j(this).html('Hide Valid Characters');
                    $j('#overlay #valid-characters').show();
                }else {
                    $j(this).html('Show Valid Characters');
                    $j('#overlay #valid-characters').hide();
                }
            });

            $j('#spellcheck-trigger').click(function(){
                amid.engraving.customizeLightbox();
            });

            $j(document).on('click','.have-suggestion td a',function(e){
                e.preventDefault();
            })
        },
        toggleEngravingFields : function(plateSize) {
            if (plateSize && amid.engraving.getHasEngraving()) {
                amid.engraving.hideEngravingFields();
                amid.engraving.showEngravingFields(plateSize);
            }
        },
        showEngravingFields : function(plateSize) {
            if (!plateSize) { return; }
            var fields = amid.engraving.textFields.filter('input[data-plate-size="'+plateSize+'"]');
            amid.engraving.activateEngravingFields(fields);
            var container = fields.closest('.inner_group_options').parent().show();
            var title = container.prev().show();
        },

        hideEngravingFields : function() {
            amid.engraving.deactivateEngravingFields();
            var fieldGroups = amid.engraving.textFields.closest('.inner_group_options').parent().hide();
            fieldGroups.prev().hide();
        },
        activateEngravingFields : function(textFields) {
            amid.engraving.engravingTextFields = textFields;
            textFields
                .removeAttr('disabled')
                .addClass('active-engraving')
                .bind('keyup', amid.engraving.engravingTextHandler)
                .bind('blur', amid.engraving.engravingTextHandler);

            amid.engraving.addEngravingValidation(textFields);
            amid.engraving.buildEngravingTextDataContainer(textFields);
            amid.engraving.simulation.generateEngravingDisplay();
            textFields.closest('form').trigger('change');
        },
        deactivateEngravingFields : function() {
            amid.engraving.engravingTextFields = null;
            amid.engraving.textFields
                .attr('disabled', 'disabled')
                .removeClass('active-engraving')
                .unbind('keyup', amid.engraving.engravingTextHandler)
                .unbind('blur', amid.engraving.engravingTextHandler)
                .val('')
                .trigger('keyup');
            amid.engraving.engravingText = [];
            amid.engraving.removeEngravingValidation();
        },
        customizeLightbox : function(addToCart){
            var overlay = $j('#overlay');
            overlay.find('h4.title').html('Spell Check');
            overlay.append('<div class="buttons-set"></div>');
            if(addToCart){
                overlay.find('.buttons-set').append('<button type="button" class="button btn-cart"><span><span>Continue to Cart</span></span></button>')
            }else{
                overlay.find('.buttons-set').append('<button type="button" class="button close-overlay"><span><span>Close Spell Check</span></span></button>');
            }
            $j('.validation-notice').remove();
        },
        toggleEngravingValidation : function() {
            if (amid.engraving.getHasEngraving()) {
                amid.engraving.addEngravingValidation();
            }
            else {
                amid.engraving.removeEngravingValidation();
            }
        },
        addEngravingValidation : function(fields) {
            fields = fields ? fields : amid.engraving.textFields.filter('.active-engraving');
            fields.addClass('engraving-text-one');
        },
        removeEngravingValidation : function(fields) {
            fields = fields ? fields : amid.engraving.textFields;
            fields.removeClass('engraving-text-one');
            amid.engraving.resetValidation(fields);
        },
        setHasEngraving : function(radio) {
            var hasEngraving = radio.attr('data-option-value').trim().toLowerCase() == 'yes';
            if (hasEngraving) {
                amid.engraving.addToCartForm.addClass('has-engraving');
            }
            else {
                amid.engraving.addToCartForm.removeClass('has-engraving');
            }
        },
        getHasEngraving : function() {
            return amid.engraving.addToCartForm.hasClass('has-engraving');
        },
        getPlateSize : function(defaultSize) {
            var plateSize = defaultSize ? defaultSize : null;
            amid.engraving.plates.each(function (i) {
                if (this.checked) {
                    plateSize = $j(this).attr('data-option-value');
                    return false;
                }
            });
            return plateSize;
        },
        resetValidation : function(inputs) {
            // Clear validation messages
            for ( var i = 0; i < inputs.length; i++ ) {
                Validation.reset( inputs[i] );
            }
        },
        toggleSpellcheckBtn : function() {
            if (amid.engraving.getHasEngraving()) {
                $j('#spellcheck-trigger').show();
            }
            else {
                $j('#spellcheck-trigger').hide();
            }
        },
        characterCount : function(elm){
            var input = $j(elm);
            var maxCount = parseInt(input.attr('maxlength'));
            // Set remaining character count
            input.siblings( "p.note" ).text( maxCount - input.val().length );
        },
        // Duplicate of method above that allows passing of parameter when this isn't available
        characterCountInitial : function ( value ) {
            "use strict";

            // Get input from value
            var input = $j( value );

            // Get maxlength from input
            var maxCount = parseInt( input.attr( "maxlength" ) );

            // Set remaining characters text
            input.siblings( "p.note" ).text( maxCount - input.val().length );

        },

        engravingTextHandler : function(event){
            var txtElm = $j(event.target||event.srcElement);
            var location = txtElm.attr('data-line-location');
            var index = txtElm.attr('data-line-index')*1;
            amid.engraving.engravingText[location][index] = txtElm.val();
            amid.engraving.setEngravingText();
        },
        setEngravingText : function(){
            for (var location in amid.engraving.engravingText) {
                if (amid.engraving.engravingText.hasOwnProperty(location)) {
                    var engravingDataField = amid.engraving.engravingDataFields.filter('[data-engraving-location="' + location + '"]');
                    var engravingData = amid.engraving.engravingText[location].join('~');
                    engravingDataField.val(engravingData).trigger('keyup');
                }
            }
        },
        buildEngravingTextDataContainer : function(textFields) {

            textFields.each(function(i) {
                var txtElm = $j(this);
                var index = txtElm.attr('data-line-index')*1;
                var location = txtElm.attr('data-line-location');

                if (amid.engraving.engravingText.hasOwnProperty(location)) {

                    if (amid.engraving.engravingText[location].hasOwnProperty(index)) {
                        amid.engraving.engravingText[location] = [];
                        amid.engraving.engravingText[location][index] = "";
                    }
                    else {
                        amid.engraving.engravingText[location][index] = "";
                    }
                }
                else {
                    amid.engraving.engravingText[location] = [];
                    amid.engraving.engravingText[location][index] = "";
                }

            });
        },
        repopulateEngraving : function() {
            var textFields = amid.engraving.textFields.filter('input.active-engraving');
            amid.engraving.buildEngravingTextDataContainer(textFields);
            amid.engraving.engravingDataFields.each(function(i) {

                var dataField = $j(this);
                var engravingValue = dataField.val();

                if (!engravingValue) {
                    return true;
                }

                var dataLocation = dataField.attr('data-engraving-location');
                var engravingData = engravingValue.split('~');

                for (var index=0; index < engravingData.length; index++) {
                    amid.engraving.engravingText[dataLocation] = engravingData;
                }
            });

            textFields.each(function(i) {
                var txtElm = $j(this);
                var index = txtElm.attr('data-line-index')*1;
                var location = txtElm.attr('data-line-location');

                if (amid.engraving.engravingText.hasOwnProperty(location)) {

                    if (amid.engraving.engravingText[location].hasOwnProperty(index)) {
                        txtElm.val(amid.engraving.engravingText[location][index]).trigger('blur');
                    }
                }
            });

            textFields.closest('form').trigger('change');
        },

        simulation : {
            displayEngravingText : function(field){
                field = $j(field);
                var location = field.attr('data-line-location');
                var index = field.attr('data-line-index');
                var plateSize = field.attr('data-plate-size');
                $j('span#' + plateSize + '-' + location + '-' + index).text(field.val());
            },
            generateEngravingDisplay : function(){
                $j('.engraving-right').remove();
                amid.engraving.groupTitles.each(function(i) {
                    var dt = $j(this);
                    var title = dt.find('label').text().toLowerCase().replace(/engraving\s*/, '');
                    var plateImgUrl = _product[title+"PlateImage"];
                    var engravingFontColor = _product.engravingFontColor;
                    var display = $j('<span>', {class: 'engraving-'+title+'-display'});

                    if (engravingFontColor) {
                        display.css({color: engravingFontColor});
                    }
                    dt.next().prepend('<div class="engraving-right"><div class="engraving-simulation"></div></div>');
                    dt.next().find('.engraving-simulation').append(display);
                    if (plateImgUrl) {
                        dt.next().find('.engraving-simulation').append('<div class="plate-image"><img src="' + plateImgUrl + '" alt="Plate Image" /></div>');
                    }
                    amid.engraving.simulation.adjustPosition(title);
                });

                amid.engraving.engravingTextFields.each( function(i)
                {
                    var field = $j(this);
                    var lineLocation = field.attr('data-line-location');
                    var index = field.attr('data-line-index');
                    var plateSize = field.attr('data-plate-size');
                    var elmBuild = $j('<span>', {class: 'engraving-field ' + lineLocation, id: plateSize + '-' + lineLocation + '-' + index});
                    $j('.engraving-'+lineLocation+'-display').append(elmBuild);
                });
                amid.engraving.simulation.scalePlate();

            },
            scalePlate : function(){
                var engravingScalingFactor;
                var plateSize;

                if (_product.engravingScaleFactors && _product.engravingScaleFactors !== "[]") {
                    var scaleFactors = $j.parseJSON(_product.engravingScaleFactors);
                    if(amid.engraving.getPlateSize() != null){
                        plateSize = amid.engraving.getPlateSize();
                        engravingScalingFactor = scaleFactors[plateSize];
                    }else{
                        if($j('input[data-option="id_tag_size"]').length){
                            engravingScalingFactor = scaleFactors[Object.keys(scaleFactors)[0]];
                        }else{
                            engravingScalingFactor = scaleFactors[0];
                        }
                    }
                    $j('.engraving-simulation').css('width',engravingScalingFactor + '%');
                }
            },
            adjustPosition : function(plateLocation){
                var engravingScalingFactor = 100;
                var plateSize;

                if (_product.engravingOffsets && _product.engravingOffsets !== "[]") {
                    var engravingOffsets = $j.parseJSON(_product.engravingOffsets);
                    var scaleFactors = $j.parseJSON(_product.engravingScaleFactors);
                    var engravingOffsetX;
                    var engravingOffsetY;
                    var sideSelector;

                    if(amid.engraving.getPlateSize() != null){
                        plateSize = amid.engraving.getPlateSize();
                        if (scaleFactors.length && scaleFactors.hasOwnProperty(plateSize)) {
                            engravingScalingFactor = scaleFactors[plateSize];
                        }
                    }
                    else if(scaleFactors.length && scaleFactors.hasOwnProperty(0)) {
                            engravingScalingFactor = scaleFactors[0];
                    }

                    if($j('.engraving-list').length > 1){
                        sideSelector = '.engraving-simulation > span.engraving-' + plateLocation + '-display';
                    }else{
                        sideSelector = '.engraving-simulation > span';
                        plateLocation = 0; //only one side
                    }

                    if (engravingOffsets.hasOwnProperty(plateLocation)) {
                        var offsetStyles = {};
                        if (engravingOffsets[plateLocation].hasOwnProperty('x')) {
                            engravingOffsetX = engravingOffsets[plateLocation]['x'];
                            var offsetX = engravingOffsetX * engravingScalingFactor * 0.01 + 'px';
                            offsetStyles['margin-left'] = offsetX;
                        }
                        if (engravingOffsets[plateLocation].hasOwnProperty('y')) {
                            engravingOffsetY = engravingOffsets[plateLocation]['y'];
                            var offsetY = engravingOffsetY * engravingScalingFactor * 0.01 + 'px';
                            offsetStyles['margin-top'] = offsetY;
                        }
                        $j(sideSelector).css(offsetStyles);
                    }
                }
            }
        },

        // Function(s) to get spelling suggestions
        spellings : function() {
            "use strict";

            // Call AJAX function
            amid.engraving.spellingsAjax( false );
        },
        // Function to intercept Add to Cart
        spellingsAddToCart : function( object ) {
            "use strict";

            // Call AJAX function
            amid.engraving.spellingsAjax( object );
        },
        spellingsAjax : function( addToCart ) {
            "use strict";

            // Abort pending requests
            if (request) {
                request.abort();
            }

            // Get form
            var $form = $j( "#product_addtocart_form" );

            // Get inputs
            var $inputs = $form.find( "button" );

            // Get engraving field data
            var data = $form.find("input.active-engraving").serialize();

            // Disable inputs, avoid duplicate submission
            $inputs.prop( "disabled", true );

            // Send request
            request = $j.ajax({
                url: "/lexicon/lexicon/check",
                type: "post",
                data: data,
                beforeSend: function() {
                    $j( ".spellcheck-results" ).html( '' );
                }
            });

            // On success
            request.done(function( response ) {
                // Check response
                if ( response.status === "success" ) {
                    amid.engraving.spellingsResults( response.data, addToCart );
                } else if ( response.status === "failure" ) {
                    $j( ".spellcheck-message" ).html( "<p>" + response.data + "</p>" );
                } else if ( response.status === "error" ) {
                    $j( ".spellcheck-message" ).html( "<p>Error</p>" );
                } else {
                    alert( "Error!" );
                }
            });

            // On failure
            request.fail(function( jqXHR, textStatus ) {
                alert( "Request failed: " + textStatus );
            });

            // Always
            request.always(function() {
                // Re-enable inputs
                $inputs.prop( "disabled", false );
            });
        },
        spellingsResults : function( data, addToCart ) {
            "use strict";
            $j( ".spellcheck-results" ).html( data.html );

            $j( "span.validation-invalid-characters" ).each(function(i) {
                amid.engraving.spellingReplace($j(this));
            });

            // Check if add to cart
            if ( addToCart ) {
                amid.engraving.spellingsAddToCartProcess( data.spelling, addToCart );
            }
        },
        spellingReplace : function( tag ) {
            "use strict";
            // Replace text in inputs
            $j("input.active-engraving").val(function (index, value) {
                // If there is a value
                if (value) {
                    var txtElm = $j(this);
                    var line = txtElm.attr('data-line-index');
                    var location = txtElm.attr('data-line-location');
                    var maxLength = txtElm.attr('maxlength');

                    // Search and replace
                    if (tag.data("replace").length > maxLength)
                        txtElm.after('<div class="validation-notice">This line has been truncated to fit.</div>');

                    if((line == tag.data("line")) && (location == tag.data("location")) || tag.hasClass('validation-invalid-characters') ) {
                        return value.replace(tag.data("search"), tag.data("replace").substring(0, maxLength));
                    } else {
                        return value;
                    }
                }
            });
            amid.engraving.textFields.each(function(){
                amid.engraving.characterCount(this);
                $j(this).trigger('blur');
            });

            if(!tag.hasClass('validation-invalid-characters'))
                this.notification(tag);
        },
        // Method to process spellcheck results from Add to Cart click
        spellingsAddToCartProcess : function( spelling, object ) {
            "use strict";
            var passed = true;
            $j('.btn-cart').each(function(){
                if($j(this).text() == "Customize"){
                    passed = passed && false;
                }
            })
            // If spelling suggestions
            if ( spelling && passed) {
                // Display
                $j.perficient.overlay.openOverlay('#overlay-spellcheck');
                amid.engraving.customizeLightbox(true);
            } else {
                productAddToCartForm.submit( object,'',true);
            }
        },
        notification : function(tag){
            var userEntry = tag.parent().prev().find('.user-entry');
            var replaceText = '<td>You have changed <span class="user-entry">' + userEntry.html() + '</span> to <span class="suggestion">' + tag.html() + '</span> <a class="display-suggestion">Display Suggestion(s)</a> <span class="icon icon-websymbolsligaregular-152"></span></td>';
            tag.parent().after(replaceText);
            tag.parent().addClass('hidden');
            userEntry.html(tag.html());
        },

        displaySuggestion : function(ele){
            ele.parent().prev().removeClass('hidden');
            ele.parent().remove();
        }
    },

    productOptionRadios : {

        cartForm : null,
        radios : null,
        hasBundleOptions : false,
        bundleRadios : null,
        bundleRadioDivs : null,
        bundleOptionContainers : null,
        customOptionRadios : null,
        selectedBundleOptionProductIds : {
            productId:null,
            componentIds:[]
        },

        init : function(){
            this.cartForm = $j('#product_addtocart_form');
            this.radios = this.cartForm.find('input:radio');
            this.radios.change(function(){
                var radio = $j(this);
                amid.productOptionRadios.setSelectedOptionText(radio);
            });

            this.customOptionRadios = this.radios.filter('.product-custom-option');

            this.showChainsByLength.init();

            this.customOptionRadios.each(function(i) {
                var radio = $j(this);
                if (this.checked) {
                    radio.trigger('change');
                }
            });

            this.hasBundleOptions = $j('.product-view.bundle').length;

            if (this.hasBundleOptions) {
                this.bundleRadios = this.radios.filter('[name^="bundle_option"]');
                this.bundleRadioDivs = this.bundleRadios.closest('li').find('.bundledmojo_image_wrapper');
                this.bundleOptionContainers = this.cartForm.find('dd.bundle-option-container');
                this.selectedBundleOptionProductIds['productId'] = this.cartForm.find('input[name="product"]').val();

                amid.bundleProductImage.init();

                this.bundleRadioDivs.bind("click", function (e) {

                    var div = $j(this);
                    var li = div.closest('li');
                    var lis = li.siblings('li');

                    // remove active state
                    lis.removeClass('active');

                    // add active state to selected lis
                    li.addClass('active');

                    // click label to select radio
                    div.next('.bundledmojo_desc_wrapper').find('label').trigger('click');

                });

                amid.productOptionRadios.bundleRadios.change(function(){
                    var radio = $j(this);
                    amid.bundleProductImage.setSelectedOptionProductId(radio);
                    amid.bundleProductImage.makeImageRequest();
                });

                this.bundleRadios.each(function(i) {
                    var radio = $j(this);
                    if (this.checked) {
                        amid.productOptionRadios.bundleRadioDivs.eq(i).trigger('click');
                        radio.trigger('change');
                    }
                });

            }

        },

        setSelectedOptionText : function(radio) {
            var titleBar =  radio.parents('dd').last().prev();
            var selectedOption = '';
            var price = 0;
            titleBar.find('.selected-option').remove();

            if (radio[0].checked) {

                if (titleBar.hasClass('engraving_group_title')) {

                    titleBar.next().find('input[type="radio"]:checked').each(function (index) {
                        var checkedRadio = $j(this);
                        var checkedRadioParent = checkedRadio.parent();
                        if (checkedRadio.attr('data-option') != 'myihr') {
                            selectedOption += (index != 0 ? ', ' : '') + checkedRadioParent.find('label').text();
                        }
                        price += parseFloat(checkedRadioParent.find('.price').text().replace(/\$/g, ""));
                    });

                    price = "$" + price.toFixed(2);
                }
                else {
                    var radioParent = radio.parent();
                    selectedOption = radioParent.find('label').text();
                    price = radioParent.find('.price').text();
                }

                titleBar.append('<span class="selected-option">' + selectedOption + '&nbsp;&nbsp;' + price + '</span>');
            }
        },

        showChainsByLength : {

            // Initialize
            init : function() {
                "use strict";

                this.lengthRadios = amid.productOptionRadios.customOptionRadios.filter('input[data-option="select_length"]');

                if (this.lengthRadios.length) {
                    this.lengthRadios.change(function () {
                        amid.productOptionRadios.showChainsByLength.adjustDisplay($j(this));
                    });

                    this.lengthRadios.each(function (i) {
                        var radio = $j(this);
                        if (this.checked) {
                            radio.trigger('change');
                        }
                    });
                }
            },

            // Function to adjust display based on length
            adjustDisplay : function( object ) {
                "use strict";

                // Get length
                var length = parseInt( $j( object ).data( "option-value" ) );

                // Hide chains
                $j( ".chain-length").hide().find(':radio').removeAttr('checked').trigger('change');

                // Show appropriate chains
                $j( ".chain-length-" + length ).show();
            }
        }
    },

    bundleProductImage : {
        productImage : null,
        productImageContainer : null,
        bundleOptionContainers : null,

        init : function() {
            this.productImage = $j('#image-main');
            this.productImageContainer =  this.productImage.closest('div.product-image-gallery');
            this.bundleOptionContainers = amid.productOptionRadios.cartForm.find('dd.bundle-option-container');
            this.productImageContainer.append('<div class="mask"></div>');
        },
        setSelectedOptionProductId : function(radio) {
            var productId = radio.attr('data-selection-id');
            if (productId) {
                var optionContainer = radio.closest('dd.bundle-option-container');
                var index = amid.productOptionRadios.bundleOptionContainers.index(optionContainer)*1;
                if (index > -1) {
                    amid.productOptionRadios.selectedBundleOptionProductIds['componentIds'][index] = productId;
                }
            }
        },
        makeImageRequest : function() {
            amid.bundleProductImage.config.data = amid.productOptionRadios.selectedBundleOptionProductIds;
            // Abort pending requests
            if (amid.bundleProductImage.request) {
                amid.bundleProductImage.request.abort();
            }
            amid.bundleProductImage.request = $j.ajax(amid.bundleProductImage.config);
        },
        requestSuccess : function(data) {
            $j('.product-image-gallery .gallery-image.visible').attr("src",data);

        },
        requestError : function(jqXHR, textStatus, errorThrown) {

        },
        config : {
            url: '/bundleimage/dynamic/get',
            method: 'GET',
            data: {},
            dataType: 'json',
            beforeSend: function(jqXHR, settings) {
                amid.bundleProductImage.productImageContainer.addClass('loading');
            },
            error: function(jqXHR, textStatus, errorThrown ) {
                amid.bundleProductImage.requestError(jqXHR, textStatus, errorThrown);
            },
            success: function(data, textStatus, jqXHR) {
                if (data.status === "success") {
                    amid.bundleProductImage.requestSuccess(data.data.image);
                }
                else {
                    amid.bundleProductImage.requestError(jqXHR, textStatus, data.message);
                }
            },
            complete: function(jqXHR, textStatus) {
                amid.bundleProductImage.productImageContainer.removeClass('loading');
                amid.bundleProductImage.request = null;
            }
        }
    },
    engravingPlates : {
        defaultFront : undefined,
        defaultBack : undefined,
        init : function() {
            var selectedOption = $j('.product-options .configurable-swatch-list li.selected a');
            this.updatePlate(selectedOption);
        },
        updatePlate : function(element) {
            var attributeId = $j(element).closest('ul').siblings('.swatch-select').attr('id').replace('attribute','');
            var optionId = $j(element).parents('li').attr('id').replace('option','');
            var result, front, back, fontColor;

            if(!(amid.engravingPlates.defaultFront || amid.engravingPlates.defaultBack)) {
                amid.engravingPlates.defaultFront = $j('.front_group_options .plate-image img').attr('src');
                amid.engravingPlates.defaultBack = $j('.back_group_options .plate-image img').attr('src');
            }

            try {
                result = spConfig['config']['engraving_simulation'][attributeId][optionId][0];
                front = result['front'];
                back = result['back'];
                fontColor = result['color'];
            } catch(error) {
                result = undefined;
            }

            if(result) {
                $j('.front_group_options .plate-image img').attr('src', front);
                $j('.back_group_options .plate-image img').attr('src', back);
                $j('.engraving-simulation span').css('color', fontColor);
            } else {
                $j('.front_group_options .plate-image img').attr('src', amid.engravingPlates.defaultFront);
                $j('.back_group_options .plate-image img').attr('src', amid.engravingPlates.defaultBack);
                $j('.engraving-simulation span').css('color', 'inherit');
            }
        }
    },
    myihr : {
        ihr : null,
        radios : null,
        engravingRadios : null,
        engravingRadio : null,
        myihrRadio : null,
        hasMyihr : null,
        plate : null,
        templateApplied : false,
        engravingNote : null,
        myihrContainer : null,

        init : function(){
            this.engravingContainer = $j('.engraving_group_options');
            this.radios = this.engravingContainer.find('input:radio').not('input[data-option*="size"]');
            this.engravingRadios = this.engravingContainer.find('input[data-option="engrave_this_item"]');
            this.engravingRadio = this.engravingRadios.filter('input[data-option-value="yes"]');
            this.myihrRadio = this.radios.filter('input[data-option="myihr"]', 'input[data-option-value="yes"], input[data-option-value="add_myihr"]');
            this.hasMyihr = this.myihrRadio.length;
            this.plates = this.engravingContainer.find('input[data-option*="_size"]');
            this.engravingNote = this.engravingContainer.find('.engraving-note');
            this.myihrContainer = $j( ".myihr_group_options" );
            this.myihrTitle = this.myihrContainer.prev('dt');
            this.myihrIdentityFields = this.myihrContainer.find(':text');

            if (this.hasMyihr) {
                this.ihr = $j.parseJSON(this.myihrRadio.attr('data-myihr-template'));
            }
            this.radios.change(function(e){

                if (amid.myihr.hasMyihr) {

                    e.preventDefault();

                    var radio = $j(this);
                    var isChecked = this.checked;
                    var isMyihr = radio.is(amid.myihr.myihrRadio);
                    var isEngraving = radio.is(amid.myihr.engravingRadios);

                    var engravingSelected = amid.myihr.engravingRadio[0].checked;
                    var myihrSelected = amid.myihr.myihrRadio[0].checked;

                    var fieldContainer = amid.myihr.myihrContainer;
                    var fieldTitle = amid.myihr.myihrTitle;

                    if(((isMyihr && myihrSelected) && engravingSelected) || ((isEngraving && engravingSelected) && myihrSelected)) {
                        amid.myihr.setMyihrTemplateToFields();
                        amid.myihr.addMyihrGroupOptionsValidation();
                        fieldContainer.show();
                        fieldTitle.show();
                    }else if(isMyihr && !myihrSelected) {
                        amid.myihr.removeMyihrTemplateFromFields();
                        amid.myihr.removeMyihrGroupOptionsValidation();
                        fieldContainer.hide();
                        fieldTitle.hide();
                    }else if(isEngraving && !engravingSelected) {
                        amid.myihr.removeMyihrTemplateFromFields(true);
                        // Don't validate additional fields
                        amid.myihr.removeMyihrGroupOptionsValidation();
                    }
                }
            });

            if (this.myihrRadio.length && this.myihrRadio[0].checked) {
                this.myihrRadio.trigger('change');
            }
        },
        setMyihrTemplateToFields : function() {

            if (!amid.myihr.templateApplied) {

                //select place size
                if(amid.myihr.plates.length){
                    amid.myihr.plates.filter('input[data-option-value="large"]').trigger('click').trigger('change');
                    amid.myihr.plates.filter('input[data-option-value="small"]').attr('disabled',true);
                    var forAttr = amid.myihr.plates.filter('input[data-option-value="small"]').attr('id');
                    $j('label[for="'+forAttr+'"]').addClass('disabled');
                }

                //apply myIHR template
                $j.each(amid.myihr.ihr, function (index, value) {
                    amid.myihr.engravingContainer.find('input.active-engraving').eq( index ).val( value ).prop( "readonly", true).trigger('keyup');
                });

                amid.myihr.engravingNote.removeClass('hidden');

                amid.myihr.templateApplied = true;
            }

        },
        removeMyihrTemplateFromFields : function(force) {
            force = force||false;

            if (force || amid.myihr.templateApplied) {
                amid.myihr.engravingContainer.find('input.active-engraving').val( "" ).prop( "readonly", false ).trigger('keyup');

                if(amid.myihr.plates.length){
                    amid.myihr.plates.filter('input[data-option-value="small"]').removeAttr('disabled');
                    var forAttr = amid.myihr.plates.filter('input[data-option-value="small"]').attr('id');
                    $j('label[for="'+forAttr+'"]').removeClass('disabled');
                }

                amid.myihr.engravingNote.addClass('hidden');

                amid.myihr.templateApplied = false;
            }
        },
        addMyihrGroupOptionsValidation : function() {
            "use strict";

            amid.myihr.myihrIdentityFields.each(function(i){
                var input = $j(this);
                var label = input.closest('dd').prev('dt').find('label').text().toLowerCase();

                // add required validation class
                input.addClass("required-entry");

                if (label === 'email') {
                    // add email validation class
                    input.addClass("validate-email");
                }
            });
        },
        removeMyihrGroupOptionsValidation : function() {
            "use strict";

            amid.myihr.myihrIdentityFields.each(function() {
                // Remove validation classes
                $j(this).removeClass( "required-entry validate-email" );
            })
        }
    },
    sort :{
        sorter : null,

        init : function(){
            this.sorter = $j('#sorter');
        },

        sorterPosition : function(){
            amid.sort.sorter.css('top','');
            var top = parseInt(amid.sort.sorter.css('top'));
            if($j('#state-holder').height()){
                amid.sort.sorter.css('top', (top - $j('#state-holder').height()) + 'px');
            }
        }
    },

    toggle : {

        toggler : null,

        init : function(){
            this.toggler = $j('.toggler');
            this.toggler.click(function(){
                amid.toggle.toggleContent($j(this).parents('.toggle-area').find('.toggle-content'));
                amid.toggle.toggleText($j(this));
            });
        },

        toggleContent : function(ele){
            if(ele.hasClass('hidden')){
                ele.slideDown();
                ele.removeClass('hidden');
            }else{
                ele.slideUp();
                ele.addClass('hidden');
            }
        },

        toggleText : function(ele){
            if(ele.html() == 'Close'){
                ele.html('Learn More')
            }else{
                ele.html('Close');
            }
        }
    },

    slider : {

        controller : null,

        init : function(){
            this.controller = $j('.flex-control-nav');
            this.bpMatch();
        },

        controllerPosition : function(){
            setTimeout(function(){
                var slideHeight = amid.slider.controller.parents('.flexslider').find('.slide-image').height();
                amid.slider.controller.css('top',slideHeight - 25);
            },200);
        },

        bpMatch : function(){
            enquire.register('screen and (max-width: ' + $j.perficient.bp.large + 'px)', {
                match: function () {
                    amid.slider.controller.addClass('mobile');
                    amid.slider.controllerPosition();

                    if(amid.slider.controller.hasClass('mobile')){
                        $j(window).resize(function(){
                            amid.slider.controllerPosition();
                        })
                    }
                },

                unmatch : function(){
                    amid.slider.controller.removeClass('mobile');
                    amid.slider.controller.css('top','');
                }
            });
        }
    },

    collapse : {

        optionTerms : null,
        optionContainers : null,

        init : function(){

            this.optionTerms = $j('.bundle #product-options-wrapper > dl > dt');
            this.optionContainers = $j('#product-options-wrapper > dl > dd');

            if($j('.product-view.bundle').length){
                this.closeAll();
            }

            $j('.toggle-all .expand').click(function(){
                amid.collapse.optionTerms.addClass('active');
                amid.collapse.openAll();
            });

            $j('.toggle-all .collapse').click(function(){
                amid.collapse.optionTerms.removeClass('active');
                amid.collapse.closeAll();
            });

            this.optionTerms.click(function() {
                $j(this).toggleClass('active');
                $j(this).next().slideToggle();
            });

        },

        openAll : function() {
            amid.collapse.optionContainers.each(function () {
                amid.collapse.open($j(this));
            });
        },

        closeAll : function(){
            amid.collapse.optionContainers.each(function () {
                amid.collapse.close($j(this));
            });
        },

        open : function(ele){
            ele.prev().addClass('active');
            ele.slideDown();
            ele.removeClass('hidden');
        },

        close : function(ele){
            ele.prev().removeClass('active');
            ele.slideUp();
            ele.addClass('hidden');
        }
    },

    arrival : {

        shippingMethod : null,
        processingMethod : null,

        init : function(){
            amid.arrival.setShippingProcessing();
            $j(document).on('change','.inner-box.estimate input[type="radio"]', amid.arrival.setShippingProcessing);
        },

        setShippingProcessing : function(){
            var shippingMethod = $j('input[name="shipping_method"]:checked').data('shippingDays');

            if(shippingMethod === null || shippingMethod === undefined){
                shippingMethod = $j('input[name="shipping_method"]').first().data('shippingDays');
            }

            var processingDays = $j('input[data-selection-group="processing"]:checked').data('processingDays');

            if(processingDays === null || processingDays === undefined){
                processingDays = $j('input[data-selection-group="processing"]').last().data('processingDays');
            }

            amid.arrival.shippingMethod = shippingMethod;
            amid.arrival.processingMethod = processingDays;
            amid.arrival.estimateAjax();
        },

        // Function to send AJAX request for estimate
        estimateAjax : function() {
            "use strict";

            var shipping = amid.arrival.shippingMethod;
            var processing = amid.arrival.processingMethod;

            // Abort pending requests
            if (request) {
                request.abort();
            }

            // Get form
            var $form = jQuery("#co-shipping-method-form");
            // Get inputs
            var $inputs = $form.find("input, textarea, select, button");

            var data = "";

            if(shipping && processing) {
                data = "shipping_method=" + shipping;
                data += "&processing_days=" + processing;

                // Send request
                request = jQuery.ajax({
                    url: "/shipping/estimating/ajax",
                    type: "post",
                    data: data
                });

                $j('#estimated-arrival-date').html('<span><img src="/skin/frontend/perficient/default/images/opc-ajax-loader.gif" alt="Loading" /></span>');

                // On success
                request.done(function( response ) {
                    // Check response
                    if (response.status === "success") {
                        //alert( "Success!" );
                        var date = response.data['estimated_arrival_date'];
                        var temp = date.split('-');
                        var convertedDate = temp[1] + '/' + temp[2] + '/' + temp[0];
                        $j('#estimated-arrival-date').html(convertedDate);
                    } else if (response.status === "failure") {
                        alert("Failure!");
                    } else if (response.status === "error") {
                        alert("Error!");
                    } else {
                        alert("Error!");
                    }

                });

                // On failure
                request.fail(function (jqXHR, textStatus, errorThrown) {
                    // need to look into why this always abort new request
                    //alert( "Request failed: " + textStatus );
                });

                // Always
                request.always(function () {
                    // Re-enable inputs
                    $inputs.prop("disabled", false);
                });
            }
        },
        processingDays : function() {
            "use strict";

            var values = [];

            // Loop through processing days
            jQuery.each( jQuery( "input[name^='magikfee']:checked" ), function() {
                // Add values
                values.push( jQuery( this ).data( "processingDays" ) );
            });

            // Return values
            return values;
        }
    },

    tab : {

        tabs : null,
        tabContent : null,

        init : function(){
            this.tabs = $j('.tab-area li.tab');
            this.tabContent = $j('.tab-content');

            $j('.tab-content:gt(0)').hide();

            this.tabs.click(function(){
                var tab = $j(this);
                var index = tab.index();
                var tabArea = tab.parents('.tab-area');
                tab.addClass('active');
                tab.siblings().removeClass('active');
                tabArea.find('.tab-content').eq(index).show();
                tabArea.find('.tab-content').eq(index).siblings().hide();
            });

            this.bpMatch();

            $j('a[href^="#"]').click(function(e){
                var hash = $j(this).attr('href').slice(1);
                if (amid.tab.tabs.filter('[data-tab="'+hash+'"]').length) {
                    e.preventDefault();
                    amid.tab.activateTabFromLink(hash);
                }
            });

            var hash = $j.trim(location.hash.slice(1));
            if (hash) {
                this.activateTabFromLink(hash);
            }
        },

        activateTabFromLink : function(hash) {
            var hash = hash||$j.trim(location.hash.slice(1));
            var triggerTab = amid.tab.tabs.filter('[data-tab="'+hash+'"]');
            if (hash && triggerTab.length) {
                triggerTab.trigger('click');
                $j(window).scrollTop(triggerTab.offset().top);
            }
        },

        bpMatch : function() {
            enquire.register('screen and (max-width: ' + $j.perficient.bp.medium + 'px)', {
                match: function () {
                    var clonedTabs = $j('.tab').clone();
                    $j('.tab-content').each(function(index){
                        var content = $j(this);
                        content.before(clonedTabs[index]);
                        content.prev('.tab').changeElementType('label');
                    });

                    $j('.tab').removeClass('active');
                    $j('.tab-content').slideUp();

                    amid.tab.tabs = $j('.tab-area label.tab').click(function(){
                        var tab = $j(this);
                        if(!tab.hasClass('active')){
                            $j('.tab').removeClass('active');
                            tab.addClass('active');
                            var index = tab.parent().find('.tab').index(this);
                            $j('ul li.tab').eq(index).addClass('active');
                            $j('.tab-content').slideUp();
                            tab.next().slideDown();
                        }
                        else if(tab.hasClass('active')){
                            tab.removeClass('active');
                            var index = tab.parent().find('.tab').index(this);
                            $j('ul li.tab').eq(index).removeClass('active');
                            $j('.tab-content').slideUp();
                        }
                    });
                },

                unmatch : function(){
                    $j('section label.tab').remove();
                }
            });
        }
    },

    youtubeEmbed : {
        activeIframe : null,
        init : function() {
            amid.youtubeEmbed.buildPlayer();
        },
        buildPlayer : function() {
            $j('.youtube-player').each(function(i) {
                var player = $j(this);
                var html = amid.youtubeEmbed.buildThumbnailAndPlayBtn(player.attr('data-id'));
                player.append(html);
            });
        },
        buildThumbnailAndPlayBtn : function(id) {
            return '<div onclick="amid.youtubeEmbed.buildIframe(\'' + id + '\');"><img class="youtube-thumb" src="//i.ytimg.com/vi/' + id + '/hqdefault.jpg"><div class="play-button"></div></div>';
        },
        buildIframe : function(id) {
            var iframe = $j('<iframe/>', {
                src : "//www.youtube.com/embed/" + id + "?autoplay=1&autohide=2&border=0&wmode=opaque&enablejsapi=1&controls=1&showinfo=1",
                frameborder : "0",
                class : "youtube-iframe",
                allowfullscreen : true
            });
            var iframeContainer = $j('#overlay-content div[data-id="'+id+'"]');
            iframeContainer.html(iframe);
            amid.youtubeEmbed.activeIframe = iframeContainer.find('iframe');
            setInterval("amid.youtubeEmbed.pauseHiddenVideo", 500);
        },
        pauseHiddenVideo : function() {
            if (amid.youtubeEmbed.activeIframe.is(':hidden')) {
                amid.youtubeEmbed.activeIframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            }
        }

    },

    bundleQty : {
        init : function(){
            $j('.product-view.bundle').find('input[name="qty"]').keyup(function(){
                $j('.product-view.bundle').find('input[name="qty"]').val($j(this).val());
            })
        }
    },

    pricebox : {
        init : function(){
            this.bpMatch();
        },

        bpMatch : function() {
            enquire.register('screen and (max-width: ' + $j.perficient.bp.medium + 'px)', {
                match: function () {
                    var price = $j('.product-options-bottom .price-box');
                    $j('.config-sec .right .add-to-cart .qty-wrapper').after(price);
                },

                unmatch : function(){
                    var price = $j('.config-sec .right .add-to-cart .price-box');
                    $j('.config-sec .left .product-options-bottom .add-to-cart').after(price);
                }
            });
        }
    },

    qmark : {

        init : function () {
            $j('.q-mark').addClass('closed');
            $j('.option-note').addClass('hidden');
            $j('.option-note').append('<span class="close">Close</span>');

            $j('.q-mark.special').each(function(){
                $j(this).next().addClass('special');
                $j(this).parent().parent().prev().append($j(this));
            });

            $j('.q-mark').click(function() {
                $j(this).toggleClass('closed');
                if($j(this).hasClass('special'))
                    $j(this).parent().next().find('.option-note').toggleClass('hidden');
                else
                    $j(this).next().toggleClass('hidden');
            });

            $j(document).on('click','.option-note .close',function(){
                $j(this).parent().addClass('hidden');
                if($j(this).parent().hasClass('special')){
                    $j(this).parents('.ob-note').parent().prev().find('.q-mark').addClass('closed');
                }else{
                    $j(this).parent().prev().addClass('closed');
                }
            })
        }
    },

    learnmore : {

        trigger : null,
        close : null,

        init : function(){
            this.trigger = $j('.learn-more');
            this.close = $j('.close');
            this.trigger.click(function(){
                $j(this).next().removeClass('hidden');
                $j(this).addClass('hidden');
            })

            this.close.click(function(){
                $j(this).parent().addClass('hidden');
                amid.learnmore.trigger.removeClass('hidden');
            })
        }
    },

    engravingSuggestions: function() {
        var engravingIdeas = $j.parseJSON( window.engravingIdeas );

        function updateRadios( section )
        {
            var list = $j('select[name="suggestions-list"]');

            list.find('option').remove();
            list.append('<option value="none">Select...</option>');

            $j.each( engravingIdeas[section], function( index, value )
            {
                list.append('<option value="' + index + '">' + index + '</option>');
            } );
        }

        $j('select[name="suggestions-list"]').change( function()
        {
            var selection = $j(this).val();
            var category  = $j('input[name="suggestion-categories"]:checked').val();

            $j('#condition-title').text(selection);
            $j('#condition-message').text(engravingIdeas[category][selection].Message);
            $j('#suggestion-one-message').text(engravingIdeas[category][selection].Suggestions[0]);
            $j('#suggestion-two-message').text(engravingIdeas[category][selection].Suggestions[1]);
            $j('#suggestion-three-message').text(engravingIdeas[category][selection].Suggestions[2]);
        } );

        $j('input[name="suggestion-categories"]').click( function()
        {
            updateRadios( $(this).value );
        } );
    },

    oneSelectionByData : {
        selectionGroup : null,
        init : function(attrValue) {
            this.selectionGroup = $j('input[data-selection-group="'+attrValue+'"]');
            this.selectionGroup.change(function(e) {
                e.preventDefault();
                amid.oneSelectionByData.selectionGroup.not(this).removeAttr('checked');
            });

            $j(document).on('click','.ob-note .close',function(){
                element.addClass('note-closed');
                $j(this).parent().hide();
            });
        },

        addQmark : function(){
            $j('.engrave_this_item_option_title').append('<span class="q-mark">?</span>');
        }
    },

    checkoutProcess : {

        process : null,

        init : function(){
            this.process = $j('#checkout-progress-wrapper');
            this.bpMatch();
        },

        bpMatch : function() {
            enquire.register('screen and (max-width: ' + $j.perficient.bp.large + 'px)', {
                match: function () {
                    $j('#checkout-review-load').before(amid.checkoutProcess.process);
                },

                unmatch : function(){
                    $j('div.right').append(amid.checkoutProcess.process);
                }
            });
        }
    },

    cartButton : {

        addToCartBtn : null,
        addToCartTxtContainer : null,
        addToCartTxt : null,

        init : function(){
            this.addToCartBtn = $j('.btn-cart');
            this.addToCartTxtContainer = this.addToCartBtn.find('span span');
            this.addToCartTxt = this.addToCartTxtContainer.eq(0).text();
            this.addToCartTxtContainer.text('Customize');

            this.addToCartBtn.click(function(event){
                if($j(this).text() == 'Customize'){
                    amid.collapse.open($j('.product-options > dl > dd').eq(0));
                }
            });

            $j('#product_addtocart_form').change(function(){
                if(productAddToCartForm.validator.silentValidate()) {
                    amid.cartButton.addToCartTxtContainer.text(amid.cartButton.addToCartTxt);
                    amid.cartButton.addToCartBtn.addClass('active');
                }else{
                    amid.cartButton.addToCartTxtContainer.text('Customize');
                    amid.cartButton.addToCartBtn.removeClass('active');
                }
            });
        }
    },

    stickyHeader : {

        originalPosition : null,
        headerWrapped : false,

        init : function(){
            this.originalPosition = $j('.breadcrumbs').offset().top;
            this.bpMatch();
        },

        pageScroll : function(){
            if($j(document).scrollTop() >= $j('.breadcrumbs').offset().top){
                amid.stickyHeader.wrapHeader();
            }

            if($j(document).scrollTop() <= 0 ){
                amid.stickyHeader.unwrapHeader();
            }
        },

        wrapHeader : function(){
            if (!amid.stickyHeader.headerWrapped) {
                $j('.breadcrumbs').after($j('.bundle-container'));
                if (!$j('.sticky-header-wrapper').length) {
                    $j('.breadcrumbs, .bundle-container').wrapAll('<div class="sticky-header-wrapper" />');
                }
                $j('.product-shop').css('margin-top', $j('.sticky-header-wrapper').height());
                amid.stickyHeader.headerWrapped = true;
            }
        },

        unwrapHeader : function(){
            if (amid.stickyHeader.headerWrapped) {
                $j('.breadcrumbs, .bundle-container').unwrap();
                $j('.product-shop').css('margin-top', '').before($j('.bundle-container'));
                amid.stickyHeader.headerWrapped = false;
            }
        },

        bpMatch : function(){

            //tablet and desktop
            enquire.register('screen and (min-width: ' + ($j.perficient.bp.medium + 1) + 'px)', {
                match: function () {
                    amid.stickyHeader.pageScroll();
                    $j(window).scroll(function(){
                        amid.stickyHeader.pageScroll();
                    });
                }
            });

            //mobile
            enquire.register('screen and (max-width: ' + $j.perficient.bp.medium + 'px)', {
                match: function () {
                    $j(window).unbind('scroll');
                    if($j('.sticky-header-wrapper').length)
                        amid.stickyHeader.unwrapHeader();
                }
            });
        }
    },
    powerReview : function() {
        $j(".pr-snippet-read-reviews .pr-snippet-link").attr('href', 'javascript:void(0)');

        $j(".pr-snippet-read-reviews .pr-snippet-link").on('click', function(){
            if($j(document).scrollTop() <= 0 ){
                window.scrollTo(0,$j('.breadcrumbs').offset().top + 1);
            }
            setTimeout(function(){
                window.scrollTo(0, $j('.product-collateral').offset().top - $j('.sticky-header-wrapper').height());
            },100)
        });
    }
};
$j(window).load(function() {
    amid.powerReview();
});

$j(window).resize(function(){
    if($j('#state-holder').height()){
        amid.sort.sorterPosition();
    }
});

// Global variable for AJAX request
var request;

$j( document ).ready(function( $ ) {
    amid.init();

    // Bind click handler to spellcheck button
    $( "#spellcheck-trigger" ).click(function( event ) {
        "use strict";

        // Prevent submission
        event.preventDefault();

        // Get spelling suggestions
        amid.engraving.spellings();
    });

    if($('#messages_product_view').children().length){
        if($('#messages_product_view').children().children().attr('class') == 'success-msg') {
            if($('.success-msg ul li span').text().indexOf('was added to your shopping cart') > -1) {
                $('.header-minicart').find('.skip-cart').trigger('click');
            }
        }
    }

});


if ( typeof Validation !== "undefined" )
{
    Validation.prototype.silentValidate = function() {
        var result = false;
        result = Form.getElements(this.form).collect(function(elm) {
            if (elm.hasClassName('local-validation') && !this.isElementInForm(elm, this.form)) {
                return true;
            }
            var cn = $w(elm.className);
            return result = cn.all(function(name) {
                var v = Validation.get(name);
                var test = v.test($F(elm), elm);
                return test;
            });
        }, this).all();

        return result;
    };

    Validation.add( 'engraving-text', 'Illegal Character', function ( v )
    {
        if (/[^!#&\(\)\-\+=:\/\.,' a-zA-Z0-9]/.test(v))
        {
            return false;
        }

        return true;
    } );

    Validation.add( 'engraving-text-one', 'You need at least one character in any of the text fields.', function (v,elm)
    {
        var inputs = document.getElementsByClassName('engraving-field');
        var isValid = false;

        for(var i=0;i<inputs.length;i++) {
            if(!Validation.get('IsEmpty').test(inputs[i].value)) {
                isValid = true;
            }
            if(Validation.isOnChange) {
                Validation.reset(inputs[i]);
            }
        }

        return isValid;

    } );

}