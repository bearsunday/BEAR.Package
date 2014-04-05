$(document).ready(function () {
    $('.confirm').click(function () {
        confirm('Are you sure you want to delete this?');
    });
    $('.remove').click(function (event) {
        event.preventDefault();
        var postId = $(event.target).data('postId');
        $.ajax({
            url: '/blog/posts/post',
            type: "POST",
            headers: {
                'X-HTTP-Method-Override': 'DELETE'
            },
            data: 'id=' + postId,
            success: function () {
                window.location = "/blog/posts";
            }
        });
    });
});
