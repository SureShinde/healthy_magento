var amid = {

    init: function () {

        if ( $j( "#product_composite_configure_form_fields" ).length ) {

            if ($j('input[data-option="engrave_this_item"]').length) {
                this.engraving.init();
            }

            if($j('input[data-option="myihr"]').length){
                this.myihr.init();
            }
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
            this.addToCartForm = $j('#product_composite_configure_form');
            this.engravingContainer = this.addToCartForm.find('.product-options');
            this.engravingDataFields = this.engravingContainer.find('input.engraving-data');
            this.textFields = this.engravingContainer.find('input.engraving-field');
            this.engravingTextFields = this.textFields;
            this.engravingRadios = this.engravingContainer.find('input[data-option="engrave_this_item"]');
            this.engravingRadio = this.engravingRadios.filter('input[data-option-value="yes"]');
            this.plates = this.engravingContainer.find('input[data-option*="_size"]');
            this.groupTitles = $j('dt.front_group_title, dt.back_group_title');
            this.engravingRadio.prop('checked',true);

            this.engravingRadios.change(function(e) {
                amid.engraving.setHasEngraving($j(this));
                amid.engraving.toggleEngravingValidation();
                //amid.engraving.toggleSpellcheckBtn(e);
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
                //this.simulation.generateEngravingDisplay();
                this.textFields.on('keyup blur', function(){
                    amid.engraving.characterCount(this);
                    //amid.engraving.simulation.displayEngravingText(this);
                });

            }

            this.toggleEngravingValidation();
            this.repopulateEngraving();

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
            amid.engraving.activePlateSize = plateSize;
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
            //amid.engraving.simulation.generateEngravingDisplay();
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
            fields.addClass('engraving-text engraving-text-one');
        },
        removeEngravingValidation : function(fields) {
            fields = fields ? fields : amid.engraving.textFields;
            fields.removeClass('engraving-text engraving-text-one');
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
        }
    },

    myihr : {
        ihr : null,
        radios : null,
        engravingRadio : null,
        myihrRadio : null,
        hasMyihr : null,
        plate : null,
        templateApplied : false,
        //engravingNote : null,
        myihrContainer : null,
        engravingContainer: null,

        init : function(){
            this.engravingContainer = $j('#product_composite_configure_form .product-options');
            this.radios = this.engravingContainer.find('input:radio').not('input[data-option*="size"]');
            this.engravingRadio = this.radios.filter('input[data-option="engrave_this_item"], input[data-option-value="yes"]');
            this.myihrRadio = this.radios.filter('input[data-option="myihr"]', 'input[data-option-value="yes"], input[data-option-value="add_myihr"]');
            this.hasMyihr = this.myihrRadio.length;
            this.plates = this.engravingContainer.find('input[data-option*="_size"]');
            //this.engravingNote = this.engravingContainer.find('.engraving-note');
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
                    var isEngraving = radio.is(amid.myihr.engravingRadio);

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
                    $j('label[for="'+forAttr+'"').addClass('disabled');
                }

                //apply myIHR template
                $j.each(amid.myihr.ihr, function (index, value) {
                    amid.myihr.engravingContainer.find('input.active-engraving').eq( index ).val( value ).prop( "readonly", true).trigger('keyup');
                });

                //amid.myihr.engravingNote.removeClass('hidden');

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
                    $j('label[for="'+forAttr+'"').removeClass('disabled');
                }

                //amid.myihr.engravingNote.addClass('hidden');

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
    }

};

/*$j( document ).ready(function( $ ) {
    amid.init();
});*/


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