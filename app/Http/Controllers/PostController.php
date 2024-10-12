<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     summary="Get all posts",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(response="200", description="List of posts",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Post Title"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post."),
     *             @OA\Property(property="userId", type="integer", example=1),
     *             @OA\Property(property="createdAt", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updatedAt", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="commentsCount", type="integer", example=5)
     *         ))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $posts = Post::withCount('comments')->get()->map(fn($post) => $post->toCamelCase());
        return response()->json($posts, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Get a specific post",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Post details with comments and their count",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Post Title"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post."),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="comments_count", type="integer", example=5),
     *             @OA\Property(property="comments", type="array", @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="content", type="string", example="This is a comment."),
     *                  @OA\Property(property="post_id", type="integer", example=1),
     *                  @OA\Property(property="user_id", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", example="2024-10-10 18:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-10-10 18:00:00")
     *              ))
     *         )
     *     ),
     *     @OA\Response(response="404", description="Post not found")
     * )
     */
    public function show($id): JsonResponse
    {
        $post = Post::with('comments')->find($id);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'user_id' => $post->user_id,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'comments_count' => $post->comments()->count(),
            'comments' => $post->comments->map(fn($comment) => $comment->toCamelCase()),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     summary="Create a new post",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "user_id"},
     *             @OA\Property(property="title", type="string", example="Post Title"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post."),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response="201", description="Post created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Post Title"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post."),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2024-10-10 18:00:00")
     *         )
     *     ),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        $post = Post::create($validatedData);
        return response()->json($post->toCamelCase(), 201);
    }


    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Update a specific post",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Post Title"),
     *             @OA\Property(property="content", type="string", example="Updated content of the post."),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Post updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Updated Post Title"),
     *             @OA\Property(property="content", type="string", example="Updated content of the post."),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2024-10-10 18:00:00")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Post not found")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
        ]);

        $post->update($validatedData);

        return response()->json($post->toCamelCase(), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Delete a specific post",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Post deleted"),
     *     @OA\Response(response="404", description="Post not found")
     * )
     */
    public function destroy($id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully.'], 204);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/user/{id}",
     *     tags={"Posts"},
     *     summary="Get posts of a user by user ID with comments count",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to fetch posts for",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="List of user posts with comments count",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Post Title"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post."),
     *             @OA\Property(property="userId", type="integer", example=1),
     *             @OA\Property(property="createdAt", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updatedAt", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="commentsCount", type="integer", example=5)
     *         ))
     *     ),
     *     @OA\Response(response="404", description="User not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function userPosts($id): JsonResponse
    {
        $posts = Post::where('user_id', $id)
            ->withCount('comments')
            ->with(['comments'])
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'commentsCount' => $post->comments_count,
                    'comments' => $post->comments->map(function ($comment) {
                        return [
                            'id' => $comment->id,
                            'content' => $comment->content,
                            'userId' => $comment->user_id,
                            'createdAt' => $comment->created_at,
                        ];
                    }),
                ];
            });

        return response()->json($posts);
    }

}
