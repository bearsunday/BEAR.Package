<h1>New Post</h1>
<form action="/blog/posts/newpost" method="POST" role="form">
    <div class="form-group {if $errors.title}has-error{/if}">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="{$submit.title}"class="form-control">
        <label class="control-label" for="title">{$errors.title}</label>
    </div>
    <div class="form-group {if $errors.body}has-error{/if}">
        <label for="body">Body</label>
        <textarea name="body" rows="10" cols="40" class="form-control" id="body">{$submit.body}</textarea>
        <label class="control-label" for="body">{$errors.body}</label>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>
