<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use App\Rules\Numeros_USP;

class ConfigController extends Controller
{
  public function show()
  {
    $this->authorize('manager');
    $config = Config::all()->last();
    if ($config == null) {
      $config = new Config;
      $config->save();
    }
    $codpes_sem_vinculo = $config->codpes_sem_vinculo;
    return view('configs/edit', compact('codpes_sem_vinculo'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Config  $config
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request)
  {
    $this->authorize('manager');
    $request->validate([
      'codpes_sem_vinculo' => [new Numeros_USP($request->codpes_sem_vinculo)],
    ]);
    $codpes_sem_vinculo = explode(',', $request->codpes_sem_vinculo);
    $codpes_sem_vinculo = array_map('trim', $codpes_sem_vinculo);
    $codpes_sem_vinculo = implode(',', $codpes_sem_vinculo);

    $config = Config::all()->last();
    $config->codpes_sem_vinculo = $codpes_sem_vinculo;
    $request->session()->flash('alert-info', 'Números USP alterados com sucesso');
    $config->save();
    return redirect()->back();
  }
}
