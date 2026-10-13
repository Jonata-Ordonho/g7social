<?php // Campos do formulário de projeto (usado no cadastro e na alteração). Espera: $projeto ?>
<div class="formulario">
    <div>
        <label>Título</label>
        <input type="text" name="titulo" maxlength="200" required value="<?php echo htmlspecialchars($projeto['titulo']); ?>">
    </div>

    <div>
        <label>Descrição</label>
        <textarea name="descricao" rows="5"><?php echo htmlspecialchars((string) $projeto['descricao']); ?></textarea>
    </div>

    <div>
        <label>Início da captação</label>
        <input type="date" name="data_inicio" required value="<?php echo htmlspecialchars($projeto['data_inicio']); ?>">
    </div>

    <div>
        <label>Fim da captação</label>
        <input type="date" name="data_fim" required value="<?php echo htmlspecialchars($projeto['data_fim']); ?>">
    </div>

    <div>
        <label>Arrecadação estimada (R$) <span>(opcional)</span></label>
        <input type="text" name="arrecadacao_estimada" id="arrecadacao_estimada" placeholder="0,00" inputmode="decimal" value="<?php echo htmlspecialchars($projeto['arrecadacao_estimada_campo']); ?>">
        <span class="dica-campo">Valor em dinheiro previsto em doações. Quando preenchido, a listagem mostra o progresso da arrecadação.</span>
    </div>
</div>
<script>
    $(function () {
        $('#arrecadacao_estimada').mask('#.##0,00', {reverse: true});
    });
</script>
