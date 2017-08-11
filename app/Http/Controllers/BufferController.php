<?php

namespace App\Http\Controllers;

use App\Models\Buffer\Item;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class BufferController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::orderBy('updated_at', 'desc')
            ->when($request->has('search'), function($query) use ($request) {
                return $query->withUrl($request->get('search'));
            })
            ->when($request->has('show'), function($query) use ($request) {
                switch (strtolower($request->input('show'))) {
                    case 'trashed':
                        return $query->withTrashed();

                    case 'only-trashed':
                        return $query->onlyTrashed();

                    default:
                        return $query;
                }
            })
            ->get();

        return view('buffer.index', compact('items'));
    }

    public function add(Request $request)
    {
        $urls  = $request->input('urls');
        $items = [];

        foreach (array_filter(array_map('trim', explode("\n", $urls))) as $url) {
            try {
                $items[] = Item::create(compact('url'));
            } catch (QueryException $e) {
                //
            }
        }

        return redirect()
            ->route('buffer.index')
            ->with('created_items', $items);
    }

    public function view(Item $item)
    {
        return view('buffer.view', compact('item'));
    }

    public function refresh(Item $item)
    {
        $item->refresh();

        return redirect()->route('buffer.index');
    }

    public function tweet()
    {
        $item->tweet();

        return redirect()->route('buffer.index');
    }

    public function delete(Item $item)
    {
        $item->delete();

        return redirect()->route('buffer.index');
    }

    public function pixel(Request $request)
    {
        if ($url = $request->server('HTTP_REFERER')) {
            try {
                Item::create(compact('url'));
            } catch (QueryException $e) {
                //
            }
        }

        return response()->file(public_path('pixel.png'));
    }

    public function bookmarklet()
    {
        return view('buffer.bookmarklet', compact('js'));
    }
}
