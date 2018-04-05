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

class Roles {

    const ADMINISTRATOR = 'administrator';
    const PREPVOL = 'prepvol';

    // If adding a new role remember to add it to the validation in create_user
}

class Distribution {

    public $mysqli;
    public $user;
    private $log;

    public function __construct($mysqli, $user) {
        $this->mysqli = $mysqli;
        $this->user = $user;
    }

    public function get_role($userid) {
        $userid = (int) $userid;
        if ($userid == 1)
            return 'administrator';
        else {
            $result = $this->mysqli->query("SELECT role FROM distribution_users WHERE id='$userid'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_array();
                return $row['role'];
            }
            else {
                return false;
            }
        }
    }

    /**
     * Fetches the role and organization of the given user
     * @param type $userid
     * @return false if user not found or an asociative array('userid' => $userid, 'role' => $role', 'organization' => $organization)
     */
    public function get_user($userid) {
        $userid = (int) $userid;
        $result = $this->mysqli->query("SELECT role, organization FROM distribution_users WHERE id='$userid'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_array();
            return array('userid' => $userid, 'role' => $row['role'], 'organization' => $row['organization']);
        }
        else {
            if ($userid == 1)
                return array('userid' => $userid, 'role' => 'administrator', 'organization' => '');
            else
                return false;
        }
    }

    /**
     * Returns an array of organizations including the users and distribution points
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
            $org['distribution_points'] = $this->get_distribution_points($org['id']);
        }
        return $orgs;
    }

    /**
     * Returns array of users for a given organization 
     * @param type $orgid
     * @return type
     */
    public function get_users($orgid) {
        $orgid =(int)$orgid;
        $users = array();
        $result = $this->mysqli->query("SELECT id, role FROM distribution_users WHERE organization = $orgid");
        while ($row = $result->fetch_array()) {
            $name = $this->user->get_username($row['id']);
            $users[] = ['id' => $row['id'], 'name' => $name, 'role' => $row['role']];
        }
        return $users;
    } 
    
    /**
     * Returns array of distribution points for a given organization 
     * @param type $orgid
     * @return type
     */
    public function get_distribution_points($orgid) {
        $orgid =(int)$orgid;
        $distribution_points = array();
        $result = $this->mysqli->query("SELECT id, name FROM distribution_points WHERE organizationid = '$orgid'");
        while ($row = $result->fetch_array()) {
            $distribution_points[] = ['id' => $row['id'], 'name' => $row['name']];
        }
        return $distribution_points;
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
     * Adds a new user to the database
     * @param tring $name
     * @param string $organization
     * @return integer id of the new user or an associative array in case of error: array('error' => "Error meassage")
     */
    public function create_user($name, $organizationid, $email, $password, $role) {
        if ($role != Roles::ADMINISTRATOR && $role != Roles::PREPVOL) {
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

    /**
     * Adds a new distribution point to the database for the given organization
     * @param string $name
     * @return integer id of the new organization or an associative array in case of error: array('error' => "Error meassage")
     */
    public function create_distribution_point($name, $orgid) {
        $name2 = preg_replace('/[^\w\s_-]/', '', $name);
        if ($name != $name2) {
            return (array('error' => "Name contains invalid characters. <br />You can only use numbers, letters and blank spaces"));
        }

        if ($this->distribution_point_exists($name)) {
            return (array('error' => "Name already exists"));
        }

        $result = $this->mysqli->query("INSERT INTO distribution_points (name, organizationid) VALUES ('$name','$orgid')");
        if ($this->mysqli->error != "" || $result == false) {
            return array('error' => "There was a problem saving the distribution point in the database<br />" . $this->mysqli->error);
        }

        return $this->mysqli->insert_id;
    }

    public function distribution_point_exists($name) {
        $name = preg_replace('/[^\w\s_-]/', '', $name);
        $result = $this->mysqli->query("SELECT id FROM distribution_points WHERE name='$name'");
        $row = $result->fetch_array();
        if ($row) {
            return true;
        }
        else {
            return false;
        }
    }

}
