<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;

use function PHPUnit\Framework\returnCallback;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $posts = $request->user()->posts()->latest()->paginate(1);
            // $data = $posts->map(function ($post) {
            //     return [
            //         'id' => $post->id,
            //         'title' => $post->title,
            //         'body' => $post->body,
            //         'image_url' => $post->image ? asset(Storage::url($post->image)) : null,
            //         'created_at' => $post->created_at
            //     ];
            // });

            // return response()->json([
            //     'status' => true,
            //     'data' => $data,
        // ]);

        //New Way With Resource and search (Better)
        $query = $request->user()->posts()->withCount('likedByUsers');
        
        // This First Way to search
        // if ($request->has('search')) {
        //     $search = $request->get('search');
        //     $query->where(function ($q) use ($search) {
        //         $q->where('title', 'like', "%$search")->orwhere("body", "like", "%$search");
        //     });
        // }

        //Secound Way to search
        // if ($request->filled('search')) {
        //     $query->search($request->get('search'));
        // }

        $query->filter($request);

        $posts = $query->paginate(10);

        return PostResource::collection($posts);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        // $validated = $request->validate([
        //     'title' => 'required|string',
        //     'body' => 'required|string',
        //     'string' => ['required', Rule::in(Post::STATUSES)],
        //     'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        // ]);

        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $validated['user_id'] = $request->user()->id;

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('posts', 'public');
                $validated['image'] = $path;
            }

            // $post = Post::create($validated);
            $post = $request->user()->posts()->create($validated);

            if ($request->has('tags')) {
                $post->tags()->sync($request->tags);
            }

            DB::commit();

            
            return response()->json([
                'message' => 'Post posted successfully',
                'data' => $post->load('tags'),
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Somthing went wrong',
                'error' => $th->getMessage()
            ], 500);
        }


        // $validated = $request->validated();

        // if ($request->hasFile('image')) {
        //     $path = $request->file('image')->store('posts', 'public');
        //     $validated['image'] = $path;
        // }

        // $post = $request->user()->posts()->create($validated);

    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {

        // $post_ = Post::withCount('likedByUsers')->findOrFail($post->id); //We use this when i start from the zero to find the object
        $post_ = $post->loadCount('likedByUsers'); // This i can use it when i have the post like that already requested

        if (!$post_) {
            return response()->json([
                'message' => 'Post not found'
            ]);
        }

        return response()->json([
            'data' => $post_
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        
        // if ($request->user()->id != $post->user_id) {
        //     return response()->json([
        //         'message' => 'Unauthorized'
        //     ], 403);
        // }

        $this->authorize('update', $post);

        DB::beginTransaction();

        try {

            $validated = $request->validated();

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = now()->timestamp . '.' . $image->extension();
                $path = $image->storeAs('posts', $imageName, 'public');
                $validated['image'];
            }

            $post->update($validated);

            if ($request->has('tags')) {
                $post->tags()->sync($request->tags);
            }

            DB::commit();

            return response()->json([
                'message' => 'Post updated successfully',
                'data' => $post->load('tags')
            ],200);

        } catch (\Throwable $th) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Somhting went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post)
    {
        //No need to check because laravel will get the post Auto and if it is not found it wil return 404 
        // if (!$post) {
        //     return response()->json([
        //         'message' => 'Post not found'
        //     ]);
        // }


        // if ($request->user()->id != $post->user_id) {
        //     return response()->json([
        //         'message' => 'Unauthorized'
        //     ]);
        // }

        $this->authorize('delete', $post);

        DB::beginTransaction();

        try {

            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }

            $post->tags()->detach();

            $post->delete();

            return response()->json([
                'message' => 'Post deleted successfully'
            ], 200);

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Somthing went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
        
    }

    public function like($id) {
        $post = Post::findOrFail($id);
        
        $user = auth()->user();

        if ($post->likedByUsers()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You already liked this post'
            ], 403);
        }

        $post->likedByUsers()->attach($user->id);

        return response()->json([
            'message' => 'Post liked successfully'
        ]);
    }

    public function unlike($id) {
        
        $post = Post::findOrFail($id);

        $user = auth()->user();

        if (!$post->likedByUsers()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You have not liked this post yet.'
            ], 404);
        }

        $post->likedByUsers()->detach($user->id);

        return response()->json([
            'message' => 'Post unliked succefully.'
        ]);
    }
}
