/**
 * KAbel_BundlePlus
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to a BSD 3-Clause License
 * that is bundled with this package in the file LICENSE_BSD_NU.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www1.unl.edu/wdn/wiki/Software_License
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2012 Regents of the University of Nebraska (http://www.nebraska.edu/)
 * @license     http://www1.unl.edu/wdn/wiki/Software_License  BSD 3-Clause License
 */
if(typeof Product=='undefined') {
    var Product = {};
}
/**************************** BUNDLE PRODUCT **************************/
Product.Bundle = Class.create();
Product.Bundle.prototype = {
    initialize: function(config){
        this.config = config;

        // Set preconfigured values for correct price base calculation
        if (config.defaultValues) {
            for (var option in config.defaultValues) {
                if (this.config['options'][option].isMulti) {
                    var selected = new Array();
                    for (var i = 0; i < config.defaultValues[option].length; i++) {
                        selected.push(config.defaultValues[option][i]);
                    }
                    this.config.selected[option] = selected;
                } else {
                    this.config.selected[option] = new Array(config.defaultValues[option] + "");
                }
            }
        }

        this.reloadPrice();
    },
	limitSelection: function(element, total){
		idItems = element.id.split('-');
		if(idItems[3] == 'Chocolate'){
			cat = 'Vanilla';
		} else {
			cat = 'Chocolate';
		}
		this_chk = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+idItems[3]);
		other_chk = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+cat);
		this_qty = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+idItems[3]+"-qty-input");
		other_qty = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+cat+"-qty-input");
		this_qty_default = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+idItems[3]+"-qty-input-default");
		other_qty_default = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+cat+"-qty-input-default");
		
		if(this_chk.checked){
			// make the qty for this checkbox = the original value
			if(this_qty_default.value == 0){
				// the original value was zero but they checkedt this box.
				this_qty.value = 1;
				other_qty.value = other_qty_default.value - 1;
				if(other_qty.value == 0){
					other_chk.checked = false;
				} else {
					other_chk.checked = true;
				}
			} else {
				this_qty.value = this_qty_default.value;
				// reduce the other qty by 1
				other_qty.value = other_qty_default.value;
				//other_qty_field.value = other_qty;
			// if other qty is now 0 uncheck other check box
				if(other_qty.value == 0){
					
					other_chk.checked = false;
				} else {
					other_chk.checked = true;
				}
			}
		} else {
			// make qty for this checkbox = 0
			this_qty.value = 0;
			// check other check box
			other_chk.checked = true;
			// make other qty = total
			other_qty.value = total;
		}
		
	},
	
	limitQuantity: function(id, val, total){
	//alert(val);
	//alert(id);
	idItems = id.split('-');
	if(idItems[3] == 'Chocolate'){
		cat = 'Vanilla';
	} else {
		cat = 'Chocolate';
	}

	this_chk = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+idItems[3]);
	other_chk = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+cat);
	this_qty = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+idItems[3]+"-qty-input");
	other_qty = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+cat+"-qty-input");
	this_qty_default = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+idItems[3]+"-qty-input-default");
	other_qty_default = document.getElementById(idItems[0]+"-"+idItems[1]+"-"+idItems[2]+"-"+cat+"-qty-input-default");

	if(val > 0){
		// check to see if the qty is valid, not more than the total allowed
		//alert('here');
		if(val <= total){
			// this is valid continue
			// make sure this check box is checked
			this_chk.checked = true;
			
			if(val == total){
				// the other text box needs to be 0 and check box unchecked
				other_chk.checked = false;
				other_qty.value = 0;
			} else {
				// the other text box needs to be reduced by this amount
				other_qty.value = total - val;
				other_chk.checked = true;
			}
		} else {
			// qty entered is greater than what is allowed for this category
			// make this qty = total
			this_qty.value = total;
			this_chk.checked = true;
			// make the other qty = 0 
			other_qty.value = 0;
			// make other check box unchecked
			other_chk.checked = false;
		}
	} else {
		// value is less <= 0
		// make this qty = 0
		this_qty.value = 0;
		// uncheck this checkbox
		this_chk.checked = false;
		// make other checkbox checked
		other_chk.checked = true;
		// make other qty = total
		other_qty.value = total;
	}
  },
  
    changeSelection: function(selection){
		var parts = selection.id.split('-');
		//alert(parts[2]);
        if (this.config['options'][parts[2]].isMulti) {
            selected = new Array();
            if (selection.tagName == 'SELECT') {
                //alert('select');
        		for (var i = 0; i < selection.options.length; i++) {
                    if (selection.options[i].selected && selection.options[i].value != '') {
                        selected.push(selection.options[i].value);
                    }
                }
            } else if (selection.tagName == 'INPUT') {
				selector = parts[0]+'-'+parts[1]+'-'+parts[2];
                selections = $$('.'+selector);
                for (var i = 0; i < selections.length; i++) {
                    if (selections[i].checked && selections[i].value != '') {
                        selected.push(selections[i].value);
                    }
                }
                if (selection.value != '') {
                	if (selection.checked && this.config.options[parts[2]].selections[selection.value].customQty == 1) {
						test = parts[2] + '-' + selection.value
						alert(test);
                        this.showQtyInput(parts[2] + '-' + selection.value, this.config.options[parts[2]].selections[selection.value].qty, true);
                	} else if (this.config.options[parts[2]].selections[selection.value].customQty == 1) {
                        this.showQtyInput(parts[2] + '-' + selection.value, '0', false);
                	}
                }
            }
            this.config.selected[parts[2]] = selected;
        } else {
			
            if (selection.value != '') {
                this.config.selected[parts[2]] = new Array(selection.value);
            } else {
                this.config.selected[parts[2]] = new Array();
            }
            this.populateQty(parts[2], selection.value);
        }
        this.reloadPrice();
    },

    reloadPrice: function() {
        var calculatedPrice = 0;
        var dispositionPrice = 0;
        var includeTaxPrice = 0;
        for (var option in this.config.selected) {
            if (this.config.options[option]) {
                for (var i=0; i < this.config.selected[option].length; i++) {
                    var prices = this.selectionPrice(option, this.config.selected[option][i]);
                    calculatedPrice += Number(prices[0]);
                    dispositionPrice += Number(prices[1]);
                    includeTaxPrice += Number(prices[2]);
                }
            }
        }

        var event = $(document).fire('bundle:reload-price', {
            price: calculatedPrice,
            priceInclTax: includeTaxPrice,
            dispositionPrice: dispositionPrice,
            bundle: this
        });
        if (!event.noReloadPrice) {
            optionsPrice.specialTaxPrice = 'true';
            optionsPrice.changePrice('bundle', calculatedPrice);
            optionsPrice.changePrice('nontaxable', dispositionPrice);
            optionsPrice.changePrice('priceInclTax', includeTaxPrice);
            optionsPrice.reload();
        }

        return calculatedPrice;
    },

    selectionPrice: function(optionId, selectionId) {
        if (selectionId == '' || selectionId == 'none') {
            return 0;
        }
        var qty = null;
        if (this.config.options[optionId].selections[selectionId].customQty == 1) {
            if (this.config['options'][optionId].isMulti) {
            	if ($('bundle-option-' + optionId + '-' + selectionId + '-qty-input')) {
            		qty = $('bundle-option-' + optionId + '-' + selectionId + '-qty-input').value;
            	} else {
            		qty = 1;
            	}
            } else {
	        	if ($('bundle-option-' + optionId + '-qty-input')) {
	                qty = $('bundle-option-' + optionId + '-qty-input').value;
	            } else {
	                qty = 1;
	            }
            }
        } else {
            qty = this.config.options[optionId].selections[selectionId].qty;
        }

        if (this.config.priceType == '0') {
            price = this.config.options[optionId].selections[selectionId].price;
            tierPrice = this.config.options[optionId].selections[selectionId].tierPrice;

            for (var i=0; i < tierPrice.length; i++) {
                if (Number(tierPrice[i].price_qty) <= qty && Number(tierPrice[i].price) <= price) {
                    price = tierPrice[i].price;
                }
            }
        } else {
            selection = this.config.options[optionId].selections[selectionId];
            if (selection.priceType == '0') {
                price = selection.priceValue;
            } else {
                price = (this.config.basePrice*selection.priceValue)/100;
            }
        }
        //price += this.config.options[optionId].selections[selectionId].plusDisposition;
        //price -= this.config.options[optionId].selections[selectionId].minusDisposition;
        //return price*qty;
        var disposition = this.config.options[optionId].selections[selectionId].plusDisposition +
            this.config.options[optionId].selections[selectionId].minusDisposition;

        if (this.config.specialPrice) {
            newPrice = (price*this.config.specialPrice)/100;
            newPrice = (Math.round(newPrice*100)/100).toString();
            price = Math.min(newPrice, price);
        }

        selection = this.config.options[optionId].selections[selectionId];
        if (selection.priceInclTax !== undefined) {
            priceInclTax = selection.priceInclTax;
            price = selection.priceExclTax !== undefined ? selection.priceExclTax : selection.price;
        } else {
            priceInclTax = price;
        }

        var result = new Array(price*qty, disposition*qty, priceInclTax*qty);
        return result;
    },

    populateQty: function(optionId, selectionId){
        if (selectionId == '' || selectionId == 'none') {
            this.showQtyInput(optionId, '0', false);
            return;
        }
        if (this.config.options[optionId].selections[selectionId].customQty == 1) {
            this.showQtyInput(optionId, this.config.options[optionId].selections[selectionId].qty, true);
        } else {
            this.showQtyInput(optionId, this.config.options[optionId].selections[selectionId].qty, false);
        }
    },

    showQtyInput: function(optionId, value, canEdit) {
        elem = $('bundle-option-' + optionId + '-qty-input');
        elem.value = value;
        elem.disabled = !canEdit;
        if (canEdit) {
            elem.removeClassName('qty-disabled');
        } else {
            elem.addClassName('qty-disabled');
        }
    },

    changeOptionQty: function (element, event) {
        var checkQty = true;
        if (typeof(event) != 'undefined') {
            if (event.keyCode == 8 || event.keyCode == 46) {
                checkQty = false;
            }
        }
        if (checkQty && (Number(element.value) == 0 || isNaN(Number(element.value)))) {
            element.value = 1;
        }
        parts = element.id.split('-');
        optionId = parts[2];
        if (!this.config['options'][optionId].isMulti) {
            selectionId = this.config.selected[optionId][0];
            this.config.options[optionId].selections[selectionId].qty = element.value*1;
            this.reloadPrice();
        } else if (parts[3] != 'qty') {
        	selectionId = parts[3];
        	this.config.options[optionId].selections[selectionId].qty = element.value*1;
        	this.reloadPrice();
        }
    },
    
    changeOptionSelectionQty: function (element, event) {
    	var checkQty = true;
    	if (typeof(event) != 'undefined') {
            if (event.keyCode == 8 || event.keyCode == 46) {
                checkQty = false;
            }
        }
        if (checkQty && (Number(element.value) == 0 || isNaN(Number(element.value)))) {
            element.value = 1;
        }
        parts = element.id.split('-');
        optionId = parts[2];
        selectionId = parts[3];
            this.config.options[optionId].selections[selectionId].qty = element.value*1;
            this.reloadPrice();
    },

    validationCallback: function (elmId, result){
        if (elmId == undefined || $(elmId) == undefined) {
            return;
        }
        var container = $(elmId).up('ul.options-list');
        if (typeof container != 'undefined') {
            if (result == 'failed') {
                container.removeClassName('validation-passed');
                container.addClassName('validation-failed');
            } else {
                container.removeClassName('validation-failed');
                container.addClassName('validation-passed');
            }
        }
    }
};
