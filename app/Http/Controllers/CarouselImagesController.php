<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CarouselImagesController extends Controller
{
    public function setImage(Request $request)
    {
        DB::table('carousel_images')->insert(
            [
                'url' => $request->url,
                'description' => $request->description,
            ]
        );

        return response()->json([
            'id' => DB::getPdo()->lastInsertId(),
            'url' => $request->url,
            'description' => $request->description,
        ]);
    }
    // set edit description only
    public function setEditDescription(Request $request)
    {
        DB::table('carousel_images')->where('id', $request->id)->update(
            [
                'description' => $request->description,
            ]
        );
        return response()->json([
            'id' => $request->id,
            'description' => $request->description,
        ]);
    }

    public function getImages()
    {
        $images = DB::table('carousel_images')->get();
        return response()->json($images);
    }

    public function deleteImage($id)
    {
        $image = DB::table('carousel_images')->where('id', $id)->first();
        if ($image) {
            DB::table('carousel_images')->where('id', $id)->delete();
            return ['status' => 'success', 'message' => 'Image deleted successfully',];
        }
        return ['status' => 'error', 'message' => 'Image not found',];
    }

}
