<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DiscountController extends Controller
{
    public function index()
    {
        return response()->json(Discount::all());
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:discounts|max:255',
            'percentage' => 'required|integer|min:1|max:100',
            'expires_at' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $discount = Discount::create([
            'code' => $request->code,
            'percentage' => $request->percentage,
            'expires_at' => $request->expires_at,
            'created_by' => auth()->id(),
        ]);

        return response()->json($discount, 201);
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $discount = Discount::findOrFail($id);
        $discount->delete();

        return response()->json(['message' => 'Discount deleted successfully']);
    }
}
