<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add Bonfire.Emailer.View permission
 * Add Countries and Activities tables
 * Modify Users table (varchar/larger zipcode, remove zip_extra, add country and reset_by)
 */
class Migration_Version_02_upgrades extends Migration
{
	/****************************************************************
	 * Table Names
	 */
	/**
	 * @var string Name of the Permissions table
	 */
	private $permissions_table = 'permissions';

	/**
	 * @var string Name of the Users table
	 */
	private $users_table = 'users';

	/**
	 * @var string Name of the Countries table
	 */
	private $countries_table = 'countries';

	/**
	 * @var string Name of the Activities table
	 */
	private $activities_table = 'activities';

	/****************************************************************
	 * Field Definitions
	 */
	/**
	 * @var array Fields to add to the Permissions table
	 */
	private $permissions_fields = array(
//		"`Bonfire.Emailer.View` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Emailer_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
	);

	/**
	 * @var array Fields to change in the Users table
	 */
	private $users_fields = array(
		'temp_password_hash' => array(
			'name' => 'reset_hash',
			'type' => 'VARCHAR',
			'constraint' => 40,
			'null' => true,
		),
		'zipcode' => array(
			'name' => 'zipcode',
			'type' => 'VARCHAR',
			'constraint' => 20,
			'null' => true,
		),
	);

	/**
	 * @var array The previous version of the fields being changed in the Users table (to revert $users_fields)
	 */
	private $users_fields_down = array(
		'reset_hash' => array(
			'name' => 'temp_password_hash',
			'type' => 'VARCHAR',
			'constraint' => 40,
            'null' => false,
		),
		'zipcode' => array(
			'name' => 'zipcode',
			'type' => 'INT',
			'constraint' => 7,
			'null' => true,
		),
	);

	/**
	 * @var array Fields to be added to the Users table
	 */
	private $users_new_fields = array(
		'reset_by' => array(
			'type' => 'INT',
			'constraint' => 10,
			'null' => true,
		),
		'country_iso' => array(
			'type' => 'CHAR',
			'constraint' => 2,
			'default' => 'US',
            'null' => false,
		),
	);

	/**
	 * @var array Fields to be removed from the Users table
	 */
	private $users_drop_fields = array(
		'zip_extra'	=> array(
			'type' => 'INT',
			'constraint' => 5,
			'null' => true,
		),
	);

	/**
	 * @var array Fields for the Countries table
	 */
	private $countries_fields = array(
		'iso' => array(
			'type' => 'CHAR',
			'constraint' => 2,
			'default' => 'US',
			'null' => false,
		),
		'name' => array(
			'type' => 'VARCHAR',
			'constraint' => 80,
			'null' => false,
		),
		'printable_name' => array(
			'type' => 'VARCHAR',
			'constraint' => 80,
			'null' => false,
		),
		'iso3' => array(
			'type' => 'CHAR',
			'constraint' => 3,
			'null' => true,
		),
		'numcode' => array(
			'type' => 'SMALLINT',
			'null' => true,
		),
	);

	/**
	 * @var array Fields for the Activities table
	 */
	private $activities_fields = array(
		'activity_id' => array(
			'type' => 'BIGINT',
			'constraint' => 20,
			'auto_increment' => true,
            'null' => false,
		),
		'user_id' => array(
			'type' => 'BIGINT',
			'constraint' => 20,
			'default' => 0,
            'null' => false,
		),
		'activity' => array(
			'type' => 'VARCHAR',
			'constraint' => 255,
            'null' => false,
		),
		'module' => array(
			'type' => 'VARCHAR',
			'constraint' => 255,
            'null' => false,
		),
		'created_on' => array(
			'type' => 'DATETIME',
            'null' => false,
		),
	);

	/****************************************************************
	 * Data for Insert/Update
	 */
	/**
	 * @var array Data used to update the Permissions table
	 */
	private $permissions_data = array(
		'Bonfire_Emailer_View' => 1,
	);

	/**
	 * @var array Data to be inserted into the Countries table
	 */
	private $countries_data = array(
		array(
			'iso' => 'AF',
			'name' => 'AFGHANISTAN',
			'printable_name' => 'Afghanistan',
			'iso3' => 'AFG',
			'numcode' => '004',
		),
		array(
			'iso' => 'AL',
			'name' => 'ALBANIA',
			'printable_name' => 'Albania',
			'iso3' => 'ALB',
			'numcode' => '008',
		),
		array(
			'iso' => 'DZ',
			'name' => 'ALGERIA',
			'printable_name' => 'Algeria',
			'iso3' => 'DZA',
			'numcode' => '012',
		),
		array(
			'iso' => 'AS',
			'name' => 'AMERICAN SAMOA',
			'printable_name' => 'American Samoa',
			'iso3' => 'ASM',
			'numcode' => '016',
		),
		array(
			'iso' => 'AD',
			'name' => 'ANDORRA',
			'printable_name' => 'Andorra',
			'iso3' => 'AND',
			'numcode' => '020',
		),
		array(
			'iso' => 'AO',
			'name' => 'ANGOLA',
			'printable_name' => 'Angola',
			'iso3' => 'AGO',
			'numcode' => '024',
		),
		array(
			'iso' => 'AI',
			'name' => 'ANGUILLA',
			'printable_name' => 'Anguilla',
			'iso3' => 'AIA',
			'numcode' => '660',
		),
		array(
			'iso' => 'AQ',
			'name' => 'ANTARCTICA',
			'printable_name' => 'Antarctica',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'AG',
			'name' => 'ANTIGUA AND BARBUDA',
			'printable_name' => 'Antigua and Barbuda',
			'iso3' => 'ATG',
			'numcode' => '028',
		),
		array(
			'iso' => 'AR',
			'name' => 'ARGENTINA',
			'printable_name' => 'Argentina',
			'iso3' => 'ARG',
			'numcode' => '032',
		),
		array(
			'iso' => 'AM',
			'name' => 'ARMENIA',
			'printable_name' => 'Armenia',
			'iso3' => 'ARM',
			'numcode' => '051',
		),
		array(
			'iso' => 'AW',
			'name' => 'ARUBA',
			'printable_name' => 'Aruba',
			'iso3' => 'ABW',
			'numcode' => '533',
		),
		array(
			'iso' => 'AU',
			'name' => 'AUSTRALIA',
			'printable_name' => 'Australia',
			'iso3' => 'AUS',
			'numcode' => '036',
		),
		array(
			'iso' => 'AT',
			'name' => 'AUSTRIA',
			'printable_name' => 'Austria',
			'iso3' => 'AUT',
			'numcode' => '040',
		),
		array(
			'iso' => 'AZ',
			'name' => 'AZERBAIJAN',
			'printable_name' => 'Azerbaijan',
			'iso3' => 'AZE',
			'numcode' => '031',
		),
		array(
			'iso' => 'BS',
			'name' => 'BAHAMAS',
			'printable_name' => 'Bahamas',
			'iso3' => 'BHS',
			'numcode' => '044',
		),
		array(
			'iso' => 'BH',
			'name' => 'BAHRAIN',
			'printable_name' => 'Bahrain',
			'iso3' => 'BHR',
			'numcode' => '048',
		),
		array(
			'iso' => 'BD',
			'name' => 'BANGLADESH',
			'printable_name' => 'Bangladesh',
			'iso3' => 'BGD',
			'numcode' => '050',
		),
		array(
			'iso' => 'BB',
			'name' => 'BARBADOS',
			'printable_name' => 'Barbados',
			'iso3' => 'BRB',
			'numcode' => '052',
		),
		array(
			'iso' => 'BY',
			'name' => 'BELARUS',
			'printable_name' => 'Belarus',
			'iso3' => 'BLR',
			'numcode' => '112',
		),
		array(
			'iso' => 'BE',
			'name' => 'BELGIUM',
			'printable_name' => 'Belgium',
			'iso3' => 'BEL',
			'numcode' => '056',
		),
		array(
			'iso' => 'BZ',
			'name' => 'BELIZE',
			'printable_name' => 'Belize',
			'iso3' => 'BLZ',
			'numcode' => '084',
		),
		array(
			'iso' => 'BJ',
			'name' => 'BENIN',
			'printable_name' => 'Benin',
			'iso3' => 'BEN',
			'numcode' => '204',
		),
		array(
			'iso' => 'BM',
			'name' => 'BERMUDA',
			'printable_name' => 'Bermuda',
			'iso3' => 'BMU',
			'numcode' => '060',
		),
		array(
			'iso' => 'BT',
			'name' => 'BHUTAN',
			'printable_name' => 'Bhutan',
			'iso3' => 'BTN',
			'numcode' => '064',
		),
		array(
			'iso' => 'BO',
			'name' => 'BOLIVIA',
			'printable_name' => 'Bolivia',
			'iso3' => 'BOL',
			'numcode' => '068',
		),
		array(
			'iso' => 'BA',
			'name' => 'BOSNIA AND HERZEGOVINA',
			'printable_name' => 'Bosnia and Herzegovina',
			'iso3' => 'BIH',
			'numcode' => '070',
		),
		array(
			'iso' => 'BW',
			'name' => 'BOTSWANA',
			'printable_name' => 'Botswana',
			'iso3' => 'BWA',
			'numcode' => '072',
		),
		array(
			'iso' => 'BV',
			'name' => 'BOUVET ISLAND',
			'printable_name' => 'Bouvet Island',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'BR',
			'name' => 'BRAZIL',
			'printable_name' => 'Brazil',
			'iso3' => 'BRA',
			'numcode' => '076',
		),
		array(
			'iso' => 'IO',
			'name' => 'BRITISH INDIAN OCEAN TERRITORY',
			'printable_name' => 'British Indian Ocean Territory',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'BN',
			'name' => 'BRUNEI DARUSSALAM',
			'printable_name' => 'Brunei Darussalam',
			'iso3' => 'BRN',
			'numcode' => '096',
		),
		array(
			'iso' => 'BG',
			'name' => 'BULGARIA',
			'printable_name' => 'Bulgaria',
			'iso3' => 'BGR',
			'numcode' => '100',
		),
		array(
			'iso' => 'BF',
			'name' => 'BURKINA FASO',
			'printable_name' => 'Burkina Faso',
			'iso3' => 'BFA',
			'numcode' => '854',
		),
		array(
			'iso' => 'BI',
			'name' => 'BURUNDI',
			'printable_name' => 'Burundi',
			'iso3' => 'BDI',
			'numcode' => '108',
		),
		array(
			'iso' => 'KH',
			'name' => 'CAMBODIA',
			'printable_name' => 'Cambodia',
			'iso3' => 'KHM',
			'numcode' => '116',
		),
		array(
			'iso' => 'CM',
			'name' => 'CAMEROON',
			'printable_name' => 'Cameroon',
			'iso3' => 'CMR',
			'numcode' => '120',
		),
		array(
			'iso' => 'CA',
			'name' => 'CANADA',
			'printable_name' => 'Canada',
			'iso3' => 'CAN',
			'numcode' => '124',
		),
		array(
			'iso' => 'CV',
			'name' => 'CAPE VERDE',
			'printable_name' => 'Cape Verde',
			'iso3' => 'CPV',
			'numcode' => '132',
		),
		array(
			'iso' => 'KY',
			'name' => 'CAYMAN ISLANDS',
			'printable_name' => 'Cayman Islands',
			'iso3' => 'CYM',
			'numcode' => '136',
		),
		array(
			'iso' => 'CF',
			'name' => 'CENTRAL AFRICAN REPUBLIC',
			'printable_name' => 'Central African Republic',
			'iso3' => 'CAF',
			'numcode' => '140',
		),
		array(
			'iso' => 'TD',
			'name' => 'CHAD',
			'printable_name' => 'Chad',
			'iso3' => 'TCD',
			'numcode' => '148',
		),
		array(
			'iso' => 'CL',
			'name' => 'CHILE',
			'printable_name' => 'Chile',
			'iso3' => 'CHL',
			'numcode' => '152',
		),
		array(
			'iso' => 'CN',
			'name' => 'CHINA',
			'printable_name' => 'China',
			'iso3' => 'CHN',
			'numcode' => '156',
		),
		array(
			'iso' => 'CX',
			'name' => 'CHRISTMAS ISLAND',
			'printable_name' => 'Christmas Island',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'CC',
			'name' => 'COCOS (KEELING) ISLANDS',
			'printable_name' => 'Cocos (Keeling) Islands',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'CO',
			'name' => 'COLOMBIA',
			'printable_name' => 'Colombia',
			'iso3' => 'COL',
			'numcode' => '170',
		),
		array(
			'iso' => 'KM',
			'name' => 'COMOROS',
			'printable_name' => 'Comoros',
			'iso3' => 'COM',
			'numcode' => '174',
		),
		array(
			'iso' => 'CG',
			'name' => 'CONGO',
			'printable_name' => 'Congo',
			'iso3' => 'COG',
			'numcode' => '178',
		),
		array(
			'iso' => 'CD',
			'name' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
			'printable_name' => 'Congo, the Democratic Republic of the',
			'iso3' => 'COD',
			'numcode' => '180',
		),
		array(
			'iso' => 'CK',
			'name' => 'COOK ISLANDS',
			'printable_name' => 'Cook Islands',
			'iso3' => 'COK',
			'numcode' => '184',
		),
		array(
			'iso' => 'CR',
			'name' => 'COSTA RICA',
			'printable_name' => 'Costa Rica',
			'iso3' => 'CRI',
			'numcode' => '188',
		),
		array(
			'iso' => 'CI',
			'name' => 'COTE D\'IVOIRE',
			'printable_name' => 'Cote D\'Ivoire',
			'iso3' => 'CIV',
			'numcode' => '384',
		),
		array(
			'iso' => 'HR',
			'name' => 'CROATIA',
			'printable_name' => 'Croatia',
			'iso3' => 'HRV',
			'numcode' => '191',
		),
		array(
			'iso' => 'CU',
			'name' => 'CUBA',
			'printable_name' => 'Cuba',
			'iso3' => 'CUB',
			'numcode' => '192',
		),
		array(
			'iso' => 'CY',
			'name' => 'CYPRUS',
			'printable_name' => 'Cyprus',
			'iso3' => 'CYP',
			'numcode' => '196',
		),
		array(
			'iso' => 'CZ',
			'name' => 'CZECH REPUBLIC',
			'printable_name' => 'Czech Republic',
			'iso3' => 'CZE',
			'numcode' => '203',
		),
		array(
			'iso' => 'DK',
			'name' => 'DENMARK',
			'printable_name' => 'Denmark',
			'iso3' => 'DNK',
			'numcode' => '208',
		),
		array(
			'iso' => 'DJ',
			'name' => 'DJIBOUTI',
			'printable_name' => 'Djibouti',
			'iso3' => 'DJI',
			'numcode' => '262',
		),
		array(
			'iso' => 'DM',
			'name' => 'DOMINICA',
			'printable_name' => 'Dominica',
			'iso3' => 'DMA',
			'numcode' => '212',
		),
		array(
			'iso' => 'DO',
			'name' => 'DOMINICAN REPUBLIC',
			'printable_name' => 'Dominican Republic',
			'iso3' => 'DOM',
			'numcode' => '214',
		),
		array(
			'iso' => 'EC',
			'name' => 'ECUADOR',
			'printable_name' => 'Ecuador',
			'iso3' => 'ECU',
			'numcode' => '218',
		),
		array(
			'iso' => 'EG',
			'name' => 'EGYPT',
			'printable_name' => 'Egypt',
			'iso3' => 'EGY',
			'numcode' => '818',
		),
		array(
			'iso' => 'SV',
			'name' => 'EL SALVADOR',
			'printable_name' => 'El Salvador',
			'iso3' => 'SLV',
			'numcode' => '222',
		),
		array(
			'iso' => 'GQ',
			'name' => 'EQUATORIAL GUINEA',
			'printable_name' => 'Equatorial Guinea',
			'iso3' => 'GNQ',
			'numcode' => '226',
		),
		array(
			'iso' => 'ER',
			'name' => 'ERITREA',
			'printable_name' => 'Eritrea',
			'iso3' => 'ERI',
			'numcode' => '232',
		),
		array(
			'iso' => 'EE',
			'name' => 'ESTONIA',
			'printable_name' => 'Estonia',
			'iso3' => 'EST',
			'numcode' => '233',
		),
		array(
			'iso' => 'ET',
			'name' => 'ETHIOPIA',
			'printable_name' => 'Ethiopia',
			'iso3' => 'ETH',
			'numcode' => '231',
		),
		array(
			'iso' => 'FK',
			'name' => 'FALKLAND ISLANDS (MALVINAS)',
			'printable_name' => 'Falkland Islands (Malvinas)',
			'iso3' => 'FLK',
			'numcode' => '238',
		),
		array(
			'iso' => 'FO',
			'name' => 'FAROE ISLANDS',
			'printable_name' => 'Faroe Islands',
			'iso3' => 'FRO',
			'numcode' => '234',
		),
		array(
			'iso' => 'FJ',
			'name' => 'FIJI',
			'printable_name' => 'Fiji',
			'iso3' => 'FJI',
			'numcode' => '242',
		),
		array(
			'iso' => 'FI',
			'name' => 'FINLAND',
			'printable_name' => 'Finland',
			'iso3' => 'FIN',
			'numcode' => '246',
		),
		array(
			'iso' => 'FR',
			'name' => 'FRANCE',
			'printable_name' => 'France',
			'iso3' => 'FRA',
			'numcode' => '250',
		),
		array(
			'iso' => 'GF',
			'name' => 'FRENCH GUIANA',
			'printable_name' => 'French Guiana',
			'iso3' => 'GUF',
			'numcode' => '254',
		),
		array(
			'iso' => 'PF',
			'name' => 'FRENCH POLYNESIA',
			'printable_name' => 'French Polynesia',
			'iso3' => 'PYF',
			'numcode' => '258',
		),
		array(
			'iso' => 'TF',
			'name' => 'FRENCH SOUTHERN TERRITORIES',
			'printable_name' => 'French Southern Territories',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'GA',
			'name' => 'GABON',
			'printable_name' => 'Gabon',
			'iso3' => 'GAB',
			'numcode' => '266',
		),
		array(
			'iso' => 'GM',
			'name' => 'GAMBIA',
			'printable_name' => 'Gambia',
			'iso3' => 'GMB',
			'numcode' => '270',
		),
		array(
			'iso' => 'GE',
			'name' => 'GEORGIA',
			'printable_name' => 'Georgia',
			'iso3' => 'GEO',
			'numcode' => '268',
		),
		array(
			'iso' => 'DE',
			'name' => 'GERMANY',
			'printable_name' => 'Germany',
			'iso3' => 'DEU',
			'numcode' => '276',
		),
		array(
			'iso' => 'GH',
			'name' => 'GHANA',
			'printable_name' => 'Ghana',
			'iso3' => 'GHA',
			'numcode' => '288',
		),
		array(
			'iso' => 'GI',
			'name' => 'GIBRALTAR',
			'printable_name' => 'Gibraltar',
			'iso3' => 'GIB',
			'numcode' => '292',
		),
		array(
			'iso' => 'GR',
			'name' => 'GREECE',
			'printable_name' => 'Greece',
			'iso3' => 'GRC',
			'numcode' => '300',
		),
		array(
			'iso' => 'GL',
			'name' => 'GREENLAND',
			'printable_name' => 'Greenland',
			'iso3' => 'GRL',
			'numcode' => '304',
		),
		array(
			'iso' => 'GD',
			'name' => 'GRENADA',
			'printable_name' => 'Grenada',
			'iso3' => 'GRD',
			'numcode' => '308',
		),
		array(
			'iso' => 'GP',
			'name' => 'GUADELOUPE',
			'printable_name' => 'Guadeloupe',
			'iso3' => 'GLP',
			'numcode' => '312',
		),
		array(
			'iso' => 'GU',
			'name' => 'GUAM',
			'printable_name' => 'Guam',
			'iso3' => 'GUM',
			'numcode' => '316',
		),
		array(
			'iso' => 'GT',
			'name' => 'GUATEMALA',
			'printable_name' => 'Guatemala',
			'iso3' => 'GTM',
			'numcode' => '320',
		),
		array(
			'iso' => 'GN',
			'name' => 'GUINEA',
			'printable_name' => 'Guinea',
			'iso3' => 'GIN',
			'numcode' => '324',
		),
		array(
			'iso' => 'GW',
			'name' => 'GUINEA-BISSAU',
			'printable_name' => 'Guinea-Bissau',
			'iso3' => 'GNB',
			'numcode' => '624',
		),
		array(
			'iso' => 'GY',
			'name' => 'GUYANA',
			'printable_name' => 'Guyana',
			'iso3' => 'GUY',
			'numcode' => '328',
		),
		array(
			'iso' => 'HT',
			'name' => 'HAITI',
			'printable_name' => 'Haiti',
			'iso3' => 'HTI',
			'numcode' => '332',
		),
		array(
			'iso' => 'HM',
			'name' => 'HEARD ISLAND AND MCDONALD ISLANDS',
			'printable_name' => 'Heard Island and Mcdonald Islands',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'VA',
			'name' => 'HOLY SEE (VATICAN CITY STATE)',
			'printable_name' => 'Holy See (Vatican City State)',
			'iso3' => 'VAT',
			'numcode' => '336',
		),
		array(
			'iso' => 'HN',
			'name' => 'HONDURAS',
			'printable_name' => 'Honduras',
			'iso3' => 'HND',
			'numcode' => '340',
		),
		array(
			'iso' => 'HK',
			'name' => 'HONG KONG',
			'printable_name' => 'Hong Kong',
			'iso3' => 'HKG',
			'numcode' => '344',
		),
		array(
			'iso' => 'HU',
			'name' => 'HUNGARY',
			'printable_name' => 'Hungary',
			'iso3' => 'HUN',
			'numcode' => '348',
		),
		array(
			'iso' => 'IS',
			'name' => 'ICELAND',
			'printable_name' => 'Iceland',
			'iso3' => 'ISL',
			'numcode' => '352',
		),
		array(
			'iso' => 'IN',
			'name' => 'INDIA',
			'printable_name' => 'India',
			'iso3' => 'IND',
			'numcode' => '356',
		),
		array(
			'iso' => 'ID',
			'name' => 'INDONESIA',
			'printable_name' => 'Indonesia',
			'iso3' => 'IDN',
			'numcode' => '360',
		),
		array(
			'iso' => 'IR',
			'name' => 'IRAN, ISLAMIC REPUBLIC OF',
			'printable_name' => 'Iran, Islamic Republic of',
			'iso3' => 'IRN',
			'numcode' => '364',
		),
		array(
			'iso' => 'IQ',
			'name' => 'IRAQ',
			'printable_name' => 'Iraq',
			'iso3' => 'IRQ',
			'numcode' => '368',
		),
		array(
			'iso' => 'IE',
			'name' => 'IRELAND',
			'printable_name' => 'Ireland',
			'iso3' => 'IRL',
			'numcode' => '372',
		),
		array(
			'iso' => 'IL',
			'name' => 'ISRAEL',
			'printable_name' => 'Israel',
			'iso3' => 'ISR',
			'numcode' => '376',
		),
		array(
			'iso' => 'IT',
			'name' => 'ITALY',
			'printable_name' => 'Italy',
			'iso3' => 'ITA',
			'numcode' => '380',
		),
		array(
			'iso' => 'JM',
			'name' => 'JAMAICA',
			'printable_name' => 'Jamaica',
			'iso3' => 'JAM',
			'numcode' => '388',
		),
		array(
			'iso' => 'JP',
			'name' => 'JAPAN',
			'printable_name' => 'Japan',
			'iso3' => 'JPN',
			'numcode' => '392',
		),
		array(
			'iso' => 'JO',
			'name' => 'JORDAN',
			'printable_name' => 'Jordan',
			'iso3' => 'JOR',
			'numcode' => '400',
		),
		array(
			'iso' => 'KZ',
			'name' => 'KAZAKHSTAN',
			'printable_name' => 'Kazakhstan',
			'iso3' => 'KAZ',
			'numcode' => '398',
		),
		array(
			'iso' => 'KE',
			'name' => 'KENYA',
			'printable_name' => 'Kenya',
			'iso3' => 'KEN',
			'numcode' => '404',
		),
		array(
			'iso' => 'KI',
			'name' => 'KIRIBATI',
			'printable_name' => 'Kiribati',
			'iso3' => 'KIR',
			'numcode' => '296',
		),
		array(
			'iso' => 'KP',
			'name' => 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF',
			'printable_name' => 'Korea, Democratic People\'s Republic of',
			'iso3' => 'PRK',
			'numcode' => '408',
		),
		array(
			'iso' => 'KR',
			'name' => 'KOREA, REPUBLIC OF',
			'printable_name' => 'Korea, Republic of',
			'iso3' => 'KOR',
			'numcode' => '410',
		),
		array(
			'iso' => 'KW',
			'name' => 'KUWAIT',
			'printable_name' => 'Kuwait',
			'iso3' => 'KWT',
			'numcode' => '414',
		),
		array(
			'iso' => 'KG',
			'name' => 'KYRGYZSTAN',
			'printable_name' => 'Kyrgyzstan',
			'iso3' => 'KGZ',
			'numcode' => '417',
		),
		array(
			'iso' => 'LA',
			'name' => 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC',
			'printable_name' => 'Lao People\'s Democratic Republic',
			'iso3' => 'LAO',
			'numcode' => '418',
		),
		array(
			'iso' => 'LV',
			'name' => 'LATVIA',
			'printable_name' => 'Latvia',
			'iso3' => 'LVA',
			'numcode' => '428',
		),
		array(
			'iso' => 'LB',
			'name' => 'LEBANON',
			'printable_name' => 'Lebanon',
			'iso3' => 'LBN',
			'numcode' => '422',
		),
		array(
			'iso' => 'LS',
			'name' => 'LESOTHO',
			'printable_name' => 'Lesotho',
			'iso3' => 'LSO',
			'numcode' => '426',
		),
		array(
			'iso' => 'LR',
			'name' => 'LIBERIA',
			'printable_name' => 'Liberia',
			'iso3' => 'LBR',
			'numcode' => '430',
		),
		array(
			'iso' => 'LY',
			'name' => 'LIBYAN ARAB JAMAHIRIYA',
			'printable_name' => 'Libyan Arab Jamahiriya',
			'iso3' => 'LBY',
			'numcode' => '434',
		),
		array(
			'iso' => 'LI',
			'name' => 'LIECHTENSTEIN',
			'printable_name' => 'Liechtenstein',
			'iso3' => 'LIE',
			'numcode' => '438',
		),
		array(
			'iso' => 'LT',
			'name' => 'LITHUANIA',
			'printable_name' => 'Lithuania',
			'iso3' => 'LTU',
			'numcode' => '440',
		),
		array(
			'iso' => 'LU',
			'name' => 'LUXEMBOURG',
			'printable_name' => 'Luxembourg',
			'iso3' => 'LUX',
			'numcode' => '442',
		),
		array(
			'iso' => 'MO',
			'name' => 'MACAO',
			'printable_name' => 'Macao',
			'iso3' => 'MAC',
			'numcode' => '446',
		),
		array(
			'iso' => 'MK',
			'name' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
			'printable_name' => 'Macedonia, the Former Yugoslav Republic of',
			'iso3' => 'MKD',
			'numcode' => '807',
		),
		array(
			'iso' => 'MG',
			'name' => 'MADAGASCAR',
			'printable_name' => 'Madagascar',
			'iso3' => 'MDG',
			'numcode' => '450',
		),
		array(
			'iso' => 'MW',
			'name' => 'MALAWI',
			'printable_name' => 'Malawi',
			'iso3' => 'MWI',
			'numcode' => '454',
		),
		array(
			'iso' => 'MY',
			'name' => 'MALAYSIA',
			'printable_name' => 'Malaysia',
			'iso3' => 'MYS',
			'numcode' => '458',
		),
		array(
			'iso' => 'MV',
			'name' => 'MALDIVES',
			'printable_name' => 'Maldives',
			'iso3' => 'MDV',
			'numcode' => '462',
		),
		array(
			'iso' => 'ML',
			'name' => 'MALI',
			'printable_name' => 'Mali',
			'iso3' => 'MLI',
			'numcode' => '466',
		),
		array(
			'iso' => 'MT',
			'name' => 'MALTA',
			'printable_name' => 'Malta',
			'iso3' => 'MLT',
			'numcode' => '470',
		),
		array(
			'iso' => 'MH',
			'name' => 'MARSHALL ISLANDS',
			'printable_name' => 'Marshall Islands',
			'iso3' => 'MHL',
			'numcode' => '584',
		),
		array(
			'iso' => 'MQ',
			'name' => 'MARTINIQUE',
			'printable_name' => 'Martinique',
			'iso3' => 'MTQ',
			'numcode' => '474',
		),
		array(
			'iso' => 'MR',
			'name' => 'MAURITANIA',
			'printable_name' => 'Mauritania',
			'iso3' => 'MRT',
			'numcode' => '478',
		),
		array(
			'iso' => 'MU',
			'name' => 'MAURITIUS',
			'printable_name' => 'Mauritius',
			'iso3' => 'MUS',
			'numcode' => '480',
		),
		array(
			'iso' => 'YT',
			'name' => 'MAYOTTE',
			'printable_name' => 'Mayotte',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'MX',
			'name' => 'MEXICO',
			'printable_name' => 'Mexico',
			'iso3' => 'MEX',
			'numcode' => '484',
		),
		array(
			'iso' => 'FM',
			'name' => 'MICRONESIA, FEDERATED STATES OF',
			'printable_name' => 'Micronesia, Federated States of',
			'iso3' => 'FSM',
			'numcode' => '583',
		),
		array(
			'iso' => 'MD',
			'name' => 'MOLDOVA, REPUBLIC OF',
			'printable_name' => 'Moldova, Republic of',
			'iso3' => 'MDA',
			'numcode' => '498',
		),
		array(
			'iso' => 'MC',
			'name' => 'MONACO',
			'printable_name' => 'Monaco',
			'iso3' => 'MCO',
			'numcode' => '492',
		),
		array(
			'iso' => 'MN',
			'name' => 'MONGOLIA',
			'printable_name' => 'Mongolia',
			'iso3' => 'MNG',
			'numcode' => '496',
		),
		array(
			'iso' => 'MS',
			'name' => 'MONTSERRAT',
			'printable_name' => 'Montserrat',
			'iso3' => 'MSR',
			'numcode' => '500',
		),
		array(
			'iso' => 'MA',
			'name' => 'MOROCCO',
			'printable_name' => 'Morocco',
			'iso3' => 'MAR',
			'numcode' => '504',
		),
		array(
			'iso' => 'MZ',
			'name' => 'MOZAMBIQUE',
			'printable_name' => 'Mozambique',
			'iso3' => 'MOZ',
			'numcode' => '508',
		),
		array(
			'iso' => 'MM',
			'name' => 'MYANMAR',
			'printable_name' => 'Myanmar',
			'iso3' => 'MMR',
			'numcode' => '104',
		),
		array(
			'iso' => 'NA',
			'name' => 'NAMIBIA',
			'printable_name' => 'Namibia',
			'iso3' => 'NAM',
			'numcode' => '516',
		),
		array(
			'iso' => 'NR',
			'name' => 'NAURU',
			'printable_name' => 'Nauru',
			'iso3' => 'NRU',
			'numcode' => '520',
		),
		array(
			'iso' => 'NP',
			'name' => 'NEPAL',
			'printable_name' => 'Nepal',
			'iso3' => 'NPL',
			'numcode' => '524',
		),
		array(
			'iso' => 'NL',
			'name' => 'NETHERLANDS',
			'printable_name' => 'Netherlands',
			'iso3' => 'NLD',
			'numcode' => '528',
		),
		array(
			'iso' => 'AN',
			'name' => 'NETHERLANDS ANTILLES',
			'printable_name' => 'Netherlands Antilles',
			'iso3' => 'ANT',
			'numcode' => '530',
		),
		array(
			'iso' => 'NC',
			'name' => 'NEW CALEDONIA',
			'printable_name' => 'New Caledonia',
			'iso3' => 'NCL',
			'numcode' => '540',
		),
		array(
			'iso' => 'NZ',
			'name' => 'NEW ZEALAND',
			'printable_name' => 'New Zealand',
			'iso3' => 'NZL',
			'numcode' => '554',
		),
		array(
			'iso' => 'NI',
			'name' => 'NICARAGUA',
			'printable_name' => 'Nicaragua',
			'iso3' => 'NIC',
			'numcode' => '558',
		),
		array(
			'iso' => 'NE',
			'name' => 'NIGER',
			'printable_name' => 'Niger',
			'iso3' => 'NER',
			'numcode' => '562',
		),
		array(
			'iso' => 'NG',
			'name' => 'NIGERIA',
			'printable_name' => 'Nigeria',
			'iso3' => 'NGA',
			'numcode' => '566',
		),
		array(
			'iso' => 'NU',
			'name' => 'NIUE',
			'printable_name' => 'Niue',
			'iso3' => 'NIU',
			'numcode' => '570',
		),
		array(
			'iso' => 'NF',
			'name' => 'NORFOLK ISLAND',
			'printable_name' => 'Norfolk Island',
			'iso3' => 'NFK',
			'numcode' => '574',
		),
		array(
			'iso' => 'MP',
			'name' => 'NORTHERN MARIANA ISLANDS',
			'printable_name' => 'Northern Mariana Islands',
			'iso3' => 'MNP',
			'numcode' => '580',
		),
		array(
			'iso' => 'NO',
			'name' => 'NORWAY',
			'printable_name' => 'Norway',
			'iso3' => 'NOR',
			'numcode' => '578',
		),
		array(
			'iso' => 'OM',
			'name' => 'OMAN',
			'printable_name' => 'Oman',
			'iso3' => 'OMN',
			'numcode' => '512',
		),
		array(
			'iso' => 'PK',
			'name' => 'PAKISTAN',
			'printable_name' => 'Pakistan',
			'iso3' => 'PAK',
			'numcode' => '586',
		),
		array(
			'iso' => 'PW',
			'name' => 'PALAU',
			'printable_name' => 'Palau',
			'iso3' => 'PLW',
			'numcode' => '585',
		),
		array(
			'iso' => 'PS',
			'name' => 'PALESTINIAN TERRITORY, OCCUPIED',
			'printable_name' => 'Palestinian Territory, Occupied',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'PA',
			'name' => 'PANAMA',
			'printable_name' => 'Panama',
			'iso3' => 'PAN',
			'numcode' => '591',
		),
		array(
			'iso' => 'PG',
			'name' => 'PAPUA NEW GUINEA',
			'printable_name' => 'Papua New Guinea',
			'iso3' => 'PNG',
			'numcode' => '598',
		),
		array(
			'iso' => 'PY',
			'name' => 'PARAGUAY',
			'printable_name' => 'Paraguay',
			'iso3' => 'PRY',
			'numcode' => '600',
		),
		array(
			'iso' => 'PE',
			'name' => 'PERU',
			'printable_name' => 'Peru',
			'iso3' => 'PER',
			'numcode' => '604',
		),
		array(
			'iso' => 'PH',
			'name' => 'PHILIPPINES',
			'printable_name' => 'Philippines',
			'iso3' => 'PHL',
			'numcode' => '608',
		),
		array(
			'iso' => 'PN',
			'name' => 'PITCAIRN',
			'printable_name' => 'Pitcairn',
			'iso3' => 'PCN',
			'numcode' => '612',
		),
		array(
			'iso' => 'PL',
			'name' => 'POLAND',
			'printable_name' => 'Poland',
			'iso3' => 'POL',
			'numcode' => '616',
		),
		array(
			'iso' => 'PT',
			'name' => 'PORTUGAL',
			'printable_name' => 'Portugal',
			'iso3' => 'PRT',
			'numcode' => '620',
		),
		array(
			'iso' => 'PR',
			'name' => 'PUERTO RICO',
			'printable_name' => 'Puerto Rico',
			'iso3' => 'PRI',
			'numcode' => '630',
		),
		array(
			'iso' => 'QA',
			'name' => 'QATAR',
			'printable_name' => 'Qatar',
			'iso3' => 'QAT',
			'numcode' => '634',
		),
		array(
			'iso' => 'RE',
			'name' => 'REUNION',
			'printable_name' => 'Reunion',
			'iso3' => 'REU',
			'numcode' => '638',
		),
		array(
			'iso' => 'RO',
			'name' => 'ROMANIA',
			'printable_name' => 'Romania',
			'iso3' => 'ROM',
			'numcode' => '642',
		),
		array(
			'iso' => 'RU',
			'name' => 'RUSSIAN FEDERATION',
			'printable_name' => 'Russian Federation',
			'iso3' => 'RUS',
			'numcode' => '643',
		),
		array(
			'iso' => 'RW',
			'name' => 'RWANDA',
			'printable_name' => 'Rwanda',
			'iso3' => 'RWA',
			'numcode' => '646',
		),
		array(
			'iso' => 'SH',
			'name' => 'SAINT HELENA',
			'printable_name' => 'Saint Helena',
			'iso3' => 'SHN',
			'numcode' => '654',
		),
		array(
			'iso' => 'KN',
			'name' => 'SAINT KITTS AND NEVIS',
			'printable_name' => 'Saint Kitts and Nevis',
			'iso3' => 'KNA',
			'numcode' => '659',
		),
		array(
			'iso' => 'LC',
			'name' => 'SAINT LUCIA',
			'printable_name' => 'Saint Lucia',
			'iso3' => 'LCA',
			'numcode' => '662',
		),
		array(
			'iso' => 'PM',
			'name' => 'SAINT PIERRE AND MIQUELON',
			'printable_name' => 'Saint Pierre and Miquelon',
			'iso3' => 'SPM',
			'numcode' => '666',
		),
		array(
			'iso' => 'VC',
			'name' => 'SAINT VINCENT AND THE GRENADINES',
			'printable_name' => 'Saint Vincent and the Grenadines',
			'iso3' => 'VCT',
			'numcode' => '670',
		),
		array(
			'iso' => 'WS',
			'name' => 'SAMOA',
			'printable_name' => 'Samoa',
			'iso3' => 'WSM',
			'numcode' => '882',
		),
		array(
			'iso' => 'SM',
			'name' => 'SAN MARINO',
			'printable_name' => 'San Marino',
			'iso3' => 'SMR',
			'numcode' => '674',
		),
		array(
			'iso' => 'ST',
			'name' => 'SAO TOME AND PRINCIPE',
			'printable_name' => 'Sao Tome and Principe',
			'iso3' => 'STP',
			'numcode' => '678',
		),
		array(
			'iso' => 'SA',
			'name' => 'SAUDI ARABIA',
			'printable_name' => 'Saudi Arabia',
			'iso3' => 'SAU',
			'numcode' => '682',
		),
		array(
			'iso' => 'SN',
			'name' => 'SENEGAL',
			'printable_name' => 'Senegal',
			'iso3' => 'SEN',
			'numcode' => '686',
		),
		array(
			'iso' => 'CS',
			'name' => 'SERBIA AND MONTENEGRO',
			'printable_name' => 'Serbia and Montenegro',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'SC',
			'name' => 'SEYCHELLES',
			'printable_name' => 'Seychelles',
			'iso3' => 'SYC',
			'numcode' => '690',
		),
		array(
			'iso' => 'SL',
			'name' => 'SIERRA LEONE',
			'printable_name' => 'Sierra Leone',
			'iso3' => 'SLE',
			'numcode' => '694',
		),
		array(
			'iso' => 'SG',
			'name' => 'SINGAPORE',
			'printable_name' => 'Singapore',
			'iso3' => 'SGP',
			'numcode' => '702',
		),
		array(
			'iso' => 'SK',
			'name' => 'SLOVAKIA',
			'printable_name' => 'Slovakia',
			'iso3' => 'SVK',
			'numcode' => '703',
		),
		array(
			'iso' => 'SI',
			'name' => 'SLOVENIA',
			'printable_name' => 'Slovenia',
			'iso3' => 'SVN',
			'numcode' => '705',
		),
		array(
			'iso' => 'SB',
			'name' => 'SOLOMON ISLANDS',
			'printable_name' => 'Solomon Islands',
			'iso3' => 'SLB',
			'numcode' => '090',
		),
		array(
			'iso' => 'SO',
			'name' => 'SOMALIA',
			'printable_name' => 'Somalia',
			'iso3' => 'SOM',
			'numcode' => '706',
		),
		array(
			'iso' => 'ZA',
			'name' => 'SOUTH AFRICA',
			'printable_name' => 'South Africa',
			'iso3' => 'ZAF',
			'numcode' => '710',
		),
		array(
			'iso' => 'GS',
			'name' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
			'printable_name' => 'South Georgia and the South Sandwich Islands',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'ES',
			'name' => 'SPAIN',
			'printable_name' => 'Spain',
			'iso3' => 'ESP',
			'numcode' => '724',
		),
		array(
			'iso' => 'LK',
			'name' => 'SRI LANKA',
			'printable_name' => 'Sri Lanka',
			'iso3' => 'LKA',
			'numcode' => '144',
		),
		array(
			'iso' => 'SD',
			'name' => 'SUDAN',
			'printable_name' => 'Sudan',
			'iso3' => 'SDN',
			'numcode' => '736',
		),
		array(
			'iso' => 'SR',
			'name' => 'SURINAME',
			'printable_name' => 'Suriname',
			'iso3' => 'SUR',
			'numcode' => '740',
		),
		array(
			'iso' => 'SJ',
			'name' => 'SVALBARD AND JAN MAYEN',
			'printable_name' => 'Svalbard and Jan Mayen',
			'iso3' => 'SJM',
			'numcode' => '744',
		),
		array(
			'iso' => 'SZ',
			'name' => 'SWAZILAND',
			'printable_name' => 'Swaziland',
			'iso3' => 'SWZ',
			'numcode' => '748',
		),
		array(
			'iso' => 'SE',
			'name' => 'SWEDEN',
			'printable_name' => 'Sweden',
			'iso3' => 'SWE',
			'numcode' => '752',
		),
		array(
			'iso' => 'CH',
			'name' => 'SWITZERLAND',
			'printable_name' => 'Switzerland',
			'iso3' => 'CHE',
			'numcode' => '756',
		),
		array(
			'iso' => 'SY',
			'name' => 'SYRIAN ARAB REPUBLIC',
			'printable_name' => 'Syrian Arab Republic',
			'iso3' => 'SYR',
			'numcode' => '760',
		),
		array(
			'iso' => 'TW',
			'name' => 'TAIWAN, PROVINCE OF CHINA',
			'printable_name' => 'Taiwan, Province of China',
			'iso3' => 'TWN',
			'numcode' => '158',
		),
		array(
			'iso' => 'TJ',
			'name' => 'TAJIKISTAN',
			'printable_name' => 'Tajikistan',
			'iso3' => 'TJK',
			'numcode' => '762',
		),
		array(
			'iso' => 'TZ',
			'name' => 'TANZANIA, UNITED REPUBLIC OF',
			'printable_name' => 'Tanzania, United Republic of',
			'iso3' => 'TZA',
			'numcode' => '834',
		),
		array(
			'iso' => 'TH',
			'name' => 'THAILAND',
			'printable_name' => 'Thailand',
			'iso3' => 'THA',
			'numcode' => '764',
		),
		array(
			'iso' => 'TL',
			'name' => 'TIMOR-LESTE',
			'printable_name' => 'Timor-Leste',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'TG',
			'name' => 'TOGO',
			'printable_name' => 'Togo',
			'iso3' => 'TGO',
			'numcode' => '768',
		),
		array(
			'iso' => 'TK',
			'name' => 'TOKELAU',
			'printable_name' => 'Tokelau',
			'iso3' => 'TKL',
			'numcode' => '772',
		),
		array(
			'iso' => 'TO',
			'name' => 'TONGA',
			'printable_name' => 'Tonga',
			'iso3' => 'TON',
			'numcode' => '776',
		),
		array(
			'iso' => 'TT',
			'name' => 'TRINIDAD AND TOBAGO',
			'printable_name' => 'Trinidad and Tobago',
			'iso3' => 'TTO',
			'numcode' => '780',
		),
		array(
			'iso' => 'TN',
			'name' => 'TUNISIA',
			'printable_name' => 'Tunisia',
			'iso3' => 'TUN',
			'numcode' => '788',
		),
		array(
			'iso' => 'TR',
			'name' => 'TURKEY',
			'printable_name' => 'Turkey',
			'iso3' => 'TUR',
			'numcode' => '792',
		),
		array(
			'iso' => 'TM',
			'name' => 'TURKMENISTAN',
			'printable_name' => 'Turkmenistan',
			'iso3' => 'TKM',
			'numcode' => '795',
		),
		array(
			'iso' => 'TC',
			'name' => 'TURKS AND CAICOS ISLANDS',
			'printable_name' => 'Turks and Caicos Islands',
			'iso3' => 'TCA',
			'numcode' => '796',
		),
		array(
			'iso' => 'TV',
			'name' => 'TUVALU',
			'printable_name' => 'Tuvalu',
			'iso3' => 'TUV',
			'numcode' => '798',
		),
		array(
			'iso' => 'UG',
			'name' => 'UGANDA',
			'printable_name' => 'Uganda',
			'iso3' => 'UGA',
			'numcode' => '800',
		),
		array(
			'iso' => 'UA',
			'name' => 'UKRAINE',
			'printable_name' => 'Ukraine',
			'iso3' => 'UKR',
			'numcode' => '804',
		),
		array(
			'iso' => 'AE',
			'name' => 'UNITED ARAB EMIRATES',
			'printable_name' => 'United Arab Emirates',
			'iso3' => 'ARE',
			'numcode' => '784',
		),
		array(
			'iso' => 'GB',
			'name' => 'UNITED KINGDOM',
			'printable_name' => 'United Kingdom',
			'iso3' => 'GBR',
			'numcode' => '826',
		),
		array(
			'iso' => 'US',
			'name' => 'UNITED STATES',
			'printable_name' => 'United States',
			'iso3' => 'USA',
			'numcode' => '840',
		),
		array(
			'iso' => 'UM',
			'name' => 'UNITED STATES MINOR OUTLYING ISLANDS',
			'printable_name' => 'United States Minor Outlying Islands',
			'iso3' => NULL,
			'numcode' => NULL,
		),
		array(
			'iso' => 'UY',
			'name' => 'URUGUAY',
			'printable_name' => 'Uruguay',
			'iso3' => 'URY',
			'numcode' => '858',
		),
		array(
			'iso' => 'UZ',
			'name' => 'UZBEKISTAN',
			'printable_name' => 'Uzbekistan',
			'iso3' => 'UZB',
			'numcode' => '860',
		),
		array(
			'iso' => 'VU',
			'name' => 'VANUATU',
			'printable_name' => 'Vanuatu',
			'iso3' => 'VUT',
			'numcode' => '548',
		),
		array(
			'iso' => 'VE',
			'name' => 'VENEZUELA',
			'printable_name' => 'Venezuela',
			'iso3' => 'VEN',
			'numcode' => '862',
		),
		array(
			'iso' => 'VN',
			'name' => 'VIET NAM',
			'printable_name' => 'Viet Nam',
			'iso3' => 'VNM',
			'numcode' => '704',
		),
		array(
			'iso' => 'VG',
			'name' => 'VIRGIN ISLANDS, BRITISH',
			'printable_name' => 'Virgin Islands, British',
			'iso3' => 'VGB',
			'numcode' => '092',
		),
		array(
			'iso' => 'VI',
			'name' => 'VIRGIN ISLANDS, U.S.',
			'printable_name' => 'Virgin Islands, U.s.',
			'iso3' => 'VIR',
			'numcode' => '850',
		),
		array(
			'iso' => 'WF',
			'name' => 'WALLIS AND FUTUNA',
			'printable_name' => 'Wallis and Futuna',
			'iso3' => 'WLF',
			'numcode' => '876',
		),
		array(
			'iso' => 'EH',
			'name' => 'WESTERN SAHARA',
			'printable_name' => 'Western Sahara',
			'iso3' => 'ESH',
			'numcode' => '732',
		),
		array(
			'iso' => 'YE',
			'name' => 'YEMEN',
			'printable_name' => 'Yemen',
			'iso3' => 'YEM',
			'numcode' => '887',
		),
		array(
			'iso' => 'ZM',
			'name' => 'ZAMBIA',
			'printable_name' => 'Zambia',
			'iso3' => 'ZMB',
			'numcode' => '894',
		),
		array(
			'iso' => 'ZW',
			'name' => 'ZIMBABWE',
			'printable_name' => 'Zimbabwe',
			'iso3' => 'ZWE',
			'numcode' => '716',
		),
	);

	/**
	 * @var int The role_id of the Administrator role
	 */
	private $admin_role_id = 1;

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		// Email Queue Permissions
		if ( ! $this->db->field_exists('Bonfire_Emailer_View', $this->permissions_table))
		{
			$this->dbforge->add_column($this->permissions_table, $this->permissions_fields);

			$this->db->where('role_id', $this->admin_role_id)
				->update($this->permissions_table, $this->permissions_data);
/*			$prefix = $this->db->dbprefix;
			$this->db->query("UPDATE {$prefix}permissions SET `Bonfire.Emailer.View`=1 WHERE `role_id`=1");
 */
		}

		// Add countries table for our users.
		// Source: http://27.org/isocountrylist/
		if ( ! $this->db->table_exists($this->countries_table))
		{
			$this->dbforge->add_field($this->countries_fields);
			$this->dbforge->add_key('iso', true);
			$this->dbforge->create_table($this->countries_table);

			// And... the countries themselves. (whew!)
			$this->db->insert_batch($this->countries_table, $this->countries_data);
		}

		// Users table changes
		if ($this->db->field_exists('temp_password_hash', $this->users_table))
		{
			$this->dbforge->modify_column($this->users_table, $this->users_fields);
		}
		if ( ! $this->db->field_exists('country_iso', $this->users_table))
		{
			$this->dbforge->add_column($this->users_table, $this->users_new_fields);
		}

		// Remove the zip_extra field
		foreach ($this->users_drop_fields as $column_name => $column_def)
		{
			if ( $this->db->field_exists($column_name, $this->users_table))
			{
				$this->dbforge->drop_column($this->users_table, $column_name);
			}
		}

		// Activity Table
		if ( ! $this->db->table_exists($this->activities_table))
		{
			$this->dbforge->add_field($this->activities_fields);
			$this->dbforge->add_key('activity_id', true);
			$this->dbforge->create_table($this->activities_table);
		}
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		// drop new columns on permissions table
		foreach ($this->permissions_fields as $column_name => $column_def)
		{
			if ($this->db->field_exists($column_name, $this->permissions_table))
			{
				$this->dbforge->drop_column($this->permissions_table, $column_name);
			}
		}

		// revert users table changes
		// drop added columns from users table
		foreach ($this->users_new_fields as $column_name => $column_def)
		{
			if ($this->db->field_exists($column_name, $this->users_table))
			{
				$this->dbforge->drop_column($this->users_table, $column_name);
			}
		}

		// revert modified columns on users table
		$this->dbforge->modify_column($this->users_table, $this->users_fields_down);

		// add dropped columns back to users table
		$users_drop_field_names = array_keys($this->users_drop_fields);
		if ( ! empty($users_drop_field_names))
		{
			if ( ! $this->db->field_exists($users_drop_field_names[0], $this->users_table))
			{
				$this->dbforge->add_column($this->users_table, $this->users_drop_fields);
			}
		}

		// Drop our countries table
		$this->dbforge->drop_table($this->countries_table);

		// Drop Activities Table
		$this->dbforge->drop_table($this->activities_table);
	}
}