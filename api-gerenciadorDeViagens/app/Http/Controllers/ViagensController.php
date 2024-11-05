<?php

namespace App\Http\Controllers;

use App\Models\viagens;
use App\Http\Requests\StoreviagensRequest;
use App\Http\Requests\UpdateviagensRequest;
use Illuminate\Support\Carbon;

class ViagensController extends Controller
{
    public function __construct(viagens $viagem){
        $this->viagem = $viagem;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $viagem = $this->viagem->all();
        return response()->json($viagem, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreviagensRequest $request)
    {
        try {
            // Validação dos campos obrigatórios
            $request->validate($this->viagem->rules(), $this->viagem->feedback());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => $e->validator->errors()
            ], 422); // 422 Unprocessable Entity
        }

        // Verificar se já existe uma viagem com os mesmos dados
        $duplicata = $this->viagem->where([
            ['Nome_do_Solicitante', $request->Nome_do_Solicitante],
            ['Destino', $request->Destino],
            ['Data_de_Ida', Carbon::createFromFormat('d/m/Y', $request->Data_de_Ida)->format('Y-m-d')],
            ['Data_de_Volta', Carbon::createFromFormat('d/m/Y', $request->Data_de_Volta)->format('Y-m-d')]
        ])->first();

        if ($duplicata) {
            return response()->json([
                'error' => 'Viagem duplicada! Já existe uma viagem com esses dados.'
            ], 409);
        }

        $this->viagem->create([
            'Nome_do_Solicitante' => $request->Nome_do_Solicitante,
            'Destino' => $request->Destino,
            'Data_de_Ida' => Carbon::createFromFormat('d/m/Y', $request->Data_de_Ida)->format('Y-m-d'),
            'Data_de_Volta' => Carbon::createFromFormat('d/m/Y', $request->Data_de_Volta)->format('Y-m-d'),
            'status' => $request->status
        ]);

        return response()->json(['mensagem' =>'Pedido de Viagem criado com sucesso!'], 201);
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
    public function update(UpdateviagensRequest $request, $id)
    {
        $viagem = $this->viagem->find($id);
        if($viagem === null){
            return response()->json(['error' => 'ID dessa viagem não existe'], 404);
        }

        if ($request->method() === 'PATCH') {
            $dynamicRules = array();

            foreach ($viagem->rules() as $input => $rule) {
                if ($request->has($input)) {
                   $dynamicRules[$input] = $rule;
                }
            }

            try {
                $request->validate($dynamicRules, $viagem->feedback());
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'error' => $e->validator->errors()
                ], 422); // 422 Unprocessable Entity
            }
        }else{
            try {
                $request->validate($viagem->rules(), $viagem->feedback());
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'error' => $e->validator->errors()
                ], 422); // 422 Unprocessable Entity
            }
        }

        $dataToUpdate = $request->only(['Nome_do_Solicitante', 'Destino', 'Data_de_Ida', 'Data_de_Volta', 'status']);

        // Formatar as datas se estiverem presentes na requisição
        if ($request->filled('Data_de_Ida')) {
            $dataToUpdate['Data_de_Ida'] = Carbon::createFromFormat('d/m/Y', $request->Data_de_Ida)->format('Y-m-d');
        }
        if ($request->filled('Data_de_Volta')) {
            $dataToUpdate['Data_de_Volta'] = Carbon::createFromFormat('d/m/Y', $request->Data_de_Volta)->format('Y-m-d');
        }

        $viagem->update($dataToUpdate);

        return response()->json(['mensagem' => 'Pedido de Viagem atualizado com sucesso!'], 200);
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
