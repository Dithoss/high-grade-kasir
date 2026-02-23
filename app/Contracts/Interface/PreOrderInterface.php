<?php

namespace App\Contracts\Interface;

interface PreorderInterface
{
    public function getByUser(string $userId, array $filters = []);
    public function getByBook(string $bookId, array $filters = []);
    public function findById(string $id);
    public function findActiveByUserAndBook(string $userId, string $bookId);
    public function create(array $data);
    public function update(string $id, array $data);
    public function cancel(string $id);
    public function getQueuePosition(string $bookId): int;
    public function notifyReady(string $bookId): void;
    public function getAll(array $filters = []);
}