@extends('master')

@section('content_header')
    <h1>Sincronizar com replicado</h1>
@stop

@section('content')

<div class="row">
    @include('alerts')

        <div class="col-md-6">
            <form method="post" action="/ldapusers/sync">
                {{ csrf_field() }}
                <table class="table">
                    <tr>
                        <th>&nbsp;</th>
                        <th>Vínculo</th>
                        <th>Replicado</th>
                        <th>&nbsp;</th>
                        <th>AD</th>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="type[]" value="servidores"></td>
                        <td>Funcionários</td>
                        <td style="text-align: right;">999</td>
                        <td>>></td>
                        <td style="text-align: right;">999</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="type[]" value="docentes"></td>
                        <td>Docentes</td>
                        <td style="text-align: right;">999</td>
                        <td>>></td>
                        <td style="text-align: right;">999</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="type[]" value="estagiarios"></td>
                        <td>Estagiários</td>
                        <td style="text-align: right;">999</td>
                        <td>>></td>
                        <td style="text-align: right;">999</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="type[]" value="graducao"></td>
                        <td>Alunos de Graduação</td>
                        <td style="text-align: right;">999</td>
                        <td>>></td>
                        <td style="text-align: right;">999</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="type[]" value="posGraduacao"></td>
                        <td>Alunos de Pós-Graduação</td>
                        <td style="text-align: right;">999</td>
                        <td>>></td>
                        <td style="text-align: right;">999</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="type[]" value="posDoutorando"></td>
                        <td>Alunos de Pós-doutorando</td>
                        <td style="text-align: right;">999</td>
                        <td>>></td>
                        <td style="text-align: right;">999</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="type[]" value="culturaExtensao"></td>
                        <td>Alunos de Cultura e Extensão</td>
                        <td style="text-align: right;">999</td>
                        <td>>></td>
                        <td style="text-align: right;">999</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="type[]" value="escolaArteDramatica"></td>
                        <td>Alunos da Escola de Arte Dramática</td>
                        <td style="text-align: right;">999</td>
                        <td>>></td>
                        <td style="text-align: right;">999</td>
                    </tr>                    
                </table>

                <div class="form-group">
                  <input type="submit" class="btn btn-primary" value="Sincronizar">
                </div>
            </form>
        </div>
    </div>

@stop
