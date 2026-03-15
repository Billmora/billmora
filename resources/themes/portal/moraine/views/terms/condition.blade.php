@extends('portal::layouts.app')

@section('title', 'Terms of Condition - ' . Billmora::getGeneral('company_name'))

@section('body')
<section class="max-w-4xl mx-auto px-4">
    <div class="grid gap-8 pt-32">
        <h1 class="text-3xl text-slate-700 font-bold text-center">{{ __('portal.terms_of_condition') }}</h1>
        <div class="bg-white border-2 border-billmora-2 rounded-2xl p-8">
            <div class="prose prose-slate max-w-none">
                {!! Billmora::getGeneral('term_toc_content') !!}
            </div>
        </div>
    </div>
</section>
@endsection