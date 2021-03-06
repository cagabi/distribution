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

    const SUPERADMINISTRATOR = 'superadministrator'; // has access to all the organizations
    const ADMINISTRATOR = 'org_administrator'; // has only access to it's own organization
    const PREPVOL = 'prepvol'; // has only got access to preparation
    const DAYVOL = 'dayvol'; // has access through day token and has only got access to preparation

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
     * Fetches the role and organizationid of the given user
     * @param type $userid
     * @return false if user not found or an asociative array('userid' => $userid, 'role' => $role', 'organizationid' => $organizationid)
     */
    public function get_user($userid) {
        global $session;
        $userid = (int) $userid;
        if ($session['admin'] == 1)
            return array('userid' => $userid, 'role' => Roles::SUPERADMINISTRATOR, 'organizationid' => '');
        $result = $this->mysqli->query("SELECT role, organizationid FROM distribution_users WHERE id='$userid'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_array();
            return array('userid' => $userid, 'role' => $row['role'], 'organizationid' => $row['organizationid']);
        }
        else {
            return false;
        }
    }

    /**
     * Returns an array with all the organization including the users and distribution points
     * @param int $userid
     * @return array of organizations (name and users)
     */
    public function get_organizations() {
        $orgs = array();
        $result = $this->mysqli->query('SELECT * FROM distribution_organizations');
        while ($row = $result->fetch_array()) {
            $orgs[] = $this->get_organization($row['id']);
        }
        return $orgs;
    }

    /**
     * Returns array of users for a given organizationid 
     * @param type $orgid
     * @return type
     */
    public function get_users($orgid) {
        $orgid = (int) $orgid;
        $users = array();
        $result = $this->mysqli->query("SELECT id, role FROM distribution_users WHERE organizationid = $orgid");
        while ($row = $result->fetch_array()) {
            $name = $this->user->get_username($row['id']);
            $users[] = ['id' => $row['id'], 'name' => $name, 'role' => $row['role']];
        }
        return $users;
    }

    /**
     * Returns array of distribution points for a given organizationid 
     * @param type $orgid
     * @return type
     */
    public function get_distribution_points($orgid) {
        $orgid = (int) $orgid;
        $distribution_points = array();
        $result = $this->mysqli->query("SELECT id, name, deleted FROM distribution_points WHERE organizationid = '$orgid'");
        while ($row = $result->fetch_array()) {
            $distribution_points[] = ['id' => $row['id'], 'name' => $row['name'], 'deleted' => $row['deleted']];
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
     * @param integer $organizationid
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
            $result = $this->mysqli->query("UPDATE users SET startingpage='distribution/preparation' WHERE id='$userid'");
            $result = $this->mysqli->query("INSERT INTO distribution_users (id,role,organizationid) VALUES ('$userid','$role','$organizationid')");
            if ($this->mysqli->error != "" || $result == false) {
                return array('error' => "There was a problem saving the user in the database<br />" . $this->mysqli->error);
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
     * Adds a new distribution point to the database for the given organizationid
     * @param string $name
     * @return integer id of the new distribution point or an associative array in case of error: array('error' => "Error meassage")
     */
    public function create_distribution_point($name, $orgid) {
        $name2 = preg_replace('/[^\w\s_-]/', '', $name);
        if ($name != $name2) {
            return (array('error' => "Name contains invalid characters. <br />You can only use numbers, letters and blank spaces"));
        }

        if ($this->distribution_point_exists($name)) {
            $distr_point = $this->get_distribution_point_by_name($name);
            if ($distr_point['deleted'] == 0) // The item is active
                return (array('error' => "Name already exists"));
            else { // the distro point is deleted so we undelete it
                $distr_point_id = $distr_point['id'];
                $result = $this->mysqli->query("UPDATE distribution_points SET deleted='0' WHERE id='$distr_point_id'");
                if ($this->mysqli->error != "" || $result == false) {
                    return array('error' => "There was a problem saving the item to the database<br />" . $this->mysqli->error);
                }
                else {
                    return $distr_point['id'];
                }
            }
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

    /**
     * Fetches all the items in the database even if they have been deleted. Useful to show past preparations as they may have items that have been deleted
     * 
     * @return array of items: array('id' => $row['id'], 'name' => $row['name'], 'regular' => $row['regular']);
     */
    public function get_items() {
        $result = $this->mysqli->query('SELECT id, name, deleted, regular  FROM distribution_items');
        $items = array();
        while ($row = $result->fetch_array()) {
            $items[] = array('id' => $row['id'], 'name' => $row['name'], 'regular' => $row['regular'], 'deleted' => $row['deleted']);
        }
        return $items;
    }

    public function get_items_not_deleted() {
        $result = $this->mysqli->query('SELECT id, name, regular FROM distribution_items WHERE deleted="0"');
        $items = array();
        while ($row = $result->fetch_array()) {
            $items[] = array('id' => $row['id'], 'name' => $row['name'], 'regular' => $row['regular']);
        }
        return $items;
    }

    public function create_item($name, $regular) {
        $name2 = preg_replace('/[^\w\s_-]/', '', $name);
        if ($regular == true || $regular == 1 || $regular == '1')
            $regular = 1;
        else
            $regular = 0;

        if ($name != $name2) {
            return (array('error' => "Name contains invalid characters. <br />You can only use numbers, letters and blank spaces"));
        }

        if ($this->item_exists($name)) {
            $item = $this->get_item_by_name($name);
            if ($item['deleted'] == 0) // The item is active
                return (array('error' => "Name already exists"));
            else { // the item is deleted so we undelete it
                $itemid = $item['id'];
                $result = $this->mysqli->query("UPDATE distribution_items SET deleted='0', regular='$regular' WHERE id='$itemid'");
                if ($this->mysqli->error != "" || $result == false) {
                    return array('error' => "There was a problem saving the item to the database<br />" . $this->mysqli->error);
                }
                else {
                    return $item['id'];
                }
            }
        }

        $result = $this->mysqli->query("INSERT INTO distribution_items (name, regular) VALUES ('$name','$regular')");
        if ($this->mysqli->error != "" || $result == false) {
            return array('error' => "There was a problem saving the item in the database<br />" . $this->mysqli->error);
        }
        return $this->mysqli->insert_id;
    }

    public function delete_item($itemid) {
        $itemid = (int) $itemid;

        $result = $this->mysqli->query("SELECT id FROM distribution_items WHERE id='$itemid'");
        if ($result->num_rows === 0) {
            return array('error' => "Item id not valid");
        }

        $result = $this->mysqli->query("UPDATE distribution_items SET deleted='1' WHERE id='$itemid'");
        if ($this->mysqli->error != "" || $result == false) {
            return array('error' => "There was a problem deleting the item from the database<br />" . $this->mysqli->error);
        }

        return true;
    }

    public function delete_distribution_point($distro_id) {
        $distro_id = (int) $distro_id;

        $result = $this->mysqli->query("SELECT id FROM distribution_points WHERE id='$distro_id'");
        if ($result->num_rows === 0) {
            return array('error' => "Distribution point id not valid");
        }

        $result = $this->mysqli->query("UPDATE distribution_points SET deleted='1' WHERE id='$distro_id'");
        if ($this->mysqli->error != "" || $result == false) {
            return array('error' => "There was a problem deleting the item from the database<br />" . $this->mysqli->error);
        }

        return true;
    }

    public function item_exists($name) {
        $name = preg_replace('/[^\w\s_-]/', '', $name);
        $result = $this->mysqli->query("SELECT id FROM distribution_items WHERE name='$name'");
        $row = $result->fetch_array();
        if ($row) {
            return true;
        }
        else {
            return false;
        }
    }

    public function get_item_by_name($name) {
        $name = preg_replace('/[^\w\s_-]/', '', $name);
        $result = $this->mysqli->query("SELECT * FROM distribution_items WHERE name='$name'");
        $row = $result->fetch_array();
        if ($row) {
            return array('id' => $row['id'], 'name' => $name, 'regular' => $row['regular'], 'deleted' => $row['deleted']);
        }
        else {
            return false;
        }
    }

    public function get_distribution_point_by_name($name) {
        $name = preg_replace('/[^\w\s_-]/', '', $name);
        $result = $this->mysqli->query("SELECT * FROM distribution_points WHERE name='$name'");
        $row = $result->fetch_array();
        if ($row) {
            return array('id' => $row['id'], 'name' => $name, 'organizationid' => $row['organizationid'], 'deleted' => $row['deleted']);
        }
        else {
            return false;
        }
    }

    public function get_organization($orgid) {
        $org = array();
        $org['id'] = $orgid;
        $org['name'] = $this->get_organization_name($orgid);
        $org['users'] = $this->get_users($org['id']);
        $org['distribution_points'] = $this->get_distribution_points($org['id']);
        return $org;
    }

    public function user_is_in_organization($userid, $orgid) {
        $userid = (int) $userid;
        $orgid = (int) $orgid;
        $result = $this->mysqli->query("SELECT * FROM distribution_users WHERE id = '$userid' AND organizationid='$orgid'");
        if ($result->num_rows > 0)
            return true;
        else
            return false;
    }

    public function get_distribution_organization($distributionid) {
        $distributionid = (int) $distributionid;
        $result = $this->mysqli->query("SELECT organizationid FROM distribution_points WHERE id='$distributionid'");
        if ($row = $result->fetch_array())
            return $row['organizationid'];
        else
            return false;
    }

    public function save_returned_item($value, $itemid, $distributionid) {
        $value = (int) $value;
        $itemid = (int) $itemid;
        $distributionid = (int) $distributionid;

        // Save in database
        $date = date('Y-m-d', time() - 24 * 60 * 60); // we save it in yesterday's record
        $result = $this->mysqli->query("SELECT * FROM distribution_preparation WHERE itemid='$itemid' and distribution_point_id='$distributionid' and date='$date'");
        if ($row_preparation = $result->fetch_array())
            $result = $this->mysqli->query("UPDATE distribution_preparation SET quantity_returned='$value' WHERE itemid='$itemid' and distribution_point_id='$distributionid' and date='$date'");
        else
            $result = $this->mysqli->query("INSERT INTO distribution_preparation (quantity_returned, itemid, distribution_point_id, date) VALUES ('$value', '$itemid', '$distributionid', '$date')");
        if (result === false)
            return false;

        // Save how many have been distributed
        $quantity_given = $row_preparation['quantity_out'] - $value;
        return $this->save_quantity_distributed_yesterday($quantity_given, $itemid, $distributionid);
    }

    public function save_going_out_item($value, $itemid, $distributionid) {
        $value = (int) $value;
        $itemid = (int) $itemid;
        $distributionid = (int) $distributionid;

        // Save in database
        $date = date('Y-m-d', time());
        $result = $this->mysqli->query("SELECT * FROM distribution_preparation WHERE itemid='$itemid' and distribution_point_id='$distributionid' and date='$date'");
        if ($result->num_rows > 0)
            $result = $this->mysqli->query("UPDATE distribution_preparation SET quantity_out='$value' WHERE itemid='$itemid' and distribution_point_id='$distributionid' and date='$date'");
        else
            $result = $this->mysqli->query("INSERT INTO distribution_preparation (quantity_out, itemid, distribution_point_id, date, quantity_returned) VALUES ('$value', '$itemid', '$distributionid', '$date', 0)");
        return $result;
    }

    public function save_distributed_item($quantity, $itemid, $distributionid, $date) {
        $quantity = (int) $quantity;
        $itemid = (int) $itemid;
        $distributionid = (int) $distributionid;
        $time = strtotime($date);
        $date = date('Y-m-d', $time);

        // Save in database
        $result = $this->mysqli->query("SELECT * FROM distribution_distributions WHERE itemid='$itemid' and distribution_point_id='$distributionid' and date='$date'");
        if ($result->num_rows > 0)
            $result = $this->mysqli->query("UPDATE distribution_distributions SET quantity='$quantity' WHERE itemid='$itemid' and distribution_point_id='$distributionid' and date='$date'");
        else
            $result = $this->mysqli->query("INSERT INTO distribution_distributions (quantity, itemid, distribution_point_id, date)VALUES ('$quantity', '$itemid', '$distributionid', '$date')");
        if ($result === false)
            return false;
        else
            return $quantity_given;
    }

    /**
     * Saves the amount of items distributed
     * @param integer $quantity_given
     * @param integer $itemid
     * @param integer $distributionid
     * @return false if data was not saved otherwise the number of items distributed
     */
    public function save_quantity_distributed_yesterday($quantity_given, $itemid, $distributionid) {
        $quantity_given = (int) $quantity_given;
        $itemid = (int) $itemid;
        $distributionid = (int) $distributionid;

        // Save in database
        $date = date('Y-m-d', time() - 24 * 60 * 60);
        return save_distributed_item($quantity_given, $itemid, $distributionid, $date);
    }

    public function get_yesterday_preparation($distributionid) {
        $distributionid = (int) $distributionid;
        $yesterday = date('Y-m-d', time() - 24 * 60 * 60);
        $items = $this->get_preparation($yesterday, $distributionid);
        return $items;
    }

    public function get_today_preparation($distributionid) {
        $distributionid = (int) $distributionid;
        $today = date('Y-m-d', time());
        $items = $this->get_preparation($today, $distributionid);
        return $items;
    }

    public function get_preparation($date, $distributionid) {
        $distributionid = (int) $distributionid;
        $date = preg_replace('/[^\w-]/', '', $date);
        $items = array();
        $result = $this->mysqli->query("SELECT itemid, quantity_out, quantity_returned FROM distribution_preparation WHERE date='$date' and distribution_point_id = '$distributionid'");
        while ($row = $result->fetch_array())
            $items[] = array('itemid' => $row['itemid'], 'quantity_out' => $row['quantity_out'], 'quantity_returned' => $row['quantity_returned']);
        return $items;
    }

    public function get_day_token() {
        global $path, $distribution_token_salt;
        if (!isset($distribution_token_salt))
            return array('error' => '<p style="margin:50px; font-size:25px">Day token cannot be generated, $distribution_token_salt is missing in settings.php.</p><p style="margin:50px; font-size:25px"> Tell your system administrator</p>');
        $date = date('Y-m-d');
        $token = hash('sha256', $date . $distribution_token_salt);
        return substr($token, 0, 6);
    }

    public function get_last_week_preparation($distributionid) {
        $distributionid = (int) $distributionid;
        $start_date = date('Y-m-d', time() - 7 * 24 * 60 * 60);
        $end_date = date('Y-m-d', time());
        return $this->get_preparations($distributionid, $start_date, $end_date);
    }

    public function get_week_preparation($distributionid, $date) {
        $distributionid = (int) $distributionid;
        $time = strtotime($date);
        $start_date = date('Y-m-d', $time - 3 * 24 * 60 * 60);
        $end_date = date('Y-m-d', $time + 3 * 24 * 60 * 60);

        return $this->get_preparations($distributionid, $start_date, $end_date);
    }

    private function get_preparations($distributionid, $start_date, $end_date) {
        $distribution = array();
        $result = $this->mysqli->query("SELECT itemid, quantity, date FROM distribution_distributions WHERE date between '$start_date' and '$end_date' and distribution_point_id = '$distributionid'");
        while ($row = $result->fetch_array()) {
            if (!isset($distribution[$row['date']]))
                $distribution[$row['date']] = array();
            $distribution[$row['date']][] = array('itemid' => $row['itemid'], 'quantity' => $row['quantity']);
        }
        return $distribution;
    }

}
