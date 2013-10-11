<?php

/**
 * Plex devices configuration.
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
$this->lang->load('network');

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('plex_device'),
    lang('network_ip'),
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

$items = array();

foreach ($devices as $device) {
    $item['title'] = $device['device'];
    $item['action'] = '';
    $item['anchors'] = button_set(array(
        anchor_delete('/app/plex/devices/delete/' . $device['address'])
    ));
    $item['details'] = array(
        $device['device'],
        "<span id='device_" . $device['device'] . "'>{$device['device']}</span>",
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('plex_device_acl'),
    array(anchor_custom('/app/plex/devices/add', lang('plex_device_add'))),
    $headers,
    $items,
    array('id' => 'plex_device_summary')
);

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
