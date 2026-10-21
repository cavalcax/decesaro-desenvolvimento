<?php
/** @var array $conteudo */
/** @var array $videos */
?>

<div class="space-y-6">

  <!-- Breadcrumbs -->
  <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
    <a href="/desenvolvimento/inicio" class="hover:text-amber-500">Início</a>
    <i class="fa-solid fa-chevron-right text-[9px]"></i>
    <a href="/desenvolvimento/<?= \App\Helpers\Security::e($conteudo['raia_slug']) ?>" class="hover:text-amber-500"><?= \App\Helpers\Security::e($conteudo['raia_descricao']) ?></a>
    <i class="fa-solid fa-chevron-right text-[9px]"></i>
    <span class="text-slate-800 dark:text-slate-200 font-semibold"><?= \App\Helpers\Security::e($conteudo['descricao']) ?></span>
  </nav>

  <!-- Cabeçalho do Conteúdo -->
  <div class="card-decesaro p-6 sm:p-8">
    <div class="flex items-center gap-2 text-xs font-semibold text-amber-600 dark:text-amber-400 mb-1">
      <i class="fa-solid fa-book-bookmark"></i>
      <span class="uppercase tracking-wider">Trilha de Aprendizagem</span>
    </div>

    <h1 class="text-2xl sm:text-3xl font-bold font-serif text-slate-900 dark:text-white">
      <?= \App\Helpers\Security::e($conteudo['descricao']) ?>
    </h1>
    <?php if (!empty($conteudo['subtitulo'])): ?>
      <p class="text-sm font-medium text-amber-600 dark:text-amber-400 mt-1">
        <?= \App\Helpers\Security::e($conteudo['subtitulo']) ?>
      </p>
    <?php endif; ?>

    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 max-w-3xl leading-relaxed">
      <?= nl2br(\App\Helpers\Security::e($conteudo['detalhes'] ?? '')) ?>
    </p>
  </div>

  <!-- Lista de Vídeos / Aulas -->
  <div>
    <h2 class="text-lg font-bold font-serif text-slate-900 dark:text-white mb-3">
      Vídeos e Aulas Disponíveis
    </h2>

    <?php if (empty($videos)): ?>
      <div class="card-decesaro p-8 text-center text-slate-400">
        <i class="fa-solid fa-video-slash text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
        <p class="text-sm">Nenhum vídeo cadastrado neste conteúdo ainda.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($videos as $index => $v): 
          $isCompleted = ($v['concluido'] ?? '') === 'S';
          $percent = (float)($v['percentual'] ?? 0);
        ?>
          <div class="card-decesaro card-hoverable p-4 flex flex-col justify-between">
            <div class="flex items-start gap-4">
              <!-- Thumbnail / Play icon -->
              <div class="w-16 h-14 rounded-xl bg-navy-900 text-amber-400 flex items-center justify-center text-xl flex-shrink-0 shadow-md">
                <?php if ($isCompleted): ?>
                  <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <?php else: ?>
                  <i class="fa-solid fa-play"></i>
                <?php endif; ?>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Aula <?= $index + 1 ?></span>
                  <?php if ($v['exclusivo'] === 'Não'): ?>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">Livre</span>
                  <?php endif; ?>
                  <?php if ($isCompleted): ?>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">Concluído</span>
                  <?php endif; ?>
                </div>

                <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                  <?= \App\Helpers\Security::e($v['titulo']) ?>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">
                  <?= \App\Helpers\Security::e($v['descricao'] ?? '') ?>
                </p>
                <span class="inline-block text-[11px] text-slate-400 mt-2 font-mono">
                  Duração: <?= \App\Helpers\Format::duration((int)$v['duracao_segundos']) ?>
                </span>
              </div>
            </div>

            <!-- Progresso e Botão Assistir -->
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <div class="flex-1 mr-4">
                <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                  <div class="h-full bg-amber-500 rounded-full" style="width: <?= $percent ?>%"></div>
                </div>
              </div>

              <a href="/desenvolvimento/video/<?= $v['id'] ?>" 
                 class="px-4 py-1.5 rounded-lg bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 flex-shrink-0">
                <i class="fa-solid fa-play text-[10px]"></i>
                <span><?= $isCompleted ? 'Reassistir' : ($percent > 0 ? 'Continuar' : 'Assistir') ?></span>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>
