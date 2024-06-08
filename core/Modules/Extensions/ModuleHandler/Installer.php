<?php

namespace Mini\Cms\Modules\Extensions\ModuleHandler;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Extensions\Extensions;

class Installer
{

    private string $info_file;

    private string $schema_file;
    private string $extension_path;

    /**
     * @param string $path_to_module This is a to module.
     */
    public function __construct(string $path_to_module)
    {
        $this->info_file = '';
        $this->schema_file = '';

        Extensions::extensionsStorage();
        if(is_dir($path_to_module)){
            $files = array_diff(scandir($path_to_module), ['..', '.']);
            foreach ($files as $file) {
                if(str_ends_with($file, '.info.json')){
                    $this->info_file = $path_to_module . '/'. $file;
                }
                if(str_ends_with($file, '.schema.json')){
                    $this->schema_file = $path_to_module . '/'. $file;
                }
            }
        }
        $this->extension_path = $path_to_module;
    }

    /**
     * Installation of database tables this module will need.
     * @return void
     */
    public function installModuleSchema(): void
    {
        if(!empty($this->schema_file) && file_exists($this->schema_file)){
            $schema_data = json_decode(file_get_contents($this->schema_file), true);
            if(!empty($schema_data['install'])){
                foreach ($schema_data['install'] as $schema){
                    $query = Database::database()->prepare($schema);
                    $query->execute();
                }
            }

        }
    }

    /**
     * Query of table to be removed.
     * @return void
     */
    public function uninstallModuleSchema(): void
    {
        if(!empty($this->schema_file) && file_exists($this->schema_file)){
            $schema_data = json_decode(file_get_contents($this->schema_file), true);
            if(!empty($schema_data['uninstall'])){
                foreach ($schema_data['uninstall'] as $schema){
                    $query = Database::database()->prepare($schema);
                    $query->execute();
                }
            }

        }
    }

    /**
     * Installing module info.
     * @return bool
     */
    public function saveModuleSchema(): bool
    {
        if(!empty($this->info_file) && file_exists($this->info_file)){
            $info_data = json_decode(file_get_contents($this->info_file), true);
            if(!empty($info_data)){
                $ext_name = $info_data['name'];
                $ext_version = $info_data['version'] ?? null;
                $ext_status = $info_data['status'] ?? null;
                $ext_type = $info_data['type'] ?? null;
                if(!empty($ext_name) && !empty($ext_type)){
                    $query = Database::database()->prepare("INSERT INTO extensions (ext_name, ext_version, ext_status, ext_type, ext_path) VALUES (?, ?, ?, ?,?)");
                    return $query->execute([$ext_name, $ext_version, $ext_status, $ext_type, $this->extension_path]);
                }
            }
        }
        return false;
    }

}