<?php

class Kint_Objects_SplFileInfo extends KintObject
{
	public function parse( & $variable )
	{
		if ( !KINT_PHP53 || !is_object( $variable ) || !$variable instanceof SplFileInfo ) return false;

		$this->name  = 'SplFileInfo';
		$this->value = $variable->getBasename();


		$flags = array();
		$perms = $variable->getPerms();

		if ( ( $perms & 0xC000 ) === 0xC000 ) {
			$type    = 'File socket';
			$flags[] = 's';
		} elseif ( ( $perms & 0xA000 ) === 0xA000 ) {
			$type    = 'File symlink';
			$flags[] = 'l';
		} elseif ( ( $perms & 0x8000 ) === 0x8000 ) {
			$type    = 'File';
			$flags[] = '-';
		} elseif ( ( $perms & 0x6000 ) === 0x6000 ) {
			$type    = 'Block special file';
			$flags[] = 'b';
		} elseif ( ( $perms & 0x4000 ) === 0x4000 ) {
			$type    = 'Directory';
			$flags[] = 'd';
		} elseif ( ( $perms & 0x2000 ) === 0x2000 ) {
			$type    = 'Character special file';
			$flags[] = 'c';
		} elseif ( ( $perms & 0x1000 ) === 0x1000 ) {
			$type    = 'FIFO pipe file';
			$flags[] = 'p';
		} else {
			$type    = 'Unknown file';
			$flags[] = 'u';
		}

		// owner
		$flags[] = ( ( $perms & 0x0100 ) ? 'r' : '-' );
		$flags[] = ( ( $perms & 0x0080 ) ? 'w' : '-' );
		$flags[] = ( ( $perms & 0x0040 ) ? ( ( $perms & 0x0800 ) ? 's' : 'x' ) : ( ( $perms & 0x0800 ) ? 'S' : '-' ) );

		// group
		$flags[] = ( ( $perms & 0x0020 ) ? 'r' : '-' );
		$flags[] = ( ( $perms & 0x0010 ) ? 'w' : '-' );
		$flags[] = ( ( $perms & 0x0008 ) ? ( ( $perms & 0x0400 ) ? 's' : 'x' ) : ( ( $perms & 0x0400 ) ? 'S' : '-' ) );

		// world
		$flags[] = ( ( $perms & 0x0004 ) ? 'r' : '-' );
		$flags[] = ( ( $perms & 0x0002 ) ? 'w' : '-' );
		$flags[] = ( ( $perms & 0x0001 ) ? ( ( $perms & 0x0200 ) ? 't' : 'x' ) : ( ( $perms & 0x0200 ) ? 'T' : '-' ) );

		$size  = sprintf( '%.2fK', $variable->getSize() / 1024 );
		$flags = implode( $flags );
		$path  = $variable->getRealPath();

		return array(
			'File information' => array(
				'Full path' => $path,
				'Type'      => $type,
				'Size'      => $size,
				'Flags'     => $flags
			)
		);
	}
}