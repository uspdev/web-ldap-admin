<form action="ldapusers/{{ $user->samaccountname[0] }}" method="post">
  @csrf
  @method('delete')
  <button class="btn btn-sm btn-danger delete-item" type="submit">
    Excluir <i class="fas fa-trash-alt ml-1"></i>
  </button>
</form>

@once
  @section('javascripts_bottom')
    @parent
    <script type="text/javascript">
      $(document).ready(function() {
        $(".delete-item").on("click", function() {
          return confirm("Tem certeza que quer excluir?")
        })
      })
    </script>
  @endsection
@endonce
