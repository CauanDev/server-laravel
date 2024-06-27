<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;
use App\Models\CarsService;
use App\Models\Cars;
use App\Models\CarsOwner;
use App\Models\Worker;
class ServicesController extends Controller
{

    public function index(AuthRequest $request){
        $services = CarsService::orderBy('id', 'DESC')->get();
        $today = Carbon::today();
        $next7Days = $today->copy()->addDays(7);
        $next15Days = $today->copy()->addDays(15);
        $next30Days = $today->copy()->addDays(30);
    
        $servicesNext7Days = CarsService::whereBetween('date_service', [$today, $next7Days])
            ->orderBy('date_service', 'ASC')
            ->get();
    
        $servicesNext15Days = CarsService::whereBetween('date_service', [$today, $next15Days])
            ->orderBy('date_service', 'ASC')
            ->get();
    
        $servicesNext30Days = CarsService::whereBetween('date_service', [$today, $next30Days])
            ->orderBy('date_service', 'ASC')
            ->get();
        return response()->json([
            'status' => true,
            'services' => $services,
            'services_next_7_days' => $servicesNext7Days,
            'services_next_15_days' => $servicesNext15Days,
            'services_next_30_days' => $servicesNext30Days,
        ], 200);
    }

    public function especial(AuthRequest $request)
    {
        
        $query = CarsService::query();
        $ownerQuery = CarsOwner::query();
        $car = Cars::query();
        $workerQuery = Worker::query();
        
 

        if (isset($request->name) && $request->name !=='all') 
        {
            $ownerQuery->where('name', 'ilike', '%' . $request->name . '%')->get('id');
            $ownerIds = $ownerQuery->pluck('id'); 
            $query->whereIn('owner_id', $ownerIds);
        }
        if(isset($request->minSalary))$query->where('price', '>=', $request->minSalary);
        if(isset($request->maxSalary))$query->where('price', '<=', $request->maxSalary);
        if (isset($request->worker)&& $request->worker !=='all')
        {
            $workerQuery->where('name', 'ilike', '%' . $request->worker . '%')->get('id');
            $workerIds = $workerQuery->pluck('id');
            $query->whereIn('worker_id', $workerIds);
        }
        if(isset($request->brand))        {
            if($request->brand=='maior')
            {
                $query->select('car_id', DB::raw('COUNT(*) as total'))
                ->groupBy('car_id')
                ->orderByDesc('total')
                ->first();

            }
            if($request->brand=='menor')
            {
                $query->select('car_id', DB::raw('COUNT(*) as total'))
                ->groupBy('car_id')
                ->orderBy('total')
                ->first();
            }
        }

     
           

        if(isset($request->startDate)) $query->whereDate('date_service', '>=', $request->startDate);
            
       if(isset($request->endDate))$query->whereDate('date_service', '<=', $request->endDate);
            
        
        if(isset($request->average) && $request->average)
        {

            $carIds = $query->distinct()->pluck('car_id')->toArray();

            $results = [];
            
            foreach ($carIds as $carId) {
                $services = CarsService::where('car_id', $carId)
                            ->orderBy('date_service')
                            ->get();
            
                $count = $services->count();
                $totalDays = 0;
            
                if ($count > 1) {
                    for ($i = 1; $i < $count; $i++) {
                        $datePrev = Carbon::parse($services[$i - 1]->date_service);
                        $dateCurr = Carbon::parse($services[$i]->date_service);
                        $diffInDays = abs($dateCurr->diffInDays($datePrev));
                        $totalDays += $diffInDays;
                    }
                    $averageDays = $totalDays / ($count - 1); 
                    $averageDays = number_format($averageDays, 2, '.', ''); 
                    $averageDays = $averageDays." Dias"; 
                } else {
                    $averageDays = "Feito Apenas Uma Revisão"; 
                }
            
                $carName = $car->where('id', $carId)->first(['model', 'brand']);
                $car = Cars::where('id', $carId)->first();
                $results[] = [
                    'service'=>$car,
                    'average_days' => $averageDays,
                    'car_name' => $carName
                ];
            }
            return response($results);

        }
        $results = $query->get();
        return response()->json($results);

    }
    public function register(AuthRequest $request)
    {

       
        if(isset($request->car_id))
        {
            $car_id = Cars::where('owner_id' ,$request->car_id)->first('id');
            Cars::where('owner_id' ,$request->car_id)->first('id')->increment('number_services');
            $car_id = $car_id->id;
        }
        if(isset($request->owner_id))
        {
            CarsOwner::where('id' ,$request->owner_id)->first('id')->increment('number_services');
            $owner_id = $request->owner_id;
        }
        $worker_id = $request->worker_id;

        if(isset($request->worker_name)){
            $worker_id = Worker::where('name',$request->worker_name)->first('id');
        
        }
        DB::beginTransaction();
        
        try {

            $services = CarsService::create([
                'name_service' => json_encode($request->nameService),
                'price' => $request->price,
                'car_id' =>  $car_id,
                'owner_id' =>  $owner_id,
                'worker_id' =>  $worker_id,
                'date_service' => $request->date,
            ]);
            Cars::where('id', $car_id)->update(['last_service'=>$request->date]);
            DB::commit();

            return response()->json([
                'status' => true,
                'services' => $services,
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


    public function destroy($id,AuthRequest $request )
    {
        
        $carsService = CarsService::where('id',$id)->first();
        $car_id = CarsService::where('id',$id)->first('car_id');
        Cars::where('id',$car_id->car_id)->first()->decrement('number_services');
        try {

            $carsService->delete();

            return response()->json([
                'status' => true,
                'owners' => $carsService,
                'message' => "Serviço apagado com sucesso!",
            ], 200);


        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Serviço não apagado!",
            ], 400);
        }
    }


    public function update(AuthRequest $request)
    {
        if(isset($request->car_id))$car_id = $request->car_id;

        if(isset($request->car_name))$car_id = Cars::where('name',$request->car_name)->first('id');

        if(isset($request->owner_id))$owner_id = $request->owner_id;
        if(isset($request->owner_name))$owner_id = CarsOwner::where('name',$request->owner_name)->first('id');

        if(isset($request->worker_id))$worker_id = $request->worker_id;

        if(isset($request->worker_name))$worker_id = Worker::where('name',$request->worker_name)->first('id');
        
        DB::beginTransaction();
        $service = CarsService::where('id',$request->service_id)->first();
        
        
        try {

            $service->update([
                'name_service' => json_encode($request->nameService),
                'price' => $request->price,
                'car_id' =>  $car_id,
                'owner_id' =>  $owner_id,
                'worker_id' =>  $worker_id,
                'date_service' => $request->date,
                'updated_at'=>Carbon::now()->format('Y-m-d H:i:s')
            ]);
            Cars::where('id', $car_id)->update(['last_service'=>$request->date]);
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
}
