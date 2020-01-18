define([
    'jquery',
    'jquery/ui',
    'ko',
    'Magento_Ui/js/modal/modal'
], function ($, ui, ko, modal) {
    'use strict';

    $.widget('magenest.Library', {
        options: {
            url: null
        },

        attributes: _.toArray($.attributes),

        _create: function () {
            this.element
                .off('click.button')
                .on('click.button', $.proxy(this.show, this));

            this._super();

            _.bindAll(this, 'toggleRow');
        },

        show: function () {
            var self = this;

            var modal = $('<div/>').modal({
                type: 'slide',
                title: $.mage.__('Library of patterns'),
                modalClass: 'library-aside',
                closeOnEscape: true,
                opened: function () {
                    self.update(modal);
                },
                closed: function () {
                    $('.library-aside').remove();
                },

                buttons: []
            });

            modal.modal('openModal');
        },

        update: function (modal) {
            var self = this;

            $('body').trigger('processStart');

            $.ajax({
                method: 'GET',
                url: this.options.url,
                data: {}
            }).done(function (html) {
                $(modal).html(html);

                ko.applyBindings(self, $(modal)[0]);

                $('body').trigger('processStop');
            });
        },

        toggleRow: function (row) {
            var self = this;

            var modal = $('<div/>').modal({
                title: row.label,
                buttons: [
                    {
                        text: $.mage.__('Reload'),
                        click: function (e) {
                            self.updateRow(row, modal);
                        }
                    }
                ]
            });

            modal.modal('openModal');

            self.updateRow(row, modal);
        },

        updateRow: function (row, modal) {
            var self = this;

            $(modal).trigger('processStart');

            $.ajax({
                url: self.options.url,
                data: {
                    pattern: row.value
                }
            }).done(function (html) {
                $(modal).html(html);

                $(modal).trigger('processStop');
            });
        }
    });

    return $.magenest.Library;
});
