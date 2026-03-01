<div class="custom-tab-panel absolute inset-x-0 top-0 opacity-0 translate-y-8 pointer-events-none transition-all duration-500 ease-out z-0"
    role="tabpanel" id="panel-pr" aria-labelledby="tab-pr" aria-hidden="true">
    <div
        class="bg-white/5 border border-white/10 p-6 md:p-8 hover:border-[#f3a600]/50 hover:shadow-[0_0_25px_rgba(243,166,0,0.3)] transition-all duration-500 group rounded-2xl">
        <div class="flex items-start gap-4 md:gap-5 mb-6">
            <div
                class="w-10 h-10 md:w-12 md:h-12 bg-[#f3a600]/10 border border-[#f3a600]/30 flex items-center justify-center text-[#f3a600] shrink-0 rounded-xl">
                <i data-lucide="megaphone" class="w-5 h-5 md:w-6 md:h-6"></i>
            </div>
            <div>
                <h3 class="text-xl md:text-2xl font-bold text-white tracking-tight">
                    แหล่งทุนวิจัยภายนอก</h3>
                <p class="text-white/60 mt-1 text-sm md:text-base">เลือกแหล่งทุนวิจัยที่สนใจ
                    คลิกเพื่อเข้าสู่เว็บไซต์ของหน่วยงาน</p>
            </div>
        </div>

        <!-- Funding Sources Image Grid -->
        <?php
        $fundingSources = [
            ['img' => 'assets/img/1.png',  'url' => 'https://www.nrct.go.th/',           'name' => 'วช.'],
            ['img' => 'assets/img/2.png',  'url' => 'https://www.nia.or.th/',            'name' => 'NIA'],
            ['img' => 'assets/img/3.png',  'url' => 'https://www.arda.or.th/',           'name' => 'ARDA'],
            ['img' => 'assets/img/4.png',  'url' => 'https://www.hsri.or.th/',           'name' => 'สวรส.'],
            ['img' => 'assets/img/5.png',  'url' => 'https://pmu-hr.or.th/',             'name' => 'บพค.'],
            ['img' => 'assets/img/6.png',  'url' => 'https://pmuc.or.th/',               'name' => 'บพข.'],
            ['img' => 'assets/img/7.png',  'url' => 'https://pmua.or.th/',               'name' => 'บพท.'],
            ['img' => 'assets/img/8.png',  'url' => 'https://nvi.go.th/',                'name' => 'NVI'],
            ['img' => 'assets/img/9.png',  'url' => 'https://www.tcels.or.th/home',      'name' => 'TCELS'],
            ['img' => 'assets/img/10.png', 'url' => 'https://www.ops.go.th/th/',         'name' => 'สวทช.'],
            ['img' => 'assets/img/11.png', 'url' => 'https://nriis.go.th/Login.aspx',    'name' => 'NRIIS'],
            ['img' => 'assets/img/12.png', 'url' => 'https://www.tsri.or.th/',           'name' => 'สกสว.'],
            ['img' => 'assets/img/13.png', 'url' => 'https://www.nxpo.or.th/th/',        'name' => 'สอวช.'],
        ];
        ?>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 rounded-xl overflow-hidden border border-white/10">
            <?php foreach ($fundingSources as $source): ?>
                <a href="<?php echo $source['url']; ?>" target="_blank" rel="noopener"
                    class="group/card bg-white flex items-center justify-center p-2 overflow-hidden border-r border-b border-gray-100 last:border-r-0 hover:bg-[#fffbe6] transition-all duration-300">
                    <img src="<?php echo $source['img']; ?>"
                        alt="<?php echo htmlspecialchars($source['name']); ?>"
                        class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover/card:scale-110">
                </a>
            <?php endforeach; ?>
            <!-- Fill remaining empty cell with white -->
            <div class="bg-white border-b border-gray-100"></div>
        </div>

        <!-- Divider -->
        <div class="border-t border-white/10 my-8"></div>

        <!-- 3D Infinite Gallery Integration -->
        <div class="flex items-start gap-4 md:gap-5 mb-4">
            <div
                class="w-10 h-10 md:w-12 md:h-12 bg-[#f3a600]/10 border border-[#f3a600]/30 flex items-center justify-center text-[#f3a600] shrink-0 rounded-xl">
                <i data-lucide="newspaper" class="w-5 h-5 md:w-6 md:h-6"></i>
            </div>
            <div>
                <h3 class="text-xl md:text-2xl font-bold text-white tracking-tight">
                    ข่าวประชาสัมพันธ์ทุนวิจัย</h3>
                <p class="text-white/60 mt-1 text-sm md:text-base">อัปเดตข่าวสารและประกาศสำคัญจากแหล่งทุน</p>
            </div>
        </div>

        <div id="pr-gallery-wrapper"
            class="relative w-full h-[500px] overflow-hidden rounded-xl border border-white/5">
            <div id="canvas-container" class="absolute inset-0 w-full h-full z-[1]"></div>

            <!-- Floating UI Layer — slides are rendered by JS from API -->
            <div id="ui-layer" class="absolute inset-0 w-full h-full z-[2] pointer-events-none">
                <div id="pr-slides-container"></div>

                <!-- Scroll Hint -->
                <div
                    class="absolute bottom-[20px] left-[5%] right-0 text-center md:text-left md:left-[8%] text-[10px] text-white/40 uppercase tracking-[2px] pointer-events-none">
                    <i class="fa-solid fa-arrows-left-right mr-1"></i>
                    เลื่อนเมาส์หรือปัดหน้าจอเพื่อไปยังข่าวถัดไป
                </div>
            </div>
        </div>
    </div>
</div>
