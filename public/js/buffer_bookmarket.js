(function(d){
    var css = d.createElement('style'),
        img = d.createElement('img'),
        msg = d.createElement('span');

    css.type = 'text/css';
    css.appendChild(document.createTextNode(".buffer-add-item-message{all:initial; display:inline-block; position:fixed; top:10px; right:10px; z-index:9999; background:#2ecc71; padding:20px 40px; border:4px solid #27ae60; color:white; font-weight:bold; font-size:20px} .buffer-add-item-message.error{border-color:#c0392b; background:#e74c3c} .slide-out-top{-webkit-animation:slide-out-top .5s cubic-bezier(.55,.085,.68,.53) 1s both;animation:slide-out-top .5s cubic-bezier(.55,.085,.68,.53) 1s both} @-webkit-keyframes slide-out-top{0%{-webkit-transform:translateY(0);transform:translateY(0);opacity:1}100%{-webkit-transform:translateY(-1000px);transform:translateY(-1000px);opacity:0}} @keyframes slide-out-top{0%{-webkit-transform:translateY(0);transform:translateY(0);opacity:1}100%{-webkit-transform:translateY(-1000px);transform:translateY(-1000px);opacity:0}}"));
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

    img.src = "https://bdelespierre-twitter-bot.herokuapp.com/buffer/pixel?" + Math.random();
    d.body.appendChild(img);
})(document);