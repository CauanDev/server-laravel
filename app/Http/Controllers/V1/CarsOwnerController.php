<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CarsOwner;
use App\Models\Cars;
use App\Models\CarsService;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class CarsOwnerController extends Controller
{
    public function index()
    {
        $carOwners = CarsOwner::orderBy('id', 'DESC')->get();

        return response()->json([
            'status' => true,
            'owners' => $carOwners,
        ], 200);
    }
    public function register(AuthRequest $request)
    {
        
        DB::beginTransaction();
        
        try {

            $owners = CarsOwner::create([
                'name' => $request->name,
                'email' => $request->email,
                'active' => 'ativo',
                'sex' => $request->sex,
                'age' => $request->age,
                'adress' => $request->adress,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'owners' => $owners,
                'message' => "Proprietário cadastrado com sucesso!",
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

    public function destroy(CarsOwner $carsOwner,AuthRequest $request )
    {
         
        $carsOwner = CarsOwner::where('id',$request->id);
        try {

            $carsOwner->delete();

            return response()->json([
                'status' => true,
                'owners' => $carsOwner,
                'message' => "Proprietário apagado com sucesso!",
            ], 200);


        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Proprietário não apagado!",
            ], 400);
        }
    }

    public function especial(AuthRequest $request){
        
        $query = CarsOwner::query();


        if (isset($request->name)) $query->where('name', 'ilike', '%' . $request->name . '%');
        if(isset($request->sex)&& $request->sex !== 'A')$query->where('sex', $request->sex );
        
        
        if (isset($request->startDate) && isset($request->endDate))$query->whereBetween('created_at', [$request->startDate, $request->endDate]);
        else
        {
           

            if(isset($request->startDate))
            {
                $query->whereDate('created_at', '>=', $request->startDate);
            }
            if(isset($request->endDate))
            {
                $query->whereDate('created_at', '<=', $request->endDate);
            }
        }
        if(isset($request->sex) && $request->sex!=='A')$query->where('sex', $request->sex );            
        if (isset($request->minAge) && isset($request->maxAge)) {
            $query->whereBetween('age', [$request->minAge, $request->maxAge]);
        } else {
            if (isset($request->minAge)) {
                $query->where('age', '>=', $request->minAge);
            }
            if (isset($request->maxAge)) {
                $query->where('age', '<=', $request->maxAge);
            }
        }
        if(isset($request->brand) && $request->brand !== 'All')
        {
            $cars = Cars::where('brand',$request->brand)->get('owner_id');
            $query->whereIn('id',$cars);

        }
        if($request->order =="ageOrder")$query->orderBy('age','desc');    
        if($request->order =="nameOwners") $query->orderBy('name');            
 
        if($request->order =="moreServices") $query->orderBy('number_services');
            
            if($request->order =="moreVehicles")
            {
                $cars = Cars::select('owner_id', DB::raw('count(*) as total'))
                ->groupBy('owner_id')
                ->orderBy('total', 'desc') 
                ->get();

                $ownerIds = $cars->pluck('owner_id')->toArray();
      
                $query->whereIn('id', $ownerIds);
                
                if($request->ordenateOrder)
                {
                    $owner = $query->get()->reverse()->values();
                    return response()->json([            
                            'status' => true,
                            'owner' => $owner,            
                    ],200);
                }

            }
        
        if($request->ordenateOrder)$owner = $query->get()->reverse()->values();
        else $owner = $query->get();
        return response()->json([            
                'status' => true,
                'owner' => $owner,            
        ],200);
    }
    public function update(AuthRequest $request)
    {
        DB::beginTransaction();
        $owner = CarsOwner::where('id',$request->id)->first();
        try {
            $owner->update([
                'name' => $request->name,
                'email' => $request->email,
                'sex'=>$request->sex,
                'age'=>$request->age,
                'adress'=> $request->adress,
                'created_at'=> $owner->created_at,
                'updated_at'=>Carbon::now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'owner' => $owner,
                'message' => "Funcionário editado com sucesso!",
            ], 200);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    public function show(AuthRequest $request)
    {
        $carsQuery = Cars::where('owner_id', $request->id)->select('model','last_service');

        $carsTable = $carsQuery->get();
        $carCount = $carsQuery->count();
        $totalPrice = CarsService::where('owner_id', $request->id)->sum('price');

        return response()->json([
            'status' => true,
            'data' => [
                'cars' => $carsTable,
                'totalPrice' => $totalPrice,
                'owner'=> CarsOwner::where('id',$request->id)->first()
            ],
            'count'=> $carCount
        ], 200);
    }


}
