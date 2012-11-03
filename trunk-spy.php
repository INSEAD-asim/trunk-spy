<?php

define('ROOT_DIR', dirname(__FILE__));
require_once ROOT_DIR . '/Svn/Agent.php';
require_once ROOT_DIR . '/Svn/Revision.php';

function get_config()
{
    return include ROOT_DIR . '/config.php';
}

$config = get_config();
foreach ($config['entries'] as $name => $entry) {
    $agent = new Svn_Agent($entry);
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
