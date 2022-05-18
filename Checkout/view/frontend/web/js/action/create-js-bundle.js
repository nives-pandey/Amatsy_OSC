define([
    'underscore',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder'
], function (_, storage, urlBuilder) {
    "use strict";

    var API_PATH = '/amasty_checkout/js/create-bundle';

    return function () {
        var ulr = urlBuilder.createUrl(API_PATH, {}),
            match = /\/frontend\//.exec(Object.keys(require.s.contexts._.urlFetched)[0]),
            trashStringSize = match.index || 0,
            data = [];

        _.each(require.s.contexts._.urlFetched, function (val, key) {
            data.push(key.substr(trashStringSize));
        });

        _.each(require.s.contexts._.defined, function (val, key) {
            if (key.substr(0, 5) === "text!") {
                // collect all html
                data.push(require.toUrl(key.substr(5)).substr(trashStringSize));
            }
        });

        storage.post(ulr, JSON.stringify({fileNames: data}), false);
    };
});
