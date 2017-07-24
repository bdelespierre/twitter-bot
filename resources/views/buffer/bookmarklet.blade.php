@extends('layouts.app')

@section('content')
    @php
        $css = preg_replace("/\s{2,}/", " ", str_replace("\n", '', (string) view('buffer.bookmarklet.css')));
        $js  = 'javascript:' . preg_replace("/\s{2,}/", " ", str_replace(['"', "\n"], ['%22', ''], (string) view('buffer.bookmarklet.js', compact('css'))));
    @endphp

    <a href="{{ $js }}" class="btn btn-primary">Buffer</a>

    <pre><code class="js">{{ $js }}</code></pre>
@endsection