<?php // Campos do formulário de atividade (usado no cadastro e na alteração) ?>
<div class="formulario">
    <div>
        <label>Título</label>
        <input type="text" name="titulo" maxlength="200" required placeholder="Ex.: Triagem das doações" value="<?php echo htmlspecialchars($dados['titulo']); ?>">
    </div>

    <div>
        <label>Descrição para a página de inscrição</label>
        <textarea name="descricao" rows="5"><?php echo htmlspecialchars((string) $dados['descricao']); ?></textarea>
    </div>

    <div>
        <label>Local</label>
        <input type="text" name="local" maxlength="200" required value="<?php echo htmlspecialchars($dados['local']); ?>">
    </div>

    <div>
        <label>Data de início</label>
        <input type="date" name="data_inicio" required value="<?php echo htmlspecialchars($dados['data_inicio']); ?>">
    </div>

    <div>
        <label>Data de término</label>
        <input type="date" name="data_fim" required value="<?php echo htmlspecialchars($dados['data_fim']); ?>">
    </div>

    <div>
        <label>Horário <span>(opcional)</span></label>
        <input type="text" name="horario" maxlength="100" placeholder="Ex.: 08:00 às 12:00" value="<?php echo htmlspecialchars((string) $dados['horario']); ?>">
    </div>

    <div>
        <label>Vagas para voluntários <span>(deixe em branco para ilimitado)</span></label>
        <input type="number" name="vagas" min="1" step="1" value="<?php echo htmlspecialchars((string) $dados['vagas']); ?>">
    </div>

    <div>
        <label>Status <span>(a página de inscrição só fica disponível com a atividade ativa)</span></label>
        <select name="status" required>
            <option value="on" <?php if($dados['status'] == 'on'){ echo 'selected'; } ?>>Ativa</option>
            <option value="off" <?php if($dados['status'] == 'off'){ echo 'selected'; } ?>>Inativa</option>
        </select>
    </div>
</div>
