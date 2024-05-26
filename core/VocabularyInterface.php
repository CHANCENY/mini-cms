<?php

namespace Mini\Cms;

use Mini\Cms\StorageManager\Connector;

interface VocabularyInterface
{
    public function getVocabulary();

    public function setVocabulary($vocabulary);


    public function load(string $vocabulary);


    public function save();


    public static function create(string $vocabulary);

}