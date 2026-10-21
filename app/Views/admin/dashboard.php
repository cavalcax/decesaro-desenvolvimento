<?php
/** @var array $kpis */
/** @var array $porFamilia */
/** @var array $porGeracao */
/** @var array $atividadesRecentes */
?>

<div class="space-y-6">

  <!-- Cabeçalho Administrativo -->
  <div
    class="card-decesaro p-6 sm:p-8 bg-gradient-to-r from-navy-950 via-navy-900 to-navy-800 text-white border-amber-500/30">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div
          class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500 text-slate-950 mb-2">
          <i class="fa-solid fa-shield-halved text-[9px]"></i> Gestão & Governança
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold font-serif text-white">
          Painel Administrativo — Comitê
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-1">
          Monitoramento global do engajamento, formação das gerações e gestão da plataforma.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <a href="/desenvolvimento/admin/relatorios"
          class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
          <i class="fa-solid fa-file-pdf"></i>
          <span>Emitir Relatórios</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Cards de Indicadores (KPIs Reais) -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

    <div class="card-decesaro p-4 text-center">
      <span class="text-2xl font-bold font-mono text-amber-500"><?= $kpis['usuarios'] ?></span>
      <span
        class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mt-1">Participantes</span>
    </div>

    <div class="card-decesaro p-4 text-center">
      <span class="text-2xl font-bold font-mono text-navy-800 dark:text-slate-200"><?= $kpis['raias'] ?></span>
      <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mt-1">Raias</span>
    </div>

    <div class="card-decesaro p-4 text-center">
      <span class="text-2xl font-bold font-mono text-navy-800 dark:text-slate-200"><?= $kpis['conteudos'] ?></span>
      <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mt-1">Conteúdos</span>
    </div>

    <div class="card-decesaro p-4 text-center">
      <span class="text-2xl font-bold font-mono text-navy-800 dark:text-slate-200"><?= $kpis['videos'] ?></span>
      <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mt-1">Aulas /
        Vídeos</span>
    </div>

    <div class="card-decesaro p-4 text-center">
      <span
        class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400"><?= $kpis['conclusoes'] ?></span>
      <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mt-1">Conclusões</span>
    </div>

    <div class="card-decesaro p-4 text-center">
      <span
        class="text-2xl font-bold font-mono text-blue-600 dark:text-blue-400"><?= $kpis['proximos_eventos'] ?></span>
      <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mt-1">Eventos
        Futuros</span>
    </div>

  </div>

  <!-- Gráficos Chart.js com Dados Reais -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Gráfico 1: Participantes por Família -->
    <div class="card-decesaro p-6">
      <h3 class="text-sm font-bold font-serif text-slate-900 dark:text-white mb-1">
        Participantes por Ramo Familiar
      </h3>
      <p class="text-xs text-slate-400 mb-4">Distribuição dos membros cadastrados</p>
      <div class="h-64">
        <canvas id="chart-familias"></canvas>
      </div>
    </div>

    <!-- Gráfico 2: Participantes por Geração -->
    <div class="card-decesaro p-6">
      <h3 class="text-sm font-bold font-serif text-slate-900 dark:text-white mb-1">
        Participantes por Geração
      </h3>
      <p class="text-xs text-slate-400 mb-4">Estrutura intergeracional da Família De Cesaro</p>
      <div class="h-64">
        <canvas id="chart-geracoes"></canvas>
      </div>
    </div>

  </div>

  <!-- Tabela de Auditoria e Atividades Recentes -->
  <div class="card-decesaro p-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-sm font-bold font-serif text-slate-900 dark:text-white">
          Trilha de Auditoria Recente
        </h3>
        <p class="text-xs text-slate-400">Últimas ações e alterações registradas</p>
      </div>
      <a href="/desenvolvimento/admin/auditoria"
        class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
        Ver auditoria completa
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
        <thead
          class="bg-slate-50 dark:bg-slate-800/60 uppercase text-[10px] tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800">
          <tr>
            <th class="py-2.5 px-3">Data / Hora</th>
            <th class="py-2.5 px-3">Usuário</th>
            <th class="py-2.5 px-3">Ação</th>
            <th class="py-2.5 px-3">Entidade</th>
            <th class="py-2.5 px-3">IP</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php foreach ($atividadesRecentes as $act): ?>
            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-2.5 px-3 font-mono text-slate-400"><?= \App\Helpers\Format::dateTime($act['data_hora']) ?>
              </td>
              <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200">
                <?= \App\Helpers\Security::e($act['usuario_nome'] ?? 'Sistema') ?></td>
              <td class="py-2.5 px-3">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                  <?= \App\Helpers\Security::e($act['acao']) ?>
                </span>
              </td>
              <td class="py-2.5 px-3 text-slate-500 font-mono"><?= \App\Helpers\Security::e($act['entidade']) ?>
                #<?= $act['entidade_id'] ?></td>
              <td class="py-2.5 px-3 font-mono text-slate-400"><?= \App\Helpers\Security::e($act['ip'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Scripts dos Gráficos Chart.js -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Gráfico Famílias
    const famLabels = <?= json_encode(array_column($porFamilia, 'familia')) ?>;
    const famData = <?= json_encode(array_column($porFamilia, 'total')) ?>;

    const ctxFam = document.getElementById('chart-familias');
    if (ctxFam) {
      new Chart(ctxFam, {
        type: 'bar',
        data: {
          labels: famLabels.map(f => 'Família ' + f),
          datasets: [{
            label: 'Participantes',
            data: famData,
            backgroundColor: '#c59b27',
            borderRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    }

    // Gráfico Gerações
    const gerLabels = <?= json_encode(array_column($porGeracao, 'geracao')) ?>;
    const gerData = <?= json_encode(array_column($porGeracao, 'total')) ?>;

    const ctxGer = document.getElementById('chart-geracoes');
    if (ctxGer) {
      new Chart(ctxGer, {
        type: 'doughnut',
        data: {
          labels: gerLabels,
          datasets: [{
            data: gerData,
            backgroundColor: ['#03254A', '#1b3b5f', '#c59b27', '#e5c058', '#254d78'],
            borderWidth: 2,
            borderColor: 'transparent'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }
  });
</script>