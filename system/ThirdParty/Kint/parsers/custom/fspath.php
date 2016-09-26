<?php

class Kint_Parsers_FsPath extends kintParser
{
	protected function _parse( & $variable )
	{
		if ( !KINT_PHP53
			|| !is_string( $variable )
			|| strlen( $variable ) > 2048
			|| preg_match( '[[:?<>"*|]]', $variable )
			|| !@is_readable( $variable ) # f@#! PHP and its random warnings
		) return false;

		try {
			$fileInfo = new SplFileInfo( $variable );
			$flags    = array();
			$perms    = $fileInfo->getPerms();

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

			$this->type  = $type;
			$this->size  = sprintf( '%.2fK', $fileInfo->getSize() / 1024 );
			$this->value = implode( $flags );

		} catch ( Exception $e ) {
			return false;
		}

	}
}
