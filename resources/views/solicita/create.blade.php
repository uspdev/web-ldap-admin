@extends('laravel-usp-theme::master')

@section('content_header')
    <h1>Liberação temporária de administração do computador </h1>
@stop

@section('content')

<h2>Liberação temporária para administração de computador </h2>

<div class="row">
    @include('alerts')

        <div class="col-md-12">
            <form method="post" action="/solicita">
                @csrf
                    
                    Eu, <b>{{ auth()->user()->name }}</b>, número USP, <b>{{ auth()->user()->username }}</b>, solicito a liberação de meu perfil como administrador temporário (1 hora)
                    <br><br>
                    <input type="checkbox" name="ciencia1" value="yes"> 
                    Declaro ter ciência de que essa permissão me coloca sob inteira responsabilidade
                    quanto ao uso de forma idônea de tecnologia. E que a instalação de quaisquer 
                    softwares (obtidos de forma legal ou ilegal) ou similares, que porventura 
                    possam prejudicar a Faculdade, me colocam na posição de responsável sobre 
                    todos os prejuízos que possam ocorrer.

                    <br><br>
                    <input type="checkbox" name="ciencia2" value="estagiarios">
                    Estou ciente de que o manuseio de um computador, quando efetuado sem a devida 
                    seriedade e cuidado, pode acarretar em prejuízos, como a contaminação de uma
                     ou mais máquinas por arquivos maliciosos (que compreendem todos os tipos e 
                     subtipos de vírus de computador) capazes de prejudicar toda uma rede de 
                     computadores, já que, em grande parte, encontram-se interligados, prejudicando 
                     a integridade dos mesmos.
                    <br> <br>

                    <input type="checkbox" name="ciencia3" value="estagiarios">
                    Estou ciente de que o sistema de auditoria da Faculdade coletará toda e 
                    qualquer atividade referente aos privilégios de administração sobre a máquina
                    <br> <br>
                
                    <div class="form-group">
                        <label>Justificativa</label>
                        <textarea class="form-control" name="justificativa" rows="3">{{ old('justificativa')}}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="computer">Em computador deseja a liberação de acesso administrativo?</label>
                        <select name="computer" class="form-control">
                            <option value="" selected="">-- Selecione --</option>
                            @foreach($computers as $computer)
                                <option value="{{ $computer['computer'] }}">
                                    {{ $computer['computer'] }}
                                    ({{ $computer['os'] }})
                                </option>   
                            @endforeach()
                        </select>
                    </div>       

                <div class="form-group">
                  <input type="submit" class="btn btn-primary" value="Enviar">
                </div>
            </form>
        </div>
    </div>

@stop
