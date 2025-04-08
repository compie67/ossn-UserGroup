<?php
/**
 * OSSN UserGroups Component
 * Auteur / Author: Eric Redegeld / ChatGPT
 */

// Pad naar deze component (voor interne includes)
// Path to this component (for internal includes)
define('__USERGROUPS__', ossn_route()->com . 'UserGroups/');

/**
 * Wordt uitgevoerd bij het initialiseren van OSSN
 * Called during OSSN initialization
 */
function com_UserGroups_init() {
    // Registreer een custom pagina handler (optioneel, bijv. /usergroups/user/gebruikersnaam)
    // Register a custom page handler (optional, e.g. /usergroups/user/username)
    ossn_register_page('usergroups', 'com_UserGroups_page_handler');

    // Registreer 'groups' als subpagina op gebruikersprofiel
    // Register 'groups' as a subpage on user profiles
    ossn_profile_subpage('groups');

    // Voeg de knop toe aan het profielmenu
    // Add the button to the profile menu
    ossn_register_callback('page', 'load:profile', 'com_UserGroups_profile_link');

    // Zorg dat onze eigen handler de inhoud van de subpagina levert
    // Ensure our custom handler provides the subpage content
    ossn_add_hook('profile', 'subpage', 'com_UserGroups_subpage_handler');

    // Voeg optioneel CSS toe aan <head>
    // Optionally add CSS to the <head> section
    ossn_extend_view('ossn/site/head', 'usergroups/css');

    // Laad taalbestanden automatisch vanuit de /locale/ map
    // Automatically load language files from the /locale/ folder
    foreach (['nl', 'en'] as $lang) {
        $langfile = __USERGROUPS__ . "locale/ossn.{$lang}.php";
        if (is_file($langfile)) {
            include_once($langfile);
        }
    }
}

/**
 * Handler voor eigen pagina's (indien gebruikt)
 * Handler for custom pages (if used)
 */
function com_UserGroups_page_handler($pages) {
    if (!isset($pages[0]) || !isset($pages[1])) {
        ossn_error_page();
        return;
    }

    switch ($pages[0]) {
        case 'user':
            ossn_set_input('username', $pages[1]);
            include __USERGROUPS__ . 'pages/user/groups.php';
            break;

        default:
            ossn_error_page();
    }
}

/**
 * Voeg een knop toe op het profielmenu ("Groepen")
 * Add a "Groups" button to the profile menu
 */
function com_UserGroups_profile_link() {
    $user = ossn_user_by_guid(ossn_get_page_owner_guid());
    if ($user) {
        ossn_register_menu_link(
            'groups',                     // unieke naam / unique name
            ossn_print('groups'),        // vertaalde label / translated label
            ossn_site_url("u/{$user->username}/groups"), // link naar subpagina / link to subpage
            'user_timeline'              // plaatsing op profiel / position in profile
        );
    }
}

/**
 * Verwerkt de subpagina 'groups' op profielniveau
 * Handles the 'groups' subpage in the user profile
 */
function com_UserGroups_subpage_handler($hook, $type, $return, $params) {
    if ($params['subpage'] == 'groups') {
		ossn_set_input('username', $params['user']->username);
        include __USERGROUPS__ . 'pages/user/groups.php';
    }
}

// Activeer de component bij OSSN-init
// Activate the component at OSSN init
ossn_register_callback('ossn', 'init', 'com_UserGroups_init');
