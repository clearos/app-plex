<?php

/**
 * Plex ACL add.
 *
 * @category   apps
 * @package    plex
 * @subpackage views
 * @author     eLogic <developer@elogic.ca>
 * @copyright  2013 eLogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.elogic.ca/clearos/marketplace/apps/plex
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
    form_submit_add('submit-form'),
    anchor_cancel('/app/plex')
);

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////
echo form_open('plex/acl/add');
echo form_header(lang('plex_add_acl'));

echo field_input('nickname', '', lang('plex_nickname'),FALSE);
echo field_dropdown('start', $time_options, $start, lang('plex_start'), FALSE);
echo field_dropdown('stop', $time_options, $stop, lang('plex_stop'), FALSE);
echo field_multiselect_dropdown('dow', $days_of_week, $dow, lang('plex_dow'));
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
            anchor_delete('/app/plex/acl/delete/' . $nickname),
            anchor_edit('/app/plex/acl/edit/' . $nickname)
        )
    );
    $item['details'] = array(
        $nickname,
        $info['start'] . ' - ' . $info['stop'],
        $info['dow']
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'plex_acl_summary'
);
echo summary_table(
    lang('plex_acl_times'),
    NULL,
    $headers,
    $items,
    $options
);

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
