<?php

/**
 * Plex settings controller.
 *
 * @category   apps
 * @package    plex
 * @subpackage controllers
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Plex settings controller.
 *
 * @category   apps
 * @package    plex
 * @subpackage controllers
 * @author     eLogic <developer@elogic.ca>
 * @copyright  2013 eLogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.elogic.ca/clearos/marketplace/apps/plex
 */

class Settings extends ClearOS_Controller
{
    /**
     * Index.
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->load->library('plex/Plex');

        // Load view data
        //---------------

        $data = array(
            'edit' => FALSE,
            'mode' => $this->plex->get_mode(),
            'modes' => $this->plex->get_modes()
        );

        $this->page->view_form('plex/settings', $data, lang('base_settings'));
    }

    /**
     * Edit settings view.
     *
     * @return view
     */

    function edit()
    {
        // Load libraries
        //---------------

        $this->load->library('plex/Plex');

        // Set validation rules
        //---------------------
       
        $this->form_validation->set_policy('mode', 'plex/Plex', 'validate_mode');
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------
        if ($form_ok) {
            try {
                $this->plex->set_mode($this->input->post('mode'));
                redirect('/plex');
                return;
            } catch (Exception $e) {
                $this->page->set_message(clearos_exception_message($e), 'warning');
            }
        }

        $data = array(
            'edit' => TRUE,
            'mode' => $this->plex->get_mode(),
            'modes' => $this->plex->get_modes()
        );

        $this->page->view_form('plex/settings', $data, lang('base_settings'));
    }
}

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
