<?php

namespace App\Http\Controllers;

use App\Contracts\Interface\AuthInterface;
use App\Http\Handlers\UserHandler;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\UpdateForgotPasswordRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected UserHandler $authHandler;
    protected AuthInterface $authInterface;
    protected UserService $authService;

    public function __construct(
        UserHandler $authHandler,
        AuthInterface $authInterface,
        UserService $authService
    ) {
        $this->authHandler = $authHandler;
        $this->authInterface = $authInterface;
        $this->authService = $authService;
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $this->authHandler->register($request->validated());

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil, silakan login');
    }
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authHandler->login($request->validated());

        if (! $user) {
            return back()->withErrors(['email' => 'Email atau password salah']);
        }

        $user->load('roles');

        if ($user->hasRole('admin')) {
            return redirect()->route('dashboard');
        }else{
            return redirect()->route('users.dashboard');
        }
    }


    public function logout()
    {
        try {
            $this->authInterface->logout();

            return redirect()
                ->route('login')
                ->with('success', __('auth.logout_success'));
        } catch (\Throwable $e) {
            return back()->withErrors(__('auth.logout_error'));
        }
    }


    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $this->authService->forgotPassword($request->validated()['email']);

            return back()->with('success', __('auth.forgot_password_success'));
        } catch (\Throwable $e) {
            return back()->withErrors(__('auth.forgot_password_error'));
        }
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', compact('token'));
    }

    public function updateForgotPassword(UpdateForgotPasswordRequest $request)
    {
        try {
            $this->authHandler->updateForgotPassword($request->validated());

            return redirect()
                ->route('login')
                ->with('success', __('auth.update_password_success'));
        } catch (\Throwable $e) {
            return back()->withErrors(__('auth.update_password_error'));
        }
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $this->authHandler->updatePassword($request->validated());

            return back()->with('success', __('auth.update_password_success'));
        } catch (\Throwable $e) {
            return back()->withErrors(__('auth.update_password_error'));
        }
    }


    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'date_from',
            'date_to',
            'per_page'
        ]);

        $users = $this->authInterface->getAll($filters);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }
    public function dashboard()
    {
        return view('dashboard');
    }
    public function userDashboard()
    {
        return view('users.dashboard');
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->authHandler->storeCustomer($request->validated());

            return redirect()
                ->route('users.index')
                ->with('success', __('alert.add_success'));
        } catch (\Throwable $e) {
            return back()
                ->withErrors('Gagal menambahkan user: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function edit(string $id)
    {
        try {
            $user = $this->authInterface->findById($id);

            return view('users.edit', compact('user'));
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }


    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $this->authHandler->updateCustomer($id, $request->validated());

            $user = Auth::user();

            $redirectRoute = $user->hasRole('admin')
                ? 'dashboard'
                : 'users.dashboard';

            return redirect()
                ->route($redirectRoute)
                ->with('success', __('alert.update_success'));

        } catch (ModelNotFoundException $e) {
            abort(404);
        } catch (\Throwable $e) {
            return back()->withErrors(__('alert.update_failed'))->withInput();
        }
    }


    public function destroy(string $id)
    {
        try {
            $this->authInterface->delete($id);

            return back()->with('success', __('alert.delete_success'));
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function trash(Request $request)
    {
        $filters = $request->only([
            'search',
            'per_page',
            'date_from',
            'date_to'
        ]);

        $users = $this->authInterface->trash($filters);

        return view('users.trash', compact('users'));
    }

    public function restore(string $id)
    {
        try {
            $this->authInterface->restore($id);

            return back()->with('success', __('alert.user_restore_success'));
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            $this->authInterface->forceDelete($id);

            return back()->with('success', __('alert.delete_success'));
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }
    
}
