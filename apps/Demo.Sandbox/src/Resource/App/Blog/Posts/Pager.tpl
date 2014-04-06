<script src="/assets/js/delete_post.js"></script>
<table class="table table-bordered table-striped">
    <tr>
        <th class="span1">Id</th>
        <th>Title</th>
        <th>Body</th>
        <th>CreatedAt</th>
    </tr>
{foreach from=$resource->body item=post}
    <tr>
        <td>{$post.id}</td>
        <td><a href="post?id={$post.id}">{$post.title|escape}</a></td>
        <td>{$post.body|truncate:60|escape}</td>
        <td>{$post.created}</td>
    </tr>
{/foreach}
</table>
