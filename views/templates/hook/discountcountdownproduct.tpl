{if isset($dc) && $dc}    
    {if $dc.countdown_format==1}
        {$date_format="<span>%D<em>{l s='days' mod='discountcountdown'}</em></span> <span>%H<em>{l s='hours' mod='discountcountdown'}</em></span> <span>%M<em>{l s='minutes' mod='discountcountdown'}</em></span> <span>%S<em>{l s='seconds' mod='discountcountdown'}</em></span>"}
    {else}
        {$date_format="<span>%H<em>{l s='hours' mod='discountcountdown'}</em></span> <span>%M<em>{l s='minutes' mod='discountcountdown'}</em></span> <span>%S<em>{l s='seconds' mod='discountcountdown'}</em></span>"}
    {/if}
    <div class="dc-top dc-product">
        <strong>{$dc.caption|escape:'htmlall':'UTF-8'}</strong><span data-date-countdown="{$dc_activated|date_format:"Y-m-d H:i:s"}" data-date-format="{$date_format|escape:'UTF-8'}" class="clock"></span>
    </div>
{/if}