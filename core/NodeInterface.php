<?php

namespace Mini\Cms;



interface NodeInterface
{
    public function id();

    public function type();

    public function getValues();

    public function set(string $key, mixed $value);

    public function get(string $key);

    public function getTitle();

    public function setTitle(string $title);

    public function save();

    public function delete();

    public function isDeleted();

    public function update();

    public function isUpdated();

    public function find(int $node_id);

}