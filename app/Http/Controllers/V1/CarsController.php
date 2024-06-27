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
 
        if (isset($request->startDate)) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }
        if (isset($request->endDate)) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }
        
 
        if (isset($request->minAge)) {
            $query->where('age', '>=', $request->minAge);
        }
        if (isset($request->maxAge)) {
            $query->where('age', '<=', $request->maxAge);
        }
        
        
        $ownerIds = $query->pluck('id');
        
        $brands = Cars::whereIn('owner_id', $ownerIds)
        ->select('brand', DB::raw('count(*) as total'))
        ->groupBy('brand')
        ->orderBy('total', 'DESC')
        ->get();
        
        if(isset($request->separate)) 
        {
            $manIds = CarsOwner::where('sex','M')->get('id')->toArray();
            $womanIds = CarsOwner::where('sex','F')->get('id')->toArray();
            $mans = Cars::whereIn('owner_id', $manIds)
                        ->select('brand', DB::raw('count(*) as total'))
                        ->groupBy('brand')
                        ->orderBy('total', 'DESC')
                        ->get();
            $womans = Cars::whereIn('owner_id', $womanIds)
                        ->select('brand', DB::raw('count(*) as total'))
                        ->groupBy('brand')
                        ->orderBy('total', 'DESC')
                        ->get();                        
            return response()->json([
                "total"=>$brands,
                "M"=>$mans,
                "F"=>$womans]);
        }

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

        if ($request->moreServices || $request->order ==="moreServices")$query->orderBy('number_services', 'desc');
        
    

        if($request->order==='vehiclesName'){$query->orderBy('model','asc');
        return response($query->get());}
            
        if($request->startDate)$query->whereDate('created_at', '>=', $request->startDate);

        if ($request->endDate)$query->whereDate('created_at', '<=', $request->endDate);
        
        if (isset($request->brand)&&$request->brand!='all')$query->where('brand', $request->brand);
        

        if (isset($request->nameOwners) || ($request->order ==="nameOwners")) 
        {
            $cars = CarsOwner::select('id')
            ->orderBy('name')
            ->get();

            
           
            
            $carOwnerIds = $cars->pluck('id')->toArray();
            if(isset($request->ordenateOrder))$carOwnerIds = array_reverse($carOwnerIds);
            $orderedResults = collect();
            foreach ($carOwnerIds as $id) {
                $find = clone $query;
                $cars = $find->orWhere('owner_id', $id)->get();
                $orderedResults = $orderedResults->concat($cars);
    
            }
            $query = $orderedResults;
            $data = $orderedResults;
        } 


        if(!($request->order ==="nameOwners"))
        {
            if(isset($request->ordenateOrder))$data = $query->get()->reverse()->values();
            else{$data = $query->get();}        
        }


        if(isset($request->subOrder))
        {
            $suborder=[];
            if($request->subOrder ==="moreVehicleSex")
            {
                $man = CarsOwner::Where('sex','M')->get('id')->pluck('id');
                $woman = CarsOwner::Where('sex','F')->get('id')->pluck('id');
                $manCount=Cars::whereIn('owner_id',$man)->get()->count();
                $womanCount=Cars::whereIn('owner_id',$woman)->get()->count();
                $suborder = [
                    
                    "man"=> $manCount,
                    "woman"=> $womanCount 

                ];

            }
            if($request->subOrder ==="countModel")
            {


                $suborder = Cars::select('brand', DB::raw('COUNT(*) as car_count'))
                    ->groupBy('brand')
                    ->get();

            }
            if($request->subOrder ==="countModelSex")
            {
                $manIds = CarsOwner::where('sex', 'M')->pluck('id');
                $womanIds = CarsOwner::where('sex', 'F')->pluck('id');
                
                $manCounts = Cars::query()
                    ->select('brand', DB::raw('COUNT(*) as total_cars'))
                    ->whereIn('owner_id', $manIds)
                    ->groupBy('brand');
                
                $womanCounts = Cars::query()
                    ->select('brand', DB::raw('COUNT(*) as total_cars'))
                    ->whereIn('owner_id', $womanIds)
                    ->groupBy('brand');
                
                if ($request->has('ordenateOrder')) {
                    $manCounts->orderBy('total_cars', 'asc');
                    $womanCounts->orderBy('total_cars', 'asc');
                } else {
                    $manCounts->orderBy('total_cars', 'desc');
                    $womanCounts->orderBy('total_cars', 'desc');
                }
                
                $menCars = $manCounts->get();
                $womenCars = $womanCounts->get();
                
                $totalMenCars = $menCars->sum('total_cars');
                $totalWomenCars = $womenCars->sum('total_cars');
                
                $moreCarsSex = $totalMenCars > $totalWomenCars ? 'Masculino' : 'Feminino';
                
                $suborder = [
                    'man' => $menCars,
                    'women' => $womenCars,
                    'more_cars_sex' => $moreCarsSex,
                ];
            
    
            }
            return response()->json([
                "cars"=>$data,
                "order"=> $suborder

            ]);
        }
        
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
