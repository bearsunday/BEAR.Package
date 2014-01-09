var MyDialogs = {
    confirm:function (confirmURL, body) {
        if(window.confirm(body)){
            location.href = confirmURL;
        }
    }
}
