</div><!-- fim .casca (aberta em partes/menu.php) -->

<!-- Janela para abrir um áudio (usada nas Paradas) -->
<div class="modal" id="modalPost" hidden>
    <div class="modal-fundo" data-fechar></div>
    <div class="modal-caixa">
        <button class="modal-fechar" data-fechar aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
        <div id="modalPostConteudo"></div>
    </div>
</div>

<!-- Janela de denúncia -->
<div class="modal" id="modalDenuncia" hidden>
    <div class="modal-fundo" data-fechar-denuncia></div>
    <form class="cartao denuncia-caixa" id="formDenuncia">
        <h2 class="editar-titulo"><i class="bi bi-flag"></i> Denunciar</h2>
        <p class="texto-2 denuncia-sub">A denúncia é anônima: a outra pessoa não fica sabendo quem denunciou.</p>
        <label class="rotulo-campo" for="motivoDenuncia">Motivo</label>
        <select id="motivoDenuncia" name="motivo" class="campo" required>
            <option value="">Escolha um motivo...</option>
            <option value="spam">Spam ou propaganda</option>
            <option value="assedio">Assédio ou bullying</option>
            <option value="odio">Discurso de ódio</option>
            <option value="sexual">Conteúdo sexual</option>
            <option value="violencia">Violência ou ameaça</option>
            <option value="falso">Informação falsa ou golpe</option>
            <option value="outro">Outro</option>
        </select>
        <label class="rotulo-campo" for="detalheDenuncia">Detalhes (opcional)</label>
        <textarea id="detalheDenuncia" name="detalhe" class="campo" rows="3" maxlength="300" placeholder="Conte o que aconteceu..."></textarea>
        <div class="editar-acoes">
            <button type="button" class="botao botao-link-cinza" data-fechar-denuncia>Cancelar</button>
            <button type="submit" class="botao botao-neon">Enviar denúncia</button>
        </div>
    </form>
</div>

<div class="aviso" id="aviso" hidden></div>

<script src="audio-player.js?v=10"></script>
<script src="js/app.js?v=10"></script>
</body>
</html>
