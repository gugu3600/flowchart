<?php

namespace App\Repositories\user;

interface UserRepositoryInterface
{
    public function find(int $id);
    public function findByEmail(string $email);
    public function create(array $data);
}
