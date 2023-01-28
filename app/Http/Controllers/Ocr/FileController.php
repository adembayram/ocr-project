<?php

namespace App\Http\Controllers\Ocr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory;
use App\Http\Requests\Ocr\OcrCreateRequest;
use App\Models\Ocr;


class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $ocrs = Ocr::OrderBy('id','DESC')->get();

        return view('index',compact('ocrs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OcrCreateRequest $request)
    {
        //
        $fileName = $request->fileInput->getClientOriginalName() . "_" . date("YmdHis") . "." . $request->fileInput->getClientOriginalextension();
        $fileNameWithUpload = 'uploads/ocr/' . $fileName;
        $request->fileInput->move(public_path('uploads/ocr'), $fileName);

        $content = ''; 

        if($request->fileInput->getClientOriginalextension() == "pdf"){

            $content =  (new Pdf(env("PDF_TO_TEXT")))->setPdf(public_path($fileNameWithUpload))->text();


        }elseif($request->fileInput->getClientOriginalextension() == "docx"){
            
            $phpWord = IOFactory::load(public_path($fileNameWithUpload));
                     
        
             foreach($phpWord->getSections() as $section) {
                foreach($section->getElements() as $element) {
                    if (method_exists($element, 'getElements')) {
                        foreach($element->getElements() as $childElement) {
                            if (method_exists($childElement, 'getText')) {
                                $content .= $childElement->getText() . ' ';
                            }
                            else if (method_exists($childElement, 'getContent')) {
                                $content .= $childElement->getContent() . ' ';
                            }
                        }
                    }
                    else if (method_exists($element, 'getText')) {
                        $content .= $element->getText() . ' ';
                    }
                }
            } 

        
           
           

        }else{

            $content = (new TesseractOCR(public_path($fileNameWithUpload)))->run(); 

        }

        $ocrCreate = Ocr::create([

            'filename' => $fileName,
            'old_filename' => $request->fileInput->getClientOriginalName(),
            'content' => $content
        ]);

            return redirect()->route('index')->withSuccess('Operation Success!');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $ocr = Ocr::find($id);

        return response()->json($ocr);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //

      

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}