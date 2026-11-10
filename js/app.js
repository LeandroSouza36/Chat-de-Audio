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
    if (caminho.some((el) => el.tagName === "BUTTON" || el.tagName === "CANVAS")) return;
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
        mostrar("previa");
        campoLegenda.focus();
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
    areaPrevia.innerHTML = "";
    campoLegenda.value = "";
    mostrar("inicial");
  }

  function publicar() {
    if (!audioGravado) return;
    formEnvio.querySelector('[name="audio"]').value = audioGravado;
    formEnvio.querySelector('[name="textoAssunto"]').value = campoLegenda.value.trim();
    composer.querySelector("[data-publicar]").disabled = true;
    composer.querySelector("[data-publicar]").textContent = "Publicando...";
    formEnvio.submit();
  }

  composer.querySelectorAll("[data-iniciar]").forEach((b) => b.addEventListener("click", iniciarGravacao));
  composer.querySelector("[data-parar]").addEventListener("click", pararGravacao);
  composer.querySelector("[data-descartar]").addEventListener("click", descartar);
  composer.querySelector("[data-publicar]").addEventListener("click", publicar);
  campoLegenda.addEventListener("keydown", (ev) => {
    if (ev.key === "Enter") {
      ev.preventDefault();
      publicar();
    }
  });

  // Botão "Seu áudio" na barra de stories
  document.querySelectorAll("[data-gravar-story]").forEach((b) =>
    b.addEventListener("click", () => {
      composer.scrollIntoView({ behavior: "smooth", block: "center" });
      iniciarGravacao();
    })
  );

  // Veio de outra página pelo botão "Criar"
  if (location.hash === "#gravar") {
    history.replaceState(null, "", location.pathname);
    setTimeout(() => {
      composer.scrollIntoView({ block: "center" });
      iniciarGravacao();
    }, 300);
  }
})();
