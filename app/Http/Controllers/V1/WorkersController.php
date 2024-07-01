<?php

namespace App\Http\Controllers\V1;

use App\Models\CarsService;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use App\Models\CarsOwner;
use Illuminate\Support\Facades\DB;
use App\Models\Worker;
use Carbon\Carbon;

class WorkersController extends Controller
{
    public function index(AuthRequest $request)
    {
        $worker = Worker::orderBy('id', 'DESC')->get();

        return response()->json([
            'status' => true,
            'workers' => $worker,
        ], 200);
    }

    public function register(AuthRequest $request)
    {


        
         DB::beginTransaction();

         try {
 
             $user = Worker::create([
                 'name' => $request->name,
                 'email' => $request->email,
                 'salary' => $request->salary,
                 'sex' => $request->sex,
                 'age' => $request->age,
                 'adress' => $request->adress,
             ]);
 
             DB::commit();
 
             return response()->json([
                 'status' => true,
                 'user' => $user,
                 'message' => "Trabalhador cadastrado com sucesso!",
             ], 201);
            } 
            catch (Exception $e) 
            {
 
                DB::rollBack(); 
    

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 400);
                
            }
        }
        public function update(AuthRequest $request)
        {
    
            DB::beginTransaction();
            $worker = Worker::where('id',$request->id)->first();
            try {
                $worker->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'salary'=>$request->salary,
                    'sex'=>$request->sex,
                    'age'=>$request->age,
                    'adress'=> $request->adress,
                    'created_at'=> $worker->created_at,
                    'updated_at'=>Carbon::now()->format('Y-m-d H:i:s')
                ]);
    
                DB::commit();
    
                return response()->json([
                    'status' => true,
                    'user' => $worker,
                    'message' => "Funcionário editado com sucesso!",
                ], 200);
            } catch (Exception $e) {
    
                DB::rollBack();
    
                return response()->json([
                    'status' => false,
                    'message' => "Funcionário não editado!",
                ], 400);
            }
        }

        
    public function destroy(Worker $worker,AuthRequest $request )
    {
         
        $worker = Worker::where('id',$request->id);
        try {

            $worker->delete();

            return response()->json([
                'status' => true,
                'user' => $worker,
                'message' => "Funcionário apagado com sucesso!",
            ], 200);


        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Funcionário não apagado!",
            ], 400);
        }
    }

    public function show(AuthRequest $request, $id)
    {
        $worker = Worker::where('id',$id)->get();
        $count = CarsService::where('worker_id',$id)->get()->count();
        return response()->json([
            "worker"=>$worker,
            "count"=>$count
        ]);
    }
    public function especial(AuthRequest $request)
    {
        $query = Worker::query();
        

        if (isset($request->name)) $query->where('name', 'ilike', '%' . $request->name . '%');
        if(isset($request->sex)&& $request->sex !== 'A')$query->where('sex', $request->sex );

        if(isset($request->startDate))$query->whereDate('created_at', '>=', $request->startDate);  
        if(isset($request->endDate))$query->whereDate('created_at', '<=', $request->endDate);

        if(isset($request->minAge))$query->where(DB::raw('CAST(age AS DECIMAL)'), '>=', (integer)$request->minAge);       
        if(isset($request->maxAge))$query->where(DB::raw('CAST(age AS DECIMAL)'), '<=', (integer)$request->maxAge);       

        if (isset($request->minSalary)) $query->where(DB::raw('CAST(salary AS DECIMAL)'), '>=', (float)$request->minSalary); 
        if (isset($request->maxSalary)) $query->where(DB::raw('CAST(salary AS DECIMAL)'), '<=', (float)$request->maxSalary);

        if(isset($request->order))
        {
            if($request->order == "salaryWorkers")$query->orderBy('salary','desc');
            if($request->order=="nameWorkers")$query->orderBy('name');
            if($request->order=="moreServices")
            {
                $services = CarsService::select('worker_id', DB::raw('count(*) as total'))
                ->groupBy('worker_id')
                ->get();
                
                $workerIds = $services->pluck('worker_id')->toArray();
                $query->whereIn('id', $workerIds)->get()->keyBy('id');
                if(isset($request->ordenateOrder))
                {
                    return response()->json([            
                        'status' => true,
                        'workers' => $query->get(),            
                ],200);
                }
                return response()->json([            
                    'status' => true,
                    'workers' => $query->get()->reverse()->values(),            
            ],200);
            }
        }

        if(isset($request->ordenateOrder))$worker = $query->get()->reverse()->values();              
        else $worker = $query->get();
        
        return response()->json([            
                'status' => true,
                'workers' => $worker,            
        ],200);
    }

}
