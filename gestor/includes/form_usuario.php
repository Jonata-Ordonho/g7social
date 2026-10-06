<?php
// Campos do formulário de usuário (cadastro e alteração)
// Espera: $dados, $tiposPermitidos, $edicaoPropria (bool), $senhaObrigatoria (bool), $exibirStatus (bool)
?>
<div class="formulario">
    <div>
        <label>Nome</label>
        <input type="text" name="nome" maxlength="150" required value="<?php echo htmlspecialchars($dados['nome']); ?>">
    </div>

    <div>
        <label>E-mail <span>(usado para entrar no sistema)</span></label>
        <input type="email" name="email" maxlength="150" required value="<?php echo htmlspecialchars($dados['email']); ?>">
    </div>

    <div>
        <label>Tipo</label>
        <?php if($edicaoPropria){ ?>
            <input type="text" value="<?php echo htmlspecialchars(\App\Helpers\Permissao::nomeTipo($dados['tipo'])); ?>" disabled>
            <span class="dica-campo">Você não pode alterar o seu próprio tipo.</span>
        <?php }elseif(count($tiposPermitidos) == 1){ ?>
            <?php // Uma única opção possível: exibe fixo, sem lista ?>
            <input type="text" value="<?php echo htmlspecialchars(reset($tiposPermitidos)); ?>" disabled>
            <input type="hidden" name="tipo" value="<?php echo htmlspecialchars(key($tiposPermitidos)); ?>">
        <?php }else{ ?>
            <select name="tipo" required>
                <?php foreach ($tiposPermitidos as $valor => $rotulo) { ?>
                    <option value="<?php echo $valor; ?>" <?php if($dados['tipo'] == $valor){ echo 'selected'; } ?>><?php echo $rotulo; ?></option>
                <?php } ?>
            </select>
        <?php } ?>
    </div>

    <?php if($exibirStatus){ ?>
        <div>
            <label>Status</label>
            <?php if($edicaoPropria){ ?>
                <input type="text" value="Ativo" disabled>
                <span class="dica-campo">Você não pode desativar o seu próprio acesso.</span>
            <?php }else{ ?>
                <select name="status" required>
                    <option value="on" <?php if($dados['status'] == 'on'){ echo 'selected'; } ?>>Ativo</option>
                    <option value="off" <?php if($dados['status'] == 'off'){ echo 'selected'; } ?>>Desativado (não consegue entrar)</option>
                </select>
            <?php } ?>
        </div>
    <?php } ?>

    <div>
        <label>Senha <span><?php echo $senhaObrigatoria ? '(mínimo de 6 caracteres)' : '(deixe em branco para manter a atual)'; ?></span></label>
        <input type="password" name="senha" id="senha" autocomplete="new-password" <?php if($senhaObrigatoria){ echo 'required'; } ?> data-parsley-minlength="6" data-parsley-minlength-message="A senha deve ter pelo menos 6 caracteres.">
    </div>

    <div>
        <label>Confirmar senha</label>
        <input type="password" name="confirmar_senha" autocomplete="new-password" <?php if($senhaObrigatoria){ echo 'required'; } ?> data-parsley-equalto="#senha" data-parsley-equalto-message="A confirmação não confere com a senha.">
    </div>
</div>
