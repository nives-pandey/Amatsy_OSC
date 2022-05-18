//when new error message added - scroll page to message block
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/lib/knockout/extender/bound-nodes'
], function ($, _, boundNodes) {
    'use strict';

    return function (Component) {
        return Component.extend({
            defaults: {
                scrollAllowed: true,
                scrollOffset: 0
            },
            initialize: function () {
                this._super();

                if (this.scrollAllowed === true && this.messageContainer.errorMessages) {
                    this.messageContainer.errorMessages.subscribe(function (changes) {
                        try {
                            _.find(changes, function (change) {
                                if (change.status === 'added') {
                                    this._scrollToMessageBlock();

                                    return true;//break
                                }
                            }, this);
                        } catch (e) {
                            console.log('Unable to scroll to error message.');
                            console.debug(e);
                        }
                    }, this, 'arrayChange');
                }

                return this;
            },

            /**
             * Scroll page to the message block
             * @private
             */
            _scrollToMessageBlock: function () {
                var componentNodes = boundNodes.get(this),
                    $element = $(componentNodes),
                    offset = $element.offset().top,
                    scrollTo = false,
                    windowTop = $(window).scrollTop(),
                    windowHeight = $(window).height(),
                    windowBottom = windowTop + windowHeight;

                if (offset < 0 || !$element.is(':visible')) {
                    return;
                }

                if (windowTop > offset) {
                    scrollTo = offset;
                } else if (windowBottom < offset) {
                    //scroll page to bottom screen border
                    scrollTo = offset - windowHeight * 0.7;
                }

                if (scrollTo !== false) {
                    $([document.documentElement, document.body]).animate({
                        scrollTop: scrollTo + this.scrollOffset
                    }, 250);
                }
            }
        });
    };
});
