<?php

namespace Mini\Cms\Modules\PermanentStorage\Engine;

/**
 * This file contains class Engine which will be and can be used to process .fdb file.
 *
 * This file can be exposed to copyright with the intention of open source projects or commercial projects.
 *
 */
class Engine
{

    /**
     * This should be a relative path.
     * Note: It's important to provide a path that is outside webroot for enhancing security.
     * @var string This is a file where the all database actions will be done.
     */
    private string $database_file_name;

    /**
     * @var string The username of client to open, read and write to this fdb.
     */
    private string $database_username;

    /**
     * @var string The password of given database user.
     */
    private string $database_password;

    /**
     * @var string This will be the path where all data file will be stored.
     */
    private string $collection_path;

    /**
     * Parsed the .fdb content into easy workable content data.
     * @var array
     */
    private array $database_content;

    /**
     * Initialize fdb storage center.
     * @param string $database_file_name
     * @param string $database_username
     * @param string $database_password
     */
    public function __construct(string $database_file_name, string $database_username, string $database_password)
    {
        $db_path_list = str_contains($database_file_name,'/') ? explode('/',$database_file_name) : explode('\\',$database_file_name);
        $database_file_name = implode(DIRECTORY_SEPARATOR, $db_path_list);

        @mkdir(substr($database_file_name,0,strripos($database_file_name,DIRECTORY_SEPARATOR)));

        $this->database_file_name = $database_file_name;
        $this->database_username = $database_username;
        $this->database_password = $database_password;

        // Making connections if fdb file exist or make the fdb file.
        if(!file_exists($this->database_file_name . '.fdb')) {
            $this->database_content = [
                'metadata' => [
                    'db_name' => $database_file_name,
                    'version' => 1.0,
                    'created' => date('d F, Y',time()),
                    'description' => 'This is fdb file',
                ],
                'collections' => [
                    'path'=> 'collections',
                    'backup_enforce' => 'true',
                ],
                'collection_list' => [],
            ];
            file_put_contents($this->database_file_name.'.fdb', $this->writeFDBFile());
        }

        $directory_path = substr($this->database_file_name,0 , strripos($this->database_file_name,DIRECTORY_SEPARATOR));
        $directory_path = trim($directory_path, DIRECTORY_SEPARATOR);
        $collections_path = $directory_path . DIRECTORY_SEPARATOR . 'collections';
        @mkdir($collections_path);
        if(!is_dir($collections_path)) {
            $collections_path = DIRECTORY_SEPARATOR.$collections_path;
            @mkdir($collections_path);
        }

        $this->collection_path = $collections_path;
        $this->database_file_name = $database_file_name .'.fdb';
        if(file_exists($this->database_file_name)) {
            $this->parseFDBFile();
        }
    }

    /**
     * Parsing the .fdb
     * @return void
     */
    private function parseFDBFile(): void
    {
        $assocArray = [];
        $currentSection = '';
        $collectionName = '';
        $currentKey = '';

        $lines = file($this->database_file_name, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Handle Metadata
            if (strpos($line, 'Metadata:') !== false) {
                $currentSection = 'metadata';
                $assocArray['metadata'] = [];
                continue;
            }

            // Handle Collections
            if (strpos($line, 'Collections:') !== false) {
                $currentSection = 'collections';
                $assocArray['collections'] = [];
                continue;
            }

            // Handle Collection_List
            if (strpos($line, 'Collection_List:') !== false) {
                $currentSection = 'collection_list';
                $assocArray['collection_list'] = [];
                continue;
            }

            // Parse lines based on the current section
            if ($currentSection === 'metadata' || $currentSection === 'collections') {
                if (strpos($line, '-') === 0) {
                    $parts = explode(':', ltrim($line, '- '), 2);
                    $key = trim($parts[0]);
                    $value = isset($parts[1]) ? trim($parts[1]) : null;
                    $assocArray[$currentSection][$key] = $value;
                }
            } elseif ($currentSection === 'collection_list') {
                if (strpos($line, '--') === 0) {
                    // New collection name
                    $collectionName = trim($line, '-: ');
                    $assocArray[$currentSection][$collectionName] = [];
                } else if (strpos($line, '-') === 0) {
                    if (strpos($line, ':') !== false) {
                        $parts = explode(':', ltrim($line, '- '), 2);
                        $key = trim($parts[0]);
                        $values = array_map('trim', explode(',', $parts[1] ?? ""));
                        $currentKey = $key;
                        $assocArray[$currentSection][$collectionName][$key] = $values;
                    } else {
                        // Handle lines that don't have a colon, indicating values for the last parsed key
                        $value = trim($line, '- ');
                        $assocArray[$currentSection][$collectionName][$currentKey][] = $value;
                    }
                }
            }
        }

        // Remove empty values from arrays
        foreach ($assocArray['collection_list'] as $collection => &$attributes) {
            foreach ($attributes as &$attribute) {
                $attribute = array_filter($attribute, function ($value) {
                    return $value !== '';
                });
                $attribute = array_values($attribute); // Reindex after filtering
            }
        }

        $this->database_content = $assocArray;
    }

    /**
     * Adding collection item.
     * @param string $collectionName
     * @param array $keys
     * @param array $types
     * @param array $primary
     * @param array $unique
     * @return bool
     */
    public function addCollectionListItem(string $collectionName, array $keys, array $types, array $primary, array $unique = []): bool
    {
        if(isset($this->database_content['collection_list'][$collectionName])) {
            return false;
        }

        // Ensure the collection_list exists
        if (!isset($this->database_content['collection_list'])) {
            $this->database_content['collection_list'] = [];
        }
        if(count($primary) && count($keys) === count($types)) {

            foreach ($primary as $value) {
                if(!in_array($value,$keys)) {
                    return false;
                }
            }

            foreach ($unique as $value) {
                if(!in_array($value , $keys)) {
                    return false;
                }
            }

            // Add the new collection to the collection_list
            $this->database_content['collection_list'][$collectionName] = [
                'keys' => $keys,
                'type' => $types,
                'primary' => $primary,
                'unique' => $unique,
            ];
            $document = [
                'doc_name' => $collectionName,
                'default_unique' => $collectionName.'_uuid',
                'created_at' => time(),
                'updated_at' => time(),
                'access_at' => time(),
                'content' => []
            ];
           $this->writeCollectionFile($collectionName,$document);
        }
        return !empty(file_put_contents($this->database_file_name,$this->writeFDBFile()));
    }

    /**
     * Parsing assoc to .fdb format.
     * @return string
     */
    private function writeFDBFile(): string
    {
        $fileContent = '';

        // Write Metadata section
        if (isset($this->database_content['metadata'])) {
            $fileContent .= "Metadata:\n";
            foreach ($this->database_content['metadata'] as $key => $value) {
                $fileContent .= " -{$key}: {$value}\n";
            }
        }

        // Write Collections section
        if (isset($this->database_content['collections'])) {
            $fileContent .= "Collections:\n";
            foreach ($this->database_content['collections'] as $key => $value) {
                $fileContent .= " -{$key}: {$value}\n";
            }
        }

        // Write Collection_List section
        if (isset($this->database_content['collection_list'])) {
            $fileContent .= "Collection_List:\n";
            foreach ($this->database_content['collection_list'] as $collectionName => $attributes) {
                $fileContent .= " --{$collectionName}:\n";
                foreach ($attributes as $key => $values) {
                    $fileContent .= "  -{$key}:\n";
                    foreach ($values as $value) {
                        $fileContent .= "   -{$value}\n";
                    }
                }
            }
        }
        return $fileContent;
    }

    /**
     * Removing collection from .fdb file.
     * @param $collectionName
     * @return bool
     */
    public function removeCollectionListItem($collectionName): bool {

        // Check if collection_list exists
        if (isset($this->database_content['collection_list'][$collectionName])) {
            // Remove the collection item
            unset($this->database_content['collection_list'][$collectionName]);
            @unlink($this->collection_path.DIRECTORY_SEPARATOR.$collectionName.'.fdb.json');
            return !empty(file_put_contents($this->database_file_name,$this->writeFDBFile()));
        }
        return false;
    }

    /**
     * Get collection data.
     * @param $collectionName
     * @return mixed
     */
    function getCollectionData($collectionName): mixed
    {
        // Check if collection_list exists
        if (isset($this->database_content['collection_list'])) {
            // Check if the specific collection exists
            return $this->database_content['collection_list'][$collectionName] ?? "Collection '{$collectionName}' does not exist.\n";
        } else {
            return null;
        }
    }

    /**
     * Validate the .fdb file.
     * @param $assocArray
     * @return true|string
     */
    function validateFDBFileContent($assocArray): true|string
    {
        // Define required keys for validation
        $requiredMetadataKeys = ['db_name', 'db_username', 'db_password', 'version', 'created', 'description'];
        $requiredCollectionsKeys = ['path', 'backup_enforce'];
        $requiredCollectionAttributes = ['keys', 'type', 'primary', 'unique'];

        // Validate Metadata
        if (!isset($assocArray['metadata']) || !is_array($assocArray['metadata'])) {
            return "Metadata section is missing or invalid.\n";
        }
        foreach ($requiredMetadataKeys as $key) {
            if (!isset($assocArray['metadata'][$key])) {
                return "Metadata is missing required key: {$key}\n";
            }
        }

        // Validate Collections
        if (!isset($assocArray['collections']) || !is_array($assocArray['collections'])) {
            return "Collections section is missing or invalid.\n";
        }
        foreach ($requiredCollectionsKeys as $key) {
            if (!isset($assocArray['collections'][$key])) {
                return "Collections is missing required key: {$key}\n";
            }
        }

        // Validate Collection_List
        if (!isset($assocArray['collection_list']) || !is_array($assocArray['collection_list'])) {
            return "Collection_List section is missing or invalid.\n";
        }
        foreach ($assocArray['collection_list'] as $collectionName => $attributes) {
            if (!is_array($attributes)) {
                return "Collection '{$collectionName}' attributes are not an array.\n";
            }
            foreach ($requiredCollectionAttributes as $attr) {
                if (!isset($attributes[$attr]) || !is_array($attributes[$attr])) {
                    return "Collection '{$collectionName}' is missing or has invalid '{$attr}' attribute.\n";
                }
            }
        }

        return true;
    }

    /**
     * Update metadata of .fdb
     * @param array $newMetadata
     * @return bool
     */
    function updateMetadata(array $newMetadata): bool
    {
        if (isset($this->database_content['metadata'])) {
            foreach ($newMetadata as $key => $value) {
                $this->database_content['metadata'][$key] = $value;
            }
            return !empty(file_put_contents($this->database_file_name,$this->writeFDBFile()));
        }
        return false;
    }

    /**
     * Return keys of collections
     * @param $collectionName
     * @return false|mixed
     */
    function getCollectionKeys($collectionName): mixed
    {
        return $this->database_content['collection_list'][$collectionName]['keys'] ?? false;
    }

    /**
     * Returns types on collection.
     * @param $collectionName
     * @return mixed|true
     */
    function getCollectionTypes($collectionName): mixed
    {
        return $this->database_content['collection_list'][$collectionName]['type'] ?? true;
    }

    /**
     * Returns primary on collection
     * @param $collectionName
     * @return false|mixed
     */
    function getCollectionPrimary($collectionName): mixed
    {
        return $this->database_content['collection_list'][$collectionName]['primary'] ?? false;
    }

    /**
     * Returns unique on collection.
     * @param $collectionName
     * @return false|mixed
     */
    function getCollectionUnique($collectionName): mixed
    {
        return $this->database_content['collection_list'][$collectionName]['unique'] ?? false;
    }

    /**
     * Add new key on collection.
     * @param string $collectionName
     * @param string $newKey
     * @param string $keyType
     * @param bool $isPrimary
     * @param bool $isUnique
     * @return bool
     */
    function addCollectionKey(string $collectionName, string $newKey, string $keyType, bool $isPrimary = false, bool $isUnique = false): bool
    {
        // Check if a collection exists
        if (isset($this->database_content['collection_list'][$collectionName]['keys'][$newKey])) {
            return false;
        }

        // Add the new key and type to the 'keys' and 'type' lists
        $this->database_content['collection_list'][$collectionName]['keys'][] = $newKey;
        $this->database_content['collection_list'][$collectionName]['type'][] = $keyType;

        // Add the new key to 'primary' if it's a primary key
        if ($isPrimary) {
            $this->database_content['collection_list'][$collectionName]['primary'][] = $newKey;
        }

        // Add the new key to 'unique' if it's a unique key
        if ($isUnique) {
            $this->database_content['collection_list'][$collectionName]['unique'][] = $newKey;
        }
        return !empty(file_put_contents($this->database_file_name, $this->writeFDBFile()));
    }

    /**
     * Writing to collection file.
     * @param $collectionName
     * @param $data
     * @return bool
     */
    function writeCollectionFile($collectionName, $data): bool
    {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);

        $hash_line = $this->database_username.'|'.$this->database_password;

        // Encrypt
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $ciphertext = openssl_encrypt($jsonData, 'aes-256-cbc', $hash_line, 0, $iv);
        $encrypted = base64_encode($ciphertext . '::' . $iv);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        $writeSuccess = file_put_contents($this->collection_path.DIRECTORY_SEPARATOR.$collectionName.'.fdb.collection', $encrypted);

        if ($writeSuccess === false) {
            return false;
        }

        return true;
    }

    /**
     * Get Collection data.
     * @param $collectionName
     * @return false|mixed
     */
    function readCollectionFile($collectionName): mixed
    {
       // dd($this->getCollectionData($collectionName));
        if (!$this->getCollectionData($collectionName)) {
            return [];
        }
        if(!file_exists($this->collection_path.DIRECTORY_SEPARATOR.$collectionName.'.fdb.collection')) {
            touch($this->collection_path.DIRECTORY_SEPARATOR.$collectionName.'.fdb.collection');
        }
        $fileContent = file_get_contents($this->collection_path.DIRECTORY_SEPARATOR.$collectionName.'.fdb.collection');
        if(empty($fileContent)) {
            return [];
        }

        // Decrypt
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $key = $this->database_username.'|'.$this->database_password;
        list($encrypted_data, $iv) = explode('::', base64_decode($fileContent), 2);
        $decrypted = openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
        $parsedData = json_decode($decrypted, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $parsedData;
    }

    function getCollections(): array {
        return $this->database_content['collection_list'] ?? [];
    }

    /**
     * @param $collectionName
     * @param $keyToRemove
     * @return bool
     */
    function removeCollectionKey($collectionName, $keyToRemove): bool
    {
        // Check if the collection exists
        if (!isset($this->database_content['collection_list'][$collectionName])) {
            return false;
        }

        // Find the index of the key in the 'keys' array
        $keyIndex = array_search($keyToRemove, $this->database_content['collection_list'][$collectionName]['keys']);

        // If the key exists, remove it and its associated type
        if ($keyIndex !== false) {
            // Remove the key and type
            array_splice($this->database_content['collection_list'][$collectionName]['keys'], $keyIndex, 1);
            array_splice($this->database_content['collection_list'][$collectionName]['type'], $keyIndex, 1);

            // Remove the key from 'primary' if it exists
            if (($primaryIndex = array_search($keyToRemove, $this->database_content['collection_list'][$collectionName]['primary'])) !== false) {
                array_splice($this->database_content['collection_list'][$collectionName]['primary'], $primaryIndex, 1);
            }

            // Remove the key from 'unique' if it exists
            if (($uniqueIndex = array_search($keyToRemove, $this->database_content['collection_list'][$collectionName]['unique'])) !== false) {
                array_splice($this->database_content['collection_list'][$collectionName]['unique'], $uniqueIndex, 1);
            }
            return !empty(file_put_contents($this->database_file_name, $this->writeFDBFile()));
        } else {
            return false;
        }
    }

}