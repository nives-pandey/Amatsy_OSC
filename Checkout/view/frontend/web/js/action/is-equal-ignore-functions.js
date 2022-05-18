/**
 * Extended copy of underscore comparison function for `isEqual` with.
 * Underscore's 'isEqual' doesn't compare functions correctly.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    var isEqualIgnoreFunctions = function (a, b, aStack, bStack) {
        var className,
            areArrays,
            key,
            length,
            aCtor,
            bCtor,
            keys,
            isFunction;

        if (a === b) {
            return a !== 0 || 1 / a === 1 / b;
        }

        if (a == null || b == null) {
            return a === b;
        }

        if (a instanceof _) {
            a = a._wrapped;
        }

        if (b instanceof _) {
            b = b._wrapped;
        }

        className = Object.prototype.toString.call(a);

        if (className !== Object.prototype.toString.call(b)) {
            return false;
        }

        switch (className) {
            case '[object RegExp]':
            case '[object String]':
                return '' + a === '' + b;
            case '[object Number]':
                if (+a !== +a) {
                    return +b !== +b;
                }

                return +a === 0 ? 1 / +a === 1 / b : +a === +b;
            case '[object Date]':
            case '[object Boolean]':
                return +a === +b;
            default:
                break;
        }

        areArrays = className === '[object Array]';

        if (!areArrays) {
            if (typeof a != 'object' || typeof b != 'object') { return false; }

            aCtor = a.constructor;
            bCtor = b.constructor;

            if (aCtor !== bCtor && !(_.isFunction(aCtor) && aCtor instanceof aCtor &&
                _.isFunction(bCtor) && bCtor instanceof bCtor) &&
                ('constructor' in a && 'constructor' in b)) {
                return false;
            }
        }

        aStack = aStack || [];
        bStack = bStack || [];
        length = aStack.length;

        while (length--) {
            if (aStack[length] === a) {
                return bStack[length] === b;
            }
        }

        aStack.push(a);
        bStack.push(b);

        if (areArrays) {
            length = a.length;

            if (length !== b.length) {
                return false;
            }

            while (length--) {
                if (!isEqualIgnoreFunctions(a[length], b[length], aStack, bStack)) {
                    return false;
                }
            }
        } else {
            keys = _.keys(a);
            length = keys.length;

            if (_.keys(b).length !== length) {
                return false;
            }

            while (length--) {
                key = keys[length];

                // Custom Amasty check
                isFunction = _.isFunction(a[key]) || _.isFunction(b[key]);

                if (!isFunction &&
                    !(_.has(b, key) && isEqualIgnoreFunctions(a[key], b[key], aStack, bStack))) {
                    return false;
                }
            }
        }

        aStack.pop();
        bStack.pop();

        return true;
    }

    return isEqualIgnoreFunctions;
});
