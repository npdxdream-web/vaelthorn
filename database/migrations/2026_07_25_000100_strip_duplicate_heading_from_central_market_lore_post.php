<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The new compact thread header now renders the thread title itself, so the
     * Central Market lore post (id 12) no longer needs its own embedded
     * "Central Market" heading + divider — it duplicated the header. This
     * strips exactly that leading markup and nothing else, guarded by an exact
     * prefix match so it's a no-op if the post has already been edited.
     */
    public function up(): void
    {
        $post = Post::find(12);

        if (! $post) {
            return;
        }

        $prefix = '<h1 class="ql-align-center"><strong style="color: rgb(127, 98, 52);">Central Market </strong></h1>'
            .'<p class="ql-align-center">- - - - - - - - - - - - - - - - - - - - - - - </p>'
            .'<p class="ql-align-center"><br></p>';

        if (str_starts_with($post->content, $prefix)) {
            $post->content = substr($post->content, strlen($prefix));
            $post->save();
        }
    }

    public function down(): void
    {
        $post = Post::find(12);

        if (! $post) {
            return;
        }

        $prefix = '<h1 class="ql-align-center"><strong style="color: rgb(127, 98, 52);">Central Market </strong></h1>'
            .'<p class="ql-align-center">- - - - - - - - - - - - - - - - - - - - - - - </p>'
            .'<p class="ql-align-center"><br></p>';

        if (! str_starts_with($post->content, $prefix)) {
            $post->content = $prefix.$post->content;
            $post->save();
        }
    }
};
