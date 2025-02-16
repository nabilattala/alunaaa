<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutResource;
use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AboutController extends Controller
{
    public function index()
    {
        return AboutResource::collection(About::latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'content' => 'required|string|max:10000',
            'title' => 'nullable|string|max:255',
        ]);

        $about = About::create($validatedData);
        
        return new AboutResource($about);
    }

    public function show(About $about)
    {
        return new AboutResource($about);
    }

    public function update(Request $request, About $about)
    {
        $validatedData = $request->validate([
            'content' => 'sometimes|required|string|max:10000',
            'title' => 'nullable|string|max:255'
        ]);

        $about->update($validatedData);
        
        return new AboutResource($about);
    }

    public function destroy(About $about)
    {
        $about->delete();
        return response()->json(['message' => 'About section deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}