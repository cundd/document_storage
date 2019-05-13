<?php
declare(strict_types=1);

namespace Cundd\DocumentStorage\Tests\Fixture;

interface RepositoryInterface
{
    public function add($object);

    public function remove($object);

    public function update($modifiedObject);

    public function findAll();

    public function countAll();

    public function removeAll();

    public function findByUid($uid);

    public function findByIdentifier($identifier);

    public function setDefaultOrderings(array $defaultOrderings);

    // public function setDefaultQuerySettings($defaultQuerySettings);

    public function createQuery();
}
