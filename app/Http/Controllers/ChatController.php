<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Mulai chat baru atau ambil chat yang sudah ada
    public function startChat(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'seller_id' => 'required|exists:users,id',
        ]);

        $seller = User::where('id', $request->seller_id)->where('role', 'kelas')->first();
        if (!$seller) {
            return response()->json(['error' => 'Seller must be a class (kelas).'], 400);
        }

        $chat = Chat::firstOrCreate([
            'product_id' => $request->product_id,
            'buyer_id' => Auth::id(),
            'seller_id' => $request->seller_id,
        ]);

        return response()->json($chat);
    }

    // Ambil semua chat berdasarkan peran (kelas atau pengguna)
    public function getChats()
    {
        $user = Auth::user();
        if ($user->role === 'kelas') {
            // Jika user adalah penjual (kelas), ambil semua chat yang menuju kelas tersebut
            $chats = Chat::where('seller_id', $user->id)->with(['buyer', 'messages'])->get();
        } else {
            // Jika user adalah pembeli, ambil semua chat yang dibuat olehnya
            $chats = Chat::where('buyer_id', $user->id)->with(['seller', 'messages'])->get();
        }

        return response()->json($chats);
    }

    // Ambil pesan dari chat tertentu
    public function getMessages($chatId)
    {
        $messages = Message::where('chat_id', $chatId)
            ->with('sender')
            ->get();

        return response()->json($messages);
    }

    // Kirim pesan dalam chat
    public function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'chat_id' => $request->chat_id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return response()->json($message);
    }
}
