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

function distribution_controller() {
    global $mysqli, $user, $session, $route;

    require_once "Modules/distribution/distribution_model.php";
    $distribution = new Distribution($mysqli, $user);

    // There are no actions in the distribution module that can be performed with less than write privileges
    if (!$session['write'])
        return array('content' => false);

    $result = false;

    if ($session['admin'] == 1)
        $role = Roles::SUPERADMINISTRATOR;
    else {
        $distro_user = $distribution->get_user($session['userid']);
        if (!$distro_user)
            return array('content' => false);
        $role = $distro_user['role'];
        $organizationid = $distro_user['organizationid'];
    }

    if ($route->format == 'html') {
        if ($route->action == 'admin') {
            if ($role == Roles::SUPERADMINISTRATOR || $role == Roles::ADMINISTRATOR) {
                $result = view("Modules/distribution/Views/admin_view.php", array());
            }
        }
        if ($route->action == 'preparation') { // Everybody can get to the preparation page
            //$orgs = $distribution->get_user_organizations($distro_user['userid']);
            //$distro_points = $distribution->get_distribution_points($orgid);
            $result = view("Modules/distribution/Views/preparation_view.php", array(/* 'distribution_point_id' => get('distribution_point_id'),'organization_id'=>$orgid */));
        }
    }
    else if ($route->format == 'json') {
        if ($route->action == "listorganizations") {
            if ($role == Roles::SUPERADMINISTRATOR)
                $result = $distribution->get_organizations();
            if ($role == Roles::ADMINISTRATOR) {
                $org = $distribution->get_organization($organizationid);
                $result = array($org);
            }
        }
        // Carry on here
        if ($route->action == 'createorganization') {
            if ($role == Roles::SUPERADMINISTRATOR || $role == Roles::ADMINISTRATOR)
                $result = $distribution->create_organization(get('name'));
        }
        if ($route->action == 'createuser') {
            if ($role == Roles::SUPERADMINISTRATOR || ($role == Roles::ADMINISTRATOR && $distribution->user_is_in_organization($session['userid'],$organizationid)))
                $result = $distribution->create_user(get('name'), get('organizationid'), get('email'), post('password'), get('role'));
        }
        if ($route->action == 'createdistributionpoint') {
            if ($role == Roles::SUPERADMINISTRATOR || ($role == Roles::ADMINISTRATOR && $distribution->user_is_in_organization($session['userid'],$organizationid)))
                $result = $distribution->create_distribution_point(get('name'), get('organizationid'));
        }
        if ($route->action == 'getitems') {
            $result = $distribution->get_items();
        }
    }

    return array('content' => $result);
}
