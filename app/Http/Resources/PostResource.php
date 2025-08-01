<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $this->image ? asset(Storage::url($this->image)) : null,
            'likes_count' => $this->whenCounted('likedByUsers'),
            'liked_by_me' => auth()->check() ? $this->isLikedBy(auth()->user()) : false,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'bookmarked' => auth()->check() ? auth()->user()->bookmarkedPosts->contains($this->id) : false,
        ];
    }
}
