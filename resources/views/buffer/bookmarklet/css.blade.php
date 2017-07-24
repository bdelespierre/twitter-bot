.buffer-add-item-message {
    all: initial;
    display: inline-block;
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 9999;
    background: #2ecc71;
    padding: 20px 40px;
    border: 4px solid #27ae60;
    color: white;
    font-weight: bold;
    font-size: 20px;
}

.buffer-add-item-message.error {
    border-color: #c0392b;
    background: #e74c3c;
}

.buffer-add-item-message.slide-out-top {
    animation: buffer-add-item-slide-out-top .5s cubic-bezier(.55,.085,.68,.53) 1s both
}

@keyframes buffer-add-item-slide-out-top {
    0% {
        transform: translateY(0);
        opacity: 1
    }

    100% {
        transform: translateY(-1000px);
        opacity: 0
    }
}
