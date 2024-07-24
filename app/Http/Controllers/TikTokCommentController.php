<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class TikTokCommentController extends Controller
{
    public function showForm()
    {
        return view('tiktok-comments');
    }

    public function getComments(Request $request)
    {
        $username = $request->input('username');

        $process = new Process(['node', base_path('node-scripts/index.js'), $username]);
        $process->start();

        return response()->json(['status' => 'Script is running']);
    }
}
