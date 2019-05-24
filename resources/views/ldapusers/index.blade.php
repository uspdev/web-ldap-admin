@extends('master')

@section('content')
@include('alerts')

<a href="/ldapusers/create" class="btn btn-success">Criar usuário não replicado</a>
<br><br>
<a href="/ldapusers/sync" class="btn btn-warning">Sincronizar com replicado</a>

<br><br>

<div class="panel panel-default">
  <div class="panel-heading">Filtros</div>
  <div class="panel-body">

    <form method="get" action="/ldapusers">
        <div>
            @foreach($grupos as $grupo)
                <label class="checkbox-inline"><input type="checkbox" name="grupos[]" value="{{$grupo}}">{{$grupo}}</label>
            @endforeach
        </div>
        <br>
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Buscar..." name="search">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-success"> Buscar </button>
            </span>
        </div><!-- /input-group -->
    </form>


  </div>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Número USP</th>
                <th>Nome</th>
                <th>Email</th>
                <th>grupos</th>
                <th>status</th>
                <th colspan="2">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ldapusers as $ldapuser)
            <tr> 
                <td><a href="/ldapusers/{{$ldapuser->getAccountName()}}"> {{ $ldapuser->getAccountName() }}</a></td>
                <td> {{ $ldapuser->getDisplayName() }} </td>
                <td> {{ $ldapuser->getEmail() }} </td>
                <td><?php 
                        $grupos = array_diff($ldapuser->getGroupNames(),['Domain Users']);
                        $grupos = implode(', ',$grupos);
                    ?>
                    {{ $grupos }}
                </td>

                <td>
                    @if($ldapuser->useraccountcontrol[0] == 512)
                      <form action="/ldapusers/{{$ldapuser->samaccountname[0]}}" method="post">
                        {{csrf_field()}} 
                        {{ method_field('patch') }}
                        <input type="hidden" name="status" value="disable">
                        <button class="btn btn-warning" type="submit">Desativar</button>
                      </form>
                    @else
                      <form action="/ldapusers/{{$ldapuser->samaccountname[0]}}" method="post">
                        {{csrf_field()}} 
                        {{ method_field('patch') }}
                        <input type="hidden" name="status" value="enable">
                        <button class="btn btn-info" type="submit">Ativar</button>
                      </form>
                    @endif

                </td>
                
                <td>
                    <form action="/ldapusers/{{$ldapuser->samaccountname[0]}}" method="post">
                      {{csrf_field()}} {{ method_field('delete') }}
                      <button class="delete-item btn btn-danger" type="submit">Deletar</button>
                  </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection

@section('js')
    @parent
    <script type="text/javascript">
        $(function () {
            $(".delete-item").on("click", function(){
                return confirm("Tem certeza?");
            });
        });
    </script>
@stop
