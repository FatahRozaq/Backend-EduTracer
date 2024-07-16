<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentChildController extends Controller
{
    public function sendRequest(Request $request)
    {
        $request->validate([
            'child_id' => 'required|exists:users,id',
        ]);

        $parent = Auth::user();
        $child = User::findOrFail($request->child_id);

        $parent->childrenMany()->attach($child, ['status' => 'send']);

        return response()->json(['message' => 'Request sent successfully']);
    }

    public function confirmRequest($parent_id)
    {
        $child = Auth::user();
        $parent = User::findOrFail($parent_id);

        $relationship = $child->parents()->where('parent_id', $parent_id)->first();

        if ($relationship && $relationship->pivot->status == 'send') {
            $child->parents()->updateExistingPivot($parent_id, ['status' => 'confirm']);
            return response()->json(['message' => 'Request confirmed successfully']);
        }

        return response()->json(['message' => 'No request found or already confirmed'], 404);
    }

    public function getChildren()
    {
        $parent = Auth::user();

        $children = $parent->childrenMany()->wherePivot('status', 'confirm')->get();

        return response()->json($children);
    }

    public function getParents()
    {
        $child = Auth::user();

        $parents = $child->parents()->wherePivot('status', 'confirm')->get();

        return response()->json($parents);
    }
}
