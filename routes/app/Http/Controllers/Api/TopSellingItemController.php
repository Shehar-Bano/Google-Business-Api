<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TopSellingItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TopSellingItemController extends Controller
{
    /**
     * Update a specific top selling item.
     * POST /api/top-selling-items/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $item = TopSellingItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Top selling item not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'item_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'media' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['item_name', 'description', 'price']);

        // Handle media file upload
        if ($request->hasFile('media')) {
            // Delete old media file if exists
            if ($item->media) {
                $oldPath = str_replace('storage/', '', $item->media);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('media')->store('items', 'public');
            $updateData['media'] = 'storage/' . $path;
        } elseif ($request->has('media') && is_string($request->input('media'))) {
            $updateData['media'] = $request->input('media');
        }

        $item->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Top selling item updated successfully.',
            'data' => $item
        ], 200);
    }

    /**
     * Delete a specific top selling item.
     * DELETE /api/top-selling-items/{id}
     */
    public function destroy($id): JsonResponse
    {
        $item = TopSellingItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Top selling item not found.'
            ], 404);
        }

        // Delete media file if exists
        if ($item->media) {
            $oldPath = str_replace('storage/', '', $item->media);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Top selling item deleted successfully.'
        ], 200);
    }
}
