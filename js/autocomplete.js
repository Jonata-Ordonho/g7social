// Campo de busca com sugestões via AJAX (ex.: doador na arrecadação, voluntário na inscrição).
// Estrutura esperada:
//   <div class="autocomplete">
//       <input type="text" data-url="busca.php" data-parsley-selecionado="#id_oculto">
//       <input type="hidden" id="id_oculto">
//       <ul class="autocomplete-lista"></ul>
//   </div>
// O servidor recebe "consulta" e responde [{id, nome, detalhe}].

// Só aceita o formulário se um item da lista foi escolhido.
// Registrado antes do document.ready para existir quando o Parsley montar o formulário.
window.Parsley.addValidator('selecionado', {
    validateString: function(value, requirement){
        return $(requirement).val() != '';
    },
    messages: {en: 'Selecione um item da lista.'}
});

$(document).ready(function(){
    $('.autocomplete').each(function(){
        var $campo = $(this).children('input[type="text"]');
        var $id = $(this).children('input[type="hidden"]');
        var $lista = $(this).children('.autocomplete-lista');
        var temporizador = null;
        var requisicao = null;
        var ativo = -1;

        function fecharLista(){
            $lista.empty().hide();
            ativo = -1;
        }

        function marcarAtivo(indice){
            var $itens = $lista.children('li[data-id]');
            if(!$itens.length){
                return;
            }
            ativo = (indice + $itens.length) % $itens.length;
            $itens.removeClass('ativo').eq(ativo).addClass('ativo');
        }

        function selecionar($item){
            $campo.val($item.data('rotulo'));
            $id.val($item.data('id'));
            fecharLista();
            $campo.parsley().validate();
        }

        function buscar(consulta){
            if(requisicao){
                requisicao.abort();
            }

            requisicao = $.getJSON($campo.data('url'), {consulta: consulta}, function(itens){
                $lista.empty();
                ativo = -1;

                if(!itens.length){
                    $lista.append($('<li class="vazio">').text('Nenhum resultado encontrado.'));
                }

                $.each(itens, function(i, item){
                    $('<li>')
                        .attr('data-id', item.id)
                        .data('rotulo', item.nome + ' - ' + item.detalhe)
                        .append($('<b>').text(item.nome))
                        .append($('<small>').text(item.detalhe))
                        .appendTo($lista);
                });

                $lista.show();
            });
        }

        $campo.on('input', function(){
            // Texto alterado: o item escolhido anteriormente deixa de valer
            $id.val('');
            clearTimeout(temporizador);

            var consulta = $.trim($campo.val());
            if(consulta.length < 2){
                fecharLista();
                return;
            }

            temporizador = setTimeout(function(){ buscar(consulta); }, 300);
        });

        $campo.on('keydown', function(e){
            if(!$lista.is(':visible')){
                return;
            }

            if(e.key == 'ArrowDown'){
                e.preventDefault();
                marcarAtivo(ativo + 1);
            }else if(e.key == 'ArrowUp'){
                e.preventDefault();
                marcarAtivo(ativo - 1);
            }else if(e.key == 'Enter'){
                var $item = $lista.children('li.ativo');
                if($item.length){
                    e.preventDefault();
                    selecionar($item);
                }
            }else if(e.key == 'Escape'){
                fecharLista();
            }
        });

        // mousedown dispara antes do blur do campo
        $lista.on('mousedown', 'li[data-id]', function(e){
            e.preventDefault();
            selecionar($(this));
        });

        $campo.on('blur', fecharLista);
    });
});
