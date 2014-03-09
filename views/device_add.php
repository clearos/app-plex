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
    lang('network_hostname'),
    lang('network_ip'),
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

$items = array();

foreach ($devices as $device) {
    $item['title'] = $device['vendor'];
    $item['action'] = '';
    $item['anchors'] = button_set(array(
        anchor_add('/app/plex/devices/add/' . $device['ip'])
    ));
    $item['details'] = array(
        $device['vendor'],
        "<span id='hostname_" . $device['ip'] . "'>{$device['hostname']}</span>",
        "<span id='ip_" . $device['ip'] . "'>{$device['ip']}</span>",
    );

    $items[] = $item;
}

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
echo form_open('plex/devices/add', array('id' => 'device_form'));
echo form_header(lang('plex_device_add_by_address'), array('id' => 'device'));
echo form_banner(lang('plex_devices_desc'));

echo field_input('hostname', '', lang('network_hostname') . ' / ' . lang('network_ip'), FALSE);
echo field_button_set($buttons);

echo form_footer();
echo form_close();

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

if (count($items)) {
    echo summary_table(
        lang('plex_device_add_by_lease'),
        array(),
        $headers,
        $items,
        array('id' => 'plex_device_summary')
    );
}

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
