<?php

namespace Mini\Cms\Modules\FileSystem;


use Mini\Cms\ConnectorInterface;
use Mini\Cms\StorageManager\Connector;

class File implements ConnectorInterface
{
    private Connector $connector;
    /**
     * @var array|mixed
     */
    private mixed $file_data;

    /**
     * @var
     */
    private $style;

    public function __construct()
    {
        $styles = new FileImageStyles();
        $this->style = $styles->getDefaultStyle();
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


}