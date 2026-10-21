<?php
/** @var array $conteudos */
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="card-decesaro p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Administração</span>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-0.5">Gestão de Vídeos e Aulas</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Cadastro de mídias, vínculos com conteúdos e regras de exclusividade</p>
    </div>
    <button onclick="openModalCreate()" class="px-4 py-2.5 rounded-xl bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all flex items-center gap-2">
      <i class="fa-solid fa-video text-xs"></i>
      <span>Novo Vídeo</span>
    </button>
  </div>

  <!-- Card Filtrar e Buscar -->
  <div class="card-decesaro p-4 flex flex-wrap items-end gap-3">
    <div class="w-full sm:w-72">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Filtrar por Conteúdo</label>
      <select id="filter-conteudo" class="select2" onchange="applyFilters()">
        <option value="">Todos os Conteúdos</option>
        <?php foreach ($conteudos as $c): ?>
          <option value="<?= $c['id'] ?>"><?= \App\Helpers\Security::e($c['raia_descricao']) ?> • <?= \App\Helpers\Security::e($c['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Buscar Vídeo / Aula</label>
      <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" id="filter-search" class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50" placeholder="Pesquisar por título ou descrição da aula...">
      </div>
    </div>
  </div>

  <!-- Tabela Dinâmica -->
  <div id="videos-table-container"></div>

</div>

<!-- Modal com Grid de 12 Colunas (Section 22) -->
<div id="modal-video" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in duration-200 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-4">
      <h3 id="modal-title" class="text-base font-bold font-serif text-slate-900 dark:text-white">Novo Vídeo</h3>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form id="form-video" onsubmit="saveVideo(event)">
      <input type="hidden" id="v-id" name="id">

      <!-- Grid de 12 Colunas -->
      <div class="grid grid-cols-12 gap-4 text-xs">
        
        <!-- Título: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Título da Aula *</label>
          <input type="text" id="v-titulo" name="titulo" required placeholder="Ex: Origens e Primeiras Conquistas"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Conteúdo Vinculado: 6 cols -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Conteúdo da Raia *</label>
          <select id="v-id-conteudo" name="id_raia_conteudo" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="">Selecione o conteúdo...</option>
            <?php foreach ($conteudos as $c): ?>
              <option value="<?= $c['id'] ?>"><?= \App\Helpers\Security::e($c['raia_descricao']) ?> • <?= \App\Helpers\Security::e($c['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Link Arquivo: 12 cols -->
        <div class="col-span-12">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Link do Arquivo (Google Drive, YouTube ou Vídeo MP4) *</label>
          <input type="url" id="v-link" name="link_arquivo" required placeholder="https://drive.google.com/file/d/... ou https://exemplo.com/aula.mp4"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
          <p class="text-[11px] text-slate-400 mt-1">Compatível com links do Google Drive, YouTube ou streaming direto MP4/WebM.</p>
        </div>

        <!-- Exclusivo: 4 cols -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Exclusivo (Restrição por Nível) *</label>
          <select id="v-exclusivo" name="exclusivo" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            <option value="Sim">Sim (Restrito à faixa etária)</option>
            <option value="Não">Não (Livre para todos)</option>
          </select>
        </div>

        <!-- Duração: 4 cols (Aceita Google Drive MM:SS ou Segundos) -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Duração * <span class="text-[11px] font-normal text-slate-400 dark:text-slate-500">(ex: 00:11 ou seg)</span>
          </label>
          <input type="text" id="v-duracao" name="duracao_segundos" value="00:11" required placeholder="Ex: 00:11 ou 300"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
          <div id="v-duracao-preview" class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-medium flex items-center gap-1">
            <i class="fa-solid fa-clock text-[10px]"></i>
            <span id="v-duracao-preview-text">Equivale a 11 segundos</span>
          </div>
        </div>

        <!-- Ordem: 4 cols -->
        <div class="col-span-12 sm:col-span-4">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ordem na Trilha</label>
          <input type="number" id="v-ordem" name="ordem" min="0" value="0"
                 class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50">
        </div>

        <!-- Descrição: 12 cols -->
        <div class="col-span-12">
          <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Descrição da Aula / Sinopse</label>
          <textarea id="v-descricao" name="descricao" rows="3" placeholder="Resumo dos tópicos abordados nesta aula..."
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50"></textarea>
        </div>

      </div>

      <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300">
          Cancelar
        </button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-navy-900 dark:bg-amber-500 text-white dark:text-slate-950 font-bold text-xs shadow-md">
          Salvar Vídeo
        </button>
      </div>
    </form>
  </div>
</div>

<script>
let tableInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  tableInstance = new AjaxTable({
    container: '#videos-table-container',
    searchInput: '#filter-search',
    endpoint: '/desenvolvimento/admin/videos',
    columns: [
      { key: 'ordem', label: 'Ordem', sortable: false, render: (val) => `<span class="font-mono text-amber-500 font-bold">#${val}</span>` },
      {
        key: 'titulo',
        label: 'Título',
        sortable: false,
        render: (val, row) => `
          <div>
            <span class="font-bold text-slate-800 dark:text-white block">${escapeHtml(val)}</span>
            <span class="text-[11px] text-slate-400">${escapeHtml(row.raia_descricao || '')} • ${escapeHtml(row.conteudo_descricao || '')}</span>
          </div>
        `
      },
      {
        key: 'duracao_segundos',
        label: 'Duração',
        sortable: false,
        render: (val) => {
          const m = Math.floor(val / 60);
          const s = val % 60;
          return `<span class="font-mono text-xs">${m}:${s < 10 ? '0' : ''}${s} min</span>`;
        }
      },
      {
        key: 'exclusivo',
        label: 'Exclusividade',
        sortable: false,
        render: (val) => `<span class="px-2 py-0.5 rounded text-[10px] font-bold ${val === 'Sim' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300'}">${val === 'Sim' ? 'Exclusivo (Nível)' : 'Livre'}</span>`
      },
      {
        key: 'id',
        label: 'Ações',
        sortable: false,
        render: (val, row) => `
          <div class="flex items-center gap-1.5">
            <a href="/desenvolvimento/video/${val}" target="_blank" class="p-1.5 text-slate-400 hover:text-blue-500 text-xs" title="Visualizar Vídeo">
              <i class="fa-solid fa-play"></i>
            </a>
            <button onclick='openModalEdit(${JSON.stringify(row)})' class="p-1.5 text-slate-400 hover:text-amber-500 text-xs" title="Editar">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button onclick="deleteVideo(${val})" class="p-1.5 text-slate-400 hover:text-red-500 text-xs" title="Excluir">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        `
      }
    ]
  });
});

function applyFilters() {
  if (tableInstance) {
    tableInstance.setFilters({
      conteudo_id: document.getElementById('filter-conteudo').value
    });
  }
}

function parseDurationToSeconds(val) {
  if (!val) return 0;
  const str = String(val).trim();
  if (!str) return 0;

  if (/^\d+$/.test(str)) {
    return parseInt(str, 10);
  }

  const hms = str.match(/^(\d+):(\d{1,2}):(\d{1,2})$/);
  if (hms) {
    return (parseInt(hms[1], 10) * 3600) + (parseInt(hms[2], 10) * 60) + parseInt(hms[3], 10);
  }

  const ms = str.match(/^(\d+):(\d{1,2})$/);
  if (ms) {
    return (parseInt(ms[1], 10) * 60) + parseInt(ms[2], 10);
  }

  let total = 0;
  let found = false;
  const h = str.match(/(\d+)\s*h(?:oras?)?/i);
  if (h) { total += parseInt(h[1], 10) * 3600; found = true; }
  const m = str.match(/(\d+)\s*m(?:in(?:utos?)?)?/i);
  if (m) { total += parseInt(m[1], 10) * 60; found = true; }
  const s = str.match(/(\d+)\s*s(?:eg(?:undos?)?)?/i);
  if (s) { total += parseInt(s[1], 10); found = true; }

  if (found) return total;
  return parseInt(str, 10) || 0;
}

function formatDurationHuman(secs) {
  if (secs <= 0) return '0 segundos';
  if (secs < 60) return `${secs} segundos (0:${secs.toString().padStart(2, '0')})`;
  const m = Math.floor(secs / 60);
  const s = secs % 60;
  if (m < 60) {
    return s > 0 ? `${secs}s (${m}m ${s}s)` : `${secs}s (${m}m 00s)`;
  }
  const h = Math.floor(m / 60);
  const remM = m % 60;
  return `${secs}s (${h}h ${remM}m)`;
}

function updateDurationPreview() {
  const val = document.getElementById('v-duracao').value;
  const secs = parseDurationToSeconds(val);
  const preview = document.getElementById('v-duracao-preview-text');
  if (preview) {
    preview.textContent = `Equivale a ${formatDurationHuman(secs)}`;
  }
}

document.getElementById('v-duracao').addEventListener('input', updateDurationPreview);

function openModalCreate() {
  document.getElementById('form-video').reset();
  document.getElementById('v-id').value = '';
  document.getElementById('v-duracao').value = '00:11';
  updateDurationPreview();
  document.getElementById('modal-title').textContent = 'Novo Vídeo';
  document.getElementById('modal-video').classList.remove('hidden');
}

function openModalEdit(row) {
  document.getElementById('form-video').reset();
  document.getElementById('v-id').value = row.id;
  document.getElementById('v-titulo').value = row.titulo;
  document.getElementById('v-descricao').value = row.descricao || '';
  document.getElementById('v-link').value = row.link_arquivo;
  document.getElementById('v-id-conteudo').value = row.id_raia_conteudo;
  document.getElementById('v-exclusivo').value = row.exclusivo || 'Sim';
  
  const rawSecs = parseInt(row.duracao_segundos, 10) || 0;
  if (rawSecs < 60) {
    document.getElementById('v-duracao').value = '00:' + String(rawSecs).padStart(2, '0');
  } else {
    const mins = Math.floor(rawSecs / 60);
    const rem = rawSecs % 60;
    document.getElementById('v-duracao').value = String(mins).padStart(2, '0') + ':' + String(rem).padStart(2, '0');
  }
  updateDurationPreview();

  document.getElementById('v-ordem').value = row.ordem || 0;
  document.getElementById('modal-title').textContent = 'Editar Vídeo';
  document.getElementById('modal-video').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('modal-video').classList.add('hidden');
}

async function saveVideo(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  // Converte a duração para segundos caso o usuário tenha inserido no formato 00:11 (Google Drive)
  data.duracao_segundos = parseDurationToSeconds(data.duracao_segundos);

  try {
    const res = await apiRequest('/desenvolvimento/admin/videos/salvar', {
      method: 'POST',
      data: data
    });
    closeModal();
    showToast('success', res.message);
    tableInstance.load();
  } catch {}
}

async function deleteVideo(id) {
  const result = await Swal.fire({
    title: 'Excluir Vídeo?',
    text: 'Esta ação excluirá o vídeo e os registros de progresso dos participantes.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#1b3b5f',
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      const res = await apiRequest('/desenvolvimento/admin/videos/excluir', {
        method: 'POST',
        data: { id }
      });
      showToast('success', res.message);
      tableInstance.load();
    } catch {}
  }
}

// Detecção automática de duração do arquivo de vídeo (MP4/WebM)
document.getElementById('v-link').addEventListener('blur', function() {
  const url = this.value.trim();
  if (!url) return;

  // Se for Google Drive, orienta o usuário sobre os Detalhes do arquivo
  if (url.includes('drive.google.com')) {
    showToast('info', 'Google Drive detectado: no painel Detalhes do Drive, veja a Duração (ex: 00:11) e digite no campo Duração.', 6000);
    return;
  }

  // Se for link direto de streaming ou arquivo suportado pelo navegador
  if (url.match(/\.(mp4|webm|ogg|m4v)(\?.*)?$/i) || (!url.includes('youtube.com') && !url.includes('vimeo.com') && url.startsWith('http'))) {
    const tempVideo = document.createElement('video');
    tempVideo.preload = 'metadata';
    tempVideo.src = url;
    tempVideo.onloadedmetadata = function() {
      if (tempVideo.duration && !isNaN(tempVideo.duration) && tempVideo.duration > 0) {
        const secs = Math.round(tempVideo.duration);
        if (secs < 60) {
          document.getElementById('v-duracao').value = '00:' + String(secs).padStart(2, '0');
        } else {
          const mins = Math.floor(secs / 60);
          const rem = secs % 60;
          document.getElementById('v-duracao').value = String(mins).padStart(2, '0') + ':' + String(rem).padStart(2, '0');
        }
        updateDurationPreview();
        showToast('info', `Duração detectada do arquivo: ${secs}s`);
      }
    };
  }
});
</script>
