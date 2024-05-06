<?php

namespace Mini\Cms;

use Mini\Cms\StorageManager\Connector;

interface ReferenceInterface
{
    public function getId();

    public function load(string $vid);

    public function getTerm(): string;

    public function getTerms(): array;

    public function setTerm(string $term): void;

    public function setTerms(array $terms): void;

    public function save(): array;


    public function connector(Connector $connector): void;

    public function setVid(int $vid): void;

}