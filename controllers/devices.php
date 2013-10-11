<?php

/**
 * Plex daemon controller.
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
 * Plex daemon controller.
 *
 * @category   apps
 * @package    plex
 * @subpackage controllers
 * @author     eLogic <developer@elogic.ca>
 * @copyright  2013 eLogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.elogic.ca/clearos/marketplace/apps/plex
 */

class Devices extends ClearOS_Controller
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

        $devices = $this->plex->get_device_list();

        foreach ($devices as $device) {
            $entry = array();
            $entry['device'] = gethostbyaddr($device);
            $entry['address'] = $device;
            $data['devices'][] = $entry;
        }

        $this->page->view_form('plex/devices', $data, lang('plex_devices'));
    }

    /**
     * Add device view.
     *
     * @return view
     */

    function add($device)
    {
        // Load libraries
        //---------------

        $this->load->library('plex/Plex');

        // Set validation rules
        //---------------------
       
        if (($this->input->post('hostname'))) {
            $this->form_validation->set_policy('hostname', 'plex/Plex', 'validate_address');
            $form_ok = $this->form_validation->run();
        }
        else if (strlen($device)) {
            $form_ok = TRUE;
        }

        // Handle form submit
        //-------------------
        if ($form_ok) {
            $ip = NULL;
            if (($this->input->post('hostname')))
                $ip = gethostbyname($this->input->post('hostname'));
            else if (strlen($device))
                $ip = $device;

            if (inet_pton($ip) === FALSE) $ip = NULL;

            if ($ip !== NULL) {
                try {
                    $this->plex->add_device($ip);
                    redirect('/plex');
                } catch (Exception $e) {
                    $this->page->view_exception($e);
                    return;
                }
            }
        }

        // Load view data
        //---------------

        $devices = $this->plex->get_device_list();

        foreach ($leases as $lease) {
            if (! strlen($lease['ip']) ||
                array_search($lease['ip'], $devices) !== FALSE)
                continue;

            if (! strlen($lease['vendor']))
                $lease['vendor'] = lang('plex_device_unknown');
            if (! strlen($lease['hostname']))
                $lease['hostname'] = lang('plex_device_unknown');

            $data['devices'][$lease['ip']]['ip'] = $lease['ip'];
            $data['devices'][$lease['ip']]['vendor'] = $lease['vendor'];
            $data['devices'][$lease['ip']]['hostname'] = $lease['hostname'];
        }

        $this->page->view_form('plex/device_add', $data, lang('plex_device_add'));
    }

    /**
     * Delete device.
     *
     * @return view
     */

    function delete($device)
    {
        // Load libraries
        //---------------

        $this->load->library('plex/Plex');

        try {
            $this->plex->delete_device($device);
        } catch (Exception $e) { }

        redirect('/plex');
    }
}

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
