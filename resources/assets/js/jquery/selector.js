!(function ($, undefined) {

    $.getSelectorOf = function (element, ignore) {
        var pieces = [];

        do {
            if (!element || !element.tagName) {
                break;
            }

            if (-1 !== (ignore || ['HTML', 'BODY']).indexOf(element.tagName)) {
                continue;
            }

            if (element.className) {
                var classes = element.className.split(' ');
                for (var i in classes) {
                    if (classes.hasOwnProperty(i) && classes[i]) {
                        pieces.unshift('.' + classes[i]);
                    }
                }
            }

            if (element.id && !/\s/.test(element.id)) {
                pieces.unshift('#' + element.id);
            }

            pieces.unshift('>' + element.tagName.toLowerCase());

        } while (element = element.parentNode)

        return pieces.slice(1).join('');
    };

})(window.jQuery);