<?php

namespace Mini\Cms;

use Mini\Cms\StorageManager\Connector;

class Vocabulary implements VocabularyInterface
{

    private array $vocabulary = [];

    private Connector $connector;

    public function getVocabulary()
    {
       return $this->vocabulary['#values']['vocabulary_name'] ?? $this->vocabulary['#values']['name'];
    }

    public function vid()
    {
        return $this->vocabulary['#values']['vid'];
    }

    public function getLabelName()
    {
        return $this->vocabulary['#values']['vocabulary_label'] ?? $this->vocabulary['#values']['name'];
    }

    public function setVocabulary($vocabulary): void
    {
        $this->vocabulary['#values']['vocabulary_label'] = $vocabulary;
        $this->vocabulary['#values']['vocabulary_name'] = strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $vocabulary));
    }


    public function load(string $vocabulary): null|Vocabulary
    {
       $query = "SELECT * FROM `vocabularies` WHERE `vocabulary_name` = :vocabulary";
       $statement = $this->connector->getConnection()->prepare($query);
       $statement->execute(['vocabulary' => $vocabulary]);
       $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
       if(empty($data)) {
           return null;
       }
       foreach ($data as $vocabulary) {
           $this->vocabulary['#values']['name'] = $vocabulary['vocabulary_name'];
           $this->vocabulary['#values']['vid'] = $vocabulary['vid'];
           $this->vocabulary['#values']['label'] = $vocabulary['vocabulary_label'];
       }
       return $this;
    }


    public function save()
    {
        $query = "SELECT * FROM `vocabularies` WHERE `vocabulary_name` = :vocabulary";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->bindValue(':vocabulary', $this->vocabulary['#values']['name'] ?? $this->vocabulary['#values']['vocabulary_name']);
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if(empty($data)) {
            $query = "INSERT INTO vocabularies (vocabulary_name, vocabulary_label) VALUES (:vocabulary_name, :label)";
            $statement = $this->connector->getConnection()->prepare($query);

            $statement->bindValue(':vocabulary_name', $this->vocabulary['#values']['name'] ?? $this->vocabulary['#values']['vocabulary_name']);
            $statement->bindValue(':label', $this->vocabulary['#values']['label'] ?? $this->vocabulary['#values']['vocabulary_label']);
            return $statement->execute();
        }
        return false;
    }


    public static function create(string $name, mixed $connector = null): bool
    {
        $vocabulary = new Vocabulary();
        $vocabulary->connector($connector);
        $vocabulary->setVocabulary($name);
        return $vocabulary->save();
    }


    public function connector(Connector $connector): void
    {
        $this->connector = $connector;
    }

    public static function vocabulary(string $name, mixed $connector): Vocabulary|null
    {
        $vocabulary = new Vocabulary();
        $vocabulary->connector($connector);
        return $vocabulary->load($name);
    }
}