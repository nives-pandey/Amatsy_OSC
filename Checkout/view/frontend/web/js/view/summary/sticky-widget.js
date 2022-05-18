define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('amasty.stickyWidget', {
        options: {
            //selectors
            parentContainer: '[data-amsticky-js="main-container"]',
            element: '[data-amsticky-js="sticky-element"]',
            scrollContentContainer: '[data-amsticky-js="scroll-element"]',
            stopper: '[data-amsticky-js="stopper"]',

            //sticky options
            stickyOffset: {
                top: 0,
                left: 0
            },
            stickyPosition: 'right',
            stickyClass: '-sticky',
            disableBreakpoint: 1024
        },

        _create: function () {
            var toggleSticky = this.stickyToggle.bind(this),
                windowGlobal = window;

            if ($(windowGlobal).width() >= this.options.disableBreakpoint) {
                if ($(this.options.scrollContentContainer).length) {
                    this.stickyPosition(this.options.stickyPosition);
                };

                $(windowGlobal).off('scroll', toggleSticky).on('scroll', toggleSticky);
            }

            $(windowGlobal).on('resize', function () {
                this.stickyToggle();
                if ($(windowGlobal).width() >= this.options.disableBreakpoint) {
                    this.stickyPosition(this.options.stickyPosition);
                    $(windowGlobal).off('scroll', toggleSticky).on('scroll', toggleSticky);
                } else {
                    $(windowGlobal).off('scroll', toggleSticky);
                }
            }.bind(this));
        },

        stickyToggle: function () {
            var stickyOptions = this.options,
                parentIsSet = $(stickyOptions.parentContainer).length,
                parentContainer = (parentIsSet)
                    ? $(stickyOptions.parentContainer)
                    : $('body'),
                parentContainerOffset = parentContainer.offset(),
                scrollContentIsSet = $(stickyOptions.scrollContentContainer).length,
                scrollContent = (scrollContentIsSet)
                    ? $(stickyOptions.scrollContentContainer)
                    : $('body'),
                stickyElement = $(stickyOptions.element),
                stickyIsRight = stickyOptions.stickyPosition == 'right',
                stickyOffset = stickyOptions.stickyOffset,
                stickyOffsetLeft = (scrollContentIsSet)
                    ? scrollContent.offset().left + scrollContent.outerWidth() + stickyOffset.left
                    : stickyOffset.left,
                stopperOffset = ($(stickyOptions.stopper).length)
                    ? $(stickyOptions.stopper).offset()
                    :   {
                            top: parentContainerOffset.top + parentContainer.height(),
                            left: parentContainerOffset.left
                        },
                windowScrollTop = $(window).scrollTop();

            if ($(window).width() < stickyOptions.disableBreakpoint) {
                this.enableMobileLayout();

                return;
            }

            if (parentContainer.height() <= stickyElement.height()) {
                this.stickyDisable(stickyElement);

                return;
            }

            if (stopperOffset.top - stickyElement[0].scrollHeight < windowScrollTop) {
                if (stickyElement.css('position') != 'absolute') {
                    stickyElement.css({
                        position: 'absolute',
                        top: 'initial',
                        right: stickyIsRight ? 0 : 'initial',
                        bottom: parentContainer.height() - (stopperOffset.top - parentContainerOffset.top),
                        left: stickyIsRight ? 'initial' : 0
                    });
                    stickyElement.removeClass(stickyOptions.stickyClass);
                }
            } else if (parentContainerOffset.top < windowScrollTop + stickyOffset.top) {
                if (stickyElement.css('position') != 'fixed') {
                    stickyElement.css({
                        position: 'fixed',
                        top: stickyOffset.top,
                        right: 'initial',
                        bottom: 'initial',
                        left: stickyIsRight ? stickyOffsetLeft : (parentContainerOffset.left + stickyOffset.left)
                    });
                    stickyElement.addClass(this.options.stickyClass);
                }
            } else {
                this.stickyDisable(stickyElement);
            }
        },

        stickyPosition: function (position) {
            var stickyElement = $(this.options.element),
                stickyFloat = (position == 'right') ? 'right' : 'left',
                scrollContent = $(this.options.scrollContentContainer),
                scrollContentFloat = (stickyFloat == 'right') ? 'left' : 'right';

            stickyElement.css('float', stickyFloat);
            scrollContent.css('float', scrollContentFloat);
        },

        stickyDisable: function (stickyElement) {
            if (stickyElement.css('position') != 'static') {
                stickyElement.css({
                    position: 'static',
                    top: 'initial',
                    right: 'initial',
                    bottom: 'initial',
                    left: 'initial'
                });
                stickyElement.removeClass(this.options.stickyClass);
            }
        },

        enableMobileLayout: function () {
            var stickyElement = $(this.options.element),
                scrollContent = $(this.options.scrollContentContainer);

            this.stickyDisable(stickyElement);
            stickyElement.css('float', 'none');
            if (scrollContent.length) scrollContent.css('float', 'none');
        }
    });

    return $.amasty.stickyWidget;
});
