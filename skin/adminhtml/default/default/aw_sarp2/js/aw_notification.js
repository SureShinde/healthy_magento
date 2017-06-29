var awNotificationDependence = Class.create({
    initialize: function (config) {
        this.mainField = $(config.mainFieldId);
        this.dependenceField = $(config.dependenceFieldId);
        this.available = config.available;
        this.init();
    },

    init: function () {
        this.process();
        Event.observe(this.mainField, 'change', this.process.bind(this));
    },

    process: function () {
        if (this.available.indexOf(this.mainField.value) != -1) {
            this.dependenceField.up().up().show();
            this.dependenceField.addClassName('required-entry');
        } else {
            this.dependenceField.up().up().hide();
            this.dependenceField.removeClassName('required-entry');
        }
    },
});

var awNotificationTemplates = Class.create({
    initialize: function (config) {
        this.templatesSelector = config.templatesSelector;
        this.templateType = $(config.typeSelector);
        this.templateRecipientSelector = config.recipientSelector;
        this.allElements = {};
        this.init();
    },

    init: function () {
        var self = this;
        $$(this.templatesSelector).each(function(el) {
            if (typeof self.rowContainer == 'undefined') {
                self.rowContainer = el.up().up().up();
            }
            self.allElements[el.id] = el.getValue();
        });
        this.switchTemplates();

        Event.observe(this.templateType, 'change', this.switchTemplates.bind(this));
        Event.observe($(this.templateRecipientSelector), 'change', this.switchTemplates.bind(this));
    },

    switchTemplates: function() {
        var self = this;
        $$(this.templatesSelector).each(function(el) {
            var rc = el.up().up();
            self.allElements[el.id] = self.rowContainer.removeChild(rc);
        });

        var postfix = this.templateType.getValue() + ($F(this.templateRecipientSelector) == 'customer' ? '_customer' : '_admin');

        if (typeof this.allElements['email_template_' + postfix] != 'undefined') {
            this.rowContainer.appendChild(this.allElements['email_template_' + postfix]);
        };
    }

});