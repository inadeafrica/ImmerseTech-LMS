<?php  // Moodle configuration file — Docker Compose local dev
// All values come from environment variables set in docker-compose.yml (sourced from .env).
// No secrets are hardcoded here, so this file is safe to commit.

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = getenv('MOODLE_DB_HOST');
$CFG->dbname    = getenv('MOODLE_DB_NAME');
$CFG->dbuser    = getenv('MOODLE_DB_USER');
$CFG->dbpass    = getenv('MOODLE_DB_PASSWORD');
$CFG->prefix    = 'mdl_';
$CFG->dboptions = [
    'dbpersist' => false,
    'dbport'    => '',
    'dbsocket'  => false,
    'dbcollation' => 'utf8mb4_unicode_ci',
];

$CFG->wwwroot   = getenv('MOODLE_WWWROOT');
$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');
