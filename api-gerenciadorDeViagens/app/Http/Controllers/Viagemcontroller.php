<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Viagemcontroller extends Controller
{
    public function __construct(Viagem $viagem){
        $this->viagem = $viagem;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Filtragem opcional por status
        $status = $request->query('status');
        $query = $this->viagem->query()->select('id', 'Nome_do_Solicitante', 'Destino', 'Data_de_Ida', 'Data_de_Volta', 'status');

        if ($status) {
            $query->where('status', $status);
        }

        $viagens = $query->get();
        return response()->json($viagens, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate($this->viagem->rules(), $this->viagem->feedback());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        }

        $duplicata = $this->viagem->where([
            ['Nome_do_Solicitante', $request->Nome_do_Solicitante],
            ['Destino', $request->Destino],
            ['Data_de_Ida', Carbon::createFromFormat('d/m/Y', $request->Data_de_Ida)->format('Y-m-d')],
            ['Data_de_Volta', Carbon::createFromFormat('d/m/Y', $request->Data_de_Volta)->format('Y-m-d')]
        ])->first();

        if ($duplicata) {
            return response()->json(['error' => 'Viagem duplicada! Já existe uma viagem com esses dados.'], 409);
        }

        $this->viagem->create([
            'Nome_do_Solicitante' => $request->Nome_do_Solicitante,
            'Destino' => $request->Destino,
            'Data_de_Ida' => Carbon::createFromFormat('d/m/Y', $request->Data_de_Ida)->format('Y-m-d'),
            'Data_de_Volta' => Carbon::createFromFormat('d/m/Y', $request->Data_de_Volta)->format('Y-m-d'),
            'status' => $request->input('status', 'solicitado') // Define "solicitado" como padrão
        ]);

        return response()->json(['mensagem' => 'Pedido de Viagem criado com sucesso!'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $viagem = $this->viagem->find($id);
        if($viagem === null){
            return response()->json(['error' => 'ID dessa viagem não existe'], 404);
        }
        return response()->json($viagem, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $viagem = $this->viagem->find($id);
        if ($viagem === null) {
            return response()->json(['error' => 'ID dessa viagem não existe'], 404);
        }

        // Atualiza apenas o status
        $status = $request->input('status');
        if (in_array($status, ['aprovado', 'cancelado', 'solicitado'])) {
            $viagem->update(['status' => $status]);
            return response()->json(['mensagem' => 'Status do Pedido de Viagem atualizado com sucesso!'], 200);
        } else {
            if (!$request->input('status')) {
                return response()->json(['error' => 'Campo inválido! Apenas alteração de "status" permitida.'], 400);
            } else {
                return response()->json(['error' => 'Status inválido! Apenas "solicitado", "aprovado" ou "cancelado" são permitidos.'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $viagem = $this->viagem->find($id);
        if($viagem === null){
            return response()->json(['error' => 'ID dessa viagem não existe'], 404);
        }

        $viagem->delete();
        return response()->json(['mensagem' => 'Pedido de Viagem removido com sucesso!'], 200);
    }
}
