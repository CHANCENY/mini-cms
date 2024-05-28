<?php

namespace Mini\Cms;

use Mini\Cms\Connections\Database\Database;

class Vocabulary implements VocabularyInterface
{

    private array $vocabulary = [];

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
        return $this->vocabulary['#values']['vocabulary_label'] ?? $this->vocabulary['#values']['label'];
    }

    public function setVocabulary($vocabulary): void
    {
        $this->vocabulary['#values']['vocabulary_label'] = $vocabulary;
        $this->vocabulary['#values']['vocabulary_name'] = strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $vocabulary));
    }


    public function load(string $vocabulary): null|Vocabulary
    {
       $query = "SELECT * FROM `vocabularies` WHERE `vocabulary_name` = :vocabulary";
       $statement = Database::database()->prepare($query);
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
    
    public static function vocabularies(): false|array
    {
        $query = "SELECT * FROM `vocabularies`"; 
        $query = Database::database()->prepare($query);
        $query->execute();
        $voc = $query->fetchAll();
        foreach ($voc as &$value) {
            $value = Vocabulary::vocabulary($value['vocabulary_name']);
        }
        return $voc;
    }


    public function save()
    {
        $query = "SELECT * FROM `vocabularies` WHERE `vocabulary_name` = :vocabulary";
        $statement = Database::database()->prepare($query);
        $statement->bindValue(':vocabulary', $this->vocabulary['#values']['name'] ?? $this->vocabulary['#values']['vocabulary_name']);
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if(empty($data)) {
            $query = "INSERT INTO vocabularies (vocabulary_name, vocabulary_label) VALUES (:vocabulary_name, :label)";
            $statement = Database::database()->prepare($query);

            $statement->bindValue(':vocabulary_name', $this->vocabulary['#values']['name'] ?? $this->vocabulary['#values']['vocabulary_name']);
            $statement->bindValue(':label', $this->vocabulary['#values']['label'] ?? $this->vocabulary['#values']['vocabulary_label']);
            return $statement->execute();
        }
        return false;
    }


    public static function create(string $name): bool
    {
        $vocabulary = new Vocabulary();
        $vocabulary->setVocabulary($name);
        return $vocabulary->save();
    }

    public static function vocabulary(string $name): Vocabulary|null
    {
        $vocabulary = new Vocabulary();
        return $vocabulary->load($name);
    }

    public function delete(): bool
    {
        $query = Database::database()->prepare("DELETE FROM vocabularies WHERE vocabulary_name = :vid");
        $query->bindValue(':vid', $this->getVocabulary());
        return $query->execute();
    }

    public function update(string $label): bool
    {
        $query = Database::database()->prepare("UPDATE vocabularies SET vocabulary_label = :label WHERE vocabulary_name = :vid");
        return $query->execute(['label'=>$label, 'vid'=>$this->getVocabulary()]);
    }
}