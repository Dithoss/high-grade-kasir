<?php

namespace App\Contracts\Interface;

use App\Models\LibraryCard;
use Illuminate\Pagination\LengthAwarePaginator;

interface LibraryCardInterface
{
    public function findByUserId(string $userId): ?LibraryCard;
    public function findById(string $id): ?LibraryCard;
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function create(array $data): LibraryCard;
    public function update(string $id, array $data): LibraryCard;
    public function updateStatus(string $id, string $status): LibraryCard;
    public function regenerateCard(string $id): LibraryCard;
}