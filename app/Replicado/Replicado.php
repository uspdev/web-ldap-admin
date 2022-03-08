<?php

namespace App\Replicado;

use Uspdev\Replicado\Pessoa;
use Uspdev\Replicado\Posgraduacao;

class Replicado
{
    public static function listarVinculos($codpes)
    {
        $vinculos = [];
        if ($vinculosSiglas = Pessoa::vinculosSiglas($codpes)) {

            foreach ($vinculosSiglas as $vinculo) {
                $vinculos[] = [
                    'tipvin' => $vinculo,
                    'tipvinext' => $vinculo, //tem de pegar do replicado, temporariamente assim
                ];
            }

            foreach ($vinculos as &$vinculo) {
                switch ($vinculo['tipvin']) {
                    case 'ALUNOPOS':
                        $pg = Posgraduacao::obterVinculoAtivo($codpes);
                        $vinculo = array_merge($vinculo, $pg);
                        break;

                    case 'SERVIDOR':
                        $servidor = \App\Replicado\Pessoa::obterServidorAtivo($codpes);
                        $vinculo = array_merge($vinculo, $servidor);
                        break;
                }
            }
        }
        return $vinculos;
    }

}
