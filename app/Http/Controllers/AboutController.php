<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutResource;
use App\Models\About;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        return AboutResource::collection(About::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $about = About::create($request->all());

        return new AboutResource($about);
    }

    public function show(About $about)
    {
        return new AboutResource($about);
    }

    public function update(Request $request, About $about)
    {
        $request->validate([
            'content' => 'sometimes|string',
        ]);

        $about->update($request->all());

        return new AboutResource($about);
    }

    public function destroy(About $about)
    {
        $about->delete();

        return response()->json(['message' => 'About deleted successfully'], 204);
    }
}
