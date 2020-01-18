define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'collapsable',
    'jquery/ui'
], function ($, ko, _, Component) {
    'use strict';
    $.Map = function (templateType) {
        var self = this;
        this.rows = null;
        this.category_id = null;
        this.parent_id = null;
        this.name = null;
        this.level = null;
        this.has_childs = null;

        this.category = ko.observable();
        this.map = ko.observable();
        this.placeholder = ko.observable();
        this.setValue = ko.observable();
        this.opened = ko.observable(false);
        this.visible = ko.observable(false);

        this.opened.subscribe(function () {
            self.map.notifySubscribers();
            self.placeholder.notifySubscribers();

            _.each(self.rows(), function (row) {
                if (row.parent_id == self.category_id) {
                    row.visible(self.opened());
                }
            });
        });

        // this.setValue.subscribe(function () {
        //     _.each(self.rows(), function (row) {
        //         if (row.parent_id == self.category_id) {
        //             if (self.map()) {
        //                 row.setValue(self.map());
        //             } else {
        //                 row.setValue(self.placeholder());
        //             }
        //         }
        //     });
        // });

        // placeholder
        if(templateType=='google'){
            this.map.subscribe(function () {
                _.each(self.rows(), function (row) {
                    if (row.parent_id == self.category_id) {
                        // console.log(row);
                        row.placeholder(self.map());
                    }
                });
            });
            this.placeholder.subscribe(function () {
                _.each(self.rows(), function (row) {
                    if (row.parent_id == self.category_id) {
                        if (self.map()) {
                            row.placeholder(self.map());
                        } else {
                            row.placeholder(self.placeholder());
                        }
                    }
                });
            });
        }
        
        this.visible.subscribe(function () {
            // hide child categories
            if (!self.visible()) {
                _.each(self.rows(), function (row) {
                    if (row.parent_id == self.category_id) {
                        row.visible(false);
                    }
                });
            }
        });

        this.load = function (obj) {
            self.category_id = obj.category_id;
            self.name = obj.name;
            self.map(obj.map);
            self.level = obj.level;
            self.parent_id = obj.parent_id;
            self.has_childs = obj.has_childs;

            if (obj.level == 0) {
                self.visible(true);
            }

            this.onSuggestSelect = function(map, e, ui) {
                self.map(ui.item.path);
            };

            return self;
        };

        this.toggle = function (map) {
            map.opened(!map.opened());
        }
    };

    return Component.extend({
        defaults: {
            template: 'Magenest_ProductFeed/category_mapping'
        },

        initialize: function () {
            var self = this;
            this._super();

            self.rows = ko.observableArray([]);
            var templateType = self.templateType;
            _.each(self.mapping, function (row) {
                var obj = new $.Map(templateType);
                obj.rows = self.rows;
                obj.load(row);

                self.rows.push(obj);
            });
        }
    });

});