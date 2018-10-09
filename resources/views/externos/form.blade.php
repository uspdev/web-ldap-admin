<div class="form-group">
    <label for="nome">Nome</label>
    <input type="text" class="form-control" name="nome" value="{{ $externo->nome or old('nome')  }}" required >
</div>

<div class="form-group">
    <label for="nome">Email</label>
    <input type="text" class="form-control" name="email" value="{{ $externo->email or old('email')  }}" required >
</div>

<div class="form-group">
    <label for="nome">Motivo</label>
    <textarea rows="4" cols="50" class="form-control" name="motivo">{{ $externo->motivo or old('motivo')  }}</textarea>
</div>

<div class="form-group">
    <label for="nome">Data de vencimento</label>
    <input type="text" class="form-control" name="vencimento" value="{{ old('vencimento')  }}" >
</div>

<div class="form-group">
  <input type="submit" class="btn btn-primary" value="Enviar Dados">
</div>
