<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index() {

        $bokmarkedPosts = auth()->user()->bookmarkedPosts;

        return response()->json([
            'Posts' => $bokmarkedPosts
        ]);

    }

    public function store(Request $request, Post $post) {

        $this->authorize('bookmark', $post);
        
        $request->user()->bookmarkedPosts()->syncWithoutDetaching([$post->id]);

        // $post = Post::findOrFail($request->post_id);
        // $request->user()->bookmarkedPosts()->syncWithoutDetaching([$post]);

        return response()->json([
            'message' => 'Post bookmarked sucessfully'
        ]);

    }

    public function destroy(Request $request, Post $post) {
        
        $this->authorize('unbookmark', $post);
        
        $request->user()->bookmarkedPosts()->detach($post);

        return response()->json([
            'success' => 'Post unbookmarked successfully'
        ]);
    }

}
