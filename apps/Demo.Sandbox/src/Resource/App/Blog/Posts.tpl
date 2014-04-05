<script src="/assets/js/modal.js"></script>
<script src="/assets/js/delete_post.js"></script>
<table class="table table-bordered table-striped">
    <tr>
        <th class="span1">Id</th>
        <th>Title</th>
        <th>Body</th>
        <th>CreatedAt</th>
        <th></th>
    </tr>
{foreach from=$resource->body item=post}
    <tr>
        <td>{$post.id}</td>
        <td><a href="{href rel="page_item" data=$post}">{$post.title|escape}</a></td>
        <td>{$post.body|truncate:60|escape}</td>
        <td>{$post.created}</td>
        <td>
            <a title="Edit post" class="btn" href="/blog/posts/edit?id={$post.id}"><span class="glyphicon glyphicon-edit"></span></a>
            <a title="Delete post" class="btn remove confirm" href="#"><span class="glyphicon glyphicon-trash" data-post-id="{$post.id}"></span></a>
        </td>
    </tr>
{/foreach}
</table>
