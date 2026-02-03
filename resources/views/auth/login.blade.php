<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Poppins', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .input-field {
            transition: all 0.3s ease;
        }

        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .illustration {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .card-shadow {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .icon-wrapper {
            transition: all 0.3s ease;
        }

        .input-group:focus-within .icon-wrapper {
            color: #667eea;
            transform: scale(1.1);
        }

        .password-toggle {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .animate-shake {
            animation: shake 0.3s ease-out;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        .checkbox-custom {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .checkbox-custom:checked {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        .checkbox-custom:checked::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-pink-50 flex items-center justify-center p-4">

    <div class="w-full max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-8 items-center">
            
            <!-- Left Side - Illustration & Branding -->
            <div class="hidden lg:flex flex-col items-center justify-center p-12 gradient-bg rounded-3xl card-shadow">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-6 shadow-lg">
                        <i class="fas fa-book-open text-white text-4xl"></i>
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-3">Selamat Datang Kembali</h1>
                    <p class="text-white/90 text-lg">Masuk ke Perpustakaan Digital Anda</p>
                </div>

                <!-- Illustration -->
                <div class="illustration w-full max-w-md">
                    <svg viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Phone mockup -->
                        <rect x="150" y="50" width="200" height="300" rx="20" fill="white" opacity="0.9"/>
                        <rect x="150" y="50" width="200" height="300" rx="20" stroke="white" stroke-width="2"/>
                        
                        <!-- Notch -->
                        <rect x="210" y="55" width="80" height="8" rx="4" fill="#667eea" opacity="0.3"/>
                        
                        <!-- Screen content -->
                        <circle cx="250" cy="130" r="35" fill="#667eea" opacity="0.2"/>
                        <circle cx="250" cy="130" r="28" fill="#667eea"/>
                        <path d="M240 130 Q250 120 260 130 L250 145 Z" fill="white"/>
                        
                        <!-- LOGIN text -->
                        <text x="250" y="175" text-anchor="middle" fill="#667eea" font-size="16" font-weight="bold" font-family="Poppins">LOGIN</text>
                        
                        <!-- Email input -->
                        <rect x="170" y="190" width="160" height="35" rx="8" fill="#E0E7FF"/>
                        <text x="180" y="210" fill="#667eea" font-size="11" font-family="Poppins">Email</text>
                        <text x="180" y="225" fill="#94a3b8" font-size="9" font-family="Poppins">user@email.com</text>
                        
                        <!-- Password input -->
                        <rect x="170" y="235" width="160" height="35" rx="8" fill="#E0E7FF"/>
                        <text x="180" y="255" fill="#667eea" font-size="11" font-family="Poppins">Password</text>
                        <circle cx="185" cy="265" r="2" fill="#94a3b8"/>
                        <circle cx="192" cy="265" r="2" fill="#94a3b8"/>
                        <circle cx="199" cy="265" r="2" fill="#94a3b8"/>
                        <circle cx="206" cy="265" r="2" fill="#94a3b8"/>
                        
                        <!-- Login Button -->
                        <rect x="170" y="285" width="160" height="35" rx="17.5" fill="#667eea"/>
                        <text x="250" y="308" text-anchor="middle" fill="white" font-size="12" font-weight="600" font-family="Poppins">LOGIN</text>
                        
                        <!-- Decorative elements -->
                        <circle cx="80" cy="100" r="8" fill="white" opacity="0.3"/>
                        <circle cx="420" cy="150" r="12" fill="white" opacity="0.3"/>
                        <circle cx="400" cy="300" r="6" fill="white" opacity="0.3"/>
                        <circle cx="90" cy="320" r="10" fill="white" opacity="0.2"/>
                        
                        <!-- Person with phone -->
                        <ellipse cx="100" cy="340" rx="60" ry="15" fill="white" opacity="0.3"/>
                        <rect x="75" y="250" width="50" height="90" rx="25" fill="white" opacity="0.9"/>
                        <circle cx="100" cy="240" r="25" fill="white" opacity="0.9"/>
                        <circle cx="100" cy="235" r="18" fill="#667eea"/>
                        
                        <!-- Happy face -->
                        <circle cx="93" cy="232" r="3" fill="white"/>
                        <circle cx="107" cy="232" r="3" fill="white"/>
                        <path d="M90 242 Q100 248 110 242" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"/>
                        
                        <!-- Phone in hand -->
                        <rect x="85" y="280" width="30" height="45" rx="5" fill="#667eea"/>
                        <rect x="88" y="283" width="24" height="36" rx="2" fill="white"/>
                        
                        <!-- Plant decoration -->
                        <rect x="360" y="310" width="30" height="40" rx="15" fill="white" opacity="0.8"/>
                        <ellipse cx="375" cy="290" rx="15" ry="20" fill="#10b981" opacity="0.6"/>
                        <ellipse cx="370" cy="285" rx="10" ry="15" fill="#10b981" opacity="0.7"/>
                        <ellipse cx="380" cy="285" rx="10" ry="15" fill="#10b981" opacity="0.7"/>
                    </svg>
                </div>

                <div class="mt-8 text-center space-y-2">
                    <div class="flex items-center justify-center gap-2 text-white/80">
                        <i class="fas fa-check-circle"></i>
                        <p class="text-sm">Akses ribuan buku digital</p>
                    </div>
                    <div class="flex items-center justify-center gap-2 text-white/80">
                        <i class="fas fa-check-circle"></i>
                        <p class="text-sm">Sistem peminjaman mudah & cepat</p>
                    </div>
                    <div class="flex items-center justify-center gap-2 text-white/80">
                        <i class="fas fa-check-circle"></i>
                        <p class="text-sm">Notifikasi & pengingat otomatis</p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="bg-white rounded-3xl card-shadow p-8 lg:p-12">
                
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 gradient-bg rounded-2xl mb-4 shadow-lg">
                        <i class="fas fa-book-open text-white text-2xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800">Library System</h2>
                    <p class="text-gray-500 mt-2">Masuk ke akun Anda</p>
                </div>

                <!-- Logo Header for Desktop -->
                <div class="hidden lg:block mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-book-open text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">Library System</h3>
                            <p class="text-sm text-gray-500">Perpustakaan Digital</p>
                        </div>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-800 mb-2">Masuk ke akun Anda</h4>
                    <p class="text-gray-600">Selamat datang kembali! Silakan masukkan detail Anda.</p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-xl p-4 animate-shake">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-red-800 mb-2">Terjadi kesalahan:</h3>
                                <ul class="text-sm text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li class="flex items-start">
                                            <span class="mr-2">•</span>
                                            <span>{{ $error }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Success Message -->
                @if (session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-xl p-4 animate-fade-in">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <p class="text-green-800 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Field -->
                    <div class="input-group">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none icon-wrapper">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input 
                                type="email" 
                                id="email"
                                name="email" 
                                placeholder="nama@email.com"
                                value="{{ old('email') }}"
                                class="input-field w-full pl-11 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all @error('email') border-red-500 @enderror"
                                required
                                autofocus
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="input-group">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none icon-wrapper">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input 
                                type="password" 
                                id="password"
                                name="password" 
                                placeholder="••••••••"
                                class="input-field w-full pl-11 pr-12 py-3.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all @error('password') border-red-500 @enderror"
                                required
                            >
                            <button 
                                type="button"
                                onclick="togglePassword()"
                                class="password-toggle absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400"
                            >
                                <i id="eye-icon" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input 
                                type="checkbox" 
                                name="remember"
                                class="checkbox-custom"
                            >
                            <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors select-none">Ingat saya</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-purple-600 hover:text-purple-700 hover:underline transition-colors">
                            Lupa password?
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button 
                        type="submit" 
                        class="btn-primary w-full py-4 text-white font-semibold rounded-xl shadow-lg flex items-center justify-center gap-2 text-lg"
                    >
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk</span>
                    </button>

                    <!-- Social Login Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500">atau masuk dengan</span>
                        </div>
                    </div>

                    <!-- Google Login Button -->
                    <button 
                        type="button"
                        class="w-full py-3.5 border-2 border-gray-200 rounded-xl font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all flex items-center justify-center gap-3"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Google</span>
                    </button>

                    <!-- Register Link -->
                    <div class="text-center pt-6 border-t border-gray-100 mt-6">
                        <p class="text-gray-600">
                            Belum punya akun? 
                            <a href="{{ route('register') }}" class="font-semibold text-purple-600 hover:text-purple-700 hover:underline transition-colors">
                                Daftar sekarang
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }

        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.animate-fade-in, .animate-shake');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s, transform 0.5s';
                message.style.opacity = '0';
                message.style.transform = 'translateX(100%)';
                setTimeout(() => message.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>