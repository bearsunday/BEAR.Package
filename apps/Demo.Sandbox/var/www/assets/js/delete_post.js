$(document).ready(function () {
    $('.confirm').click(function () {
        confirm('Are you sure you want to delete this?');
    });
    $('.remove').click(function (event) {
        event.preventDefault();
        url = event.target.href;
        var id = url.substring(url.indexOf('#') + 1);
        $.ajax({
            url: '/blog/posts/post',
            type: "POST",
            headers: {
                'X-HTTP-Method-Override': 'DELETE'
            },
            data: 'id=' + id,
            success: function () {
                window.location = "/blog/posts";
            }
        });
    });
});
