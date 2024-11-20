<?php
@session_start();

/**
 * Boot file contains variable on request to system will need.
 */

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Entities\User;
use Mini\Cms\Mini;
use Mini\Cms\Modules\Cache\Caching;
use Mini\Cms\Modules\CurrentUser\CurrentUser;
use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Modules\Metrical\Metrical;
use Mini\Cms\Modules\Storage\Tempstore;


/**
 * Booting the mini cms system
 */
global $database;

/**
 * Get configuration value.
 * @param string $notation
 * @param $value
 * @return mixed
 * @throws Exception
 */

/**
 * @deprecated Use get_config_value() instead.
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

function get_config_value(string $notation, $value = null): mixed
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

function clean_string_advance($string): string
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
function get_client_iP(): mixed
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

function destruct_global(string $key): void
{
    if(isset($GLOBALS[$key])) {
        unset($GLOBALS[$key]);
    }
}

function destruct_globals( ): void
{
    $keys = array_keys($GLOBALS);
    if(!empty($keys)) {
        foreach($keys as $key) {
            unset($GLOBALS[$key]);
        }
    }
}

function time_ago(int $timestamp): string
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

function build_path(string $route_id, array $options = [], array $params = [] ,bool $is_absolute = false): string
{
    $route = new \Mini\Cms\Routing\Route($route_id);
    if($uri_pattern = $route->getUrl()) {
        // Loop through the options and replace the placeholders in the URI pattern
        foreach ($options as $key => $value) {
            $uri_pattern = str_replace('{' . $key . '}', $value, $uri_pattern);
        }
        $params_line = http_build_query($params);
        if($is_absolute) {
            return \Mini\Cms\Mini::request()->getSchemeAndHttpHost(). '/' . trim($uri_pattern, '/'). (empty($params_line) ? '' : '?'.$params_line);
        }
        return $uri_pattern.  (empty($params_line) ? '' : '?'.$params_line);
    }
    throw new Exception("Unable to build path: Route with ID {$route_id} not found.");
}

function mini_cms_error_handler($errno, $errstr, $errfile, $errline)
{
    // Define critical error levels (you can expand this list if needed)
    $critical_errors = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

    // Check if the error is critical
    if (in_array($errno, $critical_errors)) {
        // Get the global error saver object (assuming this is part of your custom error handling system)
        $error_saver = get_global('error_saver');

        if ($error_saver) {
            // Set the error details into the error saver object
            $error_saver->setError($errno, $errstr, $errfile, $errline);
            // Save the error (presumably to log or handle)
            $error_saver->save();
        }

        // Clean up output buffer if any
        ob_end_flush();

        // Output a generic message to the user (you can customize this)
        print_r("A critical error occurred. Please try again later.");
        exit;  // Stop script execution on a critical error
    } else {
        // For non-critical errors (e.g., warnings, notices), log them without exiting
        $error_saver = get_global('error_saver');

        if ($error_saver) {
            // Set and save the non-critical error details for logging purposes
            $error_saver->setError($errno, $errstr, $errfile, $errline);
            $error_saver->save();
        }
    }
}

// Exception handler for uncaught exceptions, including PDO exceptions
function mini_cms_exception_handler($exception) {
    $error_saver = get_global('error_saver');
    if($error_saver) {
        $error_saver->setException($exception);
        $error_saver->save();
        ob_end_flush();
        print_r("unexpected error occurred");
        exit;
    }
    ob_end_flush();
    print_r($exception->__toString());
    exit;
}

function mini_php_shutdown_handler(): void
{
    
    // Duration calculation
    $started_time = get_global('mini_speed_meter');
    $end_time = time();
    $time_taken = $end_time - $started_time;
    $current_uid = (new CurrentUser())->id();
    Metrical::store([
        'start_time' => $started_time,
        'end_time' => $end_time,
        'time_taken' => $time_taken,
        'uid' => $current_uid,
        'method' => Mini::request()->getMethod(),
        'uri' => Mini::request()->getRequestUri(),
        'ip' => Mini::request()->getClientIp(),
    ]);
    destruct_globals();
}

