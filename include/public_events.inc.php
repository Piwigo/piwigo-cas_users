<?php
defined('CASU_PATH') or die('Hacking attempt!');

/*
 * prepare things to the connexion menu :
 */
function casu_blockmanager($menu_ref_arr)
{
    global $template, $conf;
    $menu = &$menu_ref_arr[0];

    if ($menu->get_block('mbIdentification') == null) 
    {
        return;
    }

    $casu = safe_unserialize($conf['casu']);
    
    $template->assign(
      array(
        'CASU' => $casu,
        'CASU_LOGIN_URL' => get_root_url().'identification.php?cas_sso=tryLoginCAS',
        'PIWIGO_LOGIN_URL' => get_root_url().'identification.php',
      )
    );

    $template->set_prefilter('menubar', 'casu_add_menubar_buttons_prefilter');
}

/*
 * we want to replace completely the form part in identification_menubar.tpl as CAS becomme the only authentification available
 */
function casu_add_menubar_buttons_prefilter($content, $smarty)
{
    $search = '#(<form[^>]*action="{\$U_LOGIN}".*/form>)#is';
    $replace = file_get_contents(CASU_PATH . 'template/identification_menubar.tpl');
    return preg_replace($search, $replace, $content);
}


/**
 * identification page
 */
function casu_begin_identification()
{
  global $template;
    $template->assign(
      array(
        'CASU_LOGIN_URL' => get_root_url().'identification.php?cas_sso=tryLoginCAS',
      )
    );

  $template->set_prefilter('identification', 'casu_add_buttons_prefilter');
}

function casu_add_buttons_prefilter($content)
{
  $search = '</form>';
  $add = file_get_contents(CASU_PATH . 'template/identification_page.tpl');
  // $add = '<div>Hannah</div>';

  return str_replace($search, $search.$add, $content);
}