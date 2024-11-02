<?php
@session_start();

/**
 * Boot file contains variable on request to system will need.
 */

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Entities\User;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Storage\Tempstore;
use Symfony\Component\Yaml\Yaml;

/**
 * Booting the mini cms system
 */
global $bootstrap;

global $services;

$bootstrap = \Mini\Cms\System\System::boot();

$cache_global = Yaml::parseFile(__DIR__.'/../../configs/configurations.yml')['caching.cache_global'] ?? false;

// Boot up the services.
$services = \Mini\Cms\Modules\Cache\Caching::cache()->get('system-services-register');
if(empty($services)) {
    $services = Extensions::bootServices();
}

Extensions::bootRoutes();

// Routes routes loaded
global  $routes;

if($cache_global) {
    $routes = \Mini\Cms\Modules\Cache\Caching::cache()->get('system_routes');
}

global $database;

global $configurations;

if($cache_global) {
    $configurations = \Mini\Cms\Modules\Cache\Caching::cache()->get('system_configurations');
}
global $modules;

if($cache_global) {
    $modules = \Mini\Cms\Modules\Cache\Caching::cache()->get('system_modules');
}
global $menus;

if($cache_global) {
    $menus = \Mini\Cms\Modules\Cache\Caching::cache()->get('system_menus');
}

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


function clean_string(string $input, string $remove_char = ' ', string $replace_char = ''): string
{
    // Remove specified characters and replace them
    return str_replace($remove_char, $replace_char, $input);
}

function clean_string_advance($string)
{
    // Step 1: Replace all non-alphanumeric characters (including /, \) with a single space
    $cleanedString = preg_replace('/[^a-zA-Z0-9]/', ' ', $string);

    // Step 2: Replace multiple spaces with a single space
    $cleanedString = preg_replace('/\s+/', ' ', $cleanedString);

    // Step 3: Trim leading/trailing spaces and replace remaining spaces with '-'
    $finalString = str_replace(' ', '-', trim($cleanedString));

    return $finalString;
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

if(!empty($maintain_mode) && $maintain_mode['is_active'] && isset($maintain_mode['test_mode']) && $maintain_mode['test_mode'] === false) {
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

