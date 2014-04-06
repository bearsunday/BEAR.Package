{extends file="layout/demo.tpl"}
{block name=title}Aura Form{/block}

{block name=page}
    {if $code === 201}
        Name:{$name}<br>
        Email:{$email}<br>
        URL:{$url}<br>
        Message:{$message}<br>
    {else}
        <form action="/demo/form/auraform" method="POST" enctype="multipart/form-data">
            <input name="_method" type="hidden" value="POST"/>

                <div class="control-group {if $form.name.error}error{/if}">
                    <label class="control-label" for="title">Name</label>
                    <div class="controls">
                        {form type="field" name=$name}
                        <p class="help-inline">{$form.name.error}</p>
                    </div>
                </div>

                <div class="control-group {if $form.email.error}error{/if}">
                    <label class="control-label" for="title">Email</label>
                    <div class="controls">
                        {form type="field" name=$email}
                        <p class="help-inline">{$form.email.error}</p>
                    </div>
                </div>

                <div class="control-group {if $form.url.error}error{/if}">
                    <label class="control-label" for="title">URL</label>
                    <div class="controls">
                        {form type="field" name=$url}
                        <p class="help-inline">{$form.url.error}</p>
                    </div>
                </div>

                <div class="control-group {if $form.message.error}error{/if}">
                    <label class="control-label" for="title">Message</label>
                    <div class="controls">
                        {form type="field" name=$message}
                        <p class="help-inline">{$form.message.error}</p>
                    </div>
                </div>

            <input type="submit" name="submit" value="send" />
        </form>
    {/if}
{/block}