/* ============================================================
   PUBLIMARKET — main.js
   Vanilla JS | Animaciones · Calendario · WhatsApp
   ============================================================ */

'use strict';

/* ─── NAVBAR scroll effect ───────────────────────────────── */
const navbar = document.getElementById('navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 20);
  }, { passive: true });
}

/* ─── HAMBURGER ──────────────────────────────────────────── */
const hamburger = document.getElementById('hamburger');
const navLinks  = document.getElementById('navLinks');
if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    hamburger.classList.toggle('active');
  });
}

/* ─── INTERSECTION OBSERVER — Reveal animations ──────────── */
const revealEls = document.querySelectorAll('.reveal');
if (revealEls.length) {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
  revealEls.forEach(el => observer.observe(el));
}

/* ─── CALENDAR MODULE ────────────────────────────────────── */
const CalendarModule = (() => {

  let state = {
    planId:    null,
    planName:  null,
    year:      new Date().getFullYear(),
    month:     new Date().getMonth(),   // 0-based
    selectedDate: null,
    selectedTime: null,
    busySlots: {}   // { 'YYYY-MM-DD': ['09:00','10:00'] }
  };

  const AVAILABLE_TIMES = [
    '08:00','09:00','10:00','11:00',
    '14:00','15:00','16:00','17:00'
  ];

  const MONTH_NAMES = [
    'Enero','Febrero','Marzo','Abril','Mayo','Junio',
    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
  ];

  const DAY_NAMES = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];

  /** Abre el modal para un plan dado */
  function open(planId, planName) {
    state.planId   = planId;
    state.planName = planName;
    state.selectedDate = null;
    state.selectedTime = null;

    // Poblar título del modal
    const titleEl = document.getElementById('modalPlanName');
    if (titleEl) titleEl.textContent = planName;

    fetchBusySlots().then(renderCalendar);
    renderTimeSlots();

    const overlay = document.getElementById('calendarModal');
    if (overlay) {
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function close() {
    const overlay = document.getElementById('calendarModal');
    if (overlay) {
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  /** Trae los slots ocupados desde el servidor */
  async function fetchBusySlots() {
    try {
      const res = await fetch(`/publimarket/api/appointments.php?action=busy&year=${state.year}&month=${state.month + 1}`);
      if (res.ok) {
        const data = await res.json();
        state.busySlots = data.busy || {};
      }
    } catch (_) { /* offline: no hay slots ocupados */ }
  }

  /** Renderiza la grilla del calendario */
  function renderCalendar() {
    const grid = document.getElementById('calGrid');
    if (!grid) return;

    const monthLabel = document.getElementById('calMonthLabel');
    if (monthLabel) monthLabel.textContent = `${MONTH_NAMES[state.month]} ${state.year}`;

    const today       = new Date();
    const firstDay    = new Date(state.year, state.month, 1).getDay();
    const daysInMonth = new Date(state.year, state.month + 1, 0).getDate();

    // Encabezados
    let html = DAY_NAMES.map(d => `<div class="cal-day-name">${d}</div>`).join('');

    // Celdas vacías
    for (let i = 0; i < firstDay; i++) html += `<div class="cal-day empty"></div>`;

    for (let d = 1; d <= daysInMonth; d++) {
      const dateStr = `${state.year}-${String(state.month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const thisDate = new Date(state.year, state.month, d);
      const isPast   = thisDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());
      const isToday  = thisDate.toDateString() === today.toDateString();
      const isSel    = dateStr === state.selectedDate;
      const busyList = state.busySlots[dateStr] || [];
      const hasSlot  = AVAILABLE_TIMES.some(t => !busyList.includes(t));

      const classes = [
        'cal-day',
        isPast  ? 'past'     : '',
        isToday ? 'today'    : '',
        isSel   ? 'selected' : '',
        hasSlot && !isPast ? 'has-slot' : ''
      ].filter(Boolean).join(' ');

      html += `<div class="${classes}" data-date="${dateStr}">${d}</div>`;
    }

    grid.innerHTML = html;

    // Event delegation
    grid.querySelectorAll('.cal-day:not(.past):not(.empty)').forEach(cell => {
      cell.addEventListener('click', () => {
        state.selectedDate = cell.dataset.date;
        state.selectedTime = null;
        renderCalendar();
        renderTimeSlots();
      });
    });
  }

  /** Renderiza los botones de hora */
  function renderTimeSlots() {
    const wrap = document.getElementById('timeSlotsWrap');
    if (!wrap) return;

    if (!state.selectedDate) {
      wrap.innerHTML = '<p class="body-sm" style="padding:.5rem 0">Selecciona una fecha para ver los horarios disponibles.</p>';
      return;
    }

    const busyList = state.busySlots[state.selectedDate] || [];

    const html = AVAILABLE_TIMES.map(t => {
      const isBusy = busyList.includes(t);
      const isSel  = t === state.selectedTime;
      const cls    = ['slot-btn', isBusy ? 'busy' : '', isSel ? 'selected' : ''].filter(Boolean).join(' ');
      return `<button class="${cls}" data-time="${t}" ${isBusy ? 'disabled' : ''}>${t}</button>`;
    }).join('');

    wrap.innerHTML = `
      <p class="time-slots-title">Horarios disponibles</p>
      <div class="slots-grid">${html}</div>`;

    wrap.querySelectorAll('.slot-btn:not(.busy)').forEach(btn => {
      btn.addEventListener('click', () => {
        state.selectedTime = btn.dataset.time;
        renderTimeSlots();
      });
    });
  }

  /** Navega entre meses */
  function prevMonth() {
    state.month--;
    if (state.month < 0) { state.month = 11; state.year--; }
    state.selectedDate = null;
    state.selectedTime = null;
    fetchBusySlots().then(renderCalendar);
    renderTimeSlots();
  }

  function nextMonth() {
    state.month++;
    if (state.month > 11) { state.month = 0; state.year++; }
    state.selectedDate = null;
    state.selectedTime = null;
    fetchBusySlots().then(renderCalendar);
    renderTimeSlots();
  }

  /** Confirma la cita → POST + redirige a WhatsApp */
  async function confirm() {
    if (!state.selectedDate || !state.selectedTime) {
      showModalAlert('Por favor selecciona una fecha y horario.', 'error');
      return;
    }
    const name  = document.getElementById('guestName')?.value.trim();
    const email = document.getElementById('guestEmail')?.value.trim();
    const phone = document.getElementById('guestPhone')?.value.trim();

    if (!name || !email) {
      showModalAlert('Nombre y correo son obligatorios.', 'error');
      return;
    }

    const btn = document.getElementById('confirmApptBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Agendando…'; }

    try {
      const res = await fetch('/publimarket/api/appointments.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action:       'book',
          plan_id:      state.planId,
          date:         state.selectedDate,
          time:         state.selectedTime,
          guest_name:   name,
          guest_email:  email,
          guest_phone:  phone,
          _csrf:        document.querySelector('meta[name="csrf"]')?.content || ''
        })
      });

      const data = await res.json();
      if (!res.ok || data.error) throw new Error(data.error || 'Error al agendar');

      // Redirigir a WhatsApp
      const msg = `Hola Publimarket, estoy interesado en la membresía ${state.planName} ya asigné mi cita para el ${formatDate(state.selectedDate)} a las ${state.selectedTime}, quiero saber más a detalle.`;
      const waURL = `https://wa.me/${WA_PHONE}?text=${encodeURIComponent(msg)}`;

      close();
      window.open(waURL, '_blank');

    } catch (err) {
      showModalAlert(err.message, 'error');
      if (btn) { btn.disabled = false; btn.textContent = 'Confirmar cita'; }
    }
  }

  function formatDate(str) {
    const [y,m,d] = str.split('-');
    return `${d}/${m}/${y}`;
  }

  function showModalAlert(msg, type = 'error') {
    const el = document.getElementById('modalAlert');
    if (!el) return;
    el.className = `alert alert-${type}`;
    el.textContent = msg;
    el.style.display = 'flex';
    setTimeout(() => { el.style.display = 'none'; }, 5000);
  }

  return { open, close, prevMonth, nextMonth, confirm };
})();

/* ─── Botones "Adquirir" ─────────────────────────────────── */
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-plan-open]');
  if (btn) {
    CalendarModule.open(btn.dataset.planId, btn.dataset.planName);
  }
});

/* ─── Calendar modal wiring ──────────────────────────────── */
document.getElementById('calPrev')?.addEventListener('click', () => CalendarModule.prevMonth());
document.getElementById('calNext')?.addEventListener('click', () => CalendarModule.nextMonth());
document.getElementById('modalCloseBtn')?.addEventListener('click', () => CalendarModule.close());
document.getElementById('confirmApptBtn')?.addEventListener('click', () => CalendarModule.confirm());
document.getElementById('calendarModal')?.addEventListener('click', function(e) {
  if (e.target === this) CalendarModule.close();
});

/* ─── CART (simulado) ────────────────────────────────────── */
const Cart = (() => {
  let items = JSON.parse(sessionStorage.getItem('pm_cart') || '[]');

  function save()   { sessionStorage.setItem('pm_cart', JSON.stringify(items)); updateBadge(); }
  function count()  { return items.reduce((s, i) => s + i.qty, 0); }

  function add(product) {
    const existing = items.find(i => i.id === product.id);
    if (existing) existing.qty++;
    else items.push({ ...product, qty: 1 });
    save();
    showCartToast(product.name);
  }

  function updateBadge() {
    const badge = document.getElementById('cartBadge');
    if (!badge) return;
    const n = count();
    badge.textContent = n;
    badge.style.display = n > 0 ? 'grid' : 'none';
  }

  function showCartToast(name) {
    const toast = document.getElementById('cartToast');
    if (!toast) return;
    toast.querySelector('.toast-name').textContent = name;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
  }

  return { add, items: () => items, count, save };
})();

/* ─── Botones "Comprar" ──────────────────────────────────── */
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-add-cart]');
  if (btn) {
    Cart.add({
      id:    btn.dataset.productId,
      name:  btn.dataset.productName,
      price: parseFloat(btn.dataset.productPrice),
      image: btn.dataset.productImage
    });
    btn.textContent = '✓ Agregado';
    btn.classList.add('btn-ghost');
    btn.classList.remove('btn-primary');
    setTimeout(() => {
      btn.textContent = 'Comprar';
      btn.classList.remove('btn-ghost');
      btn.classList.add('btn-primary');
    }, 2000);
  }
});

/* ─── MEMBERSHIP TOGGLE (admin) ─────────────────────────── */
document.addEventListener('change', async e => {
  const toggle = e.target.closest('[data-membership-toggle]');
  if (!toggle) return;

  const userId = toggle.dataset.userId;
  const status = toggle.checked ? 'active' : 'inactive';
  const labelEl = document.getElementById(`status-label-${userId}`);

  try {
    const res = await fetch('/publimarket/admin/api/membership.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId, status,
        _csrf: document.querySelector('meta[name="csrf"]')?.content || '' })
    });
    const data = await res.json();
    if (!res.ok || data.error) throw new Error(data.error || 'Error');

    if (labelEl) {
      labelEl.textContent = status === 'active' ? 'Activa' : 'Inactiva';
      labelEl.className = `membership-badge ${status}`;
    }
  } catch (err) {
    toggle.checked = !toggle.checked; // revertir
    alert('Error al actualizar membresía: ' + err.message);
  }
});

/* ─── SMOOTH ANCHOR SCROLL ───────────────────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      const top = target.getBoundingClientRect().top + window.scrollY - 80;
      window.scrollTo({ top, behavior: 'smooth' });
    }
  });
});
