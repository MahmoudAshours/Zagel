<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UserRequestController extends Controller
{
    public function createUser(Request $request)
    {
        $username = $request->input("username");
        $wa_no = $request->input("number");
        $res = DB::table("subscriber_data")->insert([
            "username" => $username,
            "whatsapp_number" => $wa_no,
        ]);

        if ($res) {
            return response()->json();
        } else {
            return response()->json(["msg" => "Error in paramaters"], 500);
        }
    }

    public function addWhatsappKey(Request $request)
    {
        $key = $request->input("key");
        $number = $request->input("number");
        $res = DB::table("subscriber_data")->where('whatsapp_number', '=', $number)->update([
            'whatsapp_key' => $key
        ]);
    }
    public function createJobDB(Request $request)
    {
        $pdf_binaries = $request->file("pdf_file");
        $path = $this->saveLocalFile($pdf_binaries);
        $factor = $request->input("factor");
        $time = $request->input("time");
        $startPage =  $request->input("startPage");
        $receivers = $request->input("receivers");
        $interval = $request->input("interval");
        $res = DB::table("subscriber_jobs")->insert([
            "user_id" => 1,
            "incremental_factor" => $factor,
            "job_time" => $time,
            "interval" => $interval,
            "receivers" => $receivers,
            "current_page" => $startPage,
            "pdf_path" => $path,
        ]);
        if ($res) {
            return response()->json();
        } else {
            return response()->json(["msg" => "Error in paramaters"], 500);
        }
    }

    private function saveLocalFile(UploadedFile $file): string
    {
        $storedFilePath = $file->store("pdf_files", "local");
        return $storedFilePath;
    }
}
