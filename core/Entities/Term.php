<?php

namespace Mini\Cms\Entities;

use Mini\Cms\Connections\Database\Database;
use Mini\Cms\ReferenceInterface;
use Mini\Cms\Vocabulary;
use Mini\Cms\VocabularyNotFoundException;

class Term implements ReferenceInterface
{
    private array $termData;

    public function getId()
    {
        return $this->termData['term_id'];
    }

    public function load(int $term): static
    {
        $query = Database::database()->prepare("SELECT * FROM terms WHERE term_id = :id");
        $query->execute(['id' => $term]);
        $this->termData = $query->fetch();
        return $this;
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

    public static function term(int $termId): static
    {
       return (new static())->load($termId);
    }

    public function save(): array
    {
        $query = "SELECT * FROM `vocabularies` WHERE `vid` = :vid";
        $statement =  Database::database()->prepare($query);
        $statement->bindValue(':vid', $this->termData['#values']['vid']);
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC)[0]['vocabulary_name'] ?? null;
        $term_id = [];
        if(!empty($data)) {
            $terms = $this->termData['#values']['term'] ?? [];
            foreach ($terms as $term) {
                $vid = $this->termData['#values']['vid'];

                $query = "SELECT * FROM terms WHERE vocabulary_id = :vid AND term_name = :term";
                $statement =  Database::database()->prepare($query);
                $statement->bindValue(':vid', $vid);
                $statement->bindValue(':term', $term);
                $statement->execute();
                $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
                if(empty($data)) {
                    $query = "INSERT INTO terms (vocabulary_id, term_name) VALUES (:vid, :term)";
                    $statement =  Database::database()->prepare($query);
                    $statement->bindValue(':vid', $vid);
                    $statement->bindValue(':term', $term);
                    $statement->execute();
                    $term_id[] =  Database::database()->lastInsertId();
                }else {
                    $term_id[] = $data[0]['term_id'];
                }
            }
        }else {
            throw new VocabularyNotFoundException('Vocabulary not found');
        }
        return $term_id;
    }

    public function setVid(string|int $vid): void
    {
        if(is_string($vid)) {
            $vocabulary = Vocabulary::vocabulary($vid);
            $vid = $vocabulary->vid();
        }
        $this->termData['#values']['vid'] = $vid;
    }

    public static function loads(string $vid): array
    {
        $vocabulary = Vocabulary::vocabulary($vid);
        $vid = $vocabulary->vid();

        $query = "SELECT term_id FROM terms WHERE vocabulary_id = :vid";
        $statement =  Database::database()->prepare($query);
        $statement->bindValue(':vid', $vid);
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if(!empty($data)) {
           foreach ($data as &$term) {
               $term = (new Term())->load($term['term_id']);
           }
        }
        return $data;
    }

    public function update(): bool
    {
        $query = "UPDATE terms SET term_name = :term_name WHERE term_id = :term_id";
        $statement =  Database::database()->prepare($query);
        $statement->bindValue(':term_name', $this->termData['#values']['term'][0]);
        $statement->bindValue(':term_id', $this->getId());
        return $statement->execute();
    }

    public function delete(): bool
    {
        $query = "DELETE FROM terms WHERE term_id = :term_id";
        $statement =  Database::database()->prepare($query);
        $statement->bindValue(':term_id', $this->getId());
        return $statement->execute();
    }

    public static function find(string $termId): array
    {
        $query = "SELECT nid FROM term_nodes WHERE tid = :term_id";
        $statement =  Database::database()->prepare($query);
        $statement->bindValue(':term_id', $termId);
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if(!empty($data)) {
            foreach ($data as &$node) {
                $node = Node::load($node['nid']);
            }
        }
        return $data;
    }
}