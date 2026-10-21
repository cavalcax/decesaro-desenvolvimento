/**
 * Player de Vídeo HTML5 Customizado Institucional
 * Plataforma de Desenvolvimento da Família De Cesaro
 */

class DecesaroVideoPlayer {
  constructor(containerElement, options = {}) {
    this.container = typeof containerElement === 'string' ? document.querySelector(containerElement) : containerElement;
    if (!this.container) return;

    this.videoId = options.videoId;
    this.src = options.src;
    this.title = options.title || 'Vídeo';
    this.duration = options.duration || 0;
    this.initialPosition = options.initialPosition || 0;
    this.completionThreshold = options.completionThreshold || 90; // 90% padrão
    this.syncIntervalSeconds = options.syncIntervalSeconds || 5;

    this.source = this.detectSource(this.src);

    // Métricas de progresso
    this.effectiveWatchTime = 0;
    this.watchedRanges = []; // [[start, end]]
    this.lastTimeRecorded = null;
    this.isCompleted = options.isCompleted || false;
    this.syncTimer = null;

    this.render();
    this.bindEvents();
  }

  detectSource(url) {
    if (!url) return { type: 'html5', url: '' };

    // Google Drive
    // Formatos aceitos:
    // https://drive.google.com/file/d/ID/view?usp=drive_link
    // https://drive.google.com/file/d/ID/preview
    // https://drive.google.com/open?id=ID
    const driveMatch = url.match(/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/i);
    if (driveMatch) {
      const id = driveMatch[1];
      return {
        type: 'iframe',
        provider: 'googledrive',
        id: id,
        embedUrl: `https://drive.google.com/file/d/${id}/preview`,
        rawUrl: url,
        openUrl: `https://drive.google.com/file/d/${id}/view`
      };
    }

    // YouTube
    const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i);
    if (ytMatch) {
      const id = ytMatch[1];
      return {
        type: 'iframe',
        provider: 'youtube',
        id: id,
        embedUrl: `https://www.youtube.com/embed/${id}?autoplay=1&rel=0`,
        rawUrl: url,
        openUrl: `https://www.youtube.com/watch?v=${id}`
      };
    }

    // Vimeo
    const vimeoMatch = url.match(/vimeo\.com\/(?:video\/)?([0-9]+)/i);
    if (vimeoMatch) {
      const id = vimeoMatch[1];
      return {
        type: 'iframe',
        provider: 'vimeo',
        id: id,
        embedUrl: `https://player.vimeo.com/video/${id}`,
        rawUrl: url,
        openUrl: url
      };
    }

    return {
      type: 'html5',
      provider: 'native',
      rawUrl: url
    };
  }

  render() {
    this.container.className = 'video-player-container w-full aspect-video min-h-[420px] sm:min-h-[500px] lg:min-h-[560px] group relative bg-black rounded-2xl overflow-hidden shadow-2xl focus:outline-none';
    this.container.setAttribute('tabindex', '0');

    if (this.source.type === 'iframe') {
      this.container.innerHTML = `
        <div class="relative w-full h-full min-h-[420px] sm:min-h-[500px] lg:min-h-[560px] bg-slate-950 flex flex-col justify-center items-center overflow-hidden rounded-2xl border border-slate-800">
          <iframe 
            src="${this.source.embedUrl}" 
            class="w-full h-full min-h-[420px] sm:min-h-[500px] lg:min-h-[560px] border-0 rounded-2xl" 
            allow="autoplay; fullscreen; encrypted-media; picture-in-picture" 
            allowfullscreen>
          </iframe>

          <!-- Botão de Acesso Direto Externo -->
          <div class="absolute top-3 right-3 z-20 pointer-events-auto">
            <a href="${this.source.openUrl}" target="_blank" rel="noopener noreferrer" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900/90 hover:bg-slate-900 text-amber-400 hover:text-amber-300 border border-amber-400/30 text-xs font-semibold shadow-xl backdrop-blur-md transition-all">
              <i class="${this.source.provider === 'googledrive' ? 'fa-brands fa-google-drive' : 'fa-solid fa-arrow-up-right-from-square'}"></i>
              <span>${this.source.provider === 'googledrive' ? 'Abrir no Google Drive' : 'Abrir link original'}</span>
              <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
          </div>
        </div>
      `;
      return;
    }

    this.container.innerHTML = `
      <video class="w-full h-full object-contain" preload="metadata" playsinline src="${this.src}"></video>
      
      <!-- Loading Spinner -->
      <div class="video-loading-indicator absolute inset-0 flex items-center justify-center bg-black/40 pointer-events-none transition-opacity duration-200">
        <div class="w-12 h-12 border-4 border-amber-500/30 border-t-amber-500 rounded-full animate-spin"></div>
      </div>

      <!-- Big Center Play Button (overlay) -->
      <div class="video-center-play-btn absolute inset-0 flex items-center justify-center cursor-pointer pointer-events-auto bg-black/20 group-hover:bg-black/30 transition-all">
        <button class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center rounded-full bg-navy-900/90 text-amber-400 border border-amber-400/40 shadow-xl hover:scale-105 active:scale-95 transition-transform" aria-label="Reproduzir">
          <i class="fa-solid fa-play text-2xl sm:text-3xl ml-1"></i>
        </button>
      </div>

      <!-- Controles Inferiores -->
      <div class="video-controls select-none">
        <!-- Barra de Progresso com Scrub -->
        <div class="video-progress-bar">
          <div class="video-progress-filled"></div>
        </div>

        <div class="flex items-center justify-between gap-4 text-white text-xs sm:text-sm mt-1">
          <!-- Esquerda: Play/Pause, Volume, Tempo -->
          <div class="flex items-center gap-3">
            <button class="btn-play-pause text-amber-400 hover:text-amber-300 transition-colors" title="Reproduzir/Pausar (Espaço)">
              <i class="fa-solid fa-play text-base sm:text-lg"></i>
            </button>

            <div class="flex items-center gap-2 group/volume relative">
              <button class="btn-mute text-slate-300 hover:text-white transition-colors" title="Mudo (M)">
                <i class="fa-solid fa-volume-high text-sm sm:text-base"></i>
              </button>
              <input type="range" class="volume-slider w-16 sm:w-20 h-1 bg-white/30 accent-amber-500 rounded-lg cursor-pointer" min="0" max="1" step="0.05" value="1">
            </div>

            <div class="time-display font-mono text-xs text-slate-300">
              <span class="current-time">0:00</span> / <span class="total-duration">0:00</span>
            </div>
          </div>

          <!-- Direita: Velocidade, Tela Cheia, Badge de Conclusão -->
          <div class="flex items-center gap-3">
            <span class="completion-badge hidden px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
              <i class="fa-solid fa-check mr-1"></i>Concluído
            </span>

            <!-- Velocidade (máx 1.5x) -->
            <div class="relative speed-menu-container">
              <button class="btn-speed px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-xs font-semibold text-amber-300 transition-colors" title="Velocidade de reprodução">
                1.0x
              </button>
              <div class="speed-options hidden absolute bottom-full mb-2 right-0 bg-slate-900 border border-slate-700 rounded-lg shadow-xl py-1 text-xs z-30">
                <button data-speed="0.5" class="speed-option block w-full px-4 py-1.5 text-left hover:bg-navy-800 text-slate-200">0.5x</button>
                <button data-speed="0.75" class="speed-option block w-full px-4 py-1.5 text-left hover:bg-navy-800 text-slate-200">0.75x</button>
                <button data-speed="1.0" class="speed-option block w-full px-4 py-1.5 text-left hover:bg-navy-800 font-bold text-amber-400">1.0x (Padrão)</button>
                <button data-speed="1.25" class="speed-option block w-full px-4 py-1.5 text-left hover:bg-navy-800 text-slate-200">1.25x</button>
                <button data-speed="1.5" class="speed-option block w-full px-4 py-1.5 text-left hover:bg-navy-800 text-slate-200">1.5x (Máx)</button>
              </div>
            </div>

            <button class="btn-fullscreen text-slate-300 hover:text-white transition-colors" title="Tela cheia (F)">
              <i class="fa-solid fa-expand text-sm sm:text-base"></i>
            </button>
          </div>
        </div>
      </div>
    `;

    this.video = this.container.querySelector('video');
    this.playBtn = this.container.querySelector('.btn-play-pause');
    this.centerPlayBtn = this.container.querySelector('.video-center-play-btn');
    this.muteBtn = this.container.querySelector('.btn-mute');
    this.volumeSlider = this.container.querySelector('.volume-slider');
    this.progressBar = this.container.querySelector('.video-progress-bar');
    this.progressFilled = this.container.querySelector('.video-progress-filled');
    this.currentTimeSpan = this.container.querySelector('.current-time');
    this.totalDurationSpan = this.container.querySelector('.total-duration');
    this.speedBtn = this.container.querySelector('.btn-speed');
    this.speedMenu = this.container.querySelector('.speed-options');
    this.fullscreenBtn = this.container.querySelector('.btn-fullscreen');
    this.loadingIndicator = this.container.querySelector('.video-loading-indicator');
    this.completionBadge = this.container.querySelector('.completion-badge');

    if (this.isCompleted) {
      this.completionBadge.classList.remove('hidden');
    }
  }

  bindEvents() {
    if (this.source.type === 'iframe') {
      // Para vídeos incorporados via Google Drive / YouTube:
      let activeSeconds = this.initialPosition || 0;
      this.syncTimer = setInterval(() => {
        if (document.visibilityState === 'visible') {
          activeSeconds += this.syncIntervalSeconds;
          this.effectiveWatchTime = activeSeconds;
          if (activeSeconds % 15 === 0) {
            this.syncIframeProgress(activeSeconds);
          }
        }
      }, this.syncIntervalSeconds * 1000);

      window.addEventListener('beforeunload', () => {
        this.syncIframeProgress(activeSeconds, true);
      });
      return;
    }

    // Carregamento de metadados
    this.video.addEventListener('loadedmetadata', () => {
      this.totalDurationSpan.textContent = this.formatTime(this.video.duration);
      this.loadingIndicator.classList.add('opacity-0');

      // Retomar da posição inicial salva
      if (this.initialPosition > 0 && this.initialPosition < this.video.duration) {
        this.video.currentTime = this.initialPosition;
      }
    });

    this.video.addEventListener('waiting', () => {
      this.loadingIndicator.classList.remove('opacity-0');
    });

    this.video.addEventListener('playing', () => {
      this.loadingIndicator.classList.add('opacity-0');
      this.centerPlayBtn.classList.add('hidden');
      this.container.classList.remove('paused');
      this.playBtn.innerHTML = '<i class="fa-solid fa-pause text-base sm:text-lg"></i>';
      this.lastTimeRecorded = this.video.currentTime;
      this.startSyncTimer();
    });

    this.video.addEventListener('pause', () => {
      this.centerPlayBtn.classList.remove('hidden');
      this.container.classList.add('paused');
      this.playBtn.innerHTML = '<i class="fa-solid fa-play text-base sm:text-lg"></i>';
      this.syncProgress();
      this.stopSyncTimer();
    });

    // Atualização de tempo e barra
    this.video.addEventListener('timeupdate', () => {
      this.onTimeUpdate();
    });

    // Fim da reprodução do vídeo (conclusão automática)
    this.video.addEventListener('ended', () => {
      this.checkCompletion(true);
    });

    // Play/Pause nos botões e no container
    this.playBtn.addEventListener('click', () => this.togglePlay());
    this.centerPlayBtn.addEventListener('click', () => this.togglePlay());
    this.video.addEventListener('click', () => this.togglePlay());

    // Controle de volume e mute
    this.muteBtn.addEventListener('click', () => this.toggleMute());
    this.volumeSlider.addEventListener('input', (e) => {
      this.video.volume = parseFloat(e.target.value);
      this.video.muted = this.video.volume === 0;
      this.updateVolumeIcon();
    });

    // Scrub na barra de progresso
    let isScrubbing = false;
    const scrub = (e) => {
      const rect = this.progressBar.getBoundingClientRect();
      const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
      this.video.currentTime = pos * this.video.duration;
    };

    this.progressBar.addEventListener('mousedown', (e) => {
      isScrubbing = true;
      scrub(e);
    });

    window.addEventListener('mousemove', (e) => {
      if (isScrubbing) scrub(e);
    });

    window.addEventListener('mouseup', () => {
      if (isScrubbing) {
        isScrubbing = false;
        this.lastTimeRecorded = this.video.currentTime;
      }
    });

    // Velocidade
    this.speedBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      this.speedMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
      this.speedMenu.classList.add('hidden');
    });

    this.container.querySelectorAll('.speed-option').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const speed = parseFloat(e.target.getAttribute('data-speed'));
        if (speed <= 1.5) {
          this.video.playbackRate = speed;
          this.speedBtn.textContent = `${speed}x`;
          this.container.querySelectorAll('.speed-option').forEach(o => {
            o.classList.remove('font-bold', 'text-amber-400');
          });
          btn.classList.add('font-bold', 'text-amber-400');
        }
      });
    });

    // Tela Cheia
    this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());

    // Atalhos de teclado quando em foco
    this.container.addEventListener('keydown', (e) => {
      if (['Space', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'KeyF', 'KeyM'].includes(e.code)) {
        e.preventDefault();
      }
      switch (e.code) {
        case 'Space':
          this.togglePlay();
          break;
        case 'ArrowLeft':
          this.video.currentTime = Math.max(0, this.video.currentTime - 5);
          this.lastTimeRecorded = this.video.currentTime;
          break;
        case 'ArrowRight':
          this.video.currentTime = Math.min(this.video.duration, this.video.currentTime + 5);
          this.lastTimeRecorded = this.video.currentTime;
          break;
        case 'ArrowUp':
          this.video.volume = Math.min(1, this.video.volume + 0.1);
          this.volumeSlider.value = this.video.volume;
          this.updateVolumeIcon();
          break;
        case 'ArrowDown':
          this.video.volume = Math.max(0, this.video.volume - 0.1);
          this.volumeSlider.value = this.video.volume;
          this.updateVolumeIcon();
          break;
        case 'KeyM':
          this.toggleMute();
          break;
        case 'KeyF':
          this.toggleFullscreen();
          break;
      }
    });

    // Salvar progresso antes de fechar página
    window.addEventListener('beforeunload', () => {
      this.syncProgress(true);
    });
  }

  onTimeUpdate() {
    if (!this.video.duration) return;

    const current = this.video.currentTime;
    const duration = this.video.duration;
    const percent = (current / duration) * 100;

    this.progressFilled.style.width = `${percent}%`;
    this.currentTimeSpan.textContent = this.formatTime(current);

    // Contabilizar tempo assistido real (se a reprodução avançou continuamente)
    if (this.lastTimeRecorded !== null && !this.video.paused) {
      const delta = current - this.lastTimeRecorded;
      // Se delta for razoável (entre 0 e 2s), adiciona ao tempo efetivo
      if (delta > 0 && delta < 2.5) {
        this.effectiveWatchTime += delta;
        this.recordWatchedRange(this.lastTimeRecorded, current);
      }
    }
    this.lastTimeRecorded = current;

    // Verificar se atingiu meta de 90% de cobertura
    this.checkCompletion();
  }

  recordWatchedRange(start, end) {
    this.watchedRanges.push([start, end]);
  }

  calculateCoveragePercent() {
    if (!this.video.duration) return 0;
    // Mesclar intervalos sobrepostos
    const sorted = [...this.watchedRanges].sort((a, b) => a[0] - b[0]);
    if (sorted.length === 0) return (this.video.currentTime / this.video.duration) * 100;

    let merged = [sorted[0]];
    for (let i = 1; i < sorted.length; i++) {
      let prev = merged[merged.length - 1];
      let curr = sorted[i];
      if (curr[0] <= prev[1]) {
        prev[1] = Math.max(prev[1], curr[1]);
      } else {
        merged.push(curr);
      }
    }

    const totalSeconds = merged.reduce((acc, range) => acc + (range[1] - range[0]), 0);
    return Math.min(100, (totalSeconds / this.video.duration) * 100);
  }

  async checkCompletion(force = false) {
    if (this.isCompleted) return;

    const coverage = this.video ? this.calculateCoveragePercent() : (force ? 100 : 0);
    if (force || coverage >= this.completionThreshold) {
      this.isCompleted = true;
      if (this.completionBadge) {
        this.completionBadge.classList.remove('hidden');
      }

      // Atualiza botão na página
      const manualBtn = document.getElementById('btn-manual-complete');
      const manualText = document.getElementById('manual-complete-text');
      if (manualBtn && manualText) {
        manualText.textContent = 'Aula Concluída';
        manualBtn.className = 'px-3 py-1.5 rounded-lg border text-xs font-semibold bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700 flex items-center gap-1.5';
      }

      try {
        await apiRequest('/desenvolvimento/api/video-complete', {
          method: 'POST',
          data: {
            video_id: this.videoId,
            posicao: this.video ? Math.round(this.video.currentTime) : (this.duration || 0),
            tempo_assistido: Math.round(this.effectiveWatchTime),
            percentual: 100.0,
            metodo: force ? 'automatico' : (coverage >= this.completionThreshold ? 'automatico' : 'manual')
          },
          loading: false
        });

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Parabéns!',
            text: 'Você concluiu esta aula! Seu progresso na jornada foi atualizado com sucesso.',
            confirmButtonColor: '#1b3b5f'
          });
        }
      } catch (e) {
        console.error('Falha ao registrar conclusão:', e);
      }
    }
  }

  togglePlay() {
    if (this.video.paused) {
      this.video.play();
    } else {
      this.video.pause();
    }
  }

  toggleMute() {
    this.video.muted = !this.video.muted;
    this.volumeSlider.value = this.video.muted ? 0 : this.video.volume;
    this.updateVolumeIcon();
  }

  updateVolumeIcon() {
    if (this.video.muted || this.video.volume === 0) {
      this.muteBtn.innerHTML = '<i class="fa-solid fa-volume-xmark text-sm sm:text-base"></i>';
    } else if (this.video.volume < 0.5) {
      this.muteBtn.innerHTML = '<i class="fa-solid fa-volume-low text-sm sm:text-base"></i>';
    } else {
      this.muteBtn.innerHTML = '<i class="fa-solid fa-volume-high text-sm sm:text-base"></i>';
    }
  }

  toggleFullscreen() {
    if (!document.fullscreenElement) {
      this.container.requestFullscreen?.() || this.container.webkitRequestFullscreen?.();
    } else {
      document.exitFullscreen?.() || document.webkitExitFullscreen?.();
    }
  }

  startSyncTimer() {
    this.stopSyncTimer();
    this.syncTimer = setInterval(() => {
      this.syncProgress();
    }, this.syncIntervalSeconds * 1000);
  }

  stopSyncTimer() {
    if (this.syncTimer) {
      clearInterval(this.syncTimer);
      this.syncTimer = null;
    }
  }

  async syncProgress(isUnload = false) {
    if (!this.videoId || !this.video) return;

    const coverage = this.calculateCoveragePercent();
    const payload = {
      video_id: this.videoId,
      posicao: Math.round(this.video.currentTime),
      tempo_assistido: Math.round(this.effectiveWatchTime),
      percentual: Math.round(coverage * 100) / 100
    };

    if (isUnload && navigator.sendBeacon) {
      const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
      navigator.sendBeacon('/desenvolvimento/api/video-progress', blob);
      return;
    }

    try {
      await apiRequest('/desenvolvimento/api/video-progress', {
        method: 'POST',
        data: payload,
        loading: false,
        silentError: true
      });
    } catch {}
  }

  async syncIframeProgress(seconds, isUnload = false) {
    if (!this.videoId) return;

    const totalDur = this.duration > 0 ? this.duration : 300;
    const coverage = Math.min(100, Math.round((seconds / totalDur) * 100));

    // Se atingiu o limiar de conclusão (90%) enquanto assiste no iframe
    if (!this.isCompleted && coverage >= this.completionThreshold) {
      this.checkCompletion(true);
      return;
    }

    const payload = {
      video_id: this.videoId,
      posicao: Math.round(seconds),
      tempo_assistido: Math.round(seconds),
      percentual: coverage,
      duracao: totalDur
    };

    if (isUnload && navigator.sendBeacon) {
      const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
      navigator.sendBeacon('/desenvolvimento/api/video-progress', blob);
      return;
    }

    try {
      await apiRequest('/desenvolvimento/api/video-progress', {
        method: 'POST',
        data: payload,
        loading: false,
        silentError: true
      });
    } catch {}
  }

  formatTime(seconds) {
    if (!seconds || isNaN(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
  }
}
