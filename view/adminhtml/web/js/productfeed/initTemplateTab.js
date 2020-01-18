define([
    'jquery',
    'underscore',
    'Magenest_ProductFeed/js/lib/codemirror',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Magenest_ProductFeed/js/mode/xml/xml',
    'Magenest_ProductFeed/js/addon/display/autorefresh',
    'Magenest_ProductFeed/js/addon/mode/overlay',
    'jquery/ui'
], function ($, _, modal, $t) {
    "use strict";

    var loadTempBtn = $('#load-template');
    var fieldsColEl = $('#fields-map .fields-col');
    var attrSelectHtml = $('#select-attr').html();
    var attrSelectTemplate = $('#attr-template').html();

    $.widget('magenest.initTemplateTab', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.initLoadTemplate();

            this.initObservable();
            this.initDragable();
            this.initFieldsMap();
        },
        initObservable: function () {
            this.rowSelectObs();
            this.inputObs();
            this.removeModifierObs();
            this.addModifierObs();
        },

        initDragable: function () {
            var self = this;
            $('#insert-variable-popup .modifier-group').sortable({
                stop: function (event, ui) {
                    var attr_code = $(this).attr('code');
                    self.updateVariable(attr_code);
                }
            });
        },
        initLoadTemplate: function () {
            var self = this;
            loadTempBtn.click(function () {
                loadTempBtn.text($t('Loading...')).addClass('loading');
                $.ajax({
                    url: self.options.url,
                    data: {name: $('#feed_default_template').val()},
                    type: 'POST',
                    success: function (res) {
                        $('#feed_file_type').val(res.file_type);
                        $('#feed_field_separate').val(res.field_separate);
                        $('#feed_field_around').val(res.field_around);
                        $('#feed_include_header').val(res.include_header);
                        if (res.template_html && res.template_html !== '') {
                            $('#feed_template_html').val(res.template_html);
                            self.options.doc.setValue(res.template_html);
                        }
                        if (res.fields_map && res.fields_map !== '') {
                            $('#fields-map .fields-col').html('');
                            self.renderFieldsMap(JSON.parse(res.fields_map));
                        }
                        document.getElementById('feed_file_type').dispatchEvent(new Event('change'));
                        loadTempBtn.text($t('Load Template')).removeClass('loading');
                    }
                });
            });
        },

        rowSelectObs: function () {
            var self = this;
            $('#insert-variable-popup').on('change', 'select', function () {
                var elf = $(this);
                var paramsEl = elf.siblings('.params');
                var attr_code = elf.parents('.modifier').attr('code');
                paramsEl.html('');
                if (elf.val() !== 0) {
                    _.each(self.options.modifiersData[this.value].params, function (record, index) {
                        paramsEl.append('<span class="modifier-param">' + record.label + '</span><input class="modifier-param" type="text" code="' + attr_code + '"/>')
                    });
                }
                self.updateVariable(attr_code);
            });
        },
        inputObs: function () {
            var self = this;
            $('#insert-variable-popup').on('change', 'input', function () {
                var attr_code = $(this).attr('code');
                self.updateVariable(attr_code);
            });
        },
        removeModifierObs: function () {
            var self = this;
            $('#insert-variable-popup').on('click', '.remove-modifier', function () {
                var attr_code = $(this).parents('.modifier').attr('code');
                $(this).parent().remove();
                self.updateVariable(attr_code);
            });
        },
        addModifierObs: function () {
            var self = this;
            $('#insert-variable-popup').on('click', '.add-modifier', function () {
                var opt = '';
                var attr_code = $(this).parents('.attr-code').attr('code');
                _.each(self.options.modifiersData, function (record, index) {
                    opt += '<option value="' + index + '">' + record.label + '</option>';
                });
                var modifierEl = '<div class="modifier" code="' + attr_code + '"><div class="row"><select><option value="0">' + $t('--Please Select--') + '</option>' +
                    opt +
                    '</select><div class="params"></div><button class="remove-modifier">' + $t('Remove') + '</button></div></div>';
                $(this).parent().parent().find('.modifier-group').append(modifierEl);
            });
        },
        updateVariable: function (attr_code) {
            var parentEl = $('[code="' + attr_code + '"]');
            var str = '{{ ';
            str += 'product.' + attr_code;
            parentEl.find('.modifier').each(function () {
                var modifier = $(this).find('select').val();
                if (modifier && modifier !== '0') {
                    str += ' | ' + modifier;
                }
                var params = $(this).find('input.modifier-param');
                if (params.length) {
                    str += ': ';

                    params.each(function (index) {
                        if (index === (params.length - 1)) {
                            str += "'" + this.value + "'";
                            return;
                        }
                        str += "'" + this.value + "', ";
                    });
                }
            });
            str += ' }}';
            parentEl.find('.liquid-variable').text(str);
        },
        initFieldsMap: function () {
            this.modiferCollapse();
            this.changeValObs();
            this.removeFieldsMapModifierObs();
            this.selectModifierObs();
            this.addFieldsMapModifierObs();
            this.removeRowObs();
            this.selectTypeObs();
            this.addRowObs();
            this.initFieldsMapDragable();
            this.renderFieldsMap(this.options.fieldsMap.fields_map);
        },
        modiferCollapse: function () {
            var self = this;
            $('#fields-map').on('click', 'a.modifier-collapse', function () {
                $(this).parents('.field-col').find('.modifier-group').toggle();
                var i = $(this).find('i');
                self.collapse(i);
            });
        },
        changeValObs: function () {
            var self = this;
            $('#fields-map').on('change', '.col-value input,.modifier-group input,.col-value select', function () {
                var attrEl = $(this).parents('.field-col');
                self.updateFieldMapVariable(attrEl);
            });
        },
        removeFieldsMapModifierObs: function () {
            var self = this;
            $('#fields-map').on('click', 'a.remove-modifier', function () {
                var attrEl = $(this).parents('.field-col');
                $(this).parent().remove();
                self.updateFieldMapVariable(attrEl);
            });
        },
        selectModifierObs: function () {
            var self = this;
            $('#fields-map').on('change', '.modifier select', function () {
                var modifierId = $(this).parents('.modifier').attr('id');
                // var attrEl = $(this).parents('.field-col');
                var elf = $(this);
                var paramsEl = elf.siblings('.params');
                // var attr_code = elf.parents('.modifier').attr('code');
                var attrEl = $('#' + modifierId).parents('.field-col');
                paramsEl.html('');
                if (elf.val() !== 0) {
                    self.createModifierParams(modifierId);
                }
                self.updateFieldMapVariable(attrEl);
            });
        },
        addFieldsMapModifierObs: function () {
            var self = this;
            $('#fields-map').on('click', 'a.add-modifier', function () {
                if ($(this).parents('.field-col').find('.col-type select').val() == 0) {
                    return;
                }
                var i = $(this).parents('.field-col').find('.col-collapsible i');
                var rowId = this.id;
                var d = new Date();
                var _id = d.getTime() + '_' + d.getMilliseconds();
                self.createModifierRow(rowId, _id);

                var modifierGroupEl = $('#' + rowId).find('.modifier-group');
                modifierGroupEl.show();
                if (i.hasClass('fa-chevron-down')) {
                    i.removeClass('fa-chevron-down');
                    i.addClass('fa-chevron-up');
                }
            });
        },
        removeRowObs: function () {
            $('#fields-map').on('click', 'a.col-remove', function () {
                $(this).parents('.field-col').remove();
            });
        },
        selectTypeObs: function () {
            $('#fields-map').on('change', '.col-type select', function () {
                var typeEl = $(this);
                var valEl = typeEl.parent().siblings('.col-value');
                if (typeEl.val() === 'attribute') {
                    typeEl.parent().siblings('.col-add-modifier').show();
                    typeEl.parent().siblings('.col-collapsible').css('visibility', 'visible');
                    valEl.find('input').hide();
                    valEl.find('select').show();
                } else {
                    typeEl.parent().siblings('.col-add-modifier').hide();
                    typeEl.parent().siblings('.col-collapsible').css('visibility', 'hidden');

                    valEl.find('input').show();
                    valEl.find('select').hide();
                }
            });
        },
        addRowObs: function () {
            var self = this;
            $('#add-column').click(function () {
                var d = new Date();
                var _id = d.getTime() + '_' + d.getMilliseconds();
                if (self.options.tempType == 'google') {
                    self.rowGoogleXml(_id);
                } else if (self.options.tempType == 'facebook') {
                    self.rowFacebookCsv(_id);
                }
            });
        },
        initFieldsMapDragable: function () {
            var self = this;
            $('.fields-col').sortable();
            $('#fields-map .modifier-group').sortable({
                stop: function (event, ui) {
                    var attrEl = $(this).parents('.field-col');
                    self.updateFieldMapVariable(attrEl);
                }
            });
        },
        rowGoogleXml: function (_id) {
            var $htmlGoogle =
                '                        <div class="field-col row" id="' + _id + '">' +
                '                            <div class="col-row row">' +
                '                            <div class="col-drag">\n' +
                '                            </div>\n' +
                '                            <div class="col-name">\n' +
                '                                <select name="feed[fields_map][' + _id + '][col_attr_temp]">' + attrSelectTemplate +
                '                                </select>' +
                '                            </div>\n' +
                '                            <div class="col-type">\n' +
                '                                <select name="feed[fields_map][' + _id + '][col_type]">\n' +
                '                                    <option value="attribute">' + ('Atttribute') + '</option>\n' +
                '                                    </select>\n' +
                '                            </div>\n' +
                '                            <div class="col-value">\n' +
                '                                <select name="feed[fields_map][' + _id + '][col_attr_val]">' + attrSelectHtml +
                '                                </select>' +
                '                                <input name="feed[fields_map][' + _id + '][col_pattern_val]" type="text" class="pattern" style="display: none"/>\n' +
                '                                <input name="feed[fields_map][' + _id + '][col_val]" type="hidden" class="liquid-variable">\n' +
                '                            </div>\n' +
                '                            <div class="col-remove">' +
                '                                <a class="col-remove btn">' + ('Remove') + '</a>' +
                '                            </div>\n' +
                '                            <div class="col-add-modifier">' +
                '                                <a class="col-add-modifier add-modifier btn" id="' + _id + '">' + ('Add Modifier') + '</a>' +
                '                            </div>' +
                '                            </div>' +
                '                            <div class ="modifier-group"></div>' +
                '                        </div>';
            // $('a.col-remove').removeClass('col-remove').addClass('cannot_remove');
            // $('.cannot_remove').attr('disable',true);
            fieldsColEl.append($htmlGoogle);
        },
        rowFacebookCsv: function (_id) {
            var $htmlFacebook =
                '                        <div class="field-col row" id="' + _id + '">' +
                '                            <div class="col-row row">' +
                '                            <div class="col-drag">\n' +
                '                            </div>\n' +
                '                            <div class="col-name">\n' +
                '                                <input type="text" name="feed[fields_map][' + _id + '][col_name]"/>\n' +
                '                            </div>\n' +
                '                            <div class="col-type">\n' +
                '                                <select name="feed[fields_map][' + _id + '][col_type]">\n' +
                '                                    <option value="attribute">' + ('Atttribute') + '</option>\n' +
                '                                    <option value="pattern">' + ('Pattern') + '</option>\n' +
                '                                </select>\n' +
                '                            </div>\n' +
                '                            <div class="col-value">\n' +
                '                                <select name="feed[fields_map][' + _id + '][col_attr_val]">' + attrSelectHtml +
                '                                </select>' +
                '                                <input name="feed[fields_map][' + _id + '][col_pattern_val]" type="text" class="pattern" style="display: none"/>\n' +
                '                                <input name="feed[fields_map][' + _id + '][col_val]" type="hidden" class="liquid-variable">\n' +
                '                            </div>\n' +
                '                            <div class="col-remove">' +
                '                                <a class="col-remove btn">' + ('Remove') + '</a>' +
                '                            </div>\n' +
                '                            <div class="col-add-modifier">' +
                '                                <a class="col-add-modifier add-modifier btn" id="' + _id + '">' + ('Add Modifier') + '</a>' +
                '                            </div>' +
                '                            </div>' +
                '                            <div class ="modifier-group"></div>' +
                '                        </div>';
            // $('a.col-remove').removeClass('col-remove').addClass('cannot_remove');
            // $('.cannot_remove').attr('disable',true);
            fieldsColEl.append($htmlFacebook);
        },
        createModifierParams: function (modifierId, params) {
            var self = this,
                modifierName = $('#' + modifierId).attr('name'),
                paramsEl = $('#' + modifierId + ' .params'),
                attr_code = $('#' + modifierId + ' select').val(),
                params = params || {};

            if (attr_code === 0) {
                return;
            }

            _.each(self.options.modifiersData[attr_code].params, function (record, index) {
                paramsEl.append('<span class="modifier-param">' + record.label + '</span><input required value="'
                    + (params[index] === undefined ? '' : params[index]) +
                    '" name="' + modifierName + '[params][' + index + ']" class="modifier-param" type="text"/>');
            });
        },
        //create modifiers
        createModifierRow: function (rowId, _id) {
            var self = this;
            var opt = '';
            _.each(self.options.modifiersData, function (record, index) {
                opt += '<option value="' + index + '">' + record.label + '</option>';
            });

            var modifierEl = '<div class="modifier" id="' + _id + '" name="feed[fields_map][' + rowId + '][modifiers][' + _id + ']"><div class="row">' +
                '<select name="feed[fields_map][' + rowId + '][modifiers][' + _id + '][value]" id="feed[fields_map][' + rowId + '][modifiers][' + _id + ']">' +
                '<option value="0">' + ('--Please Select--') + '</option>' +
                opt +
                '</select><div class="params"></div><a class="remove-modifier btn">' + ('Remove') + '</a></div></div>';
            var modifierGroupEl = $('#' + rowId).find('.modifier-group');
            modifierGroupEl.append(modifierEl)
        },
        collapse: function (i) {
            if (i.hasClass('fa-chevron-down')) {
                i.removeClass('fa-chevron-down');
                i.addClass('fa-chevron-up');
            } else {
                i.removeClass('fa-chevron-up');
                i.addClass('fa-chevron-down');
            }
        },
        updateFieldMapVariable: function (attrEl) {
            var attr_code = attrEl.find('.col-value select').val();
            var str = '';
            if (attr_code && attrEl.find('.col-type select').val() === 'attribute') {
                str = '{{ ';
                str += 'product.' + attr_code;
                attrEl.find('.modifier').each(function () {
                    var modifier = $(this).find('select').val();
                    if (modifier === "0") {
                        return;
                    }
                    str += ' | ' + modifier;
                    var params = $(this).find('input.modifier-param');
                    if (params.length) {
                        str += ': ';

                        params.each(function (index) {
                            if (index === (params.length - 1)) {
                                str += "'" + this.value + "'";
                                return;
                            }
                            str += "'" + this.value + "', ";
                        });
                    }
                });
                str += ' }}';
            }
            attrEl.find('input.liquid-variable').val(str);
        },
        renderFieldsMap: function (fieldsMap) {
            var self = this;
            _.each(fieldsMap, function (record, index) {
                if ((record.col_type === 'attribute' && record.col_attr_val === 0)
                    || (record.col_type === 'pattern' && record.col_pattern_val === '')
                ) {
                    return;
                }
                if (self.options.tempType == 'google') {
                    self.rowGoogleXml(index);
                } else if (self.options.tempType == 'facebook') {
                    self.rowFacebookCsv(index);
                }
                $('#' + index + ' .col-name input').val(record.col_name);
                $('#' + index + ' .col-name select').val(record.col_attr_temp);
                $('#' + index + ' .col-value select').val(record.col_attr_val);
                $('#' + index + ' .col-value .pattern').val(record.col_pattern_val);
                $('#' + index + ' .col-value .liquid-variable').val(record.col_val);
                $('#' + index + ' .col-type select').val(record.col_type).trigger('change');

                _.each(record.modifiers, function (modifier, key) {
                    if (modifier.value === 0) {
                        return;
                    }
                    self.createModifierRow(index, key);
                    $('#' + key + ' select').val(modifier.value);
                    if (modifier.params !== undefined) {
                        self.createModifierParams(key, modifier.params);
                    }
                });
                self.updateFieldMapVariable($('#' + index));
            });
        }
    });

    return $.magenest.initTemplateTab;
});
