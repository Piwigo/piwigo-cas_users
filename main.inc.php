<?php

/*
  Plugin Name: casusers
  Version: auto
  Description: Authenticate again a CAS SSO server and fetch some useful attributes.
  Plugin URI: auto
  Author: Pascal
  Author URI: http://www.pantheonsorbonne.fr
  Has Settings: true
 */

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

if (basename(dirname(__FILE__)) != 'casusers') 
{
  add_event_handler('init', 'casusers_error');

  function casusers_error() 
  {
    global $page;
    $page['errors'][] = 'CAS Users folder name is incorrect, uninstall the plugin and rename it to "casusers"';
  }

  return;
}

define('CASU_PATH', PHPWG_PLUGINS_PATH . 'casusers/');

define('CASU_CAS', CASU_PATH . 'include/phpCAS/CAS.php');
define('CASU_ADMIN', get_root_url() . 'admin.php?page=plugin-casusers');

require_once CASU_PATH . 'include/auth.inc.php';
include_once(CASU_PATH . 'include/public_events.inc.php');

add_event_handler('user_init', 'casu_init');

// add_event_handler('get_admin_plugin_menu_links', 'casu_admin_plugin_menu_links', 50, CASU_PATH . 'include/admin_events.inc.php');

//Update menu link
add_event_handler('blockmanager_apply', 'casu_blockmanager');
//Add option to login page
add_event_handler('loc_begin_identification', 'casu_begin_identification');  

function casu_init()
{
  global $conf;

  // Load plugin config
  $conf['casu'] = safe_unserialize($conf['casu']);

// echo('<pre>');print_r($conf['casu']);echo('</pre>');

  // Load plugin language
  load_language('plugin.lang', CASU_PATH);

  require_once CASU_CAS;

  $serviceUrl = get_absolute_root_url(true) . 'identification.php?cas_sso=tryLoginPiwigo';

  // Enable debugging
  phpCAS::setLogger();
  // Enable verbose error messages. Disable in production!
  phpCAS::setVerbose(true); 

  if (!isset($_GET['cas_sso']))
  {
    return;
  }

  phpCAS::client(
      CAS_VERSION_2_0,
      $conf['casu']['casu_host'],
      (int)$conf['casu']['casu_port'],
      $conf['casu']['casu_context'],
      $serviceUrl,
      false
  );
    
  phpCAS::setFixedServiceURL($serviceUrl);

  // This tests if false because there is a checkbox in the plugin settings
  //False means use Cas certificate
  if (!$conf['casu']['casu_ssl'])
  {
    phpCAS::setCasServerCACert($conf['casu']['casu_ca']);
  } 
  else 
  {
    phpCAS::setNoCasServerValidation();
  }

  if ('tryLoginCAS' === $_GET['cas_sso']) 
  {
    phpCAS::forceAuthentication();
    //If we don't close the PhpCAS session it will override the Piwigo session
    //This means the session ID will be too long for the Piwigo databse and an error will output
    session_write_close();
  }

  if ('tryLoginPiwigo' === $_GET['cas_sso']) 
  {
    phpCAS::forceAuthentication();

    //the id = attributs.uid
    $cas_user = [
        'id' => phpCAS::getUser(), //this will match with a piwigo username
        'attributes' => phpCAS::getAttributes()
    ];

    //see if user exists with username if exists then get user id if not create user and get id
    $user_id = get_userid($cas_user['id']);

    if (!$user_id) {
      include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
      $user_id = register_user($cas_user['id'], generate_key(16), '', false);
    }

    //Add trigger to get user info from other plugin
    trigger_notify('cas_users_user_info', $cas_user, $user_id);

    casu_register_groups($user_id, casu_get_casu_groups($cas_user['attributes']));

    //Log user into piwigo
    log_user($user_id, false);
    
    // Redirect to index.php after login
    redirect(make_index_url());
  }
}

?>