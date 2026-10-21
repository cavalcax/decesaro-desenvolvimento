<?php
/** @var array $video */
/** @var array $progresso */
/** @var array $playlist */
/** @var float $completionThreshold */
/** @var int $syncInterval */
?>

<div class="space-y-3 sm:space-y-4">

  <!-- Breadcrumbs -->
  <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
    <a href="/desenvolvimento/inicio" class="hover:text-amber-500">Início</a>
    <i class="fa-solid fa-chevron-right text-[9px]"></i>
    <a href="/desenvolvimento/<?= \App\Helpers\Security::e($video['raia_slug']) ?>"
      class="hover:text-amber-500"><?= \App\Helpers\Security::e($video['raia_descricao']) ?></a>
    <i class="fa-solid fa-chevron-right text-[9px]"></i>
    <a href="/desenvolvimento/conteudo/<?= $video['id_raia_conteudo'] ?>"
      class="hover:text-amber-500"><?= \App\Helpers\Security::e($video['conteudo_descricao']) ?></a>
    <i class="fa-solid fa-chevron-right text-[9px]"></i>
    <span
      class="text-slate-800 dark:text-slate-200 font-semibold truncate"><?= \App\Helpers\Security::e($video['titulo']) ?></span>
  </nav>

  <!-- Layout em 2 Colunas: Player Principal à Esquerda, Playlist à Direita -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5">

    <!-- Coluna Principal: Player e Detalhes (2 colunas no desktop) -->
    <div class="lg:col-span-2 space-y-3">

      <!-- Container do Player HTML5 Customizado com Altura Generosa -->
      <div id="player-wrapper" class="w-full aspect-video min-h-[420px] sm:min-h-[500px] lg:min-h-[560px]"></div>

      <!-- Detalhes do Vídeo (Compacto) -->
      <div class="card-decesaro px-4 py-3 sm:px-5 sm:py-3.5">
        <div
          class="flex flex-wrap items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100 dark:border-slate-800">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
              <?= \App\Helpers\Security::e($video['raia_descricao']) ?> •
              <?= \App\Helpers\Security::e($video['conteudo_descricao']) ?>
            </span>
            <h1 class="text-base sm:text-lg font-bold font-serif text-slate-900 dark:text-white mt-0.5">
              <?= \App\Helpers\Security::e($video['titulo']) ?>
            </h1>
          </div>

          <div class="flex items-center gap-2">
            <button id="btn-manual-complete"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all flex items-center gap-1.5 <?= $progresso['concluido'] === 'S' ? 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700' : 'bg-slate-100 dark:bg-slate-800 hover:bg-emerald-500 hover:text-white border-slate-200 dark:border-slate-700' ?>">
              <i class="fa-solid fa-check text-xs"></i>
              <span
                id="manual-complete-text"><?= $progresso['concluido'] === 'S' ? 'Aula Concluída' : 'Marcar como Concluída' ?></span>
            </button>
          </div>
        </div>

        <div class="mt-2.5 text-xs text-slate-600 dark:text-slate-300 leading-normal">
          <?= nl2br(\App\Helpers\Security::e($video['descricao'] ?? 'Esta aula faz parte da formação continuada da Família De Cesaro.')) ?>
        </div>

        <!-- Atalhos do Teclado (Compacto)
        <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center gap-2.5 text-[10px] text-slate-400">
          <span class="font-bold text-slate-600 dark:text-slate-300"><i class="fa-regular fa-keyboard mr-1"></i>Atalhos:</span>
          <span><kbd class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-mono text-[9px]">Espaço</kbd> Play/Pause</span>
          <span><kbd class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-mono text-[9px]">← / →</kbd> 5s</span>
          <span><kbd class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-mono text-[9px]">↑ / ↓</kbd> Volume</span>
          <span><kbd class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-mono text-[9px]">F</kbd> Tela Cheia</span>
          <span><kbd class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 font-mono text-[9px]">M</kbd> Mudo</span>
        </div>
         -->
      </div>

    </div>

    <!-- Coluna Lateral: Playlist do Módulo (1 coluna) -->
    <div class="space-y-3">
      <div class="card-decesaro px-4 py-3.5 sm:px-5 sm:py-3.5">
        <h3
          class="text-xs sm:text-sm font-bold font-serif text-slate-900 dark:text-white uppercase tracking-wider mb-0.5">
          Aulas deste Conteúdo
        </h3>
        <p class="text-[11px] text-slate-400 mb-2.5"><?= count($playlist) ?> vídeos organizados</p>

        <div class="space-y-1.5">
          <?php foreach ($playlist as $index => $item):
            $isCurrent = (int) $item['id'] === (int) $video['id'];
            $isItemDone = ($item['concluido'] ?? '') === 'S';
            ?>
            <a href="/desenvolvimento/video/<?= $item['id'] ?>"
              class="flex items-center gap-2.5 p-2.5 rounded-xl border text-xs transition-all <?= $isCurrent ? 'bg-amber-500/10 border-amber-500/50 text-amber-700 dark:text-amber-300 font-bold' : 'border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-300' ?>">

              <div
                class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0 text-[11px] <?= $isCurrent ? 'bg-amber-500 text-slate-950 font-bold' : ($isItemDone ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-400') ?>">
                <?php if ($isItemDone): ?>
                  <i class="fa-solid fa-check text-[9px]"></i>
                <?php else: ?>
                  <?= $index + 1 ?>
                <?php endif; ?>
              </div>

              <div class="flex-1 min-w-0">
                <span class="block truncate"><?= \App\Helpers\Security::e($item['titulo']) ?></span>
                <span
                  class="block text-[10px] text-slate-400 font-mono mt-0.5"><?= \App\Helpers\Format::duration((int) $item['duracao_segundos']) ?></span>
              </div>

              <?php if ($isCurrent): ?>
                <i class="fa-solid fa-volume-high text-amber-500 text-xs flex-shrink-0"></i>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>

</div>

<!-- Inicialização do Player com os Dados Reais -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const player = new DecesaroVideoPlayer('#player-wrapper', {
      videoId: <?= (int) $video['id'] ?>,
      src: '<?= \App\Helpers\Security::e($video['link_arquivo']) ?>',
      title: '<?= \App\Helpers\Security::e($video['titulo']) ?>',
      duration: <?= (int) ($video['duracao_segundos'] ?? 300) ?>,
      initialPosition: <?= (int) $progresso['posicao_atual'] ?>,
      isCompleted: <?= $progresso['concluido'] === 'S' ? 'true' : 'false' ?>,
      completionThreshold: <?= (float) $completionThreshold ?>,
      syncIntervalSeconds: <?= (int) $syncInterval ?>
    });

    // Botão Conclusão Manual
    const manualBtn = document.getElementById('btn-manual-complete');
    if (manualBtn) {
      manualBtn.addEventListener('click', async () => {
        try {
          const res = await apiRequest('/desenvolvimento/api/video-complete', {
            method: 'POST',
            data: {
              video_id: <?= (int) $video['id'] ?>,
              metodo: 'manual'
            }
          });

          document.getElementById('manual-complete-text').textContent = 'Aula Concluída';
          manualBtn.className = 'px-3.5 py-1.5 rounded-lg border text-xs font-semibold bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700 flex items-center gap-1.5';

          Swal.fire({
            icon: 'success',
            title: 'Aula Concluída!',
            text: 'Seu progresso foi registrado com sucesso.',
            confirmButtonColor: '#1b3b5f'
          });
        } catch { }
      });
    }
  });
</script>