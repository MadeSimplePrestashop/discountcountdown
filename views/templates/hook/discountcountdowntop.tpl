{if isset($dc) && $dc && !empty($dc.options->element)}    
    {if isset($dc_message)}
        <script type="text/javascript">
            {if $dc_message==1}
            alert('{$dc.success_message|escape:'html':'UTF-8'}');
            {elseif $dc_message==2}
            alert('{$dc.already_message|escape:'html':'UTF-8'}');
            {/if}
        </script>
    {/if}
    {if $dc.countdown_format==1}
        {$date_format="<span>%D<em>{l s='days' mod='discountcountdown'}</em></span> <span>%H<em>{l s='hours' mod='discountcountdown'}</em></span> <span>%M<em>{l s='minutes' mod='discountcountdown'}</em></span> <span>%S<em>{l s='seconds' mod='discountcountdown'}</em></span>"}
    {else}
        {$date_format="<span>%h<em>{l s='hours' mod='discountcountdown'}</em></span> <span>%M<em>{l s='minutes' mod='discountcountdown'}</em></span> <span>%S<em>{l s='seconds' mod='discountcountdown'}</em></span>"}
    {/if}
    <div class="dc-top" id="dc-top" style="display:none;{if $dc.options->borderWidth}border-width:{$dc.options->borderWidth|escape:'html':'UTF-8'};{/if} {if $dc.options->borderColor}border-color:{$dc.options->borderColor|escape:'html':'UTF-8'};{/if}  {if $dc.options->borderStyle}border-style:{$dc.options->borderStyle|escape:'html':'UTF-8'};{/if} {if $dc.options->backgroundColor}background-color:{$dc.options->backgroundColor|escape:'html':'UTF-8'};{/if} {$dc.options->style|escape:'html':'UTF-8'}">
        {if $dc.options->link}
            <a href="{$dc.options->link|escape:'html':'UTF-8'}">
            {/if}
            <strong>{$dc.caption|escape:'htmlall':'UTF-8'}</strong><span data-date-countdown="{$dc_activated|date_format:"Y-m-d H:i:s"}" data-date-format="{$date_format|escape:'UTF-8'}" class="clock"></span>
            {if $dc.options->link}
            </a>
        {/if}
    </div>
    {if $dc.options->element}
        <script type="text/javascript">
            $(document).ready(function () {
            {if $dc.options->insert == 'after'}
                $('#dc-top').insertAfter($('{$dc.options->element|escape:'html':'UTF-8'}'));
            {elseif $dc.options->insert == 'before'}
                $('#dc-top').insertBefore($('{$dc.options->element|escape:'html':'UTF-8'}'));
            {elseif $dc.options->insert == 'prepend'}
                $('#dc-top').prependTo($('{$dc.options->element|escape:'html':'UTF-8'}'));
            {elseif $dc.options->insert == 'append'}
                $('#dc-top').appendTo($('{$dc.options->element|escape:'html':'UTF-8'}'));
            {elseif $dc.options->insert == 'replace'}
                $('{$dc.options->element|escape:'html':'UTF-8'}').replaceWith($('#dc-top'));
            {/if}
                $('#dc-top').show();
            });
        </script>
    {/if}
{/if}