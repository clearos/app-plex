<?php

/**
 * Plex ACL add.
 *
 * @category   apps
 * @package    plex
 * @subpackage views
 * @author     eGloo <developer@egloo.ca>
 * @copyright  2013 eGloo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.egloo.ca/clearos/marketplace/apps/plex
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('plex');

///////////////////////////////////////////////////////////////////////////////
// Form handler
///////////////////////////////////////////////////////////////////////////////

$buttons = array( 
    ($edit ? form_submit_update('update') : form_submit_add('add')),
    anchor_cancel('/app/plex')
);

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////
echo form_open(($edit ? 'plex/acl/edit_time' : 'plex/acl/add_time'));
echo form_header(lang('plex_add_acl'));

echo field_input('nickname', $nickname, lang('plex_nickname'), $edit);
echo field_dropdown('start', $time_options, $start, lang('plex_start'), FALSE);
echo field_dropdown('stop', $time_options, $stop, lang('plex_stop'), FALSE);
echo field_multiselect_dropdown('dow[]', $days_of_week, $dow, lang('plex_dow'));
echo field_button_set($buttons);

echo form_footer();
echo form_close();

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('plex_nickname'),
    lang('plex_time'),
    lang('plex_dow')
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

$items = array();

foreach ($definitions as $nickname => $info) {
    $item['title'] = $nickname;
    $item['action'] = '';
    $item['anchors'] = button_set(
        array(
            anchor_delete('/app/plex/acl/delete_time/' . strtr(base64_encode($nickname), '+/=', '-_.')),
            anchor_edit('/app/plex/acl/edit_time/' . strtr(base64_encode($nickname), '+/=', '-_.'))
        )
    );
    $time_window = $info['start'] . ' - ' . $info['stop'];
    if ($info['start'] == '00:00' && ($info['stop'] == '00:00' || $info['stop'] == '23:45'))
        $time_window = lang('plex_all_day');
    $item['details'] = array(
        $nickname,
        $time_window,
        (is_array($info['dow']) ? implode(', ', $info['dow']) : $info['dow'])
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'plex_acl_summary',
    'empty_table_message' => "<div class='theme-loading-small'>" . lang('software_updates_loading_updates_message') . "</div>"
);
echo summary_table(
    lang('plex_acl_times'),
    NULL,
    $headers,
    $items,
    $options
);

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
