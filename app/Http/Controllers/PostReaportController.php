<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRepportRequest;
use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Http\Request;

class PostReaportController extends Controller
{
    

    public function store(StorePostRepportRequest $request) {
        
        $alreadyReported = PostReport::where('user_id', auth()->user()->id)->where('post_id', $request->post_id)->exists();

        if ($alreadyReported) {
            return response()->json([
                'message' => 'You already reported this post'
            ], 409);
        }

        $report = PostReport::create([
            'user_id' => auth()->user()->id,
            'post_id' => $request->post_id,
            'reason' => $request->reason,
            'notes' => $request->notes
        ]);

        return response()->json([
            'message' => 'Post reported successfully', 
            'report' => $report
        ], 201);
    }

}
