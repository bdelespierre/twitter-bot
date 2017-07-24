(function(d){
    var css = d.createElement('style'),
        img = d.createElement('img'),
        msg = d.createElement('span');

    css.type = 'text/css';
    css.appendChild(d.createTextNode("{{ $css }}"));
    d.head.appendChild(css);

    msg.classList.add('buffer-add-item-message');
    msg.innerHTML = 'Saved';
    d.body.appendChild(msg);

    img.addEventListener('load', function() {
        msg.classList.add('slide-out-top');
    });

    img.addEventListener('error', function() {
        msg.classList.add('error');
        msg.innerHTML = 'Error!';
    });

    img.src = "//{{ Request::server('HTTP_HOST') }}/buffer/pixel?" + Math.random();
    d.body.appendChild(img);
})(document);