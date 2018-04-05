<?php

/*
  All Emoncms code is released under the GNU Affero General Public License.
  See COPYRIGHT.txt and LICENSE.txt.
  ---------------------------------------------------------------------
  Emoncms - open source energy visualisation
  Part of the OpenEnergyMonitor project: http://openenergymonitor.org
 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

class Distribution {

    public $mysqli;
    public $user;
    private $log;

    public function __construct($mysqli, $user) {
        $this->mysqli = $mysqli;
        $this->user = $user;
    }

    public function get_role($userid) {
        if ($userid == 1)
            return 'administrator';
        else {
            $result = $this->mysqli->query("SELECT role FROM distribution_users WHERE id=?");
            if ($result->num_rows > 0) {
                $row = $result->fetch_array();
                return $row['role'];
            }
        }
    }

    /**
     * Returns an array of organizations including the users
     * @param int $userid
     * @return array of organizations (name and users)
     */
    public function get_organizations_list($userid) {
        $orgs = array();
        $result = $this->mysqli->query('SELECT * FROM distribution_organizations');
        while ($row = $result->fetch_array()) {
            $orgs[] = ['id' => $row['id'], 'name' => $row['name']];
        }
        foreach ($orgs as &$org) {
            $org['users'] = $this->get_users($org['id']);
        }
        return $orgs;
    }

    /**
     * Returns array of users for a given organization 
     * @param type $orgid
     * @return type
     */
    public function get_users($orgid) {
        $users = array();
        $result = $this->mysqli->query("SELECT id, role FROM distribution_users WHERE organization = $orgid");
        while ($row = $result->fetch_array()) {
            $name = $this->user->get_username($row['id']);
            $users[] = ['id' => $row['id'], 'name' => $name, 'role' => $row['role']];
        }
        return $users;
    }

    /**
     * Adds a new organization to the database, will return the id of the new organization or in case of error 
     * an associative array('error' => "Error meassage")
     * @param string $name
     * @return integer id of the new organization or an associative array in case of error: array('error' => "Error meassage")
     */
    public function create_organization($name) {
        $name2 = preg_replace('/[^\w\s_-]/', '', $name);
        if ($name != $name2) {
            return (array('error' => "Name contains invalid characters. <br />You can only use numbers, letters and blank spaces"));
        }

        if ($this->organization_exists($name)) {
            return (array('error' => "Name already exists"));
        }

        $result = $this->mysqli->query("INSERT INTO distribution_organizations (name) VALUES ('$name')");
        if ($this->mysqli->error != "" || $result == false) {
            return array('error' => "There was a problem saving the organization in the database<br />" . $this->mysqli->error);
        }

        return $this->mysqli->insert_id;
    }

    /**
     * Adds a new user to the database, will return the id of the new user or in case of error 
     * an associative array('error' => "Error meassage")
     * @param tring $name
     * @param string $organization
     * @return integer id of the new user or an associative array in case of error: array('error' => "Error meassage")
     */
    public function create_user($name, $organizationid, $email, $password, $role) {
        if ($role != 'administrator' && $role != 'prepvol') {
            return array('error' => 'Role not valid');
        }
        if (!$this->organization_exists($this->get_organization_name($organizationid))) {
            return array('error' => 'Organization doesn\'t exists');
        }
        $result = $this->user->register($name, $password, $email);
        if ($result['success'] === false) {
            return array('error' => $result['message']);
        }
        else {
            $userid = $result['userid'];
            $result = $this->mysqli->query("INSERT INTO distribution_users (id,role,organization) VALUES ('$userid','$role','$organizationid')");
            if ($this->mysqli->error != "" || $result == false) {
                return array('error' => "There was a problem saving the organization in the database<br />" . $this->mysqli->error);
            }
            return $userid;
        }
    }

    public function get_organization_name($orgid) {
        $result = $this->mysqli->query("SELECT name FROM distribution_organizations WHERE id='$orgid'");
        $row = $result->fetch_array();
        if ($row) {
            return $row['name'];
        }
        else {
            return false;
        }
    }

    public function organization_exists($name) {
        $name = preg_replace('/[^\w\s_-]/', '', $name);
        $result = $this->mysqli->query("SELECT id FROM distribution_organizations WHERE name='$name'");
        $row = $result->fetch_array();
        if ($row) {
            return true;
        }
        else {
            return false;
        }
    }

    public function user_exists($name) {
        $name = preg_replace('/[^\w\s_-]/', '', $name);
        $result = $this->mysqli->query("SELECT id FROM distribution_users WHERE name='$name'");
        $row = $result->fetch_array();
        if ($row) {
            return true;
        }
        else {
            return false;
        }
    }

}
