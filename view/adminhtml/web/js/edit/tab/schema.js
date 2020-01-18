define([
    'jquery',
    'ko',
    'underscore'
], function ($, ko, _) {
    'use strict';

    $.Pattern = function () {
        var self = this;

        this.toString = ko.observable().extend({rateLimit: 50});

        this.type = ko.observable();
        this.pattern = ko.observable();
        this.attribute = ko.observable();
        this.modifiers = ko.observableArray([]);
        this.validators = ko.observableArray([]);
        this.header = ko.observable();
        this.opened = ko.observable(false);

        this.type.subscribe(function () {
            self.build();
        });

        this.pattern.subscribe(function () {
            self.build();
        });

        this.attribute.subscribe(function () {
            self.build();
        });

        this.modifiers.subscribe(function (array) {
            _.each(array, function (item) {
                item.toString.subscribe(function () {
                    self.build();
                });
            });
            self.build();
        });

        this.load = function (obj) {
            this.type(obj.type);
            this.pattern(obj.pattern);
            this.attribute(obj.attribute);
            this.pattern(obj.pattern);
            this.header(obj.header);

            _.each(obj.modifiers, function (item) {
                self.modifiers.push(new $.Modifier().load(item));
            });
            _.each(obj.validators, function (item) {
                self.validators.push(new $.Validator().load(item));
            });

            return this;
        };

        this.parse = function (line) {
            this.reset();

            var variableMatchExpr = /{{.*?}}/;
            var variableExpr = /{{(.*?)}}/;
            var variableNameExpr = /\s*("[^"]+"|'[^']+'|[^\s,|]+)/;

            if (!variableMatchExpr.exec(line)) {
                return;
            }

            var match = variableMatchExpr.exec(line);

            self.parsedChFrom = match.index;
            self.parsedChTo = self.parsedChFrom + match[0].length;

            var variable = variableExpr.exec(line)[1];

            var variableName = variableNameExpr.exec(variable)[1];
            var variableParts = variableName.split('.');

            if (variableParts[0] != 'product') {
                return;
            }

            if (variableParts.length == 2) {
                self.attribute(variableParts[1]);
                self.type('');
            } else if (variableParts.length == 3) {
                self.attribute(variableParts[2]);
                self.type(variableParts[1]);

                if (self.type() != 'parent') {
                    this.reset();
                    return;
                }
            }

            if (variable.match(/\|\s*(.*)/)) {
                var filters = variable.match(/\|\s*(.*)/)[1].split(/\|/);

                _.each(filters, function (filter) {
                    var modifier = new $.Modifier(self);
                    modifier.parse(filter);
                    self.modifiers.push(modifier)
                });
            }
        };

        this.reset = function () {
            this.modifiers.removeAll();
            this.validators.removeAll();
            this.attribute(null);
            this.pattern(null);
            this.type(null);
        };

        this.build = function () {
            if (this.type() == 'pattern') {
                this.toString(this.pattern());
                return;
            }

            if (!this.attribute()) {
                this.toString('');
                return;
            }

            var str = '{{ product.';

            if (this.type() != '') {
                str += this.type() + '.';
            }

            str += this.attribute();

            var modifiers = [];

            _.each(self.modifiers(), function (modifier) {
                if (modifier.toString()) {
                    modifiers.push(modifier.toString());
                }
            });

            if (modifiers.length) {
                str += ' | ' + modifiers.join(' | ');
            }

            str += ' }}';

            this.toString(str);
        };

        this.addModifier = function () {
            self.modifiers.push(new $.Modifier(self));
        };

        this.removeModifier = function (modifier) {
            self.modifiers.remove(modifier);
        };

        this.addValidator = function () {
            self.validators.push(new $.Validator(self));
        };

        this.removeValidator = function (validator) {
            self.validators.remove(validator);
        };
    };

    $.Modifier = function () {
        var self = this;

        this.toString = ko.observable();

        this.modifier = ko.observable();
        this.args = ko.observableArray([]);

        this.modifier.subscribe(function () {
            self.updateArgs();

            self.build();
        });

        this.args.subscribe(function (array) {
            _.each(array, function (item) {
                item.toString.subscribe(function () {
                    self.build();
                });
            });
            self.build();
        });

        this.load = function (obj) {
            this.modifier(obj.modifier);

            _.each(obj.args, function (value, index) {
                self.args()[index].argument(value);
            });

            return this;
        };

        this.parse = function (filter) {
            var filterNameExpr = /\s*(\w+)/;
            var argumentExpr = /(?::|,)\s*("[^"]+"|'[^']+'|[^\s,|]+)/g;

            if (filterNameExpr.exec(filter)) {
                self.modifier(filterNameExpr.exec(filter)[1]);
            }

            var index = 0;
            var found;

            while (found = argumentExpr.exec(filter)) {
                self.args()[index].argument(found[1]);
                index++;
            }
        };

        this.updateArgs = function () {
            var modifier = $.modifiers.find(function (el) {
                return el.value == self.modifier();
            });

            if (modifier) {
                self.args.removeAll();

                _.each(modifier.args, function (arg) {
                    self.args.push(new $.Argument(self).load(arg));
                });
            }
        };

        this.build = function () {
            if (!this.modifier()) {
                this.toString('');
                return;
            }

            var str = this.modifier();

            var args = [];
            _.each(self.args(), function (arg) {
                if (arg.toString()) {
                    args.push(arg.toString());
                }
            });

            if (args.length) {
                str += ': ' + args.join(', ');
            }

            this.toString(str);
        };
    };

    $.Validator = function () {
        this.validator = ko.observable();

        this.load = function (obj) {
            this.validator(obj.validator);

            return this;
        };
    };

    $.Argument = function () {
        var self = this;

        this.toString = ko.observable();

        this.label = ko.observable();
        this.value = ko.observable();
        this.argument = ko.observable();

        this.argument.subscribe(function () {
            self.build();
        });

        this.load = function (obj) {
            self.label(obj.label);
            self.value(obj.value);

            return self;
        };

        this.build = function () {
            if (self.argument()) {
                if (self.argument()[0] == "'") {
                    this.toString(self.argument());
                } else {
                    this.toString("'" + self.argument() + "'");
                }
            } else {
                this.toString("''");
            }
        };
    };
});