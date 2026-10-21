<?php
/** @var string $tipo */
/** @var string $title */
/** @var string $subtitle */
/** @var array $headers */
/** @var array $rows */
?>

<div class="space-y-6">

  <!-- Header -->
  <div class="card-decesaro p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Comitê de
        Desenvolvimento</span>
      <h1 class="text-2xl font-bold font-serif text-slate-900 dark:text-white mt-0.5">Relatórios Institucionais</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Exportação de métricas, acompanhamento de jornada e
        relatórios analíticos</p>
    </div>

    <!-- Botões de Exportação CSV e PDF -->
    <div class="flex items-center gap-2">
      <a href="/desenvolvimento/admin/relatorios?tipo=<?= $tipo ?>&export=csv"
        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all flex items-center gap-2 border border-slate-200 dark:border-slate-700 shadow-sm">
        <i class="fa-solid fa-file-csv text-emerald-600"></i>
        <span>Exportar CSV</span>
      </a>

      <a href="/desenvolvimento/admin/relatorios?tipo=<?= $tipo ?>&export=pdf" target="_blank"
        class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold text-xs shadow-md transition-all flex items-center gap-2">
        <i class="fa-solid fa-file-pdf"></i>
        <span>Exportar PDF</span>
      </a>
    </div>
  </div>

  <!-- Seleção de Tipo de Relatório -->
  <div class="card-decesaro p-4">
    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-2">Selecione o Modelo de Relatório:</label>
    <div class="flex flex-wrap items-center gap-2">
      <a href="/desenvolvimento/admin/relatorios?tipo=participantes"
        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $tipo === 'participantes' ? 'bg-navy-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' ?>">
        <i class="fa-solid fa-user-check mr-1.5"></i>Progresso por Participante
      </a>

      <a href="/desenvolvimento/admin/relatorios?tipo=familias"
        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $tipo === 'familias' ? 'bg-navy-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' ?>">
        <i class="fa-solid fa-people-roof mr-1.5"></i>Progresso por Núcleo Familiar
      </a>

      <a href="/desenvolvimento/admin/relatorios?tipo=videos"
        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $tipo === 'videos' ? 'bg-navy-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' ?>">
        <i class="fa-solid fa-video mr-1.5"></i>Aulas Mais Assistidas
      </a>

      <a href="/desenvolvimento/admin/relatorios?tipo=eventos"
        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $tipo === 'eventos' ? 'bg-navy-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' ?>">
        <i class="fa-solid fa-calendar-days mr-1.5"></i>Eventos e Presenças
      </a>
    </div>
  </div>

  <!-- Tabela de Pré-visualização dos Dados -->
  <div class="card-decesaro p-6">
    <div class="mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
      <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white"><?= \App\Helpers\Security::e($title) ?>
      </h3>
      <p class="text-xs text-slate-400 mt-0.5"><?= \App\Helpers\Security::e($subtitle) ?> • <?= count($rows) ?>
        registros localizados</p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
        <thead
          class="bg-slate-50 dark:bg-slate-800/80 uppercase text-[10px] tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
          <tr>
            <?php foreach ($headers as $h): ?>
              <th class="py-3 px-4 font-bold"><?= \App\Helpers\Security::e($h) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="<?= count($headers) ?>" class="py-8 text-center text-slate-400">
                Nenhum registro encontrado para este relatório.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $row): ?>
              <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                <?php foreach ($row as $val): ?>
                  <td class="py-3 px-4"><?= \App\Helpers\Security::e((string) $val) ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>