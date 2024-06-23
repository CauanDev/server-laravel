<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\ReporterRequest;
use Illuminate\Support\Facades\Log;
class ReportController extends Controller
{
    public function generate(ReporterRequest $request)
    {
        try {
            Log::info('Iniciando a geração do PDF.');
    
            $headings = $request->input('head');
            $data = $request->input('body');    
            $title = $request->input('title');   
            $user = $request->input('user');           
            $subHeadings = $request->input('subHead');
            $subData = $request->input('subBody');    
            $subDataExist = $request->input('subDataExist');
            

            Log::info('Dados recebidos do request.', compact('headings', 'data', 'title', 'user'));

            $pdf = PDF::loadView('report', compact(
                'headings', 'data', 'title', 'user',
                'subHeadings', 'subData', 'subDataExist'
            ))->setPaper('a4', 'portrait');
            Log::info('PDF gerado com sucesso.');
    
            return $pdf->download('relatorio'.$title.'.pdf');
        } catch (\Exception $e) {
            Log::error('Erro ao gerar o PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao gerar o PDF: ' . $e->getMessage()], 500);
        }
    }
    
}
