<?php


/**
 * Boot file contains variable on request to system will need.
 */

use GeoIp2\Database\Reader;
use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Entities\Node;
use Mini\Cms\Entities\User;
use Mini\Cms\Fields\AddressField;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Respositories\Territory\Country;
use Mini\Cms\Modules\Respositories\Territory\State;
use Mini\Cms\Modules\Storage\Tempstore;

// Routes routes loaded
global  $routes;

global $database;

global $configurations;

/**
 * Get configuration value.
 * @param string $notation
 * @param $value
 * @return mixed
 * @throws Exception
 */
function getConfigValue(string $notation, $value = null): mixed
{

    /**@var $config ConfigFactory **/
    $config = \Mini\Cms\Services\Services::create('config.factory');
    $array = $config->getConfigurations();
    $keys = explode('.', $notation);
    $lastKey = array_pop($keys);
    $current = &$array;

    foreach ($keys as $key) {
        if (!isset($current[$key])) {
            return null; // Key does not exist
        }
        $current = &$current[$key];
    }

    if ($value === null) {
        if (isset($current[$lastKey])) {
            return $current[$lastKey];
        } else {
            return null; // Key does not exist
        }
    } else {
        if (isset($current[$lastKey]) && $current[$lastKey] === $value) {
            return $current;
        } else {
            return null; // Value does not match
        }
    }
}

/**
 * Get user entity.
 * @param int $user_id
 * @return User
 */
function load_user(int $user_id): User
{
    return User::load($user_id);
}

/**
 * Get node entity.
 * @param int $node_id
 * @return Node|null
 */
function load_node(int $node_id): ?Node
{
    return Node::load($node_id);
}

/**
 * Get address info.
 * @param int $address_id
 * @return array
 */
function load_address(int $address_id): array
{
    $query = \Mini\Cms\Connections\Database\Database::database()->prepare("SELECT * FROM `address_fields_data` WHERE `lid` = :id");
    $query->execute([':id' => $address_id]);
    $address = $query->fetch();

    $return = array(
        'raw' => $address,
    );
    if($address)
    {
        $country = new Country($address['country_code']);
        $return['country'] = $country;
        if($address['state_code']) {
            $state = new State($address['country_code'],$address['state_code']);
            $return['state'] = $state;
        }
    }
    return $return;
}

/**
 * Get address field
 * @param string $address_field_name
 * @param string $default_country_code
 * @return StdClass
 */
function construct_address_field(string $address_field_name, string $default_country_code = 'US'): StdClass
{
    $address_field = new AddressField();
    $address_field->setName(clean_string($address_field_name,replace_char: '_'));
    $address_field->setLabel($address_field_name);
    $address_field->setDefaultValue($default_country_code);
    $address_field->setEntityID(0);

    $markup = \Mini\Cms\Field::markUp($address_field->getType());
    $markup->buildMarkup($address_field,['value'=>$default_country_code]);
    $collection = new \StdClass();
    $collection->markup = $markup;
    $collection->address = $address_field;
    return $collection;
}

function clean_string(string $input, string $remove_char = '', string $replace_char = ''): string
{
    // Remove specified characters and replace them
    return str_replace($remove_char, $replace_char, $input);
}

/**
 * Get IP address
 * @return mixed|null
 */
function getClientIP(): mixed
{
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    } else {
        return null;
    }
}

/**
 * Get Country code by ip
 * @param $ip
 * @return mixed|string|null
 */
function getCountryByIP($ip): mixed
{
    try {
        $reader = new Reader(__DIR__.'/GeoLite2-Country.mmdb');
        $record = $reader->country($ip);
        return $record->country->isoCode;
    } catch (Exception $e) {
        return null;
    }
}

function truncate_text(string $text, int $length = 100, string $end = '...'): string
{
    // Check if the text needs truncation
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    // Truncate text at the nearest space to avoid breaking words
    $truncated = mb_strimwidth($text, 0, $length, $end);

    // Ensure that truncation does not break words
    if (mb_strpos($truncated, ' ') !== false && mb_strlen($truncated) < mb_strlen($text)) {
        // Find the last space in the truncated text
        $last_space = mb_strrpos($truncated, ' ');
        if ($last_space !== false) {
            $truncated = mb_substr($truncated, 0, $last_space) . $end;
        }
    }

    return $truncated;
}

function _login_user(User $user): User
{
    $data = $user->getValues();
    Extensions::runHooks('_user_login_validated',[$data]);
    Tempstore::save('default_current_user', $user->getValues(), time() * 60 * 60 * 365);
    return $user;
}

function define_global(string $key, mixed $value): void
{
    if(isset($GLOBALS[$key])) {
        unset($GLOBALS[$key]);
    }
    $GLOBALS[$key] = $value;
}

function get_global(string $key): mixed
{
    return $GLOBALS[$key] ?? null;
}


function time_ago(int $timestamp)
{
    $timeNow = time(); // Current timestamp
    $timeDifference = $timeNow - $timestamp;

    // Time periods in seconds
    $units = [
        31556926 => 'year',
        2629743 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];

    foreach ($units as $unitSeconds => $unitName) {
        if ($timeDifference >= $unitSeconds) {
            $value = floor($timeDifference / $unitSeconds);
            return $value . ' ' . $unitName . ($value > 1 ? 's' : '') . ' ago';
        }
    }

    return "just now";
}


$config = \Mini\Cms\Services\Services::create('config.factory');
$maintain_mode = $config->get('maintain_mode');

if($maintain_mode['is_active'] && isset($maintain_mode['test_mode']) && $maintain_mode['test_mode'] === false) {
    $mail = $maintain_mode['mail'] ?? null;
    $downtime = $maintain_mode['downtime'] ?? null;
    echo <<<MAIN
<div style="padding: 20px;
    width: 100%;
    margin: auto;
    text-align: center;">
    <div>
       <h2>We'll Be Back Soon!</h2>
    </div>
    <div>
       <p>Our site is currently undergoing scheduled maintenance. We are working hard to improve your experience and will be back online shortly. Thank you for your patience and understanding.</p>
       <p>If you have any questions, please feel free to contact us at <a href="mailto:$mail">administrator</a></p>
       <p><strong>Expected Downtime:</strong> <span>$downtime</span></p>

       <p>Thank you for your patience!</p>
    </div>
</div>
MAIN;
    exit;
}

