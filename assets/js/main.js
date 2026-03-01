// assets/js/main.js

function switchStep(stepNum) {
    // 1. ซ่อนทุก Step
    const steps = document.querySelectorAll('.step-section');
    steps.forEach(el => el.classList.add('hidden'));

    // 2. แสดง Step ที่เลือก
    const targetStep = document.getElementById('step-' + stepNum);
    if(targetStep) targetStep.classList.remove('hidden');

    // 3. อัปเดตสีของวงกลม (Indicator)
    for(let i = 1; i <= 4; i++) {
        const ind = document.getElementById('ind-' + i);
        if(!ind) continue;

        // Reset classes
        ind.classList.remove('step-active', 'step-completed', 'step-inactive');

        if(i < stepNum) {
            ind.classList.add('step-completed'); // ผ่านมาแล้ว
            ind.innerHTML = '<i class="fa-solid fa-check"></i>';
        } else if (i === stepNum) {
            ind.classList.add('step-active'); // ปัจจุบัน
            ind.innerHTML = i;
        } else {
            ind.classList.add('step-inactive'); // ยังไม่ถึง
            ind.innerHTML = i;
        }
    }
}