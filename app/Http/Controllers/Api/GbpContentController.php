<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GbpPhoto;
use App\Models\GbpPost;
use Illuminate\Http\Request;

/**
 * JSON twin of GbpContentController — read-only for the first mobile pass
 * (matches the "view" half of the Core module). Create/publish endpoints
 * (storePost, storePhoto, publish) can be added the same way once the
 * mobile Posts & Photos screen needs to write, not just read.
 */
class GbpContentController extends Controller
{
    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $scope = function ($q) use ($agencyId, $clientId) {
            $q->where('agency_id', $agencyId);
            if ($clientId) $q->where('id', $clientId);
        };

        $posts = GbpPost::whereHas('location.client', $scope)
            ->with('location.client')->latest()->get();

        $photos = GbpPhoto::whereHas('location.client', $scope)
            ->with('location.client')->latest()->get();

        return response()->json([
            'posts' => $posts->map(fn ($p) => [
                'id' => $p->id,
                'type' => $p->type,
                'body' => $p->body,
                'status' => $p->status,
                'location_title' => $p->location->title,
                'image_url' => $p->image ? route('media.gbp-post-image', $p) : null,
                'published_at' => $p->published_at,
            ]),
            'photos' => $photos->map(fn ($ph) => [
                'id' => $ph->id,
                'caption' => $ph->caption,
                'status' => $ph->status,
                'location_title' => $ph->location->title,
                'image_url' => route('media.gbp-photo', $ph),
            ]),
        ]);
    }
}
