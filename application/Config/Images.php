<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Images extends BaseConfig
{
	/**
	 * Default handler used if no other handler is specified.
	 *
	 * @var string
	 */
	public $defaultHandler = 'gd';

	/**
	 * The path to the image library.
	 * Required for ImageMagick, GraphicsMagick, or NetPBM.
	 *
	 * @var string
	 */
	public $libraryPath;

	/**
	 * The available handler classes.
	 *
	 * @var array
	 */
	public $handlers = [
		'gd'        => \CodeIgniter\Images\Handlers\GDHandler::class,
		'imagick'   => \CodeIgniter\Images\Handlers\ImageMagickHandler::class,
		'gm'        => \CodeIgniter\Images\Handlers\GraphicsMagickHandler::class,
		'pbm'       => \CodeIgniter\Images\Handlers\NetPBMHandler::class,
	];
}
