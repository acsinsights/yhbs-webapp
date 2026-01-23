@extends('frontend.layouts.app')
@section('content')
    <section style="padding: 60px 0; background:#f7f9fc;">
        <div class="container">

            <div style="color:#555; font-size:15px; line-height:1.8;">
                {!! $page->content !!}
            </div>

        </div>
    </section>
@endsection
