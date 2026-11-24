<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('clearopt_scan_notice_dismissed');
delete_option('clearopt_cloud_last_scan');