<?php
/** @var array $user */
/** @var array $raias */
/** @var array $historico */
/** @var int $totalVideosGerais */
/** @var int $totalVideosConcluidos */
/** @var int $tempoTotalAssistido */
?>

<div class="space-y-6">

  <!-- Cabeçalho Institucional -->
  <div class="card-decesaro p-6 sm:p-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
          Acompanhamento Individual
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold font-serif text-slate-900 dark:text-white mt-1">
          Minha Jornada — Meu Desenvolvimento
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Acompanhe sua evolução formativa, aulas assistidas e próximas etapas na família.
        </p>
      </div>

      <!-- Resumo Rápido -->
      <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800/80 p-3 rounded-2xl border border-slate-100 dark:border-slate-700">
        <div class="text-center px-3 border-r border-slate-200 dark:border-slate-700">
          <span class="block text-xl font-bold font-mono text-amber-500"><?= $totalVideosConcluidos ?></span>
          <span class="block text-[10px] text-slate-400 uppercase">Aulas Concluídas</span>
        </div>
        <div class="text-center px-3">
          <span class="block text-xl font-bold font-mono text-navy-800 dark:text-slate-200"><?= \App\Helpers\Format::duration($tempoTotalAssistido) ?></span>
          <span class="block text-[10px] text-slate-400 uppercase">Tempo em Aula</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Abas de Navegação (layout.png: Visão geral, Meu plano, Histórico) -->
  <div class="border-b border-slate-200 dark:border-slate-800">
    <nav class="flex space-x-6 text-sm font-medium">
      <button class="pb-3 border-b-2 border-amber-500 text-amber-600 dark:text-amber-400 font-bold flex items-center gap-2">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Visão Geral</span>
      </button>
      <button onclick="Swal.fire({title:'Plano de Desenvolvimento Individual (PDI)', text:'Seu plano individual está sendo estruturado pelo Comitê conforme suas metas e participação societária.', icon:'info', confirmButtonColor:'#1b3b5f'})" class="pb-3 border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center gap-2">
        <i class="fa-solid fa-clipboard-list"></i>
        <span>Meu Plano</span>
      </button>
      <button class="pb-3 border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Histórico</span>
      </button>
    </nav>
  </div>

  <!-- Seção: Meu Progresso (4 Raias do Layout de Referência) -->
  <div>
    <h2 class="text-base font-bold font-serif text-slate-900 dark:text-white mb-4">
      Meu Progresso por Raia
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($raias as $r): ?>
        <div class="card-decesaro p-5 flex flex-col items-center text-center">
          <!-- Circular Progress Meter (SVG) -->
          <div class="relative w-24 h-24 mb-3">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
              <path class="text-slate-100 dark:text-slate-800" stroke-width="3" stroke="currentColor" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
              <path class="text-amber-500 transition-all duration-1000" stroke-dasharray="<?= $r['percentual'] ?>, 100" stroke-width="3" stroke-linecap="round" stroke="currentColor" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
              <?php if ($r['status'] === 'Bloqueada'): ?>
                <i class="fa-solid fa-lock text-slate-400 text-lg"></i>
              <?php else: ?>
                <span class="text-base font-bold font-mono text-slate-900 dark:text-white"><?= $r['percentual'] ?>%</span>
              <?php endif; ?>
            </div>
          </div>

          <h3 class="text-sm font-bold text-slate-900 dark:text-white"><?= \App\Helpers\Security::e($r['descricao']) ?></h3>
          <span class="text-xs font-semibold mt-1 px-2 py-0.5 rounded-full <?= $r['percentual'] >= 100 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : ($r['percentual'] > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400') ?>">
            <?= $r['status'] ?>
          </span>

          <a href="/desenvolvimento/<?= $r['slug'] ?>" class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 hover:underline mt-3">
            Acessar trilha &rarr;
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Bloco: Meu Plano de Desenvolvimento Individual (PDI) conforme layout.png -->
  <div class="card-decesaro p-6 bg-gradient-to-br from-amber-500/10 via-transparent to-navy-900/5 dark:from-amber-500/5 dark:to-slate-800/20 border-amber-500/30">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="max-w-xl">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500 text-slate-950 mb-2">
          <i class="fa-solid fa-star text-[9px]"></i> PDI Personalizado
        </span>
        <h3 class="text-lg font-bold font-serif text-slate-900 dark:text-white">
          Meu plano de desenvolvimento
        </h3>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
          Seu plano de desenvolvimento individual é elaborado conforme sua fase, idade e participação na governança e nos negócios da família De Cesaro.
        </p>
      </div>

      <button onclick="Swal.fire({title:'Plano Individual', text:'Seu PDI está em acompanhamento pelo Comitê de Desenvolvimento. Novas metas e reuniões de alinhamento serão divulgadas na Agenda.', icon:'info', confirmButtonColor:'#1b3b5f'})"
              class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold text-xs shadow-lg transition-all flex items-center gap-2 flex-shrink-0 active:scale-95">
        <span>Ver meu plano</span>
        <i class="fa-solid fa-arrow-right text-xs"></i>
      </button>
    </div>
  </div>

  <!-- Tabela de Histórico de Aprendizagem -->
  <div class="card-decesaro p-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white">Histórico de Atividades</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Aulas e conteúdos acessados recentemente</p>
      </div>
    </div>

    <?php if (empty($historico)): ?>
      <p class="text-xs text-slate-400 py-8 text-center">Nenhum histórico registrado até o momento.</p>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
          <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase text-[10px] tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800">
            <tr>
              <th class="py-2.5 px-3">Aula</th>
              <th class="py-2.5 px-3">Raia / Módulo</th>
              <th class="py-2.5 px-3">Progresso</th>
              <th class="py-2.5 px-3">Última Atividade</th>
              <th class="py-2.5 px-3 text-right">Ação</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php foreach ($historico as $h): ?>
              <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-100">
                  <?= \App\Helpers\Security::e($h['video_titulo']) ?>
                </td>
                <td class="py-3 px-3 text-slate-500 dark:text-slate-400">
                  <?= \App\Helpers\Security::e($h['raia_descricao']) ?> • <?= \App\Helpers\Security::e($h['conteudo_descricao']) ?>
                </td>
                <td class="py-3 px-3">
                  <span class="inline-flex items-center gap-1 font-bold <?= $h['concluido'] === 'S' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' ?>">
                    <?php if ($h['concluido'] === 'S'): ?>
                      <i class="fa-solid fa-check"></i> 100% (Concluído)
                    <?php else: ?>
                      <?= $h['percentual'] ?>%
                    <?php endif; ?>
                  </span>
                </td>
                <td class="py-3 px-3 text-slate-400 font-mono">
                  <?= \App\Helpers\Format::dateTime($h['data_ultima_atividade']) ?>
                </td>
                <td class="py-3 px-3 text-right">
                  <a href="/desenvolvimento/video/<?= $h['id_video'] ?>" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline">
                    Revisar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
