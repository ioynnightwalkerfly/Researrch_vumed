<div class="custom-tab-panel absolute inset-x-0 top-0 opacity-0 translate-y-8 pointer-events-none transition-all duration-500 ease-out z-0"
    role="tabpanel" id="panel-activities" aria-labelledby="tab-activities" aria-hidden="true">
    <div
        class="bg-white/5 border border-white/10 p-6 md:p-8 hover:border-[#22c55e]/50 hover:shadow-[0_0_25px_rgba(34,197,94,0.3)] transition-all duration-500 group rounded-2xl">
        <div class="flex items-start gap-4 md:gap-5 mb-6">
            <div
                class="w-10 h-10 md:w-12 md:h-12 bg-[#22c55e]/10 border border-[#22c55e]/30 flex items-center justify-center text-[#22c55e] shrink-0 rounded-xl">
                <i data-lucide="calendar-days" class="w-5 h-5 md:w-6 md:h-6"></i>
            </div>
            <div>
                <h3 class="text-xl md:text-2xl font-bold text-white tracking-tight">กิจกรรมฝ่ายฯ</h3>
                <p class="text-white/60 mt-1 text-sm md:text-base">ภาพบรรยากาศ โครงการอบรม และกิจกรรมที่ผ่านมา</p>
            </div>
        </div>

        <!-- News Feed Container -->
        <div id="activities-feed" class="space-y-4">
            <!-- Loading State -->
            <div id="activities-loading" class="text-center py-12">
                <div class="inline-block w-8 h-8 border-2 border-[#22c55e]/30 border-t-[#22c55e] rounded-full animate-spin"></div>
                <p class="text-white/40 text-sm mt-3">กำลังโหลดกิจกรรม...</p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    async function loadActivities() {
        const feed = document.getElementById('activities-feed');
        const loading = document.getElementById('activities-loading');
        
        try {
            const res = await fetch('api/api_activities.php');
            const json = await res.json();
            
            if (json.success && json.data.length > 0) {
                let html = '';
                json.data.forEach((item, i) => {
                    const hasImage = item.image_path && item.image_path.trim() !== '';
                    const delay = i * 100;
                    html += `
                    <article class="flex flex-col md:flex-row gap-4 p-4 md:p-5 bg-white/[0.03] border border-white/10 rounded-xl hover:border-[#22c55e]/30 hover:bg-white/[0.06] transition-all duration-300 opacity-0 translate-y-4"
                        style="animation: fadeSlideIn 0.5s ease-out ${delay}ms forwards;">
                        ${hasImage ? `
                        <div class="w-full md:w-48 h-40 md:h-32 rounded-lg overflow-hidden flex-shrink-0 bg-black/20">
                            <img src="${item.image_path}" alt="${item.title}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                        </div>` : ''}
                        <div class="flex-grow min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 bg-[#22c55e]/15 text-[#22c55e] text-[10px] font-bold uppercase tracking-wider rounded-full">กิจกรรม</span>
                                <span class="text-white/40 text-xs"><i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i>${item.activity_date_thai}</span>
                            </div>
                            <h4 class="text-white font-bold text-base md:text-lg leading-snug mb-2">${item.title}</h4>
                            ${item.description ? `<p class="text-white/50 text-sm leading-relaxed line-clamp-2">${item.description}</p>` : ''}
                            ${item.link_url ? `<a href="${item.link_url}" target="_blank" class="inline-flex items-center gap-1 text-[#22c55e] text-sm font-semibold mt-3 hover:underline">อ่านเพิ่มเติม <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>` : ''}
                        </div>
                    </article>`;
                });
                feed.innerHTML = html;
                // Re-init lucide icons for new elements
                if (window.lucide) lucide.createIcons();
            } else {
                feed.innerHTML = `
                    <div class="text-center py-12 border border-white/5 rounded-xl bg-white/[0.02]">
                        <i data-lucide="calendar-x" class="w-12 h-12 text-white/20 mx-auto mb-3"></i>
                        <p class="text-white/40 text-sm">ยังไม่มีกิจกรรม</p>
                        <p class="text-white/30 text-xs mt-1">เพิ่มกิจกรรมได้ผ่าน Admin Panel</p>
                    </div>`;
                if (window.lucide) lucide.createIcons();
            }
        } catch (e) {
            console.warn('Activities API error:', e);
            if (loading) loading.innerHTML = '<p class="text-white/40 text-sm">ไม่สามารถโหลดกิจกรรมได้</p>';
        }
    }

    // Load when tab is activated or on page load
    const tabBtn = document.getElementById('tab-activities');
    if (tabBtn) {
        tabBtn.addEventListener('click', () => setTimeout(loadActivities, 100));
    }
    // Also load on page ready
    document.addEventListener('DOMContentLoaded', loadActivities);
})();
</script>

<style>
@keyframes fadeSlideIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
