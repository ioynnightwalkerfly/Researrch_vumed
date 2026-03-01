<!-- Standalone Donut Chart Section -->
<div class="w-full max-w-7xl mx-auto px-4 lg:px-8 mb-12 reveal">
    <div class="bg-white/5 border border-white/10 p-6 md:p-8 rounded-2xl hover:border-[#6366f1]/30 transition-colors">

        <!-- 3D Donut Chart Integration -->
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16 w-full pt-4 lg:pt-8">

            <!-- Chart 3D -->
            <div
                class="relative w-72 h-72 md:w-[26rem] md:h-[26rem] flex-shrink-0 perspective-container mx-auto lg:mx-0 mt-12 md:mt-16 lg:mt-6">
                <style>
                    .perspective-container {
                        perspective: 1200px;
                    }

                    .chart-3d-wrapper {
                        transform: rotateX(55deg) rotateZ(-15deg);
                        transform-style: preserve-3d;
                        transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
                        filter: drop-shadow(0px 30px 25px rgba(0, 0, 0, 0.7));
                    }

                    .chart-3d-wrapper:hover {
                        transform: rotateX(50deg) rotateZ(-10deg);
                    }

                    .slice-path {
                        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), filter 0.4s ease, opacity 0.4s ease;
                        cursor: pointer;
                    }

                    .slice-side {
                        filter: brightness(0.35);
                    }

                    .slice-top {
                        filter: drop-shadow(0px 0px 0px transparent);
                    }

                    .slice-path.animate-draw {
                        animation: drawLine 1.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
                    }

                    @keyframes drawLine {
                        to {
                            stroke-dashoffset: 0;
                        }
                    }

                    .center-text {
                        transition: opacity 0.3s ease, transform 0.3s ease;
                    }

                    .legend-item {
                        transition: opacity 0.2s ease, transform 0.2s ease;
                    }

                    .legend-item:hover {
                        transform: translateX(8px);
                        background-color: rgba(255, 255, 255, 0.1);
                    }
                </style>

                <!-- Center Text -->
                <div
                    class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none z-20 transform -translate-y-10 md:-translate-y-14">
                    <span id="center-label"
                        class="text-white/60 text-sm md:text-base uppercase tracking-widest font-semibold mb-1 center-text drop-shadow-md text-center px-4 line-clamp-2">ยอดรวมผลงานวิจัย</span>
                    <span id="center-value"
                        class="text-5xl md:text-6xl font-extrabold text-white center-text drop-shadow-lg">0</span>
                </div>

                <!-- 3D Wrapper -->
                <div class="w-full h-full chart-3d-wrapper">
                    <svg viewBox="0 0 100 100" class="w-full h-full overflow-visible">
                        <g id="donut-bg"></g>
                        <g id="donut-slices"></g>
                    </svg>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex-grow w-full z-10">
                <div class="mb-8 text-center lg:text-left">
                    <h3 class="text-2xl md:text-3xl font-bold text-white tracking-tight">
                        ภาพรวมผลงานวิจัยฝ่ายฯ</h3>
                    <p class="text-white/60 mt-2 text-base">สถิติการตีพิมพ์และนำเสนอผลงานวิชาการ</p>
                </div>

                <div id="legend-container"
                    class="space-y-4 max-h-[400px] overflow-y-auto pr-2 scrollbar-hide">
                    <div class="flex justify-center items-center py-10 text-white/50">
                        <i class="fa-solid fa-spinner fa-spin mr-3"></i> กำลังโหลดข้อมูล...
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
