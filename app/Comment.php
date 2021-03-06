<?php

namespace App;
use App\Events\PostCommented;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Comment
 *
 * @property-read \App\User $author
 * @property-read \App\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $post_id
 * @property int $author_id
 * @property string $comment
 * @property int $important
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereImportant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereUpdatedAt($value)
 */
class Comment extends Model
{
    use HasRoles;

    protected $guard_name = 'web';

    protected $fillable = ['comment', 'author_id', 'important'];

    protected $dispatchesEvents = [
        'created' => PostCommented::class
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function likes() {
        return $this->hasMany(Like::class);
    }

    public function like()
    {
        if (auth()->user())
            if (!auth()->user()->hasLiked($this)) {
                $attributes['user_id'] = auth()->id();
                $attributes['comment_id'] = $this->id;

                Like::create($attributes);
            }
            else $this->dislike();
        else return back()->withErrors(__('You must be logged in to like a comment') . '!');
    }

    public function dislike()
    {
        $this->likes()->where('user_id', auth()->id())->delete();
    }

    public function getDate()
    {
        return $this->created_at->diffForHumans([
            'parts' => 1,
        ]);
    }
}
