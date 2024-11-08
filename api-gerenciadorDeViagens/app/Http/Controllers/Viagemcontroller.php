<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @OA\Info(
 *      version="1.0",
 *      title="API: Gerenciador de Viagens",
 *      description="Documentação da API para o gerenciamento de pedidos de viagens corporativas.",
 *      @OA\Contact(
 *          email="slpascoal01@gmail.com"
 *      ),
 *      @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *      )
 * )
 */
class Viagemcontroller extends Controller
{
    public function __construct(Viagem $viagem){
        $this->viagem = $viagem;
    }

    /**
     * @OA\Get(
     *     path="/api/viagem",
     *     tags={"Viagens"},
     *     summary="Lista todos os pedidos de viagem",
     *     description="Retorna uma lista de pedidos de viagem. Permite a filtragem opcional por status (solicitado, aprovado, cancelado).",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"solicitado", "aprovado", "cancelado"},
     *         ),
     *         description="Filtra os pedidos de viagem pelo status"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pedidos de viagem",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="Nome_do_Solicitante", type="string", example="João"),
     *                 @OA\Property(property="Destino", type="string", example="Brasil"),
     *                 @OA\Property(property="Data_de_Ida", type="string", format="date", example="2024-11-10"),
     *                 @OA\Property(property="Data_de_Volta", type="string", format="date", example="2024-11-20"),
     *                 @OA\Property(property="status", type="string", example="solicitado")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requisição inválida"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/viagem",
     *     tags={"Viagens"},
     *     summary="Cria um novo pedido de viagem",
     *     description="Este endpoint cria um novo pedido de viagem corporativa. O status é definido como 'solicitado' por padrão, caso não seja informado.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"Nome_do_Solicitante", "Destino", "Data_de_Ida", "Data_de_Volta"},
     *             @OA\Property(property="Nome_do_Solicitante", type="string", example="Tayna"),
     *             @OA\Property(property="Destino", type="string", example="Russia"),
     *             @OA\Property(property="Data_de_Ida", type="string", format="date", example="2024-11-06"),
     *             @OA\Property(property="Data_de_Volta", type="string", format="date", example="2024-11-08"),
     *             @OA\Property(property="status", type="string", example="solicitado", description="O status pode ser 'solicitado', 'aprovado' ou 'cancelado'. Se não informado, será atribuído 'solicitado'.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pedido de Viagem criado com sucesso!",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="mensagem", type="string", example="Pedido de Viagem criado com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação. Campos obrigatórios ou com formato inválido.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="Nome_do_Solicitante", type="array", @OA\Items(type="string", example="Obrigatório preencher esse campo"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Viagem duplicada! Já existe uma viagem com esses dados.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Viagem duplicada! Já existe uma viagem com esses dados.")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/viagem/{id}",
     *     tags={"Viagens"},
     *     summary="Obter detalhes de um pedido de viagem",
     *     description="Retorna as informações detalhadas de uma viagem específica com base no ID fornecido",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do pedido de viagem",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do pedido de viagem",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="Nome_do_Solicitante", type="string", example="Tayna"),
     *             @OA\Property(property="Destino", type="string", example="Russia"),
     *             @OA\Property(property="Data_de_Ida", type="string", format="date", example="2024-11-06"),
     *             @OA\Property(property="Data_de_Volta", type="string", format="date", example="2024-11-08"),
     *             @OA\Property(property="status", type="string", example="solicitado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pedido de viagem não encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="ID dessa viagem não existe")
     *         )
     *     )
     * )
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
 * @OA\Patch(
 *     path="/api/viagem/{id}",
 *     tags={"Viagens"},
 *     summary="Atualiza o status de um pedido de viagem",
 *     description="Atualiza apenas o campo `status` do pedido de viagem com base no ID fornecido.",
 *     operationId="updateViagemStatus",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do pedido de viagem a ser atualizado",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"status"},
 *             @OA\Property(property="status", type="string", enum={"solicitado", "aprovado", "cancelado"}, example="aprovado", description="Novo status do pedido de viagem")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Status do Pedido de Viagem atualizado com sucesso!",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="mensagem", type="string", example="Status do Pedido de Viagem atualizado com sucesso!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="ID da viagem não existe",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="ID dessa viagem não existe")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erro de validação de campo",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Campo inválido! Apenas alteração de 'status' permitida.")
 *         )
 *     )
 * )
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
     * @OA\Delete(
     *     path="/api/viagem/{id}",
     *     tags={"Viagens"},
     *     summary="Exclui um pedido de viagem",
     *     description="Exclui um pedido de viagem com o ID especificado.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do pedido de viagem a ser excluído",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido de Viagem removido com sucesso!",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="mensagem", type="string", example="Pedido de Viagem removido com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ID dessa viagem não existe",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="ID dessa viagem não existe")
     *         )
     *     )
     * )
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
