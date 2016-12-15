<?php

/**
 * Plex ACL controller.
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
 * Plex ACL controller.
 *
 * @category   apps
 * @package    plex
 * @subpackage controllers
 * @author     eGloo <developer@egloo.ca>
 * @copyright  2013 eGloo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.egloo.ca/clearos/marketplace/apps/plex
 */

class Acl extends ClearOS_Controller
{
    /**
     * Index.
     */

    function index()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load libraries
        //---------------

        $this->load->library('plex/Plex');
        $this->lang->load('plex');

        // Load view data
        //---------------

        $data = array (
            'rules' => $this->plex->get_acl_rules()
        );

        $this->page->view_form('plex/acl', $data, lang('plex_acl_definition'));
    }

    /**
     * Add acl rule.
     *
     * @return view
     */

    function rule()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load libraries
        //---------------

        $this->load->library('plex/Plex');
        $this->lang->load('base');
        $this->lang->load('plex');
        $this->lang->load('network_map');

        // Set validation rules
        //---------------------
       
        $this->form_validation->set_policy('mac', 'plex/Plex', 'validate_mac', TRUE);
        $this->form_validation->set_policy('nickname', 'plex/Plex', 'validate_nickname', TRUE);
        $form_ok = $this->form_validation->run();

        $data = array();

        // Handle form submit
        //-------------------
        if ($form_ok) {
            try {
                $this->plex->add_acl(
                    $this->input->post('mac'),
                    $this->input->post('nickname')
                );
                redirect('/plex');
                return;
            } catch (Exception $e) {
                $this->page->set_message(clearos_exception_message($e), 'warning');
            }
        }

        // Load view data
        //---------------
        $devices = $this->plex->get_device_list();
        foreach ($devices as $mac => $info) {
            $dev_id = key($info['mapping']) . ' (' . lang('network_map_unmapped') . ')';
            if (isset($info['nickname']))
                $dev_id = $info['nickname']; 
            else if (isset($info['username']))
                $dev_id = $info['username'] . ' - ' . $device['type']; 
            $data['devices'][$mac] = $dev_id;
        }
        $definitions = $this->plex->get_acl_time_definitions(); 
        foreach ($definitions as $nickname => $definition)
            $data['definitions'][$nickname] = $nickname;

        $this->page->view_form('plex/rule', $data, lang('plex_add_acl'));
    }

    /**
     * Add acl time definition.
     *
     * @return view
     */

    function add_time()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_add_edit_time();
    }

    /**
     * Edit acl time definition.
     *
     * @return view
     */

    function edit_time($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_add_edit_time(base64_decode($id));
    }

    /**
     * Add/Edit acl time definition.
     *
     * @return view
     */

    private function _add_edit_time($id = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load libraries
        //---------------

        $this->load->library('plex/Plex');
        $this->lang->load('base');
        $this->lang->load('plex');

        // Set validation rules
        //---------------------
       
        if ($id == NULL)
            $this->form_validation->set_policy('nickname', 'plex/Plex', 'validate_nickname', TRUE);
        $this->form_validation->set_policy('start', 'plex/Plex', 'validate_start', TRUE);
        $this->form_validation->set_policy('stop', 'plex/Plex', 'validate_stop', TRUE);
        $this->form_validation->set_policy('dow[]', 'plex/Plex', 'validate_dow', TRUE);
        $form_ok = $this->form_validation->run();

        $data = array();

        // Handle form submit
        //-------------------
        if ($form_ok) {
            try {
                $this->plex->add_or_update_acl_time_definition(
                    $this->input->post('nickname'),
                    $this->input->post('start'),
                    $this->input->post('stop'),
                    $this->input->post('dow')
                );
            } catch (Exception $e) {
                $this->page->set_message(clearos_exception_message($e), 'warning');
            }
            redirect('/plex');
            return;
        }

        // Load view data
        //---------------
        $data['time_options'] = $this->plex->get_time_options();
        $data['days_of_week'] = $this->plex->get_days_of_week(); 
        $data['definitions'] = $this->plex->get_acl_time_definitions(); 
        $data['edit'] = FALSE;
        if ($id != NULL) {
            $data['nickname'] = $id;
            $data['start'] = $data['definitions'][$id]['start'];
            $data['stop'] = $data['definitions'][$id]['stop'];
            $data['dow'] = $data['definitions'][$id]['dow'];
            $data['edit'] = TRUE;
        }

        $this->page->view_form('plex/acl_add', $data, lang('plex_add_acl'));
    }

    /**
     * Delete ACL rule.
     *
     * @param int $id ID
     *
     * @return view
     */

    function delete_rule($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load libraries
        //---------------

        $this->load->library('plex/Plex');

        try {
            $this->plex->delete_acl_rule($id);
        } catch (Exception $e) {
            $this->page->set_message(clearos_exception_message($e), 'warning');
        }

        redirect('/plex');
    }

    /**
     * Delete ACL time definition.
     *
     * @param string $nickname unique nickname (ID)
     *
     * @return view
     */

    function delete_time($nickname)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load libraries
        //---------------

        $this->load->library('plex/Plex');

        try {
            $this->plex->delete_acl_time_definition(base64_decode($nickname));
        } catch (Exception $e) {
            $this->page->set_message(clearos_exception_message($e), 'warning');
        }

        redirect('plex/acl/add_time');
    }
}

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
