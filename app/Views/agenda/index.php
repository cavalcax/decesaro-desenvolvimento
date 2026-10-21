<?php
/** @var array $user */
/** @var array $categorias */
/** @var array $eventos */
?>

<div class="space-y-6">

  <!-- Cabeçalho da Agenda -->
  <div class="card-decesaro p-6 sm:p-7">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div class="flex-1 min-w-0 pr-2">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
          Calendário de Ações
        </span>
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold font-serif text-slate-900 dark:text-white mt-1 whitespace-normal lg:whitespace-nowrap">
          Agenda — Encontros e Formação
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Acompanhe os encontros da família, oficinas, workshops e comitês de governança.
        </p>
      </div>

      <!-- Filtro de Categorias em Pílulas (Ocupa até 2 linhas) -->
      <div class="max-w-md xl:max-w-lg flex flex-wrap items-center justify-start lg:justify-end gap-1.5 flex-shrink-0">
        <button onclick="filterCategory(null)" class="category-filter-btn px-3 py-1 rounded-full text-xs font-semibold bg-navy-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-sm transition-all" data-cat="all">
          Todos
        </button>
        <?php foreach ($categorias as $cat): ?>
          <button onclick="filterCategory(<?= $cat['id'] ?>)" 
                  class="category-filter-btn px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all border border-slate-200 dark:border-slate-700" 
                  data-cat="<?= $cat['id'] ?>">
            <span class="w-2 h-2 rounded-full inline-block mr-1" style="background-color: <?= $cat['cor'] ?>"></span>
            <?= \App\Helpers\Security::e($cat['nome']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Layout Dividido: Calendário à Esquerda (8 cols) + Eventos à Direita (4 cols) -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- 1. Calendário Interativo FullCalendar - Aumentado para 8 colunas e posicionado primeiro -->
    <div class="lg:col-span-8 space-y-4">
      <div class="card-decesaro p-4 sm:p-5">
        <div id="calendar" class="w-full"></div>
      </div>
    </div>

    <!-- 2. Lista de Eventos - Reduzido para 4 colunas e posicionado depois -->
    <div class="lg:col-span-4 space-y-4">
      <div class="card-decesaro p-4 sm:p-5">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white">
            Eventos
          </h3>
          <!-- Toggle Próximos / Anteriores -->
          <div class="flex rounded-lg bg-slate-100 dark:bg-slate-800 p-0.5 text-xs font-semibold">
            <button class="px-2.5 py-1 rounded-md bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm">Próximos</button>
            <button onclick="Swal.fire({title:'Eventos Anteriores', text:'Não há eventos passados arquivados nesta temporada.', icon:'info', confirmButtonColor:'#1b3b5f'})" class="px-2.5 py-1 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Anteriores</button>
          </div>
        </div>

        <div class="space-y-3">
          <?php foreach ($eventos as $ev): 
            $dt = new DateTime($ev['data_inicio']);
            $dia = $dt->format('d');
            $meses = ['JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ'];
            $mes = $meses[(int)$dt->format('m') - 1];
            $isUserRegistered = ($ev['user_inscricao_status'] === 'inscrito');
          ?>
            <div onclick="openEventDetails(<?= (int)$ev['id'] ?>)" 
                 class="group p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/80 hover:border-amber-400/50 hover:shadow-md cursor-pointer transition-all flex items-start gap-3">
              
              <!-- Badge Data -->
              <div class="flex-shrink-0 w-11 text-center py-1.5 px-1 rounded-xl bg-navy-900 dark:bg-slate-700 text-white shadow-sm">
                <span class="block text-base font-bold font-mono leading-none"><?= $dia ?></span>
                <span class="block text-[10px] font-bold text-amber-400 tracking-wider mt-0.5"><?= $mes ?></span>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 mb-0.5">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 truncate">
                    <?= \App\Helpers\Security::e($ev['categoria_nome']) ?>
                  </span>
                  <?php if ($isUserRegistered): ?>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 flex-shrink-0">
                      Inscrito
                    </span>
                  <?php endif; ?>
                </div>

                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                  <?= \App\Helpers\Security::e($ev['titulo']) ?>
                </h4>

                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                  <?= \App\Helpers\Security::e($ev['descricao'] ?? '') ?>
                </p>

                <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-1.5 font-mono">
                  <?php if (!empty($ev['local'])): ?>
                    <span class="truncate"><i class="fa-solid fa-location-dot text-amber-500 mr-1"></i><?= \App\Helpers\Security::e($ev['local']) ?></span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="text-slate-400 group-hover:text-amber-500 self-center text-xs">
                <i class="fa-solid fa-chevron-right"></i>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>

</div>

<!-- Modal Detalhes do Evento e Inscrição -->
<div id="event-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in zoom-in-95 duration-200">
    <div class="flex items-start justify-between gap-4 mb-3">
      <div>
        <span id="modal-event-category" class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400"></span>
        <h3 id="modal-event-title" class="text-lg font-bold font-serif text-slate-900 dark:text-white mt-0.5"></h3>
      </div>
      <button onclick="closeEventModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg p-1">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="space-y-3 py-3 border-y border-slate-100 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300">
      <p id="modal-event-description" class="text-sm leading-relaxed"></p>

      <div class="grid grid-cols-2 gap-3 pt-2">
        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50">
          <span class="block text-[10px] text-slate-400 uppercase font-semibold">Data & Horário</span>
          <span id="modal-event-datetime" class="font-bold text-slate-800 dark:text-slate-100"></span>
        </div>
        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50">
          <span class="block text-[10px] text-slate-400 uppercase font-semibold">Local / Acesso</span>
          <span id="modal-event-location" class="font-bold text-slate-800 dark:text-slate-100 truncate block"></span>
        </div>
      </div>

      <div id="modal-event-link-container" class="hidden">
        <a id="modal-event-link" href="#" target="_blank" rel="noopener noreferrer" class="text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1.5 font-semibold">
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
          <span>Acessar Sala Virtual / Reunião Online</span>
        </a>
      </div>

      <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
        <span id="modal-event-vagas"></span>
        <span id="modal-event-status-badge"></span>
      </div>
    </div>

    <div class="mt-5 flex items-center justify-end gap-3">
      <button onclick="closeEventModal()" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-200">
        Fechar
      </button>
      <button id="modal-btn-action" class="px-5 py-2 rounded-lg bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all">
        Inscrever-se
      </button>
    </div>
  </div>
</div>

<script>
let calendar = null;
let currentEventId = null;
let currentCategoryFilter = null;

const eventDataCache = <?= json_encode($eventos, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', () => {
  const calendarEl = document.getElementById('calendar');
  if (calendarEl) {
    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      locale: 'pt-br',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,listMonth'
      },
      buttonText: {
        today: 'Hoje',
        month: 'Mês',
        list: 'Lista'
      },
      displayEventTime: false,
      eventDisplay: 'block',
      dayMaxEvents: 3,
      events: function(info, successCallback, failureCallback) {
        let url = `/desenvolvimento/api/agenda/eventos?start=${info.startStr}&end=${info.endStr}`;
        if (currentCategoryFilter) {
          url += `&categoria_id=${currentCategoryFilter}`;
        }
        apiRequest(url, { loading: false, silentError: true })
          .then(data => successCallback(data))
          .catch(err => failureCallback(err));
      },
      eventClick: function(info) {
        openEventDetails(parseInt(info.event.id));
      }
    });
    calendar.render();
  }
});

function filterCategory(catId) {
  currentCategoryFilter = catId;
  document.querySelectorAll('.category-filter-btn').forEach(btn => {
    btn.className = 'category-filter-btn px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all border border-slate-200 dark:border-slate-700';
  });
  const activeBtn = document.querySelector(`.category-filter-btn[data-cat="${catId || 'all'}"]`);
  if (activeBtn) {
    activeBtn.className = 'category-filter-btn px-3 py-1 rounded-full text-xs font-semibold bg-navy-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-sm transition-all';
  }
  if (calendar) {
    calendar.refetchEvents();
  }
}

function openEventDetails(eventId) {
  const ev = eventDataCache.find(e => parseInt(e.id) === eventId);
  if (!ev) return;

  currentEventId = eventId;
  document.getElementById('modal-event-category').textContent = ev.categoria_nome;
  document.getElementById('modal-event-title').textContent = ev.titulo;
  document.getElementById('modal-event-description').textContent = ev.descricao || 'Sem descrição detalhada informada.';
  document.getElementById('modal-event-datetime').textContent = new Date(ev.data_inicio).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
  document.getElementById('modal-event-location').textContent = ev.local || 'Local a definir';

  const linkBox = document.getElementById('modal-event-link-container');
  if (ev.link_online) {
    linkBox.classList.remove('hidden');
    document.getElementById('modal-event-link').href = ev.link_online;
  } else {
    linkBox.classList.add('hidden');
  }

  const vagasText = ev.limite_vagas ? `${ev.inscritos_count || 0} de ${ev.limite_vagas} vagas preenchidas` : 'Vagas ilimitadas';
  document.getElementById('modal-event-vagas').textContent = vagasText;

  const actionBtn = document.getElementById('modal-btn-action');
  const isRegistered = ev.user_inscricao_status === 'inscrito';

  if (isRegistered) {
    actionBtn.textContent = 'Cancelar Inscrição';
    actionBtn.className = 'px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md transition-all';
    actionBtn.onclick = () => cancelRegistration(eventId);
  } else {
    actionBtn.textContent = 'Confirmar Inscrição';
    actionBtn.className = 'px-5 py-2 rounded-lg bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all';
    actionBtn.onclick = () => submitRegistration(eventId);
  }

  document.getElementById('event-modal').classList.remove('hidden');
}

function closeEventModal() {
  document.getElementById('event-modal').classList.add('hidden');
}

async function submitRegistration(eventId) {
  try {
    const res = await apiRequest('/desenvolvimento/api/agenda/inscrever', {
      method: 'POST',
      data: { evento_id: eventId }
    });
    closeEventModal();
    Swal.fire({
      icon: 'success',
      title: 'Inscrição Registrada!',
      text: res.message,
      confirmButtonColor: '#1b3b5f'
    }).then(() => window.location.reload());
  } catch {}
}

async function cancelRegistration(eventId) {
  const result = await Swal.fire({
    title: 'Cancelar Inscrição?',
    text: 'Deseja realmente cancelar sua participação neste evento?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#1b3b5f',
    confirmButtonText: 'Sim, cancelar',
    cancelButtonText: 'Manter inscrição'
  });

  if (result.isConfirmed) {
    try {
      await apiRequest('/desenvolvimento/api/agenda/cancelar-inscricao', {
        method: 'POST',
        data: { evento_id: eventId }
      });
      closeEventModal();
      Swal.fire({
        icon: 'success',
        title: 'Cancelado',
        text: 'Sua inscrição foi cancelada com sucesso.',
        confirmButtonColor: '#1b3b5f'
      }).then(() => window.location.reload());
    } catch {}
  }
}
</script>
