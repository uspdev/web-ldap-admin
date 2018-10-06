@extends('adminlte::page')

@section('content')
@include('alerts')

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>username</th>
                <th>status</th>
                <th colspan="2">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ldapusers as $ldapuser)
            <tr>
                <td><a href="ldapusers/{{$ldapuser->samaccountname[0]}}"> {{ $ldapuser->samaccountname[0] }}</a></td>
                
                <td>
                    <a href="#" class="btn btn-warning">Editar</a>
                </td>
                <td>
                    <form action="ldapusers/{{$ldapuser->samaccountname[0]}}" method="post">
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
