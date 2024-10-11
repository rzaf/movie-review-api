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
        $arr = [
            'id' => $this->id,
            'text' => $this->text,
        ];
        if (isset($this->review_id)) {
            $arr['review_id'] = $this->review_id;
        }
        if (isset($this->reply_id)) {
            $arr['reply_id'] = $this->reply_id;
        }


        if ($this->relationLoaded('review')) {
            if (isset($this->review)) {
                $arr['replied_to_review'] = new ReviewResource($this->review);
            }
        }
        if ($this->relationLoaded('reply')) {
            if (isset($this->reply)) {
                $arr['replied_to_reply'] = new ReplyResource($this->reply);
            }
        }


        if ($this->relationLoaded('user')) {
            $arr['replier_username'] = $this->user->name;
        }
        if (isset($this->replies_count)) {
            $arr['replies_count'] = $this->replies_count;
        }
        if (isset($this->likes_count)) {
            $arr['likes_count'] = $this->likes_count;
        }
        if (isset($this->dislikes_count)) {
            $arr['dislikes_count'] = $this->dislikes_count;
        }
        return $arr;
    }
}
