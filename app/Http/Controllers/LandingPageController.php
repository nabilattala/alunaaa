<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LandingPageController extends Controller
{
    public function index()
    {
        $landingPages = LandingPage::all();
        return response()->json(['landing_pages' => $landingPages]);
    }

    public function show($id)
    {
        $landingPage = LandingPage::find($id);
        if (!$landingPage) {
            return response()->json(['message' => 'Landing Page not found'], 404);
        }
        return response()->json(['landing_page' => $landingPage]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $landingPage = LandingPage::create([
            'title' => $request->title,
            'content' => $request->content,
            'image_url' => $request->image_url,
        ]);

        return response()->json(['message' => 'Landing Page created successfully', 'landing_page' => $landingPage], 201);
    }

    public function update(Request $request, $id)
    {
        $landingPage = LandingPage::find($id);
        if (!$landingPage) {
            return response()->json(['message' => 'Landing Page not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $landingPage->update([
            'title' => $request->title ?? $landingPage->title,
            'content' => $request->content ?? $landingPage->content,
            'image_url' => $request->image_url ?? $landingPage->image_url,
        ]);

        return response()->json(['message' => 'Landing Page updated successfully', 'landing_page' => $landingPage]);
    }

    public function destroy($id)
    {
        $landingPage = LandingPage::find($id);
        if (!$landingPage) {
            return response()->json(['message' => 'Landing Page not found'], 404);
        }

        $landingPage->delete();
        return response()->json(['message' => 'Landing Page deleted successfully']);
    }
}
