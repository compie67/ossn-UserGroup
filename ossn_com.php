<?php
/**
 * OSSN UserGroups Component
 * Auteur: Eric Redegeld / ChatGPT
 */

// Pad naar deze component (voor interne includes)
define('__USERGROUPS__', ossn_route()->com . 'UserGroups/');

/**
 * Wordt uitgevoerd bij het initialiseren van OSSN
 */
function com_UserGroups_init() {
    // Registreer een custom pagina handler (optioneel, bijv. /usergroups/user/gebruikersnaam)
    ossn_register_page('usergroups', 'com_UserGroups_page_handler');

    // Registreer 'groups' als subpagina op gebruikersprofiel
    ossn_profile_subpage('groups');

    // Voeg de knop toe aan het profielmenu
    ossn_register_callback('page', 'load:profile', 'com_UserGroups_profile_link');

    // Zorg dat onze eigen handler de inhoud van de subpagina levert
    ossn_add_hook('profile', 'subpage', 'com_UserGroups_subpage_handler');

    // Voeg optioneel CSS toe aan <head>
    ossn_extend_view('ossn/site/head', 'usergroups/css');

    // (Optioneel) Laad taalbestanden automatisch als ze in /locale/ staan
    include_once(__USERGROUPS__ . 'locale/ossn.nl.php');

}

/**
 * Handler voor eigen pagina's (indien gebruikt)
 */
function com_UserGroups_page_handler($pages) {
    if (!isset($pages[0]) || !isset($pages[1])) {
        ossn_error_page();
        return;
    }

    switch ($pages[0]) {
        case 'user':
            $_GET['username'] = $pages[1];
            include __USERGROUPS__ . 'pages/user/groups.php';
            break;

        default:
            ossn_error_page();
    }
}

/**
 * Voeg een knop toe op het profielmenu ("Groepen")
 */
function com_UserGroups_profile_link() {
    $user = ossn_user_by_guid(ossn_get_page_owner_guid());
    if ($user) {
        ossn_register_menu_link(
            'groups', // unieque naam
            ossn_print('groups'), // vertaald label
            ossn_site_url("u/{$user->username}/groups"), // link naar subpagina
            'user_timeline' // plaatsing op profiel
        );
    }
}

/**
 * Verwerkt de subpagina 'groups' op profielniveau
 */
function com_UserGroups_subpage_handler($hook, $type, $return, $params) {
    if ($params['subpage'] == 'groups') {
        $_GET['username'] = $params['user']->username;
        include __USERGROUPS__ . 'pages/user/groups.php';
    }
}

// Activeer de component bij OSSN-init
ossn_register_callback('ossn', 'init', 'com_UserGroups_init');
