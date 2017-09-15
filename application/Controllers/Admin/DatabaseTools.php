<?php 
/**
 * Various tools to manage the Database tables.
 *
 */
class DatabaseTools extends Admin_Controller
{
    /** @var string Path to the backups (relative to APPPATH) */
    private $backup_folder  = 'db/backups/';

    //--------------------------------------------------------------------------

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->auth->restrict('Bonfire.Database.Manage');
        $this->lang->load('database');

        $this->backup_folder = APPPATH . $this->backup_folder;

        Assets::add_module_css('database', 'database');

        Template::set_block('sub_nav', 'developer/_sub_nav');
    }

    /**
     * Display a list of tables in the database.
     *
     * @return void
     */
    public function index()
    {
        // Set the default toolbar_title for this page. The actions may change it.
        Template::set('toolbar_title', lang('database_title_maintenance'));

        // Performing an action (backup/repair/optimize/drop)? If the action sets
        // a different view and/or sets the $tables variable itself, it should return
        // true to indicate that this method should not prep the form.
        $hideForm = false;
        if (isset($_POST['action'])) {
            $checked = $this->input->post('checked');

            switch ($this->input->post('action')) {
                case 'backup':
                    $hideForm = $this->backup($checked);
                    break;
                case 'repair':
                    $this->repair($checked);
                    break;
                case 'optimize':
                    $this->optimize();
                    break;
                case 'drop':
                    $hideForm = $this->drop($checked);
                    break;
                default:
                    Template::set_message(lang('database_action_unknown'), 'warning');
            }
        }

        if (! $hideForm) {
            // Load number helper for byte_format() function.
            $this->load->helper('number');

            Template::set('tables', $this->showTableStatus());
        }

        Template::render();
    }

    /**
     * Browse the DB tables.
     *
     * Displays the table data when the user selects one of the linked table names
     * from the index.
     *
     * @param string $table Name of the table to browse
     *
     * @return void
     */
    public function browse($table = '')
    {
        if (empty($table)) {
            Template::set_message(lang('database_no_table_name'), 'error');
            redirect(SITE_AREA . '/developer/database');
        }

        $query = $this->db->get($table);
        if ($query->num_rows()) {
            Template::set('rows', $query->result());
        }

        Template::set('num_rows', $query->num_rows());
        Template::set('query', $this->db->last_query());
        Template::set('toolbar_title', sprintf(lang('database_browse'), $table));

        Template::render();
    }

    /**
     * List the existing backups.
     *
     * @return void
     */
    public function backups()
    {
        if (isset($_POST['delete'])) {
            // Make sure something is selected to delete.
            $checked = $this->input->post('checked');
            if (empty($checked) || ! is_array($checked)) {
                // No files selected.
                Template::set_message(lang('database_backup_delete_none'), 'error');
            } else {
                // Delete the files.
                $failed = 0;
                foreach ($checked as $file) {
                    $deleted = unlink($this->backup_folder . $file);
                    if (! $deleted) {
                        ++$failed;
                    }
                }

                $deletedCount = sprintf(lang('database_backup_deleted_count'), count($checked) - $failed);
                if ($failed == 0) {
                    Template::set_message($deletedCount, 'success');
                } else {
                    Template::set_message(
                        "{$deletedCount}<br />" . lang('database_backup_deleted_error'),
                        'error'
                    );
                }
            }
        }

        // Load the number helper for the byte_format() function used in the view.
        $this->load->helper('number');

        // Get a list of existing backup files
        $this->load->helper('file');

        Template::set('backups', get_dir_file_info($this->backup_folder));
        Template::set('toolbar_title', lang('database_title_backups'));
        Template::render();
    }

    /**
     * Performs the actual backup.
     *
     * @param array $tables Array of tables
     *
     * @return bool
     */
    public function backup($tables = null)
    {
        if (! empty($tables) && is_array($tables)) {
            // set_view() is used because execution most likely came here from index()
            // rather than via redirect or post.
            Template::set_view('developer/backup');

            // Set the selected tables in the form.
            Template::set('tables', $tables);
            Template::set('file', ENVIRONMENT . '_backup_' . date('Y-m-j_His'));
            Template::set('toolbar_title', lang('database_title_backup_create'));

            // Return to index() and display the backup form.
            return true;
        }

        if (isset($_POST['backup'])) {
            // The backup form has been posted, perform validation.
            $this->load->library('form_validation');

            $this->form_validation->set_rules('file_name', 'lang:database_filename', 'required|trim|max_length[220]');
            $this->form_validation->set_rules('drop_tables', 'lang:database_drop_tables', 'required|trim|one_of[0,1]');
            $this->form_validation->set_rules('add_inserts', 'lang:database_add_inserts', 'required|trim|one_of[0,1]');
            $this->form_validation->set_rules('file_type', 'lang:database_compress_type', 'required|trim|one_of[txt,gzip,zip]');
            $this->form_validation->set_rules('tables[]', 'lang:database_tables', 'required');

            if ($this->form_validation->run() !== false) {
                // Perform the backup.
                $this->load->dbutil();

                $format   = $_POST['file_type'];
                $basename = $_POST['file_name'] . '.' . ($format == 'gzip' ? 'gz' : $format);
                $filename = "{$this->backup_folder}{$basename}";

                $prefs = array(
                    'add_drop'   => ($_POST['drop_tables'] == '1'),
                    'add_insert' => ($_POST['add_inserts'] == '1'),
                    'format'     => $format,
                    'filename'   => $filename,
                    'tables'     => $_POST['tables'],
                );
                $backup = $this->dbutil->backup($prefs);

                $this->load->helper('file');
                write_file($filename, $backup);

                if (file_exists($filename)) {
                    Template::set_message(
                        sprintf(
                            lang('database_backup_success'),
                            html_escape(site_url(SITE_AREA . "/developer/database/get_backup/{$basename}")),
                            html_escape($filename)
                        ),
                        'success'
                    );
                } else {
                    Template::set_message(lang('database_backup_failure'), 'error');
                }

                redirect(SITE_AREA . '/developer/database');
            }

            // Validation failed.
            Template::set('tables', $this->input->post('tables'));
            Template::set_message(lang('database_backup_failure_validation'), 'error');

        }

        Template::set('toolbar_title', lang('database_title_backup_create'));
        Template::render();
    }

    /**
     * Do a force download on a backup file.
     *
     * @param string $filename Name of the file to download.
     *
     * @return void
     */
    public function get_backup($filename = null)
    {
        // CSRF could try `../../dev/temperamental-special-file` or `COM1` and the
        // possibility of that happening should really be prevented.
        if (preg_match('{[\\/]}', $filename) || ! fnmatch('*.*', $filename)) {
            $this->security->csrf_show_error();
        }

        $backupFile = "{$this->backup_folder}{$filename}";
        if (! file_exists($backupFile)) {
            Template::set_message(sprintf(lang('database_get_backup_error'), $filename), 'error');
            redirect(SITE_AREA . '/developer/database/backups');
        }

        $data = file_get_contents($backupFile);

        $this->load->helper('download');
        force_download($filename, $data);

        redirect(SITE_AREA . '/developer/database/backups');
    }

    /**
     * Perform a restore from a database backup.
     *
     * @param string $filename Name of the file to restore.
     *
     * @return void
     */
    public function restore($filename = null)
    {
        Template::set('filename', $filename);

        if (isset($_POST['restore']) && ! empty($filename)) {
            $backupFile = "{$this->backup_folder}{$filename}";

            // Load the file from disk.
            $file = file($backupFile);
            if (empty($file)) {
                // Couldn't read from file.
                Template::set_message(sprintf(lang('database_restore_read_error'), $backupFile), 'error');
                redirect(SITE_AREA . '/developer/database/backups');
            }

            // Loop through each line, building the query until it is complete,
            // then executing the query and recording the results.
            $queryResults = array();
            $currentQuery = '';
            foreach ($file as $line) {
                // Skip it if it's a comment.
                if (substr(trim($line), 0, 1) == '#') {
                    continue;
                }

                // Add this line to the current query.
                $currentQuery .= $line;

                // If there is no semicolon at the end, move on to the next line.
                if (substr(trim($line), -1, 1) != ';') {
                    continue;
                }

                // Found a semicolon, perform the query and store the results.
                if ($this->db->query($currentQuery)) {
                    $queryResults[] = sprintf(lang('database_restore_out_successful'), $currentQuery);
                } else {
                    $queryResults[] = sprintf(lang('database_restore_out_unsuccessful'), $currentQuery);
                }

                // Reset $currentQuery and move on to the next line.
                $currentQuery = '';
            }

            // Output the results.
            Template::set('results', implode('<br />', $queryResults));
        }

        // Show verification screen.
        Template::set_view('developer/restore');
        Template::set('toolbar_title', lang('database_title_restore'));
        Template::render();
    }

    /**
     * Drop database tables.
     *
     * @param array $tables Array of table to drop
     *
     * @return bool
     */
    public function drop($tables = null)
    {
        if (! empty($tables)) {
            // set_view() is used because execution most likely came here from index()
            // rather than via redirect or post.
            Template::set_view('developer/drop');
            Template::set('tables', $tables);

            // Return to index() and display the verification screen.
            return true;
        }

        if (empty($_POST['tables']) || ! is_array($_POST['tables'])) {
            // No tables were selected.
            Template::set_message(lang('database_drop_none'), 'error');
            redirect(SITE_AREA . '/developer/database');
        }

        // Delete the tables....
        $this->load->dbforge();

        $notDropped = 0;
        foreach ($_POST['tables'] as $table) {
            // dbforge automatically adds the prefix, so remove it, if present.
            // This may cause problems if there is a table with the prefix duplicated,
            // e.g. bf_bf_table.
            $prefix = $this->db->dbprefix;
            if (strncmp($table, $prefix, strlen($prefix)) === 0) {
                $table = substr($table, strlen($prefix));
            }

            if (@$this->dbforge->drop_table($table) === false) {
                ++$notDropped;
            }
        }

        $tableCount = count($_POST['tables']) - $notDropped;
        Template::set_message(
            sprintf(
                $tableCount == 1 ? lang('database_drop_success_singular') : lang('database_drop_success_plural'),
                $tableCount
            ),
            $notDropped == 0 ? 'success' : 'error'
        );

        redirect(SITE_AREA . '/developer/database');
    }

    //--------------------------------------------------------------------------
    // Private methods.
    //--------------------------------------------------------------------------

    /**
     * Repair database tables.
     *
     * @param array $tables The names of tables to repair.
     *
     * @return void
     */
    private function repair($tables = null)
    {
        if (empty($tables) || ! is_array($tables)) {
            // No tables selected
            Template::set_message(lang('database_repair_none'), 'error');
            redirect(SITE_AREA . '/developer/database');
        }

        $this->load->dbutil();

        // Repair the tables, tracking the number of failures.
        $failed = 0;
        foreach ($tables as $table) {
            if (! $this->dbutil->repair_table($table)) {
                ++$failed;
            }
        }

        Template::set_message(
            sprintf(lang('database_repair_success'), count($tables) - $failed, count($tables)),
            $failed == 0 ? 'success' : 'info'
        );

        redirect(SITE_AREA . '/developer/database');
    }

    /**
     * Optimize the entire database.
     *
     * @return void
     */
    private function optimize()
    {
        $this->load->dbutil();

        if ($result = $this->dbutil->optimize_database()) {
            Template::set_message(lang('database_optimize_success'), 'success');
        } else {
            Template::set_message(lang('database_optimize_failure'), 'error');
        }

        redirect(SITE_AREA . '/developer/database');
    }

    /**
     * Get the data returned by MySQL's 'SHOW TABLE STATUS'.
     *
     * @todo Implement the this functionality for platforms other than MySQL.
     *
     * @return object[] An array of objects containing information about the tables
     * in the database.
     */
    private function showTableStatus()
    {
        // Since the table is built from a database-specific query, check the platform
        // to allow for other methods of generating the table.
        $platform = strtolower($this->db->platform());

        // ---------------------------------------------------------------------
        // MySQL.
        // ---------------------------------------------------------------------

        if (in_array($platform, array('mysql', 'mysqli', 'bfmysqli'))) {
            return $this->db->query('SHOW TABLE STATUS')->result();
        }

        // ---------------------------------------------------------------------
        // All other databases.
        // ---------------------------------------------------------------------

        $tables = array();
        $table  = new stdClass();

        // In the absence of information from the database, display the platform.
        $table->Engine = $platform;

        // These fields are currently unsupported.
        $table->Data_length  = lang('database_data_size_unsupported');   // The length of the data file.
        $table->Index_length = lang('database_index_field_unsupported'); // The length of the index file.
        $table->Data_free    = lang('database_data_free_unsupported');   // The number of allocated but unused bytes.

        // Set the metadata for each table.
        foreach ($this->db->list_tables() as $tableName) {
            $table->Name = $tableName;

            // Note that MySQL's 'SHOW TABLE STATUS' estimates this value, so, while
            // this is slower, it should be more accurate.
            $table->Rows = $this->db->count_all($tableName);

            // Use clone() to copy the current state of $table into $tables (otherwise,
            // an array of references to the metadata for the last table is created,
            // which isn't very useful).
            $tables[] = clone($table);
        }

        return $tables;
    }
}
