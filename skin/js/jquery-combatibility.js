/**
 * jQuery 4.0 Compatibility Patch
 * Stellt entfernte Funktionen für ältere Plugins (Gridster, UI, etc.) wieder her.
 */
(function($) {
    if (typeof $ === 'undefined') return;

    // 1. Bootstrap 3 Trick: Version vorgaukeln
    $.fn.jquery = "3.7.1";

    // 2. Statische Methoden auf dem $-Objekt
    if (!$.isArray)    $.isArray = Array.isArray;
    if (!$.isFunction) $.isFunction = (obj) => typeof obj === 'function';
    if (!$.isWindow)   $.isWindow = (obj) => obj != null && obj === obj.window;
    if (!$.type)       $.type = (obj) => Object.prototype.toString.call(obj).replace(/^\[object (.+)\]$/, "$1").toLowerCase();
    if (!$.now)        $.now = Date.now;

    if (!$.camelCase) {
        $.camelCase = (str) => str.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
    }

    // 3. Methoden für jQuery-Instanzen (Prototyp)
    // Dies fixiert den "a.sort is not a function" Fehler
    if (!$.fn.sort) {
        $.fn.sort = Array.prototype.sort;
    }

    // 4. $.swap wird oft für Dimensionen in alten Plugins genutzt
    if (!$.swap) {
        $.swap = function(elem, options, callback, args) {
            var ret, name, old = {};
            for (name in options) {
                old[name] = elem.style[name];
                elem.style[name] = options[name];
            }
            ret = callback.apply(elem, args || []);
            for (name in options) {
                elem.style[name] = old[name];
            }
            return ret;
        };
    }
})(jQuery);