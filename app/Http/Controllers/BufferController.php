<?php

namespace App\Http\Controllers;

use App\Models\BufferItem;
use Illuminate\Http\Request;

class BufferController extends Controller
{
    public function index()
    {
        $items = BufferItem::all();
        return view('buffer.index', compact('items'));
    }

    public function add(Request $request)
    {
        $urls  = $request->input('urls');
        $items = [];

        foreach (array_filter(array_map('trim', explode("\n", $urls))) as $url) {
            $items[] = BufferItem::create(compact('url'));
        }

        return redirect()
            ->route('buffer.index')
            ->with('created_items', $items);
    }

    public function view(BufferItem $item)
    {
        return view('buffer.view', compact('item'));
    }

    public function pixel(Request $request)
    {
        if ($url = $request->server('HTTP_REFERER')) {
            BufferItem::create(compact('url'));
        }

        return response()->file(public_path('pixel.png'));
    }

    public function bookmarklet()
    {
        return view('buffer.bookmarklet', compact('js'));
    }
}
