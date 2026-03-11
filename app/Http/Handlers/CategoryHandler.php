<?php

namespace App\Http\Handlers;

use App\Contracts\Interface\CategoryInterface;
use App\Helpers\UploadHelper;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryHandler
{
    public function __construct(
        protected CategoryInterface $repo,
    ) {}

    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {

            if (!empty($data['image'])) {
                $data['image'] = UploadHelper::uploadImage($data['image'], 'category');
            }

            return $this->repo->store($data);
        });
    }

    public function update(string $id, array $data): Category
    {
        DB::beginTransaction();

        try {
            $category = $this->repo->findById($id);

            if (isset($data['image']) && $data['image']) {

                if ($category->image) {
                    UploadHelper::deleteFile($category->image);
                }

                $data['image'] = UploadHelper::uploadImage($data['image'], 'category');
            }

            $updated = $this->repo->update($id, $data);

            DB::commit();
            return $updated;

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function massDelete(array $ids): int
    {
        $categories = \App\Models\Category::whereIn('id', $ids)->get();

        foreach ($categories as $cat) {
            if ($cat->image) {
                Storage::disk('public')->delete($cat->image);
            }
        }

        \App\Models\Category::whereIn('id', $ids)->delete();

        return count($ids);
    }
}