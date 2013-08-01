<?php

define('ROOT_DIR', dirname(__FILE__));
define('AGENT_ACTIVE_START_HOUR', 7);
define('AGENT_ACTIVE_END_HOUR', 20);
define('AGENT_ACTIVE_TIMEZONE', 'Asia/Manila');
define('AGENT_ACTIVE_DAYS', 'Tue,Wed,Thu,Fri,Sat');

require_once ROOT_DIR . '/Svn/Agent.php';
require_once ROOT_DIR . '/Svn/Revision.php';

function get_config()
{
    return include ROOT_DIR . '/config.php';
}

function is_agent_active()
{
    $d = new DateTime('now', new DateTimeZone(AGENT_ACTIVE_TIMEZONE));
    $h = $d->format('H');
    $day = $d->format('D');
    $days = explode(',', AGENT_ACTIVE_DAYS);
    if ($h >= AGENT_ACTIVE_START_HOUR && $h <= AGENT_ACTIVE_END_HOUR) {
        if (in_array($day, $days)) {
            return true;
        }
    }
    return false;
}

if (!is_agent_active()) {
    exit;
}
$config = get_config();
foreach ($config['entries'] as $name => $entry) {
    $agent = new Svn_Agent($entry, $config['global']);
    $previous = $agent->getPrevious();
    $latest = $agent->getLatest();
    $updated = false;

    if ($latest instanceof Svn_Revision && $latest->revision !== null) {
        // First time run
        if ($previous === null) {
            $updated = true;
        }
        if ($previous instanceof Svn_Revision && $previous->revision === null) {
            $updated = true;
        }
        if ($previous instanceof Svn_Revision && $previous->revision < $latest->revision) {
            $updated = true;
        }

        if ($updated) {
            $agent->writeLatest($latest);
            $agent->notifyUsers($latest);
        }
    }
}
