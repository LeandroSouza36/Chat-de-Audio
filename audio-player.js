// <audio-player src="..." title="..." hue="280" [compacto]>
// Player de áudio no formato de post: fundo em degradê, botão de play no centro
// e forma de onda clicável na parte de baixo.
class AudioPlayer extends HTMLElement {
  static #todos = new Set();

  #ctx = null;
  #analisador = null;
  #dados = null;
  #raf = 0;
  #tocando = false;
  #picos = [];
  #ro = null;
  #corrigindoDuracao = false;
  #duracao = 0;

  static get observedAttributes() {
    return ["src", "title", "hue"];
  }

  constructor() {
    super();
    this.attachShadow({ mode: "open" });
    this.shadowRoot.innerHTML = `
      <style>
        :host { display: block; --h: 285; }
        .midia {
          position: relative;
          aspect-ratio: 1 / 1;
          width: 100%;
          background:
            radial-gradient(circle at 20% 15%, rgba(255,255,255,.28), transparent 45%),
            linear-gradient(135deg, hsl(var(--h) 88% 66%), hsl(calc(var(--h) + 45) 78% 46%));
          color: #fff;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          gap: 22px;
          padding: 28px 24px 96px;
          box-sizing: border-box;
          user-select: none;
          overflow: hidden;
          font-family: inherit;
        }
        :host([compacto]) .midia { aspect-ratio: auto; min-height: 230px; border-radius: 12px; padding-bottom: 92px; }
        .titulo {
          font-size: clamp(18px, 4.4vw, 26px);
          font-weight: 700;
          line-height: 1.25;
          text-align: center;
          text-shadow: 0 2px 12px rgba(0,0,0,.18);
          max-width: 90%;
          display: -webkit-box;
          -webkit-line-clamp: 3;
          -webkit-box-orient: vertical;
          overflow: hidden;
          word-break: break-word;
        }
        .titulo[hidden] { display: none; }
        .play {
          width: 84px; height: 84px;
          border-radius: 50%;
          border: 2px solid rgba(255,255,255,.7);
          background: rgba(255,255,255,.2);
          backdrop-filter: blur(6px);
          -webkit-backdrop-filter: blur(6px);
          color: #fff;
          cursor: pointer;
          display: grid;
          place-items: center;
          transition: transform .15s ease, background .15s ease;
          flex-shrink: 0;
        }
        .play:hover { transform: scale(1.06); background: rgba(255,255,255,.3); }
        .play:active { transform: scale(.96); }
        .play svg { width: 34px; height: 34px; fill: currentColor; }
        .play .icone-pause { display: none; }
        .play.tocando .icone-play { display: none; }
        .play.tocando .icone-pause { display: block; }
        .rodape {
          position: absolute;
          left: 20px; right: 20px; bottom: 16px;
        }
        canvas { display: block; width: 100%; height: 48px; cursor: pointer; }
        .tempos {
          display: flex; justify-content: space-between;
          font-size: 12px; font-weight: 600; opacity: .92; margin-top: 6px;
          font-variant-numeric: tabular-nums;
        }
      </style>
      <div class="midia">
        <audio preload="metadata"></audio>
        <div class="titulo" hidden></div>
        <button class="play" type="button" aria-label="Tocar">
          <svg class="icone-play" viewBox="0 0 24 24"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/></svg>
          <svg class="icone-pause" viewBox="0 0 24 24"><rect x="6" y="5" width="4.2" height="14" rx="1.2"/><rect x="13.8" y="5" width="4.2" height="14" rx="1.2"/></svg>
        </button>
        <div class="rodape">
          <canvas></canvas>
          <div class="tempos"><span class="atual">0:00</span><span class="total">0:00</span></div>
        </div>
      </div>`;

    this.audio = this.shadowRoot.querySelector("audio");
    this.tituloEl = this.shadowRoot.querySelector(".titulo");
    this.botao = this.shadowRoot.querySelector(".play");
    this.canvas = this.shadowRoot.querySelector("canvas");
    this.atualEl = this.shadowRoot.querySelector(".atual");
    this.totalEl = this.shadowRoot.querySelector(".total");
    this.#ligarEventos();
  }

  attributeChangedCallback(nome, antigo, novo) {
    if (antigo === novo) return;
    if (nome === "src") {
      this.audio.src = novo || "";
      this.#gerarPicos(novo || "");
      this.#desenhar();
    } else if (nome === "title") {
      this.tituloEl.textContent = novo || "";
      this.tituloEl.hidden = !novo;
    } else if (nome === "hue") {
      this.style.setProperty("--h", Number(novo) || 285);
    }
  }

  connectedCallback() {
    AudioPlayer.#todos.add(this);
    this.#ro = new ResizeObserver(() => this.#ajustarCanvas());
    this.#ro.observe(this.canvas);
    this.#ajustarCanvas();
  }

  disconnectedCallback() {
    AudioPlayer.#todos.delete(this);
    if (this.#ro) this.#ro.disconnect();
    cancelAnimationFrame(this.#raf);
    this.audio.pause();
    if (this.#ctx) this.#ctx.close();
  }

  pause() { this.audio.pause(); }

  // Forma de onda "fixa" (sempre igual para o mesmo áudio)
  #gerarPicos(texto) {
    let semente = texto.length;
    for (let i = 0; i < texto.length; i += Math.max(1, Math.floor(texto.length / 200))) {
      semente = (semente * 31 + texto.charCodeAt(i)) % 2147483647;
    }
    const aleatorio = () => (semente = (semente * 16807) % 2147483647) / 2147483647;
    this.#picos = Array.from({ length: 64 }, (_, i) => {
      const onda = 0.55 + 0.45 * Math.sin(i / 3.2);
      return 0.18 + 0.82 * Math.min(1, onda * (0.45 + aleatorio() * 0.75));
    });
  }

  #ajustarCanvas() {
    const escala = window.devicePixelRatio || 1;
    const largura = this.canvas.clientWidth;
    const altura = this.canvas.clientHeight;
    if (!largura) return;
    this.canvas.width = largura * escala;
    this.canvas.height = altura * escala;
    this.#desenhar();
  }

  #desenhar() {
    const c = this.canvas;
    const ctx = c.getContext("2d");
    if (!c.width || !this.#picos.length) return;

    let energia = 0;
    if (this.#tocando && this.#analisador) {
      this.#analisador.getByteFrequencyData(this.#dados);
      let soma = 0;
      for (let i = 0; i < this.#dados.length; i++) soma += this.#dados[i];
      energia = soma / this.#dados.length / 255;
    }

    const n = this.#picos.length;
    const passo = c.width / n;
    const larguraBarra = Math.max(2, passo * 0.55);
    const progresso = this.#duracao ? this.audio.currentTime / this.#duracao : 0;

    ctx.clearRect(0, 0, c.width, c.height);
    for (let i = 0; i < n; i++) {
      let altura = this.#picos[i];
      if (this.#tocando && this.#analisador) {
        const idx = Math.floor((i / n) * this.#dados.length * 0.6);
        altura = Math.min(1, altura * (0.55 + energia * 0.9) + (this.#dados[idx] / 255) * 0.35);
      }
      const h = Math.max(larguraBarra, altura * c.height);
      const x = i * passo + (passo - larguraBarra) / 2;
      const y = (c.height - h) / 2;
      ctx.fillStyle = i / n <= progresso ? "rgba(255,255,255,1)" : "rgba(255,255,255,.45)";
      ctx.beginPath();
      if (ctx.roundRect) ctx.roundRect(x, y, larguraBarra, h, larguraBarra / 2);
      else ctx.rect(x, y, larguraBarra, h);
      ctx.fill();
    }
  }

  #animar() {
    this.#desenhar();
    if (this.#tocando) this.#raf = requestAnimationFrame(() => this.#animar());
  }

  #iniciarAnalise() {
    if (this.#ctx) return;
    try {
      this.#ctx = new (window.AudioContext || window.webkitAudioContext)();
      const fonte = this.#ctx.createMediaElementSource(this.audio);
      this.#analisador = this.#ctx.createAnalyser();
      this.#analisador.fftSize = 256;
      this.#dados = new Uint8Array(this.#analisador.frequencyBinCount);
      fonte.connect(this.#analisador);
      this.#analisador.connect(this.#ctx.destination);
    } catch (e) {
      this.#analisador = null;
    }
  }

  async alternar() {
    this.#iniciarAnalise();
    if (this.#ctx && this.#ctx.state === "suspended") await this.#ctx.resume();
    if (this.audio.paused) {
      AudioPlayer.#todos.forEach((p) => p !== this && p.pause());
      this.audio.play();
    } else {
      this.audio.pause();
    }
  }

  #formatar(s) {
    if (!isFinite(s) || s < 0) return "0:00";
    const m = Math.floor(s / 60);
    return `${m}:${String(Math.floor(s % 60)).padStart(2, "0")}`;
  }

  #atualizarDuracao() {
    const d = this.audio.duration;
    if (isFinite(d) && d > 0) {
      this.#duracao = d;
      this.totalEl.textContent = this.#formatar(d);
    }
  }

  #ligarEventos() {
    this.botao.addEventListener("click", () => this.alternar());

    this.canvas.addEventListener("click", (ev) => {
      if (!this.#duracao) return;
      const r = this.canvas.getBoundingClientRect();
      this.audio.currentTime = ((ev.clientX - r.left) / r.width) * this.#duracao;
      this.#desenhar();
    });

    this.audio.addEventListener("loadedmetadata", () => {
      // Áudios gravados no navegador às vezes vêm sem duração; isso força o cálculo
      if (!isFinite(this.audio.duration)) {
        this.#corrigindoDuracao = true;
        this.audio.currentTime = 1e101;
      } else {
        this.#atualizarDuracao();
      }
    });
    this.audio.addEventListener("durationchange", () => this.#atualizarDuracao());

    this.audio.addEventListener("timeupdate", () => {
      if (this.#corrigindoDuracao) {
        this.#corrigindoDuracao = false;
        this.#atualizarDuracao();
        this.audio.currentTime = 0;
        return;
      }
      this.atualEl.textContent = this.#formatar(this.audio.currentTime);
      if (!this.#tocando) this.#desenhar();
    });

    this.audio.addEventListener("play", () => {
      this.#tocando = true;
      this.botao.classList.add("tocando");
      this.botao.setAttribute("aria-label", "Pausar");
      this.#animar();
    });
    this.audio.addEventListener("pause", () => {
      this.#tocando = false;
      this.botao.classList.remove("tocando");
      this.botao.setAttribute("aria-label", "Tocar");
      this.#desenhar();
    });
    this.audio.addEventListener("ended", () => {
      this.audio.currentTime = 0;
      this.atualEl.textContent = "0:00";
      this.#desenhar();
    });
  }
}

customElements.define("audio-player", AudioPlayer);
