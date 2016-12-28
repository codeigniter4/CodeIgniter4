<?php namespace CodeIgniter\CLI;

use CodeIgniter\CodeIgniter;

class Console
{
    /**
     * Path to the CodeIgniter index file.
     * @var string
     */
    protected $indexPath;

    /**
     * Path to the system folder.
     * @var string
     */
    protected $systemPath;

    /**
     * The 'URI' to use to pass onto CodeIgniter
     * @var string
     */
    protected $commandString;

    /**
     * A string representation of all CLI options.
     * @var string
     */
    protected $optionString;

    //--------------------------------------------------------------------

    public function __construct()
    {
        $this->indexPath  = $this->locateIndex();
        $this->systemPath = $this->locateSystem();

        $this->commandString = CLI::getURI();
        $this->optionString  = CLI::getOptionString();
    }

    //--------------------------------------------------------------------

    /**
     * Runs the current command discovered on the CLI.
     */
    public function run()
    {
        return passthru("php {$this->indexPath} ci {$this->commandString} {$this->optionString}");
    }

    //--------------------------------------------------------------------

    /**
     * Displays basic information about the Console.
     */
    public function showHeader()
    {
        CLI::newLine(1);

        CLI::write('CodeIgniter CLI Tool', 'green');
        CLI::write('Version '. $this->getVersion());
        CLI::write('Server-Time: '. date('Y-m-d H:i:sa'));

        CLI::newLine(1);
    }

    //--------------------------------------------------------------------

    /**
     * Returns the current version of CodeIgniter.
     */
    public function getVersion()
    {
        // The CI Version number is stored in the main CodeIgniter class.
        require_once $this->systemPath.'CodeIgniter.php';

        return CodeIgniter::CI_VERSION;
    }

    //--------------------------------------------------------------------

    /**
     * Find the index path, checking the default location,
     * and where it would be if "flattened"
     *
     * @return string
     */
    protected function locateIndex()
    {
        $path = realpath(__DIR__.'/../../public/index.php');

        if (empty($path))
        {
            $path = __DIR__.'/../../index.php';

            if (! is_file($path))
            {
                die('Unable to locate the CodeIgniter index.php file.');
            }
        }

        return $path;
    }

    //--------------------------------------------------------------------

    /**
     * Attempts to locate the main application directory.
     *
     * @return string
     */
    protected function locateSystem()
    {
        $path = realpath(__DIR__.'/../../system');

        if (empty($path) || ! is_dir($path))
        {
            die('Unable to locate the CodeIgniter system directory.');
        }

        return $path.'/';
    }

    //--------------------------------------------------------------------
}
