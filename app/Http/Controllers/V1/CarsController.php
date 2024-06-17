<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use App\Models\Cars;
use App\Models\CarsOwner;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;
class CarsController extends Controller
{


    public function index(AuthRequest $request)
    {
        $cars = Cars::orderBy('id', 'DESC')->get();

        return response()->json([
            'status' => true,
            'cars' => $cars,
        ], 200);
    }
    public function register(AuthRequest $request)
    {
        

        if(isset($request->id))$owner_id = $request->id;
        if(isset($request->owner_id)){
            $owner_id = $request->owner_id;
        }
        
        if(isset($request->name)){
            $owner_id = CarsOwner::where('name',$request->name)->get('id');
            $owner_id = $owner_id[0]->id;
        }
        $zero =0;
        try {
            $car = Cars::create([
                'owner_id' => $owner_id,  
                'brand' => $request->brand,
                'model'=>$request->model,
                'year' => $request->year,
                'number_services'=>  0
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'owners' => $car,
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

    public function brand(AuthRequest $request)
    {
        $query = CarsOwner::query();
        if(empty($request))
        {
            $query->get();
        }
        if (isset($request->name)) {
            $query->where('name', 'ilike', '%' . $request->name . '%');
        }
        
        if (isset($request->sex) && $request->sex !== 'A') {
            $query->where('sex', $request->sex);
        }
        
        if (isset($request->startDate) && isset($request->endDate)) {
            $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
        } else {
            if (isset($request->startDate)) {
                $query->whereDate('created_at', '>=', $request->startDate);
            }
            if (isset($request->endDate)) {
                $query->whereDate('created_at', '<=', $request->endDate);
            }
        }
        
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
        
        $ownerIds = $query->pluck('id');
        
        $brands = Cars::whereIn('owner_id', $ownerIds)
        ->select('brand', DB::raw('count(*) as total'))
        ->groupBy('brand')
        ->orderBy('total', 'DESC')
        ->get();
        
        return response()->json($brands);
    }
    public function update(AuthRequest $request)
    {
                
        DB::beginTransaction();
        $cars = Cars::where('id',$request->id)->first();
        
        
        try {

            $cars->update([
                    'owner_id' => $request->owner_id,  
                    'brand' => $request->brand,
                    'model'=>$request->model,
                    'year' => $request->year,
                    'number_services'=>  $request->number_services
                ]);
          
                Cars::where('id', $request->id)->first()->update(['last_service' => Carbon::now()]);
                DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Proprietário Atualizado com sucesso!",
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

    public function especial(AuthRequest $request)
    {
        DB::beginTransaction();

        $query = Cars::query();
        if ($request->countModel) {
            $query->select('model', DB::raw('count(*) as car_count'))
                  ->groupBy('model')->get();

            return response($query->get());
        }
    
        if ($request->moreServices) {
            $query->orderBy('number_services', 'desc');
        }
    
        if ($request->nameOwners) {
            $carOwnerIds = CarsOwner::select('id')
                                     ->orderBy('name', 'asc')
                                     ->pluck('id');
            $query->whereIn('owner_id', $carOwnerIds);
        } 
        if ($request->startDate && $request->endDate) {
            $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
        } elseif ($request->startDate) {
            $query->whereDate('created_at', '>=', $request->startDate);
        } elseif ($request->endDate) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }
        if ($request->brand) {
            $query->where('brand', $request->brand);
        }
        $data = $query->get();
        return response($data);
    
}
public function destroy($id,AuthRequest $request )
{
     
    $cars = Cars::where('id',$id);
    try {

        $cars->delete();

        return response()->json([
            'status' => true,
            'owners' => $cars,
            'message' => "Carro apagado com sucesso!",
        ], 200);


    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => "Proprietário não apagado!",
        ], 400);
    }
}


}
