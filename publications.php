<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลงานวิจัยทั้งหม</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Glassmorphism Classes */
        .glass-nav {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
        }
    </style>
</head>

<body class="relative bg-slate-900 overflow-x-hidden selection:bg-blue-500/30 selection:text-blue-200">

    <!-- Deep Background Glow Effects -->
    <div class="fixed top-[-20%] left-[-10%] w-[50%] h-[50%] bg-[#06b6d4]/10 blur-[120px] rounded-full pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[50%] h-[50%] bg-[#3b82f6]/10 blur-[120px] rounded-full pointer-events-none z-0"></div>

    <!-- Header / Navbar -->
    <header class="fixed w-full top-0 z-50 glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Logo -->
                        <h1 class="text-white font-bold text-lg tracking-wider leading-tight">ฝ่ายวิจัย และการจัดการความรู้</h1>

                <!-- Navigation Links -->
                <nav class="hidden md:flex space-x-8">
                    <a href="index.html" class="text-white/70 hover:text-white transition-colors text-sm font-medium">หน้าหลัก</a>
                    <a href="index.html#research" class="text-[#06b6d4] font-bold text-sm">การดำเนินงานวิจัย</a>
                    <a href="#" class="text-white/70 hover:text-white transition-colors text-sm font-medium">โครงการเด่น</a>
                </nav>

    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-32 pb-20 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            <div class="mb-12 text-center">
                
                <h1 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white via-blue-100 to-[#06b6d4] mb-4">
                    ผลงานวิจัยที่ได้รับการตีพิมพ์ทั้งหมด
                </h1>
                <p class="text-white/60 text-lg max-w-2xl mx-auto">
                    รวบรวมข้อมูลผลงานวิชาการ การค้นพบ และการตีพิมพ์บทความวิจัยระดับชาติและระดับนานาชาติของบุคลากรและนักศึกษา
                </p>
            </div>

            <div class="mb-8 border-b border-white/10 pb-4 flex justify-between items-end">
                <a href="index.html#research" class="inline-flex items-center gap-2 text-sm text-white/60 hover:text-white transition-colors bg-white/5 hover:bg-white/10 px-4 py-2 rounded-lg border border-white/10">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> กลับหน้าหลัก
                </a>
            </div>

            <!-- Publications Grid -->
            <div id="publications-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Data will be loaded via JS -->
                <div class="col-span-full py-20 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white mb-4"></div>
                    <p class="text-white/50">กำลังโหลดฐานข้อมูล...</p>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-white/5 py-12 relative z-10 mt-auto shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <!-- Branding -->
                <div class="flex items-center gap-4">
                        <h4 class="text-white font-bold text-lg">ฝ่ายวิจัย และการจัดการความรู้</h4>
                    </div>
                </div>
                <!-- Copy -->
                <p class="text-white/30 text-sm text-center md:text-right">
                    &copy; <?php echo date('Y'); ?> VUMED Research Information System.<br>All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Initialize Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            const gridContainer = document.getElementById('publications-grid');

            async function loadAllPublications() {
                try {
                    // Fetch all records without limit
                    const response = await fetch('api/get_publication_records.php?limit=all');
                    const result = await response.json();

                    gridContainer.innerHTML = ''; // clear loading state

                    if (result.status === 'error') {
                        gridContainer.innerHTML = `<div class="col-span-full text-center text-red-400 py-10 bg-red-500/10 border border-red-500/20 rounded-xl">API Error: ${result.message}</div>`;
                        return;
                    }

                    if (result.status === 'success' && result.data && result.data.length > 0) {
                        result.data.forEach(record => {
                            const dateObj = new Date(record.published_date);
                            const yearDisplay = dateObj.getFullYear() + 543;

                            const html = `
                                <a href="${record.link || '#'}" target="${record.link ? '_blank' : '_self'}" class="group relative flex flex-col justify-between p-6 bg-white/5 border border-white/10 rounded-2xl overflow-hidden transition-all duration-500 hover:bg-white/10 hover:-translate-y-2 hover:shadow-[0_15px_40px_-15px_${record.color_code}alight]" style="border-top-color: ${record.color_code}; border-top-width: 4px;">
                                    <div class="absolute inset-0 bg-gradient-to-b from-[${record.color_code}]/0 via-transparent to-[${record.color_code}]/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                                    <div class="relative z-10">
                                        <div class="flex items-start justify-between gap-4 mb-4">
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] md:text-xs font-bold uppercase tracking-wider rounded-md bg-[${record.color_code}]/10 border border-[${record.color_code}]/20 transition-colors" style="color: ${record.color_code}">
                                                ${record.category_name}
                                            </span>
                                            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-[${record.color_code}] opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-500">
                                                <i data-lucide="${record.link ? 'external-link' : 'arrow-right'}" class="w-4 h-4"></i>
                                            </div>
                                        </div>
                                        <h4 class="text-white font-semibold text-lg leading-snug group-hover:text-[${record.color_code}] transition-colors duration-500 line-clamp-3 mb-4">${record.title}</h4>
                                    </div>
                                    <div class="relative z-10 pt-4 border-t border-white/5 space-y-2 mt-auto">
                                        <p class="text-white/70 text-sm flex items-start gap-2"><i data-lucide="user" class="w-4 h-4 opacity-50 shrink-0 mt-0.5"></i> <span class="line-clamp-2">${record.author}</span></p>
                                        <p class="text-white/40 text-xs flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4 opacity-50 shrink-0"></i> วันที่เผยแพร่: ${record.published_date} (ปี ${yearDisplay})</p>
                                    </div>
                                </a>
                            `;
                            gridContainer.insertAdjacentHTML('beforeend', html);
                        });
                        lucide.createIcons();
                    } else {
                        gridContainer.innerHTML = '<div class="col-span-full text-center text-white/50 py-20 bg-white/5 border border-white/10 rounded-xl">ไม่พบข้อมูลผลงานวิจัย</div>';
                    }
                } catch (err) {
                    console.error("Error loading publication records", err);
                    gridContainer.innerHTML = '<div class="col-span-full text-center text-red-400 py-10 bg-red-500/10 border border-red-500/20 rounded-xl">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</div>';
                }
            }

            loadAllPublications();
        });
    </script>
</body>
</html>
