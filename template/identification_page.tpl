    <p>{'Or sign in with'|translate}</p>
    <fieldset style="text-align:center;">
      {strip}
              <a href="{$CASU_LOGIN_URL}" class="btn btn-raised btn-primary">{if isset($CASU.casu_logo_alt)}{$CASU.casu_logo_alt}{else}{'Sign in'|translate}{/if}</a>
      {/strip}
    </fieldset>


