<?php

/**
 * Plex status controller
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
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Plex status controller
 *
 * @category   apps
 * @package    plex
 * @subpackage controllers
 * @author     eGloo <developer@egloo.ca>
 * @copyright  2013 eGloo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.egloo.ca/clearos/marketplace/apps/plex
 */

class Summary extends ClearOS_Controller
{
    /**
     * Plex users controller
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('plex');
        $this->load->library('plex/Plex');

        // Load view data
        //---------------
        $data['is_running'] = $this->plex->get_running_state();
        $data['version'] = $this->plex->get_version();

        // Load views
        //-----------

        $this->page->view_form('plex/summary', $data, lang('plex_app_name'));
    }
}
