<?php

namespace App\Http\Handlers;

use App\Contracts\Interface\ActivityLogInterface;
use App\Contracts\Interface\AuthInterface;
use App\Enums\UserRole;
use App\Helpers\UploadHelper;
use App\Http\Resources\DefaultResource;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserHandler
{
    protected $authInterface;

    public function __construct(AuthInterface $authInterface)
    {
        $this->authInterface = $authInterface;
    }

    public function login(array $data)
    {
        $user = $this->authInterface->login([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah'],
            ]);
        }

        return $user;
    }

    public function register(array $data): User
    {
        $user = $this->createUser($data);
        $this->assignRole($user, UserRole::USER);

        return $user;
    }

    private function createUser(array $data): User
    {
        return $this->authInterface->register([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    private function assignRole(User $user, UserRole|string $role): void
    {
        $roleValue = $role instanceof UserRole
            ? $role->value
            : $role;

        $user->syncRoles([$roleValue]);
    }

    public function updateForgotPassword(array $data)
    {
        $result = $this->authInterface->updateForgotPassword($data);
        $user = $result['user'];
        $token = $result['token'];

        if (! $user) {
            throw new \Exception('Email tidak ditemukan');
        }

        if (! $token) {
            throw new \Exception('Token tidak valid atau sudah digunakan');
        }

        DB::beginTransaction();
        try {
            $user->password = Hash::make($data['password']);
            $user->save();

            PasswordResetToken::where('email', $data['email'])
                ->where('token', $data['token'])
                ->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Terjadi kesalahan, silakan coba lagi');
        }
    }

    public function updatePassword(array $data)
    {
        $user = auth()->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('Password lama salah.');
        }

        if ($data['new_password'] !== $data['new_password_confirmation']) {
            throw new \Exception('Konfirmasi password baru tidak sama.');
        }

        DB::beginTransaction();
        try {
            $user->password = Hash::make($data['new_password']);
            $user->save();

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new \Exception('Terjadi kesalahan, silakan coba lagi');
        }
    }

    public function storeCustomer(array $data)
    {
        DB::beginTransaction();
        try {
            if (isset($data['image']) && $data['image']) {
                $imagePath = UploadHelper::uploadImage($data['image'], 'profile_images');
                $data['image'] = $imagePath;
            }

            $user = $this->authInterface->store($data);
            
            // ✅ TAMBAHKAN: Assign role berdasarkan input atau default ke 'user'
            if (isset($data['role'])) {
                $this->assignRole($user, $data['role']);
            } else {
                $this->assignRole($user, UserRole::USER);
            }

            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateCustomer(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $user = $this->authInterface->findById($id);

            if (isset($data['image']) && $data['image']) {
                if ($user->image) {
                    UploadHelper::deleteFile($user->image);
                }
                $imagePath = UploadHelper::uploadImage($data['image'], 'profile_images');
                $data['image'] = $imagePath;
            }

            $updatedUser = $this->authInterface->update($id, $data);


            DB::commit();
            return $updatedUser;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }



}
