// <audio-player src="..." title="..." capa="img/..." hue="320" [compacto]>
// Player em forma de disco de vinil (gira enquanto toca), com onda neon clicável.
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
  #hue = 320;

  static get observedAttributes() {
    return ["src", "title", "hue", "capa"];
  }

  constructor() {
    super();
    this.attachShadow({ mode: "open" });
    this.shadowRoot.innerHTML = `
      <style>
        :host { display: block; --h: 320; }
        .faixa {
          display: flex;
          align-items: center;
          gap: 20px;
          padding: 18px;
          border-radius: 18px;
          background:
            radial-gradient(circle at 0% 50%, hsl(var(--h) 100% 60% / .22), transparent 55%),
            linear-gradient(135deg, rgba(255,255,255,.07), rgba(255,255,255,.02));
          border: 1px solid hsl(var(--h) 100% 70% / .25);
          box-sizing: border-box;
          user-select: none;
          color: #fff;
        }
        /* ---------- disco ---------- */
        .disco {
          position: relative;
          width: 118px; height: 118px;
          flex-shrink: 0;
          border-radius: 50%;
          background: repeating-radial-gradient(circle at center, #0c0c10 0 2px, #1c1b24 2px 3.5px);
          box-shadow: 0 0 0 2px #000, 0 10px 26px rgba(0,0,0,.55), 0 0 26px hsl(var(--h) 100% 60% / .35);
          animation: girar 1.9s linear infinite;
          animation-play-state: paused;
        }
        .disco.girando { animation-play-state: running; }
        .disco::after {
          content: "";
          position: absolute; inset: 0;
          border-radius: 50%;
          background: conic-gradient(from 20deg, transparent 0 30deg, rgba(255,255,255,.16) 50deg, transparent 75deg,
                                     transparent 200deg, rgba(255,255,255,.10) 225deg, transparent 250deg);
          pointer-events: none;
        }
        .rotulo {
          position: absolute;
          inset: 29%;
          border-radius: 50%;
          overflow: hidden;
          background: hsl(var(--h) 100% 60%);
          box-shadow: 0 0 0 3px hsl(var(--h) 100% 60%);
        }
        .rotulo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .furo {
          position: absolute;
          left: 50%; top: 50%;
          width: 9px; height: 9px;
          margin: -4.5px 0 0 -4.5px;
          border-radius: 50%;
          background: #120726;
          box-shadow: 0 0 0 2px rgba(255,255,255,.5);
        }
        @keyframes girar { to { transform: rotate(360deg); } }

        /* ---------- informações ---------- */
        .info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 8px; }
        .titulo {
          font-family: "Righteous", "Rubik", system-ui, sans-serif;
          font-size: 19px;
          line-height: 1.25;
          color: #fff;
          text-shadow: 0 0 12px hsl(var(--h) 100% 60% / .7);
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;
          word-break: break-word;
        }
        canvas { display: block; width: 100%; height: 42px; cursor: pointer; }
        .linha { display: flex; align-items: center; gap: 12px; }
        .play {
          width: 46px; height: 46px;
          border-radius: 50%;
          border: 0;
          cursor: pointer;
          color: #120726;
          background: linear-gradient(135deg, hsl(var(--h) 100% 72%), hsl(calc(var(--h) + 35) 100% 58%));
          box-shadow: 0 0 18px hsl(var(--h) 100% 60% / .65);
          display: grid;
          place-items: center;
          flex-shrink: 0;
          transition: transform .15s ease;
        }
        .play:hover { transform: scale(1.08); }
        .play:active { transform: scale(.94); }
        .play svg { width: 22px; height: 22px; fill: currentColor; }
        .play .icone-pause { display: none; }
        .play.tocando .icone-play { display: none; }
        .play.tocando .icone-pause { display: block; }
        .tempos {
          font-size: 13px;
          font-weight: 600;
          color: rgba(255,255,255,.75);
          font-variant-numeric: tabular-nums;
          letter-spacing: .5px;
        }
        .tempos b { color: hsl(var(--h) 100% 75%); font-weight: 600; }
        .vu { margin-left: auto; display: flex; gap: 3px; align-items: flex-end; height: 18px; opacity: 0; transition: opacity .2s; }
        .vu.ligado { opacity: 1; }
        .vu i { width: 4px; border-radius: 2px; background: hsl(var(--h) 100% 65%); animation: vu .8s ease-in-out infinite; }
        .vu i:nth-child(2) { animation-delay: .15s; }
        .vu i:nth-child(3) { animation-delay: .3s; }
        .vu i:nth-child(4) { animation-delay: .45s; }
        @keyframes vu { 0%,100% { height: 4px; } 50% { height: 18px; } }

        @media (max-width: 480px) {
          .faixa { gap: 14px; padding: 14px; }
          .disco { width: 86px; height: 86px; }
          .titulo { font-size: 16px; }
        }
      </style>
      <div class="faixa">
        <audio preload="metadata"></audio>
        <div class="disco"><div class="rotulo"><img alt=""></div><div class="furo"></div></div>
        <div class="info">
          <div class="titulo"></div>
          <canvas></canvas>
          <div class="linha">
            <button class="play" type="button" aria-label="Tocar">
              <svg class="icone-play" viewBox="0 0 24 24"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/></svg>
              <svg class="icone-pause" viewBox="0 0 24 24"><rect x="6" y="5" width="4.2" height="14" rx="1.2"/><rect x="13.8" y="5" width="4.2" height="14" rx="1.2"/></svg>
            </button>
            <span class="tempos"><b class="atual">0:00</b> / <span class="total">0:00</span></span>
            <span class="vu"><i></i><i></i><i></i><i></i></span>
          </div>
        </div>
      </div>`;

    const r = this.shadowRoot;
    this.audio = r.querySelector("audio");
    this.tituloEl = r.querySelector(".titulo");
    this.botao = r.querySelector(".play");
    this.canvas = r.querySelector("canvas");
    this.atualEl = r.querySelector(".atual");
    this.totalEl = r.querySelector(".total");
    this.discoEl = r.querySelector(".disco");
    this.capaEl = r.querySelector(".rotulo img");
    this.vuEl = r.querySelector(".vu");
    this.capaEl.onerror = () => { this.capaEl.style.visibility = "hidden"; };
    this.#ligarEventos();
  }

  attributeChangedCallback(nome, antigo, novo) {
    if (antigo === novo) return;
    if (nome === "src") {
      this.audio.src = novo || "";
      this.#gerarPicos(novo || "");
      this.#desenhar();
    } else if (nome === "title") {
      this.tituloEl.textContent = novo || "Áudio";
    } else if (nome === "hue") {
      this.#hue = Number(novo) || 320;
      this.style.setProperty("--h", this.#hue);
      this.#desenhar();
    } else if (nome === "capa") {
      this.capaEl.style.visibility = "";
      if (novo) this.capaEl.src = novo; else this.capaEl.removeAttribute("src");
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
    let semente = texto.length || 7;
    for (let i = 0; i < texto.length; i += Math.max(1, Math.floor(texto.length / 200))) {
      semente = (semente * 31 + texto.charCodeAt(i)) % 2147483647;
    }
    const aleatorio = () => (semente = (semente * 16807) % 2147483647) / 2147483647;
    this.#picos = Array.from({ length: 48 }, (_, i) => {
      const onda = 0.55 + 0.45 * Math.sin(i / 2.7);
      return 0.2 + 0.8 * Math.min(1, onda * (0.45 + aleatorio() * 0.75));
    });
  }

  #ajustarCanvas() {
    const escala = window.devicePixelRatio || 1;
    const largura = this.canvas.clientWidth;
    if (!largura) return;
    this.canvas.width = largura * escala;
    this.canvas.height = this.canvas.clientHeight * escala;
    this.#desenhar();
  }

  #desenhar() {
    const c = this.canvas;
    if (!c.width || !this.#picos.length) return;
    const ctx = c.getContext("2d");

    let energia = 0;
    if (this.#tocando && this.#analisador) {
      this.#analisador.getByteFrequencyData(this.#dados);
      let soma = 0;
      for (let i = 0; i < this.#dados.length; i++) soma += this.#dados[i];
      energia = soma / this.#dados.length / 255;
    }

    const n = this.#picos.length;
    const passo = c.width / n;
    const larguraBarra = Math.max(2, passo * 0.5);
    const progresso = this.#duracao ? this.audio.currentTime / this.#duracao : 0;
    const neon = `hsl(${this.#hue} 100% 66%)`;

    ctx.clearRect(0, 0, c.width, c.height);
    for (let i = 0; i < n; i++) {
      let altura = this.#picos[i];
      if (this.#tocando && this.#analisador) {
        const idx = Math.floor((i / n) * this.#dados.length * 0.6);
        altura = Math.min(1, altura * (0.55 + energia * 0.9) + (this.#dados[idx] / 255) * 0.35);
      }
      const h = Math.max(larguraBarra, altura * c.height * 0.86);
      const x = i * passo + (passo - larguraBarra) / 2;
      const y = (c.height - h) / 2;
      const tocado = i / n <= progresso;
      ctx.fillStyle = tocado ? neon : "rgba(255,255,255,.22)";
      ctx.shadowColor = tocado ? neon : "transparent";
      ctx.shadowBlur = tocado ? 8 : 0;
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
    this.discoEl.addEventListener("click", () => this.alternar());

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
      this.discoEl.classList.add("girando");
      this.vuEl.classList.add("ligado");
      this.botao.setAttribute("aria-label", "Pausar");
      this.#animar();
    });
    this.audio.addEventListener("pause", () => {
      this.#tocando = false;
      this.botao.classList.remove("tocando");
      this.discoEl.classList.remove("girando");
      this.vuEl.classList.remove("ligado");
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
