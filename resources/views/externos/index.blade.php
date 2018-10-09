@extends('adminlte::page')

@section('content')
@include('alerts')

<a class="btn btn-info" href="/externos/create">Novo</a>

<br>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Existe no Ldap?</th>
                <th colspan="2">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($externos as $externo)
            <tr> 
                <td><a href="/ldapusers/e{{ $externo->id  }}"> e{{ $externo->id }}</a></td>
                <td>{{ $externo->nome }}</td>
                <td>{{ $externo->email }}</td>
                <td><b>{{ $externo->ldap }}</b></td>
                
                <td>
                    <form action="/externos/{{$externo->id}}" method="post">
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
