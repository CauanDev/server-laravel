<?php

namespace App\Http\Controllers\V1;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Custom\Jwt;
use App\Models\User;
use Exception;
use Carbon\Carbon;
use App\Http\Requests\AuthRequest;

class UserController extends Controller
{
    public function login(AuthRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {

            $token = Jwt::create($user);
            return response()->json([
                "token"=>$token,
                "user"=>[
                    "name"=>$user->name
                ]
            ]);
        }

        return response()->json(['error' => 'Credenciais inválidas'], 401);
    
    }
    public function especial(AuthRequest $request)
    {
        $query = User::query();

        if (isset($request->name))$query->where('name', 'ilike', '%' . $request->name . '%');
           

        if(isset($request->startDate))$query->whereDate('created_at', '>=', $request->startDate);
        
        if(isset($request->endDate))$query->whereDate('created_at', '<=', $request->endDate);
        if(isset($request->order) && $request->order =="orderDate")$query->orderBy('created_at');
        if(isset($request->order) && $request->order =="orderName")$query->orderBy('name','asc');
        if(isset($request->ordenateOrder)) $users = $query->get()->reverse()->values();
        else $users = $query->get();
        return response()->json([            
                'status' => true,
                'users' => $users,            
        ],200);
    }

    public function register(AuthRequest $request)
    {
         
         DB::beginTransaction();

         try {
 
             $user = User::create([
                 'name' => $request->name,
                 'email' => $request->email,
                 'password' =>Hash::make($request->password)                
             ]);
 
             DB::commit();
 
             return response()->json([
                 'status' => true,
                 'user' => $user,
                 'message' => "Usuário cadastrado com sucesso!",
             ], 201);
         } catch (Exception $e) {
 
             DB::rollBack();
 
             return response()->json([
                 'status' => false,
                 'message' => "Usuário não cadastrado!",
             ], 400);
         }
    }

    public function index(AuthRequest $request)
    {
        $users = User::orderBy('id', 'DESC')->get();

        return response()->json([
            'status' => true,
            'users' => $users,
        ], 200);
    }

    public function destroy(User $user): JsonResponse
    {
        try {

            $user->delete();

            return response()->json([
                'status' => true,
                'user' => $user,
                'message' => "Usuário apagado com sucesso!",
            ], 200);


        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Usuário não apagado!",
            ], 400);
        }
    }



    public function update(AuthRequest $request, User $user): JsonResponse
    {

        DB::beginTransaction();

        try {

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' =>Hash::make($request->password),
                'created_at'=> $user->created_at,
                'updated_at'=>Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'user' => $user,
                'message' => "Usuário editado com sucesso!",
            ], 200);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => "Usuário não editado!",
            ], 400);
        }
    }
}
