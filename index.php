<!DOCTYPE html>
<html lang="th">

<head>
    <?php require_once 'includes/header_scripts.php'; ?>
</head>

<body class="min-h-screen font-sans overflow-x-hidden selection:bg-fuchsia-500 selection:text-white"
    style="background-color: var(--dark-bg); color: var(--text-primary);">

    <!-- Background Design สำหรับงานวิจัย -->
    <div class="fixed inset-0 z-[-1]" style="background-color: var(--dark-bg);">
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 opacity-20" style="background-image: var(--primary-gradient);"></div>
        <!-- Scientific Dot Pattern (ลายจุดตารางสไตล์วิทยาศาสตร์/ข้อมูล) -->
        <div
            class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.05)_1px,transparent_1px)] [background-size:32px_32px]">
        </div>

        <!-- Modern Glow Effects (แสงออร่าล้ำๆ สีม่วงมุมซ้ายบน) -->
        <div class="absolute top-[-15%] left-[-10%] w-[500px] h-[500px] rounded-full mix-blend-screen filter blur-[130px] opacity-[0.35] animate-pulse"
            style="background-color: var(--accent-purple);">
        </div>

        <!-- Modern Glow Effects (แสงออร่าล้ำๆ สีชมพูมุมขวาล่าง พร้อมดีเลย์ให้กระพริบสลับกัน) -->
        <div class="absolute bottom-[-15%] right-[-10%] w-[600px] h-[600px] rounded-full mix-blend-screen filter blur-[140px] opacity-[0.25] animate-pulse"
            style="animation-delay: 2s; background-color: var(--accent-pink);"></div>
    </div>

    <div class="min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <?php require_once 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <main
        class="pt-28 pb-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto flex flex-col items-center justify-center w-full flex-grow">

        <!-- Header Text -->
        <?php require_once 'includes/hero_section.php'; ?>

        <!-- Fragmented Tabs Section -->
        <div class="w-full max-w-full px-4 lg:px-8 mx-auto mt-4 mb-20 relative z-10 reveal">
            <!-- Gemini Animated Wrapper -->
            <div class="gemini-tab-wrapper mb-8">
                <!-- Tabs Nav (Button Style with Sliding Indicator) -->
                <div class="flex flex-wrap w-full gap-1 p-2 bg-[#0a0a0f] rounded-2xl relative shadow-[0_8px_32px_rgba(0,0,0,0.4)] border border-white/10 backdrop-blur-md"
                    id="customTabsNav" role="tablist">

                    <!-- Tab Indicator -->
                    <div class="absolute top-2 h-[calc(100%-16px)] bg-gradient-to-br from-[#22d3ee] to-[#6366f1] rounded-xl transition-all duration-500 ease-out shadow-[0_4px_24px_rgba(34,211,238,0.4),0_0_48px_rgba(99,102,241,0.2),inset_0_1px_0_rgba(255,255,255,0.2)] pointer-events-none z-0"
                        id="tabIndicator" aria-hidden="true"></div>

                    <button
                        class="custom-tab-btn flex-1 min-w-[30%] md:min-w-0 py-3 md:py-4 px-2 md:px-4 bg-transparent text-white/50 font-semibold text-xs md:text-sm lg:text-base rounded-xl transition-all duration-300 relative z-10 flex items-center justify-center gap-2 outline-none focus-visible:ring-2 focus-visible:ring-[#6366f1] group aria-selected:text-[#080810] hover:not([aria-selected='true']):text-white/70 hover:not([aria-selected='true']):bg-white/5"
                        role="tab" aria-selected="true" aria-controls="panel-overview" id="tab-overview"
                        data-tab="overview">
                        <i data-lucide="layout-dashboard"
                            class="w-4 h-4 md:w-5 md:h-5 shrink-0 transition-transform duration-150 group-hover:scale-110"></i>
                        <span>ภาพรวม</span>
                    </button>

                    <button
                        class="custom-tab-btn flex-1 min-w-[30%] md:min-w-0 py-3 md:py-4 px-2 md:px-4 bg-transparent text-white/50 font-semibold text-xs md:text-sm lg:text-base rounded-xl transition-all duration-300 relative z-10 flex items-center justify-center gap-2 outline-none focus-visible:ring-2 focus-visible:ring-[#6366f1] group aria-selected:text-[#080810] hover:not([aria-selected='true']):text-white/70 hover:not([aria-selected='true']):bg-white/5"
                        role="tab" aria-selected="false" aria-controls="panel-pub" id="tab-pub" data-tab="pub">
                        <i data-lucide="book-open"
                            class="w-4 h-4 md:w-5 md:h-5 shrink-0 transition-transform duration-150 group-hover:scale-110"></i>
                        <span>ผลงานวิจัย</span>
                    </button>

                    <button
                        class="custom-tab-btn flex-1 min-w-[30%] md:min-w-0 py-3 md:py-4 px-2 md:px-4 bg-transparent text-white/50 font-semibold text-xs md:text-sm lg:text-base rounded-xl transition-all duration-300 relative z-10 flex items-center justify-center gap-2 outline-none focus-visible:ring-2 focus-visible:ring-[#6366f1] group aria-selected:text-[#080810] hover:not([aria-selected='true']):text-white/70 hover:not([aria-selected='true']):bg-white/5"
                        role="tab" aria-selected="false" aria-controls="panel-pr" id="tab-pr" data-tab="pr">
                        <i data-lucide="megaphone"
                            class="w-4 h-4 md:w-5 md:h-5 shrink-0 transition-transform duration-150 group-hover:scale-110"></i>
                        <span>ข่าวประชาสัมพันธ์</span>
                    </button>


                    <button
                        class="custom-tab-btn flex-1 min-w-[45%] md:min-w-0 py-3 md:py-4 px-2 md:px-4 bg-transparent text-white/50 font-semibold text-xs md:text-sm lg:text-base rounded-xl transition-all duration-300 relative z-10 flex items-center justify-center gap-2 outline-none focus-visible:ring-2 focus-visible:ring-[#6366f1] group aria-selected:text-[#080810] hover:not([aria-selected='true']):text-white/70 hover:not([aria-selected='true']):bg-white/5"
                        role="tab" aria-selected="false" aria-controls="panel-activities" id="tab-activities"
                        data-tab="activities">
                        <i data-lucide="calendar-days"
                            class="w-4 h-4 md:w-5 md:h-5 shrink-0 transition-transform duration-150 group-hover:scale-110"></i>
                        <span>กิจกรรมฝ่ายฯ</span>
                    </button>



                </div>
            </div>
            <!-- Tab Panels (Dynamic Height) -->
            <div class="relative w-full overflow-hidden min-h-[60vh]">
                <!-- Panel 0: Overview -->
                <?php require_once 'components/tab_overview.php'; ?>

                <!-- Panel 1: Publications -->
                <?php require_once 'components/tab_publications.php'; ?>

                <!-- Panel 2: PR -->
                <?php require_once 'components/tab_pr_gallery.php'; ?>


                <!-- Panel 4: Activities -->
                <?php require_once 'components/tab_activities.php'; ?>



            </div>
        </div>

    </main>




    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>
    </div>

    <!-- Modals -->
    <div id="loginModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0"
            id="loginContent">
            <div class="bg-[#1e3f5a] p-6 text-white text-center relative">
                <button onclick="closeLoginModal()"
                    class="absolute top-4 right-4 text-white/70 hover:text-white transition">
                    <i class="ph ph-x text-2xl"></i>
                </button>
                <div
                    class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-md border border-white/20">
                    <i class="ph ph-lock-key text-3xl text-[#f3a600]"></i>
                </div>
                <h3 class="text-xl font-bold tracking-wide">
                    เข้าสู่ระบบสารสนเทศ
                </h3>
                <p id="modalSystemName" class="text-[#f3a600] text-sm mt-1 font-medium">System Name</p>
            </div>

            <div class="p-8">
                <form onsubmit="handleLogin(event)" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            ชื่อผู้ใช้งาน
                        </label>
                        <input type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#1e3f5a] focus:border-transparent transition bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            รหัสผ่าน
                        </label>
                        <input type="password"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#1e3f5a] focus:border-transparent transition bg-gray-50">
                    </div>
                    <button type="submit"
                        class="w-full bg-[#1e3f5a] text-white py-3 rounded-lg hover:bg-[#152e43] transition font-bold shadow-lg hover:shadow-xl transform active:scale-95">
                        เข้าสู่ระบบ
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="imageModal"
        class="fixed inset-0 bg-black/95 z-[110] hidden flex items-center justify-center p-4 backdrop-blur-md transition-opacity duration-300 opacity-0"
        onclick="closeImageModal()">
        <button onclick="closeImageModal()"
            class="absolute top-6 right-6 text-white/70 hover:text-white transition z-50 bg-white/10 hover:bg-white/20 rounded-full p-2 w-12 h-12 flex items-center justify-center">
            <i class="ph ph-x text-2xl"></i>
        </button>
        <div class="relative max-w-7xl max-h-screen w-full flex items-center justify-center p-4"
            onclick="event.stopPropagation()">
            <img id="modalImage" src="" alt="Full Size"
                class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl transform transition-transform scale-95 opacity-0">
        </div>
    </div>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- JavaScript หล่อเลี้ยงหน้าและจัดการ Tabs/Animation -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Close Image Modal
        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            if (modal && modalImg) {
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0');
                modalImg.classList.remove('scale-100', 'opacity-100');
                modalImg.classList.add('scale-95', 'opacity-0');
                setTimeout(() => { modal.classList.add('hidden'); }, 300);
            }
        }

        // Puzzle interaction logic (Vanilla JS)
        const pieces = [
            { id: 'piece-tl', hoverTransform: 'translate(-12px, -12px)' },
            { id: 'piece-tr', hoverTransform: 'translate(12px, -12px)' },
            { id: 'piece-br', hoverTransform: 'translate(12px, 12px)' },
            { id: 'piece-bl', hoverTransform: 'translate(-12px, 12px)' }
        ];

        pieces.forEach(piece => {
            const element = document.getElementById(piece.id);
            if (element) {
                // เมื่อเอาเมาส์เข้าไปชี้ ให้ขยับตามระยะที่กำหนด
                element.addEventListener('mouseenter', () => {
                    element.style.transform = piece.hoverTransform;
                });

                // เมื่อเอาเมาส์ออก ให้กลับไปที่เดิม (0,0)
                element.addEventListener('mouseleave', () => {
                    element.style.transform = 'translate(0px, 0px)';
                });
            }
        });


        // Tab Logic with Sliding Indicator
        document.addEventListener('DOMContentLoaded', () => {
            const tabsNav = document.getElementById('customTabsNav');
            const tabIndicator = document.getElementById('tabIndicator');
            const tabBtns = document.querySelectorAll('.custom-tab-btn');
            const tabPanels = document.querySelectorAll('.custom-tab-panel');
            const panelsContainer = tabPanels[0]?.parentElement;


            const updateIndicator = (tab) => {
                if (!tab || !tabIndicator || !tabsNav) return;

                const tabRect = tab.getBoundingClientRect();
                const navRect = tabsNav.getBoundingClientRect();
                const scrollLeft = tabsNav.scrollLeft;

                const left = tabRect.left - navRect.left + scrollLeft;
                const width = tabRect.width;

                tabIndicator.style.transform = `translateX(${left}px)`;
                tabIndicator.style.width = `${width}px`;
            };

            // Call initially and on resize
            setTimeout(() => {
                const activeTab = document.querySelector('.custom-tab-btn[aria-selected="true"]');
                if (activeTab) updateIndicator(activeTab);
            }, 100);

            window.addEventListener('resize', () => {
                const activeTab = document.querySelector('.custom-tab-btn[aria-selected="true"]');
                if (activeTab) updateIndicator(activeTab);
            });

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Reset all tabs
                    tabBtns.forEach(b => {
                        b.setAttribute('aria-selected', 'false');
                    });

                    // Hide all panels
                    tabPanels.forEach(p => {
                        if (p.getAttribute('aria-hidden') === 'false') {
                            p.setAttribute('aria-hidden', 'true');
                            p.classList.remove('opacity-100', 'translate-y-0', 'scale-100', 'z-10', 'delay-150', 'relative');
                            p.classList.add('opacity-0', 'translate-y-4', 'scale-95', 'z-0', 'pointer-events-none', 'absolute', 'inset-x-0', 'top-0');
                        } else {
                            // Pre-stage hidden panels to come out from bottom next
                            p.classList.remove('translate-y-4', 'scale-95');
                            p.classList.add('translate-y-8');
                        }
                    });

                    // Activate clicked tab
                    btn.setAttribute('aria-selected', 'true');
                    const panelId = btn.getAttribute('aria-controls');
                    const activePanel = document.getElementById(panelId);

                    // Show clicked panel with slight delay yielding an elegant sequence
                    if (activePanel) {
                        // Force DOM reflow to restart animations
                        void activePanel.offsetWidth;
                        activePanel.setAttribute('aria-hidden', 'false');
                        activePanel.classList.remove('opacity-0', 'translate-y-8', 'translate-y-4', 'scale-95', 'z-0', 'pointer-events-none', 'absolute', 'inset-x-0', 'top-0');
                        activePanel.classList.add('opacity-100', 'translate-y-0', 'scale-100', 'z-10', 'delay-150', 'relative');
                    }

                    updateIndicator(btn);
                });
            });
        });
        // -------------------------------------------------------------
        // 3D Donut Chart Logic (Overview Tab)
        // -------------------------------------------------------------
        document.addEventListener('DOMContentLoaded', () => {
            const chartData = [];
            let totalValue = 0;

            const cx = 50;
            const cy = 50;
            const radius = 38;
            const strokeWidth = 10;
            const gapPadding = 8;
            const depth = 25;
            const layerSpacing = 0.8;
            const yOffset = -(depth * layerSpacing) / 2;

            function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
                const angleInRadians = (angleInDegrees - 90) * Math.PI / 180.0;
                return {
                    x: centerX + (radius * Math.cos(angleInRadians)),
                    y: centerY + (radius * Math.sin(angleInRadians))
                };
            }

            function describeArc(x, y, radius, startAngle, endAngle) {
                const start = polarToCartesian(x, y, radius, endAngle);
                const end = polarToCartesian(x, y, radius, startAngle);
                const largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";
                return [
                    "M", start.x, start.y,
                    "A", radius, radius, 0, largeArcFlag, 0, end.x, end.y
                ].join(" ");
            }

            window.highlightChart = function (index) {
                if (!chartData[index]) return;
                const item = chartData[index];
                document.getElementById('center-label').textContent = item.label;
                const valueEl = document.getElementById('center-value');
                valueEl.textContent = item.value;
                valueEl.style.color = item.colorHex;
                valueEl.style.transform = 'scale(1.1)';

                chartData.forEach((_, i) => {
                    const pieces = document.querySelectorAll(`.slice-piece-${i}`);
                    if (i === index) {
                        pieces.forEach(p => {
                            p.style.setProperty('--hover-offset', `-12px`);
                            if (p.getAttribute('data-istop') === 'true') {
                                p.style.filter = `drop-shadow(0px 15px 15px ${item.colorHex}70) brightness(1.2)`;
                            } else {
                                p.style.filter = `brightness(0.6)`;
                            }
                            p.style.opacity = '1';
                        });
                    } else {
                        pieces.forEach(p => { p.style.opacity = '0.2'; });
                    }
                });
            };

            window.resetChart = function () {
                document.getElementById('center-label').textContent = 'ยอดรวมผลงานวิจัย';
                const valueEl = document.getElementById('center-value');
                valueEl.textContent = totalValue;
                valueEl.style.color = 'white';
                valueEl.style.transform = 'scale(1)';

                chartData.forEach((_, i) => {
                    const pieces = document.querySelectorAll(`.slice-piece-${i}`);
                    pieces.forEach(p => {
                        p.style.setProperty('--hover-offset', `0px`);
                        p.style.opacity = '1';
                        if (p.getAttribute('data-istop') === 'true') {
                            p.style.filter = 'none';
                        } else {
                            p.style.filter = 'brightness(0.35)';
                        }
                    });
                });
            };

            async function loadChartData() {
                try {
                    const response = await fetch('api/get_publication_stats.php');
                    const result = await response.json();

                    const legendContainer = document.getElementById('legend-container');
                    const slicesContainer = document.getElementById('donut-slices');
                    const bgContainer = document.getElementById('donut-bg');
                    const svgContainer = document.querySelector('.chart-3d-wrapper svg');

                    if (result.status === 'success') {
                        legendContainer.innerHTML = '';
                        // Transform DB data into chart logic format
                        result.data.forEach((row, i) => {
                            chartData.push({
                                label: row.category,
                                value: parseInt(row.value),
                                gradientBase: row.color_start,
                                gradientEnd: row.color_end,
                                colorHex: row.color_start // Base color for UI elements
                            });

                            // Inject Gradients dynamically into SVG Defs
                            const defs = svgContainer.querySelector('defs') || document.createElementNS("http://www.w3.org/2000/svg", "defs");
                            if (!svgContainer.querySelector('defs')) svgContainer.insertBefore(defs, svgContainer.firstChild);

                            const grad = document.createElementNS("http://www.w3.org/2000/svg", "linearGradient");
                            grad.setAttribute("id", `grad-dynamic-${i}`);
                            grad.setAttribute("x1", "0%"); grad.setAttribute("y1", "0%");
                            grad.setAttribute("x2", "100%"); grad.setAttribute("y2", "100%");

                            const stop1 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                            stop1.setAttribute("offset", "0%"); stop1.setAttribute("stop-color", row.color_start);
                            const stop2 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                            stop2.setAttribute("offset", "100%"); stop2.setAttribute("stop-color", row.color_end);

                            grad.appendChild(stop1); grad.appendChild(stop2);
                            defs.appendChild(grad);
                        });

                        totalValue = chartData.reduce((sum, item) => sum + item.value, 0);

                        // Draw background
                        for (let layer = depth; layer >= 0; layer--) {
                            const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                            circle.setAttribute("cx", cx);
                            circle.setAttribute("cy", cy);
                            circle.setAttribute("r", radius);
                            circle.setAttribute("fill", "none");
                            circle.setAttribute("stroke", layer === 0 ? "#1e293b" : "#020617");
                            circle.setAttribute("stroke-width", strokeWidth);
                            circle.style.setProperty('--layer-offset', `${layer * layerSpacing + yOffset}px`);
                            circle.style.setProperty('--hover-offset', `0px`);
                            circle.style.transform = `translateY(calc(var(--layer-offset) + var(--hover-offset)))`;
                            bgContainer.appendChild(circle);
                        }

                        // Calculate Arcs
                        const sliceArcs = [];
                        let currentAngle = 0;
                        chartData.forEach(item => {
                            // If total is 0 or element 0, avoid NaN
                            const sliceAngle = totalValue > 0 ? (item.value / totalValue) * 360 : 0;
                            // small gap only if there is a value
                            const actualStartAngle = item.value > 0 ? currentAngle + (gapPadding / 2) : currentAngle;
                            const actualEndAngle = item.value > 0 ? currentAngle + sliceAngle - (gapPadding / 2) : currentAngle;

                            // fallback for perfectly empty charts or zero value slices
                            let d = "";
                            if (item.value > 0 && sliceAngle >= 1) {
                                d = describeArc(cx, cy, radius, actualStartAngle, actualEndAngle);
                            }

                            sliceArcs.push({ item, d });
                            currentAngle += sliceAngle;
                        });

                        // Draw Slices
                        for (let layer = depth; layer >= 0; layer--) {
                            sliceArcs.forEach((slice, index) => {
                                if (!slice.d) return; // Skip drawing empty slices

                                const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                                path.setAttribute("d", slice.d);
                                path.setAttribute("fill", "none");
                                path.setAttribute("stroke", `url(#grad-dynamic-${index})`);
                                path.setAttribute("stroke-width", strokeWidth);
                                path.setAttribute("stroke-linecap", "round");

                                const isTop = layer === 0;
                                path.setAttribute("data-istop", isTop);
                                path.classList.add(isTop ? "slice-top" : "slice-side");
                                path.classList.add(`slice-piece-${index}`, "slice-path", "animate-draw");

                                const length = 250;
                                path.setAttribute("stroke-dasharray", length);
                                path.setAttribute("stroke-dashoffset", length);

                                path.style.setProperty('--layer-offset', `${layer * layerSpacing + yOffset}px`);
                                path.style.setProperty('--hover-offset', `0px`);
                                path.style.transform = `translateY(calc(var(--layer-offset) + var(--hover-offset)))`;

                                path.addEventListener('mouseenter', () => window.highlightChart(index));
                                path.addEventListener('mouseleave', () => window.resetChart());

                                slicesContainer.appendChild(path);
                            });
                        }

                        // Build Legends
                        chartData.forEach((item, index) => {
                            const percentage = totalValue > 0 ? Math.round((item.value / totalValue) * 100) : 0;
                            const legendHtml = `
                                <div class="legend-item flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5 cursor-pointer hover:bg-white/10 transition-colors group"
                                     onmouseenter="highlightChart(${index})" onmouseleave="resetChart()">
                                    <div class="flex items-center gap-4 w-2/3">
                                        <div class="w-3 h-3 rounded-full shrink-0" style="background-color: ${item.colorHex}; box-shadow: 0 0 10px ${item.colorHex}80"></div>
                                        <span class="text-white/80 font-medium text-sm md:text-base leading-tight group-hover:text-white transition-colors line-clamp-2">${item.label}</span>
                                    </div>
                                    <div class="flex items-center gap-4 text-right">
                                        <span class="text-white/40 text-xs md:text-sm w-8">${percentage}%</span>
                                        <span class="text-white font-bold w-12 md:text-lg">${item.value}</span>
                                    </div>
                                </div>
                            `;
                            legendContainer.insertAdjacentHTML('beforeend', legendHtml);
                        });

                        window.resetChart(); // Ensure initial center text is set
                    } else {
                        legendContainer.innerHTML = '<div class="text-red-400">Failed to load statistics.</div>';
                    }
                } catch (err) {
                    console.error("Error loading chart data", err);
                    document.getElementById('legend-container').innerHTML = '<div class="text-red-400">Error fetching data.</div>';
                }
            }

            // Only load chart if the container exists (prevent double execution or errors on pages without this tab)
            if (document.getElementById('donut-slices')) {
                loadChartData();
            }
        });

        // -------------------------------------------------------------
        // Fetch Publication Records for Tab 2
        // -------------------------------------------------------------
        document.addEventListener('DOMContentLoaded', () => {
            const pubContainer = document.getElementById('publication-records-container');
            if (!pubContainer) return;

            async function loadPublicationRecords() {
                try {
                    const response = await fetch('api/get_publication_records.php?limit=4');
                    const result = await response.json();

                    pubContainer.innerHTML = ''; // clear loading state

                    if (result.status === 'error') {
                        pubContainer.innerHTML = `<div class="col-span-1 md:col-span-2 text-center text-red-500 py-10">API Error: ${result.message}</div>`;
                        return;
                    }

                    if (result.status === 'success' && result.data && result.data.length > 0) {
                        result.data.forEach(record => {
                            // Extract year (assume YYYY-MM-DD), fallback to string
                            const dateObj = new Date(record.published_date);
                            const yearDisplay = dateObj.getFullYear() + 543; // Thai Year

                            const html = `
                                <a href="${record.link || '#'}" target="${record.link ? '_blank' : '_self'}" class="group relative flex flex-col justify-between p-6 bg-white/5 border border-white/10 rounded-2xl overflow-hidden transition-all duration-500 hover:bg-white/10 hover:-translate-y-1 hover:shadow-[0_10px_40px_-15px_${record.color_code}80]" style="border-left-color: ${record.color_code}; border-left-width: 4px;">
                                    <div class="absolute inset-0 bg-gradient-to-br from-[${record.color_code}]/0 via-transparent to-[${record.color_code}]/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                                    <div class="relative z-10">
                                        <div class="flex items-start justify-between gap-4 mb-3">
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] md:text-xs font-bold uppercase tracking-wider rounded-md bg-[${record.color_code}]/10 border border-[${record.color_code}]/20 transition-colors" style="color: ${record.color_code}">
                                                ${record.category_name}
                                            </span>
                                            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-[${record.color_code}] opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-500">
                                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                            </div>
                                        </div>
                                        <h4 class="text-white font-semibold text-sm md:text-base leading-snug group-hover:text-[${record.color_code}] transition-colors duration-500 line-clamp-2 md:line-clamp-3">${record.title}</h4>
                                    </div>
                                    <div class="relative z-10 pt-4 mt-4 border-t border-white/5 space-y-2">
                                        <p class="text-white/60 text-xs flex items-center gap-2"><i data-lucide="user" class="w-3.5 h-3.5 opacity-70"></i> <span class="truncate">${record.author}</span></p>
                                        <p class="text-white/40 text-[10px] flex items-center gap-2"><i data-lucide="calendar" class="w-3.5 h-3.5 opacity-70"></i> วันที่เผยแพร่: ${record.published_date} (ปี ${yearDisplay})</p>
                                    </div>
                                </a>
                            `;
                            pubContainer.insertAdjacentHTML('beforeend', html);
                        });

                        // Render the newly injected Lucide icons
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    } else {
                        pubContainer.innerHTML = '<div class="col-span-1 md:col-span-2 text-center text-white/50 py-10">ไม่พบข้อมูลผลงานวิจัย</div>';
                    }
                } catch (err) {
                    console.error("Error loading publication records", err);
                    pubContainer.innerHTML = '<div class="col-span-1 md:col-span-2 text-center text-red-400 py-10">เกิดข้อผิดพลาดในการดึงข้อมูล</div>';
                }
            }

            loadPublicationRecords();
        });

        // -------------------------------------------------------------
        // Three.js PR Infinite Gallery (Tab 3) — Dynamic from API
        // -------------------------------------------------------------
        document.addEventListener('DOMContentLoaded', async () => {
            const wrapper = document.getElementById('pr-gallery-wrapper');
            const canvasContainer = document.getElementById('canvas-container');
            const slidesContainer = document.getElementById('pr-slides-container');
            if (!wrapper || !canvasContainer) return;

            // Fetch PR news from API
            let prNewsData = [];
            let images = [];
            try {
                const res = await fetch('api/api_pr_news.php');
                const json = await res.json();
                if (json.success && json.data.length > 0) {
                    prNewsData = json.data;
                    images = prNewsData.map(item => item.image_path ? item.image_path : 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800&q=80');
                }
            } catch (e) { console.warn('PR News API error:', e); }

            // Fallback: if no data from API, use placeholder
            if (prNewsData.length === 0) {
                prNewsData = [
                    { title: 'เปิดรับข้อเสนอ\nโครงการวิจัย', description: 'กรุณาเพิ่มข่าวประชาสัมพันธ์ผ่านหน้า Admin Panel', published_date_thai: '-', link_url: '' }
                ];
                images = ['https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800&q=80'];
            }

            // Generate slide HTML dynamically
            if (slidesContainer) {
                prNewsData.forEach((item, i) => {
                    const slideDiv = document.createElement('div');
                    slideDiv.className = 'slide-content absolute top-[15%] left-[5%] w-[65%] md:w-[50%] max-w-[420px] opacity-0 translate-y-5 transition-all duration-700 ease-out pointer-events-auto';
                    slideDiv.id = `slide-${i}`;
                    slideDiv.innerHTML = `
                        <div class="bg-[#7c3aed]/40 backdrop-blur-sm rounded-xl p-5 md:p-6" style="text-shadow: 0 2px 8px rgba(0,0,0,0.8);">
                            <span class="text-xs text-[#f3a600] uppercase tracking-[3px] border-b border-[#f3a600]/30 pb-1 mb-3 inline-block">ประกาศเมื่อ: ${item.published_date_thai || '-'}</span>
                            <h1 class="font-extrabold text-xl md:text-2xl text-white mb-3 leading-tight">${(item.title || '').replace(/\n/g, '<br>')}</h1>
                            <div class="text-sm text-white/80 mb-5 leading-relaxed">${item.description || ''}</div>
                            ${item.link_url ? `<a href="${item.link_url}" target="_blank" class="px-5 py-2.5 bg-[#f3a600] hover:bg-[#d99400] text-black font-bold text-sm rounded-lg transition-colors inline-block" style="text-shadow:none;">อ่านรายละเอียด</a>` : ''}
                        </div>
                    `;
                    slidesContainer.appendChild(slideDiv);
                });
            }

            // Gallery Configuration
            const slideCount = Math.max(prNewsData.length, 1);
            const CONFIG = {
                slideCount: slideCount,
                spacingX: 45,
                pWidth: 18,
                pHeight: 12,
                camZ: 30,
                wallAngleY: -0.25,
                snapDelay: 200,
                lerpSpeed: 0.06
            };
            const totalGalleryWidth = CONFIG.slideCount * CONFIG.spacingX;

            // Three.js Setup
            const scene = new THREE.Scene();

            const getAspect = () => wrapper.clientWidth / wrapper.clientHeight;
            const camera = new THREE.PerspectiveCamera(45, getAspect(), 0.1, 1000);
            camera.position.set(0, 0, CONFIG.camZ);

            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(wrapper.clientWidth, wrapper.clientHeight);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            canvasContainer.appendChild(renderer.domElement);

            const ambient = new THREE.AmbientLight(0xffffff, 0.6);
            scene.add(ambient);
            const dirLight = new THREE.DirectionalLight(0xffffff, 0.5);
            dirLight.position.set(10, 20, 10);
            scene.add(dirLight);

            const galleryGroup = new THREE.Group();
            scene.add(galleryGroup);

            const textureLoader = new THREE.TextureLoader();
            const planeGeo = new THREE.PlaneGeometry(CONFIG.pWidth, CONFIG.pHeight);
            const paintingGroups = [];

            for (let i = 0; i < CONFIG.slideCount; i++) {
                const group = new THREE.Group();
                group.position.set(i * CONFIG.spacingX, 0, 0);

                const baseMat = new THREE.MeshBasicMaterial({ color: 0x222222 });
                const mesh = new THREE.Mesh(planeGeo, baseMat);

                textureLoader.load(images[i], (tex) => {
                    mesh.material.color.setHex(0xffffff);
                    mesh.material.map = tex;
                    mesh.material.needsUpdate = true;
                });

                const edges = new THREE.EdgesGeometry(planeGeo);
                const outline = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: 0x444444 }));

                const shadowGeo = new THREE.PlaneGeometry(CONFIG.pWidth, CONFIG.pHeight);
                const shadowMat = new THREE.MeshBasicMaterial({ color: 0x000000, transparent: true, opacity: 0.5 });
                const shadow = new THREE.Mesh(shadowGeo, shadowMat);
                shadow.position.set(0.8, -0.8, -0.5);

                const lineZ = -1;
                const lineLen = CONFIG.spacingX;
                const lineGeo = new THREE.BufferGeometry().setFromPoints([
                    new THREE.Vector3(-lineLen / 2, 14, lineZ), new THREE.Vector3(lineLen / 2, 14, lineZ),
                    new THREE.Vector3(-lineLen / 2, -14, lineZ), new THREE.Vector3(lineLen / 2, -14, lineZ)
                ]);
                const lines = new THREE.LineSegments(lineGeo, new THREE.LineBasicMaterial({ color: 0x333333 }));

                group.add(shadow);
                group.add(mesh);
                group.add(outline);
                group.add(lines);

                galleryGroup.add(group);
                paintingGroups.push(group);
            }

            galleryGroup.rotation.y = CONFIG.wallAngleY;
            galleryGroup.position.x = 14;

            // --- Hover to preview image ---
            const raycaster = new THREE.Raycaster();
            const hoverMouse = new THREE.Vector2();

            const previewEl = document.createElement('div');
            previewEl.id = 'pr-hover-preview';
            previewEl.style.cssText = 'position:absolute; bottom:20px; right:20px; width:220px; height:280px; z-index:30; pointer-events:none; opacity:0; transform:translateY(10px) scale(0.95); transition: opacity 0.4s ease, transform 0.4s ease; border-radius:12px; overflow:hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.7); border: 2px solid rgba(255,255,255,0.15);';
            const previewImg = document.createElement('img');
            previewImg.style.cssText = 'width:100%; height:100%; object-fit:cover;';
            previewEl.appendChild(previewImg);
            wrapper.appendChild(previewEl);

            let lastHoveredIndex = -1;

            wrapper.addEventListener('mousemove', (e) => {
                const rect = wrapper.getBoundingClientRect();
                hoverMouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
                hoverMouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
                raycaster.setFromCamera(hoverMouse, camera);

                const meshes = paintingGroups.map(g => g.children[1]);
                const intersects = raycaster.intersectObjects(meshes);

                if (intersects.length > 0) {
                    const hitMesh = intersects[0].object;
                    const idx = paintingGroups.findIndex(g => g.children[1] === hitMesh);
                    if (idx !== -1 && idx !== lastHoveredIndex) {
                        lastHoveredIndex = idx;
                        previewImg.src = images[idx];
                        previewEl.style.opacity = '1';
                        previewEl.style.transform = 'translateY(0) scale(1)';
                        wrapper.style.cursor = 'pointer';
                    }
                } else {
                    if (lastHoveredIndex !== -1) {
                        lastHoveredIndex = -1;
                        previewEl.style.opacity = '0';
                        previewEl.style.transform = 'translateY(10px) scale(0.95)';
                        wrapper.style.cursor = 'default';
                    }
                }
            });

            wrapper.addEventListener('mouseleave', () => {
                lastHoveredIndex = -1;
                previewEl.style.opacity = '0';
                previewEl.style.transform = 'translateY(10px) scale(0.95)';
                wrapper.style.cursor = 'default';
            });

            // Click on gallery to open full image
            wrapper.addEventListener('click', () => {
                if (lastHoveredIndex !== -1 && images[lastHoveredIndex]) {
                    const modal = document.getElementById('imageModal');
                    const modalImg = document.getElementById('modalImage');
                    if (modal && modalImg) {
                        modalImg.src = images[lastHoveredIndex];
                        modal.classList.remove('hidden');
                        setTimeout(() => {
                            modal.classList.remove('opacity-0');
                            modal.classList.add('opacity-100');
                            modalImg.classList.remove('scale-95', 'opacity-0');
                            modalImg.classList.add('scale-100', 'opacity-100');
                        }, 50);
                    }
                }
            });

            // Scroll Logic
            let currentScroll = 0;
            let targetScroll = 0;
            let snapTimer = null;
            let mouse = { x: 0, y: 0 };
            let isTabVisible = false;

            function snapToNearest() {
                const index = Math.round(targetScroll / CONFIG.spacingX);
                targetScroll = index * CONFIG.spacingX;
            }

            wrapper.addEventListener('wheel', (e) => {
                e.preventDefault();
                targetScroll += e.deltaY * 0.1;
                if (snapTimer) clearTimeout(snapTimer);
                snapTimer = setTimeout(snapToNearest, CONFIG.snapDelay);
            });

            let touchStart = 0;
            wrapper.addEventListener('touchstart', e => {
                touchStart = e.touches[0].clientX;
                if (snapTimer) clearTimeout(snapTimer);
            });
            wrapper.addEventListener('touchmove', e => {
                e.preventDefault();
                const diff = touchStart - e.touches[0].clientX;
                targetScroll += diff * 0.6;
                touchStart = e.touches[0].clientX;
                if (snapTimer) clearTimeout(snapTimer);
            });
            wrapper.addEventListener('touchend', () => { snapToNearest(); });

            // UI updating logic
            function updateUI(scrollX) {
                const rawIndex = Math.round(scrollX / CONFIG.spacingX);
                const safeIndex = ((rawIndex % CONFIG.slideCount) + CONFIG.slideCount) % CONFIG.slideCount;
                for (let i = 0; i < CONFIG.slideCount; i++) {
                    const el = document.getElementById(`slide-${i}`);
                    if (el) {
                        if (i === safeIndex) el.classList.add('active');
                        else el.classList.remove('active');
                    }
                }
            }

            // Animation Loop
            let animationFrameId;
            let isHovering = false;

            wrapper.addEventListener('mouseenter', () => { isHovering = true; });
            wrapper.addEventListener('mouseleave', () => { isHovering = false; });

            const scaleTargets = paintingGroups.map(() => 1.0);
            const currentScales = paintingGroups.map(() => 1.0);
            // Rotation targets: 0 = default (follows gallery angle), positive = rotate to face camera
            const rotTargets = paintingGroups.map(() => 0);
            const currentRots = paintingGroups.map(() => 0);

            function animate() {
                if (isTabVisible) {
                    currentScroll += (targetScroll - currentScroll) * CONFIG.lerpSpeed;

                    const xMove = currentScroll * Math.cos(CONFIG.wallAngleY);
                    const zMove = currentScroll * Math.sin(CONFIG.wallAngleY);

                    camera.position.x = xMove;
                    camera.position.z = CONFIG.camZ - zMove;

                    const rawIndex = Math.round(currentScroll / CONFIG.spacingX);
                    const activeIndex = ((rawIndex % CONFIG.slideCount) + CONFIG.slideCount) % CONFIG.slideCount;

                    paintingGroups.forEach((group, i) => {
                        const originalX = i * CONFIG.spacingX;
                        const distFromCam = currentScroll - originalX;
                        const shift = Math.round(distFromCam / totalGalleryWidth) * totalGalleryWidth;
                        group.position.x = originalX + shift;

                        // Hover: scale up hovered painting, rotate to face camera
                        if (isHovering && lastHoveredIndex === i) {
                            scaleTargets[i] = 1.35;
                            rotTargets[i] = -CONFIG.wallAngleY; // compensate gallery angle → face camera
                        } else if (isHovering && i === activeIndex) {
                            scaleTargets[i] = 1.1;
                            rotTargets[i] = 0;
                        } else {
                            scaleTargets[i] = isHovering ? 0.88 : 1.0;
                            rotTargets[i] = 0;
                        }

                        // Smooth lerp scale
                        currentScales[i] += (scaleTargets[i] - currentScales[i]) * 0.06;
                        group.scale.set(currentScales[i], currentScales[i], 1);

                        // Smooth lerp rotation
                        currentRots[i] += (rotTargets[i] - currentRots[i]) * 0.08;
                        group.rotation.y = currentRots[i];

                        // Fade non-active paintings (blur-like effect)
                        const targetOpacity = (i === activeIndex) ? 1.0 : 0.3;
                        const imgMesh = group.children[1]; // the image mesh
                        if (imgMesh && imgMesh.material) {
                            imgMesh.material.transparent = true;
                            imgMesh.material.opacity += (targetOpacity - imgMesh.material.opacity) * 0.06;
                        }
                    });

                    camera.rotation.x = mouse.y * 0.05;
                    camera.rotation.y = -mouse.x * 0.05;

                    updateUI(currentScroll);
                    renderer.render(scene, camera);
                }
                animationFrameId = requestAnimationFrame(animate);
            }

            // Resize Observer
            const resizeObserver = new ResizeObserver(entries => {
                for (let entry of entries) {
                    const { width, height } = entry.contentRect;
                    if (width > 0 && height > 0) {
                        isTabVisible = true;
                        camera.aspect = width / height;
                        camera.updateProjectionMatrix();
                        renderer.setSize(width, height);
                    } else {
                        isTabVisible = false;
                    }
                }
            });

            resizeObserver.observe(wrapper);
            animate();
        });

        // -------------------------------------------------------------
        // Scroll Reveal with IntersectionObserver
        // -------------------------------------------------------------
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    </script>
</body>

</html>
