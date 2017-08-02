(function($){

    $.fn.ajaxify = function() {
        var opts = $.extend({
            handle: '[rel=conainer]',
            debug: true
        }, arguments[0] || {});

        return this.each(function(i, el) {
            var $container = $(el).parentsUntil(opts.handle).last(),
                selector = $.getSelectorOf($container);

            el.loading = false;

            if ($(el).is('a[href]')) {
                $(el).click(function(event) {
                    event.preventDefault();
                    if (this.loading) return;

                    var href = $(this).attr('href');
                    $.ajax({
                        url: href,
                        context: this,
                        dataType: 'html',
                        beforeSend: function(xhr) {
                            opts.debug && console.log("Loading " + href, xhr);
                            this.loading = true;
                            $container.css('opacity', .25);
                        },
                        complete: function() {
                            this.loading = false;
                        },
                        success: function(html) {
                            var block = document.createElement('div');
                            block.innerHTML = html;

                            $container.css('opacity', 1);

                            // dirty patch
                            var toRemove = $('>*', $container);
                            $container.append($(selector + '>*', block));
                            toRemove.remove();

                            // dirty patch
                            $('[rel=ajaxify]', $container).ajaxify();
                        }
                    })
                });
            }
        });
    };

    $('[rel=ajaxify]').ajaxify();

})(window.jQuery)