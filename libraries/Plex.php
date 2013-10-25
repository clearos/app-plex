<?php

/**
 * Plex class.
 *
 * @category   apps
 * @package    plex
 * @subpackage libraries
 * @author     eLogic <developer@elogic.ca>
 * @copyright  2013 eLogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.elogic.ca/clearos/marketplace/apps/plex
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\plex;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('plex');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\firewall\Firewall as Firewall;
use \clearos\apps\incoming_firewall\Incoming as Incoming;
use \clearos\apps\network\Network_Utils as Network_Utils;
use \clearos\apps\network_map\Network_Map as Network_Map;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('firewall/Firewall');
clearos_load_library('incoming_firewall/Incoming');
clearos_load_library('network/Network_Utils');
clearos_load_library('network_map/Network_Map');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Plex class.
 *
 * @category   apps
 * @package    plex
 * @subpackage libraries
 * @author     eLogic <developer@elogic.ca>
 * @copyright  2013 eLogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.elogic.ca/clearos/marketplace/apps/plex
 */

class Plex extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////
    const FILE_CONFIG = "/etc/clearos/plex.conf";
    const FILE_ACL_DEF = "/var/clearos/plex/acl.conf";
    const FILE_FIREWALL_D = "/etc/clearos/firewall.d/10-plex";
    const FOLDER_PLEX = '/var/clearos/plex';
    const DEFAULT_PORT = 32400;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $config = NULL;
    protected $is_loaded = FALSE;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Plex constructor.
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('plexmediaserver');
    }

    /**
     * Get mode.
     *
     * @return string block mode (allow_all or block_all)
     * @throws Engine_Exception
     */

    function get_mode()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_config();

        if (!isset($this->config["mode"]))
            return 'allow_all';
        return $this->config["mode"];
    }

    /**
     * Get modes.
     *
     * @return array valid modes
     * @throws Engine_Exception
     */

    function get_modes()
    {
        clearos_profile(__METHOD__, __LINE__);

        $modes = array(
            'allow_all' => lang('plex_allow_all'),
            'block_all' => lang('plex_block_all')
        );
        return $modes;
    }

    /**
     * Delete ACL rule.
     *
     * @param int $id line number (ID)
     *
     * @return void
     * @throws Engine_Exception
     */

    function delete_acl_rule($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_FIREWALL_D, TRUE);
            $lines = $file->get_contents_as_array();
            // Remove line to delete
            unset($lines[$id]);
            $temp = new File(self::FILE_FIREWALL_D, TRUE, TRUE);
            foreach ($lines as $line)
                $temp->add_lines($line . "\n");
            $file->replace($temp->get_filename());
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Delete ACL time definition.
     *
     * @param int $id ID
     *
     * @return void
     * @throws Engine_Exception
     */

    function delete_acl_time_definition($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $rules = $this->get_acl_rules();
            foreach ($rules as $mac -> $rule) {
                foreach ($rule['acl'] as $line => $time_nickname) {
                    if ($id == $time_nickname)
                        $this->delete_acl_rule($line);
                }
            } 
            $file = new File(self::FILE_ACL_DEF, TRUE);
            $temp = new File(self::FILE_ACL_DEF, TRUE, TRUE);
            if (!$file->exists())
                return;
            $lines = $file->get_contents_as_array();
            foreach ($lines as $line) {
                $info = json_decode($line); 
                if ($id != $info->nickname)
                    $temp->add_lines($line . "\n");
            }
            $file->replace($temp->get_filename());
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get ACL rules.
     *
     * @return array ACL rules
     * @throws Engine_Exception
     */

    function get_acl_rules()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_FIREWALL_D, TRUE);
        $rules = array();
        $devices = $this->get_device_list();
        if (!$file->exists())
            return $rules;
        $lines = $file->get_contents_as_array();
        $line_no = 0;
        foreach ($lines as $line) {
            if (preg_match("/^.*--mac-source\s+(([a-fA-F0-9]{2}[:|\-]?){6})\s+.*ACCEPT # (.*)$/", $line, $match)) {
                $dev_id = $match[1];
                if (isset($devices[$match[1]]['nickname']))
                    $dev_id = $devices[$match[1]]['nickname']; 
                else if (isset($devices[$match[1]]['username']))
                    $dev_id = $devices[$match[1]]['username'] . ' - ' . $devices[$match[1]]['type']; 
                if (!array_key_exists($match[1], $rules)) {
                    $rules[$match[1]] = array(
                        'device' => $dev_id,
                        'ip' => key($devices[$match[1]]['mapping'])
                    );
                }
                $rules[$match[1]]['acl'][$line_no] = $match[3];
            }
            $line_no++;
        }
        return $rules;
    }


    /**
     * Get ACL definitions.
     *
     * @return array ACL rules
     * @throws Engine_Exception
     */

    function get_acl_time_definitions()
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_ACL_DEF);
        $rules = array();
        if (!$file->exists())
            return $rules;
        $lines = $file->get_contents_as_array();
        foreach ($lines as $line) {
            if (empty($line))
                continue;
            $info = json_decode($line); 
            $rules[$info->nickname] = array(
                'start' => $info->start,
                'stop' => $info->stop,
                'dow' => $info->dow
            );
        }
        return $rules;
    }


    /**
     * Get device list.
     *
     * @return array of devices
     * @throws Engine_Exception
     */

    function get_device_list()
    {
        clearos_profile(__METHOD__, __LINE__);
        $network_map = new Network_Map();
        $devices = array_merge($network_map->get_mapped_list(), $network_map->get_unknown_list()); 
        return $devices;
    }

    /**
     * Get time options for start/stop ACL.
     *
     * @return array of times
     * @throws Engine_Exception
     */

    function get_time_options()
    {
        clearos_profile(__METHOD__, __LINE__);
        $options = array();
        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute <=45; $minute+= 15) {
                $val = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT);
                $options[$val] = $val;
            }
        }
        return $options;
    }

    /**
     * Get days of week options for start/stop ACL.
     *
     * @return array of days
     * @throws Engine_Exception
     */

    function get_days_of_week()
    {
        clearos_profile(__METHOD__, __LINE__);
        $options = array(
            'Mon' => lang('base_monday'),
            'Tue' => lang('base_tuesday'),
            'Wed' => lang('base_wednesday'),
            'Thu' => lang('base_thursday'),
            'Fri' => lang('base_friday'),
            'Sat' => lang('base_saturday'),
            'Sun' => lang('base_sunday')
        );
        return $options;
    }

    /**
     * Set the allow/block mode.
     *
     * @param string $mode mode
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_mode($mode)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_mode($mode));

        $file = new File(self::FILE_FIREWALL_D, TRUE);
        if ($mode == 'allow_all') {
            if ($file->exists())
                $file->move_to(self::FOLDER_PLEX);
        } else {
            if (!$file->exists()) {
                $file = new File(self::FOLDER_PLEX . '/10-plex', TRUE);
                if ($file->exists()) {
                    $file->move_to(self::FILE_FIREWALL_D);
                } else {
                    $file = new File(self::FILE_FIREWALL_D, TRUE);
                    $file->create('root', 'root', '0755');
                }
            }
            // Now that file exists, make sure first line is blanket block
            $file = new File(self::FILE_FIREWALL_D, TRUE);
            $lines = $file->get_contents_as_array();
            
            if (empty($lines) || !preg_match('/.*DROP$/', end($lines)))
                $file->add_lines("iptables -I INPUT -p tcp --dport " . $this->_get_port() ." -j DROP\n");
        }
        $this->_set_parameter('mode', $mode);

        // Restart firewall
        $this->_restart_firewall();
    }

    /**
     * Add or update ACL.
     *
     * @param string $nickname nickname
     * @param string $start    Start time
     * @param string $stop     Stop time
     * @param string $dow      Day of Week
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    public function add_or_update_acl_time_definition($nickname, $start, $stop, $dow)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_nickname($nickname));
        Validation_Exception::is_valid($this->validate_start($start));
        Validation_Exception::is_valid($this->validate_stop($stop));
        Validation_Exception::is_valid($this->validate_dow($dow));

        try {
            $file = new File(self::FILE_ACL_DEF, TRUE);
            $temp = new File(self::FILE_ACL_DEF, TRUE, TRUE);

            if (!$file->exists())
                $file->create('webconfig', 'webconfig', '0644');

            $lines = $file->get_contents_as_array();
            $found = FALSE;
            foreach ($lines as $line) {
                $info = json_decode($line); 
                
                if ($info->nickname == $nickname) {
                    $found = TRUE;
                    continue;
                }
                $temp->add_lines($line . "\n");
            }

            // Add back line
            $temp->add_lines(
                json_encode(
                    array(
                        'nickname' => $nickname,
                        'start' => $start,
                        'stop' => $stop,
                        'dow' => $dow
                    )
                ) .
                "\n"
            );
            $file->replace($temp->get_filename());

            // If we found an entry, we have to update 10-plex in firewall.d
            if ($found)
                $this->_update_acl_rules($nickname, $start, $stop, $dow);
            
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Add an ACL rule.
     *
     * @param string $mac      MAC address
     * @param string $nickname nickname
     *
     * @return  void
     * @throws Engine_Exception
     */

    public function add_acl($mac, $nickname)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_mac($mac));
        Validation_Exception::is_valid($this->validate_nickname($nickname));

        try {
            $definitions = $this->get_acl_time_definitions();
            $start = $definitions[$nickname]['start'];
            $stop = $definitions[$nickname]['stop'];
            $dow = $definitions[$nickname]['dow'];
            $file = new File(self::FILE_FIREWALL_D, TRUE);
            if (is_array($dow))
                $dow = implode(',', $dow);
            if (!$file->exists()) {
                $file->create('root', 'root', '0644');
                $file->add_lines("iptables -I INPUT -p tcp --dport " . $this->_get_port() ." -j DROP\n");
            }
            $time_of_day = "--timestart $start --timestop $stop";
            if ($start == '00:00' && ($stop == '00:00' || '23:45'))
                $time_of_day = '';
            $file->add_lines(
                "iptables -I INPUT -p tcp -m mac --mac-source $mac --dport " . $this->_get_port() ." -m state " .
                "--state NEW,ESTABLISHED -m time $time_of_day --weekdays $dow -j ACCEPT # $nickname\n"
            ); 

            // Restart firewall
            $this->_restart_firewall();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Sanity check mode and firewall settings.
     *
     * @return  void
     * @throws Engine_Exception
     */

    public function sanity_check_fw()
    {
        clearos_profile(__METHOD__, __LINE__);
        $incoming = new Incoming();
        
        // Don't worry if allow all access to Plex
        if ($this->get_mode() == 'allow_all')
            return NULL;

        $incoming_allow = $incoming->get_allow_ports(); 
        foreach ($incoming_allow as $info) {
            if ($info['port'] == $this->_get_port() && $info['enabled'])
                return lang('plex_sanity_incoming');
        }
        return NULL;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for mode.
     *
     * @param int $mode mode
     *
     * @return mixed void if mode is valid, errmsg otherwise
     */

    function validate_mode($mode)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!array_key_exists($mode, $this->get_modes()))
            return lang('plex_mode_invalid');
    }

    /**
     * Validation routine for nickname.
     *
     * @param string $nickname nickname
     *
     * @return mixed void if nickname is valid, errmsg otherwise
     */

    function validate_nickname($nickname)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!isset($nickname) || $nickname == '')
            return lang('plex_nickname_cannot_be_blank');

        if (! preg_match('/^[a-zA-Z0-9_\-\.\/ ]*$/', $nickname))
            return lang('plex_nickname_invalid');
    }

    /**
     * Validation routine for start time.
     *
     * @param string $start start time
     *
     * @return mixed void if start is valid, errmsg otherwise
     */

    function validate_start($start)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!array_key_exists($start, $this->get_time_options()))
            return lang('plex_start_invalid');
    }

    /**
     * Validation routine for stop time.
     *
     * @param string $stop stop time
     *
     * @return mixed void if stop is valid, errmsg otherwise
     */

    function validate_stop($stop)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!array_key_exists($stop, $this->get_time_options()))
            return lang('plex_stop_invalid');
    }

    /**
     * Validation routine for day of week.
     *
     * @param string $dow day of week
     *
     * @return mixed void if day of week is valid, errmsg otherwise
     */

    function validate_dow($dow)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_array($dow))
            $days = preg_split('/\s*,\s*/', $dow);
        foreach ($days as $day) {
            if (!array_key_exists($day, $this->get_days_of_week()))
                return lang('plex_dow_invalid');
        }
    }

    /**
     * Validates MAC address.
     *
     * @param string $mac MAC address
     *
     * @return string error message if MAC address is invalid
     */

    public function validate_mac($mac)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_mac($mac, TRUE))
            return lang('network_mac_address_invalid');

    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////
    /**
    * loads configuration files.
    *
    * @return void
    * @throws Engine_Exception
    */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $configfile = new Configuration_File(self::FILE_CONFIG);

        $this->config = $configfile->load();

        $this->is_loaded = TRUE;
    }

    /**
     * Update ACL rules.
     *
     * @param string $nickname nickname
     * @param string $start    Start time
     * @param string $stop     Stop time
     * @param string $dow      Day of Week
     *
     * @return array ACL rules
     * @throws Engine_Exception
     */

    private function _update_acl_rules($nickname, $start, $stop, $dow)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_FIREWALL_D, TRUE);
            $temp = new File(self::FILE_FIREWALL_D, TRUE, TRUE);
            $rules = array();
            if (!$file->exists())
                return $rules;
            $lines = $file->get_contents_as_array();
            if (is_array($dow))
                $dow = implode(',', $dow);
            foreach ($lines as $line) {
                $time_of_day = "--timestart $start --timestop $stop";
                if ($start == '00:00' && ($stop == '00:00' || '23:45'))
                    $time_of_day = '';
                if (preg_match("/^.*--mac-source\s+(([a-fA-F0-9]{2}[:|\-]?){6})\s+.*ACCEPT # $nickname$/", $line, $match))
                    $temp->add_lines(
                        "iptables -I INPUT -p tcp -m mac --mac-source " . $match[1] . " --dport " . $this->_get_port() ." -m state " .
                        "--state NEW,ESTABLISHED -m time $time_of_day --weekdays $dow -j ACCEPT # $nickname\n"
                    ); 
                else
                    $temp->add_lines($line . "\n");
            }
            $file->replace($temp->get_filename());

            // Restart firewall
            $this->_restart_firewall();

        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Generic set routine.
     *
     * @param string $key   key name
     * @param string $value value for the key
     *
     * @return  void
     * @throws Engine_Exception
     */

    private function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG, TRUE);
            $match = $file->replace_lines("/^$key\s*=\s*/", "$key = $value\n");
            if (!$match)
                $file->add_lines("$key = $value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = FALSE;
    }

    /**
     * Restart firewall after change.
     *
     * @return  void
     * @throws Engine_Exception
     */

    private function _restart_firewall()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $firewall = new Firewall(); 
            $firewall->restart();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get the port Plex runs on.
     *
     * @return int port
     */

    private function _get_port()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            if (! $this->is_loaded)
                $this->_load_config();

            if (!isset($this->config['port']))
                return self::DEFAULT_PORT;

            return $this->config['port'];

        } catch (Exception $e) {
            return self::DEFAULT_PORT;
        }
    }
}
