(function($){

    $.fn.infinite = function() {
        var opts = $.extend({
            handle: 'a[rel=next]',
            debug: true
        }, arguments[0] || {});

        return this.each(function(i, el) {
            el.selector = $.getSelectorOf(el);
            el.loading  = false;

            $(document).scroll(function(evt) {
                if (!el.loading &&
                    $(opts.handle, el).length &&
                    $(opts.handle, el).last().offset().top < ($(window).scrollTop() + $(window).height())
                ) {
                    var href = $(opts.handle, el).last().attr('href');
                    $.ajax({
                        url: href,
                        context: el,
                        dataType: 'html',
                        beforeSend: function(xhr) {
                            opts.debug && console.log('Loading ' + href, xhr);
                            this.loading = true;
                        },
                        complete: function() {
                            this.loading = false;
                        },
                        success: function(html) {
                            $(opts.handle, el).remove();
                            var block = document.createElement('div');
                            block.innerHTML = html;
                            $(this.selector + '>*', block).appendTo(this);
                        }
                    });
                }
            });
        });
    };

    $('[rel=infinite]').infinite();

})(window.jQuery);