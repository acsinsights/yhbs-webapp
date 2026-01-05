@extends('frontend.layouts.app')
@section('content')
    <section style="padding: 60px 0; background:#f7f9fc;">
        <div class="container" style="max-width: 900px;">

            <h1 style="font-size:32px; font-weight:700; margin-bottom:20px;">
                {{ $page->title }}
            </h1>

            <div style="color:#555; font-size:15px; line-height:1.8;">
                {!! $page->content !!}
            </div>

        </div>
    </section>
@endsection
