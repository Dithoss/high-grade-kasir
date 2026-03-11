<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Facades\Auth;
use App\Contracts\Interface\AuthInterface;
use App\Helpers\QueryFilterHelper;
use App\Http\Resources\DefaultResource;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AuthRepository implements AuthInterface
{
    protected User $model;
    public function __construct(User $model)
    {
        $this->model = $model;
    }
    public function login(array $data)
    {
        if (!Auth::guard('web')->attempt($data)) {
            return null;
        }

        request()->session()->regenerate();

        return Auth::guard('web')->user();
    }
    public function register(array $data):User
    {
        return User::create($data);
    }

    public function logout()
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();    
    }

    public function forgotPassword(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function updateForgotPassword(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        $token = PasswordResetToken::where('email', $data['email'])
            ->where('token', $data['token'])
            ->first();

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
    public function paginate(int $pagination = 10): LengthAwarePaginator
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($pagination);
    }
    public function findById(mixed $id): ?User
    {
        return $this->model->where('id', $id)->firstOrFail();
    }
    public function store(array $data): User
    {
        $data['plainPassword'] = $data['password'];
        return $this->model->create($data);
    }

    public function update(mixed $id, array $data): User
    {
        $User = $this->findById($id);
        if (isset($data['password'])) {
            $data['plain_password'] = $data['password'];
        }

        $User->update($data);
        return $User->fresh();
    }

    public function delete(mixed $id): bool
    {
        $User = $this->findById($id);
        return $User->delete();
    }

    public function forceDelete(mixed $id): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->forceDelete();
    }

    public function restore(mixed $id): bool
    {
        return $this->model->onlyTrashed()->findOrFail($id)->restore();
    }

    public function trash(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->onlyTrashed();
        $searchColumns = ['name'];

        QueryFilterHelper::applyFilters($query, $filters, $searchColumns);
        QueryFilterHelper::applySorting($query, $filters, 'deleted_at', 'desc');

        $perPage = (int) Arr::get($filters, 'per_page', 15);

        return $query->paginate($perPage);
    }
    public function searchTrashed(string $keyword, int $perPage = 10)
    {
        return $this->model->onlyTrashed()
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('roles');

        $searchColumns = ['name', 'email'];
        QueryFilterHelper::applyFilters($query, $filters, $searchColumns);
        QueryFilterHelper::applySorting($query, $filters, 'created_at', 'desc');

        $perPage = (int) Arr::get($filters, 'per_page', 10);

        return $query->paginate($perPage);
    }
}