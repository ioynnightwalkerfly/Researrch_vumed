<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อเรา - ฝ่ายวิจัย และการจัดการความรู้</title>
    <?php require_once 'includes/header_scripts.php'; ?>
</head>
<body class="min-h-screen font-sans overflow-x-hidden selection:bg-fuchsia-500 selection:text-white"
    style="background-color: var(--dark-bg); color: var(--text-primary);">

    <!-- Background -->
    <div class="fixed inset-0 bg-fixed pointer-events-none -z-10" style="background: radial-gradient(ellipse at 20% 0%, rgba(99,102,241,0.1) 0%, transparent 60%), radial-gradient(ellipse at 80% 100%, rgba(236,72,153,0.08) 0%, transparent 50%), var(--dark-bg);">
    </div>
    <div class="fixed inset-0 bg-[radial-gradient(rgba(255,255,255,0.03)_1px,transparent_1px)] [background-size:32px_32px] pointer-events-none -z-10"></div>

    <div class="min-h-screen flex flex-col">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="pt-28 pb-16 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full flex-grow">

        <!-- Page Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold tracking-wider uppercase mb-4"
                style="background: var(--glass-bg); border: 1px solid var(--glass-border); color: var(--accent-cyan);">
                <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                Contact Us
            </div>
            <h1 class="text-3xl md:text-5xl font-bold text-white mb-3">ติดต่อเรา</h1>
            <p class="text-white/50 text-base md:text-lg max-w-xl mx-auto">
                ฝ่ายวิจัย และการจัดการความรู้ คณะแพทยศาสตร์ มหาวิทยาลัยวงษ์ชวลิตกุล
            </p>
        </div>

        <!-- Contact Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-12">

            <!-- Email -->
            <a href="mailto:vumed@vu.ac.th"
                class="group glass-card rounded-2xl p-6 text-center hover:border-[#f3a600]/40 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_15px_40px_rgba(243,166,0,0.1)]">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl flex items-center justify-center transition-colors"
                    style="background: rgba(243,166,0,0.1); border: 1px solid rgba(243,166,0,0.2);">
                    <i data-lucide="mail" class="w-6 h-6 text-[#f3a600]"></i>
                </div>
                <h3 class="text-white font-bold text-sm mb-1">อีเมล</h3>
                <p class="text-[#f3a600] text-sm font-medium group-hover:underline">vumed@vu.ac.th</p>
            </a>

            <!-- Phone -->
            <a href="tel:044009711"
                class="group glass-card rounded-2xl p-6 text-center hover:border-[#2dd4bf]/40 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_15px_40px_rgba(45,212,191,0.1)]">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl flex items-center justify-center"
                    style="background: rgba(45,212,191,0.1); border: 1px solid rgba(45,212,191,0.2);">
                    <i data-lucide="phone" class="w-6 h-6 text-[#2dd4bf]"></i>
                </div>
                <h3 class="text-white font-bold text-sm mb-1">โทรศัพท์</h3>
                <p class="text-[#2dd4bf] text-sm font-medium">044-009711 ต่อ 120</p>
            </a>

            <!-- Location -->
            <div class="glass-card rounded-2xl p-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl flex items-center justify-center"
                    style="background: rgba(236,72,153,0.1); border: 1px solid rgba(236,72,153,0.2);">
                    <i data-lucide="map-pin" class="w-6 h-6 text-pink-400"></i>
                </div>
                <h3 class="text-white font-bold text-sm mb-1">สถานที่</h3>
                <p class="text-white/50 text-xs leading-relaxed">อาคารขวัญแก้ว ชั้น 1<br>คณะแพทยศาสตร์ ม.วงษ์ชวลิตกุล</p>
            </div>

        </div>

        <!-- Map + Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Google Map -->
            <div class="glass-card rounded-2xl overflow-hidden">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3870.8!2d102.1173724!3d15.0033428!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31194b76202d0947%3A0x85acb4b6742174a6!2z4LiE4LiT4Liw4LmA4Lio4Lij4Lip4LiQ4Lio4Liy4Liq4LiV4Lij4LmMIOC4p-C4h-C4qeC5jOC4iuC4p-C4peC4tOC4leC4geC4uOC4pQ!5e0!3m2!1sth!2sth!4v1700000000000!5m2!1sth!2sth"
                    width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" class="w-full"></iframe>
            </div>

            <!-- Details Card -->
            <div class="glass-card rounded-2xl p-6 md:p-8 flex flex-col justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white mb-4">ที่อยู่สำหรับติดต่อ</h2>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <i data-lucide="building-2" class="w-4 h-4 mt-1 text-white/30 shrink-0"></i>
                            <div>
                                <p class="text-white/70 text-sm font-medium">คณะแพทยศาสตร์ มหาวิทยาลัยวงษ์ชวลิตกุล</p>
                                <p class="text-white/40 text-xs">Faculty of Medicine, Vongchavalitkul University</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-4 h-4 mt-1 text-white/30 shrink-0"></i>
                            <p class="text-white/60 text-sm leading-relaxed">
                                84 หมู่ 4 ถนนมิตรภาพ-หนองคาย<br>
                                ตำบลบ้านเกาะ อำเภอเมือง<br>
                                จังหวัดนครราชสีมา 30000
                            </p>
                        </div>

                        <div class="flex items-start gap-3">
                            <i data-lucide="clock" class="w-4 h-4 mt-1 text-white/30 shrink-0"></i>
                            <div>
                                <p class="text-white/70 text-sm font-medium">เวลาทำการ</p>
                                <p class="text-white/50 text-xs">จันทร์ - ศุกร์ 08:30 - 16:30 น.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex gap-3 mt-6 pt-5 border-t border-white/10">
                    <a href="mailto:vumed@vu.ac.th"
                        class="flex-1 text-center py-2.5 px-4 rounded-xl text-sm font-semibold transition-all text-white"
                        style="background: var(--primary-gradient);">
                        <i data-lucide="send" class="w-3.5 h-3.5 inline mr-1"></i>
                        ส่งอีเมล
                    </a>
                    <a href="tel:044009711"
                        class="flex-1 text-center py-2.5 px-4 rounded-xl text-sm font-semibold text-white/80 transition-all hover:bg-white/10"
                        style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
                        <i data-lucide="phone" class="w-3.5 h-3.5 inline mr-1"></i>
                        โทร
                    </a>
                </div>
            </div>

        </div>

    </main>

    <?php require_once 'includes/footer.php'; ?>
    </div>

    <!-- Scripts -->
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>
</html>
