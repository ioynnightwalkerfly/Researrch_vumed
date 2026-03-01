<div class="custom-tab-panel relative opacity-100 translate-y-0 transition-all duration-500 ease-out z-10"
    role="tabpanel" id="panel-overview" aria-labelledby="tab-overview" aria-hidden="false">
    <div
        class="bg-white/5 border border-white/10 p-6 md:p-8 hover:border-[#6366f1]/50 transition-colors group rounded-xl">

        <!-- Puzzle Jigsaw Diagram -->
        <div class="flex flex-col items-center gap-8 w-full py-6">

        

            <!-- Puzzle Container -->
            <div class="relative w-full max-w-[500px] aspect-square drop-shadow-2xl animate-float mx-auto">

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500"
                    class="w-full h-full absolute inset-0 z-0">
                    <g stroke="#ffffff" stroke-width="8" stroke-linejoin="round" stroke-linecap="round">

                        <!-- Top-Left: VUMED.IRB (Blue) -->
                        <a href="login.html">
                            <g id="piece-tl" class="cursor-pointer puzzle-piece">
                                <path
                                    d="M 250 250 L 250 160 A 25 25 0 1 1 250 100 L 250 10 A 240 240 0 0 0 10 250 L 100 250 A 25 25 0 1 1 160 250 Z"
                                    fill="#1e3f5a" class="transition-all duration-300 hover:brightness-110" />
                                <foreignObject x="40" y="40" width="160" height="160" class="pointer-events-none">
                                    <div xmlns="http://www.w3.org/1999/xhtml"
                                        class="w-full h-full flex flex-col items-center justify-center text-white text-center p-2">
                                        <i data-lucide="users"
                                            class="mb-2 drop-shadow-md w-10 h-10 sm:w-14 sm:h-14"></i>
                                        <span
                                            class="font-bold text-xs sm:text-sm md:text-lg drop-shadow-md leading-tight">VUMED.IRB</span>
                                    </div>
                                </foreignObject>
                            </g>
                        </a>

                        <!-- Top-Right: Research and statistic clinic (Yellow) — opens popup -->
                        <g id="piece-tr" class="cursor-pointer puzzle-piece" onclick="document.getElementById('clinicModal').classList.remove('hidden')">
                            <path
                                d="M 250 250 L 250 160 A 25 25 0 1 1 250 100 L 250 10 A 240 240 0 0 1 490 250 L 400 250 A 25 25 0 1 0 340 250 Z"
                                fill="#f3a600" class="transition-all duration-300 hover:brightness-105" />
                            <foreignObject x="300" y="40" width="160" height="160" class="pointer-events-none">
                                <div xmlns="http://www.w3.org/1999/xhtml"
                                    class="w-full h-full flex flex-col items-center justify-center text-white text-center p-2">
                                    <i data-lucide="file-search"
                                        class="mb-2 drop-shadow-md w-10 h-10 sm:w-14 sm:h-14"></i>
                                    <span
                                        class="font-bold text-xs sm:text-sm md:text-lg drop-shadow-md leading-tight">Research<br />and
                                        statistic<br />clinic</span>
                                </div>
                            </foreignObject>
                        </g>

                        <!-- Bottom-Right: Knowledge management (Red) -->
                        <g id="piece-br" class="cursor-pointer puzzle-piece">
                            <path
                                d="M 250 250 L 340 250 A 25 25 0 1 1 400 250 L 490 250 A 240 240 0 0 1 250 490 L 250 400 A 25 25 0 1 1 250 340 Z"
                                fill="#a6192e" class="transition-all duration-300 hover:brightness-110" />
                            <foreignObject x="285" y="280" width="160" height="160" class="pointer-events-none">
                                <div xmlns="http://www.w3.org/1999/xhtml"
                                    class="w-full h-full flex flex-col items-center justify-center text-white text-center p-2">
                                    <i data-lucide="user-search"
                                        class="mb-2 drop-shadow-md w-10 h-10 sm:w-14 sm:h-14"></i>
                                    <span
                                        class="font-bold text-xs sm:text-sm md:text-lg drop-shadow-md leading-tight">Knowledge<br />management</span>
                                </div>
                            </foreignObject>
                        </g>

                        <!-- Bottom-Left: Academic Services (Teal) -->
                        <a href="academic_service_landing.html" target="_blank" rel="noopener">
                        <g id="piece-bl" class="cursor-pointer puzzle-piece">
                            <path
                                d="M 250 250 L 250 340 A 25 25 0 1 0 250 400 L 250 490 A 240 240 0 0 1 10 250 L 100 250 A 25 25 0 1 1 160 250 Z"
                                fill="#255b5c" class="transition-all duration-300 hover:brightness-110" />
                            <foreignObject x="55" y="280" width="160" height="160" class="pointer-events-none">
                                <div xmlns="http://www.w3.org/1999/xhtml"
                                    class="w-full h-full flex flex-col items-center justify-center text-white text-center p-2">
                                    <i data-lucide="handshake"
                                        class="mb-2 drop-shadow-md w-10 h-10 sm:w-14 sm:h-14"></i>
                                    <span
                                        class="font-bold text-xs sm:text-sm md:text-lg drop-shadow-md leading-tight">Academic<br />Services</span>
                                </div>
                            </foreignObject>
                        </g>
                        </a>
                    </g>
                </svg>

                <!-- Center Logo Circle -->
                <a href="https://vumedhr.vu.ac.th/narco/" target="_blank" rel="noopener"
                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[46%] h-[46%] bg-white/10 backdrop-blur-md rounded-full z-20 shadow-[0_0_50px_rgba(243,166,0,0.4)] flex flex-col items-center justify-center p-4 border-[2px] border-[#f3a600]/50 animate-pulse-slow ring-4 ring-[#f3a600]/20 cursor-pointer hover:scale-105 transition-transform duration-300">
                    <div class="absolute inset-2 rounded-full border border-white/30 pointer-events-none"></div>
                    <div class="relative flex flex-col items-center justify-center w-full h-full">
                        <img src="home_icon.png" alt="ASC Home Icon"
                            class="w-full h-full object-contain drop-shadow-[0_0_15px_rgba(255,255,255,0.6)] rounded-full relative z-10" />
                    </div>
                </a>

                <!-- Info Tooltips Overlay -->
                <div class="absolute inset-0 z-30 pointer-events-none">

                    <!-- Info TL (VUMED.IRB) -->
                    <div id="info-tl" class="info-card absolute -top-[2%] -left-[2%] md:-left-[28%] w-[45%] md:w-[280px] origin-bottom-right">
                        <div class="bg-white/95 backdrop-blur-md shadow-[0_10px_30px_rgba(30,63,90,0.2)] rounded-2xl p-3 md:p-5 border-l-4 border-[#1e3f5a]">
                            <h4 class="font-bold text-[#1e3f5a] text-sm md:text-lg mb-1">VUMED.IRB</h4>
                            <p class="text-[10px] md:text-sm text-slate-600 leading-tight">ระบบยื่นขอพิจารณาจริยธรรมการวิจัยในมนุษย์ <span class="text-[#1e3f5a] font-semibold">คลิกเพื่อดำเนินการยื่น</span> หรือติดตามสถานะ</p>
                        </div>
                        <svg class="absolute -bottom-6 -right-6 w-12 h-12 md:-bottom-12 md:-right-12 md:w-20 md:h-20 text-[#1e3f5a]" viewBox="0 0 100 100" fill="none">
                            <path d="M10,10 L90,90" stroke="currentColor" stroke-width="2.5" stroke-dasharray="5 4"/>
                            <circle cx="90" cy="90" r="5" fill="currentColor"/>
                            <circle cx="10" cy="10" r="3" fill="currentColor"/>
                        </svg>
                    </div>

                    <!-- Info TR (Research & Statistic Clinic) -->
                    <div id="info-tr" class="info-card absolute -top-[2%] -right-[2%] md:-right-[28%] w-[45%] md:w-[280px] origin-bottom-left">
                        <div class="bg-white/95 backdrop-blur-md shadow-[0_10px_30px_rgba(243,166,0,0.2)] rounded-2xl p-3 md:p-5 border-l-4 border-[#f3a600]">
                            <h4 class="font-bold text-[#f3a600] text-sm md:text-lg mb-1">Research & Statistic Clinic</h4>
                            <p class="text-[10px] md:text-sm text-slate-600 leading-tight">คลินิกให้คำปรึกษาด้านการวิจัยและสถิติ <span class="text-[#f3a600] font-semibold">คลิกเพื่อดูรายละเอียด</span> และลงทะเบียน</p>
                        </div>
                        <svg class="absolute -bottom-6 -left-6 w-12 h-12 md:-bottom-12 md:-left-12 md:w-20 md:h-20 text-[#f3a600]" viewBox="0 0 100 100" fill="none">
                            <path d="M90,10 L10,90" stroke="currentColor" stroke-width="2.5" stroke-dasharray="5 4"/>
                            <circle cx="10" cy="90" r="5" fill="currentColor"/>
                            <circle cx="90" cy="10" r="3" fill="currentColor"/>
                        </svg>
                    </div>

                    <!-- Info BR (Knowledge Management) -->
                    <div id="info-br" class="info-card absolute -bottom-[2%] -right-[2%] md:-right-[28%] w-[45%] md:w-[280px] origin-top-left">
                        <div class="bg-white/95 backdrop-blur-md shadow-[0_10px_30px_rgba(166,25,46,0.2)] rounded-2xl p-3 md:p-5 border-l-4 border-[#a6192e]">
                            <h4 class="font-bold text-[#a6192e] text-sm md:text-lg mb-1">Knowledge Management</h4>
                            <p class="text-[10px] md:text-sm text-slate-600 leading-tight">ระบบจัดการองค์ความรู้ รวบรวมบทความ งานวิจัย และแหล่งเรียนรู้ของศูนย์</p>
                        </div>
                        <svg class="absolute -top-6 -left-6 w-12 h-12 md:-top-12 md:-left-12 md:w-20 md:h-20 text-[#a6192e]" viewBox="0 0 100 100" fill="none">
                            <path d="M90,90 L10,10" stroke="currentColor" stroke-width="2.5" stroke-dasharray="5 4"/>
                            <circle cx="10" cy="10" r="5" fill="currentColor"/>
                            <circle cx="90" cy="90" r="3" fill="currentColor"/>
                        </svg>
                    </div>

                    <!-- Info BL (Academic Services) -->
                    <div id="info-bl" class="info-card absolute -bottom-[2%] -left-[2%] md:-left-[28%] w-[45%] md:w-[280px] origin-top-right">
                        <div class="bg-white/95 backdrop-blur-md shadow-[0_10px_30px_rgba(37,91,92,0.2)] rounded-2xl p-3 md:p-5 border-l-4 border-[#255b5c]">
                            <h4 class="font-bold text-[#255b5c] text-sm md:text-lg mb-1">Academic Services</h4>
                            <p class="text-[10px] md:text-sm text-slate-600 leading-tight">งานบริการวิชาการ ให้คำปรึกษาและสนับสนุนด้านวิชาการแก่ชุมชนและหน่วยงานภายนอก</p>
                        </div>
                        <svg class="absolute -top-6 -right-6 w-12 h-12 md:-top-12 md:-right-12 md:w-20 md:h-20 text-[#255b5c]" viewBox="0 0 100 100" fill="none">
                            <path d="M10,90 L90,10" stroke="currentColor" stroke-width="2.5" stroke-dasharray="5 4"/>
                            <circle cx="90" cy="10" r="5" fill="currentColor"/>
                            <circle cx="10" cy="90" r="3" fill="currentColor"/>
                        </svg>
                    </div>

                </div>
            </div>


        </div>

    </div>
</div>

<!-- Clinic Consultation Popup Modal -->
<div id="clinicModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
    <!-- Modal Card -->
    <div class="relative w-full max-w-2xl bg-[#0c0c14] border border-white/15 rounded-2xl shadow-2xl overflow-hidden animate-[fadeSlideIn_0.3s_ease-out]">
        <!-- Close button -->
        <button onclick="document.getElementById('clinicModal').classList.add('hidden')"
            class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white/60 hover:text-white transition z-20">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        <div class="flex flex-col md:flex-row">
            <!-- Left: Image area -->
            <div class="md:w-2/5 relative overflow-hidden border-b md:border-b-0 md:border-r border-white/10">
                <img src="assets/img/clinic_consult.jpg" alt="คลินิกให้คำปรึกษาวิจัยและสถิติ" class="w-full h-48 md:h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6">
                    <h3 class="text-xl md:text-2xl font-bold text-white leading-tight mb-1">คลินิกให้คำปรึกษา<br>วิจัยและสถิติ</h3>
                    <p class="text-white/60 text-xs">Research & Statistic Clinic</p>
                </div>
            </div>

            <!-- Right: Info area (sky blue accent) -->
            <div class="md:w-3/5 p-6 md:p-8 space-y-5 bg-gradient-to-br from-[#0ea5e9]/10 to-transparent">

                <!-- Consultation Modes -->
                <div>
                    <h4 class="text-[#0ea5e9] font-bold text-sm uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>รูปแบบการให้คำปรึกษา
                    </h4>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 text-white/80 text-sm">
                            <span class="w-2 h-2 bg-[#0ea5e9] rounded-full shrink-0"></span>Personal or Group
                        </div>
                        <div class="flex items-center gap-3 text-white/80 text-sm">
                            <span class="w-2 h-2 bg-[#0ea5e9] rounded-full shrink-0"></span>Online Consultation
                        </div>
                        <div class="flex items-center gap-3 text-white/80 text-sm">
                            <span class="w-2 h-2 bg-[#0ea5e9] rounded-full shrink-0"></span>E-mail
                        </div>
                    </div>
                </div>

                <!-- Service Hours -->
                <div class="p-4 bg-[#0ea5e9]/10 border border-[#0ea5e9]/20 rounded-xl">
                    <h4 class="text-[#0ea5e9] font-bold text-sm mb-2 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4"></i>บริการให้คำปรึกษา
                    </h4>
                    <p class="text-white/80 text-sm">ทุกวันศุกร์ เวลา 10.00 - 12.00 น.</p>
                    <p class="text-white/50 text-xs mt-1">หรือจองวันและเวลาล่วงหน้า</p>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-[#0ea5e9] font-bold text-sm uppercase tracking-wider mb-2 flex items-center gap-2">
                        <i data-lucide="phone" class="w-4 h-4"></i>สอบถามข้อมูลเพิ่มเติม
                    </h4>
                    <p class="text-white/70 text-sm">Tel. 044-009711 ต่อ 120</p>
                    <p class="text-white/70 text-sm">E-mail: <a href="mailto:researchethics_md@vu.ac.th" class="text-[#0ea5e9] hover:underline">researchethics_md@vu.ac.th</a></p>
                </div>

                <!-- Registration Button -->
                <a href="#"
                    class="block w-full text-center py-3 bg-gradient-to-r from-[#0ea5e9] to-[#3b82f6] text-white font-bold text-sm rounded-xl hover:opacity-90 transition shadow-lg shadow-[#0ea5e9]/20">
                    <i data-lucide="clipboard-list" class="w-4 h-4 inline mr-2"></i>ลงทะเบียนเพื่อขอรับคำปรึกษา
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tooltip Styles -->
<style>
.info-card {
    opacity: 0;
    visibility: hidden;
    transform: scale(0.9) translateY(15px);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.info-card.active {
    opacity: 1;
    visibility: visible;
    transform: scale(1) translateY(0);
}
</style>

<!-- Tooltip Hover Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pieces = [
        { id: 'piece-tl', infoId: 'info-tl', hoverTransform: 'translate(-12px, -12px)' },
        { id: 'piece-tr', infoId: 'info-tr', hoverTransform: 'translate(12px, -12px)' },
        { id: 'piece-br', infoId: 'info-br', hoverTransform: 'translate(12px, 12px)' },
        { id: 'piece-bl', infoId: 'info-bl', hoverTransform: 'translate(-12px, 12px)' }
    ];

    pieces.forEach(piece => {
        const element = document.getElementById(piece.id);
        const info = document.getElementById(piece.infoId);

        if (element && info) {
            element.addEventListener('mouseenter', () => {
                element.style.transform = piece.hoverTransform;
                info.classList.add('active');
            });
            element.addEventListener('mouseleave', () => {
                element.style.transform = 'translate(0px, 0px)';
                info.classList.remove('active');
            });
        }
    });
});
</script>
