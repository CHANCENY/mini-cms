<?php

namespace Mini\Cms\Modules\FileSystem;


use Mini\Cms\Connections\Database\Database;
use Mini\Cms\Modules\Streams\MiniWrapper;
use Mini\Cms\StorageManager\Connector;

class File
{
    private Connector $connector;
    /**
     * @var array|mixed
     */
    private mixed $file_data;

    private FileImageStyles $styles;

    public function __construct()
    {
        $this->styles = new FileImageStyles();
        $this->connector = new Connector(external_connection: Database::database());
        (new FileSystem())->prepareUpload([]);
    }

    /**
     * Setting connection.
     * @param Connector $connector
     * @return void
     */
    public function connector(Connector $connector): void
    {
        $this->connector = $connector;
    }

    /**
     * Loading file from database.
     * @param int $fid
     * @return $this|null
     */
    public function file(int $fid): File|null
    {
        $query = "SELECT * FROM file_managed WHERE fid = :id";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->bindValue(':id', $fid);
        $statement->execute();
        $this->file_data = $statement->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [];
        return !empty($this->file_data) ? $this : null;
    }

    /**
     * Loading file.
     * @param int $fid
     * @return File|null
     */
    public static function load(int $fid): ?File
    {
        return (new File())->file($fid);
    }

    public function fetType()
    {
        return $this->file_data['type'] ?? null;
    }

    public function getSize(): int
    {
        return $this->file_data['size'] ?? 0;
    }

    public function getHeight(): int
    {
        return $this->file_data['height'] ?? 0;
    }

    public function getWidth(): int
    {
        return $this->file_data['width'] ?? 0;
    }

    public function uploadeOn()
    {
        return $this->file_data['uploaded_on'] ?? null;
    }

    public function getAltText(): ?string
    {
        return $this->file_data['alt'] ?? null;
    }

    public function getName(): string
    {
        return $this->file_data['file_name'] ?? '';
    }
    public function getUri(): string
    {
        return $this->file_data['uri'];
    }

    public function resolveStyleUri(): string
    {
        $resolved = $this->getUri();
        if(!empty($this->styles->style())) {
            $style_key = $this->styles->style();
            $file_dir = trim(substr($resolved,strpos($resolved,'://'), strlen($resolved)),'://');
            $path = str_starts_with($resolved, 'public://') ? 'public://'.$style_key .'/'.$file_dir :
                'private://'.$style_key.'/'.$file_dir;
            if(file_exists($path)) {
                $resolved = $path;
            }
        }
        return $resolved;
    }

    public function getFilePath(bool $style_resolved = false, string|null $style_name = null): string
    {
        if($style_name) {
            $this->styles->switchStyle($style_name);
        }
        $file = new MiniWrapper();
        $path = $style_resolved ?$this->resolveStyleUri() : $this->getUri();
        return $file->getRealPath($path);
    }

    public function delete(): bool
    {
        $query = Database::database()->prepare("DELETE FROM file_managed WHERE fid = :id");
        return $query->execute(['id'=>$this->file_data['fid']]);
    }

    public function getRenderHtmlFileField(string $field_name): string
    {
        return <<<FILE
<div class="col px-5 ps-0 mt-1 mb-1">
   <a href="/{$this->getFilePath()}" target="_blank">{$this->getName()}</a>
   <span class="remove float-end text-danger" field="$field_name" title="remove" aria-label="remove" data="{$this->file_data['fid']}" style="cursor: pointer;">x</span>
</div>
FILE;

    }
}