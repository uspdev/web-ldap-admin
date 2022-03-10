<?php

namespace App\Replicado;

use Uspdev\Replicado\DB;
use Uspdev\Replicado\Pessoa as ReplicadoPessoa;

class Pessoa extends ReplicadoPessoa
{
    /**
     * Obtém dados detalhado de servidor
     */
    public static function obterServidorAtivo($codpes)
    {
        $sql = "SELECT * FROM VINCULOPESSOAUSP
            WHERE codpes = convert(int,:codpes)
            AND tipvin = 'SERVIDOR'
            AND dtafimvin IS NULL
        ";
        $param['codpes'] = $codpes;
        return DB::fetch($sql, $param);
    }
}
