define([
    'jquery',
    'jquery/ui',
    'mage/mage',
    'mage/backend/suggest'
], function ($, ko, _, Component) {
    'use strict';

    $.widget('feed.search', $.mage.suggest, {
        options: {
            loadingClass: ''
        },

        _create: function () {
            this._superApply(arguments);

            this._on($.extend({
                change: function (event) {
                    var val = $(event.target).val();
                    this.valueField.val(val);
                }.bind(this)
            }))
        },

        _createValueField: function() {
            var $input = $('<input/>', {
                type: 'hidden'
            });

            $input.val(this.element.val());
            return $input;
        },

        search: function (e) {
            var self = this;
            var value = this._value();
            if (!value && this.placeholder()) {
                this.setValue(this.placeholder());
            }

            if (this._term == value && typeof this.options.data != 'undefined') {
                this._term = '';
                this._value();
            }

            if (this.options.showRecent) {
                if (this._recentItems.length) {
                    this._processResponse(e, this._recentItems, {});
                } else {
                    this._showAll(e);
                }
            } else if (this.options.showAll) {
                this._showAll(e);
            }

            this._superApply(arguments);
        },

        _source: function (term, response) {
            var o = this.options;

            if ($.isArray(o.source)) {
                response(this.filter(o.source, term));
            } else if ($.type(o.source) === 'string') {
                if (this._xhr) {
                    this._xhr.abort();
                }
                var suggestions = $.sessionStorage.get('feed_index_search_');
                if (!suggestions) {
                    suggestions = {};
                    $.sessionStorage.set('feed_index_search_', suggestions);
                }
                if (typeof suggestions[term] != 'undefined') {
                    this.options.data = suggestions[term];
                    $.extend(arguments, [suggestions[term], 'success']);
                    response.apply(response, arguments);
                } else {
                    var parent = $(this.valueField).parent();
                    var ajaxData = {};
                    ajaxData[this.options.termAjaxArgument] = term;
                    $('.feed__dynamic-category-search', parent).show();

                    this._xhr = $.ajax($.extend(true, {
                        url: o.source,
                        type: 'POST',
                        dataType: 'json',
                        data: ajaxData,
                        success: $.proxy(function (items) {
                            $('.feed__dynamic-category-search', parent).hide();

                            var data = $.sessionStorage.get('feed_index_search_');
                            data[term] = items;
                            $.sessionStorage.set('feed_index_search_', data);

                            this.options.data = items;
                            response.apply(response, arguments);
                        }, this)
                    }, o.ajaxOptions || {}));
                }
            } else if ($.type(o.source) === 'function') {
                o.source.apply(o.source, arguments);
            }
        },


        setValue: function (val) {
            return $.trim(this.element[this.element.is(':input') ? 'val' : 'text'](val));
        },


        placeholder: function () {
            return $.trim(this.element.attr('placeholder'));
        }
    });

    return $.feed.search;

});