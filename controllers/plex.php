<?php

/**
 * Plex controller.
 *
 * @category   apps
 * @package    plex
 * @subpackage controllers
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Plex controller.
 *
 * @category   apps
 * @package    plex
 * @subpackage controllers
 * @author     eGloo <developer@egloo.ca>
 * @copyright  2013 eGloo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.egloo.ca/clearos/marketplace/apps/plex
 */

class Plex extends ClearOS_Controller
{

    /**
     * Plex default controller
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('plex/Plex');
        $this->lang->load('plex');

        // Load views
        //-----------

        $views = array('plex/server', 'plex/summary', 'plex/settings');
        if ($this->plex->get_mode() != 'allow_all')
            $views[] = 'plex/acl';

        $this->page->view_forms($views, lang('plex_app_name'));
    }
}
