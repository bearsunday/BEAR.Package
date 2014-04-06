{extends file="layout/Blog.tpl"}
{block name=title}Edit{/block}
{block name=page}

    <ul class="breadcrumb">
        <li><a href="/">Home</a> <span class="divider">/</span></li>
        <li><a href="/blog/posts">Blog</a> <span class="divider">/</span></li>
        <li class="active">Edit Post</li>
    </ul>

    <form action="/blog/posts/edit" method="POST" role="form">
        <input type="hidden" name="_method"  value="PUT"/>
        <input type="hidden" name="id" value="{$id}"/>

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
{/block}