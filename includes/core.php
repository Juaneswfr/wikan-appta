<?php
global $appta_db_version;
$appta_db_version = '1.1.0';

global $tb_appta_member ;
$tb_appta_member = $wpdb->prefix . 'appta_token'; 

function APPTAinstall()
{
    global $wpdb;
    global $appta_db_version;

    $sql_appta_member = "CREATE TABLE " . $tb_appta_member . " (
      token_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      token VARCHAR(255) NULL,
      fecha DATETIME NOT NULL,
      PRIMARY KEY (appta_token_id)
    );";
    dbDelta($sql_appta_member);
    add_option('appta_db_version', $appta_db_version);
}

register_activation_hook(__FILE__, 'APPTAinstall');