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

    /**
     * Método para listar todos os vínculos por extenso e setores de uma pessoa
     *
     * Somente ATIVOS
     * Também Docente Aposentado
     *
     * @param Integer $codpes
     * @param (opt) $codundclg (default=null)
     * @return array
     * @author modificado por Alessandro em 10/11/2022
     */
    public static function listarVinculosExtensoSetores(int $codpes, $codundclg = null) # codundclg não pode ser Integer por conta de mais de uma unidade
    {
        $codundclg = $codundclg ?: getenv('REPLICADO_CODUNDCLGS');
        $codundclg = $codundclg ?: getenv('REPLICADO_CODUNDCLG');

        // Array com os códigos de unidades
        $arrCodUnidades = explode(',', $codundclg);

        // Somente os vínculos regulares 'ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT', 'SERVIDOR', 'ESTAGIARIORH'
        // Considerando mais de uma unidade, ex.: 84 = Alunos de Pós-Graduação Interunidades
        $query = "SELECT * FROM LOCALIZAPESSOA WHERE codpes = CONVERT(INT, :codpes)
                 AND codfncetr = 0 --exclui designados
                 AND tipvinext != 'Servidor Aposentado' --exclui funcionários não docentes aposentados
                 AND tipvin IN ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT', 'SERVIDOR', 'ESTAGIARIORH')
                 AND codundclg IN ({$codundclg})";
        $param['codpes'] = $codpes;
        $result = DB::fetchAll($query, $param);

        // Inicializa o array de vínculos e setores
        $vinculosSetores = array();
        foreach ($result as $row) {
            if (!empty($row['tipvinext'])) {
                $vinculo = trim($row['tipvinext']);
                // Adiciona os vínculos por extenso
                array_push($vinculosSetores, $vinculo);
                // Adiciona o departamento quando também for Aluno de Graduação
                if (trim($row['tipvinext']) == 'Aluno de Graduação') {
                    // Considerando o primeiro código de unidade
                    $setorGraduacao = Graduacao::setorAluno($row['codpes'], $arrCodUnidades[0])['nomabvset'];
                    array_push($vinculosSetores, $row['tipvinext'] . ' ' . $setorGraduacao);
                }
            }
            if (!empty(trim($row['nomabvset']))) {
                $setor = trim($row['nomabvset']);
                // Remove o código da unidade da sigla do setor
                // Considerando o primeiro código de unidade
                $setor = str_replace('-' . $arrCodUnidades[0], '', $setor);
                // Adiciona as siglas dos setores
                array_push($vinculosSetores, $setor);
                // Adiciona os vínculos por extenso concatenando a sigla do setor
                array_push($vinculosSetores, $row['tipvinext'] . ' ' . $setor);
            }
        }
        $vinculosSetores = array_unique($vinculosSetores);
        sort($vinculosSetores);
        return $vinculosSetores;
    }

}
