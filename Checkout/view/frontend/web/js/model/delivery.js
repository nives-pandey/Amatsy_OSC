/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';

        return {
            isLoading: ko.observable(false)
        }
    }
);
