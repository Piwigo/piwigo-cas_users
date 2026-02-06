{if $id == "mbIdentification" and isset($U_LOGIN)}
  {html_style}
  dl#mbIdentification dd:first-of-type { padding-bottom:0 !important; }
  #mbIdentification .casu { margin:0 1px; }
  button.casu { center; width: 200px; border: none; margin: 0; padding: 0;}
  img.casu { padding: 0; margin: 0; width: 100%;}
  legend.casu { font-size: 12px; }
  hr.casu { padding: 0.5rem; }
  {/html_style}

  <dd>
    <form id="quickconnect" method="get" action="{$U_LOGIN}">
      <fieldset style="text-align:center;">
          <legend class="casu">{'CAS Simple Auth'|translate}</legend>
        {strip}
              <a href="{$CASU_LOGIN_URL}" class="btn btn-raised btn-primary">{if isset($CASU.casu_logo_alt)}{$CASU.casu_logo_alt}{else}{'Sign in'|translate}{/if}</a>
  {if isset($CASU.casu_altaccess)}
              <hr class="casu"/>
  <a href="{$PIWIGO_LOGIN_URL}" class="btn btn-raised btn-secondary">{if isset($CASU.casu_altaccess_text)}{$CASU.casu_altaccess_text}{else}{'Sign in using Piwigo'|translate}{/if}</a>
  {/if}
        {/strip}
      </fieldset>
    </form>
  </dd>
{/if}