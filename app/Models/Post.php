<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body', 'image', 'status'];

    public const STATUSES = ['published', 'draft', 'archived'];

    // public function scopeSearch($query, $search) {

    //     return $query->where( function ($q) use ($search) {
    //         $q->where('title', "like", "%$search")->orWhere('body', "like", "%$search");
    //     } );
    // }

    public function scopeFilter($query, $request) {
        

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')->orWhere('body', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('tags')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('tags.id', $request->tags);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }elseif ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }elseif ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('sort_by')) {

            switch($request->sort_by) {
                case 'likes_desc':
                    $query->orderByDesc('liked_by_users_count');
                    break;
                case 'likes_asc':
                    $query->orderBy('liked_by_users_count');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'asc');
                break;
            }

        }
    }


    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    
    public function likedByUsers() {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function isLikedBy(User $user) {
        return $this->likedByUsers()->where('user_id', $user->id)->exists();
    }

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }

    public function bookmarkedByUsers() {
        return $this->belongsToMany(User::class, 'bookmark_post');
    }
}
