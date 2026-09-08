<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'availability_safetygating';
$plugin->version   = 2026090801;
$plugin->requires  = 2024100700; // Moodle 4.5+ (compatible with the 5.x line used in docker-compose.yml).
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.1.1';
