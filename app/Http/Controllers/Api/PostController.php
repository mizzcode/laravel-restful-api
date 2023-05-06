<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        //get all posts
        $posts = Post::latest()->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request)
    {
        // membuat validator
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // jika validator gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image')->store('posts');

        // create post
        $post = Post::create([
            'image' => $image,
            'title' => $request->title,
            'content' => $request->content
        ]);

        // return response
        return new PostResource(true, 'Data post berhasil di tambahkan!', $post);
    }

    public function show($id)
    {
        $post = Post::find($id);

        return new PostResource(true, 'Detail Data Post!', $post);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post = Post::find($id);

        if ($request->hasFile('image')) {
            // upload image
            $image = $request->file('image')->store('posts');

            /// delete old image
            Storage::delete('public/' . $post->image);

            // update post with new image
            $post->update([
                'image' => $image,
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {
            // update post without image
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        // return response
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    public function destroy($id)
    {
        $post = Post::find($id);

        // delete image
        Storage::delete('/storage/' . $post->image);

        // delete post
        $post->delete();

        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }
}
