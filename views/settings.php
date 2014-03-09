<?php

/**
 * Plex settings configuration.
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

if ($sanity_check_fw != NULL)
    echo infobox_warning(
        lang('base_warning'),
        "<div>" . $sanity_check_fw . "</div>"
    );
///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('plex/settings/edit');
echo form_header(lang('base_settings'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

if ($edit) {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/plex')
    );
} else {
    $read_only = TRUE;
    $buttons = array(
        anchor_edit('/app/plex/settings/edit'),
        anchor_custom('/app/plex/acl/add_time', lang('plex_add_edit_acl'), 'important')
    );
}

echo field_dropdown('mode', $modes, $mode, lang('plex_mode'), $read_only);
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
