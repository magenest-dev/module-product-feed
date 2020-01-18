    define([
    'jquery',
    'uiComponent',
    'mage/url',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'uiRegistry'
], function ($, Component, urlBuilder, alertModal, modal, uiRegistry) {
    'use strict';
    $.widget('magenest.generate', {
        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this.element
                .off('click.button')
                .on('click.button', $.proxy(this.show, this));

            this._super();
            this.isGenerateInProcess = false;
        },
        show: function () {
            var self = this;

            if (!this.modal) {
                this.modal = $('#generate-queue-process').modal({
                    type: 'popup',
                    title: $.mage.__('Generate ProductFeed'),
                    modalClass: 'feed-aside',
                    closeOnEscape: true,
                    opened: function () {
                        self.loadFeed(modal);
                        // $(this).html(self.loadFeed(modal));
                    }
                });
            }

                this.modal.data('mageModal').openModal();
        },
        loadFeed: function (modal) {
            var self = this;
            var progressbar = $('.queue-process .process-bar');
            this.generateFeed(progressbar.attr("data-percent"));
        },

        generateFeed: function (percent) {
            var self = this;
            self.isGenerateInProcess = true;
            var progressbar = $('.queue-process .process-bar');
            // var progress = 0;
            // var interval = setInterval(function () {
            //     progress = progress >= 99 ? 99 : progress += 1;
            //     progressbar.find('.progress').text(progress + '%');
            //     progressbar.find('.progress').css("width", progress + "%");
            // }, 400);
            $.ajax({
                url: self.options.generateFeedUrl,
                method: 'POST',
                data: {
                    percent: percent
                },
                dataType: 'json',
                success: function (data) {
                    var percent = data.percent;
                    progressbar.data("percent", percent);
                    progressbar.html('<p class="progress success">'+Math.ceil(percent)+'%'+ '</p>');
                    progressbar.css({'width':percent+'%'});
                    if (progressbar.data("percent") < 100) {
                        self.generateFeed(percent);
                    }
                    if (progressbar.data("percent") === 100) {
                        alertModal({
                            title: '<p><b>Feed file was generated!</b></p> ',
                            content: 'Feed Access Url:  ' + '<a style="overflow-wrap:break-word;" target="_blank " href="\ ' + data.urlFile + '\"> ' + data.urlFile + '</a>',
                            buttons: [{
                                text: $.mage.__('Ok'),
                                click: function (event) {
                                    location.reload();
                                }
                            }]
                        });
                    }
                }
            });
        }
    });

    return $.magenest.generate;
});
