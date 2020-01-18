define([
    'jquery',
    'ko',
    'uiComponent',
    'underscore',
    'CodeMirror',
    'CodeMirrorXml',
    'schema'
], function ($, ko, Component, _, CodeMirror) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magenest_ProductFeed/edit/tab/schema/xml'
        },

        pattern: new $.Pattern(),

        xmlLine: null,

        initialize: function () {
            var self = this;

            this._super();

            _.bindAll(this, 'initEditor');
        },

        initObservable: function () {
            this._super()
                .observe('liquidTemplate');

            return this;
        },

        initEditor: function (area) {
            var self = this;

            this.editor = CodeMirror.fromTextArea(area, {
                value: 'fadfa',
                mode: {
                    name: 'text/xml',
                    alignCDATA: true
                },
                lineNumbers: true,
                matchTags: true,
                viewportMargin: Infinity,
                styleActiveLine: true,
                tabSize: 2,
                indentUnit: 2,
                indentWithTabs: false,
                extraKeys: {
                    "Tab": function (cm) {
                        cm.replaceSelection("  ", "end");
                    }
                }
            });

            this.editor.addOverlay({
                token: function (stream) {
                    var query = /^{{.*?}}/g;
                    if (stream.match(query)) {
                        return 'liquid-variable';
                    }
                    stream.next();
                }
            });

            this.editor.addOverlay({
                token: function (stream) {
                    var query = /^{%.*?%}/g;
                    if (stream.match(query)) {
                        return 'liquid-statement';
                    }
                    stream.next();
                }
            });

            this.editor.setValue(this.liquidTemplate());
            this.editor.setSize();

            this.xmlLine = ko.observable();

            self.xmlLine.subscribe(function () {
                self.pattern.parse(self.xmlLine());
            });

            self.pattern.toString.subscribe(function () {
                if (self.pattern.toString()) {
                    var ln = self.editor.getCursor().line;
                    var ch = self.editor.getCursor().ch;

                    self.editor.replaceRange(
                        self.pattern.toString(),
                        {line: ln, ch: self.pattern.parsedChFrom},
                        {line: ln, ch: self.pattern.parsedChTo}
                    );

                    // restore cursor position
                    self.editor.setCursor(ln, ch);
                }
            });

            setInterval(function () {
                self.editor.refresh();
                self.editor.save()
            }, 100);

            setInterval(function () {
                var text = self.editor.getLine(self.editor.getCursor().line);
                self.xmlLine(text);
            }, 50);
        }
    });

});