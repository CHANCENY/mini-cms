<?php

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\default\modules\default\terminal\src\Plugin\CommandsLoader;
use Mini\Cms\Entities\User;
use Mini\Cms\Mini;
use Mini\Cms\Modules\FileSystem\File;
use Mini\Cms\Modules\Themes\ThemeExtension;
use Symfony\Component\Yaml\Yaml;

function site_name()
{
    $colors = CommandsLoader::getColors();
    extract($colors);

    echo PHP_EOL;
    echo generate_dashes(get_config_value('site_information.BrandingAssets.Name')) . PHP_EOL;
    echo 'NAME: '. $yellow . get_config_value('site_information.BrandingAssets.Name'). $reset .PHP_EOL;
    echo generate_dashes(get_config_value('site_information.BrandingAssets.Name')) . PHP_EOL;
    echo PHP_EOL;
}

function site_mail() {
    $colors = CommandsLoader::getColors();
    extract($colors);
    echo PHP_EOL;
    echo generate_dashes(get_config_value('site_information.BrandingAssets.Email')) . PHP_EOL;
    echo 'EMAIL: '. $yellow . get_config_value('site_information.BrandingAssets.Email'). $reset. PHP_EOL;
    echo generate_dashes(get_config_value('site_information.BrandingAssets.Email')) . PHP_EOL;
    echo PHP_EOL;
}


function site_phone() {
    $colors = CommandsLoader::getColors();
    extract($colors);
    echo PHP_EOL;
    echo generate_dashes(get_config_value('site_information.BrandingAssets.Phone')).PHP_EOL;
    echo 'PHONE: '. $yellow . get_config_value('site_information.BrandingAssets.Phone'). $reset . PHP_EOL;
    echo generate_dashes(get_config_value('site_information.BrandingAssets.Phone')) . PHP_EOL;
    echo PHP_EOL;
}

function site_logo() {
    $colors = CommandsLoader::getColors();
    extract($colors);

    $file = File::load(get_config_value('site_information.BrandingAssets.Logo.fid'));
    $colors = CommandsLoader::getColors();
    extract($colors);
    echo implode('', array_fill(0, 50, '_'));
    echo PHP_EOL;
    echo PHP_EOL;
    echo $yellow . Yaml::dump($file->getRaw()) . $reset . PHP_EOL;
    echo PHP_EOL;
    echo implode('', array_fill(0, 50, '_'));
    echo PHP_EOL;
}

function db_status() {
   if(Mini::connection()) {
        $colors = CommandsLoader::getColors();
        extract($colors);
        echo generate_dashes('Connection status: ' . 'active');
        echo PHP_EOL;
        echo PHP_EOL;
        echo 'Connection status: '. $green . 'active' . $reset .PHP_EOL;
        echo PHP_EOL;
        echo generate_dashes('Connection status: ' . 'active');
        echo PHP_EOL;
   }
}

function generate_dashes(string $line) {
    $line_2 = null;
    for($i = 0; $i < strlen($line) + 1; $i++) {
        $line_2 .= '_';
    }
    return $line_2;
}

function user(int $uid) {
    $user = User::load($uid);
    if($user) {

        $colors = CommandsLoader::getColors();
        extract($colors);
        echo implode('', array_fill(0, 50,'_'));
        echo PHP_EOL;
        echo PHP_EOL;
        echo $yellow . $user . $reset .PHP_EOL;
        echo PHP_EOL;
        echo implode('', array_fill(0, 50, '_'));
        echo PHP_EOL;
    }
}

function users()
{
    $user = User::users();
    if ($user) {

        $colors = CommandsLoader::getColors();
        extract($colors);
        echo implode('', array_fill(0, 50, '_'));
        echo PHP_EOL;
        echo PHP_EOL;
        echo $yellow . Yaml::dump($user) . $reset . PHP_EOL;
        echo PHP_EOL;
        echo implode('', array_fill(0, 50, '_'));
        echo PHP_EOL;
    }
}

function configs()
{
    $config = new ConfigFactory;
    $colors = CommandsLoader::getColors();
    extract($colors);
    echo implode('', array_fill(0, 50, '_'));
    echo PHP_EOL;
    echo PHP_EOL;
    echo $yellow . Yaml::dump($config->getConfigurations()) . $reset . PHP_EOL;
    echo PHP_EOL;
    echo implode('', array_fill(0, 50, '_'));
    echo PHP_EOL;
}

function themes() {
    $themes = ThemeExtension::bootThemes();
    $colors = CommandsLoader::getColors();
    extract($colors);
    echo implode('', array_fill(0, 50, '_'));
    echo PHP_EOL;
    echo PHP_EOL;
    echo $yellow . Yaml::dump($themes) . $reset . PHP_EOL;
    echo PHP_EOL;
    echo implode('', array_fill(0, 50, '_'));
    echo PHP_EOL;
}