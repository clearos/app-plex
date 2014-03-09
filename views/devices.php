<?php

/**
 * Plex devices configuration.
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
$this->lang->load('network');

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('plex_device'),
    lang('network_ip'),
    lang('plex_acl')
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

$items = array();

foreach ($devices as $mac => $device) {
    $item['title'] = $device['device'];
    $item['action'] = '';
    $item['current_state'] = TRUE;
    $item['anchors'] = button_set(array(
        anchor_edit('/app/plex/acl/' . $mac)
    ));
    $device_or_user = $mac; 
    if (isset($device['nickname']))
        $device_or_user = $device['nickname']; 
    else if (isset($device['username']))
        $device_or_user = $device['username'] . ' - ' . $device['type']; 
    $item['details'] = array(
        $device_or_user,
        key($device['mapping']),
        'M-F 20:00'
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'plex_device_summary',
    'row-enable-disable' => TRUE
);
echo summary_table(
    lang('plex_device_acl'),
    array(anchor_custom('/app/plex/acl/add', lang('plex_add_edit_acl'), 'important')),
    $headers,
    $items,
    $options
);

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
