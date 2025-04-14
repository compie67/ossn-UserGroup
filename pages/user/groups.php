<?php
/**
 * Profielsubpagina: Groepen van gebruiker
 * Toont alle groepen die een gebruiker beheert, met coverfoto's, sortering en aantal leden.
 */

$user = ossn_user_by_guid(ossn_get_page_owner_guid());
if (!$user) {
    ossn_error_page();
}

$sort = input('sort') ?: 'newest';
$title = ossn_print('usergroups:title', array($user->username));

$group_class = new OssnGroup();
$all_groups = $group_class->getUserGroups($user->guid);
$count_groups = $group_class->getUserGroups($user->guid, array(
    'count' => true,
));

$sorting_form = '<form method="GET" style="margin-bottom:20px;">
    <label for="sort">' . ossn_print('ossn:user:groups:sort') . '</label>
    <select name="sort" onchange="this.form.submit()">
        <option value="newest" ' . ($sort == 'newest' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:newest') . '</option>
        <option value="oldest" ' . ($sort == 'oldest' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:oldest') . '</option>
        <option value="members" ' . ($sort == 'members' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:members') . '</option>
        <option value="az" ' . ($sort == 'az' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:az') . '</option>
        <option value="za" ' . ($sort == 'za' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:za') . '</option>
    </select>
</form>';

$content = $sorting_form;
$content .= '<div class="user-groups-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">';

if (!empty($all_groups)) {
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
        usort($all_groups, fn($a, $b) => $b->time_created - $a->time_created);
    }

    foreach ($all_groups as $group) {
        $group_data = ossn_get_group_by_guid($group->guid);
        if (!$group_data) continue;

        $cover = $group->coverURL();
        $cover_url = $cover ?: ossn_site_url() . 'components/UserGroups/images/banner.png';

        $members = ossn_get_relationships([
            'to' => $group_data->guid,
            'type' => 'group:join',
            'count' => true,
        ]);

        if ($group->membership == OSSN_PUBLIC) {
            $privacy = ossn_print('public');
        } elseif ($group->membership == OSSN_PRIVATE) {
            $privacy = ossn_print('private');
        } else {
            $privacy = ossn_print('usergroups:privacy:unknown');
        }

        $group_title = $group_data->title;
        $group_url = ossn_site_url("group/{$group_data->guid}");

        $content .= "<div class='group-card' style='border:1px solid #ddd; border-radius:10px; overflow:hidden; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.05);'>
            <a href='{$group_url}'><img src='{$cover_url}' alt='cover' style='width:100%; height:150px; object-fit:cover; border-bottom:1px solid #eee;'></a>
            <div style='padding:12px;'>
                <a href='{$group_url}'><strong>{$group_title}</strong></a><br>
                <small>{$privacy}</small><br>
                <small>" . ossn_print('usergroups:members', array($members)) . "</small>
            </div>
        </div>";
    }
    echo ossn_view_pagination($count_groups);
} else {
    $content .= '<p>' . ossn_print('usergroups:no_groups') . '</p>';
}

$content .= '</div>';

$mod = [
    'title'   => ossn_print('groups'),
    'content' => $content,
];

echo ossn_set_page_layout('module', $mod);
