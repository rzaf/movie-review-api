<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
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
            'text' => $this->text,
            'review_id' => $this->whenHas('review_id'),
            'reply_id' => $this->whenHas('reply_id'),
            'replier_username' => $this->whenLoaded('user', function () {
                return $this->user->name;
            }),
            'replied_to_review' => new ReviewResource($this->whenLoaded('review')),
            'replied_to_reply' => new ReplyResource($this->whenLoaded('reply')),

            'replies_count' => $this->whenCounted('replies'),
            'likes_count' => $this->whenCounted('likes'),
            'dislikes_count' => $this->whenCounted('dislikes'),
        ];
    }
}
