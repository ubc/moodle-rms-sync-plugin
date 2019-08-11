<?php
defined('MOODLE_INTERNAL') || die;

if ( $hassiteconfig ){

    // Create the new settings page
    // - in a local plugin this is not defined as standard, so normal $settings->methods will throw an error as
    // $settings will be NULL
    $settings = new admin_settingpage( 'tool_hrsync', 'HRMS Sync' );

    // Add a setting field to the settings for this page
    $settings->add( new admin_setting_configtext(

    // This is the reference you will use to your configuration
        'tool_hrsync/sftp_host',

        // This is the friendly title for the config, which will be displayed
        'SFTP: Host',

        // This is helper text for this config field
        'This is the hostname used to access SFTP',

        // This is the default value
        'None',

        // This is the type of Parameter this config is
        PARAM_TEXT

    ));

    $settings->add(new admin_setting_configtext(
        'tool_hrsync/sftp_port',
        'SFTP: Port',
        'This is the port used to access SFTP',
        22,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'tool_hrsync/sftp_username',
        'SFTP: Username',
        'This is the username used to access SFTP',
        'None',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'tool_hrsync/sftp_password',
        'SFTP: Password',
        'This is the password used to access SFTP',
        'None',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtext(
        'tool_hrsync/sftp_remote_file',
        'SFTP: Remote File',
        'This is the remote file to write to, including path',
        'None',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'tool_hrsync/timezone',
        'Timezone',
        'Allow set a different timezone than script/server default',
        'None',
        PARAM_TEXT
    ));

    $ADMIN->add('tools', $settings);
}
