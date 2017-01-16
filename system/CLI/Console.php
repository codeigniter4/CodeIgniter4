<?php namespace CodeIgniter\CLI;

use CodeIgniter\CodeIgniter;

class Console
{
    /**
     * Main CodeIgniter instance.
     * @var CodeIgniter
     */
    protected $app;

    //--------------------------------------------------------------------

    public function __construct(CodeIgniter $app)
    {
        $this->app = $app;
    }

    //--------------------------------------------------------------------

    /**
     * Runs the current command discovered on the CLI.
     */
    public function run()
    {
        $path = CLI::getURI() ?: 'help';

        // Set the path for the application to route to.
        $this->app->setPath("ci{$path}");

        return $this->app->run();
    }

    //--------------------------------------------------------------------

    /**
     * Displays basic information about the Console.
     */
    public function showHeader()
    {
        CLI::newLine(1);

        CLI::write(CLI::color('CodeIgniter CLI Tool', 'green')
            . ' - Version '. CodeIgniter::CI_VERSION
            . ' - Server-Time: '. date('Y-m-d H:i:sa'));

        CLI::newLine(1);
    }

    //--------------------------------------------------------------------

}
