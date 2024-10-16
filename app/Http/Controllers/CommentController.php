<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/comments",
     *     tags={"Comments"},
     *     summary="Get all comments",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(response="200", description="List of comments",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="postId", type="integer", example=1),
     *             @OA\Property(property="content", type="string", example="This is a comment."),
     *             @OA\Property(property="userId", type="integer", example=1),
     *             @OA\Property(property="createdAt", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updatedAt", type="string", example="2024-10-10 18:00:00")
     *         ))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $comments = Comment::all()->map(fn($comment) => $comment->toCamelCase());
        return response()->json($comments, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/comments/{id}",
     *     tags={"Comments"},
     *     summary="Get a specific comment",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Comment details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="postId", type="integer", example=1),
     *             @OA\Property(property="content", type="string", example="This is a comment."),
     *             @OA\Property(property="userId", type="integer", example=1),
     *             @OA\Property(property="createdAt", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updatedAt", type="string", example="2024-10-10 18:00:00")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Comment not found")
     * )
     */
    public function show($id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        return response()->json($comment->toCamelCase(), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/comments",
     *     tags={"Comments"},
     *     summary="Create a new comment",
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content", "post_id"},
     *             @OA\Property(property="content", type="string", example="This is a comment."),
     *             @OA\Property(property="postId", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Comment created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="postId", type="integer", example=1),
     *             @OA\Property(property="content", type="string", example="This is a comment."),
     *             @OA\Property(property="userId", type="integer", example=1),
     *             @OA\Property(property="createdAt", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updatedAt", type="string", example="2024-10-10 18:00:00")
     *         )
     *     ),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'content' => 'required|string|min:1',
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        $validatedData['user_id'] = $user->id;

        $comment = Comment::create($validatedData);

        return response()->json([
            'id' => $comment->id,
            'postId' => $comment->post_id,
            'content' => $comment->content,
            'userId' => $comment->user_id,
            'createdAt' => $comment->created_at,
            'updatedAt' => $comment->updated_at,
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/comments/{id}",
     *     tags={"Comments"},
     *     summary="Update a specific comment",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Updated comment content."),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Comment updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="postId", type="integer", example=1),
     *             @OA\Property(property="content", type="string", example="Updated comment content."),
     *             @OA\Property(property="userId", type="integer", example=1),
     *             @OA\Property(property="createdAt", type="string", example="2024-10-10 18:00:00"),
     *             @OA\Property(property="updatedAt", type="string", example="2024-10-10 18:00:00")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Comment not found")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        $validatedData = $request->validate([
            'content' => 'sometimes|required|string',
        ]);

        $comment->update($validatedData);

        return response()->json($comment->toCamelCase(), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     tags={"Comments"},
     *     summary="Delete a specific comment",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Comment deleted"),
     *     @OA\Response(response="404", description="Comment not found")
     * )
     */
    public function destroy($id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully.'], 200);

    }
}
