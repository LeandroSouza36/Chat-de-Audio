// Interações das páginas: estrelas, compartilhar, janela de post e gravação de áudio.
(function () {
  "use strict";

  // ---------- Aviso rápido no rodapé ----------
  let timerAviso;
  function aviso(texto) {
    const el = document.getElementById("aviso");
    if (!el) return;
    el.textContent = texto;
    el.hidden = false;
    clearTimeout(timerAviso);
    timerAviso = setTimeout(() => (el.hidden = true), 2600);
  }
  window.aviso = aviso;

  // ---------- Estrelas (curtir / descurtir) ----------
  async function darEstrela(id, apenasCurtir) {
    const botoes = document.querySelectorAll(`[data-estrela="${id}"]`);
    const jaCurtido = botoes.length && botoes[0].classList.contains("ativo");
    if (apenasCurtir && jaCurtido) return;

    const corpo = new FormData();
    corpo.append("estrela", id);
    try {
      const resp = await fetch("estrelaEnvio.php", {
        method: "POST",
        body: corpo,
        headers: { "X-Requested-With": "fetch" },
      });
      const r = await resp.json();
      if (!r.ok) throw new Error();
      // atualiza a página e também as cópias guardadas nos <template> (grade do perfil/explorar)
      const raizes = [document, ...Array.from(document.querySelectorAll("template"), (t) => t.content)];
      raizes.forEach((raiz) => {
        raiz.querySelectorAll(`[data-estrela="${id}"]`).forEach((b) => {
          b.classList.toggle("ativo", r.curtiu);
          b.querySelector("i").className = "bi " + (r.curtiu ? "bi-star-fill" : "bi-star");
          if (r.curtiu && raiz === document) {
            b.classList.remove("pulo");
            void b.offsetWidth;
            b.classList.add("pulo");
          }
        });
        raiz.querySelectorAll(`[data-contagem="${id}"]`).forEach((el) => (el.textContent = r.total));
        raiz.querySelectorAll(`[data-palavra="${id}"]`).forEach((el) => (el.textContent = r.total === 1 ? "estrela" : "estrelas"));
      });
    } catch (e) {
      aviso("Não foi possível dar a estrela. Tente de novo.");
    }
  }

  // ---------- Ecoar (repetir o áudio de alguém no próprio perfil) ----------
  async function ecoar(id) {
    const corpo = new FormData();
    corpo.append("eco", id);
    try {
      const resp = await fetch("ecoar.php", {
        method: "POST",
        body: corpo,
        headers: { "X-Requested-With": "fetch" },
      });
      const r = await resp.json();
      if (!r.ok) {
        aviso(r.erro || "Não foi possível ecoar. Tente de novo.");
        return;
      }
      const raizes = [document, ...Array.from(document.querySelectorAll("template"), (t) => t.content)];
      raizes.forEach((raiz) => {
        raiz.querySelectorAll(`[data-ecoar="${id}"]`).forEach((b) => {
          b.classList.toggle("ativo", r.ecoou);
          b.title = r.ecoou ? "Desfazer eco" : "Ecoar no seu perfil";
          if (r.ecoou && raiz === document) {
            b.classList.remove("pulo");
            void b.offsetWidth;
            b.classList.add("pulo");
          }
        });
        raiz.querySelectorAll(`[data-eco-texto="${id}"]`).forEach((el) => (el.textContent = r.ecoou ? "Ecoado" : "Ecoar"));
        raiz.querySelectorAll(`[data-ecos="${id}"]`).forEach((el) => (el.textContent = r.total));
      });
      aviso(r.ecoou ? "Áudio ecoado! Ele agora aparece no seu perfil." : "Eco desfeito.");
    } catch (e) {
      aviso("Não foi possível ecoar. Tente de novo.");
    }
  }

  document.addEventListener("click", (ev) => {
    const btnEstrela = ev.target.closest("[data-estrela]");
    if (btnEstrela) {
      darEstrela(btnEstrela.dataset.estrela, false);
      return;
    }

    const btnEco = ev.target.closest("[data-ecoar]");
    if (btnEco) {
      ecoar(btnEco.dataset.ecoar);
      return;
    }

    const tile = ev.target.closest("[data-abrir-post]");
    if (tile) {
      abrirPost(tile.dataset.abrirPost);
      return;
    }

    if (ev.target.closest("[data-fechar]")) {
      fecharPost();
    }
  });

  // Duplo clique no áudio = dar estrela (como nas redes sociais)
  document.addEventListener("dblclick", (ev) => {
    const midia = ev.target.closest(".post-midia");
    if (!midia) return;
    // ignora duplo clique no botão de play ou na onda
    const caminho = ev.composedPath();
    if (caminho.some((el) => el.tagName === "BUTTON" || el.tagName === "CANVAS" || (el.classList && el.classList.contains("disco")))) return;
    const post = midia.closest(".post");
    const explosao = midia.querySelector(".estrela-explosao");
    if (explosao) {
      explosao.classList.remove("mostrar");
      void explosao.offsetWidth;
      explosao.classList.add("mostrar");
    }
    darEstrela(post.dataset.id, true);
  });

  // ---------- Janela de post (Perfil / Explorar) ----------
  const modal = document.getElementById("modalPost");
  const modalConteudo = document.getElementById("modalPostConteudo");

  function abrirPost(id) {
    const tpl = document.getElementById("tpl-post-" + id);
    if (!tpl || !modal) return;
    modalConteudo.innerHTML = "";
    modalConteudo.appendChild(tpl.content.cloneNode(true));
    modal.hidden = false;
    document.body.classList.add("sem-rolagem");
  }

  function fecharPost() {
    if (!modal) return;
    modal.hidden = true;
    modalConteudo.innerHTML = "";
    document.body.classList.remove("sem-rolagem");
  }

  document.addEventListener("keydown", (ev) => {
    if (ev.key === "Escape" && modal && !modal.hidden) fecharPost();
  });

  // Abre direto um post se o link tiver #post-ID e ele estiver numa grade
  if (location.hash.startsWith("#post-")) {
    const id = location.hash.slice(6);
    if (!document.getElementById("post-" + id) && document.getElementById("tpl-post-" + id)) {
      abrirPost(id);
    }
  }

  // ---------- Gravação de áudio (página inicial) ----------
  const composer = document.getElementById("gravar");

  document.querySelectorAll("[data-gravar]").forEach((link) => {
    link.addEventListener("click", (ev) => {
      if (!composer) return; // em outras páginas, segue o link para painel.php#gravar
      ev.preventDefault();
      composer.scrollIntoView({ behavior: "smooth", block: "center" });
      iniciarGravacao();
    });
  });

  if (!composer) return;

  const estados = {
    inicial: composer.querySelector('[data-estado="inicial"]'),
    gravando: composer.querySelector('[data-estado="gravando"]'),
    previa: composer.querySelector('[data-estado="previa"]'),
  };
  const tempoGravacao = composer.querySelector("[data-tempo]");
  const areaPrevia = composer.querySelector("[data-previa]");
  const campoLegenda = composer.querySelector("[data-legenda]");
  const formEnvio = document.getElementById("formEnvioAudio");

  let gravador = null;
  let fluxo = null;
  let pedacos = [];
  let inicio = 0;
  let timerGravacao = 0;
  let audioGravado = "";
  let modoStory = false; // true quando a gravação começou pelo botão de story
  const btnPublicar = composer.querySelector("[data-publicar]");
  const btnPublicarStory = composer.querySelector("[data-publicar-story]");

  function mostrar(estado) {
    Object.entries(estados).forEach(([nome, el]) => el && (el.hidden = nome !== estado));
  }

  function bip() {
    try {
      const ctx = new AudioContext();
      const osc = ctx.createOscillator();
      const ganho = ctx.createGain();
      osc.connect(ganho);
      ganho.connect(ctx.destination);
      osc.frequency.value = 880;
      ganho.gain.setValueAtTime(0.15, ctx.currentTime);
      ganho.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.25);
      osc.start();
      osc.stop(ctx.currentTime + 0.3);
    } catch (e) {}
  }

  async function iniciarGravacao() {
    if (gravador && gravador.state === "recording") return;
    try {
      fluxo = await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch (e) {
      aviso("Permita o uso do microfone para gravar.");
      return;
    }
    pedacos = [];
    gravador = new MediaRecorder(fluxo);
    gravador.ondataavailable = (e) => e.data.size && pedacos.push(e.data);
    gravador.onstop = () => {
      fluxo.getTracks().forEach((t) => t.stop());
      const blob = new Blob(pedacos, { type: gravador.mimeType || "audio/webm" });
      const leitor = new FileReader();
      leitor.onloadend = () => {
        audioGravado = leitor.result;
        areaPrevia.innerHTML = "";
        const player = document.createElement("audio-player");
        player.setAttribute("compacto", "");
        const fotoTopo = document.querySelector(".topo-perfil img");
        if (fotoTopo) player.setAttribute("capa", fotoTopo.getAttribute("src"));
        player.setAttribute("hue", String(250 + Math.floor(Math.random() * 90)));
        player.setAttribute("src", audioGravado);
        areaPrevia.appendChild(player);
        // destaca o botão certo: story (se veio da barra de stories) ou feed
        btnPublicarStory.className = "botao " + (modoStory ? "botao-neon" : "botao-contorno");
        btnPublicar.className = "botao " + (modoStory ? "botao-contorno" : "botao-neon");
        mostrar("previa");
        if (!modoStory) campoLegenda.focus();
      };
      leitor.readAsDataURL(blob);
    };
    gravador.start();
    bip();
    inicio = Date.now();
    tempoGravacao.textContent = "0:00";
    timerGravacao = setInterval(() => {
      const s = Math.floor((Date.now() - inicio) / 1000);
      tempoGravacao.textContent = `${Math.floor(s / 60)}:${String(s % 60).padStart(2, "0")}`;
    }, 250);
    mostrar("gravando");
  }

  function pararGravacao() {
    clearInterval(timerGravacao);
    if (gravador && gravador.state === "recording") gravador.stop();
  }

  function descartar() {
    audioGravado = "";
    modoStory = false;
    areaPrevia.innerHTML = "";
    campoLegenda.value = "";
    mostrar("inicial");
  }

  // modo: "feed" (post normal) ou "story" (some em 24h)
  function publicar(modo) {
    if (!audioGravado) return;
    const botao = modo === "story" ? btnPublicarStory : btnPublicar;
    formEnvio.action = modo === "story" ? "audioEnvioStory.php" : "audioEnvio.php";
    formEnvio.querySelector('[name="audio"]').value = audioGravado;
    formEnvio.querySelector('[name="textoAssunto"]').value = campoLegenda.value.trim();
    btnPublicar.disabled = true;
    btnPublicarStory.disabled = true;
    botao.textContent = "Publicando...";
    formEnvio.submit();
  }

  composer.querySelectorAll("[data-iniciar]").forEach((b) => b.addEventListener("click", iniciarGravacao));
  composer.querySelector("[data-parar]").addEventListener("click", pararGravacao);
  composer.querySelector("[data-descartar]").addEventListener("click", descartar);
  btnPublicar.addEventListener("click", () => publicar("feed"));
  btnPublicarStory.addEventListener("click", () => publicar("story"));
  campoLegenda.addEventListener("keydown", (ev) => {
    if (ev.key === "Enter") {
      ev.preventDefault();
      publicar(modoStory ? "story" : "feed");
    }
  });

  // Botões de novo story na barra de stories
  document.querySelectorAll("[data-gravar-story]").forEach((b) =>
    b.addEventListener("click", () => {
      modoStory = true;
      composer.scrollIntoView({ behavior: "smooth", block: "center" });
      iniciarGravacao();
    })
  );
  composer.querySelectorAll("[data-iniciar]").forEach((b) => b.addEventListener("click", () => (modoStory = false), true));

  // Aviso depois de publicar um story
  if (new URLSearchParams(location.search).get("story") === "ok") {
    history.replaceState(null, "", location.pathname);
    aviso("Story publicado! Ele some em 24 horas.");
  }

  // Veio de outra página pelo botão "Criar"
  if (location.hash === "#gravar") {
    history.replaceState(null, "", location.pathname);
    setTimeout(() => {
      composer.scrollIntoView({ block: "center" });
      iniciarGravacao();
    }, 300);
  }
})();

// ==========================================================
// STORIES: player em tela cheia
// ==========================================================
(function () {
  "use strict";
  const viewer = document.getElementById("storyViewer");
  if (!viewer) return;

  const $ = (sel) => viewer.querySelector(sel);
  const audio = $("[data-sv-audio]");
  const barras = $("[data-sv-barras]");
  const disco = $("[data-sv-disco]");
  const btnPlay = $("[data-sv-play]");
  const btnExcluir = $("[data-sv-excluir]");
  const caixa = $("[data-sv-caixa]");

  let fila = [];          // usuários na ordem da barra
  let usuarioAtual = "";
  let dados = null;       // resposta do storyDados.php
  let idx = 0;
  let corrigindo = false;

  // ---------- "visto" guardado no navegador ----------
  function lerVistos() {
    try { return JSON.parse(localStorage.getItem("storiesVistos") || "{}"); } catch (e) { return {}; }
  }
  function marcarVisto(usuario, id) {
    try {
      const v = lerVistos();
      if (!v[usuario] || v[usuario] < id) v[usuario] = id;
      localStorage.setItem("storiesVistos", JSON.stringify(v));
    } catch (e) {}
  }
  function atualizarAneis() {
    const v = lerVistos();
    document.querySelectorAll("[data-story-user]").forEach((b) => {
      const visto = (v[b.dataset.storyUser] || 0) >= Number(b.dataset.ultimoId || 0);
      b.classList.toggle("tem-story", true);
      b.classList.toggle("story-visto", visto);
    });
  }
  atualizarAneis();

  function montarFila() {
    fila = [];
    document.querySelectorAll("[data-story-user]").forEach((b) => {
      if (!fila.includes(b.dataset.storyUser)) fila.push(b.dataset.storyUser);
    });
  }

  // ---------- abrir / fechar ----------
  async function abrir(usuario) {
    montarFila();
    let r;
    try {
      const resp = await fetch("storyDados.php?user=" + encodeURIComponent(usuario));
      r = await resp.json();
    } catch (e) {
      window.aviso && window.aviso("Não foi possível abrir o story.");
      return;
    }
    if (!r.stories || r.stories.length === 0) {
      window.aviso && window.aviso("Esse story já expirou.");
      return;
    }
    usuarioAtual = usuario;
    dados = r;
    // começa no primeiro story ainda não visto
    const vistoAte = lerVistos()[usuario] || 0;
    idx = Math.max(0, r.stories.findIndex((s) => s.id > vistoAte));
    if (idx === -1 || r.stories.every((s) => s.id <= vistoAte)) idx = 0;

    $("[data-sv-avatar]").src = r.avatar;
    $("[data-sv-capa]").src = r.avatar;
    $("[data-sv-capa]").style.visibility = "";
    const nome = $("[data-sv-nome]");
    nome.textContent = r.usuario;
    nome.href = "perfil.php?user=" + encodeURIComponent(r.usuario);
    btnExcluir.hidden = !r.dono;
    caixa.style.setProperty("--h", corDoUsuario(r.usuario));

    barras.innerHTML = r.stories.map(() => '<span class="story-barra"><i></i></span>').join("");
    viewer.hidden = false;
    document.body.classList.add("sem-rolagem");
    tocar();
  }

  function fechar() {
    audio.pause();
    audio.removeAttribute("src");
    viewer.hidden = true;
    document.body.classList.remove("sem-rolagem");
    disco.classList.remove("girando");
    atualizarAneis();
  }

  function corDoUsuario(nome) {
    const cores = [320, 275, 190, 25, 255, 300, 210, 340];
    let soma = 0;
    for (const c of nome) soma += c.charCodeAt(0);
    return cores[soma % cores.length];
  }

  // ---------- tocar ----------
  function tocar() {
    const s = dados.stories[idx];
    $("[data-sv-tempo]").textContent = s.tempo;
    $("[data-sv-expira]").textContent = s.expira;
    barras.querySelectorAll(".story-barra i").forEach((el, i) => {
      el.style.width = i < idx ? "100%" : "0%";
    });
    corrigindo = false;
    audio.src = s.audio;
    audio.play().catch(() => {});
    marcarVisto(usuarioAtual, s.id);
  }

  function proximo() {
    if (idx + 1 < dados.stories.length) {
      idx++;
      tocar();
      return;
    }
    const pos = fila.indexOf(usuarioAtual);
    if (pos > -1 && pos + 1 < fila.length) abrir(fila[pos + 1]);
    else fechar();
  }

  function anterior() {
    if (audio.currentTime > 2 || idx === 0) {
      audio.currentTime = 0;
      audio.play().catch(() => {});
      return;
    }
    idx--;
    tocar();
  }

  // ---------- eventos do áudio ----------
  audio.addEventListener("loadedmetadata", () => {
    // áudios gravados no navegador às vezes vêm sem duração
    if (!isFinite(audio.duration)) {
      corrigindo = true;
      audio.currentTime = 1e101;
    }
  });
  audio.addEventListener("timeupdate", () => {
    if (corrigindo) {
      corrigindo = false;
      audio.currentTime = 0;
      return;
    }
    const barra = barras.querySelectorAll(".story-barra i")[idx];
    if (barra && isFinite(audio.duration) && audio.duration > 0) {
      barra.style.width = Math.min(100, (audio.currentTime / audio.duration) * 100) + "%";
    }
  });
  audio.addEventListener("play", () => {
    disco.classList.add("girando");
    btnPlay.innerHTML = '<i class="bi bi-pause-fill"></i>';
  });
  audio.addEventListener("pause", () => {
    disco.classList.remove("girando");
    btnPlay.innerHTML = '<i class="bi bi-play-fill"></i>';
  });
  audio.addEventListener("ended", () => {
    const barra = barras.querySelectorAll(".story-barra i")[idx];
    if (barra) barra.style.width = "100%";
    setTimeout(proximo, 350);
  });

  // ---------- botões ----------
  btnPlay.addEventListener("click", () => (audio.paused ? audio.play() : audio.pause()));
  $("[data-sv-proximo]").addEventListener("click", proximo);
  $("[data-sv-anterior]").addEventListener("click", anterior);
  viewer.querySelectorAll("[data-sv-fechar]").forEach((b) => b.addEventListener("click", fechar));

  btnExcluir.addEventListener("click", async () => {
    const s = dados.stories[idx];
    if (!confirm("Excluir este story?")) return;
    audio.pause();
    const corpo = new FormData();
    corpo.append("id", s.id);
    try {
      await fetch("storyExcluir.php", { method: "POST", body: corpo });
    } catch (e) {}
    dados.stories.splice(idx, 1);
    if (dados.stories.length === 0) {
      fechar();
      location.reload();
      return;
    }
    if (idx >= dados.stories.length) idx = dados.stories.length - 1;
    barras.innerHTML = dados.stories.map(() => '<span class="story-barra"><i></i></span>').join("");
    tocar();
  });

  document.addEventListener("keydown", (ev) => {
    if (viewer.hidden) return;
    if (ev.key === "Escape") fechar();
    if (ev.key === "ArrowRight") proximo();
    if (ev.key === "ArrowLeft") anterior();
    if (ev.key === " ") {
      ev.preventDefault();
      audio.paused ? audio.play() : audio.pause();
    }
  });

  document.querySelectorAll("[data-story-user]").forEach((b) =>
    b.addEventListener("click", () => abrir(b.dataset.storyUser))
  );
})();

// ==========================================================
// COMENTÁRIOS EM ÁUDIO
// ==========================================================
(function () {
  "use strict";
  const LIMITE_SEGUNDOS = 60; // comentários de até 1 minuto
  const aviso = (t) => window.aviso && window.aviso(t);
  const meuAvatar = () => {
    const img = document.querySelector(".topo-perfil img");
    return img ? img.getAttribute("src") : "img/usuario/padrao.svg";
  };
  const esc = (t) => String(t).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const corDe = (nome) => {
    const cores = [320, 275, 190, 25, 255, 300, 210, 340];
    let soma = 0;
    for (const c of nome) soma += c.charCodeAt(0);
    return cores[soma % cores.length];
  };

  // gravação em andamento (só uma por vez)
  let gravacao = null;

  function atualizarContagem(audioId, total) {
    const raizes = [document, ...Array.from(document.querySelectorAll("template"), (t) => t.content)];
    raizes.forEach((r) => r.querySelectorAll(`[data-comentarios="${audioId}"]`).forEach((el) => (el.textContent = total)));
  }

  function htmlComentario(k) {
    return `
      <div class="comentario" data-comentario-id="${k.id}">
        <a href="perfil.php?user=${encodeURIComponent(k.usuario)}">
          <img class="avatar avatar-32" src="${esc(k.avatar)}" alt="" onerror="this.onerror=null;this.src='img/usuario/padrao.svg'">
        </a>
        <div class="comentario-corpo">
          <div class="comentario-topo">
            <a class="nome-usuario" href="perfil.php?user=${encodeURIComponent(k.usuario)}">${esc(k.usuario)}</a>
            <span class="texto-2">· ${esc(k.tempo)}</span>
            ${k.podeExcluir ? `<button type="button" class="botao-icone comentario-excluir" data-excluir-comentario="${k.id}" title="Excluir comentário"><i class="bi bi-trash3"></i></button>` : ""}
          </div>
          <audio-player mini src="${esc(k.audio)}" capa="${esc(k.avatar)}" hue="${corDe(k.usuario)}"></audio-player>
        </div>
      </div>`;
  }

  function htmlGravador() {
    return `
      <div class="comentario-gravar" data-cg-estado="inicial">
        <img class="avatar avatar-32" src="${esc(meuAvatar())}" alt="" onerror="this.onerror=null;this.src='img/usuario/padrao.svg'">
        <button type="button" class="comentario-campo" data-cg-iniciar><i class="bi bi-mic-fill"></i> Responder com um áudio...</button>
      </div>
      <div class="comentario-gravar" data-cg-estado="gravando" hidden>
        <span class="ponto-gravando"></span>
        <span class="comentario-tempo" data-cg-tempo>0:00</span>
        <span class="texto-2">de 1:00</span>
        <button type="button" class="botao-parar pequeno" data-cg-parar title="Parar"><i class="bi bi-stop-fill"></i></button>
      </div>
      <div class="comentario-previa" data-cg-estado="previa" hidden>
        <div data-cg-player></div>
        <div class="comentario-acoes">
          <button type="button" class="botao botao-link-cinza botao-pequeno" data-cg-descartar>Descartar</button>
          <button type="button" class="botao botao-neon botao-pequeno" data-cg-enviar><i class="bi bi-send-fill"></i> Enviar</button>
        </div>
      </div>`;
  }

  async function abrirPainel(post, audioId) {
    const painel = post.querySelector(".comentarios");
    if (!painel) return;
    if (!painel.hidden) {
      painel.hidden = true;
      post.querySelector("[data-comentar]").classList.remove("ativo");
      return;
    }
    painel.hidden = false;
    post.querySelector("[data-comentar]").classList.add("ativo");
    if (painel.dataset.carregado) return;

    painel.innerHTML = '<p class="comentarios-carregando texto-2">Carregando comentários...</p>';
    try {
      const r = await (await fetch("comentariosDados.php?id=" + encodeURIComponent(audioId))).json();
      const lista = r.comentarios || [];
      painel.innerHTML =
        `<div class="comentarios-lista">${lista.length ? lista.map(htmlComentario).join("") : '<p class="comentarios-vazio texto-2">Nenhum comentário ainda. Seja o primeiro a responder!</p>'}</div>` +
        htmlGravador();
      painel.dataset.carregado = "1";
      painel.dataset.audioId = audioId;
    } catch (e) {
      painel.innerHTML = '<p class="comentarios-vazio texto-2">Não foi possível carregar os comentários.</p>';
    }
  }

  function mostrarEstado(painel, estado) {
    painel.querySelectorAll("[data-cg-estado]").forEach((el) => (el.hidden = el.dataset.cgEstado !== estado));
  }

  async function iniciar(painel) {
    if (gravacao) {
      aviso("Termine a gravação que já está em andamento.");
      return;
    }
    let fluxo;
    try {
      fluxo = await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch (e) {
      aviso("Permita o uso do microfone para gravar.");
      return;
    }
    const gravador = new MediaRecorder(fluxo);
    const pedacos = [];
    const inicio = Date.now();
    const tempoEl = painel.querySelector("[data-cg-tempo]");
    gravacao = { painel, gravador, fluxo, timer: 0, dataUrl: "" };

    gravador.ondataavailable = (e) => e.data.size && pedacos.push(e.data);
    gravador.onstop = () => {
      clearInterval(gravacao.timer);
      fluxo.getTracks().forEach((t) => t.stop());
      const blob = new Blob(pedacos, { type: gravador.mimeType || "audio/webm" });
      const leitor = new FileReader();
      leitor.onloadend = () => {
        gravacao.dataUrl = leitor.result;
        const area = painel.querySelector("[data-cg-player]");
        area.innerHTML = "";
        const player = document.createElement("audio-player");
        player.setAttribute("mini", "");
        player.setAttribute("hue", "275");
        player.setAttribute("capa", meuAvatar());
        player.setAttribute("src", leitor.result);
        area.appendChild(player);
        mostrarEstado(painel, "previa");
      };
      leitor.readAsDataURL(blob);
    };

    gravador.start();
    tempoEl.textContent = "0:00";
    gravacao.timer = setInterval(() => {
      const s = Math.floor((Date.now() - inicio) / 1000);
      tempoEl.textContent = `${Math.floor(s / 60)}:${String(s % 60).padStart(2, "0")}`;
      if (s >= LIMITE_SEGUNDOS && gravador.state === "recording") gravador.stop();
    }, 250);
    mostrarEstado(painel, "gravando");
  }

  function descartar(painel) {
    if (gravacao && gravacao.gravador.state === "recording") {
      gravacao.gravador.onstop = null;
      gravacao.gravador.stop();
      gravacao.fluxo.getTracks().forEach((t) => t.stop());
      clearInterval(gravacao.timer);
    }
    gravacao = null;
    painel.querySelector("[data-cg-player]").innerHTML = "";
    mostrarEstado(painel, "inicial");
  }

  async function enviar(painel) {
    if (!gravacao || !gravacao.dataUrl) return;
    const botao = painel.querySelector("[data-cg-enviar]");
    botao.disabled = true;
    botao.textContent = "Enviando...";
    const corpo = new FormData();
    corpo.append("id", painel.dataset.audioId);
    corpo.append("audio", gravacao.dataUrl);
    try {
      const r = await (await fetch("comentarioEnviar.php", { method: "POST", body: corpo })).json();
      if (!r.ok) throw new Error(r.erro);
      const lista = painel.querySelector(".comentarios-lista");
      const vazio = lista.querySelector(".comentarios-vazio");
      if (vazio) vazio.remove();
      lista.insertAdjacentHTML("beforeend", htmlComentario(r.comentario));
      atualizarContagem(painel.dataset.audioId, r.total);
      gravacao = null;
      painel.querySelector("[data-cg-player]").innerHTML = "";
      mostrarEstado(painel, "inicial");
      aviso("Comentário enviado!");
    } catch (e) {
      aviso((e && e.message) || "Não foi possível enviar o comentário.");
    }
    botao.disabled = false;
    botao.innerHTML = '<i class="bi bi-send-fill"></i> Enviar';
  }

  async function excluir(btn) {
    if (!confirm("Excluir este comentário?")) return;
    const item = btn.closest(".comentario");
    const painel = btn.closest(".comentarios");
    const corpo = new FormData();
    corpo.append("id", btn.dataset.excluirComentario);
    try {
      const r = await (await fetch("comentarioExcluir.php", { method: "POST", body: corpo })).json();
      if (!r.ok) throw new Error();
      item.remove();
      atualizarContagem(r.audioId, r.total);
      const lista = painel.querySelector(".comentarios-lista");
      if (!lista.querySelector(".comentario")) {
        lista.innerHTML = '<p class="comentarios-vazio texto-2">Nenhum comentário ainda. Seja o primeiro a responder!</p>';
      }
    } catch (e) {
      aviso("Não foi possível excluir o comentário.");
    }
  }

  document.addEventListener("click", (ev) => {
    const alvo = ev.target;
    const btnComentar = alvo.closest("[data-comentar]");
    if (btnComentar) {
      abrirPainel(btnComentar.closest(".post"), btnComentar.dataset.comentar);
      return;
    }
    const painel = alvo.closest(".comentarios");
    if (!painel) return;
    if (alvo.closest("[data-cg-iniciar]")) iniciar(painel);
    else if (alvo.closest("[data-cg-parar]")) gravacao && gravacao.gravador.state === "recording" && gravacao.gravador.stop();
    else if (alvo.closest("[data-cg-descartar]")) descartar(painel);
    else if (alvo.closest("[data-cg-enviar]")) enviar(painel);
    else if (alvo.closest("[data-excluir-comentario]")) excluir(alvo.closest("[data-excluir-comentario]"));
  });
})();
