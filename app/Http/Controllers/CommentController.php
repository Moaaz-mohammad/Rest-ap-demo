<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $comments = $request->user()->comments()->latest()->get();
        $comments = Comment::with('replies')->whereNull('parent_id')->get();
        // $comments = Comment::where('user_id', $request->user_id)->latest()->get();

        return CommentResource::collection($comments);

        // return response()->json([
        //     'data' => $comments
        // ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'post_id' => 'required|exists:posts,id',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $validated['user_id'] = $request->user()->id;

        $comment = Comment::create($validated);

        $post = Post::find($validated['post_id']);
        $postAuthor = $post->user;

        if ($postAuthor->id !== $request->user()->id) {
            $postAuthor->notify(new NewCommentNotification($comment));
        }

        return response()->json([
            'message' => 'Comment add successfully',
            'data' => $comment
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = Comment::find($id);

        if (auth()->id() !== $comment->user_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ],403);
        }

        return response()->json([
            'data' => $comment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $comment = Comment::find($id);

        // Its Kind of Policy
        // if (auth()->id() !== $comment->user_id) {
        //     return response()->json([
        //         'message' => 'Unauthorized'
        //     ], 403);
        // }

        $this->authorize('update', $comment);

        $validated = $request->validate([
            'body' => 'required|string'
        ]);

        $comment->update($validated);

        return response()->json([
            'message' => 'Comment updated successfully',
            'data' => $comment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = Comment::find($id);

        // The old Check 
        // if (auth()->id() !== $comment->user_id) {
        //     return response()->json([
        //         'message' => 'Unauthorized'
        //     ], 403);
        // }

        // New Check With Policy
        $this->authorize('delete', $comment);

        return response()->json([
            'message' => 'Comment deleted successfully'
        ]);
    }
}
