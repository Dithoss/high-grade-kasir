<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital - Your Gateway to Knowledge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .slide-in-left {
            animation: slideInLeft 0.8s ease-out forwards;
        }
        
        .slide-in-right {
            animation: slideInRight 0.8s ease-out forwards;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .book-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .book-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-counter {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Navigation -->
    <nav class="fixed w-full top-0 z-50 glass-effect shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-book-open text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Perpustakaan</span>
                        <p class="text-xs text-gray-600">Digital Library</p>
                    </div>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-indigo-600 font-medium transition">Beranda</a>
                    <a href="#features" class="text-gray-700 hover:text-indigo-600 font-medium transition">Fitur</a>
                    <a href="#collection" class="text-gray-700 hover:text-indigo-600 font-medium transition">Koleksi</a>
                    <a href="#about" class="text-gray-700 hover:text-indigo-600 font-medium transition">Tentang</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/login" class="px-6 py-2.5 text-indigo-600 hover:text-indigo-700 font-semibold transition">
                        Masuk
                    </a>
                    @if($settings->isRegistrationOpen())
                    <a href="/register" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-xl transform hover:scale-105 transition">
                        Daftar Sekarang
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="pt-32 pb-20 px-4 overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="slide-in-left">
                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Jelajahi Dunia
                        <span class="gradient-text block">Pengetahuan Tanpa Batas</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Akses ribuan buku digital, kelola peminjaman dengan mudah, dan nikmati pengalaman membaca yang lebih baik bersama sistem perpustakaan modern kami.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="/register" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-2xl transform hover:scale-105 transition flex items-center space-x-2">
                            <span>Mulai Gratis</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#features" class="px-8 py-4 border-2 border-indigo-600 text-indigo-600 rounded-xl font-semibold hover:bg-indigo-50 transition">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 mt-12">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600 stat-counter">15K+</div>
                            <div class="text-sm text-gray-600">Buku Digital</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600 stat-counter">5K+</div>
                            <div class="text-sm text-gray-600">Pengguna Aktif</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-pink-600 stat-counter">98%</div>
                            <div class="text-sm text-gray-600">Kepuasan</div>
                        </div>
                    </div>
                </div>
                
                <div class="slide-in-right relative">
                    <div class="relative z-10 float-animation">
                        <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=600&h=600&fit=crop" alt="Library" class="rounded-3xl shadow-2xl">
                    </div>
                    <div class="absolute top-10 -right-10 w-72 h-72 bg-gradient-to-br from-indigo-400 to-purple-400 rounded-full opacity-20 blur-3xl"></div>
                    <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-gradient-to-br from-pink-400 to-purple-400 rounded-full opacity-20 blur-3xl"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Fitur Unggulan</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Nikmati berbagai kemudahan dalam mengakses dan mengelola koleksi perpustakaan</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="feature-card bg-gradient-to-br from-blue-50 to-indigo-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-search text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Pencarian Cerdas</h3>
                    <p class="text-gray-600 leading-relaxed">Temukan buku yang Anda cari dengan cepat menggunakan sistem pencarian canggih berdasarkan judul, penulis, atau kategori.</p>
                </div>
                
                <div class="feature-card bg-gradient-to-br from-purple-50 to-pink-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-mobile-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Akses Mobile</h3>
                    <p class="text-gray-600 leading-relaxed">Kelola peminjaman dan baca buku favorit Anda kapan saja, di mana saja melalui perangkat mobile.</p>
                </div>
                
                <div class="feature-card bg-gradient-to-br from-green-50 to-emerald-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Notifikasi Real-time</h3>
                    <p class="text-gray-600 leading-relaxed">Dapatkan pengingat otomatis untuk tanggal pengembalian dan informasi penting lainnya.</p>
                </div>
                
                <div class="feature-card bg-gradient-to-br from-yellow-50 to-orange-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Riwayat Lengkap</h3>
                    <p class="text-gray-600 leading-relaxed">Lacak semua aktivitas peminjaman dan lihat statistik membaca Anda secara detail.</p>
                </div>
                
                <div class="feature-card bg-gradient-to-br from-red-50 to-rose-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Keamanan Terjamin</h3>
                    <p class="text-gray-600 leading-relaxed">Data Anda terlindungi dengan sistem keamanan berlapis dan enkripsi tingkat tinggi.</p>
                </div>
                
                <div class="feature-card bg-gradient-to-br from-cyan-50 to-blue-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-headset text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Dukungan 24/7</h3>
                    <p class="text-gray-600 leading-relaxed">Tim support kami siap membantu Anda kapan saja untuk pengalaman terbaik.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Book Collection Preview -->
    <section id="collection" class="py-20 px-4 bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 text-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Koleksi Pilihan</h2>
                <p class="text-xl text-indigo-200 max-w-2xl mx-auto">Jelajahi berbagai kategori buku dari koleksi terbaik kami</p>
            </div>
            
            <div class="grid md:grid-cols-4 gap-6">
                <div class="book-card bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-6 border border-white border-opacity-20">
                    <div class="h-48 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl mb-4 flex items-center justify-center shadow-xl">
                        <i class="fas fa-book text-white text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Fiksi & Sastra</h3>
                    <p class="text-sm text-indigo-200 mb-4">3,500+ Buku</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-indigo-300">Populer</span>
                        <i class="fas fa-arrow-right text-indigo-300"></i>
                    </div>
                </div>
                
                <div class="book-card bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-6 border border-white border-opacity-20">
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl mb-4 flex items-center justify-center shadow-xl">
                        <i class="fas fa-flask text-white text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Sains & Teknologi</h3>
                    <p class="text-sm text-indigo-200 mb-4">2,800+ Buku</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-indigo-300">Terbaru</span>
                        <i class="fas fa-arrow-right text-indigo-300"></i>
                    </div>
                </div>
                
                <div class="book-card bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-6 border border-white border-opacity-20">
                    <div class="h-48 bg-gradient-to-br from-pink-400 to-pink-600 rounded-xl mb-4 flex items-center justify-center shadow-xl">
                        <i class="fas fa-history text-white text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Sejarah & Budaya</h3>
                    <p class="text-sm text-indigo-200 mb-4">1,900+ Buku</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-indigo-300">Klasik</span>
                        <i class="fas fa-arrow-right text-indigo-300"></i>
                    </div>
                </div>
                
                <div class="book-card bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-6 border border-white border-opacity-20">
                    <div class="h-48 bg-gradient-to-br from-green-400 to-green-600 rounded-xl mb-4 flex items-center justify-center shadow-xl">
                        <i class="fas fa-graduation-cap text-white text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Pendidikan</h3>
                    <p class="text-sm text-indigo-200 mb-4">4,200+ Buku</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-indigo-300">Pilihan Siswa</span>
                        <i class="fas fa-arrow-right text-indigo-300"></i>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="/books" class="inline-flex items-center space-x-2 px-8 py-4 bg-white text-indigo-900 rounded-xl font-semibold hover:shadow-2xl transform hover:scale-105 transition">
                    <span>Lihat Semua Koleksi</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">Siap Memulai Perjalanan Membaca?</h2>
            <p class="text-xl mb-8 text-indigo-100">Bergabunglah dengan ribuan pembaca lainnya dan nikmati akses tak terbatas ke koleksi buku digital kami</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/register" class="px-10 py-4 bg-white text-indigo-600 rounded-xl font-bold hover:shadow-2xl transform hover:scale-105 transition">
                    Daftar Gratis Sekarang
                </a>
                <a href="/login" class="px-10 py-4 border-2 border-white text-white rounded-xl font-bold hover:bg-white hover:text-indigo-600 transition">
                    Masuk ke Akun
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-book-open text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-white">Perpustakaan</span>
                    </div>
                    <p class="text-sm text-gray-400">Sistem perpustakaan digital modern untuk pengalaman membaca yang lebih baik.</p>
                </div>
                
                <div>
                    <h3 class="text-white font-bold mb-4">Layanan</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Peminjaman Buku</a></li>
                        <li><a href="#" class="hover:text-white transition">E-Book</a></li>
                        <li><a href="#" class="hover:text-white transition">Koleksi Digital</a></li>
                        <li><a href="#" class="hover:text-white transition">Keanggotaan</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-white font-bold mb-4">Dukungan</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Bantuan</a></li>
                        <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition">Kontak</a></li>
                        <li><a href="#" class="hover:text-white transition">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-white font-bold mb-4">Ikuti Kami</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
                <p>&copy; 2024 Perpustakaan Digital. All rights reserved. Made with <i class="fas fa-heart text-red-500"></i> by Dev Team</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .book-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>