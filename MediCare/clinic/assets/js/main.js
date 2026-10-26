document.addEventListener('DOMContentLoaded', function () {
    setupSlotLoader();
    setupPrescriptionRows();
    setupTableSearch();
    setupMobileNav();
    setupTheme();
    setupAutoDismiss();
});

function setupSlotLoader() {
    var doctorSelect = document.getElementById('doctor_id');
    var dateInput = document.getElementById('date');
    var slotBox = document.getElementById('slots');
    if (!doctorSelect || !dateInput || !slotBox) return;
    function loadSlots() {
        if (!doctorSelect.value || !dateInput.value) { slotBox.innerHTML = '<p class="muted">Select a doctor to see free slots.</p>'; return; }
        slotBox.innerHTML = '<p class="loading"><span></span> Loading available slots…</p>';
        fetch('index.php?page=api/slots&doctor_id=' + encodeURIComponent(doctorSelect.value) + '&date=' + encodeURIComponent(dateInput.value))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.slots || data.slots.length === 0) { slotBox.innerHTML = '<p class="empty-mini">' + (data.message || 'No free slots.') + '</p>'; return; }
                var html = '';
                data.slots.forEach(function (s) { html += '<label class="slot"><input type="radio" name="time_slot" value="' + s.schedule_id + '|' + s.time + '" required><span>' + s.label + '</span></label>'; });
                slotBox.innerHTML = html;
            }).catch(function () { slotBox.innerHTML = '<p class="empty-mini">Could not load slots. Please try again.</p>'; });
    }
    doctorSelect.addEventListener('change', loadSlots); dateInput.addEventListener('change', loadSlots); loadSlots();
}

function setupPrescriptionRows() {
    var table = document.getElementById('itemTable'), addBtn = document.getElementById('addItemRow');
    if (!table || !addBtn) return;
    var tbody = table.tBodies[0];
    addBtn.addEventListener('click', function (e) { e.preventDefault(); var base = tbody.querySelector('.item-row'); if (!base) return; var row = base.cloneNode(true); row.querySelectorAll('input').forEach(function(i){i.value='';}); row.querySelectorAll('select').forEach(function(s){s.selectedIndex=0;}); tbody.appendChild(row); });
    tbody.addEventListener('click', function(e){ if(!e.target.classList.contains('remove-row')) return; e.preventDefault(); if(tbody.querySelectorAll('.item-row').length===1){alert('A prescription needs at least one medicine row.');return;} e.target.closest('.item-row').remove(); });
}

function setupTableSearch() {
    var box = document.getElementById('tableSearch'), table = document.getElementById('searchTable');
    if (!box || !table) return;
    box.addEventListener('input', function(){ var term=box.value.toLowerCase().trim(); Array.prototype.forEach.call(table.tBodies[0].rows,function(row){row.style.display=row.innerText.toLowerCase().indexOf(term)>-1?'':'none';}); });
}

function setupMobileNav() {
    var toggle=document.querySelector('.nav-toggle'), nav=document.getElementById('mainNav');
    if(!toggle || !nav) return;
    toggle.addEventListener('click',function(){var open=nav.classList.toggle('open');toggle.setAttribute('aria-expanded',open?'true':'false');toggle.textContent=open?'×':'☰';});
}

function setupTheme() {
    var btn=document.querySelector('.theme-toggle');
    if(!btn) return;
    var saved=localStorage.getItem('medicare-theme');
    if(saved==='dark') document.documentElement.classList.add('dark');
    updateThemeButton();
    btn.addEventListener('click',function(){document.documentElement.classList.toggle('dark');localStorage.setItem('medicare-theme',document.documentElement.classList.contains('dark')?'dark':'light');updateThemeButton();});
    function updateThemeButton(){btn.textContent=document.documentElement.classList.contains('dark')?'☀':'☾';btn.setAttribute('title',document.documentElement.classList.contains('dark')?'Switch to light mode':'Switch to dark mode');}
}

function setupAutoDismiss(){setTimeout(function(){document.querySelectorAll('.flash').forEach(function(el){el.classList.add('flash-hide');setTimeout(function(){if(el.parentNode)el.parentNode.removeChild(el);},400);});},6000);}
