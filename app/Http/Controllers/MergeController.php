<?php

namespace App\Http\Controllers;

use App\Models\Merge;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MergeController extends Controller
{

    public function GetAllMerge()
    {
        return Merge::all();
    }

    public function getMerge($id)
    {
        return Merge::where('id', $id)->first();
    }

    public function addMerge(Request $request)
    {
        $request->validate([
            'package_name' => 'required',
            'merge' => 'required',
        ]);
        $merge = DB::table('merges')->insert([
            'package_name' => $request->package_name,
            'merge' => $request->merge,
        ]);
        return response()->json([
            'merge' => $merge,
        ], 200);
    }

    public function editMerge(Request $request, $id)
    {
        $request->validate([
            'package_name' => 'required',
            'merge' => 'required',
        ]);
        $merge = Merge::findOrFail($id);
        $merge->package_name = $request->package_name;
        $merge->merge = $request->merge;
        $merge->save();
    }

    public function destroy($id)
    {
        return Merge::findOrFail($id)->delete();
    }


}

