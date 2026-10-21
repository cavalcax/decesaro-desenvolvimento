<?php
/** @var array $user */
/** @var array $raias */
/** @var array $proximosEventos */
/** @var array|null $ultimaAtividade */
?>

<div class="space-y-8">

  <!-- Hero Banner Personalizado com Frase do Conceito (layout.png) -->
  <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-navy-950 via-navy-900 to-navy-800 text-white shadow-xl border border-white/10 p-6 sm:p-10">
    <!-- Efeito sutil de iluminação dourada de fundo -->
    <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-3xl">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-semibold mb-4">
        <i class="fa-solid fa-crown text-[10px]"></i>
        <span>Plataforma de Desenvolvimento • Legado e Futuro</span>
      </div>

      <h1 class="text-2xl sm:text-4xl font-bold font-serif tracking-tight leading-tight mb-2">
        Olá, <span class="text-amber-400"><?= \App\Helpers\Security::e($user['nome']) ?></span>
      </h1>
      <p class="text-sm sm:text-base text-slate-300 mb-6">
        Bem-vindo(a) à sua jornada de desenvolvimento pessoal, familiar e empresarial.
      </p>

      <!-- Cartão da Frase Institucional de Referência -->
      <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 sm:p-5 max-w-xl shadow-lg">
        <p class="text-xs sm:text-sm italic font-serif text-amber-200/90 leading-relaxed">
          "Desenvolver hoje. Preservar o legado. Construir o futuro."
        </p>
        <span class="block text-[11px] text-slate-400 mt-2 font-sans font-medium">— Princípio da Família De Cesaro</span>
      </div>
    </div>
  </div>

  <!-- Seção: Minhas Raias (4 Raias do Layout de Referência) -->
  <div>
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-lg font-bold font-serif text-slate-900 dark:text-white">Minhas Raias</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">Trilhas de desenvolvimento e formação continuada</p>
      </div>
      <a href="/desenvolvimento/jornada" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
        <span>Ver progresso completo</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <?php foreach ($raias as $r): 
        $isLocked = $r['bloqueada'];
        $raiaUrl = "/desenvolvimento/" . $r['slug'];
      ?>
        <div class="card-decesaro card-hoverable p-5 flex flex-col justify-between relative overflow-hidden group <?= $isLocked ? 'opacity-85' : '' ?>">
          <?php if ($isLocked): ?>
            <div class="absolute top-3 right-3 w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 flex items-center justify-center text-xs shadow-sm" title="Disponível conforme elegibilidade de idade">
              <i class="fa-solid fa-lock"></i>
            </div>
          <?php else: ?>
            <div class="absolute top-3 right-3 text-xs font-bold font-mono px-2 py-0.5 rounded-full <?= $r['percentual'] >= 100 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300' ?>">
              <?= $r['percentual'] ?>%
            </div>
          <?php endif; ?>

          <div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl mb-4 group-hover:scale-110 group-hover:bg-amber-500/10 transition-all shadow-sm">
              <i class="fa-solid <?= \App\Helpers\Security::e($r['icone']) ?>"></i>
            </div>

            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
              <?= \App\Helpers\Security::e($r['descricao']) ?>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
              <?= \App\Helpers\Security::e($r['subtitulo']) ?>
            </p>
          </div>

          <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80">
            <!-- Barra de Progresso Real -->
            <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1.5 font-medium">
              <span><?= $r['concluidos'] ?> de <?= $r['elegiveis'] ?> conteúdos</span>
              <span><?= $isLocked ? 'Bloqueada' : ($r['percentual'] >= 100 ? 'Concluída' : 'Em andamento') ?></span>
            </div>
            <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
              <div class="h-full bg-gradient-to-r from-amber-500 to-amber-400 rounded-full transition-all duration-500" style="width: <?= $r['percentual'] ?>%"></div>
            </div>

            <a href="<?= $raiaUrl ?>" class="mt-4 w-full py-2 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-amber-500 hover:text-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-all flex items-center justify-center gap-1.5 group-hover:border-amber-400/40">
              <span>Acessar Raia</span>
              <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Linha Inferior: Próximas Ações e Última Atividade -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Próximas Ações (Agenda / Eventos do layout.png) -->
    <div class="card-decesaro p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-navy-900/10 dark:bg-amber-500/10 text-navy-800 dark:text-amber-400 flex items-center justify-center text-sm">
              <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
              <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white">Próximas Ações</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">Encontros e atividades programadas</p>
            </div>
          </div>
          <a href="/desenvolvimento/agenda" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
            Ver agenda completa
          </a>
        </div>

        <div class="space-y-3">
          <?php if (empty($proximosEventos)): ?>
            <p class="text-xs text-slate-400 py-6 text-center">Nenhum evento agendado para os próximos dias.</p>
          <?php else: ?>
            <?php foreach ($proximosEventos as $evt): 
              $dt = new DateTime($evt['data_inicio']);
              $dia = $dt->format('d');
              $meses = ['JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ'];
              $mes = $meses[(int)$dt->format('m') - 1];
            ?>
              <div class="flex items-start gap-4 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 hover:border-amber-400/40 transition-colors">
                <!-- Badge de Data (ex: 15 MAR do layout de referência) -->
                <div class="flex-shrink-0 w-12 text-center py-2 px-1 rounded-xl bg-navy-900 dark:bg-slate-700 text-white shadow-sm">
                  <span class="block text-base font-bold font-mono leading-none"><?= $dia ?></span>
                  <span class="block text-[10px] font-bold text-amber-400 tracking-wider mt-0.5"><?= $mes ?></span>
                </div>

                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                    <?= \App\Helpers\Security::e($evt['titulo']) ?>
                  </h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                    <?= \App\Helpers\Security::e($evt['descricao'] ?? '') ?>
                  </p>
                  <?php if (!empty($evt['local'])): ?>
                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 mt-1">
                      <i class="fa-solid fa-location-dot text-[10px] text-amber-500"></i>
                      <span><?= \App\Helpers\Security::e($evt['local']) ?></span>
                    </span>
                  <?php endif; ?>
                </div>

                <a href="/desenvolvimento/agenda" class="text-slate-400 hover:text-amber-500 transition-colors text-xs self-center">
                  <i class="fa-solid fa-chevron-right"></i>
                </a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Última Atividade Acessada (layout.png) -->
    <div class="card-decesaro p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-navy-900/10 dark:bg-amber-500/10 text-navy-800 dark:text-amber-400 flex items-center justify-center text-sm">
              <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
              <h3 class="text-base font-bold font-serif text-slate-900 dark:text-white">Última Atividade</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">Retome de onde você parou</p>
            </div>
          </div>
          <?php if ($ultimaAtividade): ?>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $ultimaAtividade['concluido'] === 'S' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' ?>">
              <?= $ultimaAtividade['concluido'] === 'S' ? 'Concluído' : $ultimaAtividade['percentual'] . '% assistido' ?>
            </span>
          <?php endif; ?>
        </div>

        <?php if ($ultimaAtividade): ?>
          <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800/80 dark:to-slate-800/40 border border-slate-200 dark:border-slate-700">
            <div class="flex items-start gap-4">
              <!-- Thumbnail representativa com ícone de reprodução -->
              <div class="w-20 h-16 rounded-xl bg-navy-900 text-amber-400 flex items-center justify-center text-xl flex-shrink-0 shadow-md">
                <i class="fa-solid fa-circle-play"></i>
              </div>

              <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                  <?= \App\Helpers\Security::e($ultimaAtividade['raia_descricao']) ?> • <?= \App\Helpers\Security::e($ultimaAtividade['conteudo_descricao']) ?>
                </span>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">
                  <?= \App\Helpers\Security::e($ultimaAtividade['video_titulo']) ?>
                </h4>
                <p class="text-xs text-slate-400 mt-1">
                  Último acesso em <?= \App\Helpers\Format::dateTime($ultimaAtividade['data_ultima_atividade']) ?>
                </p>
              </div>
            </div>

            <!-- Botão Retomar Vídeo -->
            <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
              <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                <?= \App\Helpers\Format::duration((int)$ultimaAtividade['posicao_atual']) ?> de <?= \App\Helpers\Format::duration((int)$ultimaAtividade['duracao_segundos']) ?>
              </span>
              <a href="/desenvolvimento/video/<?= $ultimaAtividade['id_video'] ?>" 
                 class="px-4 py-1.5 rounded-lg bg-navy-900 hover:bg-navy-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white dark:text-slate-950 text-xs font-bold shadow-md transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-play text-[10px]"></i>
                <span><?= $ultimaAtividade['concluido'] === 'S' ? 'Rever Aula' : 'Continuar Assistindo' ?></span>
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="p-6 text-center text-slate-400">
            <i class="fa-solid fa-graduation-cap text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
            <p class="text-xs">Você ainda não iniciou nenhum vídeo. Escolha uma raia acima para começar sua jornada!</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Cartão Informativo de Objetivo (layout.png) -->
  <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/40 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-3">
    <i class="fa-solid fa-compass text-amber-600 dark:text-amber-400 text-base mt-0.5"></i>
    <div>
      <span class="font-bold">Objetivo da Plataforma:</span>
      <p class="text-amber-800/90 dark:text-amber-300/80 mt-0.5">
        Visão geral da jornada do participante com acesso rápido às raias de desenvolvimento, próximas ações do calendário e acompanhamento contínuo da formação das próximas gerações da Família De Cesaro.
      </p>
    </div>
  </div>

</div>
