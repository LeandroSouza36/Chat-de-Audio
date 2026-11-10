<!-- Player de stories em tela cheia (preenchido pelo js/app.js) -->
<div class="story-viewer" id="storyViewer" hidden>
    <div class="story-fundo" data-sv-fechar></div>
    <div class="story-caixa" data-sv-caixa>
        <div class="story-barras" data-sv-barras></div>

        <header class="story-topo">
            <img class="avatar avatar-36" data-sv-avatar alt="" onerror="this.onerror=null;this.src='img/usuario/padrao.svg'">
            <div class="story-quem">
                <a class="story-nome" data-sv-nome href="#"></a>
                <span data-sv-tempo></span>
            </div>
            <button type="button" class="story-btn" data-sv-excluir title="Excluir este story" hidden><i class="bi bi-trash3"></i></button>
            <button type="button" class="story-btn" data-sv-fechar title="Fechar"><i class="bi bi-x-lg"></i></button>
        </header>

        <div class="story-centro">
            <div class="story-disco" data-sv-disco>
                <img data-sv-capa alt="" onerror="this.style.visibility='hidden'">
                <span class="story-furo"></span>
            </div>
            <button type="button" class="story-play" data-sv-play title="Pausar / tocar"><i class="bi bi-pause-fill"></i></button>
            <p class="story-expira"><i class="bi bi-hourglass-split"></i> <span data-sv-expira></span></p>
        </div>

        <button type="button" class="story-nav story-anterior" data-sv-anterior aria-label="Story anterior"><i class="bi bi-chevron-left"></i></button>
        <button type="button" class="story-nav story-proximo" data-sv-proximo aria-label="Próximo story"><i class="bi bi-chevron-right"></i></button>
        <audio data-sv-audio preload="auto"></audio>
    </div>
</div>
