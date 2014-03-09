<?php

/**
 * Plex summary view.
 *
 * @category   apps
 * @package    plex
 * @subpackage views
 * @author     eGloo <developer@egloo.ca>
 * @copyright  2014 eGloo
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

$this->load->helper('number');
$this->lang->load('base');
$this->lang->load('plex');

$serveraddr = getenv("SERVER_NAME");


if ($is_running)
    echo infobox_highlight(
        lang('plex_app_name') . ' - ' . lang('base_version') . ' ' . $version,
        lang('plex_web_help') .
        "<div style='text-align: center; padding-top: 10px;'>" .  
        anchor_custom('http://'.$serveraddr.':32400/web', lang('plex_open_myplex'), 'high', array('target' => '_blank')) . 
        "</div>"
    );
else
    echo infobox_warning(
        lang('plex_app_name'),
        "<div>" . lang('plex_not_available') . "</div>"
    );
