<?php
/**
 * Profielsubpagina: Groepen van gebruiker
 * Toont alle groepen die een gebruiker beheert, met coverfoto's, sortering en aantal leden.
 * Profile subpage: User's groups with sorting, covers, member count.
 */

$user = ossn_user_by_guid(ossn_get_page_owner_guid());
if (!$user) {
    ossn_error_page();
}

$sort = input('sort') ?: 'newest';

$title = ossn_print('usergroups:title', array($user->username));

$group_class = new OssnGroup();
$all_groups = $group_class->getUserGroups($user->guid);

// Sorteerformulier (dropdown)
$sorting_form = '<form method="GET" style="margin-bottom:20px;">
    <label for="sort">Sorteren op:</label>
    <select name="sort" onchange="this.form.submit()">
        <option value="newest" ' . ($sort == 'newest' ? 'selected' : '') . '>Nieuwste eerst</option>
        <option value="oldest" ' . ($sort == 'oldest' ? 'selected' : '') . '>Oudste eerst</option>
        <option value="members" ' . ($sort == 'members' ? 'selected' : '') . '>Meeste leden</option>
        <option value="az" ' . ($sort == 'az' ? 'selected' : '') . '>Groepsnaam A-Z</option>
        <option value="za" ' . ($sort == 'za' ? 'selected' : '') . '>Groepsnaam Z-A</option>
    </select>
</form>';

$content = $sorting_form;
$content .= '<div class="user-groups-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">';

if (!empty($all_groups)) {
    // Sorteren op basis van keuze
    if ($sort == 'oldest') {
        usort($all_groups, fn($a, $b) => $a->time_created - $b->time_created);
    } elseif ($sort == 'members') {
        usort($all_groups, function($a, $b) {
            $a_count = ossn_get_relationships(['to' => $a->guid, 'type' => 'group:join', 'count' => true]);
            $b_count = ossn_get_relationships(['to' => $b->guid, 'type' => 'group:join', 'count' => true]);
            return $b_count - $a_count;
        });
    } elseif ($sort == 'az') {
        usort($all_groups, fn($a, $b) => strcasecmp($a->title, $b->title));
    } elseif ($sort == 'za') {
        usort($all_groups, fn($a, $b) => strcasecmp($b->title, $a->title));
    } else {
        // Default: newest
        usort($all_groups, fn($a, $b) => $b->time_created - $a->time_created);
    }

    foreach ($all_groups as $group) {
        $group_data = ossn_get_group_by_guid($group->guid);
        if (!$group_data) continue;

        // Coverfoto ophalen
        $cover = new OssnFile();
        $cover->owner_guid = $group_data->guid;
        $cover->type = 'object';
        $cover->subtype = 'file:cover';
        $covers = $cover->getFiles();

        if ($covers && is_array($covers)) {
            $cover_file = reset($covers);
            $cover_url = (isset($cover_file->guid) && isset($cover_file->value))
                ? ossn_site_url() . "groups/cover/{$cover_file->guid}/{$cover_file->value}"
                : ossn_site_url() . 'components/OssnGroups/images/group.png';
        } else {
            $cover_url = ossn_site_url() . 'components/OssnGroups/images/group.png';
        }

        $members = ossn_get_relationships(['to' => $group_data->guid, 'type' => 'group:join', 'count' => true]);

        $privacy = isset($group_data->privacy)
            ? ossn_print("privacy:{$group_data->privacy}")
            : ossn_print("usergroups:privacy:unknown");

        $group_url = ossn_site_url("group/{$group_data->guid}");
        $group_title = htmlspecialchars($group_data->title, ENT_QUOTES, 'UTF-8');

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
    $content .= '<p>' . ossn_print('usergroups:no_groups') . '</p>';
}

$content .= '</div>';

$mod = [
    'title'   => ossn_print('groups'),
    'content' => $content,
];

echo ossn_set_page_layout('module', $mod);
