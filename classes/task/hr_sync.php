<?php

namespace tool_hrsync\task;

/**
 * HR sync scheduled task.
 */
class hr_sync extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('hr_sync', 'tool_hrsync');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        if (empty(get_config('tool_hrsync', 'sftp_host'))) {
            return;
        }

        $remote_file =  get_config('tool_hrsync', 'sftp_remote_file');

        $query = file_get_contents(__DIR__ . '/../../query_using_coursecompletions.sql');

        # setup custom timezone if it's set
        $timezone = get_config('tool_hrsync', 'timezone');
        if (!empty($timezone) && $timezone !== 'None') {
            $DB->execute('SET @OLD_TIME_ZONE=@@TIME_ZONE');
            $DB->execute("SET TIME_ZONE='$timezone'");
        }

        $users = $DB->get_recordset_sql($query);

        $connection = ssh2_connect(get_config('tool_hrsync', 'sftp_host'), get_config('tool_hrsync', 'sftp_port'));
        ssh2_auth_password($connection, get_config('tool_hrsync', 'sftp_username'), get_config('tool_hrsync', 'sftp_password'));

        $sftp = ssh2_sftp($connection);

        $local = fopen('/tmp/hrms.csv', 'w');
        $stream = fopen('ssh2.sftp://' . (int)$sftp . $remote_file, 'w');

        if (! $stream) {
            throw new \coding_exception("Could not open file: $remote_file");
        }

        foreach ($users as $user) {
            // fputcsv will include double quotes for enclosures
            // fputcsv($local, get_object_vars($user), '|', chr(127));
            $d = array_map('trim', get_object_vars($user));
            fwrite($local, implode('|', $d) ."\r\n");
            if (fwrite($stream, implode('|', $d) ."\r\n") === false) {
                throw new \coding_exception('Error write to remote location.');
            }
        }

        @fclose($stream);
        $users->close();

        ssh2_sftp_chmod($sftp, $remote_file, 0664);

        # restore timezone setting
        if (!empty($timezone) && $timezone !== 'None') {
            $DB->execute('SET TIME_ZONE=@OLD_TIME_ZONE');
        }
    }
}


