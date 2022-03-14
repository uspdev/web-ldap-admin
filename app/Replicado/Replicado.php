<?php

namespace App\Replicado;

use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Posgraduacao;

class Replicado
{
    public static function listarVinculos($codpes)
    {
        $vinculos = Pessoa::listarVinculosAtivos($codpes);
        foreach ($vinculos as &$vinculo) {
            switch ($vinculo['tipvinext']) {
                case 'Aluno de Pós-Graduação':
                    $pg = Posgraduacao::obterVinculoAtivo($codpes);
                    $vinculo = array_merge($vinculo, $pg);
                    break;

                case 'Servidor':
                    $servidor = \App\Replicado\Pessoa::obterServidorAtivo($codpes);
                    $vinculo = array_merge($vinculo, $servidor);
                    break;
            }
        }
        return $vinculos;
    }

}
