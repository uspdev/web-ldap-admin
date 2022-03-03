<form action="ldapusers/{{ $user->samaccountname[0] }}" method="post">
    @csrf
    @method('delete')
    <button class="delete-item btn btn-light btn-sm" type="submit">
        <i class="fas fa-trash-alt text-danger"></i>
    </button>
</form>

@once
    @section('javascripts_bottom')
        @parent
        <script type="text/javascript">
            $(document).ready(function() {
                $(".delete-item").on("click", function() {
                    return confirm("Tem certeza?")
                })
            })
        </script>
    @endsection
@endonce
