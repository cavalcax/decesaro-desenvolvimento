<?php
/** @var array $raia */
/** @var string $title */
/** @var string $subtitle */
/** @var string $description */
/** @var array $conteudos */
/** @var int $progressoGeral */
/** @var int $elegiveisCount */
/** @var int $concluidosCount */
?>

<div class="space-y-6">

  <!-- Header Banner da Raia com Identidade Visual (layout.png) -->
  <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-navy-950 via-navy-900 to-navy-800 text-white shadow-xl border border-white/10 p-6 sm:p-8">
    <div class="relative z-10 max-w-3xl">
      <div class="flex items-center gap-2 text-xs font-semibold text-amber-400 mb-2">
        <i class="fa-solid <?= \App\Helpers\Security::e($raia['icone']) ?>"></i>
        <span class="uppercase tracking-wider">Raia de Desenvolvimento</span>
      </div>
      
      <h1 class="text-2xl sm:text-3xl font-bold font-serif text-white tracking-tight">
        <?= \App\Helpers\Security::e($title) ?>
      </h1>
      <p class="text-sm font-medium text-amber-300/90 mt-1">
        <?= \App\Helpers\Security::e($subtitle) ?>
      </p>
      <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-2xl leading-relaxed">
        <?= \App\Helpers\Security::e($description) ?>
      </p>

      <!-- Barra de Progresso Geral da Raia -->
      <div class="mt-6 max-w-md bg-white/10 backdrop-blur-md p-3 rounded-2xl border border-white/10">
        <div class="flex items-center justify-between text-xs text-slate-300 mb-1.5 font-medium">
          <span>Seu progresso nesta raia:</span>
          <span class="font-mono text-amber-400 font-bold"><?= $progressoGeral ?>% (<?= $concluidosCount ?> de <?= $elegiveisCount ?> concluídos)</span>
        </div>
        <div class="w-full h-2 rounded-full bg-slate-900/60 overflow-hidden">
          <div class="h-full bg-gradient-to-r from-amber-500 to-amber-300 rounded-full transition-all duration-500" style="width: <?= $progressoGeral ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <?php if (empty($isRaiaElegivel) && !\App\Helpers\Auth::isAdmin()): ?>
    <!-- Aviso de Raia Bloqueada por Faixa Etária -->
    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-200 flex items-start gap-3.5 shadow-sm">
      <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm flex-shrink-0 mt-0.5">
        <i class="fa-solid fa-lock"></i>
      </div>
      <div>
        <h3 class="text-sm font-bold font-serif text-slate-900 dark:text-white">Raia Bloqueada por Idade</h3>
        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
          Esta raia é destinada a participantes <strong><?= (int)($raia['idade_minima'] ?? 0) ?>+ anos</strong><?= !empty($raia['idade_maxima']) ? " (até {$raia['idade_maxima']} anos)" : "" ?>.
          Os conteúdos e vídeos estão bloqueados para sua faixa etária atual e serão liberados automaticamente quando você atingir a idade recomendada.
        </p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Abas de Navegação Interna da Raia (Visão geral, Atividades, Materiais - conforme layout.png) -->
  <div class="border-b border-slate-200 dark:border-slate-800">
    <nav class="flex space-x-6 text-sm font-medium">
      <button class="pb-3 border-b-2 border-amber-500 text-amber-600 dark:text-amber-400 font-bold flex items-center gap-2">
        <i class="fa-regular fa-compass"></i>
        <span>Visão Geral</span>
      </button>
      <button class="pb-3 border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center gap-2">
        <i class="fa-solid fa-list-check"></i>
        <span>Atividades</span>
      </button>
      <button class="pb-3 border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center gap-2">
        <i class="fa-regular fa-file-lines"></i>
        <span>Materiais</span>
      </button>
    </nav>
  </div>

  <!-- Lista de Conteúdos da Raia em Cartões Elegantes (layout.png) -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($conteudos as $c): 
      $isEligible = $c['is_elegivel'];
    ?>
      <div class="card-decesaro card-hoverable p-5 flex flex-col justify-between relative overflow-hidden <?= !$isEligible ? 'bg-slate-50/50 dark:bg-slate-900/40 border-dashed opacity-80' : '' ?>">
        <div>
          <!-- Cabeçalho do Card -->
          <div class="flex items-start justify-between gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-navy-900/10 dark:bg-amber-500/10 text-navy-800 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0">
              <?php if (!$isEligible): ?>
                <i class="fa-solid fa-lock text-slate-400"></i>
              <?php elseif ($c['is_concluido']): ?>
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
              <?php else: ?>
                <i class="fa-solid fa-play text-amber-600 dark:text-amber-400"></i>
              <?php endif; ?>
            </div>

            <?php if (!$isEligible): ?>
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                <i class="fa-solid fa-lock text-[9px]"></i> Bloqueado por Idade
              </span>
            <?php elseif ($c['is_concluido']): ?>
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                <i class="fa-solid fa-check text-[9px]"></i> Concluído
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                <?= $c['videos_concluidos'] ?>/<?= $c['total_videos'] ?> aulas
              </span>
            <?php endif; ?>
          </div>

          <h3 class="text-base font-bold text-slate-900 dark:text-white">
            <?= \App\Helpers\Security::e($c['descricao']) ?>
          </h3>
          <p class="text-xs font-medium text-amber-600 dark:text-amber-400 mt-0.5">
            <?= \App\Helpers\Security::e($c['subtitulo'] ?? '') ?>
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-3 leading-relaxed">
            <?= \App\Helpers\Security::e($c['detalhes'] ?? 'Conteúdo estruturado para formação e fortalecimento dos participantes.') ?>
          </p>
        </div>

        <!-- Ação do Card -->
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
          <?php if ($isEligible): ?>
            <a href="/desenvolvimento/conteudo/<?= $c['id'] ?>" 
               class="w-full py-2 px-3 rounded-lg bg-navy-900 hover:bg-navy-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white dark:text-slate-200 text-xs font-semibold shadow-sm transition-all flex items-center justify-center gap-2">
              <span><?= $c['is_concluido'] ? 'Revisar Conteúdo' : 'Acessar Aulas' ?></span>
              <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
          <?php else: ?>
            <div class="w-full py-2 px-3 rounded-lg bg-slate-100 dark:bg-slate-800/50 text-slate-400 text-xs text-center font-medium cursor-not-allowed">
              <i class="fa-solid fa-lock mr-1 text-[10px]"></i> Disponível conforme elegibilidade
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Bloco Conceitual do Mapa de Telas -->
  <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 flex items-start gap-3">
    <i class="fa-solid fa-circle-info text-amber-500 text-base mt-0.5"></i>
    <div>
      <span class="font-bold text-slate-900 dark:text-white">Critérios de Elegibilidade:</span>
      <p class="mt-0.5">
        A apresentação das atividades, conteúdos e materiais respeita a faixa etária e a sequência definida pelo comitê de desenvolvimento. Conteúdos exclusivos são liberados automaticamente à medida em que o participante atinge os níveis correspondentes.
      </p>
    </div>
  </div>

</div>
