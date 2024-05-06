<?php

namespace Mini\Cms\Entities;

use Chance\Entity\ReferenceInterface;
use Chance\Entity\StorageManager\Connector;
use Chance\Entity\VocabularyNotFoundException;

class Term implements ReferenceInterface
{
    private $termData;
    private Connector $connector;

    public function getId()
    {
        return $this->termData['term_id'];
    }

    public function load(string $vid)
    {
        // TODO: Implement load() method.
    }

    public function getTerm(): string
    {
        return $this->termData['term_name'] ?? '';
    }

    public function getTerms(): array
    {
        return $this->termData;
    }

    public function setTerm(string $term): void
    {
        $this->termData['#values']['term'][] = $term;
    }

    public function setTerms(array $terms): void
    {
        foreach ($terms as $term) {
            $this->setTerm($term);
        }
    }

    public function save(): array
    {
        $query = "SELECT * FROM `vocabularies` WHERE `vid` = :vid";
        $statement = $this->connector->getConnection()->prepare($query);
        $statement->bindValue(':vid', $this->termData['#values']['vid']);
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC)[0]['vocabulary_name'] ?? null;
        $term_id = [];
        if(!empty($data)) {
            $terms = $this->termData['#values']['term'] ?? [];
            foreach ($terms as $term) {
                $vid = $this->termData['#values']['vid'];

                $query = "SELECT * FROM terms WHERE vocabulary_id = :vid AND term_name = :term";
                $statement = $this->connector->getConnection()->prepare($query);
                $statement->bindValue(':vid', $vid);
                $statement->bindValue(':term', $term);
                $statement->execute();
                $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
                if(empty($data)) {
                    $query = "INSERT INTO terms (vocabulary_id, term_name) VALUES (:vid, :term)";
                    $statement = $this->connector->getConnection()->prepare($query);
                    $statement->bindValue(':vid', $vid);
                    $statement->bindValue(':term', $term);
                    $statement->execute();
                    $term_id[] = $this->connector->getConnection()->lastInsertId();
                }else {
                    $term_id[] = $data[0]['term_id'];
                }
            }
        }else {
            throw new VocabularyNotFoundException('Vocabulary not found');
        }
        return $term_id;
    }


    public function connector(Connector $connector): void
    {
        $this->connector = $connector;
    }

    public function setVid(int $vid): void
    {
        $this->termData['#values']['vid'] = $vid;
    }
}