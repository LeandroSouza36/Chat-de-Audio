class AudioPlayer extends HTMLElement {
  #playing = false;
  #initialized = false;
  #audioCtx;
  #analyser;
  #dataArray;
  #animationId;

  constructor() {
    super();
    this.attachShadow({ mode: "open" });

    // Configurações padrão
    this.barWidth = 3;
    this.barGap = 1;
    this.bufferPercentage = 75;
    this.volume = 0.4;

    this.render();
  }

  static get observedAttributes() {
    return [
      "src",
      "title",
      "bar-width",
      "bar-gap",
      "buffer-percentage",
      "muted",
    ];
  }

  attributeChangedCallback(name, oldValue, newValue) {
    if (oldValue === newValue) return;

    switch (name) {
      case "src":
        this.#initialized = false;
        if (this.audio) this.audio.src = newValue;
        break;
      case "title":
        if (this.titleElement) this.titleElement.textContent = newValue;
        break;
      case "bar-width":
        this.barWidth = Number(newValue) || 3;
        break;
      case "bar-gap":
        this.barGap = Number(newValue) || 1;
        break;
      case "buffer-percentage":
        this.bufferPercentage = Number(newValue) || 75;
        break;
    }
  }

  connectedCallback() {
    this.attachEvents();
  }

  disconnectedCallback() {
    // Limpeza para evitar vazamento de memória
    cancelAnimationFrame(this.#animationId);
    if (this.#audioCtx) this.#audioCtx.close();
  }

  async #initializeAudio() {
    if (this.#initialized) return;

    try {
      this.#audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      this.gainNode = this.#audioCtx.createGain();
      this.#analyser = this.#audioCtx.createAnalyser();

      const source = this.#audioCtx.createMediaElementSource(this.audio);

      this.#analyser.fftSize = 2048;
      const bufferLength = this.#analyser.frequencyBinCount;
      this.#dataArray = new Uint8Array(bufferLength);

      source.connect(this.gainNode);
      this.gainNode.connect(this.#analyser);
      this.#analyser.connect(this.#audioCtx.destination);

      this.#initialized = true;
    } catch (e) {
      console.error("Falha ao inicializar Web Audio API:", e);
    }
  }

  #drawVisualizer() {
    if (!this.#playing) return;

    this.#animationId = requestAnimationFrame(() => this.#drawVisualizer());
    this.#analyser.getByteFrequencyData(this.#dataArray);

    const canvas = this.canvas;
    const ctx = this.canvasCtx;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const barTotalWidth = this.barWidth + this.barGap;
    const barCount = Math.floor(canvas.width / barTotalWidth);
    const bufferSize = (this.#dataArray.length * this.bufferPercentage) / 100;

    for (let i = 0; i < barCount; i++) {
      const index = Math.floor((i / barCount) * bufferSize);
      const value = this.#dataArray[index];
      const percent = value / 255;
      const height = percent * canvas.height;
      const y = canvas.height - height;

      // Gradiente dinâmico baseado na frequência
      ctx.fillStyle = `rgb(${value + 100}, 150, 255)`;
      ctx.fillRect(i * barTotalWidth, y, this.barWidth, height);
    }
  }

  async togglePlay() {
    await this.#initializeAudio();

    if (this.#audioCtx.state === "suspended") {
      await this.#audioCtx.resume();
    }

    if (this.#playing) {
      this.audio.pause();
    } else {
      this.audio.play();
    }
  }

  attachEvents() {
    this.playPauseBtn.onclick = () => this.togglePlay();

    this.volumeBar.oninput = (e) => {
      const val = e.target.value;
      if (this.gainNode) this.gainNode.gain.value = val;
      this.updateVolumeIcon(val);
    };

    this.progressBar.oninput = (e) => {
      this.audio.currentTime = e.target.value;
    };

    this.audio.onplay = () => {
      this.#playing = true;
      this.playPauseBtn.classList.add("playing");
      this.#drawVisualizer();
    };

    this.audio.onpause = () => {
      this.#playing = false;
      this.playPauseBtn.classList.remove("playing");
    };

    this.audio.onloadedmetadata = () => {
      this.progressBar.max = this.audio.duration;
      this.durationEl.textContent = this.formatTime(this.audio.duration);
    };

    this.audio.ontimeupdate = () => {
      this.progressBar.value = this.audio.currentTime;
      this.currentTimeEl.textContent = this.formatTime(this.audio.currentTime);
    };
  }

  updateVolumeIcon(val) {
    const parent = this.volumeBar.parentElement;
    parent.className =
      "volume-bar " + (val > 1 ? "over" : val > 0 ? "half" : "muted");
  }

  formatTime(seconds) {
    const min = Math.floor(seconds / 60);
    const sec = Math.floor(seconds % 60);
    return `${min}:${sec.toString().padStart(2, "0")}`;
  }

  render() {
    const scale = window.devicePixelRatio || 1;
    this.shadowRoot.innerHTML = `
        <style>
            :host {
                display: block;
                width: 100%;
                max-width: 450px;
                --accent-color: #9600FF;
            }
            .audio-player {
                background: #111;
                border-radius: 12px;
                padding: 15px;
                display: flex;
                align-items: center;
                gap: 12px;
                position: relative;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            }
            .audio-name {
                position: absolute;
                bottom: -35px;
                left: 0;
                color: #333;
                font-size: 14px;
                font-weight: bold;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                width: 100%;
            }
            .play-btn {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                border: none;
                background: var(--accent-color);
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .play-btn.playing { background: #555; }
            
            .controls {
                flex: 1;
                display: flex;
                flex-direction: column;
                position: relative;
            }
            .time-info {
                display: flex;
                justify-content: space-between;
                font-size: 10px;
                color: #aaa;
                margin-top: 5px;
            }
            canvas {
                width: 100%;
                height: 30px;
                pointer-events: none;
                opacity: 0.6;
            }
            input[type="range"] {
                cursor: pointer;
                accent-color: var(--accent-color);
            }
            .volume-bar { width: 40px; }
        </style>
        <div class="audio-player">
            <audio></audio>
            <button class="play-btn">▶</button>
            <div class="controls">
                <canvas></canvas>
                <input type="range" class="progress-bar" value="0" step="0.1">
                <div class="time-info">
                    <span class="current-time">0:00</span>
                    <span class="duration">0:00</span>
                </div>
            </div>
            <div class="volume-bar">
                <input type="range" min="0" max="1.5" step="0.05" value="${this.volume}" style="width: 50px">
            </div>
            <div class="audio-name">${this.getAttribute("title") || "Sem título"}</div>
        </div>
        `;

    this.audio = this.shadowRoot.querySelector("audio");
    this.canvas = this.shadowRoot.querySelector("canvas");
    this.canvasCtx = this.canvas.getContext("2d");
    this.playPauseBtn = this.shadowRoot.querySelector(".play-btn");
    this.progressBar = this.shadowRoot.querySelector(".progress-bar");
    this.volumeBar = this.shadowRoot.querySelector(
      "input[type='range']:last-of-type",
    );
    this.currentTimeEl = this.shadowRoot.querySelector(".current-time");
    this.durationEl = this.shadowRoot.querySelector(".duration");
    this.titleElement = this.shadowRoot.querySelector(".audio-name");

    // Ajuste de DPI do Canvas
    this.canvas.width = this.canvas.offsetWidth * scale;
    this.canvas.height = this.canvas.offsetHeight * scale;
    this.canvasCtx.scale(scale, scale);

    if (this.hasAttribute("src")) this.audio.src = this.getAttribute("src");
  }
}

customElements.define("audio-player", AudioPlayer);
