<?php
require( '../Kint.class.php' );

$selectedTheme = isset( $_GET['theme'] ) ? $_GET['theme'] : 'original';
$allowedThemes = array();
$dh            = opendir( '../view/compiled' );
while ( ( $filename = readdir( $dh ) ) !== false ) {
	if ( strpos( $filename, '.css' ) !== false ) {
		$allowedThemes[] = str_replace( '.css', '', $filename );
	}
}

sort( $allowedThemes );

if ( in_array( $selectedTheme, $allowedThemes ) ) {
	Kint::$theme = $selectedTheme;
}

class BaseUser
{
	/**
	 * @return string
	 */
	public function getFullName() { }
}

class User extends BaseUser
{
	CONST DEFAULT_PATH    = 'some/default/path';
	CONST ROLE_DISALLOWED = 1;
	CONST ROLE_ALLOWED    = 2;
	CONST ROLE_FORBIDDEN  = 3;

	public  $additionalData;
	private $username = 'demo_username';
	private $password = 'demo_password';
	private $createdDate;

	public function __construct() { }

	/**
	 * Check is user is equal to another user
	 */
	public function isEqualTo( BaseUser $user ) { }

	/**
	 * Get data from this demo class
	 *
	 * @param string $username
	 *
	 * @return array
	 */
	public function setUsername( $username ) { }

	/**
	 * Set additional data
	 *
	 * @array $data
	 */
	public function setAdditionalData( array $data ) { $this->additionalData = $data; }

	/**
	 * @return \DateTime date object
	 */
	public function getCreatedDate() { }

	/**
	 * @param \DateTime $date
	 */
	public function setCreatedDate( DateTime $date ) { $this->createdDate = $date; }

	/**
	 * Dummy method that triggers trace
	 */
	public function ensure() { Kint::trace(); }
}

class UserManager
{
	private $user;

	/**
	 * Get user from manager
	 */
	public function getUser() { return $this->user; }

	/**
	 * Debug specific user
	 *
	 * @param \User $user
	 */
	public function debugUser( $user )
	{
		$this->user = $user;
		d( $this->getUser() );
	}

	/**
	 * Ensure user (triggers ensure() method on \User object that trace)
	 *
	 * @void
	 */
	public function ensureUser() { $this->user->ensure(); }
}

$user = new User;
$user->setAdditionalData( array(
		'last_login'             => new DateTime(),
		'current_unix_timestamp' => time(),
		'random_rgb_color_code'  => '#FF9900',
		'impressions'            => 60,
		'nickname'               => 'Someuser',
	)
);
$user->setCreatedDate( new DateTime( '2013-10-10' ) );
$userManager = new UserManager();

for ( $i = 1; $i < 6; $i++ ) {
	$tabularData[] = array(
		'date'        => "2013-01-0{$i}",
		'allowed'     => $i % 3 == 0,
		'action'      => "action {$i}",
		'clicks'      => rand( 100, 50000 ),
		'impressions' => rand( 10000, 500000 ),
	);

	if ( $i % 2 == 0 ) {
		unset( $tabularData[ $i - 1 ]['clicks'] );
	}
}

$nestedArray = array();

for ( $i = 1; $i < 6; $i++ ) {
	$nestedArray["user group {$i}"] = array(
		"user {$i}" => array(
			'name'    => "Name {$i}",
			'surname' => "Surname {$i}"
		),

		'data'      => array(
			'conversions' => rand( 100, 5000 ),
			'spent'       => array( 'currency' => 'EUR', 'amount' => rand( 10000, 500000 ) )
		),
	);
}
?>
<html>
<head>
	<title>Kint PHP debugging tool - overview</title>
</head>
<body>
<div>
	<label style="float: right">Switch theme:
		<select onchange="window.location = '?theme=' + this.value">
			<?php $chosen = isset( $_GET['theme'] ) ? $_GET['theme'] : 'original' ?>
			<?php foreach ( $allowedThemes as $theme ) : ?>
				<option value="<?php echo $theme ?>"<?php echo $theme === $chosen ? ' selected' : '' ?>>
					<?php echo ucfirst( str_replace( '-', ' ', $theme ) ) ?>
				</option>
			<?php endforeach ?>
		</select>
	</label>

	<h2>Kint PHP debugging tool - overview</h2>
</div>
<h3>Debug variables</h3>
<?php
$userManager->debugUser( $user );
d( $userManager, $tabularData );
d( $nestedArray );
?>
<h3>Trace</h3>
<?php $userManager->ensureUser(); ?>
</body>
</html>