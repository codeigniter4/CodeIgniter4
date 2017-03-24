<?php namespace CodeIgniter\Mail\Handlers;

use CodeIgniter\Mail\BaseHandler;
use CodeIgniter\Mail\MailHandlerInterface;
use CodeIgniter\Mail\MessageInterface;

class LogHandler extends BaseHandler
{
    protected $logPath;

    protected $message;

    //--------------------------------------------------------------------

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->logPath = $this->config['logPath'] ?? WRITEPATH;
    }

    /**
     * Does the actual delivery of a message. In this case, though, we simply
     * write the html and text files out to the log folder/emails.
     *
     * The filename format is: yyyymmddhhiiss_email.{format}
     *
     * @param \CodeIgniter\Mail\MessageInterface $message
     * @param bool                               $clear_after If TRUE, will reset the class after sending.
     *
     * @return mixed
     */
    public function send(MessageInterface $message, bool $clear_after=true)
    {
    	$this->message = $message;

        // If there is more than one email address listed in $to,
        // only use the first one.
        $email = $message->getTo();

        if (is_array($email))
        {
            $email = array_shift($email);
        }

        // Clean up the to address so we can use it as the filename
        $symbols = ['#', '%', '&', '{', '}', '\\', '/', '<', '>', '*', '?', ' ', '$', '!', '\'', '"', ':', '@', '+', '`', '='];
        $email = str_replace($symbols, '.', strtolower($email) );

        $filename = date('YmdHis_'). $email;

        // Ensure the emails folder exists in the log folder.
        $path = $this->logPath;
        $path = rtrim($path, '/ ') .'/email/';

        if (! is_dir($path))
        {
            mkdir($path, 0777, true);
        }

        helper('filesystem');

        $this->writeHTMLFile($message->getHTMLMessage(), $path, $filename);
        $this->writeTextFile($message->getTextMessage(), $path, $filename);

        return $this;
    }

    //--------------------------------------------------------------------

	/**
	 * Generates the file that represents the HTML version of the email
	 * with headers at top.
	 *
	 * @param string $html
	 * @param string $path
	 * @param string $filename
	 */
	protected function writeHTMLFile(string $html, string $path, string $filename)
	{
		$headers = $this->describeHeaders("<br/>", true);

		if (strpos($html, '<body') !== false)
		{
			$pos = '';
		}
		else
		{
			$html = $headers . $html;
		}

		$this->writeFile($html, $path, $filename.'.html');
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the file that represents that Text version of the email
	 * with headers at top.
	 *
	 * @param string $text
	 * @param string $path
	 * @param string $filename
	 */
	protected function writeTextFile(string $text, string $path, string $filename)
	{
		$headers = $this->describeHeaders("\n");

		$this->writeFile($headers . $text, $path, $filename.'.text');
	}

	//--------------------------------------------------------------------

	/**
	 * Describes the basic headers (to, from, cc, bcc, replyTo) of the message.
	 *
	 * @param string $linebreak
	 * @param bool   $escape
	 *
	 * @return string
	 */
	protected function describeHeaders(string $linebreak="\n", bool $escape = false)
	{
		$headers = [];

		$fields = ['From', 'To', 'CC', 'BCC', 'ReplyTo'];

		foreach ($fields as $field)
		{
			$rows = [];

			if (empty($field)) continue;

			$method = "get{$field}";

			$header = $this->message->$method();

			if (! empty($header))
			{
				foreach ($header as $name => $email)
				{
					$rows[] = empty($name)
						? $email
						: $escape === true
							? htmlspecialchars("{$name} <{$email}>")
							: "{$name} <{$email}>";
				}
			}

			$rows = implode(', ', $rows);

			$headers[] = "{$field}: {$rows} {$linebreak}";
		}

		$headers[] = '';
		$headers[] = '--------------------------------------------------------------------'. $linebreak;
		$headers[] = '';

		return implode($linebreak, $headers);
	}

	//--------------------------------------------------------------------

	/**
	 * Handles writing out the file.
	 *
	 * @param string $body
	 * @param string $path
	 * @param string $filename
	 */
	protected function writeFile(string $body, string $path, string $filename)
	{
		if (! empty($body) && ! write_file( $path . $filename, $body ) )
		{
			throw new \RuntimeException( sprintf( lang('mail.errorWritingFile'), $path, $filename) );
		}
	}

	//--------------------------------------------------------------------

}
