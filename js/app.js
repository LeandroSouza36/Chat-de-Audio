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

  document.addEventListener("click", (ev) => {
    const btnEstrela = ev.target.closest("[data-estrela]");
    if (btnEstrela) {
      darEstrela(btnEstrela.dataset.estrela, false);
      return;
    }

    const btnCompartilhar = ev.target.closest("[data-compartilhar]");
    if (btnCompartilhar) {
      const url = new URL(btnCompartilhar.dataset.compartilhar, location.href).href;
      if (navigator.share) {
        navigator.share({ title: "Retroway", url }).catch(() => {});
      } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => aviso("Link copiado!"));
      } else {
        prompt("Copie o link:", url);
      }
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
