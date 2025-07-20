<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Merge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Category::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $category = new Category();
        $request->validate([
            'title' => 'required',
            'image' => 'required'
        ]);
        $category->title = $request->title;
        $category->image = $request->image;
        $category->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category, $id)
    {
        return Category::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category, $id, Request $request)
    {
        $category = Category::findOrFail($id);
        $request->validate([
            'title' => 'required',
        ]);
        $category->title = $request->title;
        $category->image = $request->image;
        $category->save();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category, $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        // get Merge
        $merge = DB::table('merges')
            ->whereRaw("JSON_CONTAINS(merge, '$id')")
            ->get();

        foreach ($merge as $m) {
            $mergeArray = json_decode($m->merge, true);
            $mergeArray = array_filter($mergeArray, function ($value) use ($id) {
                return $value != $id;
            });
            $newMergeArray = array_values($mergeArray);
            DB::table('merges')->where('id', $m->id)->update(['merge' => json_encode($newMergeArray)]);
        }
        return $merge;
    }

    public function getCategoriesPaginate(Request $request)
    {
        /*  يعود بترتيب تنازلى 
            // $categories = Category::orderBy('title', 'desc')->paginate($request->input('limit'), '*', 'page', $request->input('number_page'));
        */

        $categories = Category::paginate(
            $request->input('limit'),
            '*',
            'page',
            $request->input('number_page')
        );
        return response()->json($categories);
    }
}
