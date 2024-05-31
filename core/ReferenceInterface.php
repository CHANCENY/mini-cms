<?php

namespace Mini\Cms;

interface ReferenceInterface
{
    public function getId();

    public function load(int $term): ReferenceInterface;

    public function getTerm(): string;

    public function getTerms(): array;

    public function setTerm(string $term): void;

    public function setTerms(array $terms): void;

    public function save(): array;

    public function setVid(int $vid): void;

    public static  function loads(string $vid): array;

}