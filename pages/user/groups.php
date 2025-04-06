<?php
/**
 * Profielsubpagina: Groepen van gebruiker
 * Toont alle groepen die een gebruiker beheert, met coverfoto's, sortering en aantal leden.
 */

// Haal de gebruiker op van wie het profiel wordt bekeken
$user = ossn_user_by_guid(ossn_get_page_owner_guid());
if (!$user) {
    ossn_error_page();
}

// Haal de sorteeroptie uit de URL of gebruik standaard 'newest'
$sort = input('sort') ?: 'newest';

// Titel op basis van taalbestand, bijvoorbeeld: "Groepen van Eric"
$title = ossn_print('usergroups:title', array($user->username));

// Haal alle groepen op waarvan deze gebruiker eigenaar is
$group_class = new OssnGroup();
$all_groups = $group_class->getUserGroups($user->guid);

// Bouw sorteerformulier
$sorting_form = '<form method="GET" style="margin-bottom:20px;">
    <label for="sort">' . ossn_print('ossn:user:groups:sort') . '</label>
    <select name="sort" onchange="this.form.submit()">
        <option value="newest" ' . ($sort == 'newest' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:newest') . '</option>
        <option value="oldest" ' . ($sort == 'oldest' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:oldest') . '</option>
        <option value="members" ' . ($sort == 'members' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:members') . '</option>
    </select>
</form>';

// Begin de inhoudsopbouw
$content = $sorting_form;
$content .= '<div class="user-groups-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">';

if (!empty($all_groups)) {
    // Sorteer de groepen
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
            return $b_count - $a_count;
        });
    } else {
        usort($all_groups, function($a, $b) {
            return $b->time_created - $a->time_created;
        });
    }

    // Loop door alle gesorteerde groepen
    foreach ($all_groups as $group) {
        $group_data = ossn_get_group_by_guid($group->guid);
        if (!$group_data) continue;
		
		$cover = $group->coverURL();
        if ($cover) {
                $cover_url = $cover;
        } else {
            $cover_url = ossn_site_url() . 'components/UserGroups/images/banner.png';
           // error_log("❌ GEEN cover gevonden voor groep {$group_data->guid}");
        }

        // Aantal leden ophalen via relaties
        $members = ossn_get_relationships(array(
            'to' => $group_data->guid,
            'type' => 'group:join',
            'count' => true,
        ));

        // Privacylabel ophalen
		if ($group->membership == OSSN_PUBLIC) {
    	    $privacy = ossn_print('public'); 
		} elseif ($group->membership == OSSN_PRIVATE) {
			$privacy = ossn_print('private');
		}
	        // Veiligheid en links
        $group_url = ossn_site_url("group/{$group_data->guid}");
        $group_title = $group_data->title;

        // HTML-kaart per groep
        $content .= "<div class='group-card' style='border:1px solid #ddd; border-radius:10px; overflow:hidden; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.05);'>
            <a href='{$group_url}'><img src='{$cover_url}' alt='cover' style='width:100%; height:150px; object-fit:cover; border-bottom:1px solid #eee;'></a>
            <div style='padding:12px;'>
                <a href='{$group_url}'><strong>{$group_title}</strong></a><br>
                <small>{$privacy}</small><br>
                <small>" . ossn_print('usergroups:members', array($members)) . "</small>
            </div>
        </div>";
    }
} else {
    $content .= '<p>Deze gebruiker beheert nog geen groepen.</p>';
}

$content .= '</div>';

// Toon de uiteindelijke pagina op het profiel
$mod = array(
    'title'   => ossn_print('groups'),
    'content' => $content,
);

echo ossn_set_page_layout('module', $mod);
