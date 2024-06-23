<?php

namespace Mini\Cms\Modules\Modal;

class RecordCollections
{
    /**
     * Records collection object.
     * @param array $records
     */
    public function __construct(private array $records)
    {
        foreach ($this->records as $key=>&$record) {
            $record = new RecordCollection($record);
        }
    }

    /**
     * Get all collected records.
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * Getting record.
     * @param int $index
     * @return RecordCollection|null
     */
    public function getAt(int $index): RecordCollection|null
    {
        return $this->records[$index] ?? null;
    }

    /**
     * Json data of records.
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->records);
    }
}