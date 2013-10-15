{extends file="layout/Blog.tpl"}
{block name=title}{$post.title}{/block}
{block name=page}
<ul class="breadcrumb">
    <li><a href="/">Home</a> <span class="divider">/</span></li>
    <li><a href="/blog/posts">Blog</a> <span class="divider">/</span></li>
    <li class="active">Post</li>
</ul>

<h1>{$post.title|escape}</h1>
<span class="label label-info">{$post.created}</span>
<div style="padding: 10px"></div>
<p style="font-size: 24px; font-family: serif; line-height:1.2em">{$post.body|escape|nl2br}</p>
{/block}