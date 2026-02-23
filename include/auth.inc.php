<?php

defined('CASU_PATH') or die('Hacking attempt!');

function casu_get_casu_groups($attributes)
{
    $casu_groups = array();
    $confcasu = conf_get_param('casu', array());
    foreach ($confcasu['casu_groups'] as $group_attr => $regexp)
    {
        // Skip if attribute does not exist
        if (!isset($attributes[$group_attr])) {
            continue;
        }

        if (is_array($attributes[$group_attr]))
        {
            foreach ($attributes[$group_attr] as $group)
            {
                if (empty($regexp))
                {
                    $casu_groups[] = $group;
                }
                elseif (preg_match($regexp, $group, $groupm))
                {
                    $casu_groups[] = $groupm[1];
                }
            }
        }
        else
        {
            if (empty($regexp))
            {
                $casu_groups[] = $attributes[$group_attr];
            }
            elseif (preg_match($regexp, $attributes[$group_attr], $groupm))
            {
                $casu_groups[] = $groupm[1];
            }
        }
    }
    return($casu_groups);
}

function casu_register_groups($user_id, $casu_groups)
{
    $update_groups = false ;

    //we want the list of all *casu_* groups the user is member of
    $query = 'SELECT DISTINCT  gr.id '
            . 'FROM ' . USER_GROUP_TABLE . ' AS ug '
            . 'INNER JOIN ' . GROUPS_TABLE . ' AS gr '
            . 'ON ug.group_id = gr.id '
            . 'WHERE gr.name LIKE \'casu_%\' AND ug.user_id=\'' . $user_id . '\';';

    $result = pwg_query($query);

    $groups_old = array();
    if (pwg_db_num_rows($result) > 0)
    {
        while (list($id) = pwg_db_fetch_row($result))
        {
            $groups_old[] = $id;
        }
    }

    //now we begin
    $groups_add = array();
    foreach ($casu_groups as $group)
    {
        // Skip empty group names
        if (empty($group)) {
            continue;
        }

        $query = 'SELECT id,name FROM ' . GROUPS_TABLE
                . ' WHERE name LIKE \'casu_' . pwg_db_real_escape_string($group) . '\';';
        $result = pwg_query($query);
        if (pwg_db_num_rows($result) == 1)
        {
            list($id) = pwg_db_fetch_row($result);
            $groups_add[] = $id;
        }
        elseif (pwg_db_num_rows($result) == 0)
        {
            $query = 'INSERT INTO ' . GROUPS_TABLE .
                    ' (name) VALUES (\'casu_'
                    . pwg_db_real_escape_string($group) . '\');';
            pwg_query($query);

            $query = 'SELECT id FROM ' . GROUPS_TABLE .
                    ' WHERE name LIKE \'casu_' . pwg_db_real_escape_string($group) . '\';';
            $result = pwg_query($query);

            list($id) = pwg_db_fetch_row($result);
            $groups_add[] = $id;
        }
    }
    $groups_remove = array_diff($groups_old, $groups_add);
    $groups_reg = array_diff($groups_add, $groups_old);

    $inserts = array();
    foreach ($groups_reg as $group)
    {
        $inserts[] = array(
            'user_id' => $user_id,
            'group_id' => $group
        );
    }
    if (count($inserts) != 0)
    {
        mass_inserts(USER_GROUP_TABLE, array('user_id', 'group_id'), $inserts);
        $update_groups = true ;
    }
    foreach ($groups_remove as $group)
    {
        $query = 'DELETE FROM ' . USER_GROUP_TABLE .
                ' WHERE user_id = \'' . $user_id . '\'' .
                ' AND group_id = \'' . $group . '\''
        ;
        pwg_query($query);
        $update_groups = true;
    }
    if($update_groups)
    {
        include_once 'admin/include/functions.php';
        invalidate_user_cache();
    }
}