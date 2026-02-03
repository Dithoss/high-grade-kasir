<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Perpustakaan</title>
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
                    <h1 class="text-4xl font-bold text-white mb-3">Daftar Akun</h1>
                    <p class="text-white/90 text-lg">Bergabunglah dengan Perpustakaan Digital</p>
                </div>

                <!-- Illustration -->
                <div class="illustration w-full max-w-md">
                    <svg viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Phone mockup -->
                        <rect x="150" y="50" width="200" height="300" rx="20" fill="white" opacity="0.9"/>
                        <rect x="150" y="50" width="200" height="300" rx="20" stroke="white" stroke-width="2"/>
                        
                        <!-- Screen content -->
                        <circle cx="250" cy="130" r="30" fill="#667eea" opacity="0.3"/>
                        <circle cx="250" cy="130" r="25" fill="#667eea"/>
                        <path d="M250 140 L240 150 L250 160 L260 150 Z" fill="white"/>
                        
                        <!-- Form elements -->
                        <rect x="180" y="180" width="140" height="12" rx="6" fill="#E0E7FF"/>
                        <rect x="180" y="200" width="140" height="12" rx="6" fill="#E0E7FF"/>
                        <rect x="180" y="220" width="140" height="12" rx="6" fill="#E0E7FF"/>
                        
                        <!-- Button -->
                        <rect x="180" y="250" width="140" height="30" rx="15" fill="#667eea"/>
                        
                        <!-- Decorative elements -->
                        <circle cx="80" cy="100" r="8" fill="white" opacity="0.3"/>
                        <circle cx="420" cy="150" r="12" fill="white" opacity="0.3"/>
                        <circle cx="400" cy="300" r="6" fill="white" opacity="0.3"/>
                        
                        <!-- Person illustration -->
                        <circle cx="100" cy="280" r="40" fill="white" opacity="0.9"/>
                        <circle cx="100" cy="270" r="15" fill="#667eea"/>
                        <path d="M75 295 Q100 310 125 295" stroke="#667eea" stroke-width="3" fill="none"/>
                    </svg>
                </div>

                <div class="mt-8 text-center">
                    <p class="text-white/80 text-sm">Akses ribuan buku digital</p>
                    <p class="text-white/80 text-sm">Sistem peminjaman mudah & cepat</p>
                </div>
            </div>

            <!-- Right Side - Registration Form -->
            <div class="bg-white rounded-3xl card-shadow p-8 lg:p-12">
                
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 gradient-bg rounded-2xl mb-4 shadow-lg">
                        <i class="fas fa-book-open text-white text-2xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800">Daftar Akun</h2>
                    <p class="text-gray-500 mt-2">Isi formulir untuk memulai</p>
                </div>

                <!-- Logo Header for Desktop -->
                <div class="hidden lg:block mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-book-open text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">Perpustakaan Digital</h3>
                            <p class="text-sm text-gray-500">Teaching Factory Inovasi Kesehatan</p>
                        </div>
                    </div>
                    <p class="text-gray-600">Buat Akun Anda Sekarang. Isi data singkat untuk mulai gunakan layanan.</p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-red-800 mb-2">Terdapat beberapa kesalahan:</h3>
                                <ul class="text-sm text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf

                    <!-- Name Fields - Side by Side -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Depan
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none icon-wrapper">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    name="first_name" 
                                    value="{{ old('first_name') }}"
                                    placeholder="John"
                                    class="input-field w-full pl-11 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                                    required>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Belakang
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none icon-wrapper">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    name="last_name" 
                                    value="{{ old('last_name') }}"
                                    placeholder="Doe"
                                    class="input-field w-full pl-11 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Email & Phone - Side by Side -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Email -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none icon-wrapper">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input 
                                    type="email" 
                                    name="email" 
                                    value="{{ old('email') }}"
                                    placeholder="mihumihu@gmail.com"
                                    class="input-field w-full pl-11 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                                    required>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nomer Telepon
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none icon-wrapper">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input 
                                    type="tel" 
                                    name="phone" 
                                    value="{{ old('phone') }}"
                                    placeholder="+62276558008887"
                                    class="input-field w-full pl-11 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="input-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
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
                                placeholder="••••••••••••••••••••••••"
                                class="input-field w-full pl-11 pr-12 py-3.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                                required>
                            <button 
                                type="button"
                                onclick="togglePassword('password', 'passwordIcon')"
                                class="password-toggle absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400">
                                <i id="passwordIcon" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="input-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none icon-wrapper">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input 
                                type="password" 
                                id="password_confirmation"
                                name="password_confirmation" 
                                placeholder="••••••••••••••••••••••••"
                                class="input-field w-full pl-11 pr-12 py-3.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                                required>
                            <button 
                                type="button"
                                onclick="togglePassword('password_confirmation', 'confirmIcon')"
                                class="password-toggle absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400">
                                <i id="confirmIcon" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl">
                        <input 
                            type="checkbox" 
                            id="terms"
                            name="terms"
                            class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 focus:ring-2 mt-0.5"
                            required>
                        <label for="terms" class="text-sm text-gray-600 leading-relaxed">
                            Saya setuju dengan semua <a href="#" class="text-purple-600 font-medium hover:underline">Syarat & Ketentuan</a> serta <a href="#" class="text-purple-600 font-medium hover:underline">Kebijakan Privasi</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="btn-primary w-full py-4 text-white font-semibold rounded-xl shadow-lg flex items-center justify-center gap-2 text-lg">
                        <i class="fas fa-user-plus"></i>
                        <span>Buat Akun</span>
                    </button>

                    <!-- Already have account -->
                    <div class="text-center pt-4">
                        <p class="text-gray-600">
                            Sudah memiliki akun? 
                            <a href="{{ route('login') }}" class="text-purple-600 font-semibold hover:underline">
                                Masuk
                            </a>
                        </p>
                    </div>

                    <!-- Social Login Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500">atau daftar dengan</span>
                        </div>
                    </div>

                    <!-- Social Login Button -->
                    <button 
                        type="button"
                        class="w-full py-3.5 border-2 border-gray-200 rounded-xl font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all flex items-center justify-center gap-3">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Google</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>