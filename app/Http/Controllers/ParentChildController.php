<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParentChild;

class ParentChildController extends Controller
{
    // public function sendRequest(Request $request)
    // {
    //     $request->validate([
    //         'child_id' => 'required|exists:users,id',
    //     ]);

    //     $parent = Auth::user();
    //     $child = User::findOrFail($request->child_id);

    //     $parent->childrenMany()->attach($child, ['status' => 'send']);

    //     return response()->json(['message' => 'Request sent successfully']);
    // }

    // public function confirmRequest($parent_id)
    // {
    //     $child = Auth::user();
    //     $parent = User::findOrFail($parent_id);

    //     $relationship = $child->parents()->where('parent_id', $parent_id)->first();

    //     if ($relationship && $relationship->pivot->status == 'send') {
    //         $child->parents()->updateExistingPivot($parent_id, ['status' => 'confirm']);
    //         return response()->json(['message' => 'Request confirmed successfully']);
    //     }

    //     return response()->json(['message' => 'No request found or already confirmed'], 404);
    // }

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
    
    public function sendRequest(Request $request)
    {
        $user = Auth::user();
        $parentId = $user->id;
        $childId = $request->input('child_id');

        $requestExists = ParentChild::where('parent_id', $parentId)
                                    ->where('child_id', $childId)
                                    ->where('status', '!=', 'rejected')
                                    ->exists();

        if ($requestExists) {
            return response()->json(['message' => 'Request already exists or is pending.'], 400);
        }

        $parentChild = new ParentChild();
        $parentChild->parent_id = $parentId;
        $parentChild->child_id = $childId;
        $parentChild->status = 'send';
        $parentChild->save();

        return response()->json(['message' => 'Request sent successfully.']);
    }

    public function confirmRequest(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        $parentId = $request->input('parent_id');
        $childId = $user->id;

        $parentChild = ParentChild::where('parent_id', $parentId)
                                ->where('child_id', $childId)
                                ->where('status', 'send')
                                ->first();

        if (!$parentChild) {
            return response()->json(['message' => 'No pending request found.'], 404);
        }

        $parentChild->status = 'confirm';
        $parentChild->save();

        return response()->json(['message' => 'Request confirmed successfully.']);
    }

    public function getPendingRequestsForChild()
    {
        $child = Auth::user();

        if (!$child) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $pendingRequests = ParentChild::where('child_id', $child->id)
                                       ->where('status', 'send')
                                       ->with('parent')
                                       ->get();

        return response()->json($pendingRequests);
    }
}
