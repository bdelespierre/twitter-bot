<?php

namespace App\Http\Controllers;

use App\Models\Pool\Item;
use App\Models\Buffer\Item as BufferItem;
use Illuminate\Http\Request;

class PoolController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('created_at', 'desc')->paginate(25);

        return view('pool.index', compact('items'));
    }

    public function accept(Item $item)
    {
        BufferItem::fromPool($item);

        return $this->reject($item);
    }

    public function reject(Item $item)
    {
        $item->delete();

        return redirect()->route('pool.index');
    }
}
