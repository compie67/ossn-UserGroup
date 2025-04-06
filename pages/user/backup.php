<?php
/**
 * Pagina: Groepen van de gebruiker (profielsubpagina)
 * Dit bestand toont de groepen die een gebruiker beheert met sorteer- en filtermogelijkheden.
 */

// Haal de gebruiker op van wie het profiel wordt bekeken
$user = ossn_user_by_guid(ossn_get_page_owner_guid());
if (!$user) {
    ossn_error_page();
}

// Haal de sorteerparameter op uit de URL (default: 'newest')
$sort = input('sort') ? input('sort') : 'newest';

// Titel van de pagina, via taalbestand (bijv.: "Groepen van admin")
$title = ossn_print('usergroups:title', array($user->username));

// Haal alle groepen op waarvan de gebruiker eigenaar is
$group_class = new OssnGroup();
$all_groups = $group_class->getUserGroups($user->guid);

// Voeg sorteerformulier toe
// Wanneer de gebruiker een sorteeroptie kiest, wordt de pagina opnieuw geladen
$sorting_form = '<form method="GET" style="margin-bottom:20px;">
    <label for="sort">' . ossn_print('Sorteren op:') . '</label>
    <select name="sort" onchange="this.form.submit()">
        <option value="newest" ' . ($sort == 'newest' ? 'selected' : '') . '>' . ossn_print('Nieuwste eerst') . '</option>
        <option value="oldest" ' . ($sort == 'oldest' ? 'selected' : '') . '>' . ossn_print('Oudste eerst') . '</option>
        <option value="members" ' . ($sort == 'members' ? 'selected' : '') . '>' . ossn_print('Meeste leden') . '</option>
    </select>
</form>';

// Begin met bouwen van de HTML-output
$content = $sorting_form;
$content .= '<div class="user-groups-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">';

// Als er groepen gevonden zijn, sorteer de array op basis van de gekozen optie
if (!empty($all_groups)) {
    // Sorteer de groepen op basis van tijd of aantal leden
    if ($sort == 'oldest') {
        usort($all_groups, function($a, $b) {
            return $a->time_created - $b->time_created;
        });
    } elseif ($sort == 'members') {
        usort($all_groups, function($a, $b) {
            $a_count = ossn_get_relationships(array(
                'to' => $a->guid,
                'type' => 'group:join',
                'count' => true,
            ));
            $b_count = ossn_get_relationships(array(
                'to' => $b->guid,
                'type' => 'group:join',
                'count' => true,
            ));
            // Sorteer van meest naar minst
            return $b_count - $a_count;
        });
    } else { // Standaard: nieuwste eerst
        usort($all_groups, function($a, $b) {
            return $b->time_created - $a->time_created;
        });
    }

    // Loop door alle groepen
    foreach ($all_groups as $group) {
        // Haal de volledige groepsgegevens op
        $group_data = ossn_get_group_by_guid($group->guid);
        if (!$group_data) continue;

        // COVERFOTO ophalen (of fallback gebruiken)
        $cover = new OssnFile();
        $cover->owner_guid = $group_data->guid;
        $cover->type = 'object';
        $cover->subtype = 'file:cover';
        $cover = $cover->getFile();

        if ($cover) {
            $cover_url = ossn_site_url() . "group/cover/{$group_data->guid}";
        } else {
            $cover_url = ossn_site_url() . 'components/OssnGroups/images/group.png';
        }

        // Aantal leden ophalen via relaties (gebruik de 'group:join' relatie)
        $members = ossn_get_relationships(array(
            'to' => $group_data->guid,
            'type' => 'group:join',
            'count' => true,
        ));

        // Privacylabel ophalen: gebruik een standaard label als privacy niet beschikbaar is
        $privacy = isset($group_data->privacy)
            ? ossn_print("privacy:{$group_data->privacy}")
            : ossn_print("usergroups:privacy:unknown");

        // Link en titel van de groep, veilig ge-escaped
        $group_url = ossn_site_url("group/{$group_data->guid}");
        $group_title = htmlspecialchars($group_data->title, ENT_QUOTES, 'UTF-8');

        // Bouw het HTML-kaartje voor de groep
        $content .= "<div class='group-card' style='border:1px solid #ddd; border-radius:10px; overflow:hidden; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.05);'>
            <a href='{$group_url}'><img src='{$cover_url}' alt='cover' style='width:100%; height:150px; object-fit:cover;'></a>
            <div style='padding:12px;'>
                <a href='{$group_url}'><strong>{$group_title}</strong></a><br>
                <small>{$privacy}</small><br>
                <small>" . ossn_print('usergroups:members', array($members)) . "</small>
            </div>
        </div>";
    }
} else {
    // Als er geen groepen zijn
    $content .= '<p>Deze gebruiker beheert nog geen groepen.</p>';
}

$content .= '</div>';

// Verwerk de content in het OSSN-layout als een module (voor profielsubpagina's)
$mod = array(
    'title'   => ossn_print('groups'), // Dit toont de titel boven de module
    'content' => $content,
);

// Toon de uiteindelijke pagina
echo ossn_set_page_layout('module', $mod);
?>
