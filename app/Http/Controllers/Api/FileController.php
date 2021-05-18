<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return FileResource::collection(File::latest()->paginate(10));
    }

    function getFileName($fileName)
    {
        if (Storage::exists('public/files/' . $fileName)) {
            $name = pathinfo($fileName, PATHINFO_FILENAME);
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $index = 2;
            $fileName = $name . '(' . $index . ').' . $extension;
            while (Storage::exists('public/files/' . $fileName)) {
                $index++;
                $fileName = $name . '(' . $index . ').' . $extension;
            }
        }
        return $fileName;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->has('file')) {
            $file = $request->file;
            $fileName = $this->getFileName($file->getClientOriginalName());
            $path = $file->storeAs('public/files', $fileName);
            $newFile = new File();
            $newFile->name = $fileName;
            $newFile->path = $path;
            $newFile->save();
            return new FileResource($newFile);
        }
        return response()->json([
            'message' => 'The server could not understand the request due to invalid syntax.',
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id == "all") {
            File::truncate();
            Storage::deleteDirectory('files');
        } else {
            $file = File::find($id);
            if ($file) {
                Storage::delete($file->path);
                $file->delete();
            } else {
                return response()->json([
                    'message' => 'The server can not find the requested resource.',
                ], 404);
            }
        }
        return response()->json([
            'message' => 'Delete success.',
        ], 202);
    }
    public function download(Request $request)
    {
        if ($request->has('id')) {
            $fileZip = public_path('Documents.zip');
            if (file_exists($fileZip)) {
                unlink($fileZip);
            }
            $zip = new ZipArchive;
            if ($zip->open(public_path('Documents.zip'), ZipArchive::CREATE) === TRUE) {
                $files = File::whereIn('id', $request->id)->get();
                if ($files->isNotEmpty()) {
                    foreach ($files as $file) {
                        $zip->addFile(Storage::path($file->path), $file->name);
                    }
                    $zip->close();
                    return response()->download('Documents.zip');
                }
            }
        }
        return response()->json([
            'message' => 'The server could not understand the request due to invalid syntax.',
        ], 400);
    }
}
